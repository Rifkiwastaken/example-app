@php
    $indent = str_repeat('&nbsp;&nbsp;&nbsp;&nbsp;', $level);
    $icon = $level > 0 ? '<i class="fas fa-angle-right text-muted me-1"></i>' : '';
    $prefix = $level > 0 ? '└ ' : '';
    $percentage = $item->percentage;
    $color = $percentage >= 100 ? 'danger' : ($percentage >= 80 ? 'warning' : 'success');
@endphp
<tr>
    <td>{!! $indent !!}{!! $icon !!}<code>{{ $item->account_code }}</code></td>
    <td>{!! $indent . $prefix !!}{{ $item->description }}</td>
    <td class="text-end">{{ number_format($item->budgeted_amount, 0, ',', '.') }}</td>
    <td class="text-end">{{ number_format($item->realized_amount, 0, ',', '.') }}</td>
    <td class="text-center">
        <span class="badge bg-{{ $color }}">{{ number_format($percentage, 1) }}%</span>
    </td>
    <td class="text-end {{ $item->sisa_pagu < 0 ? 'text-danger' : '' }}">
        {{ number_format($item->sisa_pagu, 0, ',', '.') }}
    </td>
    <td class="text-center">
        <div class="btn-group btn-group-sm">
            @if($level == 0)
                <a href="{{ route('planning.budget.item.create', ['budget_id' => $item->budget_id, 'parent_id' => $item->id]) }}" 
                   class="btn btn-outline-primary" title="Tambah Sub-Item">
                    <i class="fas fa-plus"></i>
                </a>
            @endif
            <a href="{{ route('planning.budget.item.edit', $item) }}" class="btn btn-outline-warning" title="Edit">
                <i class="fas fa-edit"></i>
            </a>
            <form action="{{ route('planning.budget.item.destroy', $item) }}" method="POST" class="d-inline" 
                  onsubmit="return confirm('Apakah Anda yakin ingin menghapus mata anggaran ini?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-outline-danger" title="Hapus">
                    <i class="fas fa-trash"></i>
                </button>
            </form>
        </div>
    </td>
</tr>
@foreach($item->children as $child)
    @include('planning.budget.partials.tree-item', ['item' => $child, 'level' => $level + 1])
@endforeach


