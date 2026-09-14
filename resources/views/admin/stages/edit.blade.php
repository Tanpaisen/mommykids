@extends('admin.layouts.app')

@section('page_title', 'Chỉnh sửa giai đoạn')
@section('page_subtitle', 'Cập nhật thông tin giai đoạn "' . $stage->name . '"')

@section('page_actions')
    <a
        href="{{ route('admin.stages.index') }}"
        class="px-4 py-2 rounded-xl border border-admin-border
               text-sm text-ink hover:bg-admin-bg transition"
    >
        ← Quay lại
    </a>
@endsection

@section('content')

    <form
        action="{{ route('admin.stages.update', $stage) }}"
        method="POST"
    >
        @csrf
        @method('PUT')

        <div class="card">

            <div class="border-b border-admin-border pb-4 mb-5">

                <h2 class="font-semibold text-ink text-base">
                    Thông tin giai đoạn
                </h2>

                <p class="text-sm text-ink-soft mt-1">
                    Thay đổi thông tin và trạng thái của giai đoạn.
                </p>

            </div>

            @include('admin.stages._form', ['stage' => $stage])

            <div class="flex justify-end gap-3 border-t border-admin-border mt-6 pt-5">

                <a
                    href="{{ route('admin.stages.index') }}"
                    class="px-5 py-2.5 rounded-xl border border-admin-border
                           text-sm text-ink hover:bg-admin-bg transition"
                >
                    Hủy
                </a>

                <button
                    type="submit"
                    class="btn-primary"
                >
                    Lưu thay đổi
                </button>

            </div>

        </div>

    </form>

@endsection