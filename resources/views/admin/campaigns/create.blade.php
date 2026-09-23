@extends('admin.layouts.app')

@section('page_title', 'Thêm chiến dịch')
@section('page_subtitle', 'Thiết lập chiến dịch khuyến mãi mới cho sản phẩm')

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
