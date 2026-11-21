<?php

namespace App\Http\Controllers;

use App\Models\PengajuanSurat;
use App\Models\PengaturanSurat;
use Illuminate\Contracts\View\View;

class SuratController extends Controller
{
    public function cetak(PengajuanSurat $surat): View
    {
        $surat->loadMissing(['warga', 'penyetuju']);
        abort_unless($surat->status === PengajuanSurat::STATUS_DISETUJUI, 404);

        $user = auth()->user();
        if ($user->isWarga() && (int) $surat->warga_id !== (int) $user->warga_id) {
            abort(403, 'Anda tidak memiliki hak akses untuk mencetak surat ini.');
        }

        $pengaturan = PengaturanSurat::query()->find(1) ?? new PengaturanSurat;

        return view('surat.cetak', compact('surat', 'pengaturan'));
    }
}
