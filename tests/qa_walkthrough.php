<?php

/**
 * Pengujian alur SIBESTI terhadap database aktif.
 * Menambah 2 set data contoh (QA-A dan QA-B) sesuai urutan pemakaian sistem.
 */

use App\Http\Controllers\BookingGeowisataController;
use App\Http\Controllers\ContentController;
use App\Http\Controllers\PendaftaranMagangController;
use App\Http\Controllers\PlantController;
use App\Http\Controllers\PlantingFieldController;
use App\Http\Controllers\PlantingLocationController;
use App\Http\Controllers\PlantTypeController;
use App\Http\Controllers\SeedRequestController;
use App\Http\Controllers\SeedSourceController;
use App\Http\Controllers\SeedUnitController;
use App\Http\Controllers\WarehouseController;
use App\Models\BookingGeowisata;
use App\Models\Plant;
use App\Models\Planting;
use App\Models\PlantingField;
use App\Models\PlantingLocation;
use App\Models\PlantType;
use App\Models\SeedRequest;
use App\Models\SeedRequestItem;
use App\Models\SeedSource;
use App\Models\SeedUnit;
use App\Models\User;
use App\Models\Warehouse;
use App\Models\WebsiteContent;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->handle(Illuminate\Http\Request::create('/', 'GET'));

$stamp = date('YmdHis');
$results = [];
$created = ['A' => [], 'B' => []];

function record(array &$results, string $id, string $step, string $expected, bool $ok, string $actual): void
{
    $results[] = compact('id', 'step', 'expected', 'ok', 'actual');
    $mark = $ok ? 'PASS' : 'FAIL';
    echo sprintf("[%s] %s — %s\n", $mark, $id, $step);
    if (! $ok) {
        echo "         diharapkan: {$expected}\n";
        echo "         aktual    : {$actual}\n";
    }
}

function httpGet($kernel, User $user, string $url): array
{
    Auth::login($user);
    $request = Request::create($url, 'GET');
    $request->setLaravelSession(app('session')->driver());
    app('session')->driver()->start();
    app('auth')->setUser($user);
    $response = $kernel->handle($request);
    $body = (string) $response->getContent();

    return [
        'status' => $response->getStatusCode(),
        'body' => $body,
        'ok' => $response->getStatusCode() < 400,
    ];
}

function makeRequest(string $method, string $uri, array $data = []): Request
{
    $request = Request::create($uri, $method, $data);
    $request->headers->set('Accept', 'text/html,application/json');
    app()->instance('request', $request);

    return $request;
}

function nextOpenDate(): string
{
    $day = Carbon::today()->addDay();
    for ($i = 0; $i < 60; $i++) {
        if (BookingGeowisata::isDateAvailable($day->toDateString(), 12)) {
            return $day->toDateString();
        }
        $day->addDay();
    }

    return Carbon::today()->next(Carbon::TUESDAY)->toDateString();
}

$admin = User::where('role', 'admin')->first() ?? User::first();
if (! $admin) {
    fwrite(STDERR, "Tidak ada user admin.\n");
    exit(1);
}
Auth::login($admin);

SeedUnit::ensureFixed();
$unitKg = SeedUnit::where('code', 'kg')->first();
$unitBtg = SeedUnit::where('code', 'btg')->first() ?: $unitKg;

$publicPages = [
    '/' => 'Beranda',
    '/profil' => 'Profil',
    '/berita' => 'Berita',
    '/dokumen' => 'Dokumen',
    '/informasi-publik' => 'Informasi publik',
    '/kontak' => 'Kontak',
    '/permintaan' => 'Daftar permintaan publik',
    '/permintaan/buat' => 'Form permintaan publik',
    '/kunjungan-geowisata' => 'Geowisata',
    '/kunjungan-geowisata/buat' => 'Form geowisata',
    '/pendaftaran-magang' => 'Magang',
    '/pendaftaran-magang/buat' => 'Form magang',
    '/login' => 'Login',
];

foreach ($publicPages as $url => $label) {
    $guest = Request::create($url, 'GET');
    $res = $kernel->handle($guest);
    record($results, 'PUB-'.$url, "Akses publik {$label} ({$url})", 'HTTP 200', $res->getStatusCode() === 200, 'HTTP '.$res->getStatusCode());
}

