@extends('admin.layouts.app')

@section('page_title', 'Sửa chiến dịch')
@section('page_subtitle', 'Cập nhật thời gian, giá khuyến mãi và giới hạn chiến dịch')

@section('content')

<div class="mb-5">
    <a
        href="{{ route('admin.campaigns.index') }}"
        class="text-sm text-ink-soft hover:text-coral transition"
    >
        ← Quay lại danh sách chiến dịch
    </a>
</div>

@include('admin.campaigns._form')

@endsection
