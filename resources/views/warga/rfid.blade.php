@extends('layouts.app')

@section('title', 'Akses Portal & e-KTP')
@section('page_title', 'Akses Portal & e-KTP Warga')
@section('page_subtitle', 'Sistem Integrasi IoT Smart Gate & Autentikasi e-KTP RFID / NFC')

@section('content')
<div class="row g-4 mb-4">
    <!-- Digital Smart Access Card -->
    <div class="col-lg-5 col-md-6">
        <div class="card-rt h-100" style="background: linear-gradient(135deg, #0b1e36 0%, #102a4e 100%); color: #ffffff; border: 1px solid rgba(59, 130, 246, 0.3); position: relative; overflow: hidden;">
            <div style="position: absolute; right: -20px; top: -20px; width: 140px; height: 140px; background: radial-gradient(circle, rgba(59, 130, 246, 0.15) 0%, transparent 70%); border-radius: 50%;"></div>
            
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div class="d-flex align-items-center gap-2">
                    <span style="font-size: 24px;">📡</span>
                    <div>
                        <strong style="letter-spacing: 1px; font-size: 15px;">RTEX ACCESS PASS</strong>
                        <div style="font-size: 11px; color: #94a3b8;">Smart Gate NFC / RFID</div>
                    </div>
                </div>
                <span class="badge" style="background: {{ $user->hasRfidRegistered() && $user->rfid_is_active ? 'rgba(16, 185, 129, 0.2)' : 'rgba(239, 68, 68, 0.2)' }}; color: {{ $user->hasRfidRegistered() && $user->rfid_is_active ? '#34d399' : '#f87171' }}; border: 1px solid {{ $user->hasRfidRegistered() && $user->rfid_is_active ? '#059669' : '#dc2626' }};">
                    {{ $user->hasRfidRegistered() ? ($user->rfid_is_active ? '● AKTIF' : '● NONAKTIF') : '○ BELUM TERDAFTAR' }}
                </span>
            </div>

            <div class="my-3 text-center py-2" style="background: rgba(15, 23, 42, 0.4); border-radius: 8px; border: 1px dashed rgba(148, 163, 184, 0.2);">
                <div style="font-size: 12px; color: #94a3b8; text-transform: uppercase; letter-spacing: 1px;">KTP UID (HEX)</div>
                <div class="mt-1" style="font-family: monospace; font-size: 20px; font-weight: 700; letter-spacing: 2px; color: #60a5fa;">
                    {{ $user->hasRfidRegistered() ? $user->rfid_uid : 'BELUM TERDAFTAR' }}
                </div>
                @if($user->rfid_registered_at)
                    <div style="font-size: 11px; color: #64748b; margin-top: 4px;">
                        Terdaftar: {{ $user->rfid_registered_at->format('d M Y, H:i') }}
                    </div>
                @endif
            </div>

            <div class="d-flex justify-content-between align-items-end mt-4 pt-2 border-top border-secondary-subtle" style="border-color: rgba(255,255,255,0.1) !important;">
                <div>
                    <div style="font-size: 11px; color: #94a3b8;">PEMILIK AKSES</div>
                    <div class="fw-bold" style="font-size: 15px;">{{ $user->nama }}</div>
                    <div style="font-size: 12px; color: #cbd5e1;">Rumah No. {{ $user->warga?->no_rumah ?? '-' }}</div>
                </div>
                <div class="text-end">
                    <span style="font-size: 28px;">💳</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions & Pairing Control -->
    <div class="col-lg-7 col-md-6">
        <div class="card-rt h-100">
            <div class="section-title"><span class="dot"></span> Kontrol Pendaftaran & Pengujian Akses</div>
            <p class="text-muted-rt" style="font-size: 13.5px;">
                Anda dapat mendaftarkan e-KTP secara langsung menggunakan scanner ESP32 yang terhubung ke jaringan RT, atau menguji simulasi respon gerbang perumahan.
            </p>

            <div class="d-flex flex-wrap gap-2 mb-3">
                <button type="button" class="btn-rt-primary" onclick="openScanModal()">
                    📡 Mulai Scan e-KTP (via ESP32)
                </button>

                <button type="button" class="btn-rt-outline" data-bs-toggle="modal" data-bs-target="#modalManualInput">
                    ⌨️ Input / Ganti UID Manual
                </button>

                @if($user->hasRfidRegistered())
                    <button type="button" class="btn-rt-outline" style="border-color: #3b82f6; color: #2563eb;" onclick="runGateSimulation()">
                        ⚡ Uji Tap Gerbang (Simulasi)
                    </button>

                    <form action="{{ route('warga.rfid.remove') }}" method="POST" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus e-KTP terdaftar dari akun ini?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn-rt-outline" style="color: #ef4444; border-color: #fca5a5;">
                            🗑️ Hapus KTP
                        </button>
                    </form>
                @endif
            </div>

            <!-- Simulation Feedback Result Alert -->
            <div id="simResultBox" class="mt-3 p-3 rounded-3" style="display: none; border-left: 4px solid #3b82f6;">
                <div class="d-flex align-items-center gap-2">
                    <span id="simIcon" style="font-size: 22px;"></span>
                    <div>
                        <strong id="simTitle" style="font-size: 14px;"></strong>
                        <div id="simMessage" style="font-size: 13px;"></div>
                    </div>
                </div>
            </div>

            <div class="alert alert-info mt-3 mb-0" style="font-size: 13px; background: rgba(59, 130, 246, 0.08); border-color: rgba(59, 130, 246, 0.2); color: #1e40af;">
                💡 <b>Petunjuk Penggunaan:</b>
                <ul class="mb-0 ps-3 mt-1">
                    <li>Klik <b>"Mulai Scan e-KTP"</b>, lalu tempelkan fisik e-KTP pada pembaca RFID/NFC di pos gerbang dalam waktu 90 detik.</li>
                    <li>Sistem otomatis mendeteksi UID dan langsung menghubungkannya ke akun warga Anda.</li>
                    <li>Setelah terdaftar, e-KTP dapat langsung digunakan untuk membuka portal masuk/keluar otomatis.</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<!-- Modal Scan e-KTP Real-Time via ESP32 -->
