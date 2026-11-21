<?php

if (!function_exists('format_rupiah')) {
    function format_rupiah($angka): string
    {
        return 'Rp' . number_format((float) $angka, 0, ',', '.');
    }
}

if (!function_exists('format_tanggal')) {
    function format_tanggal($tanggal, bool $withTime = false): string
    {
        if (empty($tanggal)) return '-';
        $ts = is_numeric($tanggal) ? $tanggal : strtotime($tanggal);
        if (!$ts) return '-';
        $bulan = [
            1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr', 5 => 'Mei', 6 => 'Jun',
            7 => 'Jul', 8 => 'Agu', 9 => 'Sep', 10 => 'Okt', 11 => 'Nov', 12 => 'Des'
        ];
        $hasil = date('d', $ts) . ' ' . $bulan[(int) date('n', $ts)] . ' ' . date('Y', $ts);
        if ($withTime) {
            $hasil .= ', ' . date('H:i', $ts);
        }
        return $hasil;
    }
}

if (!function_exists('periode_label')) {
    function periode_label($periode): string
    {
        if (empty($periode) || !str_contains($periode, '-')) {
            return (string) $periode;
        }
        $namaBulan = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 5 => 'Mei', 6 => 'Juni',
            7 => 'Juli', 8 => 'Agustus', 9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];
        [$y, $m] = explode('-', $periode);
        return ($namaBulan[(int) $m] ?? $m) . ' ' . $y;
    }
}

if (!function_exists('periode_sekarang')) {
    function periode_sekarang(): string
    {
        return date('Y-m');
    }
}

if (!function_exists('badge_status')) {
    function badge_status($status): string
    {
        $map = [
            'Lunas'                => 'success',
            'Belum Bayar'          => 'secondary',
            'Menunggu Konfirmasi'  => 'warning',
            'Menunggu'             => 'warning',
            'Diterima'             => 'success',
            'Ditolak'              => 'danger',
            'Draft'                => 'secondary',
            'Tervalidasi'          => 'success',
            'Diajukan'             => 'secondary',
            'Diproses'             => 'info',
            'Menunggu Persetujuan' => 'warning',
            'Disetujui'            => 'success',
            'Dipublikasikan'       => 'success',
            'Selesai'              => 'secondary',
            'Dibatalkan'           => 'danger',
            'Arsip'                => 'secondary',
            'Terdaftar'            => 'info',
            'Menunggu Validasi'    => 'warning',
            'Dipesan'              => 'warning',
            'Dikonfirmasi'         => 'info',
            'Dibayar'              => 'success',
        ];
        $warna = $map[$status] ?? 'secondary';
        return '<span class="badge badge-status text-bg-' . $warna . '">' . e($status) . '</span>';
    }
}
