@extends('layouts.app')
@section('title', 'รายละเอียดสัญญา')

@section('content')
@php
    $rental = $contract->rental;
    $tenant = $rental?->tenant;
    $room = $rental?->room;
    $statusColor = match($contract->status) {
        'active' => 'success',
        'expired' => 'secondary',
        'terminated' => 'danger',
        default => 'secondary',
    };
    $documents = [
        [
            'key' => 'contract',
            'title' => 'ไฟล์สัญญา PDF',
            'subtitle' => 'เอกสารสัญญาแบบดิจิทัล',
            'path' => $contract->file_path,
            'icon' => 'bi-file-pdf',
            'preview' => 'pdf',
        ],
        [
            'key' => 'id-card',
            'title' => 'สำเนาบัตรประชาชน',
            'subtitle' => 'ไฟล์บัตรประชาชนของผู้เช่า',
            'path' => $contract->tenant_id_card_copy_path,
            'icon' => 'bi-person-vcard',
            'preview' => in_array(strtolower(pathinfo($contract->tenant_id_card_copy_path ?? '', PATHINFO_EXTENSION)), ['jpg', 'jpeg', 'png', 'webp']) ? 'image' : 'pdf',
        ],
    ];
    foreach ($contract->paper_contract_images as $index => $paperPath) {
        $documents[] = [
            'key' => 'paper',
            'index' => $index,
            'title' => 'สัญญาตัวจริง หน้า ' . ($index + 1),
            'subtitle' => 'รูปเอกสารกระดาษที่เซ็นจริง',
            'path' => $paperPath,
            'icon' => 'bi-file-earmark-image',
            'preview' => 'image',
        ];
    }
@endphp

<div class="page-header">
    <div class="d-flex align-items-center gap-2">
        <a href="{{ route('contracts.index') }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-arrow-left"></i></a>
        <div>
            <h1>สัญญา {{ $contract->contract_number }}</h1>
            <div class="text-muted small">
                ห้อง {{ $room?->room_number ?? '-' }} · {{ $tenant?->name ?? 'ไม่ระบุผู้เช่า' }}
            </div>
        </div>
    </div>
    <span class="badge bg-{{ $statusColor }}">{{ $contract->status_label }}</span>
</div>

