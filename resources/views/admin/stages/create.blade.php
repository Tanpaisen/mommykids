@extends('admin.layouts.app')

@section('page_title', 'Thêm giai đoạn của bé')
@section('page_subtitle', 'Tạo một mốc phát triển mới để gắn với sản phẩm và bài viết')

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
        action="{{ route('admin.stages.store') }}"
        method="POST"
    >
        @csrf

        <div class="card">

            <div class="border-b border-admin-border pb-4 mb-5">
                <h2 class="font-semibold text-ink text-base">
                    Thông tin giai đoạn
                </h2>

                <p class="text-sm text-ink-soft mt-1">
                    Nhập thông tin về giai đoạn phát triển của mẹ hoặc bé.
                </p>
            </div>

            <fieldset @cannot('catalog.manage') disabled @endcannot>
                @include('admin.stages._form')
            </fieldset>

            <div class="flex justify-end gap-3 border-t border-admin-border mt-6 pt-5">

                <a
                    href="{{ route('admin.stages.index') }}"
                    class="px-5 py-2.5 rounded-xl border border-admin-border
                           text-sm text-ink hover:bg-admin-bg transition"
                >
                    @can('catalog.manage') Hủy @else Quay lại @endcan
                </a>

                @can('catalog.manage')
                    <button
                        type="submit"
                        class="btn-primary"
                    >
                        Lưu giai đoạn
                    </button>
                @endcan
            </div>

        </div>

    </form>

@endsection