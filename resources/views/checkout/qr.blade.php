@extends('client.layouts.app')

@section('title', 'Quét QR thanh toán - MommyKids')

@section('content')

<div class="mk-pay-page">

    <div class="mk-pay-wrap">

        <a
            href="{{ route('checkout.index') }}"
            class="mk-back"
        >
            ← Quay lại thanh toán
        </a>


        {{-- =====================================================
             COUNTDOWN
        ====================================================== --}}
        <div class="mk-countdown-card">

            <div class="mk-clock">
                ◷
            </div>

            <div>

                <strong>
                    Vui lòng thanh toán trong vòng
                    <span id="mk-countdown">
                        15:00
                    </span>
                </strong>

                <small>
                    Sau khi hết thời gian, đơn hàng sẽ bị hủy.
                </small>

            </div>

        </div>


        {{-- =====================================================
             QR PAYMENT CARD
        ====================================================== --}}
        <section class="mk-qr-card">

            <h1>
                Chuyển khoản ngân hàng
            </h1>

            <p>
                Quét mã QR bên dưới để thanh toán đơn hàng
            </p>


            {{-- QR --}}
            <img
                src="{{ $qrUrl }}"
                class="mk-qr"
                alt="VietQR thanh toán MommyKids"
            >


            {{-- =================================================
                 BANK INFORMATION
            ================================================== --}}
            <div class="mk-bank-info">

                <div>

                    <span>
                        Số tài khoản
                    </span>

                    <strong>
                        {{ $accountNo }}
                    </strong>

                </div>


                <div>

                    <span>
                        Chủ tài khoản
                    </span>

                    <strong>
                        {{ $accountName }}
                    </strong>

                </div>


                <div>

                    <span>
                        Ngân hàng
                    </span>

                    <strong>
                        MB Bank
                    </strong>

                </div>


                <div>

                    <span>
                        Số tiền
                    </span>

                    <strong class="mk-pink">
                        {{ number_format($total, 0, ',', '.') }}đ
                    </strong>

                </div>


                <div>

                    <span>
                        Nội dung chuyển khoản
                    </span>

                    <strong>
                        {{ $transferContent }}
                    </strong>

                </div>

            </div>


            {{-- =================================================
                 NOTE
            ================================================== --}}
            <div class="mk-note">

                <strong>
                    Lưu ý:
                </strong>

                <ul>

                    <li>
                        Vui lòng chuyển đúng số tiền.
                    </li>

                    <li>
                        Không thay đổi nội dung chuyển khoản.
                    </li>

                    <li>
                        Sau khi ngân hàng ghi nhận giao dịch,
                        hệ thống sẽ tự động xác nhận qua SePay.
                    </li>

                </ul>

            </div>


            {{-- =================================================
                 PAYMENT STATUS
            ================================================== --}}
            <div
                id="mk-payment-status"
                class="mk-payment-status"
            >

                <div
                    id="mk-payment-spinner"
                    class="mk-spinner"
                ></div>

                <div>

                    <strong id="mk-payment-status-title">
                        Đang chờ thanh toán
                    </strong>

                    <small id="mk-payment-status-text">
                        Hệ thống đang tự động kiểm tra giao dịch...
                    </small>

                </div>

            </div>

        </section>

    </div>

</div>


<style>

.mk-pay-page {
    background: #fff8f7;
    min-height: 100vh;
    padding: 32px 16px 60px;
}


.mk-pay-wrap {
    max-width: 620px;
    margin: auto;
}


.mk-back {
    color: #ff5f76;
    text-decoration: none;
}


.mk-back:hover {
    text-decoration: underline;
}


/* =============================================================
   COUNTDOWN
============================================================= */

.mk-countdown-card {
    display: flex;
    gap: 14px;
    align-items: center;

    background: #fff8e8;

    border: 1px solid #ffd98a;
    border-radius: 14px;

    padding: 16px;
    margin: 20px 0;
}


.mk-countdown-card strong {
    display: block;
}


.mk-countdown-card small {
    display: block;
    color: #746a59;
    margin-top: 4px;
}


.mk-clock {
    font-size: 30px;
    color: #f2a900;
}


/* =============================================================
   QR CARD
============================================================= */

.mk-qr-card {
    background: #fff;

    border: 1px solid #f0e1e4;
    border-radius: 18px;

    padding: 26px;

    box-shadow:
        0 8px 28px rgba(70, 40, 45, .05);

    text-align: center;
}


.mk-qr-card h1 {
    font-size: 24px;
    margin: 0 0 6px;
}