<div class="modal fade" id="modalScanEsp32" tabindex="-1" aria-labelledby="modalScanEsp32Label" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius: 16px; border: 1px solid rgba(59, 130, 246, 0.3);">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold" id="modalScanEsp32Label">📡 Menunggu Tap e-KTP pada ESP32</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" onclick="stopScanPolling()"></button>
            </div>
            <div class="modal-body text-center py-4">
                <!-- Radar animation -->
                <div class="scan-radar-container my-3">
                    <div class="scan-pulse"></div>
                    <div class="scan-icon">💳</div>
                </div>

                <h6 class="fw-bold mt-3 mb-1" id="scanStatusHeading">Dekatkan e-KTP ke Sensor RFID Gerbang</h6>
                <p class="text-muted mb-2" id="scanStatusSub" style="font-size: 13.5px;">
                    Alat scanner ESP32 sedang aktif menunggu kartu Anda...
                </p>

                <div class="badge bg-primary-subtle text-primary px-3 py-2 fs-6 mb-3" id="scanTimerBadge">
                    Sisa waktu: <span id="scanTimerCountdown">90</span> detik
                </div>

                <div id="scanResultSuccess" class="alert alert-success d-none mt-2" style="font-size: 13.5px;">
                    🎉 <strong id="scanSuccessMessage">e-KTP Berhasil Didaftarkan!</strong>
                    <div class="mt-1 font-monospace fw-bold" id="scanSuccessUid" style="font-size: 16px;"></div>
                </div>

                <div id="scanResultError" class="alert alert-danger d-none mt-2" style="font-size: 13px;">
                    ⚠️ <span id="scanErrorMessage"></span>
                </div>
            </div>
            <div class="modal-footer border-0 pt-0 justify-content-center">
                <button type="button" class="btn-rt-outline" data-bs-dismiss="modal" onclick="stopScanPolling()">
                    Tutup / Batalkan
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Input Manual UID -->
<div class="modal fade" id="modalManualInput" tabindex="-1" aria-labelledby="modalManualInputLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius: 16px;">
            <div class="modal-header">
                <h5 class="modal-title fw-bold" id="modalManualInputLabel">⌨️ Input Manual UID KTP</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('warga.rfid.register_manual') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold" style="font-size: 13.5px;">UID e-KTP (Hexadecimal)</label>
                        <input type="text" name="rfid_uid" class="form-control" placeholder="Contoh: 4A:2F:89:1B atau 045AB21A" value="{{ $user->rfid_uid }}" required autocomplete="off">
                        <div class="form-text" style="font-size: 12px;">
                            Bisa berupa 8 hingga 14 karakter hex. Karakter selain hex dan pemisah akan disesuaikan otomatis oleh sistem.
                        </div>
                    </div>
                    <div class="mb-2">
                        <label class="form-label fw-semibold" style="font-size: 13.5px;">Label Kartu</label>
                        <input type="text" name="rfid_label" class="form-control" value="{{ $user->rfid_label ?? 'e-KTP' }}" placeholder="e-KTP Warga">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-rt-outline" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn-rt-primary">Simpan UID e-KTP</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Access History Log Table -->
