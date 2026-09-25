<?php

namespace App\Http\Controllers;

use App\Models\PendaftaranMagang;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PendaftaranMagangController extends Controller
{
    public function publicIndex()
    {
        $berjalan = PendaftaranMagang::query()
            ->where('status_magang', PendaftaranMagang::STATUS_DITERIMA)
            ->whereDate('tgl_mulai', '<=', now())
            ->whereDate('tgl_selesai', '>=', now())
            ->orderBy('tgl_mulai')
            ->get();

        $pengajuan = PendaftaranMagang::query()->orderByDesc('created_at')->paginate(15);

        return view('landing.magang.index', compact('berjalan', 'pengajuan'));
    }

    public function publicCreate()
    {
        return view('landing.magang.create');
    }

    public function publicStore(Request $request)
    {
        $data = $request->validate([
            'nama_lengkap' => 'required|string|max:150',
            'institusi_asal' => 'required|string|max:150',
            'jabatan' => 'required|string|max:100',
            'no_whatsapp' => 'required|string|max:20',
            'tgl_mulai' => 'required|date|after_or_equal:today',
            'tgl_selesai' => 'required|date|after_or_equal:tgl_mulai',
            'peserta' => 'required|array|min:1',
            'peserta.*.nama_lengkap' => 'required|string|max:150',
            'peserta.*.identitas' => 'nullable|string|max:50',
            'peserta.*.jabatan' => 'nullable|string|max:100',
        ], [
            'jabatan.required' => 'Jabatan wajib diisi.',
            'peserta.required' => 'Daftar peserta magang wajib diisi.',
            'peserta.*.nama_lengkap.required' => 'Nama setiap peserta magang wajib diisi.',
        ]);

        $peserta = collect($data['peserta'])
            ->filter(fn ($row) => trim((string) ($row['nama_lengkap'] ?? '')) !== '')
            ->values()
            ->all();

        $pendaftaran = PendaftaranMagang::create([
            'nomor_registrasi' => PendaftaranMagang::generateNomor(),
            'nama_lengkap' => $data['nama_lengkap'],
            'institusi_asal' => $data['institusi_asal'],
            'jabatan' => $data['jabatan'],
            'no_whatsapp' => $data['no_whatsapp'],
            'tgl_mulai' => $data['tgl_mulai'],
            'tgl_selesai' => $data['tgl_selesai'],
            'peserta' => $peserta,
            'status_magang' => PendaftaranMagang::STATUS_MENUNGGU,
        ]);

        return redirect()->route('public.magang.show', $pendaftaran)
            ->with('success', 'Pendaftaran magang terkirim. Nomor: '.$pendaftaran->nomor_registrasi);
    }

    public function publicShow(PendaftaranMagang $pendaftaran)
    {
        return view('landing.magang.show', compact('pendaftaran'));
    }

    public function index()
    {
        $pengajuan = PendaftaranMagang::query()->orderByDesc('created_at')->paginate(20);

        return view('pelayanan.magang.index', compact('pengajuan'));
    }

    public function show(PendaftaranMagang $pendaftaran)
    {
        $pendaftaran->load('processor');

        return view('pelayanan.magang.show', compact('pendaftaran'));
    }

    public function approve(PendaftaranMagang $pendaftaran)
    {
        if ($pendaftaran->status_magang === PendaftaranMagang::STATUS_DITOLAK) {
            return back()->with('error', 'Pendaftaran yang ditolak tidak dapat diterima.');
        }

        $pendaftaran->update([
            'status_magang' => PendaftaranMagang::STATUS_DITERIMA,
            'alasan_ditolak' => null,
            'processed_by' => Auth::user()->user_id ?? Auth::id(),
            'approved_at' => now(),
        ]);

        return back()->with('success', 'Pendaftaran magang diterima. Status publik diperbarui.');
    }

    public function reject(Request $request, PendaftaranMagang $pendaftaran)
    {
        $data = $request->validate([
            'alasan_ditolak' => 'required|string|max:2000',
        ]);

        $pendaftaran->update([
            'status_magang' => PendaftaranMagang::STATUS_DITOLAK,
            'alasan_ditolak' => $data['alasan_ditolak'],
            'processed_by' => Auth::user()->user_id ?? Auth::id(),
        ]);

        return back()->with('success', 'Pendaftaran ditolak. Alasan tampil di halaman publik.');
    }
}