.mk-qr-card > p {
    color: #81777d;
}


/* =============================================================
   QR IMAGE
============================================================= */

.mk-qr {
    width: 330px;
    max-width: 100%;

    margin: 18px auto;

    display: block;

    border-radius: 14px;
}


/* =============================================================
   BANK INFORMATION
============================================================= */

.mk-bank-info {
    background: #fff7f8;

    border: 1px solid #ffd9df;
    border-radius: 14px;

    padding: 16px;

    text-align: left;
}


.mk-bank-info > div {
    display: flex;

    justify-content: space-between;

    align-items: flex-start;

    gap: 20px;

    padding: 8px 0;
}


.mk-bank-info span {
    color: #81777d;
}


.mk-bank-info strong {
    text-align: right;

    max-width: 60%;

    word-break: break-word;
}


.mk-pink {
    color: #ff536e;
}


/* =============================================================
   NOTE
============================================================= */

.mk-note {
    margin-top: 16px;

    background: #eef9ff;

    border: 1px solid #bfe9ff;
    border-radius: 14px;

    padding: 16px;

    text-align: left;

    color: #31596b;
}


.mk-note ul {
    margin: 8px 0 0 18px;
}


.mk-note li + li {
    margin-top: 5px;
}


/* =============================================================
   PAYMENT STATUS
============================================================= */

.mk-payment-status {
    margin-top: 20px;

    display: flex;

    align-items: center;

    justify-content: center;

    gap: 12px;

    padding: 15px 18px;

    border-radius: 14px;

    background: #fff8e8;

    border: 1px solid #ffd98a;

    text-align: left;
}


.mk-payment-status strong {
    display: block;

    color: #5f5138;
}


.mk-payment-status small {
    display: block;

    color: #88775c;

    margin-top: 3px;
}


/* =============================================================
   PAYMENT SUCCESS STATE
============================================================= */

.mk-payment-status.is-success {
    background: #effcf4;

    border-color: #a7e6bd;
}


.mk-payment-status.is-success strong {
    color: #178548;
}


.mk-payment-status.is-success small {
    color: #39865b;
}


/* =============================================================
   PAYMENT ERROR / EXPIRED
============================================================= */

.mk-payment-status.is-error {
    background: #fff1f2;

    border-color: #ffc8cf;
}


.mk-payment-status.is-error strong {
    color: #d9475b;
}


.mk-payment-status.is-error small {
    color: #a9535f;
}


/* =============================================================
   LOADING SPINNER
============================================================= */

.mk-spinner {
    width: 22px;
    height: 22px;

    flex-shrink: 0;

    border-radius: 999px;

    border: 3px solid #ffe1a4;
    border-top-color: #f2a900;

    animation: mk-spin .8s linear infinite;
}


@keyframes mk-spin {

    to {
        transform: rotate(360deg);
    }

}


/* =============================================================
   RESPONSIVE
============================================================= */

@media (max-width: 640px) {

    .mk-pay-page {
        padding:
            20px
            12px
            40px;
    }


    .mk-qr-card {
        padding: 20px 16px;
    }


    .mk-qr-card h1 {
        font-size: 21px;
    }


    .mk-bank-info > div {
        gap: 12px;
    }


    .mk-bank-info span,
    .mk-bank-info strong {
        font-size: 14px;
    }

}


</style>

<div
    id="mk-payment-config"
    data-order-code="{{ $order['code'] }}"
    data-status-url="{{ route('checkout.payment-status', ['code' => $order['code']]) }}"
    hidden
></div>
<script>

