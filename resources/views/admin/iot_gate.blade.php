@extends('layouts.app')

@section('title', 'Monitoring Portal IoT & KTP')
@section('page_title', 'Monitoring Portal IoT & Autentikasi e-KTP')
@section('page_subtitle', 'Pusat Kontrol Akses Gerbang Perumahan Berbasis ESP32 RFID / NFC')

@section('content')
<!-- Statistik Real-Time Hari Ini -->
<div class="row g-3 mb-4">
    <div class="col-xl-3 col-sm-6">
        <div class="stat-card">
            <div class="stat-label">Total Akses Hari Ini</div>
            <div class="stat-value" id="statTodayTotal">{{ $stats['today_total'] }}</div>
            <div class="stat-hint">Seluruh aktivitas tap di gerbang</div>
        </div>
    </div>
    <div class="col-xl-3 col-sm-6">
        <div class="stat-card">
            <div class="stat-label" style="color: #059669;">Akses Diterima</div>
            <div class="stat-value text-success" id="statTodayGranted">{{ $stats['today_granted'] }}</div>
            <div class="stat-hint">Portal terbuka otomatis</div>
        </div>
    </div>
    <div class="col-xl-3 col-sm-6">
        <div class="stat-card {{ $stats['today_denied'] > 0 ? 'accent' : '' }}">
            <div class="stat-label" style="color: #dc2626;">Akses Ditolak</div>
            <div class="stat-value text-danger" id="statTodayDenied">{{ $stats['today_denied'] }}</div>
            <div class="stat-hint">KTP belum terdaftar / dinonaktifkan</div>
        </div>
    </div>
    <div class="col-xl-3 col-sm-6">
        <div class="stat-card">
            <div class="stat-label">KTP Warga Terdaftar</div>
            <div class="stat-value text-primary">{{ $stats['registered_users'] }}</div>
            <div class="stat-hint">Warga aktif pemilik akses</div>
        </div>
    </div>
</div>

<!-- Quick Control Actions -->
<div class="card-rt mb-4">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div>
            <div class="section-title mb-1"><span class="dot"></span> Kontrol Gerbang & Alat IoT</div>
            <div class="text-muted-rt" style="font-size: 13.5px;">
                Status Server IoT: <span class="badge bg-success-subtle text-success border border-success-subtle">● ONLINE</span>
                &nbsp;|&nbsp; Endpoint: <code style="font-size: 12px; background: rgba(59,130,246,0.1); padding: 2px 6px; border-radius: 4px;">POST /api/iot/gate/tap</code>
            </div>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <button type="button" class="btn-rt-primary" data-bs-toggle="modal" data-bs-target="#modalRegisterWarga">
                ➕ Daftarkan KTP Warga
            </button>
            <form action="{{ route('admin.iot_gate.manual_open') }}" method="POST" class="d-inline" onsubmit="return confirm('Buka portal perumahan secara manual sekarang?');">
                @csrf
                <button type="submit" class="btn-rt-outline" style="border-color: #f59e0b; color: #b45309;">
                    🚨 Buka Portal Manual (Darurat)
                </button>
            </form>
            <a href="{{ route('admin.iot_gate.firmware') }}" class="btn-rt-outline">
                💾 Download Firmware ESP32 (.ino)
            </a>
        </div>
    </div>
</div>

<!-- Tabs: Log Realtime, Kelola KTP, & Panduan Hardware -->
<ul class="nav nav-pills mb-3 gap-2" id="iotGateTabs" role="tablist">
    <li class="nav-item" role="presentation">
        <button class="nav-link active fw-semibold" id="tab-logs-btn" data-bs-toggle="tab" data-bs-target="#tab-logs" type="button" role="tab">
            📋 Log Akses Gerbang Real-time
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link fw-semibold" id="tab-users-btn" data-bs-toggle="tab" data-bs-target="#tab-users" type="button" role="tab">
            👥 Data KTP Seluruh Warga ({{ $wargaUsers->count() }})
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link fw-semibold" id="tab-guide-btn" data-bs-toggle="tab" data-bs-target="#tab-guide" type="button" role="tab">
            🛠️ Skema Hardware & Firmware ESP32
        </button>
    </li>
</ul>

