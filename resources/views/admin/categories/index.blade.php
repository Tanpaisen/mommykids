@extends('admin.layouts.app')

@php
    $activeTab = request('tab', 'categories');
    $showCreateTagModal = request('create_tag') == 1;
@endphp

@section('page_title', 'Danh mục & Thuộc tính')
@section('page_subtitle', 'Quản lý danh mục sản phẩm và các tag thuộc tính')

@section('content')

    <div class="card overflow-hidden">

        {{-- =========================
            TABS
        ========================== --}}
        <div class="border-b border-admin-border px-5">
            <div class="flex items-center gap-8">

                <a
                    href="{{ route('admin.categories.index', ['tab' => 'categories']) }}"
                    class="relative py-4 text-sm font-semibold transition
                           {{ $activeTab === 'categories'
                                ? 'text-coral'
                                : 'text-ink-soft hover:text-ink' }}"
                >
                    <span class="inline-flex items-center gap-2">
                        📦 Danh mục sản phẩm
                    </span>

                    @if ($activeTab === 'categories')
                        <span class="absolute bottom-0 left-0 right-0 h-0.5 bg-coral rounded-full"></span>
                    @endif
                </a>

                <a
                    href="{{ route('admin.categories.index', ['tab' => 'tags']) }}"
                    class="relative py-4 text-sm font-semibold transition
                           {{ $activeTab === 'tags'
                                ? 'text-coral'
                                : 'text-ink-soft hover:text-ink' }}"
                >
                    <span class="inline-flex items-center gap-2">
                        🏷️ Thuộc tính / Tags
                    </span>

                    @if ($activeTab === 'tags')
                        <span class="absolute bottom-0 left-0 right-0 h-0.5 bg-coral rounded-full"></span>
                    @endif
                </a>

            </div>
        </div>


        {{-- =====================================================
            TAB DANH MỤC
        ====================================================== --}}
        @if ($activeTab === 'categories')

            <div class="p-5 border-b border-admin-border">

                <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">

                    <div>
                        <h2 class="font-semibold text-ink text-base">
                            Danh mục sản phẩm
                        </h2>

                        <p class="text-sm text-ink-soft mt-1">
                            Quản lý tên, slug, icon, ảnh, thứ tự và trạng thái hiển thị.
                        </p>
                    </div>

                    <div class="flex items-center gap-3">

                        <a
                            href="{{ route('admin.categories.trash') }}"
                            class="inline-flex items-center gap-2
                                   px-4 py-2.5 rounded-xl
                                   border border-admin-border
                                   bg-white text-sm font-semibold text-ink
                                   hover:border-coral hover:text-coral transition"
                        >
                            <span>🗑️</span>
                            <span>Thùng rác</span>

                            @if (($categoryTrashCount ?? 0) > 0)
                                <span
                                    class="inline-flex items-center justify-center
                                           min-w-[22px] h-[22px] px-1.5
                                           rounded-full bg-red-500
                                           text-white text-xs font-bold"
                                >
                                    {{ $categoryTrashCount }}
                                </span>
                            @endif
                        </a>

                        <button
                            type="button"
                            onclick="openCreateCategoryModal()"
                            class="btn-primary whitespace-nowrap"
                        >
                            + Thêm danh mục
                        </button>

                    </div>

                </div>

                <form
                    action="{{ route('admin.categories.index') }}"
                    method="GET"
                    class="mt-5 flex flex-col lg:flex-row gap-3"
                >
                    <input type="hidden" name="tab" value="categories">

                    <div class="flex-1">
                        <input
                            type="text"
                            name="category_search"
                            value="{{ request('category_search') }}"
                            placeholder="Tìm theo tên hoặc slug danh mục..."
                            class="w-full border border-admin-border rounded-xl
                                   px-4 py-2.5 text-sm text-ink
                                   outline-none focus:border-coral"
                        >
                    </div>

                    <div class="w-full lg:w-56">
                        <select
                            name="category_status"
                            class="w-full border border-admin-border rounded-xl
                                   px-4 py-2.5 text-sm text-ink
                                   outline-none focus:border-coral"
                        >
                            <option value="">Tất cả trạng thái</option>

                            <option
                                value="active"
                                @selected(request('category_status') === 'active')
                            >
                                Đang hiển thị
                            </option>

                            <option
                                value="inactive"
                                @selected(request('category_status') === 'inactive')
                            >
                                Đã ẩn
                            </option>
                        </select>
                    </div>

                    <div class="flex gap-2">

                        @if (
                            request()->filled('category_search')
                            || request()->filled('category_status')
                        )
                            <a
                                href="{{ route('admin.categories.index', ['tab' => 'categories']) }}"
                                class="px-4 py-2.5 rounded-xl border border-admin-border
                                       text-sm text-ink-soft hover:bg-admin-bg transition"
                            >
                                Làm mới
                            </a>
                        @endif

                        <button
                            type="submit"
                            class="px-5 py-2.5 rounded-xl bg-coral text-white
                                   text-sm font-semibold hover:opacity-90 transition"
                        >
                            Lọc
                        </button>

                    </div>

                </form>

            </div>


            <div class="overflow-x-auto">

                <table class="w-full min-w-[950px] text-sm">

                    <thead class="bg-admin-bg text-ink-soft text-xs uppercase tracking-wide">
                        <tr>
                            <th class="text-left px-5 py-3 font-semibold w-16">
                                STT
                            </th>

                            <th class="text-left px-5 py-3 font-semibold min-w-[270px]">
                                Danh mục
                            </th>

                            <th class="text-left px-5 py-3 font-semibold min-w-[200px]">
                                Slug
                            </th>

                            <th class="text-center px-5 py-3 font-semibold w-28">
                                Thứ tự
                            </th>

                            <th class="text-left px-5 py-3 font-semibold min-w-[170px]">
                                Trạng thái
                            </th>

                            <th class="text-right px-5 py-3 font-semibold min-w-[150px]">
                                Thao tác
                            </th>
                        </tr>
                    </thead>


                    <tbody class="divide-y divide-admin-border">

                        @forelse ($categories as $category)

                            <tr class="hover:bg-admin-bg/50 transition">

                                <td class="px-5 py-4 text-ink-soft">
                                    {{ $categories->firstItem() + $loop->index }}
                                </td>

                                <td class="px-5 py-4">

                                    <div class="flex items-center gap-3">

                                        <div
                                            class="w-11 h-11 rounded-xl bg-coral-light text-coral
                                                   flex items-center justify-center
                                                   text-lg shrink-0 overflow-hidden"
                                        >
                                            @if ($category->image)

                                                <img
                                                    src="{{ asset('storage/' . $category->image) }}"
                                                    alt="{{ $category->name }}"
                                                    class="w-full h-full object-cover"
                                                >

                                            @elseif ($category->icon)

                                                {{ $category->icon }}

                                            @else

                                                {{ strtoupper(mb_substr($category->name, 0, 1)) }}

                                            @endif
                                        </div>

                                        <div>
                                            <p class="font-semibold text-ink">
                                                {{ $category->name }}
                                            </p>

                                            <p class="text-xs text-ink-soft mt-0.5">
                                                ID #{{ $category->id }}
                                            </p>
                                        </div>

                                    </div>

                                </td>


                                <td class="px-5 py-4">
                                    <span
                                        class="inline-flex rounded-lg bg-admin-bg
                                               px-2.5 py-1 text-xs text-ink-soft"
                                    >
                                        {{ $category->slug }}
                                    </span>
                                </td>


                                <td class="px-5 py-4 text-center">
                                    <span
                                        class="inline-flex w-9 h-9
                                               items-center justify-center
                                               rounded-xl bg-admin-bg
                                               font-semibold text-ink"
                                    >
                                        {{ $category->sort_order }}
                                    </span>
                                </td>


                                <td class="px-5 py-4">

                                    @if ($category->is_active)

                                        <span
                                            class="inline-flex items-center gap-2
                                                   rounded-full bg-green-50
                                                   px-3 py-1.5 text-xs
                                                   font-semibold text-green-600"
                                        >
                                            <span class="w-2 h-2 rounded-full bg-green-500"></span>
                                            Đang hiển thị
                                        </span>

                                    @else

                                        <span
                                            class="inline-flex items-center gap-2
                                                   rounded-full bg-gray-100
                                                   px-3 py-1.5 text-xs
                                                   font-semibold text-gray-500"
                                        >
                                            <span class="w-2 h-2 rounded-full bg-gray-400"></span>
                                            Đã ẩn
                                        </span>

                                    @endif

                                </td>


                                <td class="px-5 py-4">

                                    <div class="flex justify-end gap-2">

                                        <a
                                            href="{{ route('admin.categories.edit', $category) }}"
                                            class="px-3.5 py-2 rounded-lg
                                                   border border-admin-border
                                                   text-sm text-ink
                                                   hover:border-coral
                                                   hover:text-coral transition"
                                        >
                                            Sửa
                                        </a>

                                        <button
                                            type="button"
                                            data-action="{{ route('admin.categories.destroy', $category) }}"
                                            data-name="{{ $category->name }}"
                                            onclick="openDeleteCategoryModal(this)"
                                            class="px-3.5 py-2 rounded-lg
                                                   bg-red-50 text-sm text-red-500
                                                   hover:bg-red-100 transition"
                                        >
                                            Xóa
                                        </button>

                                    </div>

                                </td>

                            </tr>

                        @empty

                            <tr>
                                <td colspan="6" class="px-5 py-20 text-center">

                                    <div
                                        class="mx-auto w-16 h-16
                                               rounded-2xl bg-coral-light
                                               flex items-center justify-center
                                               text-3xl"
                                    >
                                        📦
                                    </div>

                                    <p class="mt-4 font-semibold text-ink">
                                        Chưa có danh mục nào
                                    </p>

                                    <p class="mt-1 text-sm text-ink-soft">
                                        Tạo danh mục đầu tiên cho cửa hàng.
                                    </p>

                                    <button
                                        type="button"
                                        onclick="openCreateCategoryModal()"
                                        class="btn-primary mt-5"
                                    >
                                        + Thêm danh mục
                                    </button>

                                </td>
                            </tr>

                        @endforelse

                    </tbody>

                </table>

            </div>


            {{-- PAGINATION CATEGORY --}}
            @if ($categories->total() > 0)

                <div
                    class="flex flex-col sm:flex-row
                           sm:items-center sm:justify-between
                           gap-4 border-t border-admin-border
                           px-5 py-4"
                >

                    <p class="text-sm text-ink-soft">
                        Hiển thị
                        <strong class="text-ink">{{ $categories->firstItem() }}</strong>
                        -
                        <strong class="text-ink">{{ $categories->lastItem() }}</strong>
                        trong
                        <strong class="text-ink">{{ $categories->total() }}</strong>
                        danh mục
                    </p>

                    @if ($categories->hasPages())

                        <div class="flex gap-1">

                            @if ($categories->onFirstPage())

                                <span
                                    class="w-9 h-9 rounded-lg border border-admin-border
                                           flex items-center justify-center text-gray-300"
                                >
                                    ‹
                                </span>

                            @else

                                <a
                                    href="{{ $categories->previousPageUrl() }}"
                                    class="w-9 h-9 rounded-lg border border-admin-border
                                           flex items-center justify-center
                                           hover:border-coral hover:text-coral"
                                >
                                    ‹
                                </a>

                            @endif


                            @for ($page = 1; $page <= $categories->lastPage(); $page++)

                                @if ($page === $categories->currentPage())

                                    <span
                                        class="w-9 h-9 rounded-lg bg-coral text-white
                                               flex items-center justify-center font-semibold"
                                    >
                                        {{ $page }}
                                    </span>

                                @else

                                    <a
                                        href="{{ $categories->url($page) }}"
                                        class="w-9 h-9 rounded-lg border border-admin-border
                                               flex items-center justify-center
                                               hover:border-coral hover:text-coral"
                                    >
                                        {{ $page }}
                                    </a>

                                @endif

                            @endfor


                            @if ($categories->hasMorePages())

                                <a
                                    href="{{ $categories->nextPageUrl() }}"
                                    class="w-9 h-9 rounded-lg border border-admin-border
                                           flex items-center justify-center
                                           hover:border-coral hover:text-coral"
                                >
                                    ›
                                </a>

                            @else

                                <span
                                    class="w-9 h-9 rounded-lg border border-admin-border
                                           flex items-center justify-center text-gray-300"
                                >
                                    ›
                                </span>

                            @endif

                        </div>

                    @endif

                </div>

            @endif

        @endif



        {{-- =====================================================
            TAB TAGS
        ====================================================== --}}
        @if ($activeTab === 'tags')

            <div class="p-5 border-b border-admin-border">

                <div class="flex items-center justify-between gap-4">

                    <div>
                        <h2 class="font-semibold text-ink">
                            Thuộc tính / Tags
                        </h2>

                        <p class="mt-1 text-sm text-ink-soft">
                            Quản lý thuộc tính dùng để phân loại sản phẩm.
                        </p>
                    </div>

                    <div class="flex items-center gap-3">

                        <a
                            href="{{ route('admin.tags.trash') }}"
                            class="inline-flex items-center gap-2
                                   px-4 py-2.5 rounded-xl
                                   border border-admin-border
                                   bg-white text-sm font-semibold text-ink
                                   hover:border-coral hover:text-coral transition"
                        >
                            <span>🗑️</span>
                            <span>Thùng rác</span>

                            @if (($tagTrashCount ?? 0) > 0)
                                <span
                                    class="inline-flex items-center justify-center
                                           min-w-[22px] h-[22px] px-1.5
                                           rounded-full bg-red-500
                                           text-white text-xs font-bold"
                                >
                                    {{ $tagTrashCount }}
                                </span>
                            @endif
                        </a>

                        <a
                            href="{{ route('admin.categories.index', [
                                'tab' => 'tags',
                                'create_tag' => 1
                            ]) }}"
                            class="btn-primary whitespace-nowrap"
                        >
                            + Thêm thuộc tính
                        </a>

                    </div>

                </div>


                <form
                    action="{{ route('admin.categories.index') }}"
                    method="GET"
                    class="mt-5 flex flex-col lg:flex-row gap-3"
                >

                    <input type="hidden" name="tab" value="tags">

                    <input
                        type="text"
                        name="tag_search"
                        value="{{ request('tag_search') }}"
                        placeholder="Tìm theo tên hoặc slug thuộc tính..."
                        class="flex-1 border border-admin-border
                               rounded-xl px-4 py-2.5 text-sm
                               outline-none focus:border-coral"
                    >

                    <select
                        name="tag_type"
                        class="w-full lg:w-56
                               border border-admin-border
                               rounded-xl px-4 py-2.5 text-sm
                               outline-none focus:border-coral"
                    >
                        <option value="">Tất cả loại</option>

                        <option
                            value="attribute"
                            @selected(request('tag_type') === 'attribute')
                        >
                            Thuộc tính
                        </option>

                        <option
                            value="stage"
                            @selected(request('tag_type') === 'stage')
                        >
                            Giai đoạn
                        </option>

                        <option
                            value="brand"
                            @selected(request('tag_type') === 'brand')
                        >
                            Thương hiệu
                        </option>
                    </select>


                    @if (
                        request()->filled('tag_search')
                        || request()->filled('tag_type')
                    )

                        <a
                            href="{{ route('admin.categories.index', ['tab' => 'tags']) }}"
                            class="px-4 py-2.5 rounded-xl
                                   border border-admin-border
                                   text-sm text-ink-soft
                                   hover:bg-admin-bg transition"
                        >
                            Làm mới
                        </a>

                    @endif


                    <button
                        type="submit"
                        class="px-5 py-2.5 rounded-xl
                               bg-coral text-white
                               text-sm font-semibold"
                    >
                        Lọc
                    </button>

                </form>

            </div>


            <div class="overflow-x-auto">

                <table class="w-full min-w-[760px] text-sm">

                    <thead class="bg-admin-bg text-ink-soft text-xs uppercase">

                        <tr>
                            <th class="text-left px-5 py-3">STT</th>
                            <th class="text-left px-5 py-3">Tên thuộc tính</th>
                            <th class="text-left px-5 py-3">Slug</th>
                            <th class="text-left px-5 py-3">Loại</th>
                            <th class="text-right px-5 py-3">Thao tác</th>
                        </tr>

                    </thead>


                    <tbody class="divide-y divide-admin-border">

                        @forelse ($tags as $tag)

                            <tr class="hover:bg-admin-bg/50">

                                <td class="px-5 py-4 text-ink-soft">
                                    {{ $tags->firstItem() + $loop->index }}
                                </td>

                                <td class="px-5 py-4 font-semibold text-ink">
                                    {{ $tag->name }}
                                </td>

                                <td class="px-5 py-4">

                                    <span class="bg-admin-bg rounded-lg px-2.5 py-1 text-xs">
                                        {{ $tag->slug }}
                                    </span>

                                </td>

                                <td class="px-5 py-4">

                                    @if ($tag->type === 'attribute')

                                        <span class="bg-blue-50 text-blue-600 rounded-full px-3 py-1 text-xs font-semibold">
                                            Thuộc tính
                                        </span>

                                    @elseif ($tag->type === 'stage')

                                        <span class="bg-purple-50 text-purple-600 rounded-full px-3 py-1 text-xs font-semibold">
                                            Giai đoạn
                                        </span>

                                    @elseif ($tag->type === 'brand')

                                        <span class="bg-amber-50 text-amber-600 rounded-full px-3 py-1 text-xs font-semibold">
                                            Thương hiệu
                                        </span>

                                    @else

                                        <span class="bg-gray-100 text-gray-600 rounded-full px-3 py-1 text-xs font-semibold">
                                            {{ $tag->type }}
                                        </span>

                                    @endif

                                </td>


                                <td class="px-5 py-4">

                                    <div class="flex justify-end gap-2">

                                        <button
                                            type="button"
                                            data-name="{{ $tag->name }}"
                                            data-slug="{{ $tag->slug }}"
                                            data-type="{{ $tag->type }}"
                                            data-action="{{ route('admin.tags.update', $tag) }}"
                                            onclick="openEditTagModal(this)"
                                            class="px-3 py-2 rounded-lg
                                                   border border-admin-border
                                                   hover:text-coral"
                                        >
                                            Sửa
                                        </button>

                                        <button
                                            type="button"
                                            data-name="{{ $tag->name }}"
                                            data-action="{{ route('admin.tags.destroy', $tag) }}"
                                            onclick="openDeleteTagModal(this)"
                                            class="px-3 py-2 rounded-lg
                                                   bg-red-50 text-red-500"
                                        >
                                            Xóa
                                        </button>

                                    </div>

                                </td>

                            </tr>

                        @empty

                            <tr>
                                <td colspan="5" class="py-20 text-center">

                                    <div class="text-4xl">
                                        🏷️
                                    </div>

                                    <p class="mt-3 font-semibold">
                                        Chưa có thuộc tính nào
                                    </p>

                                    <a
                                        href="{{ route('admin.categories.index', [
                                            'tab' => 'tags',
                                            'create_tag' => 1
                                        ]) }}"
                                        class="btn-primary inline-block mt-4"
                                    >
                                        + Thêm thuộc tính
                                    </a>

                                </td>
                            </tr>

                        @endforelse

                    </tbody>

                </table>

            </div>


            {{-- PAGINATION TAGS --}}
            @if ($tags->total() > 0)

                <div
                    class="flex flex-col sm:flex-row
                           sm:items-center sm:justify-between
                           gap-4 border-t border-admin-border
                           px-5 py-4"
                >

                    <p class="text-sm text-ink-soft">
                        Hiển thị
                        <strong class="text-ink">{{ $tags->firstItem() }}</strong>
                        -
                        <strong class="text-ink">{{ $tags->lastItem() }}</strong>
                        trong
                        <strong class="text-ink">{{ $tags->total() }}</strong>
                        thuộc tính
                    </p>


                    @if ($tags->hasPages())

                        <div class="flex items-center gap-1">

                            @if ($tags->onFirstPage())

                                <span
                                    class="w-9 h-9 rounded-lg
                                           border border-admin-border
                                           flex items-center justify-center
                                           text-gray-300"
                                >
                                    ‹
                                </span>

                            @else

                                <a
                                    href="{{ $tags->previousPageUrl() }}"
                                    class="w-9 h-9 rounded-lg
                                           border border-admin-border
                                           flex items-center justify-center
                                           text-ink hover:border-coral
                                           hover:text-coral transition"
                                >
                                    ‹
                                </a>

                            @endif


                            @for ($page = 1; $page <= $tags->lastPage(); $page++)

                                @if ($page === $tags->currentPage())

                                    <span
                                        class="w-9 h-9 rounded-lg
                                               bg-coral text-white
                                               flex items-center justify-center
                                               text-sm font-semibold"
                                    >
                                        {{ $page }}
                                    </span>

                                @else

                                    <a
                                        href="{{ $tags->url($page) }}"
                                        class="w-9 h-9 rounded-lg
                                               border border-admin-border
                                               flex items-center justify-center
                                               text-sm text-ink
                                               hover:border-coral
                                               hover:text-coral transition"
                                    >
                                        {{ $page }}
                                    </a>

                                @endif

                            @endfor


                            @if ($tags->hasMorePages())

                                <a
                                    href="{{ $tags->nextPageUrl() }}"
                                    class="w-9 h-9 rounded-lg
                                           border border-admin-border
                                           flex items-center justify-center
                                           text-ink hover:border-coral
                                           hover:text-coral transition"
                                >
                                    ›
                                </a>

                            @else

                                <span
                                    class="w-9 h-9 rounded-lg
                                           border border-admin-border
                                           flex items-center justify-center
                                           text-gray-300"
                                >
                                    ›
                                </span>

                            @endif

                        </div>

                    @endif

                </div>

            @endif

        @endif

    </div>



    {{-- =====================================================
        MODAL THÊM CATEGORY
    ====================================================== --}}
    <div
        id="categoryModal"
        class="fixed inset-0 z-50 hidden
               items-center justify-center
               bg-black/40 px-4"
    >

        <div
            class="w-full max-w-xl rounded-2xl
                   bg-white shadow-2xl"
            onclick="event.stopPropagation()"
        >

            <form
                action="{{ route('admin.categories.store') }}"
                method="POST"
                enctype="multipart/form-data"
            >
                @csrf

                <div class="p-6">

                    <div class="flex justify-between gap-4">

                        <div>

                            <div
                                class="w-11 h-11 rounded-xl
                                       bg-coral-light
                                       flex items-center justify-center
                                       text-xl mb-3"
                            >
                                📦
                            </div>

                            <h3 class="text-lg font-semibold text-ink">
                                Thêm danh mục
                            </h3>

                            <p class="mt-1 text-sm text-ink-soft">
                                Tạo danh mục mới cho sản phẩm.
                            </p>

                        </div>

                        <button
                            type="button"
                            onclick="closeCategoryModal()"
                            class="w-9 h-9 rounded-lg
                                   hover:bg-admin-bg text-xl"
                        >
                            ×
                        </button>

                    </div>


                    <div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-5">

                        <div>
                            <label class="block mb-2 text-sm font-semibold">
                                Tên danh mục
                                <span class="text-coral">*</span>
                            </label>

                            <input
                                type="text"
                                name="name"
                                required
                                placeholder="Ví dụ: Sữa cho bé"
                                class="w-full border border-admin-border
                                       rounded-xl px-4 py-2.5
                                       outline-none focus:border-coral"
                            >
                        </div>

                        <div>
                            <label class="block mb-2 text-sm font-semibold">
                                Slug
                            </label>

                            <input
                                type="text"
                                name="slug"
                                placeholder="Để trống để tự tạo"
                                class="w-full border border-admin-border
                                       rounded-xl px-4 py-2.5
                                       outline-none focus:border-coral"
                            >
                        </div>

                        <div>
                            <label class="block mb-2 text-sm font-semibold">
                                Icon
                            </label>

                            <input
                                type="text"
                                name="icon"
                                placeholder="Ví dụ: 🍼"
                                class="w-full border border-admin-border
                                       rounded-xl px-4 py-2.5
                                       outline-none focus:border-coral"
                            >
                        </div>

                        <div>
                            <label class="block mb-2 text-sm font-semibold">
                                Thứ tự hiển thị
                            </label>

                            <input
                                type="number"
                                name="sort_order"
                                min="0"
                                value="0"
                                class="w-full border border-admin-border
                                       rounded-xl px-4 py-2.5
                                       outline-none focus:border-coral"
                            >
                        </div>

                    </div>


                    <div class="mt-5">

                        <label class="block mb-2 text-sm font-semibold">
                            Ảnh danh mục
                        </label>

                        <input
                            type="file"
                            name="image"
                            accept="image/*"
                            class="w-full border border-admin-border
                                   rounded-xl px-4 py-2.5 text-sm"
                        >

                        <p class="mt-1 text-xs text-ink-soft">
                            JPG, PNG, WEBP. Tối đa 2MB.
                        </p>

                    </div>


                    <label
                        class="mt-5 flex items-center justify-between
                               border border-admin-border
                               rounded-xl px-4 py-3"
                    >

                        <div>

                            <p class="text-sm font-semibold">
                                Hiển thị danh mục
                            </p>

                            <p class="text-xs text-ink-soft mt-1">
                                Danh mục sẽ xuất hiện trên website.
                            </p>

                        </div>

                        <input
                            type="checkbox"
                            name="is_active"
                            value="1"
                            checked
                            class="w-5 h-5 accent-coral"
                        >

                    </label>


                    <div
                        class="mt-6 pt-5
                               border-t border-admin-border
                               flex justify-end gap-3"
                    >

                        <button
                            type="button"
                            onclick="closeCategoryModal()"
                            class="px-5 py-2.5 rounded-xl
                                   border border-admin-border"
                        >
                            Hủy
                        </button>

                        <button
                            type="submit"
                            class="btn-primary"
                        >
                            Lưu danh mục
                        </button>

                    </div>

                </div>

            </form>

        </div>

    </div>



    {{-- =====================================================
        MODAL THÊM TAG
    ====================================================== --}}
    <div
        id="createTagModal"
        class="fixed inset-0 z-50
               {{ $showCreateTagModal ? 'flex' : 'hidden' }}
               items-center justify-center
               bg-black/40 px-4"
    >

        <div
            class="w-full max-w-lg rounded-2xl bg-white shadow-2xl"
            onclick="event.stopPropagation()"
        >

            <form
                action="{{ route('admin.tags.store') }}"
                method="POST"
            >
                @csrf

                <div class="p-6">

                    <div class="flex justify-between gap-4">

                        <div>

                            <div
                                class="w-11 h-11 rounded-xl
                                       bg-coral-light
                                       flex items-center justify-center
                                       text-xl mb-3"
                            >
                                🏷️
                            </div>

                            <h3 class="text-lg font-semibold text-ink">
                                Thêm thuộc tính
                            </h3>

                            <p class="mt-1 text-sm text-ink-soft">
                                Tạo thuộc tính để gắn với sản phẩm.
                            </p>

                        </div>

                        <a
                            href="{{ route('admin.categories.index', ['tab' => 'tags']) }}"
                            class="w-9 h-9 rounded-lg
                                   flex items-center justify-center
                                   hover:bg-admin-bg text-xl text-ink-soft"
                        >
                            ×
                        </a>

                    </div>


                    <div class="mt-6 space-y-5">

                        <div>

                            <label class="block mb-2 text-sm font-semibold text-ink">
                                Tên thuộc tính
                                <span class="text-coral">*</span>
                            </label>

                            <input
                                type="text"
                                name="name"
                                required
                                value="{{ old('name') }}"
                                placeholder="Ví dụ: Không lactose"
                                class="w-full border border-admin-border
                                       rounded-xl px-4 py-2.5
                                       outline-none focus:border-coral"
                            >

                            @error('name')
                                <p class="mt-1.5 text-xs text-red-500">
                                    {{ $message }}
                                </p>
                            @enderror

                        </div>


                        <div>

                            <label class="block mb-2 text-sm font-semibold text-ink">
                                Slug
                            </label>

                            <input
                                type="text"
                                name="slug"
                                value="{{ old('slug') }}"
                                placeholder="Để trống để hệ thống tự tạo"
                                class="w-full border border-admin-border
                                       rounded-xl px-4 py-2.5
                                       outline-none focus:border-coral"
                            >

                            <p class="mt-1 text-xs text-ink-soft">
                                Ví dụ: Không lactose → khong-lactose
                            </p>

                            @error('slug')
                                <p class="mt-1.5 text-xs text-red-500">
                                    {{ $message }}
                                </p>
                            @enderror

                        </div>


                        <div>

                            <label class="block mb-2 text-sm font-semibold text-ink">
                                Loại
                                <span class="text-coral">*</span>
                            </label>

                            <select
                                name="type"
                                required
                                class="w-full border border-admin-border
                                       rounded-xl px-4 py-2.5
                                       outline-none focus:border-coral"
                            >

                                <option
                                    value="attribute"
                                    @selected(old('type', 'attribute') === 'attribute')
                                >
                                    Thuộc tính
                                </option>

                                <option
                                    value="stage"
                                    @selected(old('type') === 'stage')
                                >
                                    Giai đoạn
                                </option>

                                <option
                                    value="brand"
                                    @selected(old('type') === 'brand')
                                >
                                    Thương hiệu
                                </option>

                            </select>

                            @error('type')
                                <p class="mt-1.5 text-xs text-red-500">
                                    {{ $message }}
                                </p>
                            @enderror

                        </div>

                    </div>


                    <div
                        class="mt-6 pt-5 border-t border-admin-border
                               flex justify-end gap-3"
                    >

                        <a
                            href="{{ route('admin.categories.index', ['tab' => 'tags']) }}"
                            class="px-5 py-2.5 rounded-xl
                                   border border-admin-border
                                   text-sm text-ink
                                   hover:bg-admin-bg transition"
                        >
                            Hủy
                        </a>

                        <button
                            type="submit"
                            class="btn-primary"
                        >
                            Lưu thuộc tính
                        </button>

                    </div>

                </div>

            </form>

        </div>

    </div>



    {{-- =====================================================
        MODAL SỬA TAG
    ====================================================== --}}
    <div
        id="editTagModal"
        class="fixed inset-0 z-50 hidden
               items-center justify-center
               bg-black/40 px-4"
    >

        <div class="w-full max-w-lg rounded-2xl bg-white shadow-2xl">

            <form
                id="editTagForm"
                method="POST"
            >
                @csrf
                @method('PUT')

                <div class="p-6">

                    <div class="flex justify-between">

                        <div>

                            <h3 class="text-lg font-semibold">
                                Chỉnh sửa thuộc tính
                            </h3>

                            <p class="mt-1 text-sm text-ink-soft">
                                Cập nhật thông tin thuộc tính.
                            </p>

                        </div>

                        <button
                            type="button"
                            onclick="closeEditTagModal()"
                            class="text-xl"
                        >
                            ×
                        </button>

                    </div>


                    <div class="mt-6 space-y-4">

                        <input
                            id="editTagName"
                            type="text"
                            name="name"
                            required
                            class="w-full border border-admin-border
                                   rounded-xl px-4 py-2.5"
                        >

                        <input
                            id="editTagSlug"
                            type="text"
                            name="slug"
                            class="w-full border border-admin-border
                                   rounded-xl px-4 py-2.5"
                        >

                        <select
                            id="editTagType"
                            name="type"
                            required
                            class="w-full border border-admin-border
                                   rounded-xl px-4 py-2.5"
                        >
                            <option value="attribute">
                                Thuộc tính
                            </option>

                            <option value="stage">
                                Giai đoạn
                            </option>

                            <option value="brand">
                                Thương hiệu
                            </option>
                        </select>

                    </div>


                    <div class="mt-6 flex justify-end gap-3">

                        <button
                            type="button"
                            onclick="closeEditTagModal()"
                            class="px-5 py-2.5 rounded-xl
                                   border border-admin-border"
                        >
                            Hủy
                        </button>

                        <button
                            type="submit"
                            class="btn-primary"
                        >
                            Lưu thay đổi
                        </button>

                    </div>

                </div>

            </form>

        </div>

    </div>



    {{-- =====================================================
        DELETE CATEGORY
    ====================================================== --}}
    <div
        id="deleteCategoryModal"
        class="fixed inset-0 z-50 hidden
               items-center justify-center
               bg-black/40 px-4"
    >

        <div class="w-full max-w-md rounded-2xl bg-white shadow-2xl">

            <div class="p-6">

                <h3 class="text-lg font-semibold">
                    Xóa danh mục?
                </h3>

                <p class="mt-2 text-sm text-ink-soft">

                    Bạn có chắc muốn xóa

                    <strong id="deleteCategoryName"></strong>?

                </p>

                <p class="text-xs text-red-500 mt-2">
                    Không thể xóa danh mục đang có sản phẩm.
                </p>


                <div class="mt-6 flex justify-end gap-3">

                    <button
                        type="button"
                        onclick="closeDeleteCategoryModal()"
                        class="px-4 py-2.5 rounded-xl
                               border border-admin-border"
                    >
                        Hủy
                    </button>

                    <form
                        id="deleteCategoryForm"
                        method="POST"
                    >
                        @csrf
                        @method('DELETE')

                        <button
                            type="submit"
                            class="px-4 py-2.5 rounded-xl
                                   bg-red-500 text-white font-semibold"
                        >
                            Xóa danh mục
                        </button>

                    </form>

                </div>

            </div>

        </div>

    </div>



    {{-- =====================================================
        DELETE TAG
    ====================================================== --}}
    <div
        id="deleteTagModal"
        class="fixed inset-0 z-50 hidden
               items-center justify-center
               bg-black/40 px-4"
    >

        <div class="w-full max-w-md rounded-2xl bg-white shadow-2xl">

            <div class="p-6">

                <h3 class="text-lg font-semibold">
                    Xóa thuộc tính?
                </h3>

                <p class="mt-2 text-sm text-ink-soft">

                    Bạn có chắc muốn xóa

                    <strong id="deleteTagName"></strong>?

                </p>


                <div class="mt-6 flex justify-end gap-3">

                    <button
                        type="button"
                        onclick="closeDeleteTagModal()"
                        class="px-4 py-2 rounded-xl
                               border border-admin-border"
                    >
                        Hủy
                    </button>

                    <form
                        id="deleteTagForm"
                        method="POST"
                    >
                        @csrf
                        @method('DELETE')

                        <button
                            type="submit"
                            class="px-4 py-2 rounded-xl
                                   bg-red-500 text-white"
                        >
                            Xóa thuộc tính
                        </button>

                    </form>

                </div>

            </div>

        </div>

    </div>

