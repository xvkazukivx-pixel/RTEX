@extends('layouts.app')

@section('title', 'Dashboard Bendahara')
@section('page_title', 'Dashboard')
@section('page_subtitle', 'Gambaran kas dan aktivitas warga — ' . periode_label($periode))

@section('content')
<div class="admin-dashboard">
    <section class="dashboard-hero" aria-labelledby="dashboardHeroTitle">
        <div class="dashboard-hero__copy">
            <div class="dashboard-kicker"><span></span> PUSAT KEUANGAN RT <b>/</b> {{ periode_label($periode) }}</div>
            <p class="dashboard-hero__overline">Ruang kendali bendahara</p>
            <h2 id="dashboardHeroTitle">Kas warga,<br><em>terbaca jelas.</em></h2>
            <p class="dashboard-hero__description">
                Pantau pembayaran, lihat tren pemasukan, dan tindak lanjuti bukti transfer dari satu tempat.
            </p>
            <div class="dashboard-hero__actions">
                <a href="{{ route('admin.konfirmasi') }}" class="dashboard-action dashboard-action--primary">
                    Periksa pembayaran <span aria-hidden="true">↗</span>
                </a>
                <a href="{{ route('admin.laporan') }}" class="dashboard-action dashboard-action--quiet">
                    Buka laporan <span aria-hidden="true">→</span>
                </a>
            </div>
        </div>

        <div class="dashboard-balance">
            <div class="dashboard-balance__topline">
                <span>Penerimaan terkonfirmasi</span>
                <span class="dashboard-live"><i></i> PERIODE AKTIF</span>
            </div>
            <div class="dashboard-balance__amount">{{ format_rupiah($kasBulanIni) }}</div>
            <div class="dashboard-balance__rule"></div>
            <div class="dashboard-balance__foot">
                <span>Kas masuk bulan ini</span>
                <strong>{{ periode_label($periode) }}</strong>
            </div>
            <div class="dashboard-balance__seal" aria-hidden="true">RT</div>
        </div>
    </section>

    @if ($totalTagihanPeriode < $totalWargaAktif)
        <section class="dashboard-notice" aria-label="Pengingat tagihan">
            <div class="dashboard-notice__icon" aria-hidden="true">!</div>
            <div class="dashboard-notice__copy">
                <strong>Tagihan {{ periode_label($periode) }} belum lengkap</strong>
                <span>
                    {{ $totalTagihanPeriode }} dari {{ $totalWargaAktif }} kepala keluarga memiliki tagihan periode ini.
                </span>
            </div>
            <form action="{{ route('admin.dashboard.generate') }}" method="POST">
                @csrf
                <button type="submit" class="dashboard-notice__button">Buat tagihan <span aria-hidden="true">→</span></button>
            </form>
        </section>
    @endif

    <section class="dashboard-metrics" aria-label="Ringkasan warga dan pembayaran">
        <article class="dashboard-metric dashboard-metric--red">
            <div class="dashboard-metric__top">
                <span class="dashboard-metric__label">Total keluarga</span>
                <span class="dashboard-metric__icon" aria-hidden="true">⌂</span>
            </div>
            <div class="dashboard-metric__value">{{ $totalWargaAktif }}</div>
            <p>Kepala keluarga aktif</p>
        </article>

        <article class="dashboard-metric dashboard-metric--blue">
            <div class="dashboard-metric__top">
                <span class="dashboard-metric__label">Kepala keluarga lunas</span>
                <span class="dashboard-metric__icon" aria-hidden="true">✓</span>
            </div>
            <div class="dashboard-metric__value">{{ $wargaLunas }}</div>
            <p>Pembayaran lunas periode ini</p>
        </article>

        <article class="dashboard-metric dashboard-metric--green">
            <div class="dashboard-metric__top">
                <span class="dashboard-metric__label">Kas masuk bulan ini</span>
                <span class="dashboard-metric__icon" aria-hidden="true">Rp</span>
            </div>
            <div class="dashboard-metric__value dashboard-metric__value--money">{{ format_rupiah($kasBulanIni) }}</div>
            <p>Sudah dikonfirmasi bendahara</p>
        </article>

        <article class="dashboard-metric dashboard-metric--orange">
            <div class="dashboard-metric__top">
                <span class="dashboard-metric__label">Saldo kas</span>
                <span class="dashboard-metric__icon" aria-hidden="true">▣</span>
            </div>
            <div class="dashboard-metric__value dashboard-metric__value--money">{{ format_rupiah($saldoKas) }}</div>
            <p>Akumulasi kas diterima</p>
        </article>

        <article class="dashboard-metric dashboard-metric--wine">
            <div class="dashboard-metric__top">
                <span class="dashboard-metric__label">Pembayaran menunggu</span>
                <span class="dashboard-metric__icon" aria-hidden="true">↗</span>
            </div>
            <div class="dashboard-metric__value">{{ $menunggu }}</div>
            <p>Bukti perlu dikonfirmasi</p>
        </article>

        <article class="dashboard-metric dashboard-metric--purple">
            <div class="dashboard-metric__top">
                <span class="dashboard-metric__label">Surat menunggu</span>
                <span class="dashboard-metric__icon" aria-hidden="true">✉</span>
            </div>
            <div class="dashboard-metric__value">{{ $suratMenunggu }}</div>
            <p>Pengajuan belum diproses</p>
        </article>

        <article class="dashboard-metric dashboard-metric--teal">
            <div class="dashboard-metric__top">
                <span class="dashboard-metric__label">Kegiatan mendatang</span>
                <span class="dashboard-metric__icon" aria-hidden="true">▣</span>
            </div>
            <div class="dashboard-metric__value">{{ $kegiatanMenunggu }}</div>
            <p>Kegiatan menunggu persetujuan</p>
        </article>
    </section>

    <div class="dashboard-insights">
        <section class="dashboard-panel dashboard-trend" aria-labelledby="dashboardTrendTitle">
            <div class="dashboard-panel__heading">
                <div>
                    <span class="dashboard-panel__eyebrow">ARUS DANA</span>
                    <h3 id="dashboardTrendTitle">Pemasukan enam bulan</h3>
                    <p>Kas yang telah dikonfirmasi per periode</p>
                </div>
                <span class="dashboard-panel__tag">6 periode</span>
            </div>

            @php($maksKas = max(array_merge($kasGrafik, [1])))
            <div class="dashboard-chart" role="img" aria-label="Grafik pemasukan kas enam bulan terakhir">
                @foreach ($kasGrafik as $index => $nilai)
                    @php($tinggi = round(($nilai / $maksKas) * 148))
                    <div class="dashboard-chart__column">
                        <span class="dashboard-chart__amount">{{ format_rupiah($nilai) }}</span>
                        <div class="dashboard-chart__track">
                            <div
                                class="dashboard-chart__bar"
                                style="--bar-height: {{ max($tinggi, 4) }}px;"
                                title="{{ $periodeGrafik[$index] }}: {{ format_rupiah($nilai) }}"
                            ></div>
                        </div>
                        <span class="dashboard-chart__period">{{ $periodeGrafik[$index] }}</span>
                    </div>
                @endforeach
            </div>
        </section>

        <section class="dashboard-panel dashboard-compliance" aria-labelledby="dashboardComplianceTitle">
            <div class="dashboard-panel__heading">
                <div>
                    <span class="dashboard-panel__eyebrow">KEBIASAAN BAYAR</span>
                    <h3 id="dashboardComplianceTitle">Kepatuhan iuran</h3>
                    <p>Persentase tagihan yang lunas</p>
                </div>
            </div>
            <div class="dashboard-compliance__list">
                @foreach ($grafikKepatuhan as $item)
                    <div class="dashboard-compliance__item">
                        <div class="dashboard-compliance__label">
                            <span>{{ $item['periode'] }}</span>
                            <strong>{{ $item['persen'] }}%</strong>
                        </div>
                        <div class="dashboard-compliance__track">
                            <div style="width: {{ $item['persen'] }}%;"></div>
                        </div>
                    </div>
                @endforeach
            </div>
        </section>
    </div>

    <section class="dashboard-panel dashboard-review" aria-labelledby="dashboardReviewTitle">
        <div class="dashboard-panel__heading dashboard-review__heading">
            <div>
                <span class="dashboard-panel__eyebrow">TINDAKAN BERIKUTNYA</span>
                <h3 id="dashboardReviewTitle">Pembayaran perlu diperiksa</h3>
                <p>{{ $menunggu }} bukti pembayaran menunggu konfirmasi</p>
            </div>
            <a href="{{ route('admin.konfirmasi') }}" class="dashboard-panel__link">
                Lihat antrean <span aria-hidden="true">→</span>
            </a>
        </div>

        @if ($antrianKonfirmasi->isEmpty())
            <div class="dashboard-empty">
                <span class="dashboard-empty__mark" aria-hidden="true">✓</span>
                <div>
                    <strong>Semua sudah tertangani</strong>
                    <p>Belum ada bukti pembayaran yang menunggu konfirmasi.</p>
                </div>
            </div>
        @else
            <div class="dashboard-review__list">
                @foreach ($antrianKonfirmasi as $transaksi)
                    <article class="dashboard-review__row">
                        <span class="dashboard-review__mark" aria-hidden="true">↗</span>
                        <div class="dashboard-review__person">
                            <strong>{{ $transaksi->warga->nama ?? '-' }}</strong>
                            <span>{{ $transaksi->metode }} <i>·</i> {{ format_tanggal($transaksi->tanggal_bayar, true) }}</span>
                        </div>
                        <strong class="dashboard-review__amount">{{ format_rupiah($transaksi->jumlah_bayar) }}</strong>
                        <a
                            href="{{ route('admin.konfirmasi') }}"
                            class="dashboard-review__action"
                            aria-label="Periksa pembayaran {{ $transaksi->warga->nama ?? '' }}"
                        >
                            Periksa <span aria-hidden="true">→</span>
                        </a>
                    </article>
                @endforeach
            </div>
        @endif
    </section>
</div>
@endsection
