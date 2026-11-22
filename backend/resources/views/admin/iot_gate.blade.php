@extends('layouts.app')

@section('title', 'Monitoring Portal IoT & KTP')
@section('page_title', 'Monitoring Portal IoT & Autentikasi e-KTP')
@section('page_subtitle', 'Pusat Kontrol Akses Gerbang Perumahan Berbasis ESP32 RFID / NFC')

@section('content')
<style>
.scan-radar-container {
    position: relative;
    width: 120px;
    height: 120px;
    margin: 0 auto;
    display: flex;
    align-items: center;
    justify-content: center;
}
.scan-pulse {
    position: absolute;
    width: 100px;
    height: 100px;
    border-radius: 50%;
    background: rgba(59, 130, 246, 0.2);
    animation: radarPulse 1.8s infinite ease-out;
}
.scan-icon {
    font-size: 42px;
    z-index: 2;
}
@keyframes radarPulse {
    0% { transform: scale(0.6); opacity: 0.8; }
    50% { opacity: 0.4; }
    100% { transform: scale(1.4); opacity: 0; }
}
</style>

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
            <div class="stat-value text-primary" id="statRegisteredUsers">{{ $stats['registered_users'] }}</div>
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
                &nbsp;|&nbsp; Login: <b>{{ auth()->user()->nama }}</b> ({{ auth()->user()->role }})
                &nbsp;|&nbsp; Status KTP Anda: 
                @if (auth()->user()->hasRfidRegistered())
                    <span class="badge bg-success text-white">● Terdaftar (<code>{{ auth()->user()->rfid_uid }}</code>)</span>
                @else
                    <span class="badge bg-warning-subtle text-warning-emphasis border">○ Belum Terdaftar</span>
                @endif
            </div>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <!-- Scan e-KTP Khusus untuk Akun yang Sedang Login -->
            <button type="button" class="btn-rt-primary" onclick="openAdminScanForSelf()" title="Pindai dan daftarkan e-KTP untuk akun Anda sendiri">
                📡 Scan e-KTP Akun Saya ({{ auth()->user()->nama }})
            </button>

            <!-- Scan e-KTP untuk Warga Lain -->
            <button type="button" class="btn-rt-outline" onclick="openAdminScanModal()" title="Pindai dan daftarkan e-KTP untuk warga lain">
                👥 Scan untuk Warga Lain
            </button>

            <!-- Uji Simulasi Tap Gerbang -->
            <button type="button" class="btn-rt-outline" style="border-color: #3b82f6; color: #2563eb;" data-bs-toggle="modal" data-bs-target="#modalAdminSimulateTap">
                ⚡ Uji Tap Gerbang (Simulasi)
            </button>

            <!-- Input Manual UID -->
            <button type="button" class="btn-rt-outline" data-bs-toggle="modal" data-bs-target="#modalRegisterWarga">
                ➕ Input UID Manual
            </button>

            <!-- Buka Portal Manual Darurat -->
            <form action="{{ route('admin.iot_gate.manual_open') }}" method="POST" class="d-inline" onsubmit="return confirm('Buka portal perumahan secara manual sekarang?');">
                @csrf
                <button type="submit" class="btn-rt-outline" style="border-color: #f59e0b; color: #b45309;">
                    🚨 Buka Portal Manual (Darurat)
                </button>
            </form>

            <!-- Download Firmware -->
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
                <div class="section-title mb-0">
                    <span class="dot"></span> Riwayat Tap Gerbang Terkini
                    <span class="badge bg-light text-muted border ms-2" style="font-size: 11px; font-weight: normal;" id="logAutoRefreshStatus">● Auto-update aktif</span>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="fetchLatestLogs(true)" title="Segarkan Data Log">
                        🔄 Refresh Log
                    </button>
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
                            <tr id="emptyLogsRow">
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
            <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                <div class="section-title mb-0"><span class="dot"></span> Daftar Pengguna & Status Hak Akses KTP</div>
                <div>
                    <button type="button" class="btn btn-sm btn-rt-primary" onclick="openAdminScanModal()">
                        📡 Scan e-KTP Baru
                    </button>
                </div>
            </div>
            
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
                    <tbody id="wargaTableBody">
                        @foreach ($wargaUsers as $wu)
                            <tr id="user-row-{{ $wu->id }}" class="{{ $wu->id === auth()->id() ? 'table-primary-subtle' : '' }}">
                                <td>
                                    <strong>{{ $wu->nama }}</strong>
                                    @if ($wu->id === auth()->id())
                                        <span class="badge bg-primary text-white ms-1" style="font-size: 10.5px;">⭐ Akun Anda</span>
                                    @endif
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
                                        <!-- Tombol Scan KTP Langsung via ESP32 -->
                                        <button type="button" class="btn btn-sm {{ $wu->id === auth()->id() ? 'btn-success' : 'btn-primary' }} text-white" onclick="openAdminScanForUser({{ $wu->id }}, '{{ addslashes($wu->nama) }}', '{{ $wu->warga?->no_rumah ?? '-' }}')" title="Scan KTP langsung via ESP32 untuk {{ $wu->nama }}">
                                            📡 {{ $wu->id === auth()->id() ? 'Scan KTP Saya' : 'Scan KTP' }}
                                        </button>

                                        <!-- Edit/Assign Manual UID Button -->
                                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="setEditUserModal({{ $wu->id }}, '{{ addslashes($wu->nama) }}', '{{ $wu->rfid_uid ?? '' }}')" title="Input / Edit UID Manual">
                                            ✏️ {{ $wu->hasRfidRegistered() ? 'Ubah' : 'Manual' }}
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

