@php
    use Illuminate\Support\Str;

    $productNameLower = Str::lower($product->name ?? '');

    /*
    |--------------------------------------------------------------------------
    | NHẬN DIỆN NHÓM SẢN PHẨM ĐỒ SƠ SINH
    |--------------------------------------------------------------------------
    */
    $isNewbornClothing =
        Str::contains($productNameLower, ['bodysuit', 'body sơ sinh', 'set đồ sơ sinh']);

    $isNewbornMittens =
        Str::contains($productNameLower, ['bao tay', 'bao chân']);

    $isNewbornHat =
        Str::contains($productNameLower, ['mũ sơ sinh']);

    $isNewbornBib =
        Str::contains($productNameLower, ['yếm sơ sinh']);

    $isNewbornTowel =
        Str::contains($productNameLower, ['khăn sữa', 'muslin']);

    $isNewbornPad =
        Str::contains($productNameLower, ['tấm lót']);

    $isNewbornPillow =
        Str::contains($productNameLower, ['gối xô', 'gối sơ sinh']);

    $isNewbornSwaddle =
        Str::contains($productNameLower, ['ủ kén', 'chăn ủ']);

    $isNewbornSet =
        Str::contains($productNameLower, ['set đồ sơ sinh']);

    $isNewbornBodysuit =
        Str::contains($productNameLower, ['bodysuit']);

    $isNewbornBodyShort =
        Str::contains($productNameLower, ['body sơ sinh']);

    $isNewbornMuslin =
        Str::contains($productNameLower, ['muslin']);

    $isNewbornCocoon =
        Str::contains($productNameLower, ['ủ kén']);

    $isNewbornBlanket =
        Str::contains($productNameLower, ['chăn ủ']);

    /*
    |--------------------------------------------------------------------------
    | THÔNG TIN LOẠI SẢN PHẨM
    |--------------------------------------------------------------------------
    */
    $newbornMeta = match (true) {
        $isNewbornClothing => [
            'group' => 'Quần áo sơ sinh',
            'form' => Str::contains($productNameLower, 'bodysuit') ? 'Bodysuit liền thân' :
                (Str::contains($productNameLower, 'body sơ sinh') ? 'Body đùi' : 'Set quần áo sơ sinh'),
            'purpose' => 'Mặc hằng ngày',
            'age' => $ageText ?: (Str::contains($productNameLower, '0-3') ? '0–3 tháng' : '0–12 tháng tùy size'),
            'trust_title' => 'Trang phục dành cho giai đoạn đầu đời',
            'trust_text' => 'Ưu tiên chất liệu mềm, đường may gọn và lựa chọn size phù hợp với cân nặng, chiều dài cơ thể của bé.',
        ],

        $isNewbornMittens => [
            'group' => 'Phụ kiện sơ sinh',
            'form' => 'Bao tay & bao chân',
            'purpose' => 'Giữ ấm nhẹ và bảo vệ da',
            'age' => $ageText ?: 'Sơ sinh',
            'trust_title' => 'Phụ kiện nhỏ nhưng cần kiểm tra độ vừa',
            'trust_text' => 'Phần bo nên ôm vừa phải, không siết cổ tay hoặc cổ chân và cần thay ngay khi sản phẩm bị ẩm.',
        ],

        $isNewbornHat => [
            'group' => 'Phụ kiện sơ sinh',
            'form' => 'Mũ sơ sinh',
            'purpose' => 'Che đầu và giữ ấm nhẹ',
            'age' => $ageText ?: 'Sơ sinh',
            'trust_title' => 'Đội vừa đầu và ưu tiên sự thông thoáng',
            'trust_text' => 'Mũ cần có độ co giãn phù hợp, không quá chật và nên tháo khi bé ra nhiều mồ hôi.',
        ],

        $isNewbornBib => [
            'group' => 'Phụ kiện sơ sinh',
            'form' => 'Yếm vải',
            'purpose' => 'Thấm sữa và nước bọt',
            'age' => $ageText ?: 'Từ sơ sinh',
            'trust_title' => 'Giữ vùng cổ và áo khô ráo hơn',
            'trust_text' => 'Nên thay yếm khi đã ẩm và không để bé đeo yếm khi ngủ mà không có người lớn giám sát.',
        ],

        $isNewbornTowel => [
            'group' => 'Khăn & đồ vải',
            'form' => Str::contains($productNameLower, 'muslin') ? 'Khăn muslin nhiều lớp' : 'Khăn sữa nhiều lớp',
            'purpose' => 'Lau sữa, lau miệng và vệ sinh nhẹ',
            'age' => $ageText ?: 'Từ sơ sinh',
            'trust_title' => 'Đồ dùng sử dụng nhiều lần trong ngày',
            'trust_text' => 'Khăn nên được giặt sạch, phơi khô hoàn toàn và phân loại riêng cho vùng mặt, miệng nếu gia đình có nhu cầu.',
        ],

        $isNewbornPad => [
            'group' => 'Vệ sinh sơ sinh',
            'form' => 'Tấm lót nhiều lớp',
            'purpose' => 'Lót khi thay tã và chăm sóc bé',
            'age' => $ageText ?: 'Từ sơ sinh',
            'trust_title' => 'Giữ bề mặt chăm sóc sạch hơn',
            'trust_text' => 'Sử dụng trên bề mặt phẳng, thay mới sau khi thấm nhiều và không tái sử dụng sản phẩm dùng một lần.',
        ],

        $isNewbornPillow => [
            'group' => 'Đồ ngủ & đồ vải',
            'form' => 'Gối xô sơ sinh',
            'purpose' => 'Hỗ trợ chăm sóc trong thời gian ngắn',
            'age' => $ageText ?: 'Sơ sinh',
            'trust_title' => 'Ưu tiên nguyên tắc ngủ an toàn',
            'trust_text' => 'Không dùng gối hoặc vật mềm để che mặt bé và cần tuân thủ hướng dẫn chăm sóc giấc ngủ an toàn.',
        ],

        $isNewbornSwaddle => [
            'group' => 'Chăn ủ & giữ ấm',
            'form' => Str::contains($productNameLower, 'ủ kén') ? 'Ủ kén sơ sinh' : 'Chăn ủ có mũ',
            'purpose' => 'Quấn, ủ và giữ ấm nhẹ',
            'age' => $ageText ?: (Str::contains($productNameLower, '0-3') ? '0–3 tháng' : 'Từ sơ sinh'),
            'trust_title' => 'Quấn vừa phải và luôn giữ đường thở thông thoáng',
            'trust_text' => 'Không quấn quá chặt, không để lớp vải che mũi hoặc miệng và điều chỉnh độ ấm theo nhiệt độ môi trường.',
        ],

        default => [
            'group' => 'Đồ sơ sinh',
            'form' => 'Đồ dùng chăm sóc bé',
            'purpose' => 'Chăm sóc hằng ngày',
            'age' => $ageText ?: 'Từ sơ sinh',
            'trust_title' => 'Chọn sản phẩm phù hợp nhu cầu của bé',
            'trust_text' => 'Ưu tiên chất liệu phù hợp, vệ sinh đúng cách và luôn kiểm tra tình trạng sản phẩm trước khi sử dụng.',
        ],
    };

    /*
    |--------------------------------------------------------------------------
    | 4 THẺ TỔNG QUAN
    |--------------------------------------------------------------------------
    */
    $newbornQuickFacts = match (true) {
        $isNewbornClothing => [
            ['title' => 'Kiểu sản phẩm', 'text' => $newbornMeta['form']],
            ['title' => 'Chất liệu', 'text' => $product->ingredients ?: 'Vải mềm phù hợp đồ sơ sinh'],
            ['title' => 'Độ tuổi', 'text' => $newbornMeta['age']],
            ['title' => 'Nhu cầu', 'text' => 'Mặc hằng ngày và thay đồ thuận tiện'],
        ],

        $isNewbornMittens => [
            ['title' => 'Thiết kế', 'text' => 'Set bao tay và bao chân'],
            ['title' => 'Chất liệu', 'text' => $product->ingredients ?: 'Vải mềm có độ co giãn'],
            ['title' => 'Độ tuổi', 'text' => $newbornMeta['age']],
            ['title' => 'Điểm chính', 'text' => 'Bo vừa, dễ mang và dễ thay'],
        ],

        $isNewbornHat => [
            ['title' => 'Kiểu dáng', 'text' => 'Mũ sơ sinh ôm nhẹ'],
            ['title' => 'Chất liệu', 'text' => $product->ingredients ?: 'Vải cotton mềm'],
            ['title' => 'Độ tuổi', 'text' => $newbornMeta['age']],
            ['title' => 'Sử dụng', 'text' => 'Trong thời tiết mát hoặc khi ra ngoài'],
        ],

        $isNewbornBib => [
            ['title' => 'Kiểu dáng', 'text' => 'Yếm vải mềm'],
            ['title' => 'Chất liệu', 'text' => $product->ingredients ?: 'Cotton thấm hút'],
            ['title' => 'Độ tuổi', 'text' => $newbornMeta['age']],
            ['title' => 'Công dụng', 'text' => 'Hứng sữa, nước bọt và giữ áo khô hơn'],
        ],

        $isNewbornTowel => [
            ['title' => 'Dạng khăn', 'text' => $newbornMeta['form']],
            ['title' => 'Chất liệu', 'text' => $product->ingredients ?: 'Vải cotton / muslin'],
            ['title' => 'Độ tuổi', 'text' => $newbornMeta['age']],
            ['title' => 'Công dụng', 'text' => 'Lau miệng, lau sữa và vệ sinh nhẹ'],
        ],

        $isNewbornPad => [
            ['title' => 'Cấu trúc', 'text' => 'Tấm lót nhiều lớp'],
            ['title' => 'Vật liệu', 'text' => $product->ingredients ?: 'Lớp thấm hút và màng đáy'],
            ['title' => 'Độ tuổi', 'text' => $newbornMeta['age']],
            ['title' => 'Nhu cầu', 'text' => 'Lót khi thay tã hoặc chăm sóc bé'],
        ],

        $isNewbornPillow => [
            ['title' => 'Dạng sản phẩm', 'text' => 'Gối xô sơ sinh'],
            ['title' => 'Chất liệu', 'text' => $product->ingredients ?: 'Vải xô mềm'],
            ['title' => 'Độ tuổi', 'text' => $newbornMeta['age']],
            ['title' => 'Lưu ý', 'text' => 'Sử dụng theo nguyên tắc ngủ an toàn'],
        ],

        $isNewbornSwaddle => [
            ['title' => 'Kiểu sản phẩm', 'text' => $newbornMeta['form']],
            ['title' => 'Chất liệu', 'text' => $product->ingredients ?: 'Vải mềm dùng cho trẻ nhỏ'],
            ['title' => 'Độ tuổi', 'text' => $newbornMeta['age']],
            ['title' => 'Nhu cầu', 'text' => 'Quấn bé và giữ ấm nhẹ khi cần'],
        ],

        default => [
            ['title' => 'Nhóm sản phẩm', 'text' => $newbornMeta['group']],
            ['title' => 'Chất liệu', 'text' => $product->ingredients ?: 'Theo thông tin sản phẩm'],
            ['title' => 'Độ tuổi', 'text' => $newbornMeta['age']],
            ['title' => 'Mục đích', 'text' => $newbornMeta['purpose']],
        ],
    };

    /*
    |--------------------------------------------------------------------------
    | CẤU TẠO / ĐẶC ĐIỂM CHÍNH
    |--------------------------------------------------------------------------
    */
    $newbornFeatureItems = match (true) {
        $isNewbornClothing => [
            [
                'title' => 'Bề mặt vải',
                'text' => 'Chất vải mềm tạo cảm giác dễ chịu khi tiếp xúc với làn da nhạy cảm của bé trong thời gian mặc hằng ngày.',
            ],
            [
                'title' => 'Đường may',
                'text' => 'Các đường may và mép vải cần gọn, hạn chế phần chỉ thừa hoặc vị trí cộm gây khó chịu khi bé vận động.',
            ],
            [
                'title' => 'Cúc / khuy',
                'text' => 'Thiết kế đóng mở giúp ba mẹ thay đồ nhanh hơn; nên kiểm tra cúc đã được cài chắc trước mỗi lần mặc.',
            ],
            [
                'title' => 'Phom dáng',
                'text' => 'Phom quần áo nên có độ rộng vừa đủ để bé cử động tay chân thoải mái mà không bị bó sát.',
            ],
        ],

        $isNewbornMittens => [
            [
                'title' => 'Phần bo',
                'text' => 'Bo tay và bo chân giữ sản phẩm đúng vị trí nhưng cần có độ đàn hồi vừa phải, không để lại vết siết trên da bé.',
            ],
            [
                'title' => 'Khoang bao',
                'text' => 'Khoang bên trong cần đủ rộng để ngón tay, ngón chân của bé có thể cử động tự nhiên.',
            ],
            [
                'title' => 'Mặt vải',
                'text' => 'Vải mềm giúp giảm ma sát và phù hợp sử dụng trong những tuần đầu khi da bé còn nhạy cảm.',
            ],
            [
                'title' => 'Đường may',
                'text' => 'Nên kiểm tra mặt trong để loại bỏ chỉ thừa hoặc sợi vải lỏng trước khi đeo cho bé.',
            ],
        ],

        $isNewbornHat => [
            [
                'title' => 'Vành mũ',
                'text' => 'Phần bo quanh đầu cần co giãn vừa phải, ôm nhẹ nhưng không tạo cảm giác siết.',
            ],
            [
                'title' => 'Thân mũ',
                'text' => 'Thiết kế gọn giúp che phần đầu khi thời tiết mát hoặc khi ba mẹ đưa bé ra ngoài.',
            ],
            [
                'title' => 'Chất vải',
                'text' => 'Chất liệu mềm và thoáng giúp hạn chế cảm giác bí khi bé đội trong thời gian phù hợp.',
            ],
            [
                'title' => 'Độ vừa',
                'text' => 'Mũ không nên che xuống vùng mắt, mũi hoặc làm bé khó chịu khi cử động đầu.',
            ],
        ],

        $isNewbornBib => [
            [
                'title' => 'Mặt yếm',
                'text' => 'Bề mặt yếm giúp thấm sữa, nước bọt hoặc lượng nhỏ chất lỏng trong quá trình chăm sóc bé.',
            ],
            [
                'title' => 'Vùng cổ',
                'text' => 'Phần cài hoặc buộc quanh cổ cần có khoảng hở phù hợp, không siết sát cổ bé.',
            ],
            [
                'title' => 'Độ thấm hút',
                'text' => 'Chất vải cotton hỗ trợ giữ phần áo phía trước khô hơn khi bé bị trớ sữa hoặc chảy nước bọt.',
            ],
            [
                'title' => 'Khả năng vệ sinh',
                'text' => 'Kích thước nhỏ giúp yếm dễ giặt, nhanh khô và thuận tiện thay nhiều lần trong ngày.',
            ],
        ],

        $isNewbornTowel => [
            [
                'title' => 'Cấu trúc nhiều lớp',
                'text' => 'Nhiều lớp vải tăng khả năng thấm hút trong khi vẫn giữ kích thước khăn gọn để sử dụng thường xuyên.',
            ],
            [
                'title' => 'Bề mặt mềm',
                'text' => 'Bề mặt vải phù hợp cho các thao tác lau nhẹ vùng miệng, má hoặc tay chân của bé.',
            ],
            [
                'title' => 'Kích thước gọn',
                'text' => 'Khăn nhỏ dễ xếp vào túi đồ, giỏ sơ sinh và có thể chuẩn bị nhiều chiếc để thay trong ngày.',
            ],
            [
                'title' => 'Dễ phân loại',
                'text' => 'Ba mẹ có thể chia riêng khăn lau miệng, khăn lau sữa và khăn vệ sinh để sử dụng vệ sinh hơn.',
            ],
        ],

        $isNewbornPad => [
            [
                'title' => 'Lớp bề mặt',
                'text' => 'Bề mặt tiếp xúc được thiết kế để tiếp nhận chất lỏng trong quá trình thay tã hoặc vệ sinh cho bé.',
            ],
            [
                'title' => 'Lớp thấm hút',
                'text' => 'Phần lõi hỗ trợ hút và giữ chất lỏng, giúp khu vực chăm sóc sạch hơn trong thời gian sử dụng.',
            ],
            [
                'title' => 'Màng đáy',
                'text' => 'Lớp đáy hạn chế chất lỏng thấm xuống nệm, ga hoặc bề mặt phía dưới.',
            ],
            [
                'title' => 'Thiết kế dùng nhanh',
                'text' => 'Tấm lót có thể trải nhanh trên bề mặt phẳng và thay mới khi đã bẩn hoặc thấm nhiều.',
            ],
        ],

        $isNewbornPillow => [
            [
                'title' => 'Vỏ gối',
                'text' => 'Vỏ xô mềm và thoáng giúp sản phẩm dễ vệ sinh, phù hợp với nhu cầu chăm sóc trong thời gian ngắn.',
            ],
            [
                'title' => 'Ruột gối',
                'text' => 'Ruột gối tạo độ êm nhẹ nhưng không nên dùng để nâng đầu quá cao hoặc che quanh mặt bé.',
            ],
            [
                'title' => 'Kích thước',
                'text' => 'Kích thước nhỏ giúp sản phẩm phù hợp không gian chăm sóc trẻ sơ sinh và dễ mang theo.',
            ],
            [
                'title' => 'Vệ sinh',
                'text' => 'Gối cần được làm khô hoàn toàn sau khi vệ sinh để hạn chế ẩm và mùi khó chịu.',
            ],
        ],

        $isNewbornSwaddle => [
            [
                'title' => 'Lớp vải',
                'text' => 'Chất vải mềm bao quanh cơ thể bé, phù hợp cho việc ủ hoặc quấn nhẹ trong thời gian ngắn.',
            ],
            [
                'title' => 'Khoang quấn',
                'text' => 'Không gian quấn cần đủ rộng để hông và chân bé không bị ép sát hoặc hạn chế cử động tự nhiên.',
            ],
            [
                'title' => 'Phần đầu / mũ',
                'text' => 'Nếu sản phẩm có mũ, phần này hỗ trợ che đầu khi cần nhưng tuyệt đối không được che mũi hoặc miệng.',
            ],
            [
                'title' => 'Khả năng điều chỉnh',
                'text' => 'Ba mẹ nên quấn vừa phải và thay đổi mức độ giữ ấm theo nhiệt độ môi trường và phản ứng của bé.',
            ],
        ],

        default => [
            ['title' => 'Chất liệu', 'text' => $product->ingredients ?: 'Theo thông tin sản phẩm.'],
            ['title' => 'Thiết kế', 'text' => 'Thiết kế hướng đến nhu cầu chăm sóc trẻ trong giai đoạn sơ sinh.'],
            ['title' => 'Sử dụng', 'text' => 'Sử dụng theo đúng mục đích và hướng dẫn kèm theo sản phẩm.'],
            ['title' => 'Vệ sinh', 'text' => 'Giữ sản phẩm sạch, khô và kiểm tra thường xuyên trước khi dùng.'],
        ],
    };

    /*
    |--------------------------------------------------------------------------
    | TÌNH HUỐNG SỬ DỤNG PHÙ HỢP
    |--------------------------------------------------------------------------
    */
    $newbornSuitableItems = match (true) {
        $isNewbornClothing => [
            ['title' => 'Mặc hằng ngày', 'text' => 'Phù hợp cho sinh hoạt tại nhà khi bé cần trang phục mềm, gọn và dễ thay.'],
            ['title' => 'Chuẩn bị giỏ sơ sinh', 'text' => 'Có thể bổ sung vào bộ đồ dùng cần thiết trước khi bé chào đời.'],
            ['title' => 'Mang theo khi ra ngoài', 'text' => 'Nên chuẩn bị thêm một bộ dự phòng trong túi đồ để thay khi quần áo bị ẩm hoặc bẩn.'],
        ],

        $isNewbornMittens || $isNewbornHat || $isNewbornBib => [
            ['title' => 'Dùng trong ngày', 'text' => 'Phù hợp với các thời điểm bé cần giữ ấm nhẹ, thấm sữa hoặc bảo vệ vùng da nhạy cảm.'],
            ['title' => 'Mang theo trong túi đồ', 'text' => 'Kích thước nhỏ nên dễ chuẩn bị thêm một hoặc hai món dự phòng khi ra ngoài.'],
            ['title' => 'Thay khi ẩm hoặc bẩn', 'text' => 'Nên đổi sản phẩm sạch ngay khi vật dụng đã thấm sữa, mồ hôi hoặc bị bẩn.'],
        ],

        $isNewbornTowel => [
            ['title' => 'Sau khi bú', 'text' => 'Dùng khăn sạch để lau sữa quanh miệng hoặc hỗ trợ vệ sinh nhẹ cho bé.'],
            ['title' => 'Trong ngày', 'text' => 'Có thể chuẩn bị nhiều khăn và thay luân phiên để luôn có khăn khô, sạch.'],
            ['title' => 'Khi ra ngoài', 'text' => 'Khăn gọn nhẹ phù hợp đặt trong túi đồ mẹ và bé để dùng nhanh khi cần.'],
        ],

        $isNewbornPad => [
            ['title' => 'Khi thay tã', 'text' => 'Trải dưới người bé để hạn chế chất lỏng làm bẩn ga, nệm hoặc bàn thay tã.'],
            ['title' => 'Khi đi khám / ra ngoài', 'text' => 'Có thể mang theo để tạo bề mặt lót riêng khi cần thay tã bên ngoài nhà.'],
            ['title' => 'Trong khu vực chăm sóc', 'text' => 'Dùng như lớp bảo vệ phụ trên các bề mặt cần giữ sạch trong thời gian ngắn.'],
        ],

        $isNewbornPillow => [
            ['title' => 'Chăm sóc trong thời gian ngắn', 'text' => 'Chỉ sử dụng theo đúng mục đích và dưới sự quan sát của người lớn.'],
            ['title' => 'Khu vực chăm sóc bé', 'text' => 'Sản phẩm gọn nên thuận tiện đặt tại nơi thay đồ hoặc khu vực sinh hoạt.'],
            ['title' => 'Khi cần vệ sinh', 'text' => 'Vỏ và ruột gối cần được làm khô hoàn toàn trước khi tiếp tục sử dụng.'],
        ],

        $isNewbornSwaddle => [
            ['title' => 'Sau khi tắm', 'text' => 'Có thể dùng để quấn hoặc ủ nhẹ khi bé vừa được lau khô.'],
            ['title' => 'Khi bế và di chuyển', 'text' => 'Giúp ba mẹ quấn bé gọn hơn trong điều kiện thời tiết mát.'],
            ['title' => 'Khi nghỉ ngơi', 'text' => 'Chỉ quấn vừa phải, giữ mặt bé luôn thoáng và ngưng quấn khi bé có dấu hiệu tự lật.'],
        ],

        default => [
            ['title' => 'Tại nhà', 'text' => 'Dùng trong các hoạt động chăm sóc bé hằng ngày.'],
            ['title' => 'Khi ra ngoài', 'text' => 'Mang theo nếu sản phẩm phù hợp với nhu cầu di chuyển.'],
            ['title' => 'Khi vệ sinh', 'text' => 'Làm sạch và làm khô theo hướng dẫn của từng sản phẩm.'],
        ],
    };


    /*
    |--------------------------------------------------------------------------
    | MÔ TẢ BỔ SUNG - GIẢI THÍCH RÕ HƠN CHO TỪNG NHÓM
    |--------------------------------------------------------------------------
    */
    $newbornContextParagraphs = match (true) {
        $isNewbornClothing => [
            $isNewbornSet
                ? 'Với set đồ sơ sinh, ba mẹ có thể chuẩn bị đồng bộ nhiều món cơ bản trong cùng một bộ để sử dụng luân phiên trong ngày. Khi chọn size, nên ưu tiên số đo thực tế của bé thay vì chỉ dựa vào số tháng ghi trên nhãn.'
                : ($isNewbornBodysuit
                    ? 'Bodysuit liền thân giúp phần thân áo ít bị xô lên khi bé cử động. Thiết kế cúc bấm phía dưới cũng hỗ trợ thay tã thuận tiện hơn, đặc biệt trong những tháng đầu bé cần thay tã nhiều lần.'
                    : 'Body đùi phù hợp với thời tiết ấm hoặc không gian trong nhà. Phần ống ngắn giúp bé cử động chân dễ hơn nhưng vẫn giữ được ưu điểm liền thân, gọn và ít bị xô lệch khi vận động.'),
            'Quần áo sơ sinh thường tiếp xúc trực tiếp với làn da nhạy cảm của bé trong nhiều giờ. Vì vậy, ngoài chất liệu, ba mẹ nên kiểm tra đường may, cúc bấm, phần bo cổ tay/chân và tình trạng vải sau mỗi lần giặt.',
        ],

        $isNewbornMittens => [
            'Bao tay và bao chân thường được sử dụng trong giai đoạn đầu khi bé chưa kiểm soát tốt cử động tay chân. Sản phẩm cần đủ mềm, nhẹ và có phần bo vừa phải để không tạo vết hằn trên da.',
            'Không nên mặc liên tục nếu tay hoặc chân bé bị nóng, ẩm. Khi sử dụng, ba mẹ nên kiểm tra mặt trong để chắc chắn không có chỉ thừa quấn vào ngón tay hoặc ngón chân.',
        ],

        $isNewbornHat => [
            'Mũ sơ sinh phù hợp khi thời tiết mát, khi di chuyển hoặc trong những tình huống cần che đầu nhẹ. Mục tiêu là tạo cảm giác vừa vặn và thoáng, không phải quấn kín hoặc giữ nhiệt quá mức.',
            'Nếu vùng đầu bé ra mồ hôi, nên tháo mũ và thay bằng sản phẩm khô sạch. Phần bo mũ cần nằm phía trên lông mày, không trượt xuống che mắt, mũi hoặc cản trở hô hấp.',
        ],

        $isNewbornBib => [
            'Yếm sơ sinh giúp giữ vùng cổ và phần áo phía trước sạch hơn khi bé bú, trớ sữa hoặc chảy nước bọt. Với trẻ nhỏ, việc thay yếm sạch nhiều lần trong ngày thường thuận tiện hơn thay cả bộ quần áo.',
            'Yếm nên được kiểm tra độ vừa quanh cổ trước mỗi lần dùng. Khi yếm đã thấm ẩm, nên thay sản phẩm khác để vùng cổ của bé luôn khô và hạn chế ma sát kéo dài.',
        ],

        $isNewbornTowel => [
            $isNewbornMuslin
                ? 'Khăn muslin có cấu trúc vải thoáng và mềm, phù hợp để chuẩn bị nhiều chiếc sử dụng luân phiên trong ngày. Kích thước nhỏ giúp khăn dễ mang theo và nhanh khô sau khi giặt.'
                : 'Khăn sữa nhiều lớp thường được dùng với tần suất cao để lau miệng, lau sữa hoặc thấm mồ hôi. Cấu trúc nhiều lớp hỗ trợ tăng khả năng thấm nhưng vẫn giữ khăn gọn và dễ gấp.',
            'Để vệ sinh hơn, gia đình có thể phân loại khăn theo mục đích: khăn dùng cho vùng mặt/miệng, khăn lau sữa và khăn vệ sinh khác. Khăn đã ẩm nên được tách riêng khỏi khăn sạch.',
        ],

        $isNewbornPad => [
            'Tấm lót sơ sinh tạo một lớp trung gian giữa bé và bề mặt phía dưới trong lúc thay tã hoặc vệ sinh. Cấu trúc nhiều lớp giúp tiếp nhận chất lỏng và hạn chế thấm xuống ga, nệm hoặc bàn thay tã.',
            'Sản phẩm dùng một lần nên được thay ngay khi đã bẩn hoặc thấm nhiều. Khi thay tã trên giường, bàn hoặc mặt phẳng cao, người lớn vẫn cần giữ bé bằng một tay và không rời vị trí.',
        ],

        $isNewbornPillow => [
            'Gối xô sơ sinh có kích thước nhỏ và bề mặt mềm, phù hợp cho một số hoạt động chăm sóc ngắn có người lớn theo dõi. Sản phẩm cần được giữ sạch, khô và không biến dạng.',
            'Đối với thời gian ngủ, ưu tiên nguyên tắc ngủ an toàn cho trẻ sơ sinh: không để vật mềm che quanh mặt bé và không dùng gối như một vật chèn giữ tư thế khi không có người lớn quan sát.',
        ],

        $isNewbornSwaddle => [
            $isNewbornCocoon
                ? 'Ủ kén tạo thao tác quấn nhanh và gọn hơn so với chăn rời. Khi sử dụng, phần thân cần ôm vừa phải nhưng vùng hông và chân vẫn phải có khoảng trống để bé cử động tự nhiên.'
                : 'Chăn ủ có mũ phù hợp khi bế bé sau tắm, lúc di chuyển hoặc khi thời tiết mát. Phần mũ hỗ trợ che đầu nhưng không nên kéo sâu xuống mặt hoặc dùng trong môi trường quá nóng.',
            'Ba mẹ cần theo dõi nhiệt độ cơ thể, phản ứng và khả năng vận động của bé. Với sản phẩm quấn, nên ngưng sử dụng khi bé bắt đầu có dấu hiệu tự lật hoặc khi hướng dẫn của nhà sản xuất yêu cầu dừng.',
        ],

        default => [
            'Sản phẩm được lựa chọn cho nhu cầu chăm sóc trẻ trong giai đoạn sơ sinh. Nên đọc kỹ thông tin chất liệu, kích thước, độ tuổi và mục đích sử dụng trước khi dùng.',
            'Trong quá trình sử dụng, ba mẹ nên kiểm tra tình trạng sản phẩm thường xuyên và ưu tiên vệ sinh, làm khô đúng cách để duy trì độ sạch và sự thoải mái cho bé.',
        ],
    };

    /*
    |--------------------------------------------------------------------------
    | 4 ĐIỂM NỔI BẬT KHI SỬ DỤNG
    |--------------------------------------------------------------------------
    */
    $newbornBenefitItems = match (true) {
        $isNewbornClothing => [
            ['title' => 'Êm dịu khi mặc', 'text' => 'Bề mặt vải mềm và phom gọn giúp bé thoải mái hơn trong các hoạt động hằng ngày, đặc biệt khi phải mặc trong nhiều giờ.'],
            ['title' => 'Dễ thay đồ', 'text' => 'Thiết kế cúc hoặc khuy hợp lý hỗ trợ ba mẹ thao tác nhanh hơn khi thay tã, thay quần áo hoặc vệ sinh cho bé.'],
            ['title' => 'Linh hoạt theo thời tiết', 'text' => 'Có thể phối thêm lớp ngoài hoặc giảm lớp mặc tùy nhiệt độ môi trường, tránh để bé quá nóng hoặc quá lạnh.'],
            ['title' => 'Phù hợp chuẩn bị giỏ sinh', 'text' => 'Các kiểu body và set đồ cơ bản dễ kết hợp thành nhiều bộ dự phòng để thay khi quần áo bị ẩm hoặc bẩn.'],
        ],

        $isNewbornMittens => [
            ['title' => 'Hạn chế cào xước', 'text' => 'Bao tay tạo một lớp vải mềm giữa móng tay và da mặt khi bé còn hay đưa tay lên mặt.'],
            ['title' => 'Giữ ấm nhẹ', 'text' => 'Bao tay và bao chân hỗ trợ che phủ nhẹ khi thời tiết mát mà không cần thêm lớp dày.'],
            ['title' => 'Dễ thay trong ngày', 'text' => 'Kích thước nhỏ giúp ba mẹ chuẩn bị nhiều cặp sạch để thay ngay khi sản phẩm bị ẩm hoặc bẩn.'],
            ['title' => 'Dễ mang theo', 'text' => 'Có thể xếp gọn trong túi đồ sơ sinh, phù hợp khi đi khám, về quê hoặc di chuyển ngắn.'],
        ],

        $isNewbornHat => [
            ['title' => 'Che đầu gọn nhẹ', 'text' => 'Thiết kế ôm nhẹ giúp che phần đầu trong thời tiết mát hoặc khi bé cần di chuyển ra ngoài.'],
            ['title' => 'Co giãn vừa phải', 'text' => 'Phần bo có độ đàn hồi phù hợp giúp mũ bám tốt hơn mà không cần siết chặt quanh đầu.'],
            ['title' => 'Dễ phối trang phục', 'text' => 'Màu sắc trung tính hoặc nhẹ nhàng dễ kết hợp cùng body, set quần áo và chăn ủ.'],
            ['title' => 'Vệ sinh đơn giản', 'text' => 'Kích thước nhỏ giúp mũ dễ giặt, nhanh khô và thuận tiện chuẩn bị một chiếc dự phòng.'],
        ],

        $isNewbornBib => [
            ['title' => 'Giữ áo sạch hơn', 'text' => 'Yếm hứng một phần sữa, nước bọt hoặc chất lỏng để hạn chế phần áo trước ngực bị ướt.'],
            ['title' => 'Thay nhanh khi ẩm', 'text' => 'Kích thước nhỏ giúp ba mẹ đổi sang chiếc yếm sạch mà không cần thay toàn bộ trang phục.'],
            ['title' => 'Thấm hút thuận tiện', 'text' => 'Bề mặt cotton giúp thấm lượng chất lỏng nhỏ trong quá trình bú và chăm sóc hằng ngày.'],
            ['title' => 'Dễ chuẩn bị dự phòng', 'text' => 'Có thể mang theo vài chiếc trong túi đồ để thay khi bé trớ sữa hoặc chảy nhiều nước bọt.'],
        ],

        $isNewbornTowel => [
            ['title' => 'Thấm hút tốt hơn', 'text' => 'Cấu trúc nhiều lớp hỗ trợ tiếp nhận sữa, nước bọt hoặc nước sau khi vệ sinh nhẹ cho bé.'],
            ['title' => 'Mềm khi tiếp xúc', 'text' => 'Bề mặt vải phù hợp cho thao tác chấm và lau nhẹ trên vùng da mỏng như má, miệng hoặc cổ.'],
            ['title' => 'Dùng nhiều tình huống', 'text' => 'Một chiếc khăn sạch có thể dùng để lau sữa, lau tay, thấm mồ hôi hoặc hỗ trợ vệ sinh nhẹ.'],
            ['title' => 'Gọn trong túi đồ', 'text' => 'Khăn nhỏ dễ xếp thành nhiều chiếc dự phòng và không chiếm nhiều diện tích khi mang theo.'],
        ],

        $isNewbornPad => [
            ['title' => 'Bảo vệ bề mặt', 'text' => 'Tấm lót giúp hạn chế chất lỏng tiếp xúc trực tiếp với ga giường, nệm hoặc bàn thay tã.'],
            ['title' => 'Thao tác nhanh', 'text' => 'Có thể trải ngay trên bề mặt phẳng trước khi thay tã mà không cần chuẩn bị nhiều bước.'],
            ['title' => 'Tiện khi ra ngoài', 'text' => 'Kích thước gọn phù hợp bỏ trong túi đồ để tạo lớp lót riêng khi phải thay tã bên ngoài nhà.'],
            ['title' => 'Dễ xử lý sau dùng', 'text' => 'Với loại dùng một lần, ba mẹ chỉ cần cuộn gọn và bỏ đúng nơi sau khi sản phẩm đã bẩn.'],
        ],

        $isNewbornPillow => [
            ['title' => 'Bề mặt mềm', 'text' => 'Vỏ xô tạo cảm giác mềm và thông thoáng hơn trong những hoạt động chăm sóc ngắn có giám sát.'],
            ['title' => 'Kích thước gọn', 'text' => 'Thiết kế nhỏ phù hợp khu vực thay đồ, chăm sóc và dễ cất khi không sử dụng.'],
            ['title' => 'Dễ làm sạch', 'text' => 'Bề mặt vải thuận tiện vệ sinh định kỳ; cần làm khô hoàn toàn trước khi dùng lại.'],
            ['title' => 'Dễ kiểm tra tình trạng', 'text' => 'Kích thước nhỏ giúp ba mẹ dễ quan sát đường may, độ phồng và tình trạng vỏ sau mỗi lần giặt.'],
        ],

        $isNewbornSwaddle => [
            ['title' => 'Quấn gọn cơ thể', 'text' => 'Thiết kế hỗ trợ ba mẹ bao quanh cơ thể bé nhanh hơn khi bế, di chuyển hoặc sau khi tắm.'],
            ['title' => 'Giữ ấm nhẹ', 'text' => 'Lớp vải tạo độ che phủ vừa phải trong điều kiện thời tiết mát, không cần quấn quá nhiều lớp.'],
            ['title' => 'Thuận tiện khi di chuyển', 'text' => 'Có thể gấp gọn trong túi đồ và dùng khi đưa bé ra ngoài hoặc thay đổi môi trường nhiệt độ.'],
            ['title' => 'Dễ điều chỉnh', 'text' => 'Ba mẹ có thể nới hoặc tháo nhanh khi bé nóng, khó chịu hoặc cần thay tã.'],
        ],

        default => [
            ['title' => 'Phù hợp chăm sóc hằng ngày', 'text' => 'Thiết kế hướng đến các thao tác quen thuộc trong quá trình chăm sóc trẻ sơ sinh.'],
            ['title' => 'Dễ theo dõi tình trạng', 'text' => 'Ba mẹ có thể kiểm tra sản phẩm trước mỗi lần dùng để phát hiện bẩn, ẩm hoặc hư hỏng.'],
            ['title' => 'Thuận tiện vệ sinh', 'text' => 'Sản phẩm nên được làm sạch và làm khô theo đúng hướng dẫn sau khi sử dụng.'],
            ['title' => 'Dễ mang theo', 'text' => 'Kích thước và cách sử dụng phù hợp với nhu cầu chuẩn bị túi đồ chăm sóc bé.'],
        ],
    };

    /*
    |--------------------------------------------------------------------------
    | CHĂM SÓC / VỆ SINH THEO LOẠI
    |--------------------------------------------------------------------------
    */
    $newbornCareItems = match (true) {
        $isNewbornClothing => [
            'Ưu tiên giặt riêng hoặc giặt cùng nhóm đồ trẻ nhỏ bằng chế độ nhẹ.',
            'Lộn trái quần áo trước khi giặt để hạn chế ma sát lên bề mặt in và cúc bấm.',
            'Làm khô hoàn toàn trước khi gấp; kiểm tra lại đường may và cúc sau nhiều lần giặt.',
            'Không dùng sản phẩm nếu vải bị xù cứng, cúc lỏng hoặc xuất hiện chi tiết có thể gây cấn da.',
        ],

        $isNewbornMittens || $isNewbornHat || $isNewbornBib => [
            'Giặt sạch ngay khi sản phẩm bị thấm sữa, mồ hôi hoặc chất bẩn.',
            'Ưu tiên giặt nhẹ để giữ phần bo, dây cài và đường may ổn định.',
            'Phơi khô hoàn toàn trước khi tiếp tục sử dụng cho bé.',
            'Kiểm tra độ đàn hồi và các sợi chỉ thừa sau mỗi lần vệ sinh.',
        ],

        $isNewbornTowel => [
            'Giặt khăn trước lần sử dụng đầu tiên và giặt sạch sau khi đã bẩn hoặc ẩm.',
            'Tách khăn dùng cho mặt/miệng khỏi khăn dùng cho mục đích vệ sinh khác nếu có thể.',
            'Phơi trải khăn ở nơi thông thoáng để các lớp vải khô đều.',
            'Thay khăn mới khi bề mặt xơ cứng, có mùi khó chịu hoặc khả năng thấm hút giảm rõ rệt.',
        ],

        $isNewbornPad => [
            'Giữ phần tấm chưa sử dụng trong bao bì khô, sạch và kín tương đối.',
            'Không giặt hoặc tái sử dụng sản phẩm được thiết kế dùng một lần.',
            'Sau khi dùng, cuộn gọn mặt bẩn vào trong trước khi bỏ đúng nơi quy định.',
            'Không bỏ tấm lót đã dùng vào bồn cầu hoặc nơi có thể gây tắc nghẽn.',
        ],

        $isNewbornPillow => [
            'Vệ sinh vỏ và ruột theo đúng hướng dẫn chăm sóc của sản phẩm.',
            'Làm khô hoàn toàn cả bên ngoài lẫn phần ruột trước khi cất hoặc dùng lại.',
            'Định kỳ kiểm tra gối có bị vón, biến dạng, rách hoặc xuất hiện mùi ẩm hay không.',
            'Không dùng gối có bề mặt ẩm hoặc có dấu hiệu xuống cấp.',
        ],

        $isNewbornSwaddle => [
            'Giặt nhẹ để giữ bề mặt vải mềm và hạn chế biến dạng sau nhiều lần sử dụng.',
            'Phơi mở hoàn toàn để các lớp vải khô đều, đặc biệt ở phần mũ hoặc các mép gấp.',
            'Kiểm tra đường may, phần khóa/dính nếu có trước khi quấn cho bé.',
            'Cất sản phẩm ở nơi khô, sạch; tránh để chung với đồ ẩm hoặc vật có mùi mạnh.',
        ],

        default => [
            'Làm sạch sản phẩm theo hướng dẫn đi kèm.',
            'Chỉ sử dụng lại khi sản phẩm đã khô hoàn toàn.',
            'Kiểm tra đường may, bề mặt và các chi tiết trước mỗi lần dùng.',
            'Ngưng sử dụng khi sản phẩm có dấu hiệu hư hỏng rõ rệt.',
        ],
    };

    /*
    |--------------------------------------------------------------------------
    | HƯỚNG DẪN - ƯU TIÊN DỮ LIỆU DATABASE
    |--------------------------------------------------------------------------
    */
    $newbornUsageItems = collect($usageSteps ?? []);

    if ($newbornUsageItems->isEmpty() && filled($product->usage_instructions)) {
        $newbornUsageItems = collect(
            preg_split('/\r\n|\r|\n/', (string) $product->usage_instructions)
        )
            ->map(fn ($item) => trim($item, " \t\n\r\0\x0B•-"))
            ->filter()
            ->values();
    }

    $usageFallback = match (true) {
        $isNewbornClothing => [
            'Giặt sạch sản phẩm trước lần mặc đầu tiên.',
            'Kiểm tra size và mở cúc hoặc khuy trước khi mặc cho bé.',
            'Mặc vừa người, không để cổ áo hoặc bo chun siết cơ thể.',
            'Thay đồ sạch khi trang phục bị ẩm, bẩn hoặc bé ra nhiều mồ hôi.',
        ],

        $isNewbornTowel || $isNewbornBib => [
            'Giặt sạch trước lần sử dụng đầu tiên.',
            'Dùng khăn hoặc yếm sạch cho đúng mục đích.',
            'Thay sản phẩm khác khi đã ẩm hoặc bẩn.',
            'Giặt và phơi khô hoàn toàn trước lần dùng tiếp theo.',
        ],

        $isNewbornPad => [
            'Trải tấm lót trên bề mặt phẳng và sạch.',
            'Đặt mặt thấm hút hướng lên phía cơ thể bé.',
            'Sử dụng khi thay tã hoặc chăm sóc trong thời gian cần thiết.',
            'Thay mới ngay khi tấm đã bẩn hoặc thấm nhiều.',
        ],

        $isNewbornSwaddle => [
            'Trải sản phẩm phẳng và đặt bé đúng vị trí.',
            'Quấn vừa phải quanh cơ thể, không ép sát vùng ngực và hông.',
            'Luôn giữ mặt, mũi và miệng bé thông thoáng.',
            'Tháo hoặc điều chỉnh khi bé nóng, khó chịu hoặc có dấu hiệu tự lật.',
        ],

        default => [
            'Kiểm tra sản phẩm trước khi sử dụng.',
            'Sử dụng đúng mục đích và theo độ tuổi phù hợp.',
            'Theo dõi bé trong quá trình sử dụng.',
            'Vệ sinh và bảo quản sản phẩm sau khi dùng.',
        ],
    };

    foreach ($usageFallback as $item) {
        if ($newbornUsageItems->count() >= 4) {
            break;
        }
        $newbornUsageItems->push($item);
    }

    $newbornUsageItems = $newbornUsageItems->take(4)->values();

    $usageTitles = match (true) {
        $isNewbornClothing => ['Giặt trước', 'Chọn size', 'Mặc cho bé', 'Thay đồ'],
        $isNewbornTowel || $isNewbornBib => ['Giặt sạch', 'Sử dụng', 'Thay khăn', 'Phơi khô'],
        $isNewbornPad => ['Trải lót', 'Đặt đúng mặt', 'Sử dụng', 'Thay mới'],
        $isNewbornSwaddle => ['Trải phẳng', 'Đặt bé', 'Quấn vừa', 'Theo dõi'],
        default => ['Kiểm tra', 'Chuẩn bị', 'Sử dụng', 'Vệ sinh'],
    };

    /*
    |--------------------------------------------------------------------------
    | BẢO QUẢN + LƯU Ý
    |--------------------------------------------------------------------------
    */
    $newbornStorageItems = collect($storageItems ?? []);

    if ($newbornStorageItems->isEmpty() && filled($product->storage_instructions)) {
        $newbornStorageItems = collect(
            preg_split('/\r\n|\r|\n/', (string) $product->storage_instructions)
        )
            ->map(fn ($item) => trim($item, " \t\n\r\0\x0B•-"))
            ->filter()
            ->values();
    }

    $storageFallback = match (true) {
        $isNewbornClothing => [
            'Bảo quản quần áo ở ngăn sạch, khô và tách khỏi đồ đã mặc.',
            'Chỉ gấp hoặc cất khi vải đã khô hoàn toàn.',
            'Tránh nắng gắt kéo dài làm bề mặt vải khô cứng hoặc phai màu.',
            'Kiểm tra cúc, khuy và đường may định kỳ trong quá trình sử dụng.',
        ],
        $isNewbornTowel || $isNewbornBib => [
            'Để khăn hoặc yếm sạch ở khu vực khô, thông thoáng và dễ lấy.',
            'Chỉ xếp cất khi sản phẩm đã khô hoàn toàn.',
            'Tách riêng đồ sạch với đồ đã thấm sữa hoặc đang chờ giặt.',
            'Không để khăn ẩm cuộn lại trong túi kín trong thời gian dài.',
        ],
        $isNewbornPad => [
            'Bảo quản phần chưa sử dụng trong bao bì sạch, khô.',
            'Tránh nơi có độ ẩm cao hoặc nguồn nhiệt trực tiếp.',
            'Giữ tấm lót tránh xa nước trước khi sử dụng.',
            'Không đặt vật nặng làm biến dạng hoặc rách bao bì.',
        ],
        $isNewbornPillow || $isNewbornSwaddle => [
            'Bảo quản ở nơi sạch, khô và có không khí lưu thông.',
            'Chỉ cất sau khi toàn bộ sản phẩm đã khô hoàn toàn.',
            'Tránh môi trường ẩm kéo dài hoặc nguồn nhiệt mạnh.',
            'Gấp gọn nhẹ, không nén quá chặt làm biến dạng sản phẩm.',
        ],
        default => [
            'Bảo quản sản phẩm ở nơi sạch, khô và thông thoáng.',
            'Chỉ cất sản phẩm sau khi đã khô hoàn toàn.',
            'Tránh môi trường ẩm kéo dài hoặc nguồn nhiệt trực tiếp.',
            'Kiểm tra tình trạng sản phẩm trước lần sử dụng tiếp theo.',
        ],
    };

    foreach ($storageFallback as $item) {
        if ($newbornStorageItems->count() >= 4) {
            break;
        }
        $newbornStorageItems->push($item);
    }

    $newbornStorageItems = $newbornStorageItems->take(4)->values();

    $newbornWarningItems = collect($warningItems ?? []);

    if ($newbornWarningItems->isEmpty() && filled($product->warning)) {
        $newbornWarningItems = collect(
            preg_split('/\r\n|\r|\n/', (string) $product->warning)
        )
            ->map(fn ($item) => trim($item, " \t\n\r\0\x0B•-"))
            ->filter()
            ->values();
    }

    $warningFallback = match (true) {
        $isNewbornClothing => [
            'Không mặc sản phẩm quá chật hoặc để phần bo để lại vết hằn trên da.',
            'Không dùng khi cúc, khuy hoặc chi tiết trang trí bị lỏng.',
            'Thay trang phục khi vải bị ẩm do mồ hôi, sữa hoặc chất bẩn.',
            'Theo dõi nhiệt độ cơ thể để điều chỉnh số lớp quần áo phù hợp.',
        ],
        $isNewbornMittens => [
            'Không đeo nếu phần bo quá chặt hoặc để lại vết hằn.',
            'Kiểm tra mặt trong, loại bỏ chỉ thừa có thể quấn vào ngón tay/ngón chân.',
            'Tháo sản phẩm khi tay chân bé nóng hoặc ẩm kéo dài.',
            'Ngưng dùng khi đường may bung hoặc vải bị rách.',
        ],
        $isNewbornHat => [
            'Không dùng mũ quá chật hoặc kéo xuống che mắt, mũi.',
            'Tháo mũ khi bé ra nhiều mồ hôi hoặc ở môi trường nóng.',
            'Không để dây, chỉ thừa hoặc chi tiết lỏng quanh vùng đầu và cổ.',
            'Luôn quan sát biểu hiện khó chịu của bé khi đội.',
        ],
        $isNewbornBib => [
            'Không buộc hoặc cài quá sát vùng cổ.',
            'Không để bé đeo yếm khi ngủ mà không có người lớn giám sát.',
            'Thay yếm ngay khi đã ẩm nhiều để vùng cổ không bị ẩm kéo dài.',
            'Ngưng dùng nếu dây/cúc bị lỏng hoặc vải bị rách.',
        ],
        $isNewbornTowel => [
            'Không chà xát mạnh khăn lên vùng da nhạy cảm.',
            'Không dùng khăn còn ẩm lâu, có mùi lạ hoặc dấu hiệu mốc.',
            'Tách khăn sạch khỏi khăn đã sử dụng để hạn chế nhiễm bẩn chéo.',
            'Thay khăn khi bề mặt đã xơ cứng hoặc xuống cấp rõ rệt.',
        ],
        $isNewbornPad => [
            'Luôn giám sát bé khi thay tã trên giường, bàn hoặc bề mặt cao.',
            'Không tái sử dụng tấm lót được thiết kế dùng một lần.',
            'Không để tấm lót che mặt hoặc quấn quanh cơ thể bé.',
            'Không bỏ sản phẩm đã dùng vào bồn cầu.',
        ],
        $isNewbornPillow => [
            'Không dùng gối hoặc vật mềm để che mặt hay chèn cố định cơ thể bé.',
            'Không sử dụng sản phẩm ẩm, biến dạng hoặc rách.',
            'Trong thời gian ngủ, ưu tiên hướng dẫn ngủ an toàn cho trẻ sơ sinh.',
            'Luôn giám sát khi sử dụng gối trong hoạt động chăm sóc ngắn.',
        ],
        $isNewbornSwaddle => [
            'Không quấn quá chặt vùng ngực, hông và chân của bé.',
            'Luôn giữ mũi, miệng và toàn bộ vùng mặt thông thoáng.',
            'Không quấn quá nhiều lớp khi môi trường nóng.',
            'Ngưng quấn khi bé có dấu hiệu tự lật hoặc theo hướng dẫn an toàn của sản phẩm.',
        ],
        default => [
            'Kiểm tra sản phẩm trước mỗi lần sử dụng.',
            'Ngưng dùng nếu sản phẩm bị rách, biến dạng hoặc có chi tiết không còn chắc chắn.',
            'Luôn giám sát bé khi sản phẩm được sử dụng trong hoạt động chăm sóc.',
            'Sử dụng đúng mục đích, độ tuổi và hướng dẫn của sản phẩm.',
        ],
    };

    foreach ($warningFallback as $item) {
        if ($newbornWarningItems->count() >= 4) {
            break;
        }
        $newbornWarningItems->push($item);
    }

    $newbornWarningItems = $newbornWarningItems->take(4)->values();

    $displayBrand = $brandTag?->name
        ?? $product->manufacturer
        ?? 'Đang cập nhật';

    $displayOrigin = $product->origin ?: 'Đang cập nhật';

    $displayMaterial = $product->ingredients ?: 'Theo thông tin sản phẩm';

    $displayStock = max(0, (int) ($product->stock ?? 0));
