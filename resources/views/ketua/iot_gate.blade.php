@extends('layouts.app')

@section('title', 'Monitoring Portal Gerbang RT/RW')
@section('page_title', 'Monitoring Portal Gerbang RT/RW (IoT)')
@section('page_subtitle', 'Pengawasan Akses Keluar-Masuk Warga Berbasis e-KTP RFID / NFC')

@section('content')
<!-- Statistik Real-Time Hari Ini -->
<div class="row g-3 mb-4">
    <div class="col-xl-3 col-sm-6">
        <div class="stat-card">
            <div class="stat-label">Total Akses Hari Ini</div>
            <div class="stat-value">{{ $stats['today_total'] }}</div>
            <div class="stat-hint">Seluruh kendaraan/warga melintas</div>
        </div>
    </div>
    <div class="col-xl-3 col-sm-6">
        <div class="stat-card">
            <div class="stat-label" style="color: #059669;">Akses Diterima</div>
            <div class="stat-value text-success">{{ $stats['today_granted'] }}</div>
            <div class="stat-hint">Warga resmi berizin</div>
        </div>
    </div>
    <div class="col-xl-3 col-sm-6">
        <div class="stat-card {{ $stats['today_denied'] > 0 ? 'accent' : '' }}">
            <div class="stat-label" style="color: #dc2626;">Akses Ditolak</div>
            <div class="stat-value text-danger">{{ $stats['today_denied'] }}</div>
            <div class="stat-hint">KTP asing / tidak dikenal</div>
        </div>
    </div>
    <div class="col-xl-3 col-sm-6">
        <div class="stat-card">
            <div class="stat-label">Total KTP Terdaftar</div>
            <div class="stat-value text-primary">{{ $stats['registered_users'] }}</div>
            <div class="stat-hint">Warga pemegang kartu aktif</div>
        </div>
    </div>
</div>

<div class="card-rt mb-4">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div>
            <div class="section-title mb-1"><span class="dot"></span> Kontrol Gerbang Eksekutif</div>
            <div class="text-muted-rt" style="font-size: 13.5px;">
                Status Smart Barrier Gate: <span class="badge bg-success-subtle text-success border border-success-subtle">● TERHUBUNG</span>
            </div>
        </div>
        <div>
            <form action="{{ route('ketua.iot_gate.manual_open') }}" method="POST" class="d-inline" onsubmit="return confirm('Buka portal perumahan secara manual sekarang?');">
                @csrf
                <button type="submit" class="btn-rt-outline" style="border-color: #f59e0b; color: #b45309;">
                    🚨 Buka Portal Darurat (Manual)
                </button>
            </form>
        </div>
    </div>
</div>

<div class="card-rt">
    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
        <div class="section-title mb-0"><span class="dot"></span> Log Keluar-Masuk Gerbang Terkini</div>
        <form method="GET" class="d-flex gap-2">
            <input type="text" name="q" class="form-control form-control-sm" placeholder="Cari nama, rumah, UID..." value="{{ $search }}">
            <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                <option value="">Semua Status</option>
                <option value="granted" {{ $statusFilter === 'granted' ? 'selected' : '' }}>Diterima Saja</option>
                <option value="denied" {{ $statusFilter === 'denied' ? 'selected' : '' }}>Ditolak Saja</option>
            </select>
            <button type="submit" class="btn btn-sm btn-outline-secondary">Filter</button>
        </form>
    </div>

    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0" style="font-size: 13.5px;">
            <thead class="table-light">
                <tr>
                    <th>Waktu Melintas</th>
                    <th>Nama Warga</th>
                    <th>No. Rumah</th>
                    <th>UID KTP</th>
                    <th>Status Akses</th>
                    <th>Aksi Portal</th>
                    <th>Keterangan</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($logs as $log)
                    <tr>
                        <td>{{ $log->tapped_at->format('H:i:s - d/m/Y') }}</td>
                        <td>
                            <strong>{{ $log->warga_nama }}</strong>
                            @if ($log->user)
                                <div style="font-size: 11px; color: #64748b;">Peran: {{ $log->user->role }}</div>
                            @endif
                        </td>
                        <td><span class="badge bg-light text-dark border">{{ $log->no_rumah ?: '-' }}</span></td>
                        <td><code class="font-monospace fw-semibold">{{ $log->rfid_uid }}</code></td>
                        <td>
                            @if ($log->access_status === 'granted')
                                <span class="badge bg-success-subtle text-success border border-success-subtle">
                                    ✓ Diterima
                                </span>
                            @else
                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle">
                                    ✗ Ditolak
                                </span>
                            @endif
                        </td>
                        <td>
                            @if ($log->gate_action === 'OPEN_GATE')
                                <span class="text-success fw-semibold">🟢 Portal Terbuka</span>
                            @elseif ($log->gate_action === 'MANUAL_OPEN')
                                <span class="text-warning fw-semibold">🟡 Buka Manual</span>
                            @else
                                <span class="text-muted">🔒 Terkunci</span>
                            @endif
                        </td>
                        <td class="text-muted" style="max-width: 250px;">
                            {{ $log->denial_reason ?: 'Verifikasi e-KTP Valid' }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center py-4 text-muted">Belum ada catatan log aktivitas gerbang.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if ($logs->hasPages())
        <div class="mt-3">
            {{ $logs->links() }}
        </div>
    @endif
</div>
@endsection
