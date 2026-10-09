@extends('client.layouts.app')

@section('title', 'Chính sách vận chuyển - MommyKids')


@section('content')

<div class="container mx-auto px-4 py-8">

    {{-- Breadcrumb --}}
    <div class="text-sm text-gray-500 mb-6">
        Trang chủ 
        <span class="mx-2">/</span>
        <span class="text-gray-800">
            Chính sách vận chuyển
        </span>
    </div>


    <div class="bg-white rounded-2xl shadow-sm p-8">


        <h1 class="text-3xl font-bold text-gray-900 mb-6">
            Chính sách vận chuyển & giao hàng
        </h1>


        <hr class="mb-6">


        <div class="space-y-6 text-gray-700 leading-relaxed">


            <div>

                <h2 class="font-bold text-lg text-gray-900 mb-2">
                    1. Phạm vi giao hàng
                </h2>

                <p>
                    MommyKids hỗ trợ giao hàng trên toàn quốc đến khách hàng tại 
                    63 tỉnh thành thông qua các đơn vị vận chuyển uy tín.
                </p>

            </div>



            <div>

                <h2 class="font-bold text-lg text-gray-900 mb-2">
                    2. Thời gian giao hàng
                </h2>


                <ul class="list-disc pl-6 space-y-2">

                    <li>
                        <strong>Khu vực nội thành Hà Nội:</strong>
                        Dự kiến nhận hàng từ 1 - 2 ngày làm việc.
                    </li>

                    <li>
                        <strong>Các tỉnh/thành phố khác:</strong>
                        Dự kiến nhận hàng từ 2 - 5 ngày làm việc.
                    </li>

                    <li>
                        Thời gian giao hàng có thể thay đổi tùy theo điều kiện
                        vận chuyển, thời tiết hoặc các dịp lễ, Tết.
                    </li>

                </ul>

            </div>




            <div>

                <h2 class="font-bold text-lg text-gray-900 mb-2">
                    3. Phí vận chuyển
                </h2>


                <ul class="list-disc pl-6 space-y-2">

                    <li>
                        Miễn phí vận chuyển cho đơn hàng có giá trị từ 
                        <strong>300.000đ</strong>.
                    </li>

                    <li>
                        Đối với đơn hàng dưới 300.000đ, phí vận chuyển được
                        tính theo khu vực giao hàng.
                    </li>

                </ul>

            </div>




            <div>

                <h2 class="font-bold text-lg text-gray-900 mb-2">
                    4. Kiểm tra hàng khi nhận
                </h2>


                <p>
                    Khách hàng vui lòng kiểm tra tình trạng sản phẩm trước khi
                    nhận hàng. Trong trường hợp sản phẩm bị lỗi, thiếu hàng hoặc
                    giao sai sản phẩm, vui lòng liên hệ MommyKids để được hỗ trợ.
                </p>

            </div>




            <div>

                <h2 class="font-bold text-lg text-gray-900 mb-2">
                    5. Lưu ý khi giao hàng
                </h2>


                <ul class="list-disc pl-6 space-y-2">

                    <li>
                        Vui lòng cung cấp đầy đủ và chính xác thông tin nhận hàng.
                    </li>

                    <li>
                        Đơn hàng có thể bị chậm trong trường hợp khách hàng
                        không liên hệ được hoặc thay đổi địa chỉ nhận hàng.
                    </li>

                    <li>
                        MommyKids luôn cố gắng đảm bảo đơn hàng được giao
                        nhanh chóng và an toàn nhất.
                    </li>

                </ul>

            </div>


        </div>


    </div>

</div>

@endsection