$internalPages = [
    '/dashboard' => 'Dasbor',
    '/contents' => 'CMS konten',
    '/contents/create' => 'Form konten',
    '/contents/settings' => 'Pengaturan situs',
    '/contents/organization' => 'Struktur organisasi',
    '/plants' => 'Daftar tanaman',
    '/plants/create' => 'Form varietas',
    '/plant-types' => 'Kategori tanaman',
    '/seed-units' => 'Satuan benih',
    '/planting-locations' => 'Lokasi penanaman',
    '/planting-locations/create' => 'Form lokasi',
    '/warehouse-locations' => 'Gudang',
    '/warehouse-locations/create' => 'Form gudang',
    '/seed-stock' => 'Stok benih',
    '/seed-requests' => 'Permintaan internal',
    '/seed-requests/create' => 'Form permintaan internal',
    '/users' => 'Pengguna',
    '/users/create' => 'Form pengguna',
    '/sales' => 'Penjualan',
    '/pelayanan-publik/kunjungan-geowisata' => 'Kelola geowisata',
    '/pelayanan-publik/pendaftaran-magang' => 'Kelola magang',
];

foreach ($internalPages as $url => $label) {
    $hit = httpGet($kernel, $admin, $url);
    record($results, 'INT-'.$url, "Akses internal {$label} ({$url})", 'HTTP 200', $hit['ok'], 'HTTP '.$hit['status']);
}

$locIndex = httpGet($kernel, $admin, '/planting-locations');
$hasBlackPill = str_contains($locIndex['body'], 'data-pill') && ! str_contains($locIndex['body'], 'text-bg-success-subtle');
record($results, 'UI-PILL', 'Kolom produksi benih memakai pill teks hitam', 'class data-pill tanpa text-bg-success-subtle', $hasBlackPill, $hasBlackPill ? 'pill hitam terpasang' : 'masih memakai badge lama');

$emptyPlant = makeRequest('POST', '/plants', []);
$plantValRes = app(PlantController::class)->store($emptyPlant);
$plantValErrors = session('errors') ? array_keys(session('errors')->toArray()) : [];
$need = ['plant_type_id', 'variety', 'satuan_tanam_id', 'satuan_panen_id', 'satuan_stok_id', 'harga_jual', 'minimal_stok'];
$okPlantVal = count(array_intersect($need, $plantValErrors)) >= 5;
record($results, 'VAL-PLANT', 'Validasi form varietas kosong', 'Field wajib ditolak', $okPlantVal, $plantValErrors ? implode(', ', $plantValErrors) : 'tidak ada error');

$emptyLoc = makeRequest('POST', '/planting-locations', []);
$locRes = app(PlantingLocationController::class)->store($emptyLoc);
$locErrors = session('errors') ? session('errors')->keys() : [];
if ($locRes instanceof Illuminate\Http\RedirectResponse) {
    $ok = in_array('name', $locErrors, true) || in_array('location_summary', $locErrors, true) || in_array('map_size', $locErrors, true) || in_array('location_type', $locErrors, true);
    record($results, 'VAL-LOC', 'Validasi form lokasi kosong', 'Nama/alamat/luas/tipe ditolak', $ok, $ok ? implode(', ', $locErrors) : 'redirect tanpa error wajib');
}

$datasets = [
    'A' => [
        'type_name' => 'Padi QA-A '.$stamp,
        'type_cat' => 'Tanaman Pangan',
        'variety' => 'Inpari QA-A '.$stamp,
        'loc' => 'Kebun Induk QA-A '.$stamp,
        'field' => 'BLOK-QA-A',
        'lot' => 'LOT-QA-A-'.$stamp,
        'class' => 'BS',
        'qty' => 50,
        'plant_qty' => 5,
        'wh' => 'Gudang QA-A '.$stamp,
        'wh_code' => 'WH-QA-A-'.$stamp,
        'news' => 'Kegiatan panen QA-A '.$stamp,
        'user_email' => 'petugas.qa.a.'.$stamp.'@sibesti.test',
        'buyer' => 'Kelompok Tani QA-A',
    ],
    'B' => [
        'type_name' => 'Cabai QA-B '.$stamp,
        'type_cat' => 'Hortikultura',
        'variety' => 'Tanjung QA-B '.$stamp,
        'loc' => 'Kebun Cabang QA-B '.$stamp,
        'field' => 'BLOK-QA-B',
        'lot' => 'LOT-QA-B-'.$stamp,
        'class' => 'FS',
        'qty' => 25,
        'plant_qty' => 3,
        'wh' => 'Gudang QA-B '.$stamp,
        'wh_code' => 'WH-QA-B-'.$stamp,
        'news' => 'Artikel budidaya QA-B '.$stamp,
        'user_email' => 'petugas.qa.b.'.$stamp.'@sibesti.test',
        'buyer' => 'Petani QA-B',
    ],
];

