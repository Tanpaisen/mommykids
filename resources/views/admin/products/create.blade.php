@extends('admin.layouts.app')

@section('page_title', 'Thêm sản phẩm')
@section('page_subtitle', 'Tạo sản phẩm mới và gắn danh mục, giai đoạn, thuộc tính')

@section('page_actions')
    <a href="{{ route('admin.products.index') }}"
       class="px-4 py-2.5 rounded-xl border border-admin-border bg-white text-sm text-ink hover:bg-admin-bg transition">
        ← Quay lại
    </a>
@endsection

@section('content')
@php
    $selectedStageIds = collect(old('stage_ids', []))->map(fn ($id) => (string) $id)->all();
    $selectedTagIds = collect(old('tag_ids', []))->map(fn ($id) => (string) $id)->all();

    $highlightIconOptions = [
        'shield' => 'Khiên / Miễn dịch',
        'brain' => 'Trí não',
        'digest' => 'Tiêu hóa',
        'heart' => 'Trái tim / Toàn diện',
        'bone' => 'Xương / Tăng trưởng',
        'eye' => 'Thị lực',
        'check' => 'Dấu kiểm',
    ];
@endphp

<form action="{{ route('admin.products.store') }}" method="POST" enctype="multipart/form-data">
    @csrf

    <div class="space-y-6">

        {{-- =========================================================
            THÔNG TIN CƠ BẢN - FULL WIDTH
        ========================================================== --}}
        <section class="card">
            <div class="border-b border-admin-border pb-4 mb-5">
                <h2 class="text-base font-semibold text-ink">Thông tin cơ bản</h2>
                <p class="text-sm text-ink-soft mt-1">Thông tin nhận diện và trạng thái của sản phẩm.</p>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
                <div class="lg:col-span-2">
                    <label class="block mb-2 text-sm font-semibold text-ink">Tên sản phẩm <span class="text-coral">*</span></label>
                    <input type="text" name="name" required value="{{ old('name') }}"
                           placeholder="Ví dụ: Sữa Aptamil Profutura 900g"
                           class="w-full border border-admin-border rounded-xl px-4 py-3 bg-white text-ink outline-none focus:border-coral focus:ring-2 focus:ring-coral/10">
                    @error('name')<p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block mb-2 text-sm font-semibold text-ink">Danh mục <span class="text-coral">*</span></label>
                    <select name="category_id" required
                            class="w-full border border-admin-border rounded-xl px-4 py-3 bg-white text-ink outline-none focus:border-coral focus:ring-2 focus:ring-coral/10">
                        <option value="">— Chọn danh mục —</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}" @selected((string) old('category_id') === (string) $category->id)>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('category_id')<p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block mb-2 text-sm font-semibold text-ink">Slug</label>
                    <input type="text" name="slug" value="{{ old('slug') }}" placeholder="Để trống để tự tạo"
                           class="w-full border border-admin-border rounded-xl px-4 py-3 bg-white text-ink outline-none focus:border-coral focus:ring-2 focus:ring-coral/10">
                    @error('slug')<p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>@enderror
                </div>

                <div class="lg:col-span-2">
                    <label class="block mb-2 text-sm font-semibold text-ink">Mô tả</label>
                    <textarea name="description" rows="5" placeholder="Nhập mô tả sản phẩm..."
                              class="w-full border border-admin-border rounded-xl px-4 py-3 bg-white text-ink outline-none resize-y focus:border-coral focus:ring-2 focus:ring-coral/10">{{ old('description') }}</textarea>
                    @error('description')<p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>@enderror
                </div>

                <div class="lg:col-span-2 flex flex-col gap-4 border border-admin-border rounded-xl px-4 py-4 bg-admin-bg/30 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <p class="text-sm font-semibold text-ink">Trạng thái sản phẩm</p>
                        <p class="text-xs text-ink-soft mt-1">Quản lý trạng thái hiển thị và đánh dấu sản phẩm nổi bật.</p>
                    </div>

                    <div class="flex flex-wrap items-center gap-x-6 gap-y-3 shrink-0">
                        <label class="inline-flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" name="is_active" value="1"
                                   @checked(old('is_active', true))
                                   class="w-5 h-5 accent-coral">
                            <span class="text-sm font-medium text-ink">Đang bán</span>
                        </label>

                        <label class="inline-flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" name="is_featured" value="1"
                                   @checked(old('is_featured', false))
                                   class="w-5 h-5 accent-coral">
                            <span class="text-sm font-medium text-ink">Sản phẩm nổi bật</span>
                        </label>
                    </div>
                </div>
            </div>
        </section>

        {{-- =========================================================
            NỘI DUNG + TAXONOMY
        ========================================================== --}}
        <div class="grid grid-cols-1 xl:grid-cols-[1.55fr_0.85fr] gap-6 items-start">
            <div class="space-y-6">
            <section class="card">
                <div class="border-b border-admin-border pb-4 mb-5">
                    <h2 class="text-base font-semibold text-ink">Nội dung chi tiết sản phẩm</h2>
                    <p class="text-sm text-ink-soft mt-1">Nội dung hiển thị tại trang chi tiết sản phẩm.</p>
                </div>

                <div class="space-y-5">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div>
                            <label class="block mb-2 text-sm font-semibold text-ink">Xuất xứ</label>
                            <input type="text" name="origin" value="{{ old('origin') }}" placeholder="Ví dụ: Nhật Bản"
                                   class="w-full border border-admin-border rounded-xl px-4 py-3 bg-white text-ink outline-none focus:border-coral">
                            @error('origin')<p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="block mb-2 text-sm font-semibold text-ink">Nhà sản xuất</label>
                            <input type="text" name="manufacturer" value="{{ old('manufacturer') }}" placeholder="Ví dụ: Morinaga Milk Industry"
                                   class="w-full border border-admin-border rounded-xl px-4 py-3 bg-white text-ink outline-none focus:border-coral">
                            @error('manufacturer')<p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>@enderror
                        </div>
                    </div>

                    <div>
                        <label class="block mb-2 text-sm font-semibold text-ink">Thành phần</label>
                        <textarea name="ingredients" rows="5" placeholder="Nhập thành phần của sản phẩm..."
                                  class="w-full border border-admin-border rounded-xl px-4 py-3 bg-white text-ink outline-none resize-y focus:border-coral">{{ old('ingredients') }}</textarea>
                        @error('ingredients')<p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label class="block mb-2 text-sm font-semibold text-ink">Hướng dẫn sử dụng</label>
                        <textarea name="usage_instructions" rows="5" placeholder="Nhập cách sử dụng, cách pha, liều lượng..."
                                  class="w-full border border-admin-border rounded-xl px-4 py-3 bg-white text-ink outline-none resize-y focus:border-coral">{{ old('usage_instructions') }}</textarea>
                        @error('usage_instructions')<p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>@enderror
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div>
                            <label class="block mb-2 text-sm font-semibold text-ink">Hướng dẫn bảo quản</label>
                            <textarea name="storage_instructions" rows="4" placeholder="Nhập cách bảo quản sản phẩm..."
                                      class="w-full border border-admin-border rounded-xl px-4 py-3 bg-white text-ink outline-none resize-y focus:border-coral">{{ old('storage_instructions') }}</textarea>
                            @error('storage_instructions')<p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="block mb-2 text-sm font-semibold text-ink">Cảnh báo / Lưu ý</label>
                            <textarea name="warning" rows="4" placeholder="Nhập cảnh báo hoặc lưu ý khi sử dụng..."
                                      class="w-full border border-admin-border rounded-xl px-4 py-3 bg-white text-ink outline-none resize-y focus:border-coral">{{ old('warning') }}</textarea>
                            @error('warning')<p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>@enderror
                        </div>
                    </div>
                </div>
            </section>

            {{-- =========================================================
                ĐIỂM NỔI BẬT - COMPACT TRONG CỘT NỘI DUNG
            ========================================================== --}}
            <section class="card">
                <div class="border-b border-admin-border pb-4 mb-5">
                    <h2 class="text-base font-semibold text-ink">Điểm nổi bật sản phẩm</h2>
                    <p class="text-sm text-ink-soft mt-1">Thiết lập nội dung nổi bật hiển thị tại phần mô tả sản phẩm.</p>
                </div>

                <div class="space-y-3">
                    @for ($i = 0; $i < 4; $i++)
                        @php($highlightIcon = old('highlights.items.' . $i . '.icon'))
                        <div class="grid grid-cols-1 lg:grid-cols-[48px_1fr_1fr_220px] gap-3 items-end rounded-xl border border-admin-border bg-admin-bg/25 p-3.5">
                            <div class="hidden lg:flex w-10 h-10 rounded-xl bg-coral-light text-coral font-bold items-center justify-center self-center">
                                {{ str_pad((string) ($i + 1), 2, '0', STR_PAD_LEFT) }}
                            </div>
                            <div>
                                <label class="block mb-1.5 text-xs font-semibold text-ink-soft">Tiêu đề</label>
                                <input type="text" name="highlights[items][{{ $i }}][title]"
                                       value="{{ old('highlights.items.' . $i . '.title') }}" maxlength="100"
                                       placeholder="Ví dụ: Hỗ trợ miễn dịch"
                                       class="w-full border border-admin-border rounded-xl px-3.5 py-2.5 bg-white text-ink outline-none focus:border-coral">
                                @error("highlights.items.$i.title")<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label class="block mb-1.5 text-xs font-semibold text-ink-soft">Nội dung ngắn</label>
                                <input type="text" name="highlights[items][{{ $i }}][subtitle]"
                                       value="{{ old('highlights.items.' . $i . '.subtitle') }}" maxlength="150"
                                       placeholder="Ví dụ: Với Synbiotic+"
                                       class="w-full border border-admin-border rounded-xl px-3.5 py-2.5 bg-white text-ink outline-none focus:border-coral">
                                @error("highlights.items.$i.subtitle")<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label class="block mb-1.5 text-xs font-semibold text-ink-soft">Icon</label>
                                <select name="highlights[items][{{ $i }}][icon]"
                                        class="w-full border border-admin-border rounded-xl px-3.5 py-2.5 bg-white text-ink outline-none focus:border-coral">
                                    <option value="">— Chọn icon —</option>
                                    @foreach ($highlightIconOptions as $value => $label)
                                        <option value="{{ $value }}" @selected($highlightIcon === $value)>{{ $label }}</option>
                                    @endforeach
                                </select>
                                @error("highlights.items.$i.icon")<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                            </div>
                        </div>
                    @endfor
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5 mt-5 pt-5 border-t border-admin-border">
                    <div>
                        <label class="block mb-2 text-sm font-semibold text-ink">Thông điệp nổi bật</label>
                        <input type="text" name="highlights[message]" value="{{ old('highlights.message') }}" maxlength="255"
                               placeholder="Ví dụ: Lựa chọn tin cậy của hàng triệu ba mẹ..."
                               class="w-full border border-admin-border rounded-xl px-4 py-3 bg-white text-ink outline-none focus:border-coral">
                        @error('highlights.message')<p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block mb-2 text-sm font-semibold text-ink">Nội dung phụ</label>
                        <input type="text" name="highlights[submessage]" value="{{ old('highlights.submessage') }}" maxlength="255"
                               placeholder="Ví dụ: Aptamil – Đồng hành cùng sự phát triển của bé yêu."
                               class="w-full border border-admin-border rounded-xl px-4 py-3 bg-white text-ink outline-none focus:border-coral">
                        @error('highlights.submessage')<p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>@enderror
                    </div>
                </div>
            </section>

            </div>

            <aside class="space-y-6 xl:sticky xl:top-24">
                <section class="card">
                    <div class="border-b border-admin-border pb-4">
                        <h2 class="text-base font-semibold text-ink">Giai đoạn phù hợp</h2>
                        <p class="text-sm text-ink-soft mt-1">Chọn một hoặc nhiều giai đoạn phù hợp với sản phẩm.</p>
                    </div>
                    <div class="mt-4 space-y-2 max-h-[360px] overflow-y-auto pr-1">
                        @forelse ($stages as $stage)
                            <label class="flex items-start gap-3 border border-admin-border rounded-xl px-4 py-3 bg-white cursor-pointer hover:border-coral/40 hover:bg-coral-light/20 transition">
                                <input type="checkbox" name="stage_ids[]" value="{{ $stage->id }}"
                                       @checked(in_array((string) $stage->id, $selectedStageIds, true))
                                       class="mt-1 w-4 h-4 accent-coral">
                                <div>
                                    <p class="text-sm font-semibold text-ink">{{ $stage->icon }} {{ $stage->name }}</p>
                                    <p class="text-xs text-ink-soft mt-1">{{ $stage->age_from }} - {{ $stage->age_to }} tháng</p>
                                </div>
                            </label>
                        @empty
                            <p class="text-sm text-ink-soft">Chưa có giai đoạn.</p>
                        @endforelse
                    </div>
                </section>

                <section class="card">
                    <div class="border-b border-admin-border pb-4">
                        <h2 class="text-base font-semibold text-ink">Thuộc tính / Tags</h2>
                        <p class="text-sm text-ink-soft mt-1">Gắn thuộc tính và thương hiệu cho sản phẩm.</p>
                    </div>
                    <div class="mt-4 space-y-2 max-h-[420px] overflow-y-auto pr-1">
                        @forelse ($tags as $tag)
                            <label class="flex items-center justify-between gap-4 border border-admin-border rounded-xl px-4 py-3 bg-white cursor-pointer hover:border-coral/40 hover:bg-coral-light/20 transition">
                                <div class="flex items-center gap-3 min-w-0">
                                    <input type="checkbox" name="tag_ids[]" value="{{ $tag->id }}"
                                           @checked(in_array((string) $tag->id, $selectedTagIds, true))
                                           class="w-4 h-4 accent-coral">
                                    <span class="text-sm font-medium text-ink truncate">{{ $tag->name }}</span>
                                </div>
                                @if ($tag->type === 'attribute')
                                    <span class="shrink-0 text-[11px] text-blue-600 bg-blue-50 rounded-full px-2.5 py-1">Thuộc tính</span>
                                @elseif ($tag->type === 'brand')
                                    <span class="shrink-0 text-[11px] text-amber-600 bg-amber-50 rounded-full px-2.5 py-1">Thương hiệu</span>
                                @elseif ($tag->type === 'stage')
                                    <span class="shrink-0 text-[11px] text-purple-600 bg-purple-50 rounded-full px-2.5 py-1">Giai đoạn</span>
                                @endif
                            </label>
                        @empty
                            <p class="text-sm text-ink-soft">Chưa có thuộc tính.</p>
                        @endforelse
                    </div>
                </section>
            </aside>
        </div>

        {{-- =========================================================
            GIÁ + HÌNH ẢNH
        ========================================================== --}}
        <div class="grid grid-cols-1 xl:grid-cols-2 gap-6 items-start">
            <section class="card">
                <div class="border-b border-admin-border pb-4 mb-5">
                    <h2 class="text-base font-semibold text-ink">Giá & Tồn kho</h2>
                    <p class="text-sm text-ink-soft mt-1">Quản lý giá bán, khuyến mãi, tồn kho và thông tin đóng gói.</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div>
                        <label class="block mb-2 text-sm font-semibold">Giá bán <span class="text-coral">*</span></label>
                        <div class="relative">
                            <input type="number" name="price" required min="0" value="{{ old('price') }}"
                                   class="w-full border border-admin-border rounded-xl px-4 py-3 pr-12 outline-none focus:border-coral">
                            <span class="absolute right-4 top-1/2 -translate-y-1/2 text-sm text-ink-soft">đ</span>
                        </div>
                        @error('price')<p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block mb-2 text-sm font-semibold">Giá cũ</label>
                        <div class="relative">
                            <input type="number" name="old_price" min="0" value="{{ old('old_price') }}"
                                   class="w-full border border-admin-border rounded-xl px-4 py-3 pr-12 outline-none focus:border-coral">
                            <span class="absolute right-4 top-1/2 -translate-y-1/2 text-sm text-ink-soft">đ</span>
                        </div>
                        @error('old_price')<p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block mb-2 text-sm font-semibold">Giảm giá</label>
                        <div class="relative">
                            <input type="number" name="discount_percent" min="0" max="100" value="{{ old('discount_percent') }}"
                                   class="w-full border border-admin-border rounded-xl px-4 py-3 pr-12 outline-none focus:border-coral">
                            <span class="absolute right-4 top-1/2 -translate-y-1/2 text-sm text-ink-soft">%</span>
                        </div>
                        @error('discount_percent')<p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block mb-2 text-sm font-semibold">Tồn kho <span class="text-coral">*</span></label>
                        <input type="number" name="stock" required min="0" value="{{ old('stock', 0) }}"
                               class="w-full border border-admin-border rounded-xl px-4 py-3 outline-none focus:border-coral">
                        @error('stock')<p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>@enderror
                    </div>
                    <div class="md:col-span-2">
                        <label class="block mb-2 text-sm font-semibold">Khối lượng sản phẩm <span class="text-coral">*</span></label>
                        <div class="relative">
                            <input type="number" name="weight_grams" required min="1" step="1" value="{{ old('weight_grams') }}"
                                   placeholder="Ví dụ: 900"
                                   class="w-full border border-admin-border rounded-xl px-4 py-3 pr-12 outline-none focus:border-coral">
                            <span class="absolute right-4 top-1/2 -translate-y-1/2 text-sm text-ink-soft">g</span>
                        </div>
                        <p class="mt-1.5 text-xs text-ink-soft">Dùng để tính phí vận chuyển GHN.</p>
                        @error('weight_grams')<p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>@enderror
                    </div>
                    <div class="md:col-span-2 pt-1">
                        <p class="text-sm font-semibold text-ink">Kích thước đóng gói <span class="text-coral">*</span></p>
                        <p class="mt-1 text-xs text-ink-soft">Đơn vị centimet (cm).</p>
                    </div>
                    @foreach (['length_cm' => 'Chiều dài', 'width_cm' => 'Chiều rộng', 'height_cm' => 'Chiều cao'] as $field => $label)
                        <div class="{{ $field === 'height_cm' ? 'md:col-span-2' : '' }}">
                            <label class="block mb-2 text-sm font-semibold">{{ $label }} <span class="text-coral">*</span></label>
                            <div class="relative">
                                <input type="number" name="{{ $field }}" required min="1" step="1" value="{{ old($field) }}"
                                       class="w-full border border-admin-border rounded-xl px-4 py-3 pr-12 outline-none focus:border-coral">
                                <span class="absolute right-4 top-1/2 -translate-y-1/2 text-sm text-ink-soft">cm</span>
                            </div>
                            @error($field)<p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>@enderror
                        </div>
                    @endforeach
                </div>
            </section>

            <section class="card">
                <div class="border-b border-admin-border pb-4 mb-5">
                    <h2 class="text-base font-semibold text-ink">Hình ảnh sản phẩm</h2>
                    <p class="text-sm text-ink-soft mt-1">Upload ảnh đại diện và tối đa 8 ảnh chi tiết.</p>
                </div>

                <div class="space-y-6">
                    <div>
                        <label class="block mb-2 text-sm font-semibold text-ink">Ảnh đại diện</label>
                        <input type="file" name="image" accept="image/jpeg,image/png,image/webp"
                               class="w-full border border-admin-border rounded-xl px-4 py-3 bg-white text-sm">
                        <p class="mt-2 text-xs text-ink-soft">JPG, JPEG, PNG hoặc WEBP. Tối đa 4MB.</p>
                        @error('image')<p class="mt-2 text-xs text-red-500">{{ $message }}</p>@enderror
                    </div>
                    <div class="pt-5 border-t border-admin-border">
                        <label class="block mb-2 text-sm font-semibold text-ink">Ảnh chi tiết</label>
                        <input type="file" name="images[]" multiple accept="image/jpeg,image/png,image/webp"
                               class="w-full border border-admin-border rounded-xl px-4 py-3 bg-white text-sm">
                        <p class="mt-2 text-xs text-ink-soft">Có thể chọn nhiều ảnh cùng lúc, tối đa 8 ảnh.</p>
                        @error('images')<p class="mt-2 text-xs text-red-500">{{ $message }}</p>@enderror
                        @error('images.*')<p class="mt-2 text-xs text-red-500">{{ $message }}</p>@enderror
                    </div>
                </div>
            </section>
        </div>

        {{-- =========================================================
            ACTIONS
        ========================================================== --}}
        <section class="card">
            <div class="flex flex-col-reverse sm:flex-row sm:items-center sm:justify-end gap-3">
                <a
                    href="{{ route('admin.products.index') }}"
                    class="w-full sm:w-28
                           text-center
                           border border-admin-border
                           rounded-xl px-4 py-3
                           text-sm font-medium text-ink
                           hover:bg-admin-bg transition"
                >
                    Hủy
                </a>

                @can('products.manage')
                <button
                    type="submit"
                    class="w-full sm:w-48
                           bg-coral text-white
                           rounded-xl px-5 py-3
                           text-sm font-semibold
                           hover:opacity-90 transition"
                >
                    ✓ Lưu sản phẩm
                </button>
                @endcan
            </div>
        </section>
    </div>
</form>
@endsection