document.addEventListener(
    'DOMContentLoaded',
    function () {

        /*
        |--------------------------------------------------------------------------
        | CẤU HÌNH
        |--------------------------------------------------------------------------
        */

       const paymentConfig =
    document.getElementById('mk-payment-config');

const orderCode =
    paymentConfig.dataset.orderCode;

const statusUrl =
    paymentConfig.dataset.statusUrl;


        const countdownElement =
            document.getElementById(
                'mk-countdown'
            );


        const statusBox =
            document.getElementById(
                'mk-payment-status'
            );


        const statusTitle =
            document.getElementById(
                'mk-payment-status-title'
            );


        const statusText =
            document.getElementById(
                'mk-payment-status-text'
            );


        const spinner =
            document.getElementById(
                'mk-payment-spinner'
            );


        /*
        |--------------------------------------------------------------------------
        | COUNTDOWN 15 PHÚT
        |--------------------------------------------------------------------------
        */

        let seconds =
            15 * 60;


        let expired =
            false;


        const countdownTimer =
            setInterval(
                function () {

                    seconds--;


                    const minutes =
                        String(
                            Math.floor(
                                seconds / 60
                            )
                        ).padStart(
                            2,
                            '0'
                        );


                    const remainingSeconds =
                        String(
                            seconds % 60
                        ).padStart(
                            2,
                            '0'
                        );


                    countdownElement.textContent =
                        `${minutes}:${remainingSeconds}`;


                    if (
                        seconds <= 0
                    ) {

                        clearInterval(
                            countdownTimer
                        );


                        countdownElement.textContent =
                            '00:00';


                        expired =
                            true;


                        stopPaymentPolling();


                        statusBox.classList.add(
                            'is-error'
                        );


                        spinner.style.display =
                            'none';


                        statusTitle.textContent =
                            'Đã hết thời gian thanh toán';


                        statusText.textContent =
                            'Phiên thanh toán đã hết hạn. Vui lòng quay lại và tạo đơn hàng mới.';

                    }

                },
                1000
            );


        /*
        |--------------------------------------------------------------------------
        | CHECK PAYMENT STATUS
        |--------------------------------------------------------------------------
        */

        let checking =
            false;


        let paymentTimer =
            null;


        async function checkPaymentStatus()
        {

            if (
                checking ||
                expired
            ) {
                return;
            }


            checking =
                true;


            try {

                const response =
                    await fetch(
                        statusUrl,
                        {
                            method: 'GET',

                            headers: {
                                'Accept':
                                    'application/json',

                                'X-Requested-With':
                                    'XMLHttpRequest'
                            },

                            credentials:
                                'same-origin',

                            cache:
                                'no-store'
                        }
                    );


                /*
                 * Session/order không còn tồn tại.
                 */
                if (
                    response.status === 404
                ) {

                    console.warn(
                        'Không tìm thấy trạng thái thanh toán của đơn:',
                        orderCode
                    );


                    return;

                }


                if (
                    !response.ok
                ) {

                    console.warn(
                        'Payment status HTTP:',
                        response.status
                    );


                    return;

                }


                const data =
                    await response.json();


                /*
                |--------------------------------------------------------------------------
                | SEPAY ĐÃ XÁC NHẬN THANH TOÁN
                |--------------------------------------------------------------------------
                */

                if (
                    data.paid === true
                ) {

                    stopPaymentPolling();


                    clearInterval(
                        countdownTimer
                    );


                    statusBox.classList.remove(
                        'is-error'
                    );


                    statusBox.classList.add(
                        'is-success'
                    );


                    spinner.style.display =
                        'none';


                    statusTitle.textContent =
                        'Thanh toán thành công';


                    statusText.textContent =
                        'Đã nhận được giao dịch. Đang chuyển đến trang xác nhận đơn hàng...';


                    /*
                     * Chờ một chút để người dùng
                     * nhìn thấy trạng thái thành công.
                     */
                    setTimeout(
                        function () {

                            window.location.href =
                                data.redirect;

                        },
                        1000
                    );

                }

            }
            catch (
                error
            ) {

                /*
                 * Không báo lỗi lớn ra UI vì có thể
                 * chỉ là lỗi mạng tạm thời.
                 * Lần polling sau sẽ thử lại.
                 */

                console.error(
                    'Không kiểm tra được trạng thái thanh toán:',
                    error
                );

            }
            finally {

                checking =
                    false;

            }

        }


        /*
        |--------------------------------------------------------------------------
        | POLLING
        |--------------------------------------------------------------------------
        |
        | Mỗi 2 giây hỏi Laravel:
        |
        | checkout.payment-status
        |
        | Laravel đọc Cache đã được SePay webhook cập nhật.
        |
        */

        function startPaymentPolling()
        {

            checkPaymentStatus();


            paymentTimer =
                setInterval(
                    checkPaymentStatus,
                    2000
                );

        }


        function stopPaymentPolling()
        {

            if (
                paymentTimer
            ) {

                clearInterval(
                    paymentTimer
                );


                paymentTimer =
                    null;

            }

        }


        /*
        |--------------------------------------------------------------------------
        | START
        |--------------------------------------------------------------------------
        */

        startPaymentPolling();


        /*
         * Khi rời khỏi trang thì dừng timer.
         */
        window.addEventListener(
            'beforeunload',
            function () {

                stopPaymentPolling();

            }
        );

    }
);

</script>

@endsection