<!-- ========================================================================= -->
<!-- MODAL 1: SCAN e-KTP REAL-TIME VIA ESP32                                   -->
<!-- ========================================================================= -->
<div class="modal fade" id="modalAdminScanEsp32" tabindex="-1" aria-labelledby="modalAdminScanEsp32Label" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius: 16px; border: 1px solid rgba(59, 130, 246, 0.3);">
            <div class="modal-header border-0 pb-0">
                <div class="d-flex align-items-center gap-2">
                    <span style="font-size: 22px;">📡</span>
                    <h5 class="modal-title fw-bold" id="modalAdminScanEsp32Label">Pindai e-KTP via ESP32</h5>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" onclick="stopAdminScanPolling()"></button>
            </div>
            <div class="modal-body py-3">
                <!-- Pilihan Cepat: Akun Sendiri vs Warga Lain -->
                <div class="d-flex gap-2 mb-3">
                    <button type="button" class="btn btn-sm btn-outline-primary active flex-fill" id="btnTargetSelf" onclick="openAdminScanForSelf()">
                        ⭐ Akun Saya Sendiri ({{ auth()->user()->nama }})
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-secondary flex-fill" id="btnTargetOther" onclick="toggleSelectTargetOther()">
                        👥 Pilih Warga Lain
                    </button>
                </div>

                <!-- Dropdown Pilih Warga Target -->
                <div class="mb-3" id="adminScanUserSelectContainer" style="display: none;">
                    <label class="form-label fw-semibold" style="font-size: 13px;">Pilih Akun Warga yang Didaftarkan:</label>
                    <select id="adminScanTargetUserId" class="form-select form-select-sm" onchange="onScanTargetUserChanged()">
                        <option value="{{ auth()->id() }}" data-nama="{{ auth()->user()->nama }}" data-rumah="{{ auth()->user()->warga?->no_rumah ?? '-' }}" selected>
                            ⭐ [AKUN SAYA SENDIRI] {{ auth()->user()->nama }} ({{ auth()->user()->role }}) {{ auth()->user()->hasRfidRegistered() ? '✓ [KTP: ' . auth()->user()->rfid_uid . ']' : '[Belum Terdaftar]' }}
                        </option>
                        @foreach ($wargaUsers as $wu)
                            @if ($wu->id !== auth()->id())
                                <option value="{{ $wu->id }}" data-nama="{{ $wu->nama }}" data-rumah="{{ $wu->warga?->no_rumah ?? '-' }}">
                                    {{ $wu->nama }} ({{ $wu->role }}) {{ $wu->warga?->no_rumah ? '- No. ' . $wu->warga->no_rumah : '' }} {{ $wu->hasRfidRegistered() ? '✓ [KTP: ' . $wu->rfid_uid . ']' : '[Belum Terdaftar]' }}
                                </option>
                            @endif
                        @endforeach
                    </select>
                </div>

                <!-- Info Box Target Warga Aktif -->
                <div class="p-2 mb-3 rounded-2 text-center" style="background: rgba(59, 130, 246, 0.08); border: 1px solid rgba(59, 130, 246, 0.2);">
                    <div style="font-size: 11px; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px;">Target Registrasi e-KTP</div>
                    <div class="fw-bold text-primary fs-6" id="scanTargetUserDisplay">{{ auth()->user()->nama }}</div>
                    <div style="font-size: 12px; color: #475569;" id="scanTargetHouseDisplay">Akun yang Sedang Login</div>
                </div>

                <!-- Radar pulse animation -->
                <div class="scan-radar-container my-3">
                    <div class="scan-pulse"></div>
                    <div class="scan-icon">💳</div>
                </div>

                <div class="text-center">
                    <h6 class="fw-bold mt-2 mb-1" id="adminScanStatusHeading">Dekatkan e-KTP ke Sensor RFID / NFC</h6>
                    <p class="text-muted mb-2" id="adminScanStatusSub" style="font-size: 13px;">
                        Alat scanner ESP32 sedang mendengarkan tap kartu...
                    </p>

                    <div class="badge bg-primary-subtle text-primary px-3 py-2 fs-6 mb-3" id="adminScanTimerBadge">
                        Sisa waktu: <span id="adminScanTimerCountdown">90</span> detik
                    </div>

                    <div id="adminScanResultSuccess" class="alert alert-success d-none mt-2 text-start" style="font-size: 13.5px;">
                        <div class="d-flex align-items-center gap-2">
                            <span style="font-size: 22px;">🎉</span>
                            <div>
                                <strong id="adminScanSuccessTitle">e-KTP Berhasil Didaftarkan!</strong>
                                <div class="mt-1" id="adminScanSuccessDetail"></div>
                                <div class="mt-1 font-monospace fw-bold" id="adminScanSuccessUid" style="color: #047857; font-size: 15px;"></div>
                            </div>
                        </div>
                    </div>

                    <div id="adminScanResultError" class="alert alert-danger d-none mt-2 text-start" style="font-size: 13px;">
                        ⚠️ <span id="adminScanErrorMessage"></span>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-0 pt-0 justify-content-between">
                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="restartAdminScan()">
                    🔄 Ulangi Scan
                </button>
                <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal" onclick="stopAdminScanPolling()">
                    Tutup
                </button>
            </div>
        </div>
    </div>
