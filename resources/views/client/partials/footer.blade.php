<style>
    /* =========================================================
       MOMMYKIDS FOOTER
    ========================================================== */

    #mk-footer {
        margin-top: 0;
        padding: 3rem 0 2rem;

        background: #2B2530;
        color: #ffffff;
    }

    .mk-footer-container {
        width: 100%;
        max-width: 1400x;

        margin: 0 auto;
        padding: 0 1.5rem;
    }

    .mk-footer-grid {
        display: grid;
        grid-template-columns:
            2fr 1fr 1fr 1fr 1fr;

        gap: 2rem;

        margin-bottom: 2rem;
    }


    /* =========================================================
       BRAND
    ========================================================== */

    .mk-footer-brand {
        display: flex;
        align-items: center;

        gap: .5rem;

        margin-bottom: .9rem;
    }

    .mk-footer-logo {
        width: 2rem;
        height: 2rem;

        display: flex;
        align-items: center;
        justify-content: center;

        flex-shrink: 0;

        border-radius: 50%;

        background: #FF6F81;
        color: #ffffff;

        font-family: 'Baloo 2', cursive;
        font-size: .9rem;
        font-weight: 800;
    }

    .mk-footer-brand-name {
        color: #ffffff;

        font-family: 'Baloo 2', cursive;
        font-size: 1.1rem;
        font-weight: 800;
    }

    .mk-footer-brand-name span {
        color: #FF6F81;
    }

    .mk-footer-description {
        max-width: 320px;

        margin: 0 0 .8rem;

        color: #DDD5DE;

        font-size: .825rem;
        line-height: 1.7;
    }

    .mk-footer-hotline {
        margin: 0;

        color: #EAE3EB;

        font-size: .825rem;
    }

    .mk-footer-hotline strong {
        color: #FF8292;
        font-weight: 700;
    }


    /* =========================================================
       COLUMNS
    ========================================================== */

    .mk-footer-title {
        position: relative;

        margin: 0 0 1rem;
        padding-bottom: .45rem;

        color: #ffffff;

        font-family: 'Baloo 2', cursive;
        font-size: .9rem;
        font-weight: 700;

        letter-spacing: .03em;
    }

    .mk-footer-title::after {
        content: "";

        position: absolute;
        left: 0;
        bottom: 0;

        width: 28px;
        height: 2px;

        border-radius: 999px;

        background: #FF6F81;
    }

    .mk-footer-list {
        display: flex;
        flex-direction: column;

        gap: .55rem;

        margin: 0;
        padding: 0;

        list-style: none;
    }

    .mk-footer-list a {
        display: inline-block;

        color: #D7CED9;

        font-size: .825rem;
        line-height: 1.4;

        text-decoration: none;

        transition:
            color .18s ease,
            transform .18s ease;
    }

    .mk-footer-list a:hover {
        color: #FF8292;
        transform: translateX(2px);
    }


    /* =========================================================
       SOCIAL
    ========================================================== */

    .mk-footer-social {
        display: flex;

        gap: .55rem;

        margin-bottom: .9rem;
    }

    .mk-footer-social-link {
        width: 2.35rem;
        height: 2.35rem;

        display: flex;
        align-items: center;
        justify-content: center;

        border: 1px solid rgba(255,255,255,.10);
        border-radius: .6rem;

        background: rgba(255,255,255,.10);
        color: #ffffff;

        font-size: .78rem;
        font-weight: 600;

        text-decoration: none;

        transition:
            background .18s ease,
            border-color .18s ease,
            transform .18s ease;
    }

    .mk-footer-social-link:hover {
        border-color: #FF6F81;

        background: #FF6F81;

        transform: translateY(-2px);
    }

    .mk-footer-app-text {
        margin: 0;

        color: #D7CED9;

        font-size: .78rem;
        line-height: 1.5;
    }


    /* =========================================================
       BOTTOM
    ========================================================== */

    .mk-footer-bottom {
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;

        gap: 1rem;

        padding-top: 1.25rem;

        border-top: 1px solid
            rgba(255,255,255,.14);
    }

    .mk-footer-bottom p {
        margin: 0;

        color: #BBB1BE;

        font-size: .75rem;
        line-height: 1.5;
    }

    .mk-footer-bottom-features {
        display: flex;
        align-items: center;
        flex-wrap: wrap;

        gap: .4rem;
    }

    .mk-footer-bottom-features span {
        color: #CFC5D1;
    }

    .mk-footer-bottom-features .dot {
        color: #FF6F81;
    }


    /* =========================================================
       RESPONSIVE
    ========================================================== */

    @media (max-width: 900px) {
        .mk-footer-grid {
            grid-template-columns:
                repeat(2, minmax(0, 1fr));
        }

        .mk-footer-grid > div:first-child {
            grid-column: 1 / -1;
        }
    }

    @media (max-width: 520px) {
        #mk-footer {
            padding:
                2.5rem 0
                5.5rem;
        }

        .mk-footer-container {
            padding:
                0 1rem;
        }

        .mk-footer-grid {
            grid-template-columns:
                1fr;

            gap: 1.75rem;
        }

        .mk-footer-grid > div:first-child {
            grid-column: auto;
        }

        .mk-footer-bottom {
            align-items: flex-start;
            flex-direction: column;
        }
    }