<div class="row g-3">
    <div class="col-md-5">
        <div class="card detail-card">
            <div class="card-header">
                <h5 class="title"><span class="icon"><i class="bi bi-file-earmark-text"></i></span>ข้อมูลสัญญา</h5>
            </div>
            <div class="card-body p-0">
                <dl class="detail-list">
                    <div class="detail-row">
                        <dt><i class="bi bi-hash"></i>เลขที่</dt>
                        <dd class="value-strong">{{ $contract->contract_number }}</dd>
                    </div>
                    <div class="detail-row">
                        <dt><i class="bi bi-door-open"></i>ห้องพัก</dt>
                        <dd>{{ $room?->room_number ?? '-' }}</dd>
                    </div>
                    <div class="detail-row">
                        <dt><i class="bi bi-person"></i>ผู้เช่า</dt>
                        <dd>{{ $tenant?->name ?? '-' }}</dd>
                    </div>
                    <div class="detail-row">
                        <dt><i class="bi bi-telephone"></i>โทร</dt>
                        <dd>{{ $tenant?->phone ?? '-' }}</dd>
                    </div>
                    <div class="detail-row">
                        <dt><i class="bi bi-calendar-check"></i>วันเริ่ม</dt>
                        <dd>{{ $contract->start_date->format('d/m/Y') }}</dd>
                    </div>
                    <div class="detail-row">
                        <dt><i class="bi bi-calendar-x"></i>วันหมดอายุ</dt>
                        <dd>{{ $contract->end_date->format('d/m/Y') }}</dd>
                    </div>
                    <div class="detail-row">
                        <dt><i class="bi bi-hourglass-split"></i>คงเหลือ</dt>
                        <dd>
                            @if($contract->days_until_expiry < 0)
                                หมดอายุแล้ว
                            @else
                                {{ $contract->days_until_expiry }} วัน
                            @endif
                        </dd>
                    </div>
                </dl>
            </div>
        </div>
    </div>

    <div class="col-md-7">
        <div class="card detail-card">
            <div class="card-header">
                <h5 class="title"><span class="icon"><i class="bi bi-pen"></i></span>สถานะการลงนาม</h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="card" style="box-shadow:none">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <strong>ผู้เช่า</strong>
                                    <span class="badge bg-{{ $contract->tenant_signed_at ? 'success' : 'secondary' }}">
                                        {{ $contract->tenant_signed_at ? 'ลงนามแล้ว' : 'รอลงนาม' }}
                                    </span>
                                </div>
                                <div class="text-muted small mb-3">
                                    {{ $contract->tenant_signed_at?->format('d/m/Y H:i') ?? 'ยังไม่มีเวลาลงนาม' }}
                                </div>
                                @unless($contract->tenant_signed_at)
                                <form method="POST" action="{{ route('contracts.sign', $contract) }}">
                                    @csrf
                                    <input type="hidden" name="type" value="tenant">
                                    <button class="btn btn-primary btn-sm w-100"><i class="bi bi-check2 me-1"></i>บันทึกลายเซ็นผู้เช่า</button>
                                </form>
                                @endunless
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card" style="box-shadow:none">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <strong>เจ้าของ</strong>
                                    <span class="badge bg-{{ $contract->owner_signed_at ? 'success' : 'secondary' }}">
                                        {{ $contract->owner_signed_at ? 'ลงนามแล้ว' : 'รอลงนาม' }}
                                    </span>
                                </div>
                                <div class="text-muted small mb-3">
                                    {{ $contract->owner_signed_at?->format('d/m/Y H:i') ?? 'ยังไม่มีเวลาลงนาม' }}
                                </div>
                                @unless($contract->owner_signed_at)
                                <form method="POST" action="{{ route('contracts.sign', $contract) }}">
                                    @csrf
                                    <input type="hidden" name="type" value="owner">
                                    <button class="btn btn-primary btn-sm w-100"><i class="bi bi-check2 me-1"></i>บันทึกลายเซ็นเจ้าของ</button>
                                </form>
                                @endunless
                            </div>
                        </div>
                    </div>
                </div>

                @if($contract->terms)
                <div class="mt-3">
                    <label class="form-label">เงื่อนไขสัญญา</label>
                    <div style="padding:1rem;border:1px solid rgba(0,44,44,.08);border-radius:8px;background:#fff;white-space:pre-line">{{ $contract->terms }}</div>
                </div>
                @endif

                <div class="mt-3">
                    <label class="form-label">เอกสารแนบ</label>
                    <div class="contract-doc-grid">
                        @foreach($documents as $document)
                            @if($document['path'])
                            <div class="contract-doc-card">
                                @php
                                    $documentParams = [
                                        'contract' => $contract,
                                        'type' => $document['key'],
                                    ];

                                    if (array_key_exists('index', $document)) {
                                        $documentParams['index'] = $document['index'];
                                    }

                                    $documentUrl = route('contracts.documents.show', $documentParams);
                                @endphp
                                <a href="{{ $documentUrl }}" target="_blank" class="contract-doc-preview">
                                    @if($document['preview'] === 'image')
                                        <img src="{{ $documentUrl }}" alt="{{ $document['title'] }}">
                                    @else
                                        <span><i class="bi {{ $document['icon'] }}"></i></span>
                                    @endif
                                </a>
                                <div class="contract-doc-body">
                                    <div>
                                        <h6>{{ $document['title'] }}</h6>
                                        <p>{{ $document['subtitle'] }}</p>
                                    </div>
                                    <a href="{{ $documentUrl }}" class="btn btn-outline-secondary btn-sm" target="_blank">
                                        <i class="bi bi-eye me-1"></i>เปิดดูไฟล์
                                    </a>
                                </div>
                            </div>
                            @endif
                        @endforeach
                    </div>
                    @unless($contract->file_path || $contract->tenant_id_card_copy_path || count($contract->paper_contract_images))
                    <span class="text-muted small">ยังไม่มีเอกสารแนบ</span>
                    @endunless
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