@endsection



@push('scripts')

<script>

    function showModal(modal) {
        if (!modal) return;

        modal.classList.remove('hidden');
        modal.classList.add('flex');

        document.body.style.overflow = 'hidden';
    }


    function hideModal(modal) {
        if (!modal) return;

        modal.classList.add('hidden');
        modal.classList.remove('flex');

        document.body.style.overflow = '';
    }


    // CATEGORY
    function openCreateCategoryModal() {
        showModal(document.getElementById('categoryModal'));
    }


    function closeCategoryModal() {
        hideModal(document.getElementById('categoryModal'));
    }


    // EDIT TAG
    function openEditTagModal(button) {

        document.getElementById('editTagName').value =
            button.dataset.name;

        document.getElementById('editTagSlug').value =
            button.dataset.slug;

        document.getElementById('editTagType').value =
            button.dataset.type;

        document.getElementById('editTagForm').action =
            button.dataset.action;

        showModal(document.getElementById('editTagModal'));
    }


    function closeEditTagModal() {
        hideModal(document.getElementById('editTagModal'));
    }


    // DELETE CATEGORY
    function openDeleteCategoryModal(button) {

        document.getElementById('deleteCategoryForm').action =
            button.dataset.action;

        document.getElementById('deleteCategoryName').textContent =
            button.dataset.name;

        showModal(document.getElementById('deleteCategoryModal'));
    }


    function closeDeleteCategoryModal() {
        hideModal(document.getElementById('deleteCategoryModal'));
    }


    // DELETE TAG
    function openDeleteTagModal(button) {

        document.getElementById('deleteTagForm').action =
            button.dataset.action;

        document.getElementById('deleteTagName').textContent =
            button.dataset.name;

        showModal(document.getElementById('deleteTagModal'));
    }


    function closeDeleteTagModal() {
        hideModal(document.getElementById('deleteTagModal'));
    }


    document.addEventListener('keydown', function (event) {

        if (event.key === 'Escape') {

            closeCategoryModal();
            closeEditTagModal();
            closeDeleteCategoryModal();
            closeDeleteTagModal();

        }

    });

</script>

@endpush