<div class="card-rt mt-4">
    <div class="section-title"><span class="dot"></span> Riwayat Akses Gerbang Portal Saya</div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0" style="font-size: 13.5px;">
            <thead class="table-light">
                <tr>
                    <th>Waktu Akses</th>
                    <th>Gerbang / Scanner</th>
                    <th>Status Akses</th>
                    <th>Aksi Portal</th>
                    <th>Keterangan</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($logs as $log)
                    <tr>
                        <td>{{ $log->tapped_at->format('d M Y, H:i:s') }}</td>
                        <td><span class="badge bg-light text-dark border">{{ $log->device_id }}</span></td>
                        <td>
                            @if ($log->access_status === 'granted')
                                <span class="badge bg-success-subtle text-success border border-success-subtle">
                                    ✓ Akses Diterima
                                </span>
                            @else
                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle">
                                    ✗ Akses Ditolak
                                </span>
                            @endif
                        </td>
                        <td>
                            @if ($log->gate_action === 'OPEN_GATE' || $log->gate_action === 'MANUAL_OPEN')
                                <span class="text-success fw-semibold">🟢 Portal Terbuka (5s)</span>
                            @else
                                <span class="text-secondary">🔒 Portal Terkunci</span>
                            @endif
                        </td>
                        <td class="text-muted">{{ $log->denial_reason ?: 'Verifikasi e-KTP Berhasil' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center py-4 text-muted">
                            Belum ada riwayat aktivitas akses gerbang untuk akun Anda.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<style>
.scan-radar-container {
    position: relative;
    width: 100px;
    height: 100px;
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

<script>
let scanInterval = null;
let countdownTimer = null;
let remainingSeconds = 90;

function openScanModal() {
    const modalEl = document.getElementById('modalScanEsp32');
    const modal = new bootstrap.Modal(modalEl);
    modal.show();

    // Reset UI
    document.getElementById('scanResultSuccess').classList.add('d-none');
    document.getElementById('scanResultError').classList.add('d-none');
    document.getElementById('scanStatusHeading').innerText = 'Dekatkan e-KTP ke Sensor RFID Gerbang';
    document.getElementById('scanStatusSub').innerText = 'Alat scanner ESP32 sedang aktif menunggu kartu Anda...';
    remainingSeconds = 90;
    document.getElementById('scanTimerCountdown').innerText = remainingSeconds;

    // Trigger start scan di backend
    fetch("{{ route('warga.rfid.start_scan') }}", {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            "X-CSRF-TOKEN": "{{ csrf_token() }}"
        },
        body: JSON.stringify({ device_id: 'PORTAL-UTAMA' })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            startPollingScan();
        } else {
            showScanError(data.message || 'Gagal memulai sesi scan.');
        }
    })
    .catch(err => {
        showScanError('Terjadi kesalahan koneksi ke server.');
    });
}

function startPollingScan() {
    stopScanPolling();

    // Timer countdown
    countdownTimer = setInterval(() => {
        remainingSeconds--;
        document.getElementById('scanTimerCountdown').innerText = remainingSeconds;
        if (remainingSeconds <= 0) {
            stopScanPolling();
            showScanError('Waktu habis. Silakan ulangi scan e-KTP kembali.');
        }
    }, 1000);

    // Polling backend setiap 1.5 detik
    scanInterval = setInterval(() => {
        fetch("{{ route('warga.rfid.check_scan') }}")
            .then(r => r.json())
            .then(data => {
                if (data.status === 'scanned') {
                    stopScanPolling();
                    showScanSuccess(data.uid, data.message);
                    setTimeout(() => {
                        window.location.reload();
                    }, 2200);
                } else if (data.status === 'expired') {
                    stopScanPolling();
                    showScanError('Waktu scan habis. Silakan ulangi kembali.');
                }
            })
            .catch(err => console.error(err));
    }, 1500);
}

function stopScanPolling() {
    if (scanInterval) clearInterval(scanInterval);
    if (countdownTimer) clearInterval(countdownTimer);
}

function showScanSuccess(uid, msg) {
    document.getElementById('scanStatusHeading').innerText = '✅ Kartu Berhasil Terdeteksi!';
    document.getElementById('scanStatusSub').innerText = 'Menyimpan data e-KTP ke akun Anda...';
    document.getElementById('scanSuccessUid').innerText = 'UID: ' + uid;
    document.getElementById('scanSuccessMessage').innerText = msg;
    document.getElementById('scanResultSuccess').classList.remove('d-none');
}

function showScanError(msg) {
    document.getElementById('scanErrorMessage').innerText = msg;
    document.getElementById('scanResultError').classList.remove('d-none');
}

function runGateSimulation() {
    const box = document.getElementById('simResultBox');
    const icon = document.getElementById('simIcon');
    const title = document.getElementById('simTitle');
    const msg = document.getElementById('simMessage');

    box.style.display = 'block';
    box.style.background = '#f1f5f9';
    box.style.borderLeftColor = '#3b82f6';
    icon.innerText = '⏳';
    title.innerText = 'Menguji Akses Gerbang...';
    msg.innerText = 'Mengirim sinyal UID e-KTP Anda ke sistem portal perumahan...';

    fetch("{{ route('warga.rfid.simulate_tap') }}", {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            "X-CSRF-TOKEN": "{{ csrf_token() }}"
        }
    })
    .then(r => r.json())
    .then(data => {
        if (data.access === 'granted' || data.open_gate) {
            box.style.background = 'rgba(16, 185, 129, 0.1)';
            box.style.borderLeftColor = '#10b981';
            icon.innerText = '🟢';
            title.innerText = 'AKSES DITERIMA (PORTAL TERBUKA)';
            title.style.color = '#047857';
            msg.innerText = data.message + ' Palang portal naik selama 5 detik.';
        } else {
            box.style.background = 'rgba(239, 68, 68, 0.1)';
            box.style.borderLeftColor = '#ef4444';
            icon.innerText = '🔴';
            title.innerText = 'AKSES DITOLAK (PORTAL TERKUNCI)';
            title.style.color = '#b91c1c';
            msg.innerText = data.message;
        }
    })
    .catch(err => {
        box.style.background = 'rgba(239, 68, 68, 0.1)';
        icon.innerText = '⚠️';
        title.innerText = 'Terjadi Kesalahan';
        msg.innerText = 'Gagal menghubungi simulator portal.';
    });
}
</script>
@endsection
