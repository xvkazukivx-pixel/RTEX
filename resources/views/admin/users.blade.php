@extends('layouts.app')

@section('title', 'Kelola Akun')
@section('page_title', 'Kelola Akun')
@section('page_subtitle', 'Manajemen akun login & hak akses peran')

@section('content')
<div class="card-rt mb-4">
    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
        <div class="section-title mb-0"><span class="dot"></span> Daftar Pengguna ({{ $daftarUser->count() }})</div>
        <button class="btn-rt-primary" data-bs-toggle="modal" data-bs-target="#modalTambahUser">+ Tambah Akun</button>
    </div>

    <div class="table-responsive">
        <table class="table-rt">
            <thead>
                <tr><th>Nama</th><th>Username</th><th>Peran</th><th>Warga Terkait</th><th></th></tr>
            </thead>
            <tbody>
            @foreach ($daftarUser as $u)
                <tr>
                    <td><strong>{{ $u->nama }}</strong></td>
                    <td><code>{{ $u->username }}</code></td>
                    <td>
                        @if ($u->role === 'super_admin')
                            <span class="badge text-bg-danger">Super Admin</span>
                        @elseif (in_array($u->role, ['rt', 'ketua'], true))
                            <span class="badge text-bg-warning text-dark">Ketua RT</span>
                        @elseif (in_array($u->role, ['bendahara_rt', 'bendahara', 'admin'], true))
                            <span class="badge text-bg-primary">Bendahara RT</span>
                        @elseif ($u->role === 'sekretaris_rt')
                            <span class="badge text-bg-info text-white" style="background-color:#0284c7!important;">Sekretaris RT</span>
                        @elseif ($u->role === 'rw')
                            <span class="badge text-bg-info">Ketua RW</span>
                        @elseif ($u->role === 'bendahara_rw')
                            <span class="badge text-bg-primary" style="background-color:#0d9488!important;">Bendahara RW</span>
                        @elseif ($u->role === 'sekretaris_rw')
                            <span class="badge text-bg-secondary" style="background-color:#6366f1!important;">Sekretaris RW</span>
                        @else
                            <span class="badge text-bg-secondary">Warga</span>
                        @endif
                    </td>
                    <td>{{ $u->warga->nama ?? '-' }}</td>
                    <td class="text-nowrap text-end">
                        <button class="btn-rt-outline btn-sm-edit btn-reset-pass"
                            data-bs-toggle="modal" data-bs-target="#modalResetPass"
                            data-id="{{ $u->id }}"
                            data-username="{{ $u->username }}">Reset Password</button>

                        @if ($u->id !== auth()->id())
                        <form action="{{ route('admin.users.destroy', $u->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus akun ini?');">
                            @csrf
                            <button type="submit" class="btn-rt-outline btn-sm-edit" style="color:var(--color-danger); border-color:#eecfcd;">Hapus</button>
                        </form>
                        @endif
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Tambah Akun -->
<div class="modal fade" id="modalTambahUser" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content" style="border-radius:14px;">
      <form action="{{ route('admin.users.store') }}" method="POST">
        @csrf
        <div class="modal-header">
          <h5 class="modal-title" style="font-family:var(--font-display);">Tambah Akun Pengguna</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
            <div class="mb-3">
                <label class="form-label-rt">Nama Lengkap</label>
                <input type="text" name="nama" class="form-control-rt" required>
            </div>
            <div class="mb-3">
                <label class="form-label-rt">Username</label>
                <input type="text" name="username" class="form-control-rt" required>
            </div>
            <div class="mb-3">
                <label class="form-label-rt">Password</label>
                <input type="password" name="password" class="form-control-rt" placeholder="Min. 6 karakter" required>
            </div>
            <div class="mb-3">
                <label class="form-label-rt">Peran (Role)</label>
                <select name="role" id="roleSelect" class="form-control-rt" required>
                    <option value="super_admin">Super Admin (Akses Penuh)</option>
                    <option value="rt">Ketua RT (Persetujuan & Monitoring RT)</option>
                    <option value="bendahara_rt">Bendahara RT (Keuangan & Iuran RT)</option>
                    <option value="sekretaris_rt">Sekretaris RT (Administrasi & Surat RT)</option>
                    <option value="rw">Ketua RW (Pengawasan & Rekap RW)</option>
                    <option value="bendahara_rw">Bendahara RW (Keuangan & Rekap RW)</option>
                    <option value="sekretaris_rw">Sekretaris RW (Administrasi RW)</option>
                    <option value="warga">Warga</option>
                </select>
            </div>
            <div class="mb-3" id="wargaSelectWrapper" style="display:none;">
                <label class="form-label-rt">Hubungkan ke Data Warga</label>
                <select name="warga_id" class="form-control-rt">
                    <option value="">— Pilih Warga —</option>
                    @foreach ($wargaTanpaAkun as $w)
                        <option value="{{ $w->id }}">{{ $w->nama }} (Rumah: {{ $w->no_rumah ?: '-' }})</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn-rt-outline" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn-rt-primary">Simpan Akun</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Modal Reset Password -->
<div class="modal fade" id="modalResetPass" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content" style="border-radius:14px;">
      <form id="formResetPass" method="POST">
        @csrf
        <div class="modal-header">
          <h5 class="modal-title" style="font-family:var(--font-display);">Reset Password</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
            <p style="font-size:13px;">Ganti password untuk akun: <b id="resetUsername"></b></p>
            <div class="mb-3">
                <label class="form-label-rt">Password Baru</label>
                <input type="password" name="password_baru" class="form-control-rt" placeholder="Min. 6 karakter" required>
            </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn-rt-outline" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn-rt-primary">Simpan Password</button>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
const roleSelect = document.getElementById('roleSelect');
const wargaWrapper = document.getElementById('wargaSelectWrapper');
if (roleSelect && wargaWrapper) {
    roleSelect.addEventListener('change', () => {
        wargaWrapper.style.display = roleSelect.value === 'warga' ? 'block' : 'none';
    });
}

document.querySelectorAll('.btn-reset-pass').forEach(btn => {
    btn.addEventListener('click', () => {
        const id = btn.dataset.id;
        document.getElementById('formResetPass').action = "{{ url('admin/users/reset-password') }}/" + id;
        document.getElementById('resetUsername').textContent = btn.dataset.username;
    });
});
</script>
@endpush
