<div class="d-flex justify-content-between mb-3">
    <h5 class="mb-0">Penugasan</h5>
    @if(empty($readonly) && $planting->canAddReports())
        <button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#modalNote">Tambah penugasan</button>
    @endif
</div>
<div class="table-responsive">
    <table class="table table-hover">
        <thead>
            <tr>
                <th>Judul tugas</th>
                <th>Deskripsi</th>
                <th>Dibuat oleh</th>
                <th>Tujuan penugasan</th>
                <th>Status</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($notes as $item)
                <tr>
                    <td>{{ $item->task_title ?: $item->title }}</td>
                    <td>{{ Str::limit($item->description, 80) ?: '-' }}</td>
                    <td>{{ $item->creator?->name ?: '-' }}</td>
                    <td>{{ $item->assignee?->name ?: '-' }}</td>
                    <td>
                        <span class="badge bg-{{ $item->status === 'selesai' ? 'success' : 'warning' }}">{{ $item->statusLabel() }}</span>
                    </td>
                    <td>
                        <button type="button" class="btn btn-sm btn-outline-info" data-bs-toggle="modal" data-bs-target="#detail-note-{{ $item->getKey() }}">Detail</button>
                        @if($item->status !== 'selesai' && $item->isAssignedTo(auth()->user()))
                            <form method="POST" action="{{ route('planting-locations.plantings.assignments.complete', [$plantingLocation, $planting, $item]) }}" class="d-inline">
                                @csrf
                                <button class="btn btn-sm btn-success" onclick="return confirm('Tandai tugas ini selesai?')">Selesaikan</button>
                            </form>
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" class="text-center text-muted">Belum ada penugasan.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="modal fade" id="modalNote" tabindex="-1">
    <div class="modal-dialog"><div class="modal-content">
        <form method="POST" action="{{ route('planting-locations.plantings.notes.store', [$plantingLocation, $planting]) }}" enctype="multipart/form-data">
            @csrf
            <div class="modal-header"><h5 class="modal-title">Tambah Penugasan</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Judul tugas <span class="text-danger">*</span></label>
                    <input type="text" name="task_title" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Deskripsi tugas</label>
                    <textarea name="description" class="form-control" rows="3"></textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label">Lampiran</label>
                    <input type="file" name="file" class="form-control">
                </div>
                <div class="mb-3">
                    <label class="form-label">Dibuat oleh</label>
                    <input type="text" class="form-control" value="{{ auth()->user()->name }}" readonly>
                </div>
                <div class="mb-0">
                    <label class="form-label">Tujuan penugasan <span class="text-danger">*</span></label>
                    <select name="intended_for" class="form-select" required>
                        <option value="">Pilih user</option>
                        @foreach($users as $user)
                            <option value="{{ $user->user_id }}">{{ $user->name }} ({{ $user->role }})</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-success">Simpan</button>
            </div>
        </form>
    </div></div>
</div>

@foreach($notes as $item)
<div class="modal fade" id="detail-note-{{ $item->getKey() }}" tabindex="-1">
    <div class="modal-dialog"><div class="modal-content">
        <div class="modal-header"><h5 class="modal-title">Detail Penugasan</h5><button class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body">
            <p><strong>Judul tugas:</strong> {{ $item->task_title ?: $item->title }}</p>
            <p><strong>Deskripsi:</strong> {{ $item->description ?: '-' }}</p>
            <p><strong>Dibuat oleh:</strong> {{ $item->creator?->name ?: '-' }}</p>
            <p><strong>Tujuan penugasan:</strong> {{ $item->assignee?->name ?: '-' }}</p>
            <p><strong>Status:</strong> {{ $item->statusLabel() }}</p>
            @if($item->completed_at)
                <p><strong>Selesai:</strong> {{ $item->completed_at->format('d M Y H:i') }} oleh {{ $item->completer?->name }}</p>
            @endif
            @if($item->file_path)
                <p><strong>Lampiran:</strong> <a href="{{ asset('storage/'.$item->file_path) }}" target="_blank">{{ $item->file_name ?: 'Unduh' }}</a></p>
            @endif
        </div>
    </div></div>
</div>
@endforeach