</div>

<!-- ========================================================================= -->
<!-- MODAL 2: UJI TAP GERBANG (SIMULASI IOT)                                    -->
<!-- ========================================================================= -->
<div class="modal fade" id="modalAdminSimulateTap" tabindex="-1" aria-labelledby="modalAdminSimulateTapLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius: 16px;">
            <div class="modal-header">
                <div class="d-flex align-items-center gap-2">
                    <span style="font-size: 22px;">⚡</span>
                    <h5 class="modal-title fw-bold" id="modalAdminSimulateTapLabel">Simulasi Uji Tap Gerbang (IoT Test)</h5>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted" style="font-size: 13px;">
                    Uji respon logika gerbang perumahan langsung dari browser tanpa perlu menyentuh perangkat ESP32 fisik.
                </p>

                <div class="mb-3">
                    <label class="form-label fw-semibold" style="font-size: 13px;">Pilih Akun Warga Terdaftar (Otomatis ambil UID):</label>
                    <select id="simTargetUserSelect" class="form-select form-select-sm" onchange="onSimUserSelected()">
                        <option value="">-- Ketik UID Manual / Pilih Warga Terdaftar --</option>
                        @foreach ($wargaUsers as $wu)
                            @if ($wu->hasRfidRegistered())
                                <option value="{{ $wu->rfid_uid }}" data-nama="{{ $wu->nama }}" data-status="{{ $wu->rfid_is_active ? 'aktif' : 'blokir' }}">
                                    {{ $wu->nama }} (Rumah: {{ $wu->warga?->no_rumah ?? '-' }}) - UID: {{ $wu->rfid_uid }} [{{ $wu->rfid_is_active ? 'AKTIF' : 'DIBLOKIR' }}]
                                </option>
                            @endif
                        @endforeach
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold" style="font-size: 13px;">Atau Masukkan UID KTP Uji Coba (Bisa Kartu Asing / Baru):</label>
                    <div class="input-group input-group-sm">
                        <input type="text" id="simUidInput" class="form-control font-monospace" placeholder="Contoh: 4A:2F:89:1B atau AA:BB:CC:99" value="4A:2F:89:1B">
                        <button type="button" class="btn btn-outline-secondary" onclick="document.getElementById('simUidInput').value = 'FF:EE:DD:CC:99'">Kartu Asing</button>
                    </div>
                </div>

                <!-- Simulation Output Box -->
                <div id="simFeedbackBox" class="p-3 rounded-3 mt-3 d-none" style="border-left: 4px solid #3b82f6;">
                    <div class="d-flex align-items-center gap-2">
                        <span id="simFeedbackIcon" style="font-size: 24px;"></span>
                        <div>
                            <strong id="simFeedbackTitle" style="font-size: 14px;"></strong>
                            <div id="simFeedbackMsg" style="font-size: 12.5px;" class="mt-1"></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-rt-outline" data-bs-dismiss="modal">Tutup</button>
                <button type="button" class="btn-rt-primary" onclick="submitSimulateTap()">
                    ⚡ Jalankan Tap Simulasi
                </button>
            </div>
        </div>
    </div>
