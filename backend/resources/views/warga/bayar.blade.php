@extends('layouts.app')

@section('title', 'Bayar Iuran')
@section('page_title', 'Bayar Iuran')
@section('page_subtitle', 'Unggah bukti transfer pembayaran iuran warga')

@section('content')
@if ($tagihanMenunggu->isNotEmpty())
<div class="card-rt mb-4" style="border-color:var(--color-accent);">
    <div class="section-title mb-2"><span class="dot"></span> Pembayaran Menunggu Konfirmasi</div>
    <div class="table-responsive">
        <table class="table-rt mb-0">
            <thead><tr><th>Periode</th><th>Nominal</th><th>Status</th></tr></thead>
            <tbody>
            @foreach ($tagihanMenunggu as $tm)
                <tr>
                    <td>{{ periode_label($tm->periode) }}</td>
                    <td>{{ format_rupiah($tm->total_tagihan) }}</td>
                    <td>{!! badge_status($tm->status) !!}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

<div class="row g-4">
    <div class="col-lg-6">
        <div class="card-rt h-100">
            <div class="section-title"><span class="dot"></span> Formulir Pembayaran Transfer</div>

            @if ($tagihanBelumBayar->isEmpty())
                <div class="empty-state">Tidak ada tagihan yang belum dibayar saat ini. Semua lunas! 🎉</div>
            @else
                <form action="{{ route('warga.bayar.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label-rt">Pilih Periode Tagihan</label>
                        <select name="tagihan_id" class="form-control-rt" required>
                            @foreach ($tagihanBelumBayar as $tb)
                                <option value="{{ $tb->id }}">{{ periode_label($tb->periode) }} — {{ format_rupiah($tb->total_tagihan) }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label-rt">Unggah Bukti Transfer (JPG, PNG, PDF maks. 3MB)</label>
                        <input type="file" name="bukti_transfer" class="form-control-rt" accept=".jpg,.jpeg,.png,.pdf" required>
                    </div>

                    <div class="mb-4">
                        <label class="form-label-rt">Catatan Tambahan (Opsional)</label>
                        <input type="text" name="keterangan" class="form-control-rt" placeholder="Contoh: Transfer via BCA a.n. Budi">
                    </div>

                    <button type="submit" class="btn-rt-primary w-100" style="padding:11px;">Kirim Bukti Pembayaran</button>
                </form>
            @endif
        </div>
    </div>

    <div class="col-lg-6">
        <div class="card-rt h-100">
            <div class="section-title"><span class="dot"></span> Informasi Rekening Kas RT</div>
            <div class="p-3 rounded border mb-3" style="background:#f8fafc;">
                <div class="text-muted-rt" style="font-size:12px;">Bank Tujuan:</div>
                <div class="fw-bold" style="font-size:16px;">Bank BCA</div>
                <div class="my-1"><code>123-456-7890</code></div>
                <div style="font-size:13px;">a.n. <b>Kas RT 05 Rukun Warga</b></div>
            </div>
            <div style="font-size:13px; line-height:1.6;" class="text-muted-rt">
                <p class="mb-2"><b>Panduan Pembayaran:</b></p>
                <ol class="ps-3 mb-0">
                    <li>Transfer sesuai nominal total tagihan periode Anda.</li>
                    <li>Simpan resi / tangkapan layar (screenshot) bukti transfer.</li>
                    <li>Unggah melalui form di sebelah kiri.</li>
                    <li>Bendahara akan memverifikasi dan status tagihan otomatis berubah menjadi <b>Lunas</b>.</li>
                </ol>
            </div>
        </div>
    </div>
</div>
@endsection
