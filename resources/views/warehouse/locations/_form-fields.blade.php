@php
    $current = $warehouse ?? null;
@endphp
<div class="mb-3">
    <label for="name" class="form-label">Nama <span class="text-danger">*</span></label>
    <input type="text" class="form-control @error('name') is-invalid @enderror"
           id="name" name="name" value="{{ old('name', $current->name ?? '') }}"
           placeholder="Contoh: Gudang Utama Sukarami" required>
    @error('name')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<div class="mb-3">
    <label for="internal_id" class="form-label">ID Internal <span class="text-danger">*</span></label>
    <input type="text" class="form-control @error('internal_id') is-invalid @enderror"
           id="internal_id" name="internal_id" value="{{ old('internal_id', $current->internal_id ?? '') }}"
           placeholder="Contoh: GUD-SKR" required>
    @error('internal_id')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<div class="mb-3">
    <label for="tipe_lokasi" class="form-label">Tipe lokasi penyimpanan <span class="text-danger">*</span></label>
    <select class="form-select @error('tipe_lokasi') is-invalid @enderror" id="tipe_lokasi" name="tipe_lokasi" required>
        <option value="">Pilih tipe</option>
        @foreach(\App\Models\Warehouse::TIPE_LOKASI as $value => $label)
            <option value="{{ $value }}" {{ old('tipe_lokasi', $current->tipe_lokasi ?? 'gudang') === $value ? 'selected' : '' }}>{{ $label }}</option>
        @endforeach
    </select>
    @error('tipe_lokasi')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<div class="mb-3">
    <label for="description" class="form-label">Deskripsi</label>
    <textarea class="form-control @error('description') is-invalid @enderror"
              id="description" name="description" rows="3"
              placeholder="Contoh: Lokasi utama untuk penyimpanan benih padi dan palawija">{{ old('description', $current->description ?? '') }}</textarea>
    @error('description')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<div class="mb-3">
    <label for="responsible_person_id" class="form-label">Penanggung Jawab</label>
    <select class="form-select @error('responsible_person_id') is-invalid @enderror"
            id="responsible_person_id" name="responsible_person_id">
        <option value="">-- Pilih User --</option>
        @foreach($users as $user)
            <option value="{{ $user->user_id }}" {{ old('responsible_person_id', $current->responsible_person_id ?? auth()->id()) == $user->user_id ? 'selected' : '' }}>
                {{ $user->name }}
            </option>
        @endforeach
    </select>
    @error('responsible_person_id')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>