<div class="tab-content" id="iotGateTabsContent">
    <!-- TAB 1: LOG REAL-TIME -->
    <div class="tab-pane fade show active" id="tab-logs" role="tabpanel">
        <div class="card-rt">
            <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                <div class="section-title mb-0"><span class="dot"></span> Riwayat Tap Gerbang Terkini</div>
                <div class="d-flex align-items-center gap-2">
                    <form method="GET" class="d-flex gap-2">
                        <input type="text" name="q" class="form-control form-control-sm" placeholder="Cari nama, rumah, UID..." value="{{ $search }}">
                        <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                            <option value="">Semua Status</option>
                            <option value="granted" {{ $statusFilter === 'granted' ? 'selected' : '' }}>Diterima Saja</option>
                            <option value="denied" {{ $statusFilter === 'denied' ? 'selected' : '' }}>Ditolak Saja</option>
                        </select>
                        <button type="submit" class="btn btn-sm btn-outline-secondary">Cari</button>
                    </form>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" style="font-size: 13.5px;">
                    <thead class="table-light">
                        <tr>
                            <th>Waktu Tap</th>
                            <th>Nama Warga</th>
                            <th>No. Rumah</th>
                            <th>UID KTP</th>
                            <th>Status Akses</th>
                            <th>Aksi Portal</th>
                            <th>Keterangan</th>
                        </tr>
                    </thead>
                    <tbody id="logsTableBody">
                        @forelse ($logs as $log)
                            <tr>
                                <td>{{ $log->tapped_at->format('H:i:s - d/m/Y') }}</td>
                                <td>
                                    <strong>{{ $log->warga_nama }}</strong>
                                    @if ($log->user)
                                        <div style="font-size: 11px; color: #64748b;">Role: {{ $log->user->role }}</div>
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
    </div>

    <!-- TAB 2: DATA KTP SELURUH WARGA -->
    <div class="tab-pane fade" id="tab-users" role="tabpanel">
        <div class="card-rt">
            <div class="section-title"><span class="dot"></span> Daftar Pengguna & Status Hak Akses KTP</div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" style="font-size: 13.5px;">
                    <thead class="table-light">
                        <tr>
                            <th>Nama Akun</th>
                            <th>Role</th>
                            <th>No. Rumah</th>
                            <th>Status KTP</th>
                            <th>UID e-KTP</th>
                            <th>Status Akses</th>
                            <th class="text-end">Aksi Pengurus</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($wargaUsers as $wu)
                            <tr>
                                <td>
                                    <strong>{{ $wu->nama }}</strong>
                                    <div style="font-size: 11.5px; color: #64748b;">{{ $wu->username }}</div>
                                </td>
                                <td><span class="badge bg-light text-dark border">{{ $wu->role }}</span></td>
                                <td>{{ $wu->warga?->no_rumah ?: '-' }}</td>
                                <td>
                                    @if ($wu->hasRfidRegistered())
                                        <span class="badge bg-success-subtle text-success border border-success-subtle">
                                            ● Terdaftar
                                        </span>
                                    @else
                                        <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle">
                                            ○ Belum Terdaftar
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    @if ($wu->hasRfidRegistered())
                                        <code class="font-monospace fw-semibold">{{ $wu->rfid_uid }}</code>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    @if ($wu->hasRfidRegistered())
                                        @if ($wu->rfid_is_active)
                                            <span class="badge bg-success text-white">Aktif</span>
                                        @else
                                            <span class="badge bg-danger text-white">Diblokir</span>
                                        @endif
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <div class="d-inline-flex gap-1">
                                        <!-- Edit/Assign Button -->
                                        <button type="button" class="btn btn-sm btn-outline-primary" onclick="setEditUserModal({{ $wu->id }}, '{{ $wu->nama }}', '{{ $wu->rfid_uid ?? '' }}')">
                                            ✏️ {{ $wu->hasRfidRegistered() ? 'Ganti' : 'Daftarkan' }}
                                        </button>

                                        @if ($wu->hasRfidRegistered())
                                            <!-- Toggle Active/Block Button -->
                                            <form action="{{ route('admin.iot_gate.toggle_status', $wu->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-sm {{ $wu->rfid_is_active ? 'btn-outline-warning' : 'btn-outline-success' }}" title="{{ $wu->rfid_is_active ? 'Blokir Akses' : 'Buka Blokir' }}">
                                                    {{ $wu->rfid_is_active ? '⛔ Blokir' : '✓ Aktifkan' }}
                                                </button>
                                            </form>

                                            <!-- Remove Button -->
                                            <form action="{{ route('admin.iot_gate.remove_user', $wu->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus KTP dari user {{ $wu->nama }}?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Hapus KTP">
                                                    🗑️
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- TAB 3: PANDUAN HARDWARE & FIRMWARE ESP32 -->
    <div class="tab-pane fade" id="tab-guide" role="tabpanel">
        <div class="card-rt mb-4">
            <div class="section-title"><span class="dot"></span> Panduan Hardware & Rangkaian ESP32 IoT Smart Gate</div>
            
            <div class="row g-4 mt-1">
                <div class="col-lg-6">
                    <h6 class="fw-bold text-primary">📌 Komponen Hardware yang Digunakan:</h6>
                    <ul class="mb-3" style="font-size: 13.5px; line-height: 1.8;">
                        <li><b>Mikrokontroler</b>: ESP32 DevKit v1 / NodeMCU-32S (Built-in WiFi 2.4GHz).</li>
                        <li><b>Reader RFID/NFC</b>: Modul <b>RC522 (13.56 MHz SPI)</b> atau <b>PN532 (NFC)</b>. Mampu membaca seluruh jenis e-KTP Indonesia standar ISO 14443 Type A.</li>
                        <li><b>Aktuator Portal</b>: Motor Servo (MG995 / MG996R / SG90) untuk simulasi palang gerbang, atau Modul Relay 5V untuk mengontrol barrier gate otomatis / solenoid door lock.</li>
                        <li><b>Indikator Suara & Visual</b>: Active Buzzer 5V, LED Hijau (Akses Diterima), dan LED Merah (Akses Ditolak).</li>
                        <li><b>Display (Opsional)</b>: LCD 16x2 I2C (Address 0x27) atau OLED 0.96" SSD1306.</li>
                    </ul>

                    <h6 class="fw-bold text-primary">🔌 Skema Pinout (ESP32 ke RC522 RFID SPI):</h6>
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm text-center" style="font-size: 12.5px;">
                            <thead class="table-light">
                                <tr><th>Pin RC522</th><th>Pin ESP32</th><th>Fungsi</th></tr>
                            </thead>
                            <tbody>
                                <tr><td>SDA (SS)</td><td><b>GPIO 5</b></td><td>SPI Chip Select</td></tr>
                                <tr><td>SCK</td><td><b>GPIO 18</b></td><td>SPI Clock</td></tr>
                                <tr><td>MOSI</td><td><b>GPIO 23</b></td><td>SPI Data In</td></tr>
                                <tr><td>MISO</td><td><b>GPIO 19</b></td><td>SPI Data Out</td></tr>
                                <tr><td>RST</td><td><b>GPIO 22</b></td><td>Reset Pin</td></tr>
                                <tr><td>GND</td><td><b>GND</b></td><td>Ground</td></tr>
                                <tr><td>3.3V</td><td><b>3V3</b> (Jangan 5V)</td><td>Tegangan Kerja RC522</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="col-lg-6">
                    <h6 class="fw-bold text-primary">🔌 Skema Pinout Aktuator & Indikator:</h6>
                    <div class="table-responsive mb-3">
                        <table class="table table-bordered table-sm text-center" style="font-size: 12.5px;">
                            <thead class="table-light">
                                <tr><th>Perangkat</th><th>Pin ESP32</th><th>Keterangan</th></tr>
                            </thead>
                            <tbody>
                                <tr><td>Servo PWM / Relay</td><td><b>GPIO 13</b></td><td>Sinyal buka palang (90° / Relay ON)</td></tr>
                                <tr><td>Buzzer (+)</td><td><b>GPIO 14</b></td><td>Beep 2x Sukses, Beep 3x Ditolak</td></tr>
                                <tr><td>LED Hijau (+)</td><td><b>GPIO 26</b></td><td>Indikator Akses Diterima</td></tr>
                                <tr><td>LED Merah (+)</td><td><b>GPIO 27</b></td><td>Indikator Akses Ditolak</td></tr>
                            </tbody>
                        </table>
                    </div>

                    <h6 class="fw-bold text-primary">⚙️ Konfigurasi URL & API Key pada Firmware:</h6>
                    <div class="p-3 bg-dark text-light rounded font-monospace" style="font-size: 12px;">
                        const char* ssid = "NAMA_WIFI_RT";<br>
                        const char* password = "PASSWORD_WIFI";<br>
                        const char* serverUrl = "https://rtex.unique-monsters.my.id/api/iot/gate/tap";<br>
                        const char* apiKey = "rtex-gate-key-2026";<br>
                        const char* deviceId = "PORTAL-UTAMA-RT05";
                    </div>

                    <div class="mt-3">
                        <a href="{{ route('admin.iot_gate.firmware') }}" class="btn-rt-primary w-100 text-center">
                            ⬇️ Unduh File Kode Arduino (.ino) Lengkap
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Daftarkan KTP Warga oleh Admin -->
<div class="modal fade" id="modalRegisterWarga" tabindex="-1" aria-labelledby="modalRegisterWargaLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius: 16px;">
            <div class="modal-header">
                <h5 class="modal-title fw-bold" id="modalRegisterWargaLabel">➕ Daftarkan e-KTP Warga</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('admin.iot_gate.register_warga') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Pilih Warga / Pengguna</label>
                        <select name="user_id" id="modalUserId" class="form-select" required>
                            <option value="">-- Pilih Akun --</option>
                            @foreach ($wargaUsers as $wu)
                                <option value="{{ $wu->id }}">
                                    {{ $wu->nama }} ({{ $wu->role }}) {{ $wu->warga?->no_rumah ? '- No. ' . $wu->warga->no_rumah : '' }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">UID e-KTP (Hexadecimal)</label>
                        <input type="text" name="rfid_uid" id="modalRfidUid" class="form-control" placeholder="Contoh: 4A:2F:89:1B atau 045AB21A" required autocomplete="off">
                        <div class="form-text" style="font-size: 12px;">Format UID akan disesuaikan otomatis menjadi huruf kapital hex.</div>
                    </div>
                    <div class="mb-2">
                        <label class="form-label fw-semibold">Label Kartu</label>
                        <input type="text" name="rfid_label" class="form-control" value="e-KTP" placeholder="e-KTP">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-rt-outline" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn-rt-primary">Simpan Hak Akses</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function setEditUserModal(userId, userName, currentUid) {
    document.getElementById('modalUserId').value = userId;
    document.getElementById('modalRfidUid').value = currentUid;
    const modal = new bootstrap.Modal(document.getElementById('modalRegisterWarga'));
    modal.show();
}
</script>
@endsection