</style>


<footer id="mk-footer">

    <div class="mk-footer-container">

        <div class="mk-footer-grid">

            {{-- =====================================================
                BRAND
            ====================================================== --}}
            <div>

                <div class="mk-footer-brand">

                    <span class="mk-footer-logo">
                        M
                    </span>

                    <span class="mk-footer-brand-name">
                        Mommy<span>Kids</span>
                    </span>

                </div>


                <p class="mk-footer-description">
                    Chuỗi cửa hàng mẹ và bé chính hãng —
                    Đồng hành cùng mẹ, chắp cánh cho bé.
                </p>


                <p class="mk-footer-hotline">
                    Hotline:
                    <strong>
                        1800 6886
                    </strong>
                </p>

            </div>


            {{-- =====================================================
                ABOUT
            ====================================================== --}}
            <div>

                <p class="mk-footer-title">
                    Về chúng tôi
                </p>

                <ul class="mk-footer-list">

                    <li>
                        <a href="#mk-about">
                            Giới thiệu
                        </a>
                    </li>

                    <li>
                        <a href="#mk-stores">
                            Hệ thống cửa hàng
                        </a>
                    </li>

                    <li>
                        <a href="#">
                            Tuyển dụng
                        </a>
                    </li>

                    <li>
                        <a href="#mk-footer">
                            Liên hệ
                        </a>
                    </li>

                </ul>

            </div>


            {{-- =====================================================
                POLICY
            ====================================================== --}}
            <div>

                <p class="mk-footer-title">
                    Chính sách
                </p>

                <ul class="mk-footer-list">

                    <li>
                        <a href="#">
                            Đổi trả hàng
                        </a>
                    </li>

                    <li>
                        <a href="#">
                            Vận chuyển
                        </a>
                    </li>

                    <li>
                        <a href="#">
                            Bảo mật
                        </a>
                    </li>

                    <li>
                        <a href="#">
                            Thanh toán
                        </a>
                    </li>

                </ul>

            </div>


            {{-- =====================================================
                GUIDE
            ====================================================== --}}
            <div>

                <p class="mk-footer-title">
                    Cẩm nang
                </p>

                <ul class="mk-footer-list">

                    <li>
                        <a href="{{ url('/cam-nang') }}">
                            Mẹ mang thai
                        </a>
                    </li>

                    <li>
                        <a href="{{ url('/cam-nang') }}">
                            Chăm sóc sơ sinh
                        </a>
                    </li>

                    <li>
                        <a href="{{ url('/cam-nang') }}">
                            Ăn dặm cho bé
                        </a>
                    </li>

                    <li>
                        <a href="{{ url('/cam-nang') }}">
                            Phát triển trẻ em
                        </a>
                    </li>

                </ul>

            </div>


            {{-- =====================================================
                SOCIAL
            ====================================================== --}}
            <div>

                <p class="mk-footer-title">
                    Kết nối
                </p>


                <div class="mk-footer-social">

                    <a
                        href="#"
                        class="mk-footer-social-link"
                        aria-label="Facebook"
                    >
                        FB
                    </a>

                    <a
                        href="#"
                        class="mk-footer-social-link"
                        aria-label="Zalo"
                    >
                        Zalo
                    </a>

                    <a
                        href="#"
                        class="mk-footer-social-link"
                        aria-label="Instagram"
                    >
                        IG
                    </a>

                </div>


                <p class="mk-footer-app-text">
                    Tải app nhận voucher
                    <strong style="color:#FF8292;">
                        30K
                    </strong>
                </p>

            </div>

        </div>


        {{-- =====================================================
            FOOTER BOTTOM
        ====================================================== --}}
        <div class="mk-footer-bottom">

            <p>
                © {{ date('Y') }} MommyKids.
                Đã đăng ký bản quyền.
            </p>


            <p class="mk-footer-bottom-features">

                <span>
                    Chính hãng
                </span>

                <span class="dot">
                    ·
                </span>

                <span>
                    Kiến thức
                </span>

                <span class="dot">
                    ·
                </span>

                <span>
                    Tư vấn
                </span>

                <span class="dot">
                    ·
                </span>

                <span>
                    Giao nhanh
                </span>

            </p>

        </div>

    </div>

</footer>