</div>

<!-- ========================================================================= -->
<!-- MODAL 3: INPUT / EDIT MANUAL UID e-KTP                                    -->
<!-- ========================================================================= -->
<div class="modal fade" id="modalRegisterWarga" tabindex="-1" aria-labelledby="modalRegisterWargaLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius: 16px;">
            <div class="modal-header">
                <h5 class="modal-title fw-bold" id="modalRegisterWargaLabel">➕ Daftarkan / Edit e-KTP Warga</h5>
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
                                <option value="{{ $wu->id }}" data-nama="{{ $wu->nama }}" data-rumah="{{ $wu->warga?->no_rumah ?? '-' }}">
                                    {{ $wu->nama }} ({{ $wu->role }}) {{ $wu->warga?->no_rumah ? '- No. ' . $wu->warga->no_rumah : '' }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">UID e-KTP (Hexadecimal)</label>
                        <div class="input-group">
                            <input type="text" name="rfid_uid" id="modalRfidUid" class="form-control font-monospace" placeholder="Contoh: 4A:2F:89:1B atau 045AB21A" required autocomplete="off">
                            <button type="button" class="btn btn-outline-primary" onclick="scanFromRegisterModal()" title="Pindai langsung dari sensor ESP32">
                                📡 Scan ESP32
                            </button>
                        </div>
                        <div class="form-text" style="font-size: 12px;">Format UID otomatis disesuaikan menjadi huruf kapital hex. Anda dapat mengetik manual atau klik <b>Scan ESP32</b>.</div>
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
// =============================================================================
// KONFIGURASI ROUTE & GLOBAL STATE
// =============================================================================
const START_SCAN_URL = "{{ route('admin.iot_gate.start_scan') }}";
const CHECK_SCAN_URL = "{{ route('admin.iot_gate.check_scan') }}";
const SIMULATE_TAP_URL = "{{ route('admin.iot_gate.simulate_tap') }}";
const LOGS_DATA_URL = "{{ route('admin.iot_gate.logs_data') }}";
const CSRF_TOKEN = "{{ csrf_token() }}";

const LOGGED_IN_USER_ID = {{ auth()->id() }};
const LOGGED_IN_USER_NAME = "{{ addslashes(auth()->user()->nama) }}";
const LOGGED_IN_USER_HOUSE = "{{ auth()->user()->warga?->no_rumah ?? '-' }}";

let adminScanInterval = null;
let adminCountdownTimer = null;
let currentTargetUserId = LOGGED_IN_USER_ID;
let currentTargetUserName = LOGGED_IN_USER_NAME;
let currentTargetHouseNo = LOGGED_IN_USER_HOUSE;
let isScanningActive = false;

// =============================================================================
// MODAL SCAN ESP32 CONTROLLER
// =============================================================================
function openAdminScanForSelf() {
    const btnSelf = document.getElementById('btnTargetSelf');
    const btnOther = document.getElementById('btnTargetOther');
    if (btnSelf) {
        btnSelf.classList.add('active', 'btn-outline-primary');
        btnSelf.classList.remove('btn-outline-secondary');
    }
    if (btnOther) {
        btnOther.classList.remove('active', 'btn-outline-primary');
        btnOther.classList.add('btn-outline-secondary');
    }
    const container = document.getElementById('adminScanUserSelectContainer');
    if (container) container.style.display = 'none';

    openAdminScanForUser(LOGGED_IN_USER_ID, LOGGED_IN_USER_NAME + ' (Akun Anda)', LOGGED_IN_USER_HOUSE);
}

function toggleSelectTargetOther() {
    const btnSelf = document.getElementById('btnTargetSelf');
    const btnOther = document.getElementById('btnTargetOther');
    if (btnOther) {
        btnOther.classList.add('active', 'btn-outline-primary');
        btnOther.classList.remove('btn-outline-secondary');
    }
    if (btnSelf) {
        btnSelf.classList.remove('active', 'btn-outline-primary');
        btnSelf.classList.add('btn-outline-secondary');
    }
    const container = document.getElementById('adminScanUserSelectContainer');
    if (container) container.style.display = 'block';

    const select = document.getElementById('adminScanTargetUserId');
    if (select) {
        onScanTargetUserChanged();
    }
}

function openAdminScanModal() {
    toggleSelectTargetOther();
    const modalEl = document.getElementById('modalAdminScanEsp32');
    const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
    modal.show();
}

function openAdminScanForUser(userId, userName, houseNo) {
    currentTargetUserId = userId;
    currentTargetUserName = userName;
    currentTargetHouseNo = houseNo;

    const select = document.getElementById('adminScanTargetUserId');
    if (select) {
        select.value = userId;
    }
    updateTargetDisplay();

    const modalEl = document.getElementById('modalAdminScanEsp32');
    const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
    modal.show();
    startAdminScan(userId);
}

function onScanTargetUserChanged() {
    const select = document.getElementById('adminScanTargetUserId');
    const selectedOption = select.options[select.selectedIndex];
    if (selectedOption) {
        currentTargetUserId = selectedOption.value;
        currentTargetUserName = selectedOption.dataset.nama || selectedOption.text;
        currentTargetHouseNo = selectedOption.dataset.rumah || '-';
        updateTargetDisplay();
        startAdminScan(currentTargetUserId);
    }
}

function updateTargetDisplay() {
    const nameEl = document.getElementById('scanTargetUserDisplay');
    const houseEl = document.getElementById('scanTargetHouseDisplay');
    if (nameEl) nameEl.innerText = currentTargetUserName || 'Warga Terpilih';
    if (houseEl) houseEl.innerText = 'No. Rumah: ' + (currentTargetHouseNo || '-');
}

function startAdminScan(userId) {
    stopAdminScanPolling();
    resetScanModalUI();

    fetch(START_SCAN_URL, {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            "X-CSRF-TOKEN": CSRF_TOKEN
        },
        body: JSON.stringify({ user_id: userId })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            isScanningActive = true;
            let seconds = data.remaining_seconds || 90;
            const timerEl = document.getElementById('adminScanTimerCountdown');
            if (timerEl) timerEl.innerText = seconds;

            adminCountdownTimer = setInterval(() => {
                seconds--;
                if (timerEl) timerEl.innerText = Math.max(0, seconds);
                if (seconds <= 0) {
                    stopAdminScanPolling();
                    showAdminScanError('Waktu tunggu scan habis (90 detik). Silakan klik Ulangi Scan.');
                }
            }, 1000);

            pollAdminScanCheck(userId);
        } else {
            showAdminScanError(data.message || 'Gagal memulai sesi scan.');
        }
    })
    .catch(err => {
        console.error(err);
        showAdminScanError('Gagal terhubung ke server RTEX.');
    });
}

