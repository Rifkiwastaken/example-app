@extends('layouts.app')

@section('title', 'Edit Akun - SIBESTI')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">Edit Akun: {{ $user->name }}</h4>
    <a href="{{ route('users.index') }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left me-2"></i>Kembali
    </a>
</div>

<form action="{{ route('users.update', $user) }}" method="POST" enctype="multipart/form-data">
    @csrf
    @method('PUT')
    
    <!-- Informasi Akun Dasar -->
    <div class="card mb-4">
        <div class="card-header bg-white">
            <h5 class="mb-0">Informasi Akun</h5>
            <small class="text-muted">Data login dan akses pengguna.</small>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="name" class="form-label">Nama Akun <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror" 
                               id="name" name="name" value="{{ old('name', $user->name) }}" required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                        <input type="email" class="form-control @error('email') is-invalid @enderror" 
                               id="email" name="email" value="{{ old('email', $user->email) }}" required>
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="password" class="form-label">Password Baru</label>
                        <input type="password" class="form-control @error('password') is-invalid @enderror" 
                               id="password" name="password">
                        @error('password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">Kosongkan jika tidak ingin mengubah password. Minimal 8 karakter.</div>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="password_confirmation" class="form-label">Konfirmasi Password Baru</label>
                        <input type="password" class="form-control @error('password') is-invalid @enderror" 
                               id="password_confirmation" name="password_confirmation">
                    </div>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="role" class="form-label">Peran/Asosiasi Pengguna <span class="text-danger">*</span></label>
                        <select class="form-select @error('role') is-invalid @enderror" id="role" name="role" required>
                            <option value="">Pilih Peran</option>
                            @foreach($roles as $key => $label)
                                <option value="{{ $key }}" {{ old('role', $user->role) == $key ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                        @error('role')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">
                            <small>
                                <strong>Admin/Kepala Seksi:</strong> Akses penuh ke semua menu dan fitur<br>
                                <strong>Kepala Satuan Tugas:</strong> Akses fitur penanaman<br>
                                <strong>Petugas Gudang:</strong> Akses fitur gudang/stok<br>
                                <strong>Petugas BBI:</strong> Akses fitur penjualan<br>
                                <strong>Penangkar:</strong> Akses khusus untuk penangkar benih
                            </small>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="location_placement" class="form-label">Lokasi Penempatan</label>
                        <input type="text" class="form-control @error('location_placement') is-invalid @enderror" 
                               id="location_placement" name="location_placement" value="{{ old('location_placement', $user->location_placement) }}" 
                               placeholder="Contoh: Sukarami, Koto Baru, dll">
                        @error('location_placement')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">
                            Tuliskan lokasi tempat user akan ditugaskan. Jika diisi, akan mengabaikan pilihan lokasi di bawah.
                        </div>
                    </div>
                </div>
            </div>
            
        </div>
    </div>

    <!-- Foto Profil -->
    <div class="card mb-4">
        <div class="card-header bg-white">
            <h5 class="mb-0">Foto Profil</h5>
        </div>
        <div class="card-body">
            <div class="row align-items-center">
                <div class="col-md-3 text-center">
                    <div class="mb-3">
                        <div class="bg-light border rounded-circle d-flex align-items-center justify-content-center mb-2 mx-auto" 
                             style="width: 120px; height: 120px;" id="photoPreview">
                            @if($user->photo_path)
                                <img src="{{ Storage::url($user->photo_path) }}" class="rounded-circle" style="width: 120px; height: 120px; object-fit: cover;">
                            @else
                                <i class="fas fa-user fa-3x text-muted"></i>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="col-md-9">
                    <div class="mb-3">
                        <label for="photo" class="form-label">Foto Profil</label>
                        <input type="file" name="photo" id="photo" class="form-control @error('photo') is-invalid @enderror" 
                               accept="image/*" onchange="previewPhoto(this)">
                        @error('photo')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @else
                            <div class="form-text">Format JPG, PNG, maksimal 2 MB. Kosongkan jika tidak ingin mengubah foto.</div>
                        @enderror
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Informasi Dasar -->
    <div class="card mb-4">
        <div class="card-header bg-white">
            <h5 class="mb-0">Informasi Dasar</h5>
            <small class="text-muted">Lengkapi data utama dan status kontak.</small>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-6">
                    <label for="full_name" class="form-label">Nama Lengkap</label>
                    <input type="text" name="full_name" id="full_name" class="form-control @error('full_name') is-invalid @enderror" 
                           value="{{ old('full_name', $user->full_name) }}">
                    @error('full_name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-3">
                    <label for="status" class="form-label">Status</label>
                    <select name="status" id="status" class="form-select @error('status') is-invalid @enderror">
                        @foreach($statuses as $key => $label)
                            <option value="{{ $key }}" {{ old('status', $user->status) == $key ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('status')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-3">
                    <label for="contact_type" class="form-label">Tipe Kontak</label>
                    <select name="contact_type" id="contact_type" class="form-select @error('contact_type') is-invalid @enderror">
                        <option value="">Pilih Tipe Kontak</option>
                        @foreach($contactTypes as $key => $label)
                            <option value="{{ $key }}" {{ old('contact_type', $user->contact_type) === $key ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('contact_type')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-6">
                    <label for="organization" class="form-label">Instansi / Organisasi</label>
                    <input type="text" name="organization" id="organization" class="form-control @error('organization') is-invalid @enderror" 
                           value="{{ old('organization', $user->organization) }}">
                    @error('organization')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-4">
                    <label for="position" class="form-label">Jabatan / Posisi</label>
                    <input type="text" name="position" id="position" class="form-control @error('position') is-invalid @enderror" 
                           value="{{ old('position', $user->position) }}">
                    @error('position')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-2">
                    <label for="nip" class="form-label">NIP</label>
                    <input type="text" name="nip" id="nip" class="form-control @error('nip') is-invalid @enderror" 
                           value="{{ old('nip', $user->nip) }}">
                    @error('nip')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <div class="form-text">Opsional, untuk pegawai UPTD.</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Informasi Kontak & Alamat -->
    <div class="card mb-4">
        <div class="card-header bg-white">
            <h5 class="mb-0">Informasi Kontak & Alamat</h5>
            <small class="text-muted">Pastikan data kontak terbaru agar mudah dihubungi.</small>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-4">
                    <label for="primary_phone" class="form-label">Nomor Telepon (Utama)</label>
                    <input type="text" name="primary_phone" id="primary_phone" class="form-control @error('primary_phone') is-invalid @enderror" 
                           value="{{ old('primary_phone', $user->primary_phone) }}">
                    @error('primary_phone')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <div class="form-check mt-2">
                        <input type="checkbox" class="form-check-input" id="primary_phone_is_whatsapp" 
                               name="primary_phone_is_whatsapp" value="1" {{ old('primary_phone_is_whatsapp', $user->primary_phone_is_whatsapp) ? 'checked' : '' }}>
                        <label class="form-check-label" for="primary_phone_is_whatsapp">Tersambung ke WhatsApp</label>
                    </div>
                </div>
                <div class="col-md-4">
                    <label for="secondary_phone" class="form-label">Nomor Telepon (Lainnya)</label>
                    <input type="text" name="secondary_phone" id="secondary_phone" class="form-control @error('secondary_phone') is-invalid @enderror" 
                           value="{{ old('secondary_phone', $user->secondary_phone) }}">
                    @error('secondary_phone')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-4">
                    <label for="user_email" class="form-label">Email</label>
                    <input type="email" name="user_email" id="user_email" class="form-control @error('user_email') is-invalid @enderror" 
                           value="{{ old('user_email') }}">
                    @error('user_email')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <div class="form-text">Email tambahan (selain email login)</div>
                </div>
                <div class="col-12">
                    <label for="address" class="form-label">Alamat Lengkap</label>
                    <textarea name="address" id="address" rows="3" class="form-control @error('address') is-invalid @enderror">{{ old('address', $user->address) }}</textarea>
                    @error('address')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <div class="form-text">Tuliskan alamat domisili lengkap (Jalan, RT/RW, Nomor Rumah).</div>
                </div>
            </div>

            <hr class="my-4">

            <div class="row g-3">
                <div class="col-md-3">
                    <label for="province" class="form-label">Provinsi</label>
                    <input type="text" name="province" id="province" class="form-control @error('province') is-invalid @enderror" 
                           placeholder="Tuliskan provinsi" value="{{ old('province', $user->province) }}">
                    @error('province')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-3">
                    <label for="city" class="form-label">Kabupaten / Kota</label>
                    <input type="text" name="city" id="city" class="form-control @error('city') is-invalid @enderror" 
                           placeholder="Tuliskan kabupaten/kota" value="{{ old('city', $user->city) }}">
                    @error('city')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-3">
                    <label for="district" class="form-label">Kecamatan</label>
                    <input type="text" name="district" id="district" class="form-control @error('district') is-invalid @enderror" 
                           placeholder="Tuliskan kecamatan" value="{{ old('district', $user->district) }}">
                    @error('district')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-3">
                    <label for="village" class="form-label">Desa / Kelurahan</label>
                    <input type="text" name="village" id="village" class="form-control @error('village') is-invalid @enderror" 
                           placeholder="Tuliskan desa/kelurahan" value="{{ old('village', $user->village) }}">
                    @error('village')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </div>
    </div>

    <!-- Catatan Tambahan -->
    <div class="card mb-4">
        <div class="card-header bg-white">
            <h5 class="mb-0">Catatan Tambahan</h5>
        </div>
        <div class="card-body">
            <label for="notes" class="form-label">Catatan</label>
            <textarea name="notes" id="notes" rows="4" class="form-control @error('notes') is-invalid @enderror" 
                      placeholder="Contoh: Ahli dalam pengendalian hama, dapat dihubungi di luar jam kerja, dsb.">{{ old('notes', $user->notes) }}</textarea>
            @error('notes')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
    
    <div class="d-flex justify-content-end gap-2">
        <a href="{{ route('users.index') }}" class="btn btn-secondary">
            <i class="fas fa-times me-2"></i>Batal
        </a>
        <button type="submit" class="btn btn-success">
            <i class="fas fa-save me-2"></i>Update Akun
        </button>
    </div>
</form>

@push('scripts')
<script>
    function previewPhoto(input) {
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const preview = document.getElementById('photoPreview');
                preview.innerHTML = `<img src="${e.target.result}" class="rounded-circle" style="width: 120px; height: 120px; object-fit: cover;">`;
            };
            reader.readAsDataURL(input.files[0]);
        }
    }
</script>
@endpush
@endsection
