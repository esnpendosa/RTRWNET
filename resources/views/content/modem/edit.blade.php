@extends('layouts/contentNavbarLayout')

@section('title', 'Edit Modem - ' . $modem->nama)

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card border-0 shadow-sm" style="border-radius:16px;">
            <div class="card-header bg-transparent border-bottom py-3">
                <h5 class="mb-0"><i class="bx bx-edit text-primary me-2"></i>Edit Modem</h5>
                <small class="text-muted">{{ $modem->nama }}</small>
            </div>
            <div class="card-body p-4">
                <form action="{{ route('modem.update', $modem) }}" method="POST" enctype="multipart/form-data">
                    @csrf @method('PUT')

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Merek <span class="text-danger">*</span></label>
                            <input type="text" name="merek" class="form-control @error('merek') is-invalid @enderror"
                                   value="{{ old('merek', $modem->merek) }}" placeholder="e.g. Huawei, ZTE">
                            @error('merek')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Model <span class="text-danger">*</span></label>
                            <input type="text" name="model" class="form-control @error('model') is-invalid @enderror"
                                   value="{{ old('model', $modem->model) }}" placeholder="e.g. HG8245H5">
                            @error('model')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-8">
                            <label class="form-label fw-semibold">Nama Lengkap <span class="text-danger">*</span></label>
                            <input type="text" name="nama" class="form-control @error('nama') is-invalid @enderror"
                                   value="{{ old('nama', $modem->nama) }}">
                            @error('nama')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">IP Address</label>
                            <input type="text" name="ip_address" class="form-control @error('ip_address') is-invalid @enderror"
                                   value="{{ old('ip_address', $modem->ip_address) }}" placeholder="e.g. 192.168.1.1">
                            @error('ip_address')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Deskripsi</label>
                            <textarea name="deskripsi" class="form-control" rows="3">{{ old('deskripsi', $modem->deskripsi) }}</textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Spesifikasi Teknis</label>
                            <textarea name="spesifikasi" class="form-control" rows="5">{{ old('spesifikasi', $modem->spesifikasi) }}</textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Foto Modem</label>
                            @if($modem->image_path)
                            <div class="mb-2">
                                <img src="{{ asset('storage/' . $modem->image_path) }}"
                                     style="max-height:150px;border-radius:10px;border:1px solid #e8eaed;" alt="Current">
                                <small class="d-block text-muted mt-1">Gambar saat ini. Upload baru untuk mengganti.</small>
                            </div>
                            @endif
                            <input type="file" name="image" class="form-control @error('image') is-invalid @enderror"
                                   accept="image/*" id="imageInput">
                            @error('image')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            <div id="imagePreview" class="mt-2" style="display:none;">
                                <img id="previewImg" style="max-height:150px;border-radius:10px;border:1px solid #e8eaed;">
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="is_active" value="1"
                                       id="is_active" {{ old('is_active', $modem->is_active) ? 'checked' : '' }}>
                                <label class="form-check-label fw-semibold" for="is_active">Tampilkan di katalog</label>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex gap-2 mt-4">
                        <button type="submit" class="btn btn-primary">
                            <i class="bx bx-save me-1"></i>Update Modem
                        </button>
                        <a href="{{ route('modem.admin.index') }}" class="btn btn-outline-secondary">Batal</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@section('page-script')
<script>
document.getElementById('imageInput').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (!file) return;
    const reader = new FileReader();
    reader.onload = function(ev) {
        document.getElementById('previewImg').src = ev.target.result;
        document.getElementById('imagePreview').style.display = 'block';
    };
    reader.readAsDataURL(file);
});
</script>
@endsection
