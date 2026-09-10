@php
    // Populated automatically by App\Providers\AppServiceProvider's View::composer()
    // from the `categories` table (App\Models\Category).
    $categories = $categories ?? [];
@endphp

<aside
    id="mk-sidebar"
    class="fixed lg:sticky top-0 lg:top-[92px] left-0
           h-full lg:h-[calc(100vh-108px)]
           w-72 lg:w-64
           bg-surface z-50 lg:z-0
           -translate-x-full lg:translate-x-0
           transition-transform duration-300
           shrink-0
           rounded-none lg:rounded-card
           lg:shadow-soft
           overflow-y-auto"
>

    <div
        class="flex items-center justify-between
               px-4 py-3
               lg:hidden
               border-b border-coral-light"
    >
        <span class="font-display font-bold text-ink">
            Danh mục
        </span>

        <button
            id="mk-sidebar-close"
            onclick="
                document.getElementById('mk-sidebar')
                    .classList.add('-translate-x-full');

                document.getElementById('mk-sidebar-overlay')
                    .classList.add('hidden');
            "
            class="p-1 text-ink-soft"
        >
            <svg
                xmlns="http://www.w3.org/2000/svg"
                class="w-5 h-5"
                fill="none"
                viewBox="0 0 24 24"
                stroke="currentColor"
                stroke-width="2"
            >
                <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    d="M6 18L18 6M6 6l12 12"
                />
            </svg>
        </button>
    </div>

    <nav class="py-2">

        <ul class="divide-y divide-cream">

            @foreach ($categories as $cat)

                @php
                    /*
                    |--------------------------------------------------------------------------
                    | Chuẩn hóa dữ liệu category
                    |--------------------------------------------------------------------------
                    |
                    | View composer có thể trả array hoặc model đã convert.
                    | Vì file hiện tại đang dùng $cat['slug'], nên tiếp tục hỗ trợ array.
                    */

                    $catName =
                        $cat['name']
                        ?? 'Danh mục';

                    $catSlug =
                        $cat['slug']
                        ?? '#';

                    $catIcon =
                        $cat['icon']
                        ?? '📦';

                    $catImage =
                        $cat['image']
                        ?? null;

                    /*
                    |--------------------------------------------------------------------------
                    | Resolve URL ảnh
                    |--------------------------------------------------------------------------
                    |
                    | Cloudinary:
                    | https://res.cloudinary.com/...
                    |
                    | Local cũ:
                    | categories/abc.png
                    */

                    $catImageUrl = null;

                    if ($catImage) {

                        if (
                            str_starts_with($catImage, 'http://')
                            ||
                            str_starts_with($catImage, 'https://')
                        ) {
                            $catImageUrl =
                                $catImage;
                        } else {
                            $catImageUrl =
                                asset(
                                    'storage/' .
                                    ltrim(
                                        $catImage,
                                        '/'
                                    )
                                );
                        }
                    }
                @endphp


                <li>

                    <a
                        href="{{ route('category.show', $catSlug) }}"
                        class="group
                               flex items-center gap-3
                               px-4 py-2.5
                               hover:bg-coral-light/60
                               transition-colors"
                    >

                        {{-- ICON / IMAGE --}}
                        <span
                            class="category-blob
                                   w-12 h-12
                                   shrink-0
                                   overflow-hidden
                                   flex items-center justify-center"
                        >

                            @if ($catImageUrl)

                                <img
                                    src="{{ $catImageUrl }}"
                                    alt="{{ $catName }}"
                                    class="w-full h-full
                                           object-contain
                                           p-1"
                                    loading="lazy"
                                    onerror="
                                        this.style.display='none';
                                        this.nextElementSibling.style.display='flex';
                                    "
                                >

                                <span
                                    style="display:none;"
                                    class="w-full h-full
                                           items-center justify-center
                                           text-xl"
                                >
                                    {{ $catIcon }}
                                </span>

                            @else

                                <span
                                    class="w-full h-full
                                           flex items-center justify-center
                                           text-xl"
                                >
                                    {{ $catIcon }}
                                </span>

                            @endif

                        </span>


                        {{-- CATEGORY NAME --}}
                        <span
                            class="flex-1
                                   text-sm font-medium
                                   text-ink
                                   group-hover:text-coral"
                        >
                            {{ $catName }}
                        </span>


                        {{-- ARROW --}}
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            class="w-4 h-4
                                   text-ink-soft/50
                                   group-hover:text-coral"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                            stroke-width="2"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M9 5l7 7-7 7"
                            />
                        </svg>

                    </a>

                </li>

            @endforeach

        </ul>

    </nav>


    {{-- Promo tile inside sidebar --}}
    <a
        href="#"
        class="hidden lg:block
               mx-3 mt-2 mb-4
               rounded-card
               overflow-hidden
               shadow-soft"
    >
        <div
            class="bg-gradient-to-br
                   from-coral to-peach
                   p-4 text-white"
        >
            <p
                class="font-display font-bold
                       text-lg leading-tight"
            >
                Giá tốt<br>
                hôm nay
            </p>

            <p
                class="text-xs mt-1
                       text-white/90"
            >
                Ưu đãi ngập tràn cho mẹ & bé
            </p>
        </div>
    </a>

</aside>