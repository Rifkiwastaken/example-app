<?php

namespace App\Http\Controllers;

use App\Models\BookingGeowisata;
use App\Support\IndonesiaHoliday;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BookingGeowisataController extends Controller
{
    public function publicIndex(Request $request)
    {
        $year = (int) $request->get('year', now()->year);
        $month = (int) $request->get('month', now()->month);
        $calendar = $this->calendarPayload($year, $month);
        $pengajuan = BookingGeowisata::query()->orderByDesc('created_at')->paginate(15)->withQueryString();

        return view('landing.geowisata.index', compact('calendar', 'pengajuan', 'year', 'month'));
    }

    public function publicCreate(Request $request)
    {
        $year = (int) $request->get('year', now()->year);
        $month = (int) $request->get('month', now()->month);
        $calendar = $this->calendarPayload($year, $month);

        return view('landing.geowisata.create', compact('calendar', 'year', 'month'));
    }

    public function publicStore(Request $request)
    {
        $data = $request->validate([
            'nama_lembaga' => 'required|string|max:255',
            'jumlah_peserta' => 'required|integer|min:1|max:500',
            'tgl_kunjungan' => 'required|date|after_or_equal:today',
            'nama_pj' => 'required|string|max:150',
            'no_whatsapp_pj' => 'required|string|max:20',
        ], [
            'nama_lembaga.required' => 'Nama lembaga wajib diisi.',
            'jumlah_peserta.required' => 'Jumlah peserta wajib diisi.',
            'tgl_kunjungan.required' => 'Tanggal kunjungan wajib dipilih pada kalender.',
            'nama_pj.required' => 'Nama penanggung jawab wajib diisi.',
            'no_whatsapp_pj.required' => 'Nomor WhatsApp penanggung jawab wajib diisi.',
        ]);

        if (! BookingGeowisata::isDateAvailable($data['tgl_kunjungan'], (int) $data['jumlah_peserta'])) {
            return back()->withErrors([
                'tgl_kunjungan' => 'Tanggal tidak tersedia (weekend, libur nasional, atau daya tampung kebun induk penuh).',
            ])->withInput();
        }

        $booking = BookingGeowisata::create([
            'kode_booking' => BookingGeowisata::generateKode(),
            'nama_lembaga' => $data['nama_lembaga'],
            'jumlah_peserta' => $data['jumlah_peserta'],
            'tgl_kunjungan' => $data['tgl_kunjungan'],
            'nama_pj' => $data['nama_pj'],
            'no_whatsapp_pj' => $data['no_whatsapp_pj'],
            'status_kedatangan' => BookingGeowisata::STATUS_BOOKED,
            'status_pengajuan' => BookingGeowisata::PENGAJUAN_MENUNGGU,
        ]);

        return redirect()->route('public.geowisata.show', $booking)
            ->with('success', 'Pengajuan kunjungan terkirim. Kode booking: '.$booking->kode_booking);
    }

    public function publicShow(BookingGeowisata $booking)
    {
        return view('landing.geowisata.show', compact('booking'));
    }

    public function calendarJson(Request $request)
    {
        $year = (int) $request->get('year', now()->year);
        $month = (int) $request->get('month', now()->month);

        return response()->json($this->calendarPayload($year, $month));
    }

    public function index()
    {
        $pengajuan = BookingGeowisata::query()->orderByDesc('created_at')->paginate(20);

        return view('pelayanan.geowisata.index', compact('pengajuan'));
    }

    public function show(BookingGeowisata $booking)
    {
        $booking->load('processor');

        return view('pelayanan.geowisata.show', compact('booking'));
    }

    public function approve(BookingGeowisata $booking)
    {
        if ($booking->status_pengajuan === BookingGeowisata::PENGAJUAN_DITOLAK) {
            return back()->with('error', 'Pengajuan yang ditolak tidak dapat disetujui.');
        }

        $booking->update([
            'status_pengajuan' => BookingGeowisata::PENGAJUAN_DISETUJUI,
            'status_kedatangan' => BookingGeowisata::STATUS_BOOKED,
            'alasan_ditolak' => null,
            'processed_by' => Auth::user()->user_id ?? Auth::id(),
            'approved_at' => now(),
        ]);

        return back()->with('success', 'Kunjungan disetujui. Status publik diperbarui.');
    }

    public function reject(Request $request, BookingGeowisata $booking)
    {
        $data = $request->validate([
            'alasan_ditolak' => 'required|string|max:2000',
        ], [
            'alasan_ditolak.required' => 'Alasan penolakan wajib diisi.',
        ]);

        $booking->update([
            'status_pengajuan' => BookingGeowisata::PENGAJUAN_DITOLAK,
            'status_kedatangan' => BookingGeowisata::STATUS_BATAL,
            'alasan_ditolak' => $data['alasan_ditolak'],
            'processed_by' => Auth::user()->user_id ?? Auth::id(),
        ]);

        return back()->with('success', 'Pengajuan ditolak. Alasan tampil di halaman publik.');
    }

    public function markHadir(BookingGeowisata $booking)
    {
        if ($booking->status_pengajuan !== BookingGeowisata::PENGAJUAN_DISETUJUI) {
            return back()->with('error', 'Hanya kunjungan yang disetujui yang dapat ditandai hadir.');
        }

        $booking->update([
            'status_kedatangan' => BookingGeowisata::STATUS_HADIR,
            'processed_by' => Auth::user()->user_id ?? Auth::id(),
        ]);

        return back()->with('success', 'Status kedatangan: Hadir.');
    }

    protected function calendarPayload(int $year, int $month): array
    {
        $start = Carbon::create($year, $month, 1)->startOfMonth();
        $end = $start->copy()->endOfMonth();
        $bookings = BookingGeowisata::query()
            ->whereBetween('tgl_kunjungan', [$start->toDateString(), $end->toDateString()])
            ->where('status_kedatangan', '!=', BookingGeowisata::STATUS_BATAL)
            ->where('status_pengajuan', '!=', BookingGeowisata::PENGAJUAN_DITOLAK)
            ->orderBy('tgl_kunjungan')
            ->get();

        $byDate = $bookings->groupBy(fn ($item) => $item->tgl_kunjungan->toDateString());
        $days = [];
        for ($day = $start->copy(); $day->lte($end); $day->addDay()) {
            $key = $day->toDateString();
            $list = $byDate->get($key, collect());
            $weekend = $day->isWeekend();
            $holiday = IndonesiaHoliday::isHoliday($day);
            $booked = $list->isNotEmpty();
            $days[] = [
                'date' => $key,
                'day' => $day->day,
                'weekday' => $day->dayOfWeek,
                'weekend' => $weekend,
                'holiday' => $holiday,
                'booked' => $booked,
                'unavailable' => $weekend || $holiday || $booked || $day->lt(now()->startOfDay()),
                'bookings' => $list->map(fn ($item) => [
                    'nama_lembaga' => $item->nama_lembaga,
                    'status_kedatangan' => $item->status_kedatangan,
                    'status_pengajuan' => $item->pengajuanLabel(),
                    'jumlah_peserta' => $item->jumlah_peserta,
                ])->values(),
            ];
        }

        return [
            'year' => $year,
            'month' => $month,
            'label' => $start->locale('id')->translatedFormat('F Y'),
            'start_weekday' => $start->dayOfWeek,
            'days' => $days,
            'visits' => $bookings->map(fn ($item) => [
                'id' => $item->id,
                'nama_lembaga' => $item->nama_lembaga,
                'tgl_kunjungan' => $item->tgl_kunjungan->format('d M Y'),
                'status_kedatangan' => $item->status_kedatangan,
                'status_pengajuan' => $item->pengajuanLabel(),
            ])->values(),
        ];
    }
}