@endphp

<section class="product-long-content product-newborn-detail">
    {{-- =========================================================
         TỔNG QUAN
    ========================================================== --}}
    <div class="product-content-grid product-newborn-overview-grid">
        <article class="product-content-card product-newborn-spec-card">
            <div class="product-section-heading product-newborn-heading">
                <div>
                    <span class="product-detail-eyebrow">THÔNG TIN SẢN PHẨM</span>
                    <h2>Chi tiết đồ sơ sinh</h2>
                </div>
                <span class="product-newborn-heading-badge">{{ $newbornMeta['group'] }}</span>
            </div>

            <div class="product-spec-table product-newborn-spec-table">
                <div class="product-spec-row">
                    <strong>Tên sản phẩm</strong>
                    <span>{{ $product->name }}</span>
                </div>
                <div class="product-spec-row">
                    <strong>Thương hiệu</strong>
                    <span>{{ $displayBrand }}</span>
                </div>
                <div class="product-spec-row">
                    <strong>Nhóm sản phẩm</strong>
                    <span>{{ $newbornMeta['group'] }}</span>
                </div>
                <div class="product-spec-row">
                    <strong>Dạng sản phẩm</strong>
                    <span>{{ $newbornMeta['form'] }}</span>
                </div>
                <div class="product-spec-row">
                    <strong>Chất liệu</strong>
                    <span>{{ $displayMaterial }}</span>
                </div>
                <div class="product-spec-row">
                    <strong>Độ tuổi</strong>
                    <span>{{ $newbornMeta['age'] }}</span>
                </div>
                <div class="product-spec-row">
                    <strong>Mục đích sử dụng</strong>
                    <span>{{ $newbornMeta['purpose'] }}</span>
                </div>
                <div class="product-spec-row">
                    <strong>Xuất xứ</strong>
                    <span>{{ $displayOrigin }}</span>
                </div>
                <div class="product-spec-row">
                    <strong>Nhà sản xuất</strong>
                    <span>{{ $product->manufacturer ?: $displayBrand }}</span>
                </div>
                <div class="product-spec-row">
                    <strong>Tình trạng</strong>
                    <span class="{{ $displayStock > 0 ? 'is-stock' : 'is-out' }}">
                        {{ $displayStock > 0 ? 'Còn ' . $displayStock . ' sản phẩm' : 'Tạm hết hàng' }}
                    </span>
                </div>
            </div>
        </article>

        <article class="product-content-card product-description-card product-newborn-description-card">
            <div class="product-section-heading product-newborn-heading">
                <div>
                    <span class="product-detail-eyebrow">TỔNG QUAN</span>
                    <h2>Mô tả sản phẩm</h2>
                </div>
                <span class="product-newborn-heading-note">Thông tin theo từng loại sản phẩm</span>
            </div>

            @if (filled($product->description))
                <div class="product-description-text-demo">
                    {{ $product->description }}
                </div>
            @else
                <p class="product-empty-content">
                    Thông tin mô tả sản phẩm đang được cập nhật.
                </p>
            @endif

            <div class="product-newborn-context-copy">
                @foreach ($newbornContextParagraphs as $paragraph)
                    <p>{{ $paragraph }}</p>
                @endforeach
            </div>

            <div class="product-newborn-quick-grid">
                @foreach ($newbornQuickFacts as $index => $fact)
                    <div class="product-newborn-quick-item">
                        <span class="product-newborn-quick-icon" aria-hidden="true">✦</span>
                        <div>
                            <strong>{{ $fact['title'] }}</strong>
                            <span>{{ $fact['text'] }}</span>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="product-trust-banner product-newborn-trust-banner">
                <div class="product-trust-banner-icon" aria-hidden="true">✓</div>
                <div class="product-trust-banner-content">
                    <strong>{{ $newbornMeta['trust_title'] }}</strong>
                    <span>{{ $newbornMeta['trust_text'] }}</span>
                </div>
            </div>
        </article>
    </div>

    {{-- =========================================================
         CHẤT LIỆU & ĐẶC ĐIỂM
    ========================================================== --}}
    <article class="product-content-card product-wide-card product-newborn-section-card">
        <div class="product-newborn-section-heading">
            <div>
                <span class="product-detail-eyebrow">ĐẶC ĐIỂM SẢN PHẨM</span>
                <h2>Chất liệu &amp; thiết kế</h2>
            </div>
            <p>
                Các chi tiết dưới đây giúp ba mẹ hiểu rõ cấu tạo, cách tiếp xúc với bé
                và những điểm nên kiểm tra trước khi sử dụng.
            </p>
        </div>

        <div class="product-newborn-feature-grid">
            @foreach ($newbornFeatureItems as $index => $item)
                <div class="product-newborn-feature-card">
                    <div class="product-newborn-feature-top">
                        <span class="product-newborn-feature-icon" aria-hidden="true">✦</span>
                        <span class="product-newborn-card-number">
                            {{ str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT) }}
                        </span>
                    </div>
                    <strong>{{ $item['title'] }}</strong>
                    <p>{{ $item['text'] }}</p>
                </div>
            @endforeach
        </div>
    </article>

    {{-- =========================================================
         ĐIỂM NỔI BẬT
    ========================================================== --}}
    <article class="product-content-card product-wide-card product-newborn-section-card">
        <div class="product-newborn-section-heading">
            <div>
                <span class="product-detail-eyebrow">GIÁ TRỊ SỬ DỤNG</span>
                <h2>Điểm nổi bật khi sử dụng</h2>
            </div>
            <p>
                Nội dung được điều chỉnh riêng cho {{ Str::lower($newbornMeta['form']) }},
                tập trung vào sự thuận tiện, vệ sinh và cách sử dụng thực tế hằng ngày.
            </p>
        </div>

        <div class="product-newborn-benefit-grid">
            @foreach ($newbornBenefitItems as $index => $item)
                <div class="product-newborn-benefit-card">
                    <span class="product-newborn-benefit-number">
                        {{ str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT) }}
                    </span>
                    <div>
                        <strong>{{ $item['title'] }}</strong>
                        <p>{{ $item['text'] }}</p>
                    </div>
                </div>
            @endforeach
        </div>
    </article>

    {{-- =========================================================
         PHÙ HỢP SỬ DỤNG
    ========================================================== --}}
    <article class="product-content-card product-wide-card product-newborn-section-card">
        <div class="product-newborn-section-heading">
            <div>
                <span class="product-detail-eyebrow">GỢI Ý SỬ DỤNG</span>
                <h2>Phù hợp sử dụng cho</h2>
            </div>
            <p>
                Một số tình huống thường gặp giúp ba mẹ hình dung sản phẩm nên được
                chuẩn bị và sử dụng vào thời điểm nào.
            </p>
        </div>

        <div class="product-newborn-suitable-grid">
            @foreach ($newbornSuitableItems as $index => $item)
                <div class="product-newborn-suitable-card">
                    <span class="product-newborn-suitable-index">
                        {{ str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT) }}
                    </span>
                    <div>
                        <strong>{{ $item['title'] }}</strong>
                        <p>{{ $item['text'] }}</p>
                    </div>
                </div>
            @endforeach
        </div>
    </article>

    {{-- =========================================================
         HƯỚNG DẪN SỬ DỤNG
    ========================================================== --}}
    <article class="product-content-card product-guide-card product-newborn-section-card product-newborn-guide-card">
        <div class="product-newborn-section-heading">
            <div>
                <span class="product-detail-eyebrow">SỬ DỤNG ĐÚNG CÁCH</span>
                <h2>Hướng dẫn sử dụng</h2>
            </div>
            <p>
                Thực hiện theo thứ tự giúp sản phẩm được dùng gọn gàng hơn và dễ kiểm tra
                tình trạng trước, trong và sau khi sử dụng.
            </p>
        </div>

        <div class="product-newborn-guide-steps">
            @foreach ($newbornUsageItems as $index => $item)
                <div class="product-newborn-guide-step">
                    <span class="product-newborn-step-number">
                        {{ str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT) }}
                    </span>
                    <strong>{{ $usageTitles[$index] ?? ('Bước ' . ($index + 1)) }}</strong>
                    <p>{{ $item }}</p>
                </div>
            @endforeach
        </div>
    </article>

    {{-- =========================================================
         BẢO QUẢN + VỆ SINH + LƯU Ý
    ========================================================== --}}
    <div class="product-newborn-bottom-grid">
        <article class="product-info-box product-newborn-info-card">
            <div class="product-info-box-title">
                <div class="product-info-box-icon product-newborn-info-icon" aria-hidden="true">♢</div>
                <div>
                    <span class="product-detail-eyebrow">BẢO QUẢN</span>
                    <h3>Giữ sản phẩm sạch &amp; khô</h3>
                </div>
            </div>
            <ul>
                @foreach ($newbornStorageItems as $item)
                    <li>{{ $item }}</li>
                @endforeach
            </ul>
        </article>

        <article class="product-info-box product-newborn-info-card product-newborn-care-card">
            <div class="product-info-box-title">
                <div class="product-info-box-icon product-newborn-info-icon" aria-hidden="true">✦</div>
                <div>
                    <span class="product-detail-eyebrow">CHĂM SÓC &amp; VỆ SINH</span>
                    <h3>Duy trì sản phẩm đúng cách</h3>
                </div>
            </div>
            <ul>
                @foreach ($newbornCareItems as $item)
                    <li>{{ $item }}</li>
                @endforeach
            </ul>
        </article>

        <article class="product-info-box product-warning-box product-newborn-info-card">
            <div class="product-info-box-title">
                <div class="product-info-box-icon product-newborn-info-icon" aria-hidden="true">△</div>
                <div>
                    <span class="product-detail-eyebrow">LƯU Ý</span>
                    <h3>An toàn khi sử dụng</h3>
                </div>
            </div>
            <ul>
                @foreach ($newbornWarningItems as $item)
                    <li>{{ $item }}</li>
                @endforeach
            </ul>
        </article>
    </div>
</section>