function pollAdminScanCheck(userId) {
    adminScanInterval = setInterval(() => {
        if (!isScanningActive) return;

        fetch(`${CHECK_SCAN_URL}?user_id=${userId}`)
            .then(r => r.json())
            .then(data => {
                if (data.status === 'scanned') {
                    stopAdminScanPolling();
                    showAdminScanSuccess(data.uid, data.user_name || currentTargetUserName, data.message);
                    
                    // Auto-refresh data table & stats after 2 seconds
                    setTimeout(() => {
                        window.location.reload();
                    }, 2200);
                } else if (data.status === 'expired') {
                    stopAdminScanPolling();
                    showAdminScanError('Sesi scan kedaluwarsa. Silakan klik Ulangi Scan.');
                }
            })
            .catch(err => console.error("Poll scan error:", err));
    }, 1500);
}

function stopAdminScanPolling() {
    isScanningActive = false;
    if (adminScanInterval) clearInterval(adminScanInterval);
    if (adminCountdownTimer) clearInterval(adminCountdownTimer);
}

function restartAdminScan() {
    if (currentTargetUserId) {
        startAdminScan(currentTargetUserId);
    } else {
        openAdminScanModal();
    }
}

function resetScanModalUI() {
    document.getElementById('adminScanStatusHeading').innerText = 'Dekatkan e-KTP ke Sensor RFID / NFC';
    document.getElementById('adminScanStatusSub').innerText = 'Alat scanner ESP32 sedang mendengarkan tap kartu...';
    document.getElementById('adminScanResultSuccess').classList.add('d-none');
    document.getElementById('adminScanResultError').classList.add('d-none');
    document.getElementById('adminScanTimerBadge').classList.remove('d-none');
}