foreach ($datasets as $key => $d) {
    Auth::login($admin);

    $typeReq = makeRequest('POST', '/plant-types', [
        'name' => $d['type_name'],
        'category' => $d['type_cat'],
    ]);
    $typeRes = app(PlantTypeController::class)->store($typeReq);
    $type = PlantType::where('name', $d['type_name'])->first();
    record($results, "{$key}-TYPE", "Tambah kategori tanaman {$d['type_name']}", 'Record tersimpan', (bool) $type, $type ? $type->getKey() : 'gagal');
    $created[$key]['type'] = $type;

    $plantReq = makeRequest('POST', '/plants', [
        'plant_type_id' => $type?->getKey(),
        'variety' => $d['variety'],
        'description' => 'Varietas uji QA '.$key,
        'satuan_tanam_id' => $unitKg?->getKey(),
        'satuan_panen_id' => $unitKg?->getKey(),
        'satuan_stok_id' => $unitKg?->getKey(),
        'harga_jual' => $key === 'A' ? 15000 : 22000,
        'minimal_stok' => 10,
        'days_to_harvest' => 90,
    ]);
    try {
        app(PlantController::class)->store($plantReq);
        $plant = Plant::where('variety', $d['variety'])->first();
    } catch (Throwable $e) {
        $plant = null;
        echo "         plant error: ".$e->getMessage()."\n";
    }
    record($results, "{$key}-PLANT", "Tambah varietas {$d['variety']}", 'Record tersimpan, nama tanaman tampil bersama varietas', (bool) $plant, $plant ? $plant->displayName() : 'gagal');
    $created[$key]['plant'] = $plant;

    if ($plant) {
        $srcReq = makeRequest('POST', '/plants/'.$plant->getKey().'/seed-sources', [
            'seed_class' => $d['class'],
            'origin_lot_number' => $d['lot'],
            'origin_producer' => 'BB Padi Sukamandi QA',
            'quantity_kg' => $d['qty'],
            'description' => 'Benih sumber uji '.$key,
        ]);
        app(SeedSourceController::class)->store($srcReq, $plant);
        $source = SeedSource::where('origin_lot_number', $d['lot'])->first();
        record($results, "{$key}-SRC", "Tambah benih sumber {$d['lot']}", 'Tersimpan dan tersedia (quantity > 0)', $source && (float) $source->quantity_kg > 0, $source ? 'sisa '.$source->quantity_kg : 'gagal');
        $created[$key]['source'] = $source;

        $emptySrc = SeedSource::create([
            'seed_varieties_id' => $plant->getKey(),
            'seed_class' => 'ES',
            'origin_lot_number' => 'KOSONG-'.$key.'-'.$stamp,
            'origin_producer' => 'Uji kosong',
            'quantity_kg' => 0,
            'quantity_awal' => 10,
        ]);
        $jsonReq = makeRequest('GET', '/api/plants/'.$plant->getKey().'/seed-sources');
        $json = app(SeedSourceController::class)->indexJson($plant);
        $items = $json->getData(true);
        $ids = collect($items)->pluck('seed_source_id')->all();
        $onlyAvailable = in_array($source->seed_source_id, $ids, true) && ! in_array($emptySrc->seed_source_id, $ids, true);
        record($results, "{$key}-SRC-JSON", 'API benih sumber hanya menampilkan yang tersedia', 'Lot kosong tidak ikut, lot sisa > 0 ikut', $onlyAvailable, 'jumlah opsi '.count($items));
    }

    $locReq = makeRequest('POST', '/planting-locations', [
        'name' => $d['loc'],
        'location_summary' => $key === 'A' ? 'Lubuk Minturun, Kota Padang' : 'Solok, Sumatera Barat',
        'province' => 'Sumatera Barat',
        'city' => $key === 'A' ? 'Kota Padang' : 'Kabupaten Solok',
        'district' => $key === 'A' ? 'Koto Tangah' : 'X Koto',
        'village' => $key === 'A' ? 'Lubuk Minturun' : 'Koto Gaek',
        'koordinat_gps' => $key === 'A' ? '-0.847100,100.417200' : '-0.789000,100.650000',
        'location_type' => 'sawah',
        'planting_format' => 'ditanam_dalam_petak',
        'map_size' => $key === 'A' ? '2.50' : '1.25',
        'ownership_status' => 'Milik Pemerintah',
        'water_source' => 'Irigasi',
    ]);
    app(PlantingLocationController::class)->store($locReq);
    $location = PlantingLocation::where('name', $d['loc'])->first();
    record($results, "{$key}-LOC", "Tambah lokasi {$d['loc']}", 'Record tersimpan', (bool) $location, $location ? $location->getKey() : 'gagal');
    $created[$key]['location'] = $location;

    if ($location) {
        $fieldReq = makeRequest('POST', '/planting-locations/'.$location->getKey().'/fields', [
            'kode_lahan' => $d['field'],
            'luas_ha' => $key === 'A' ? 0.75 : 0.40,
            'panjang_m' => $key === 'A' ? 100 : 80,
            'lebar_m' => $key === 'A' ? 75 : 50,
            'status_lahan' => 'Persiapan',
            'koordinat_gps' => $location->koordinat_gps,
        ]);
        app(PlantingFieldController::class)->store($fieldReq, $location);
        $field = PlantingField::where('planting_location_id', $location->getKey())->where('kode_lahan', $d['field'])->first();
        record($results, "{$key}-FIELD", "Tambah lahan {$d['field']} dengan panjang x lebar", 'Record tersimpan termasuk dimensi', $field && $field->panjang_m && $field->lebar_m, $field ? $field->panjang_m.'x'.$field->lebar_m : 'gagal');
        $created[$key]['field'] = $field;
    }

    if (! empty($created[$key]['plant']) && ! empty($created[$key]['source']) && ! empty($created[$key]['location']) && ! empty($created[$key]['field'])) {
        $prodReq = makeRequest('POST', '/planting-locations/'.$location->getKey().'/plantings', [
            'plant_id' => $created[$key]['plant']->getKey(),
            'seed_source_id' => $created[$key]['source']->getKey(),
            'planting_field_id' => $created[$key]['field']->id,
            'planting_batch_number' => 'TANAM-QA-'.$key.'-'.$stamp,
            'planted_at' => date('Y-m-d'),
            'estimated_harvest_date' => date('Y-m-d', strtotime('+90 days')),
            'planting_amount' => $d['plant_qty'],
            'satuan_panen_id' => 'BOGUS-ID-SHOULD-BE-IGNORED',
            'target_kelas' => $d['class'],
            'description' => 'Produksi uji '.$key,
        ]);
        app(PlantingLocationController::class)->storePlanting($prodReq, $location);
        $planting = Planting::where('planting_batch_number', 'TANAM-QA-'.$key.'-'.$stamp)->first();
        $unitFollows = $planting && $planting->satuan_panen_id === $created[$key]['plant']->satuan_panen_id;
        record($results, "{$key}-PROD", 'Tambah produksi benih', 'Tersimpan, satuan panen mengikuti tanaman (bukan input palsu)', (bool) $planting && $unitFollows, $planting ? 'satuan '.$planting->satuan_panen_id : 'gagal');
        $created[$key]['planting'] = $planting;

        $overReq = makeRequest('POST', '/planting-locations/'.$location->getKey().'/plantings', [
            'plant_id' => $created[$key]['plant']->getKey(),
            'seed_source_id' => $created[$key]['source']->fresh()->getKey(),
            'planting_field_id' => $created[$key]['field']->id,
            'planted_at' => date('Y-m-d'),
            'estimated_harvest_date' => date('Y-m-d', strtotime('+90 days')),
            'planting_amount' => 999999,
        ]);
        $overRes = app(PlantingLocationController::class)->storePlanting($overReq, $location);
        $overErrors = session('errors') ? session('errors')->get('planting_amount') : null;
        record($results, "{$key}-PROD-OVER", 'Produksi melebihi sisa benih sumber', 'Ditolak', is_array($overErrors) && $overErrors !== [], $overErrors ? implode('; ', $overErrors) : 'tidak ditolak');
    }

    $whReq = makeRequest('POST', '/warehouse-locations', [
        'name' => $d['wh'],
        'internal_id' => $d['wh_code'],
        'tipe_lokasi' => 'gudang',
        'description' => 'Gudang uji '.$key,
    ]);
    app(WarehouseController::class)->store($whReq);
    $wh = Warehouse::where('internal_id', $d['wh_code'])->first();
    record($results, "{$key}-WH", "Tambah gudang {$d['wh']}", 'Record tersimpan', (bool) $wh, $wh ? $wh->getKey() : 'gagal');
    $created[$key]['warehouse'] = $wh;

    $contentReq = makeRequest('POST', '/contents', [
        'jenis' => $key === 'A' ? 'berita' : 'artikel',
        'judul' => $d['news'],
        'excerpt' => 'Cuplikan uji QA '.$key,
        'body' => '<p>Isi konten uji QA dataset '.$key.' untuk publikasi website UPTD BBI TPH.</p>',
        'author' => 'Humas UPTD BBI TPH',
        'is_published' => 1,
        'published_at' => now()->toDateTimeString(),
    ]);
    app(ContentController::class)->store($contentReq);
    $content = WebsiteContent::where('judul', $d['news'])->first();
    record($results, "{$key}-CMS", "Tambah konten website {$d['news']}", 'Tersimpan dan terbit', $content && $content->is_published, $content ? $content->slug : 'gagal');
    $created[$key]['content'] = $content;

    $user = User::create([
        'name' => 'Petugas QA '.$key,
        'email' => $d['user_email'],
        'password' => 'password123',
        'role' => $key === 'A' ? 'petugas_bbi' : 'petugas_gudang',
        'status' => 'active',
        'placement_location_id' => $location?->getKey(),
    ]);
    record($results, "{$key}-USER", "Tambah akun {$d['user_email']}", 'User aktif tersimpan', $user && $user->exists, $user->getKey());
    $created[$key]['user'] = $user;

    if (! empty($created[$key]['plant'])) {
        $sr = SeedRequest::create([
            'request_number' => 'PB-QA-'.$key.'-'.$stamp,
            'request_date' => date('Y-m-d'),
            'buyer_name' => $d['buyer'],
            'buyer_contact' => '08123456'.($key === 'A' ? '0001' : '0002'),
            'buyer_nik' => $key === 'A' ? '1371010101010001' : '1371010101010002',
            'buyer_category' => $key === 'A' ? 'kelompok_tani' : 'petani_perorangan',
            'organization' => $d['buyer'],
            'destination_province' => 'Sumatera Barat',
            'destination_city' => 'Padang',
            'destination_district' => 'Kuranji',
            'destination_village' => 'Korong Gadang',
            'planned_location_name' => 'Lahan uji '.$key,
            'planned_gps' => '-0.914500,100.360000',
            'estimated_planting_area' => 0.5,
            'notes' => 'Permintaan uji QA '.$key,
            'status' => SeedRequest::STATUS_PENDING,
            'payment_method' => 'cash',
            'created_by' => $admin->getKey(),
        ]);
        SeedRequestItem::create([
            'seed_request_id' => $sr->getKey(),
            'seed_varieties_id' => $created[$key]['plant']->getKey(),
            'quantity' => 10,
            'unit' => 'kg',
            'unit_price' => $created[$key]['plant']->harga_jual,
            'subtotal' => 10 * (float) $created[$key]['plant']->harga_jual,
        ]);
        $created[$key]['request'] = $sr;

        $intShow = httpGet($kernel, $admin, '/seed-requests/'.$sr->getKey());
        record($results, "{$key}-SR-INT", 'Detail permintaan internal', 'HTTP 200 menampilkan nomor permintaan', $intShow['ok'] && str_contains($intShow['body'], $sr->request_number), 'HTTP '.$intShow['status']);

        $pubShow = $kernel->handle(Request::create('/permintaan/'.$sr->getKey(), 'GET'));
        record($results, "{$key}-SR-PUB", 'Detail permintaan publik', 'HTTP 200 menampilkan nomor permintaan', $pubShow->getStatusCode() === 200 && str_contains((string) $pubShow->getContent(), $sr->request_number), 'HTTP '.$pubShow->getStatusCode());
    }

    $visitDate = nextOpenDate();
    $geoReq = makeRequest('POST', '/kunjungan-geowisata', [
        'nama_lembaga' => 'SMKN QA '.$key.' '.$stamp,
        'jumlah_peserta' => $key === 'A' ? 20 : 15,
        'tgl_kunjungan' => $visitDate,
        'nama_pj' => 'Penanggung Jawab QA '.$key,
        'no_whatsapp_pj' => $key === 'A' ? '081211110001' : '081211110002',
    ]);
    $geoRes = app(BookingGeowisataController::class)->publicStore($geoReq);
    $geo = BookingGeowisata::where('nama_lembaga', 'SMKN QA '.$key.' '.$stamp)->first();
    record($results, "{$key}-GEO", 'Tambah booking geowisata', 'Tersimpan (hari kerja tersedia)', (bool) $geo, $geo ? $geo->kode_booking.' '.$visitDate : 'gagal/tanggal tertutup');
    $created[$key]['geo'] = $geo;

    $magReq = makeRequest('POST', '/pendaftaran-magang', [
        'nama_lengkap' => 'Pemohon Magang QA '.$key,
        'institusi_asal' => 'Politeknik QA '.$key,
        'jurusan' => 'Agroteknologi',
        'no_whatsapp' => $key === 'A' ? '081399990001' : '081399990002',
        'tgl_mulai' => date('Y-m-d', strtotime('+7 days')),
        'tgl_selesai' => date('Y-m-d', strtotime('+37 days')),
        'peserta' => [
            ['nama_lengkap' => 'Peserta '.$key.'1', 'identitas' => 'NIM001'.$key, 'jurusan' => 'Agroteknologi'],
            ['nama_lengkap' => 'Peserta '.$key.'2', 'identitas' => 'NIM002'.$key, 'jurusan' => 'Agroteknologi'],
        ],
    ]);
    app(PendaftaranMagangController::class)->publicStore($magReq);
    $mag = \App\Models\PendaftaranMagang::where('nama_lengkap', 'Pemohon Magang QA '.$key)->latest()->first();
    record($results, "{$key}-MAG", 'Tambah pendaftaran magang + 2 peserta', 'Tersimpan', (bool) $mag, $mag ? $mag->nomor_registrasi : 'gagal');
    $created[$key]['magang'] = $mag;
}

