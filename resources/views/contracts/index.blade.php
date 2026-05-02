@extends('layouts.app')
@section('title', 'สัญญาเช่า')

@section('content')
<div class="page-header">
    <h1><i class="bi bi-file-earmark-text me-2" style="color:#00A884"></i>สัญญาเช่า</h1>
    <a href="{{ route('contracts.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg"></i> สร้างสัญญาใหม่</a>
</div>

<div class="card" style="padding:0;overflow:hidden">
    <div class="table-wrapper" style="border:none;border-radius:0">
        <table class="data-table">
            <thead>
                <tr>
                    <th>เลขที่</th>
                    <th>ห้อง / ผู้เช่า</th>
                    <th>วันเริ่ม</th>
                    <th>วันหมด</th>
                    <th>เหลือ</th>
                    <th>สถานะ</th>
                    <th>ลายเซ็น</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($contracts as $contract)
                <tr>
                    <td style="font-size:.78rem;color:#888">{{ $contract->contract_number }}</td>
                    <td>
                        <div style="font-weight:700">{{ $contract->rental?->room?->room_number }}</div>
                        <div style="font-size:.78rem;color:#888">{{ $contract->rental?->tenant?->name }}</div>
                    </td>
                    <td style="font-size:.85rem">{{ $contract->start_date->format('d/m/Y') }}</td>
                    <td style="font-size:.85rem">{{ $contract->end_date->format('d/m/Y') }}</td>
                    <td>
                        @php $days = $contract->days_until_expiry; @endphp
                        @if($days < 0)
                        <span class="badge badge-secondary">หมดอายุ</span>
                        @elseif($days <= 7)
                        <span class="badge badge-danger">{{ $days }} วัน</span>
                        @elseif($days <= 30)
                        <span class="badge badge-warning">{{ $days }} วัน</span>
                        @else
                        <span style="font-size:.82rem;color:#888">{{ $days }} วัน</span>
                        @endif
                    </td>
                    <td>
                        @php $sc = match($contract->status) { 'active'=>'success','expired'=>'secondary','terminated'=>'danger', default=>'secondary' }; @endphp
                        <span class="badge badge-{{ $sc }}">{{ $contract->status_label }}</span>
                    </td>
                    <td>
                        <span title="ผู้เช่า">{{ $contract->tenant_signed_at ? '✅' : '⬜' }}</span>
                        <span title="เจ้าของ">{{ $contract->owner_signed_at  ? '✅' : '⬜' }}</span>
                    </td>
                    <td>
                        <a href="{{ route('contracts.show', $contract) }}" class="btn btn-ghost btn-sm"><i class="bi bi-eye"></i></a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" style="text-align:center;padding:3rem;color:#888">
                        <i class="bi bi-file-earmark-text" style="font-size:2rem;display:block;margin-bottom:.5rem;color:#ccc"></i>
                        ยังไม่มีสัญญา
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($contracts->hasPages())
    <div style="padding:1rem 1.25rem">{{ $contracts->withQueryString()->links() }}</div>
    @endif
</div>
@endsection