function showAdminScanSuccess(uid, userName, msg) {
    document.getElementById('adminScanStatusHeading').innerText = '✅ Kartu Berhasil Terdeteksi!';
    document.getElementById('adminScanStatusSub').innerText = 'e-KTP telah terhubung ke akun ' + userName + '.';
    document.getElementById('adminScanTimerBadge').classList.add('d-none');
    
    document.getElementById('adminScanSuccessTitle').innerText = 'e-KTP Berhasil Didaftarkan!';
    document.getElementById('adminScanSuccessDetail').innerText = 'Pemilik: ' + userName + ' (Rumah: ' + currentTargetHouseNo + ')';
    document.getElementById('adminScanSuccessUid').innerText = 'UID: ' + uid;
    document.getElementById('adminScanResultSuccess').classList.remove('d-none');
}

function showAdminScanError(msg) {
    document.getElementById('adminScanErrorMessage').innerText = msg;
    document.getElementById('adminScanResultError').classList.remove('d-none');
}

// =============================================================================
// MODAL REGISTER WARGA & SCAN SHORTCUT
// =============================================================================
function setEditUserModal(userId, userName, currentUid) {
    document.getElementById('modalUserId').value = userId;
    document.getElementById('modalRfidUid').value = currentUid;
    const modal = new bootstrap.Modal(document.getElementById('modalRegisterWarga'));
    modal.show();
}

function scanFromRegisterModal() {
    const userSelect = document.getElementById('modalUserId');
    const selectedUserId = userSelect.value;
    if (!selectedUserId) {
        alert('Silakan pilih akun warga terlebih dahulu pada dropdown di atas.');
        userSelect.focus();
        return;
    }

    const selectedOption = userSelect.options[userSelect.selectedIndex];
    const userName = selectedOption.dataset.nama || selectedOption.text;
    const houseNo = selectedOption.dataset.rumah || '-';

    // Tutup modal register warga
    const regModalEl = document.getElementById('modalRegisterWarga');
    const regModal = bootstrap.Modal.getInstance(regModalEl);
    if (regModal) regModal.hide();

    // Buka scan modal terarah ke warga tersebut
    openAdminScanForUser(selectedUserId, userName, houseNo);
}

// =============================================================================
// MODAL SIMULASI TAP GERBANG (SIMULATOR)
// =============================================================================
function onSimUserSelected() {
    const select = document.getElementById('simTargetUserSelect');
    const val = select.value;
    if (val) {
        document.getElementById('simUidInput').value = val;
    }
}