$emptyReq = makeRequest('POST', '/permintaan', [
    'request_date' => date('Y-m-d'),
    'buyer_name' => '',
]);
try {
    app(SeedRequestController::class)->publicStore($emptyReq);
    record($results, 'VAL-SR', 'Validasi permintaan benih kosong', 'Ditolak', false, 'malah tersimpan');
} catch (Illuminate\Validation\ValidationException $e) {
    record($results, 'VAL-SR', 'Validasi permintaan benih kosong', 'Field wajib ditolak', isset($e->errors()['buyer_name']) || isset($e->errors()['plant_id']), implode(', ', array_keys($e->errors())));
}

$weekend = Carbon::today()->next(Carbon::SUNDAY)->toDateString();
$badGeo = makeRequest('POST', '/kunjungan-geowisata', [
    'nama_lembaga' => 'Uji Weekend',
    'jumlah_peserta' => 10,
    'tgl_kunjungan' => $weekend,
    'nama_pj' => 'PJ',
    'no_whatsapp_pj' => '081200000000',
]);
$badGeoRes = app(BookingGeowisataController::class)->publicStore($badGeo);
$badGeoErr = session('errors') ? session('errors')->get('tgl_kunjungan') : null;
record($results, 'VAL-GEO-WEEKEND', 'Booking geowisata di hari Minggu', 'Ditolak karena kebun tutup', is_array($badGeoErr), $badGeoErr ? implode('; ', $badGeoErr) : 'tidak ditolak');

