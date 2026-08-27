@php
    $isEdit = isset($stage);
@endphp

<div class="space-y-5">

    {{-- Tên + Icon --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">

        <div>
            <label for="name" class="block text-sm font-semibold text-ink mb-2">
                Tên giai đoạn
                <span class="text-coral">*</span>
            </label>

            <input
                id="name"
                type="text"
                name="name"
                value="{{ old('name', $stage->name ?? '') }}"
                placeholder="Ví dụ: Sơ sinh 0-3 tháng"
                class="w-full border border-admin-border rounded-xl px-4 py-2.5
                       text-sm outline-none focus:border-coral focus:ring-1 focus:ring-coral
                       @error('name') border-red-400 @enderror"
            >

            @error('name')
                <p class="mt-1.5 text-xs text-red-500">
                    {{ $message }}
                </p>
            @enderror
        </div>


        <div>
            <label for="icon" class="block text-sm font-semibold text-ink mb-2">
                Icon
            </label>

            <input
                id="icon"
                type="text"
                name="icon"
                value="{{ old('icon', $stage->icon ?? '') }}"
                placeholder="Ví dụ: 👶"
                class="w-full border border-admin-border rounded-xl px-4 py-2.5
                       text-sm outline-none focus:border-coral focus:ring-1 focus:ring-coral"
            >

            <p class="mt-1.5 text-xs text-ink-soft">
                Có thể nhập emoji như 👶, 🍼, 🤰.
            </p>

            @error('icon')
                <p class="mt-1.5 text-xs text-red-500">
                    {{ $message }}
                </p>
            @enderror
        </div>

    </div>


    {{-- Độ tuổi --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">

        <div>
            <label for="age_from" class="block text-sm font-semibold text-ink mb-2">
                Độ tuổi từ (tháng)
            </label>

            <input
                id="age_from"
                type="number"
                min="0"
                name="age_from"
                value="{{ old('age_from', $stage->age_from ?? '') }}"
                placeholder="Ví dụ: 0"
                class="w-full border border-admin-border rounded-xl px-4 py-2.5
                       text-sm outline-none focus:border-coral focus:ring-1 focus:ring-coral"
            >

            @error('age_from')
                <p class="mt-1.5 text-xs text-red-500">
                    {{ $message }}
                </p>
            @enderror
        </div>


        <div>
            <label for="age_to" class="block text-sm font-semibold text-ink mb-2">
                Đến (tháng)
            </label>

            <input
                id="age_to"
                type="number"
                min="0"
                name="age_to"
                value="{{ old('age_to', $stage->age_to ?? '') }}"
                placeholder="Ví dụ: 3"
                class="w-full border border-admin-border rounded-xl px-4 py-2.5
                       text-sm outline-none focus:border-coral focus:ring-1 focus:ring-coral"
            >

            @error('age_to')
                <p class="mt-1.5 text-xs text-red-500">
                    {{ $message }}
                </p>
            @enderror
        </div>

    </div>


    {{-- Mô tả --}}
    <div>
        <label for="description" class="block text-sm font-semibold text-ink mb-2">
            Mô tả
        </label>

        <textarea
            id="description"
            name="description"
            rows="5"
            placeholder="Nhập mô tả cho giai đoạn..."
            class="w-full border border-admin-border rounded-xl px-4 py-3
                   text-sm outline-none resize-y focus:border-coral focus:ring-1 focus:ring-coral"
        >{{ old('description', $stage->description ?? '') }}</textarea>

        @error('description')
            <p class="mt-1.5 text-xs text-red-500">
                {{ $message }}
            </p>
        @enderror
    </div>


    {{-- Sort + Status --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">

        <div>
            <label for="sort_order" class="block text-sm font-semibold text-ink mb-2">
                Thứ tự hiển thị
            </label>

            <input
                id="sort_order"
                type="number"
                min="0"
                name="sort_order"
                value="{{ old('sort_order', $stage->sort_order ?? 0) }}"
                class="w-full border border-admin-border rounded-xl px-4 py-2.5
                       text-sm outline-none focus:border-coral focus:ring-1 focus:ring-coral"
            >

            <p class="mt-1.5 text-xs text-ink-soft">
                Số nhỏ hơn sẽ được hiển thị trước.
            </p>

            @error('sort_order')
                <p class="mt-1.5 text-xs text-red-500">
                    {{ $message }}
                </p>
            @enderror
        </div>


        <div>
            <label class="block text-sm font-semibold text-ink mb-2">
                Trạng thái
            </label>

            <label
                class="flex items-center justify-between border border-admin-border
                       rounded-xl px-4 py-3 cursor-pointer"
            >

                <div>
                    <p class="font-medium text-sm text-ink">
                        Hiển thị giai đoạn
                    </p>

                    <p class="text-xs text-ink-soft mt-0.5">
                        Cho phép sử dụng giai đoạn trong hệ thống.
                    </p>
                </div>

                <input
                    type="checkbox"
                    name="is_active"
                    value="1"
                    class="w-5 h-5 accent-coral"
                    @checked(old('is_active', $stage->is_active ?? true))
                >

            </label>

        </div>

    </div>

</div>