function submitSimulateTap() {
    const uid = document.getElementById('simUidInput').value.trim();
    if (!uid) {
        alert('Harap masukkan UID KTP atau pilih warga.');
        return;
    }

    const box = document.getElementById('simFeedbackBox');
    const icon = document.getElementById('simFeedbackIcon');
    const title = document.getElementById('simFeedbackTitle');
    const msg = document.getElementById('simFeedbackMsg');

    box.classList.remove('d-none');
    box.style.background = '#f8fafc';
    box.style.borderLeftColor = '#3b82f6';
    icon.innerText = '⏳';
    title.innerText = 'Memproses Tap Kartu...';
    title.style.color = '#1e293b';
    msg.innerText = 'Mengirim UID ' + uid + ' ke backend verifikasi gerbang...';

    fetch(SIMULATE_TAP_URL, {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            "X-CSRF-TOKEN": CSRF_TOKEN
        },
        body: JSON.stringify({ uid: uid })
    })
    .then(r => r.json())
    .then(data => {
        if (data.mode === 'registration' && data.success) {
            box.style.background = 'rgba(16, 185, 129, 0.1)';
            box.style.borderLeftColor = '#10b981';
            icon.innerText = '🎉';
            title.innerText = 'REGISTRASI BERHASIL!';
            title.style.color = '#047857';
            msg.innerText = data.message + ' UID: ' + uid;
            setTimeout(() => window.location.reload(), 1800);
        } else if (data.access === 'granted' || data.open_gate) {
            box.style.background = 'rgba(16, 185, 129, 0.1)';
            box.style.borderLeftColor = '#10b981';
            icon.innerText = '🟢';
            title.innerText = 'AKSES DITERIMA (PORTAL TERBUKA)';
            title.style.color = '#047857';
            msg.innerText = (data.message || 'Akses Diterima') + ' (Palang gerbang terbuka otomatis)';
            fetchLatestLogs(false);
        } else {
            box.style.background = 'rgba(239, 68, 68, 0.1)';
            box.style.borderLeftColor = '#ef4444';
            icon.innerText = '🔴';
            title.innerText = 'AKSES DITOLAK (PORTAL TERKUNCI)';
            title.style.color = '#b91c1c';
            msg.innerText = data.message || 'KTP tidak terdaftar atau dinonaktifkan.';
            fetchLatestLogs(false);
        }
    })
    .catch(err => {
        console.error(err);
        box.style.background = 'rgba(239, 68, 68, 0.1)';
        icon.innerText = '⚠️';
        title.innerText = 'Gagal Menguji Tap';
        msg.innerText = 'Terjadi kesalahan sistem saat menghubungi simulator.';
    });
}

// =============================================================================
// AUTO-REFRESH LOGS DI TAB 1
// =============================================================================
function fetchLatestLogs(notifyUser) {
    fetch(LOGS_DATA_URL)
        .then(r => r.json())
        .then(data => {
            if (data.logs && data.logs.length > 0) {
                const tbody = document.getElementById('logsTableBody');
                if (tbody) {
                    let html = '';
                    data.logs.forEach(log => {
                        const isGranted = log.access_status === 'granted';
                        const badgeStatus = isGranted 
                            ? '<span class="badge bg-success-subtle text-success border border-success-subtle">✓ Diterima</span>'
                            : '<span class="badge bg-danger-subtle text-danger border border-danger-subtle">✗ Ditolak</span>';
                        
                        let portalAction = '<span class="text-muted">🔒 Terkunci</span>';
                        if (log.gate_action === 'OPEN_GATE') {
                            portalAction = '<span class="text-success fw-semibold">🟢 Portal Terbuka</span>';
                        } else if (log.gate_action === 'MANUAL_OPEN') {
                            portalAction = '<span class="text-warning fw-semibold">🟡 Buka Manual</span>';
                        }

                        html += `<tr>
                            <td>${log.tapped_at}</td>
                            <td><strong>${log.warga_nama}</strong></td>
                            <td><span class="badge bg-light text-dark border">${log.no_rumah}</span></td>
                            <td><code class="font-monospace fw-semibold">${log.rfid_uid}</code></td>
                            <td>${badgeStatus}</td>
                            <td>${portalAction}</td>
                            <td class="text-muted" style="max-width: 250px;">${log.denial_reason || 'Verifikasi e-KTP Valid'}</td>
                        </tr>`;
                    });
                    tbody.innerHTML = html;
                }
            }

            // Update stats
            if (data.today_total !== undefined) document.getElementById('statTodayTotal').innerText = data.today_total;
            if (data.today_granted !== undefined) document.getElementById('statTodayGranted').innerText = data.today_granted;
            if (data.today_denied !== undefined) document.getElementById('statTodayDenied').innerText = data.today_denied;

            if (notifyUser) {
                const badge = document.getElementById('logAutoRefreshStatus');
                if (badge) {
                    badge.innerText = '✓ Diperbarui barusan';
                    setTimeout(() => { badge.innerText = '● Auto-update aktif'; }, 2000);
                }
            }
        })
        .catch(err => console.error("Error refreshing logs:", err));
}

// Background poller for logs every 8 seconds
setInterval(() => {
    fetchLatestLogs(false);
}, 8000);
</script>
@endsection