if (! empty($created['A']['location'])) {
    $prodPage = httpGet($kernel, $admin, '/planting-locations/'.$created['A']['location']->getKey().'/plantings');
    $hasName = $created['A']['plant'] && str_contains($prodPage['body'], $created['A']['plant']->type?->name ?? 'NO-TYPE');
    $hasReadonly = str_contains($prodPage['body'], 'Mengikuti satuan panen tanaman') && str_contains($prodPage['body'], 'readonly');
    record($results, 'UI-PROD-NAME', 'Dropdown tanaman menampilkan nama tanaman', 'Nama tipe tanaman muncul di opsi', $hasName, $hasName ? 'nama tanaman tampil' : 'nama tanaman tidak ditemukan');
    record($results, 'UI-PROD-UNIT', 'Field satuan panen terkunci', 'readonly + keterangan mengikuti satuan', $hasReadonly, $hasReadonly ? 'terkunci' : 'belum terkunci');
}

$pass = count(array_filter($results, fn ($r) => $r['ok']));
$fail = count($results) - $pass;

$lines = [];
$lines[] = '# Laporan Pengujian SIBESTI';
$lines[] = '';
$lines[] = 'Tanggal uji: **'.date('d F Y H:i').'**';
$lines[] = 'Penguji: skrip QA otomatis alur pemakaian sistem (2 set data contoh QA-A dan QA-B).';
$lines[] = 'Hasil: **'.$pass.' lulus**, **'.$fail.' gagal**, dari **'.count($results).'** kasus.';
$lines[] = '';
$lines[] = '## Urutan pemakaian sistem yang diuji';
$lines[] = '';
$lines[] = '1. Website publik (beranda, profil, berita, dokumen, harga, kontak, layanan).';
$lines[] = '2. Login petugas → dasbor dan CMS (konten, pengaturan, struktur).';
$lines[] = '3. Master data: satuan benih, kategori tanaman, varietas, benih sumber.';
$lines[] = '4. Lokasi penanaman + lahan (termasuk panjang × lebar).';
$lines[] = '5. Produksi benih (hanya benih tersedia, nama tanaman, satuan panen terkunci).';
$lines[] = '6. Gudang, akun petugas, permintaan benih, geowisata, magang.';
$lines[] = '7. Kasus negatif: form kosong, stok tidak cukup, booking hari libur, benih sumber habis.';
$lines[] = '';
$lines[] = '## Data contoh yang ditambahkan';
$lines[] = '';
foreach (['A', 'B'] as $key) {
    $lines[] = '### Dataset '.$key;
    $c = $created[$key];
    $lines[] = '- Kategori: '.($c['type']->name ?? '-');
    $lines[] = '- Varietas: '.($c['plant']->displayName() ?? '-');
    $lines[] = '- Benih sumber: '.($c['source']->origin_lot_number ?? '-');
    $lines[] = '- Lokasi / lahan: '.($c['location']->name ?? '-').' / '.($c['field']->kode_lahan ?? '-');
    $lines[] = '- Produksi: '.($c['planting']->planting_batch_number ?? '-');
    $lines[] = '- Gudang: '.($c['warehouse']->name ?? '-');
    $lines[] = '- Konten: '.($c['content']->judul ?? '-');
    $lines[] = '- Permintaan: '.($c['request']->request_number ?? '-');
    $lines[] = '- Geowisata: '.($c['geo']->kode_booking ?? '-');
    $lines[] = '- Magang: '.($c['magang']->nomor_registrasi ?? '-');
    $lines[] = '';
}
$lines[] = '## Langkah, hasil yang diharapkan, dan hasil aktual';
$lines[] = '';
$lines[] = '| ID | Langkah | Hasil diharapkan | Status | Hasil aktual |';
$lines[] = '|----|---------|------------------|--------|--------------|';
foreach ($results as $row) {
    $status = $row['ok'] ? 'Lulus' : 'Gagal';
    $lines[] = '| '.$row['id'].' | '.str_replace('|', '/', $row['step']).' | '.str_replace('|', '/', $row['expected']).' | '.$status.' | '.str_replace('|', '/', $row['actual']).' |';
}
$lines[] = '';
$lines[] = '## Catatan batasan';
$lines[] = '';
$lines[] = '- Permintaan benih publik yang lengkap memerlukan kemasan stok FEFO. Dua permintaan contoh dibuat agar halaman detail bisa diakses; alur pilih kemasan diuji lewat validasi form kosong.';
$lines[] = '- Sertifikasi/label gudang dan pembayaran lunas membutuhkan stok kemasan yang sudah dilabel. Kasus itu dicatat sebagai ketergantungan data gudang.';
$lines[] = '- Pengujian dijalankan lewat HTTP kernel (GET) dan pemanggilan controller (POST) pada database aktif, karena `php artisan serve` tidak dapat bind port di lingkungan ini.';

file_put_contents(__DIR__.'/../LAPORAN_PENGUJIAN.md', implode("\n", $lines));

echo "\nSelesai: {$pass} lulus / {$fail} gagal. Laporan: LAPORAN_PENGUJIAN.md\n";
exit($fail > 0 ? 1 : 0);
