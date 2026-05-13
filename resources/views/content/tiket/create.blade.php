@extends('layouts/contentNavbarLayout')

@section('title', 'Buat Tiket Gangguan')

@section('content')
<h4 class="fw-bold py-3 mb-4"><span class="text-muted fw-light">Tiket /</span> Buat Baru</h4>

<div class="card">
  <div class="card-body">
    <form action="{{ route('tiket.store') }}" method="POST">
      @csrf
      <div class="mb-3">
        <label class="form-label">Pelanggan</label>
        <select name="id_pelanggan" id="pelanggan_select" class="form-select" required>
          <option value="">-- Pilih Pelanggan --</option>
          @foreach($pelanggan as $p)
            <option value="{{ $p->id_pelanggan }}" data-priority="{{ $p->prioritas_label }}">{{ $p->id_pelanggan }} - {{ $p->nama_pelanggan }} ({{ $p->prioritas_label }})</option>
          @endforeach
        </select>
      </div>
      <div class="mb-3">
        <label class="form-label">Keluhan</label>
        <textarea name="keluhan" class="form-control" rows="3" placeholder="Contoh: Koneksi terputus, Sinyal lemah" required></textarea>
      </div>
      <div class="mb-3">
        <label class="form-label">Prioritas</label>
        <select name="prioritas" id="prioritas_select" class="form-select" required>
          <option value="Low">Low</option>
          <option value="Medium">Medium</option>
          <option value="High">High</option>
        </select>
        <small class="text-muted">Prioritas otomatis terisi berdasarkan hasil klasifikasi KNN terakhir.</small>
      </div>
      <div class="mb-3">
        <label class="form-label">Assign Teknisi (Opsional)</label>
        <select name="id_teknisi" id="teknisi_select" class="form-select">
          <option value="">-- Pilih Teknisi --</option>
          @foreach($teknisi as $t)
            <option value="{{ $t->id_teknisi }}">{{ $t->nama_teknisi }}</option>
          @endforeach
        </select>
      </div>
      <button type="submit" class="btn btn-primary">Buat Tiket</button>
      <a href="{{ route('tiket.index') }}" class="btn btn-label-secondary">Batal</a>
    </form>
  </div>
</div>

@endsection

@section('page-style')
<link href="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/css/tom-select.bootstrap5.min.css" rel="stylesheet">
@endsection

@section('page-script')
<script src="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/js/tom-select.complete.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    if (document.getElementById('pelanggan_select')) {
        new TomSelect("#pelanggan_select", {
            create: false,
            placeholder: "-- Pilih Pelanggan --"
        });
    }

    if (document.getElementById('teknisi_select')) {
        new TomSelect("#teknisi_select", {
            create: false,
            placeholder: "-- Pilih Teknisi --"
        });
    }

    const pelangganSelect = document.getElementById('pelanggan_select');
    if (pelangganSelect) {
        pelangganSelect.addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            if (selectedOption) {
                const priority = selectedOption.getAttribute('data-priority');
                if (priority && priority !== 'Unclassified') {
                    document.getElementById('prioritas_select').value = priority;
                }
            }
        });
    }
});
</script>
@endsection
