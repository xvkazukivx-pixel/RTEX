<?php

namespace Database\Seeders;

use App\Models\Warga;
use Illuminate\Database\Seeder;

class DemoHouseholdMembersSeeder extends Seeder
{
    public function run(): void
    {
        $membersByHouse = [
            'C-01' => [
                ['nama' => 'Siti Ahmad', 'nik' => '3299000000000001'],
                ['nama' => 'Rafi Ahmad', 'nik' => '3299000000000002'],
            ],
            'C-02' => [
                ['nama' => 'Dewi Ratna', 'nik' => '3299000000000003'],
                ['nama' => 'Raka Ratna', 'nik' => '3299000000000004'],
            ],
            'C-03' => [
                ['nama' => 'Lestari Dimas', 'nik' => '3299000000000005'],
                ['nama' => 'Ardi Dimas', 'nik' => '3299000000000006'],
            ],
            'C-04' => [
                ['nama' => 'Nadia Maya', 'nik' => '3299000000000007'],
                ['nama' => 'Bima Maya', 'nik' => '3299000000000008'],
            ],
            'C-05' => [
                ['nama' => 'Rina Fajar', 'nik' => '3299000000000009'],
                ['nama' => 'Doni Fajar', 'nik' => '3299000000000010'],
            ],
            'C-06' => [
                ['nama' => 'Maya Lina', 'nik' => '3299000000000011'],
                ['nama' => 'Rizky Lina', 'nik' => '3299000000000012'],
            ],
            'C-07' => [
                ['nama' => 'Ayu Reza', 'nik' => '3299000000000013'],
                ['nama' => 'Fikri Reza', 'nik' => '3299000000000014'],
            ],
            'C-08' => [
                ['nama' => 'Putri Ayu', 'nik' => '3299000000000015'],
                ['nama' => 'Daffa Ayu', 'nik' => '3299000000000016'],
            ],
            'B-12' => [
                ['nama' => 'Sari Hafis', 'nik' => '3299000000000017'],
                ['nama' => 'Faris Hafis', 'nik' => '3299000000000018'],
            ],
            'B-14' => [
                ['nama' => 'Nisa Sari', 'nik' => '3299000000000019'],
                ['nama' => 'Rafi Sari', 'nik' => '3299000000000020'],
            ],
        ];

        foreach ($membersByHouse as $noRumah => $members) {
            $kepala = Warga::where('no_rumah', $noRumah)
                ->whereNull('kepala_keluarga_id')
                ->firstOrFail();

            foreach ($members as $member) {
                $nikHash = Warga::nikHash($member['nik']);
                $warga = Warga::firstOrNew(['nik_hash' => $nikHash]);
                $warga->fill([
                    ...$member,
                    'no_rumah' => $kepala->no_rumah,
                    'alamat' => $kepala->alamat,
                    'no_hp' => null,
                    'status_hunian' => $kepala->status_hunian,
                    'status_aktif' => $kepala->status_aktif,
                    'status_pendaftaran' => 'Disetujui',
                    'kepala_keluarga_id' => $kepala->id,
                ]);
                $warga->nik_hash = $nikHash;
                $warga->save();
            }
        }
    }
}
