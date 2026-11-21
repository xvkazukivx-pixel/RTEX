<?php

namespace Database\Seeders;

use App\Models\Tagihan;
use App\Models\Transaksi;
use App\Models\User;
use App\Models\Warga;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DemoFinanceDataSeeder extends Seeder
{
    public function run(): void
    {
        $heads = [
            ['nama' => 'Bapak Ahmad', 'no_rumah' => 'C-01', 'nik' => '3201000000000001'],
            ['nama' => 'Ibu Ratna', 'no_rumah' => 'C-02', 'nik' => '3201000000000002'],
            ['nama' => 'Bapak Dimas', 'no_rumah' => 'C-03', 'nik' => '3201000000000003'],
            ['nama' => 'Ibu Maya', 'no_rumah' => 'C-04', 'nik' => '3201000000000004'],
            ['nama' => 'Bapak Fajar', 'no_rumah' => 'C-05', 'nik' => '3201000000000005'],
            ['nama' => 'Ibu Lina', 'no_rumah' => 'C-06', 'nik' => '3201000000000006'],
            ['nama' => 'Bapak Reza', 'no_rumah' => 'C-07', 'nik' => '3201000000000007'],
            ['nama' => 'Ibu Ayu', 'no_rumah' => 'C-08', 'nik' => '3201000000000008'],
            ['nama' => 'Bapak Hafis', 'no_rumah' => 'B-12', 'nik' => '3201000000000012'],
            ['nama' => 'Ibu Sari', 'no_rumah' => 'B-14', 'nik' => '3201000000000014'],
        ];

        $savedHeads = [];
        foreach ($heads as $index => $head) {
            $warga = Warga::updateOrCreate(
                ['no_rumah' => $head['no_rumah']],
                [
                    'nama' => $head['nama'],
                    'nik' => $head['nik'],
                    'alamat' => 'Jl. Mawar '.($index + 1),
                    'no_hp' => '0812'.str_pad((string) (800000 + $index), 6, '0', STR_PAD_LEFT),
                    'status_hunian' => $index % 3 === 0 ? 'Kontrak' : 'Tetap',
                    'status_aktif' => true,
                    'status_pendaftaran' => 'Disetujui',
                    'kepala_keluarga_id' => null,
                ]
            );
            $warga->nik_hash = Warga::nikHash($head['nik']);
            $warga->save();
            $savedHeads[] = $warga;
        }

        $bendahara = User::where('role', 'bendahara')->first()
            ?? User::where('role', 'admin')->first();

        $periods = [
            '2026-06' => 62,
            '2026-07' => 68,
            '2026-08' => 74,
            '2026-09' => 81,
            '2026-10' => 88,
            '2026-11' => 92,
        ];

        DB::transaction(function () use ($savedHeads, $bendahara, $periods) {
            foreach ($periods as $period => $paidPercent) {
                $paidCount = (int) round((count($savedHeads) * $paidPercent) / 100);

                foreach ($savedHeads as $index => $head) {
                    $isPaid = $index < $paidCount;
                    $tagihan = Tagihan::updateOrCreate(
                        ['warga_id' => $head->id, 'periode' => $period],
                        [
                            'total_tagihan' => 50000,
                            'status' => $isPaid ? 'Lunas' : 'Belum Bayar',
                        ]
                    );

                    $tagihan->transaksi()->delete();

                    if ($isPaid) {
                        $day = 3 + (($index + (int) substr($period, 5, 2)) % 20);
                        $paidAt = $period.'-'.str_pad((string) $day, 2, '0', STR_PAD_LEFT).' 09:00:00';
                        Transaksi::create([
                            'tagihan_id' => $tagihan->id,
                            'warga_id' => $head->id,
                            'jumlah_bayar' => 50000,
                            'metode' => $index % 3 === 0 ? 'Tunai' : 'Transfer',
                            'bukti_transfer' => $index % 3 === 0 ? null : 'demo-'.$period.'-'.$head->id.'.jpg',
                            'keterangan' => 'Data contoh iuran periode '.$period,
                            'tanggal_bayar' => $paidAt,
                            'status_konfirmasi' => 'Diterima',
                            'dikonfirmasi_oleh' => $bendahara?->id,
                            'tanggal_konfirmasi' => $paidAt,
                            'catatan_admin' => 'Data demo',
                        ]);
                    }
                }
            }
        });
    }
}
