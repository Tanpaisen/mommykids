@php
    /*
     * Template riêng cho category: do-dung-me-be
     * Dùng dữ liệu Product + brand/attribute tags hiện có.
     */

    $motherBabyAttributeNames = $attributeTags
        ->pluck('name')
        ->filter()
        ->map(fn ($name) => trim($name))
        ->values();

    $motherBabyAttributeSlugs = $attributeTags
        ->pluck('slug')
        ->filter()
        ->map(fn ($slug) => trim($slug))
        ->values();

    $motherBabyProductName = mb_strtolower(trim($product->name ?? ''));

    $hasMotherBabyAttribute = function (array $slugs) use ($motherBabyAttributeSlugs) {
        return $motherBabyAttributeSlugs->contains(
            fn ($slug) => in_array($slug, $slugs, true)
        );
    };

    $findMotherBabyAttribute = function (array $keywords) use ($motherBabyAttributeNames) {
        return $motherBabyAttributeNames->first(function ($name) use ($keywords) {
            foreach ($keywords as $keyword) {
                if (mb_stripos($name, $keyword) !== false) {
                    return true;
                }
            }

            return false;
        });
    };

    /*
     * Nhận diện subtype.
     */
    $isBabyCarrier =
        $hasMotherBabyAttribute(['diu-em-be'])
        || str_contains($motherBabyProductName, 'địu');

    $isMotherBag =
        $hasMotherBabyAttribute(['tui-dung-do-me-be'])
        || str_contains($motherBabyProductName, 'túi đựng đồ');

    $isHighChair =
        $hasMotherBabyAttribute(['ghe-an-dam'])
        || str_contains($motherBabyProductName, 'ghế');

    $isFoodBlender =
        $hasMotherBabyAttribute(['may-xay-thuc-an'])
        || str_contains($motherBabyProductName, 'máy xay');

    $isFoodTray =
        $hasMotherBabyAttribute(['khay-tru-thuc-an'])
        || str_contains($motherBabyProductName, 'khay trữ');

    $isFormulaDispenser =
        $hasMotherBabyAttribute(['hop-chia-sua'])
        || str_contains($motherBabyProductName, 'hộp chia sữa');

    $isBib =
        $hasMotherBabyAttribute(['yem-an-dam'])
        || str_contains($motherBabyProductName, 'yếm');

    $isMotorbikeBelt =
        $hasMotherBabyAttribute(['dai-xe-may'])
        || str_contains($motherBabyProductName, 'đai xe máy');

    $motherBabyTypeLabel = match (true) {
        $isBabyCarrier => 'Địu em bé',
        $isMotherBag => 'Túi đựng đồ mẹ & bé',
        $isHighChair => 'Ghế ăn dặm',
        $isFoodBlender => 'Máy xay thức ăn',
        $isFoodTray => 'Khay trữ thức ăn',
        $isFormulaDispenser => 'Hộp chia sữa',
        $isBib => 'Yếm ăn dặm',
        $isMotorbikeBelt => 'Đai xe máy',
        default => 'Đồ dùng mẹ & bé',
    };

    $motherBabyPrimaryFeature = match (true) {
        $isBabyCarrier => $findMotherBabyAttribute(['6in1', '4 tư thế']) ?: 'Hỗ trợ nhiều tư thế',
        $isMotherBag => $findMotherBabyAttribute(['Nhiều ngăn']) ?: 'Nhiều ngăn tiện dụng',
        $isHighChair => 'Hỗ trợ bé ngồi ăn',
        $isFoodBlender => $findMotherBabyAttribute(['0,3 lít']) ?: 'Xay khẩu phần nhỏ',
        $isFoodTray => $findMotherBabyAttribute(['Nhựa PP']) ?: 'Chia khẩu phần',
        $isFormulaDispenser => $findMotherBabyAttribute(['3 ngăn']) ?: 'Chia sẵn từng cữ',
        $isBib => $findMotherBabyAttribute(['Silicone']) ?: 'Dễ vệ sinh',
        $isMotorbikeBelt => $findMotherBabyAttribute(['Có đỡ cổ']) ?: 'Hỗ trợ cố định tư thế',
        default => 'Tiện lợi khi sử dụng',
    };

    $motherBabyMaterial = $findMotherBabyAttribute([
        'Nhựa PP',
        'Silicone',
    ]);

    $motherBabyCapacity = $findMotherBabyAttribute([
        '0,3 lít',
        '3 ngăn',
    ]);

    $motherBabyDimensions = $motherBabyAttributeNames->first(function ($name) {
        return preg_match('/\d+\s*x\s*\d+(?:[.,]\d+)?\s*x\s*\d+\s*cm/iu', $name);
    });

    $motherBabyAgeAttribute = $motherBabyAttributeNames->first(function ($name) {
        return preg_match('/\d+\s*[MY]\+|\d+\s*tháng|\d+\s*tuổi/iu', $name);
    });

    $motherBabyAgeText =
        $ageText
        ?: $motherBabyAgeAttribute;

    /*
     * Quick facts theo subtype.
     */
    $motherBabyFacts = collect(
        match (true) {
            $isBabyCarrier => [
                ['title' => $motherBabyPrimaryFeature, 'subtitle' => 'Thiết kế linh hoạt', 'icon' => 'carrier'],
                ['title' => 'Địu em bé', 'subtitle' => 'Di chuyển cùng bé', 'icon' => 'baby'],
                ['title' => 'Đeo chắc chắn', 'subtitle' => 'Hỗ trợ khi bế bé', 'icon' => 'shield'],
                ['title' => 'Dễ điều chỉnh', 'subtitle' => 'Theo nhu cầu sử dụng', 'icon' => 'adjust'],
            ],

            $isMotherBag => [
                ['title' => 'Nhiều ngăn', 'subtitle' => 'Sắp xếp đồ dùng gọn gàng', 'icon' => 'bag'],
                ['title' => 'Quai đeo tiện dụng', 'subtitle' => 'Thuận tiện khi di chuyển', 'icon' => 'strap'],
                ['title' => $motherBabyDimensions ?: 'Kích thước tiện dụng', 'subtitle' => 'Phù hợp mang theo', 'icon' => 'ruler'],
                ['title' => 'Mẹ & bé', 'subtitle' => 'Dùng cho nhiều vật dụng', 'icon' => 'heart'],
            ],

            $isHighChair => [
                ['title' => 'Ghế ăn dặm', 'subtitle' => 'Hỗ trợ bé ngồi ăn', 'icon' => 'chair'],
                ['title' => 'Ổn định', 'subtitle' => 'Phù hợp bữa ăn của bé', 'icon' => 'shield'],
                ['title' => 'Tiện vệ sinh', 'subtitle' => 'Dùng hằng ngày', 'icon' => 'clean'],
                ['title' => 'Ăn dặm', 'subtitle' => 'Phù hợp giai đoạn tập ăn', 'icon' => 'spoon'],
            ],

            $isFoodBlender => [
                ['title' => 'Máy xay thức ăn', 'subtitle' => 'Chuẩn bị đồ ăn dặm', 'icon' => 'blender'],
                ['title' => $motherBabyCapacity ?: 'Dung tích nhỏ gọn', 'subtitle' => 'Phù hợp khẩu phần bé', 'icon' => 'capacity'],
                ['title' => 'Xay nhuyễn', 'subtitle' => 'Tiện chuẩn bị bữa ăn', 'icon' => 'blade'],
                ['title' => 'Đa năng', 'subtitle' => 'Sử dụng tiện lợi', 'icon' => 'spark'],
            ],

            $isFoodTray => [
                ['title' => 'Khay trữ thức ăn', 'subtitle' => 'Chia khẩu phần ăn dặm', 'icon' => 'tray'],
                ['title' => $motherBabyMaterial ?: 'Chất liệu phù hợp', 'subtitle' => 'Theo thông tin sản phẩm', 'icon' => 'material'],
                ['title' => 'Trữ đông', 'subtitle' => 'Hỗ trợ bảo quản khẩu phần', 'icon' => 'snow'],
                ['title' => $motherBabyAgeText ?: 'Giai đoạn ăn dặm', 'subtitle' => 'Theo hướng dẫn sản phẩm', 'icon' => 'baby'],
            ],

            $isFormulaDispenser => [
                ['title' => 'Hộp chia sữa', 'subtitle' => 'Chia sẵn từng cữ', 'icon' => 'container'],
                ['title' => $motherBabyCapacity ?: 'Nhiều ngăn', 'subtitle' => 'Sắp xếp tiện lợi', 'icon' => 'grid'],
                ['title' => 'Mang theo', 'subtitle' => 'Thuận tiện khi ra ngoài', 'icon' => 'bag'],
                ['title' => 'Gọn nhẹ', 'subtitle' => 'Dễ cất trong túi đồ', 'icon' => 'spark'],
            ],

            $isBib => [
                ['title' => 'Yếm ăn dặm', 'subtitle' => 'Hỗ trợ giữ quần áo sạch', 'icon' => 'bib'],
                ['title' => $motherBabyMaterial ?: 'Chất liệu dễ vệ sinh', 'subtitle' => 'Theo thông tin sản phẩm', 'icon' => 'material'],
                ['title' => 'Dễ vệ sinh', 'subtitle' => 'Phù hợp dùng hằng ngày', 'icon' => 'clean'],
                ['title' => 'Gọn nhẹ', 'subtitle' => 'Tiện mang theo', 'icon' => 'spark'],
            ],

            $isMotorbikeBelt => [
                ['title' => 'Đai xe máy', 'subtitle' => 'Hỗ trợ cố định tư thế', 'icon' => 'belt'],
                ['title' => $motherBabyPrimaryFeature, 'subtitle' => 'Thiết kế hỗ trợ bé', 'icon' => 'shield'],
                ['title' => 'Dễ điều chỉnh', 'subtitle' => 'Theo vóc dáng sử dụng', 'icon' => 'adjust'],
                ['title' => 'Di chuyển', 'subtitle' => 'Dùng cùng người lớn', 'icon' => 'move'],
            ],

            default => [
                ['title' => 'Tiện dụng', 'subtitle' => 'Phù hợp nhu cầu hằng ngày', 'icon' => 'spark'],
                ['title' => 'Dễ sử dụng', 'subtitle' => 'Thiết kế thân thiện', 'icon' => 'check'],
                ['title' => 'Gọn gàng', 'subtitle' => 'Thuận tiện cất giữ', 'icon' => 'box'],
                ['title' => 'Mẹ & bé', 'subtitle' => 'Phù hợp gia đình', 'icon' => 'heart'],
            ],
        }
    );

    /*
     * 4 cấu tạo / tiện ích nổi bật.
     */
    $motherBabyStructureItems = collect(
        match (true) {
            $isBabyCarrier => [
                ['title' => 'Bệ/đệm đỡ bé', 'text' => 'Phần bệ hoặc đệm đỡ tạo điểm tựa cho cơ thể bé, hỗ trợ giữ tư thế ổn định hơn khi ba mẹ bế và di chuyển trong thời gian ngắn.'],
                ['title' => 'Dây đeo', 'text' => 'Dây đeo giúp liên kết phần địu với cơ thể người lớn, đồng thời cho phép phân bổ lực đều hơn khi đã được điều chỉnh đúng cách.'],
                ['title' => 'Khóa điều chỉnh', 'text' => 'Khóa và dây điều chỉnh giúp căn chỉnh độ ôm theo vóc dáng người đeo, hạn chế tình trạng dây quá lỏng hoặc siết quá chặt.'],
                ['title' => 'Thiết kế đa tư thế', 'text' => 'Thiết kế cho phép thay đổi tư thế theo từng giai đoạn phát triển, nhưng cần ưu tiên hướng dẫn cụ thể của nhà sản xuất cho từng độ tuổi.'],
            ],
            $isMotherBag => [
                ['title' => 'Ngăn chính', 'text' => 'Ngăn chính có không gian đủ rộng để sắp xếp bỉm, khăn, quần áo dự phòng và các vật dụng thiết yếu thường mang theo cho mẹ và bé.'],
                ['title' => 'Ngăn phụ', 'text' => 'Các ngăn phụ giúp tách vật dụng nhỏ như khăn giấy, núm ti, đồ vệ sinh hoặc phụ kiện để dễ tìm hơn khi cần sử dụng nhanh.'],
                ['title' => 'Quai xách / quai đeo', 'text' => 'Quai xách hoặc quai đeo hỗ trợ nhiều cách mang khác nhau, giúp ba mẹ linh hoạt hơn khi đi chơi, đi khám hoặc di chuyển cùng bé.'],
                ['title' => 'Thiết kế gọn', 'text' => 'Thiết kế gọn giúp túi dễ bố trí cùng xe đẩy, cốp hành lý hoặc các vật dụng khác mà không chiếm quá nhiều không gian.'],
            ],
            $isHighChair => [
                ['title' => 'Mặt ghế', 'text' => 'Mặt ghế tạo khu vực ngồi riêng cho bé trong bữa ăn, giúp bé duy trì tư thế ổn định hơn khi tập ăn hoặc tập tự xúc.'],
                ['title' => 'Khay ăn', 'text' => 'Khay ăn tạo bề mặt riêng để đặt bát, thìa, cốc và thức ăn, đồng thời giúp ba mẹ tổ chức bữa ăn gọn gàng hơn.'],
                ['title' => 'Khung chân', 'text' => 'Khung chân chịu lực và tạo độ ổn định cho ghế khi đặt trên mặt sàn phẳng, giúp hạn chế rung lắc trong quá trình bé ngồi ăn.'],
                ['title' => 'Thiết kế vệ sinh', 'text' => 'Các bề mặt chính được thiết kế để dễ lau sạch thức ăn và nước bám sau bữa ăn, giúp việc vệ sinh hằng ngày nhanh hơn.'],
            ],
            $isFoodBlender => [
                ['title' => 'Cối xay', 'text' => 'Cối xay dùng để chứa lượng thực phẩm đã sơ chế, phù hợp với nhu cầu chuẩn bị khẩu phần nhỏ cho từng bữa ăn dặm.'],
                ['title' => 'Lưỡi xay', 'text' => 'Lưỡi xay hỗ trợ làm nhỏ và tạo độ nhuyễn cho thực phẩm sau sơ chế; cần thao tác cẩn thận khi tháo rửa vì đây là bộ phận sắc.'],
                ['title' => 'Thân máy', 'text' => 'Thân máy chứa bộ phận vận hành và kết nối nguồn, vì vậy cần giữ khô và tránh để nước tiếp xúc trực tiếp trong quá trình vệ sinh.'],
                ['title' => 'Nắp đậy', 'text' => 'Nắp đậy giúp giữ thực phẩm bên trong cối khi máy hoạt động và cần được lắp đúng vị trí trước mỗi lần sử dụng.'],
            ],
            $isFoodTray => [
                ['title' => 'Các ô chia', 'text' => 'Các ô chia giúp ba mẹ phân sẵn thức ăn thành từng khẩu phần nhỏ, thuận tiện lấy đúng lượng cần dùng cho mỗi bữa.'],
                ['title' => 'Nắp đậy', 'text' => 'Nắp đậy giúp hạn chế bụi bẩn và mùi từ môi trường xung quanh tiếp xúc với thức ăn trong thời gian bảo quản.'],
                ['title' => 'Chất liệu PP', 'text' => 'Chất liệu được sử dụng theo thông tin hiện có của sản phẩm; trước khi hâm hoặc trữ đông nên đối chiếu thêm giới hạn nhiệt trên nhãn.'],
                ['title' => 'Thiết kế trữ đông', 'text' => 'Thiết kế dạng khay phù hợp với nhu cầu chuẩn bị trước thức ăn dặm và chia thành từng phần nhỏ để bảo quản thuận tiện hơn.'],
            ],
            $isFormulaDispenser => [
                ['title' => 'Các ngăn chia', 'text' => 'Các ngăn riêng giúp chia sẵn lượng sữa bột hoặc thực phẩm khô theo từng cữ, hạn chế phải mang theo cả hộp lớn khi ra ngoài.'],
                ['title' => 'Nắp đậy', 'text' => 'Nắp đậy giúp các ngăn được đóng kín và gọn hơn khi đặt trong túi đồ, đồng thời hạn chế bột bị đổ ra ngoài khi di chuyển.'],
                ['title' => 'Thân hộp', 'text' => 'Thân hộp có kích thước nhỏ gọn, thuận tiện đặt cạnh bình sữa, khăn và những vật dụng thiết yếu khác trong túi mẹ và bé.'],
                ['title' => 'Thiết kế tháo lắp', 'text' => 'Các phần có thể tháo rời giúp ba mẹ dễ rửa sạch và làm khô hoàn toàn trước khi tiếp tục chứa sữa bột hoặc thực phẩm khô.'],
            ],
            $isBib => [
                ['title' => 'Thân yếm', 'text' => 'Thân yếm che phần trước ngực và quần áo của bé, giúp hạn chế thức ăn, nước hoặc nước sốt bắn trực tiếp lên trang phục trong bữa ăn.'],
                ['title' => 'Vùng hứng thức ăn', 'text' => 'Phần máng hứng phía dưới hỗ trợ giữ lại một phần thức ăn rơi, đặc biệt hữu ích khi bé đang tập tự xúc hoặc cầm thức ăn.'],
                ['title' => 'Dây / nút điều chỉnh', 'text' => 'Dây hoặc nút điều chỉnh ở vùng cổ giúp thay đổi độ rộng phù hợp hơn với bé, tránh đeo quá lỏng hoặc quá chặt.'],
                ['title' => 'Chất liệu silicone', 'text' => 'Silicone có bề mặt dễ lau rửa sau bữa ăn, có thể vệ sinh nhiều lần và thuận tiện cuộn nhẹ để mang theo khi ra ngoài.'],
            ],
            $isMotorbikeBelt => [
                ['title' => 'Đai cố định', 'text' => 'Đai cố định hỗ trợ giữ bé gần cơ thể người lớn hơn khi ngồi cùng, nhưng không thay thế việc chủ động giữ và giám sát bé.'],
                ['title' => 'Phần đỡ cổ', 'text' => 'Phần đỡ phía sau hỗ trợ vùng đầu và cổ theo thiết kế sản phẩm, giúp bé có thêm điểm tựa trong quá trình di chuyển.'],
                ['title' => 'Khóa cài', 'text' => 'Khóa cài giúp đóng mở nhanh và cố định hệ thống dây; cần kiểm tra khóa đã vào đúng vị trí trước mỗi lần di chuyển.'],
                ['title' => 'Dây điều chỉnh', 'text' => 'Dây điều chỉnh cho phép thay đổi độ dài theo vóc dáng người lớn và tư thế ngồi của bé để hạn chế tình trạng quá lỏng.'],
            ],
            default => [
                ['title' => 'Thiết kế', 'text' => 'Phù hợp nhu cầu sử dụng hằng ngày.'],
                ['title' => 'Cấu tạo', 'text' => 'Bố trí thuận tiện khi thao tác.'],
                ['title' => 'Tiện ích', 'text' => 'Hỗ trợ quá trình chăm sóc mẹ và bé.'],
                ['title' => 'Dễ vệ sinh', 'text' => 'Thuận tiện cất giữ và sử dụng lại.'],
            ],
        }
    );


    /*
     * Điểm nổi bật / lợi ích theo từng subtype.
     */
    $motherBabyBenefitItems = collect(
        match (true) {
            $isBabyCarrier => [
                ['title' => 'Phân bổ lực tốt hơn', 'text' => 'Đai eo, dây vai và phần đỡ bé phối hợp để hỗ trợ giảm cảm giác dồn lực vào một vị trí khi bế.', 'icon' => 'balance'],
                ['title' => 'Linh hoạt nhiều tư thế', 'text' => 'Có thể thay đổi cách bế theo độ tuổi, khả năng giữ đầu - cổ và hướng dẫn riêng của từng sản phẩm.', 'icon' => 'rotate'],
                ['title' => 'Thuận tiện khi ra ngoài', 'text' => 'Phù hợp khi ba mẹ cần di chuyển, đi dạo, mua sắm hoặc chăm bé mà vẫn muốn rảnh tay hơn.', 'icon' => 'move'],
                ['title' => 'Điều chỉnh theo người đeo', 'text' => 'Hệ thống dây và khóa hỗ trợ căn chỉnh độ ôm theo vóc dáng người lớn để sử dụng chắc chắn hơn.', 'icon' => 'adjust'],
            ],

            $isMotherBag => [
                ['title' => 'Phân loại đồ dùng', 'text' => 'Nhiều khu vực chứa giúp tách bỉm, khăn, quần áo, bình sữa và các vật dụng nhỏ để dễ tìm hơn.', 'icon' => 'grid'],
                ['title' => 'Mang theo gọn gàng', 'text' => 'Thiết kế phục vụ nhu cầu đi chơi, đi khám hoặc di chuyển ngắn cùng bé mà không cần nhiều túi rời.', 'icon' => 'bag'],
                ['title' => 'Dễ lấy vật dụng', 'text' => 'Việc sắp xếp theo từng ngăn giúp ba mẹ thao tác nhanh hơn trong lúc chăm bé ở bên ngoài.', 'icon' => 'spark'],
                ['title' => 'Phù hợp nhiều tình huống', 'text' => 'Có thể dùng cùng xe đẩy, mang tay hoặc đeo tùy thiết kế và nhu cầu của gia đình.', 'icon' => 'move'],
            ],

            $isHighChair => [
                ['title' => 'Tạo vị trí ăn riêng', 'text' => 'Giúp bé làm quen với thói quen ngồi ăn tại một vị trí ổn định trong giai đoạn tập ăn dặm.', 'icon' => 'chair'],
                ['title' => 'Hỗ trợ bữa ăn gọn hơn', 'text' => 'Khay ăn tạo khu vực riêng cho thức ăn, thìa, cốc và các vật dụng cần thiết trong bữa ăn.', 'icon' => 'tray'],
                ['title' => 'Dễ vệ sinh sau ăn', 'text' => 'Các bề mặt chính có thể được lau sạch sau bữa ăn, hạn chế thức ăn khô bám lâu ngày.', 'icon' => 'clean'],
                ['title' => 'Ổn định khi sử dụng', 'text' => 'Khi được lắp và đặt đúng cách trên mặt phẳng, ghế hỗ trợ bé ngồi ăn an toàn hơn dưới sự giám sát.', 'icon' => 'shield'],
            ],

            $isFoodBlender => [
                ['title' => 'Chuẩn bị khẩu phần nhỏ', 'text' => 'Cối dung tích nhỏ phù hợp với nhu cầu chế biến lượng thức ăn vừa đủ cho từng bữa của bé.', 'icon' => 'capacity'],
                ['title' => 'Hỗ trợ xay nhuyễn', 'text' => 'Giúp làm nhỏ thực phẩm đã sơ chế để ba mẹ dễ điều chỉnh kết cấu theo từng giai đoạn ăn dặm.', 'icon' => 'blade'],
                ['title' => 'Tiết kiệm thời gian', 'text' => 'Phù hợp với các bữa cần chuẩn bị nhanh một lượng thực phẩm nhỏ thay vì dùng thiết bị dung tích lớn.', 'icon' => 'spark'],
                ['title' => 'Dễ tháo vệ sinh', 'text' => 'Các bộ phận tiếp xúc thực phẩm có thể được làm sạch sau khi đã ngắt nguồn điện hoàn toàn.', 'icon' => 'clean'],
            ],

            $isFoodTray => [
                ['title' => 'Chia khẩu phần tiện lợi', 'text' => 'Các ô riêng giúp chia thức ăn thành từng phần nhỏ để ba mẹ chủ động chuẩn bị trước cho nhiều bữa.', 'icon' => 'grid'],
                ['title' => 'Hỗ trợ trữ lạnh / trữ đông', 'text' => 'Phù hợp nhu cầu chuẩn bị thức ăn trước và bảo quản theo thời gian phù hợp với từng loại thực phẩm.', 'icon' => 'snow'],
                ['title' => 'Dễ quản lý khẩu phần', 'text' => 'Mỗi ô có thể dùng cho một lượng thức ăn riêng, giúp hạn chế phải rã đông toàn bộ khay cùng lúc.', 'icon' => 'tray'],
                ['title' => 'Gọn khi xếp tủ', 'text' => 'Thiết kế dạng khay thuận tiện bố trí trong ngăn mát hoặc ngăn đông nếu được xếp trên mặt phẳng.', 'icon' => 'box'],
            ],

            $isFormulaDispenser => [
                ['title' => 'Chia sẵn từng cữ', 'text' => 'Giúp ba mẹ chuẩn bị lượng sữa bột hoặc thực phẩm khô theo từng lần dùng trước khi ra ngoài.', 'icon' => 'grid'],
                ['title' => 'Giảm thao tác khi pha', 'text' => 'Không cần mang theo cả hộp lớn và đong nhiều lần tại nơi công cộng hoặc khi đang di chuyển.', 'icon' => 'spark'],
                ['title' => 'Gọn trong túi đồ', 'text' => 'Kích thước nhỏ giúp dễ đặt cùng bình sữa, khăn và các vật dụng thiết yếu khác của bé.', 'icon' => 'bag'],
                ['title' => 'Dễ vệ sinh', 'text' => 'Các ngăn và nắp nên được tháo rửa, làm khô hoàn toàn trước mỗi lần chứa sữa bột.', 'icon' => 'clean'],
            ],

            $isBib => [
                ['title' => 'Giữ quần áo sạch hơn', 'text' => 'Phần thân yếm che phía trước cơ thể bé, giúp hạn chế thức ăn và nước rơi trực tiếp lên quần áo.', 'icon' => 'bib'],
                ['title' => 'Máng hứng thức ăn', 'text' => 'Phần hứng phía dưới hỗ trợ giữ lại một phần thức ăn rơi trong quá trình bé tự xúc hoặc tập ăn.', 'icon' => 'tray'],
                ['title' => 'Mềm và dễ điều chỉnh', 'text' => 'Vùng cổ có thể điều chỉnh để vừa vặn hơn, trong khi silicone mềm giúp yếm ôm theo cơ thể bé.', 'icon' => 'adjust'],
                ['title' => 'Rửa nhanh sau bữa ăn', 'text' => 'Bề mặt ít bám sợi vải nên dễ làm sạch thức ăn, dầu và các vết bẩn thông thường sau khi sử dụng.', 'icon' => 'clean'],
            ],

            $isMotorbikeBelt => [
                ['title' => 'Hỗ trợ cố định tư thế', 'text' => 'Hệ thống đai giúp giữ bé gần người lớn hơn khi ngồi cùng trong quá trình di chuyển.', 'icon' => 'belt'],
                ['title' => 'Có phần hỗ trợ cổ', 'text' => 'Phần đỡ phía sau hỗ trợ vùng đầu - cổ theo thiết kế sản phẩm trong quá trình sử dụng.', 'icon' => 'shield'],
                ['title' => 'Điều chỉnh độ ôm', 'text' => 'Dây đai có thể căn chỉnh để phù hợp hơn với người lớn và vị trí ngồi của bé.', 'icon' => 'adjust'],
                ['title' => 'Kiểm tra nhanh trước khi đi', 'text' => 'Các khóa, dây và đường may có thể được kiểm tra lại trước mỗi lần di chuyển để phát hiện bất thường.', 'icon' => 'check'],
            ],

            default => [
                ['title' => 'Thiết kế tiện dụng', 'text' => 'Hỗ trợ ba mẹ trong các hoạt động chăm sóc bé hằng ngày.', 'icon' => 'spark'],
                ['title' => 'Dễ sử dụng', 'text' => 'Cách thao tác hướng tới sự đơn giản và thuận tiện trong gia đình.', 'icon' => 'check'],
                ['title' => 'Dễ vệ sinh', 'text' => 'Phù hợp nhu cầu làm sạch và sử dụng lặp lại theo đặc điểm của sản phẩm.', 'icon' => 'clean'],
                ['title' => 'Gọn khi cất giữ', 'text' => 'Thuận tiện sắp xếp cùng các vật dụng khác của mẹ và bé.', 'icon' => 'box'],
            ],
        }
    );

    /*
     * Tình huống / đối tượng sử dụng.
     */
    $motherBabySuitableItems = collect(
        match (true) {
            $isBabyCarrier => [
                ['title' => 'Đi dạo cùng bé', 'text' => 'Hữu ích khi cần bế bé trong quãng đi bộ ngắn và vẫn muốn rảnh tay hơn.'],
                ['title' => 'Đi mua sắm / ra ngoài', 'text' => 'Phù hợp những lúc xe đẩy không thuận tiện hoặc không gian di chuyển hẹp.'],
                ['title' => 'Chăm bé trong nhà', 'text' => 'Có thể hỗ trợ ba mẹ trong một số hoạt động nhẹ khi vẫn cần giữ bé gần cơ thể.'],
            ],

            $isMotherBag => [
                ['title' => 'Đi khám / tiêm', 'text' => 'Mang theo bỉm, khăn, quần áo dự phòng và vật dụng cần thiết trong một túi.'],
                ['title' => 'Đi chơi ngắn ngày', 'text' => 'Giúp chia đồ dùng theo từng nhóm để dễ tìm khi cần.'],
                ['title' => 'Dùng cùng xe đẩy', 'text' => 'Có thể bố trí cùng xe đẩy nếu kiểu quai và tải trọng phù hợp.'],
            ],

            $isHighChair => [
                ['title' => 'Giai đoạn tập ăn dặm', 'text' => 'Phù hợp khi bé đã có khả năng ngồi theo yêu cầu của sản phẩm và cần vị trí ăn riêng.'],
                ['title' => 'Bữa ăn hằng ngày', 'text' => 'Giúp duy trì thói quen ngồi ăn tại một vị trí cố định.'],
                ['title' => 'Tập tự xúc', 'text' => 'Khay ăn tạo không gian riêng để bé làm quen với thìa và thức ăn dưới sự giám sát.'],
            ],

            $isFoodBlender => [
                ['title' => 'Xay rau củ / thịt đã sơ chế', 'text' => 'Phù hợp làm nhỏ lượng thực phẩm vừa đủ cho một hoặc vài khẩu phần nhỏ.'],
                ['title' => 'Chuẩn bị cháo / sốt ăn dặm', 'text' => 'Hỗ trợ điều chỉnh độ nhuyễn tùy nguyên liệu và giai đoạn ăn của bé.'],
                ['title' => 'Bữa ăn cần làm nhanh', 'text' => 'Thuận tiện khi chỉ cần xử lý một lượng thực phẩm nhỏ.'],
            ],

            $isFoodTray => [
                ['title' => 'Chuẩn bị thức ăn trước', 'text' => 'Phù hợp ba mẹ muốn chia sẵn khẩu phần cho nhiều bữa.'],
                ['title' => 'Trữ cháo / rau củ / nước dùng', 'text' => 'Dùng cho các thực phẩm đã được chế biến và bảo quản đúng cách.'],
                ['title' => 'Quản lý từng khẩu phần', 'text' => 'Giúp lấy lượng vừa đủ thay vì rã đông toàn bộ lượng thức ăn đã chuẩn bị.'],
            ],

            $isFormulaDispenser => [
                ['title' => 'Đi chơi / đi khám', 'text' => 'Chuẩn bị trước từng cữ sữa bột để mang theo gọn hơn.'],
                ['title' => 'Đi du lịch', 'text' => 'Giảm nhu cầu mang theo hộp sữa lớn trong các chuyến đi ngắn.'],
                ['title' => 'Chuẩn bị cữ đêm', 'text' => 'Có thể chia sẵn lượng bột khô để thao tác nhanh hơn khi cần pha sữa.'],
            ],

            $isBib => [
                ['title' => 'Bé bắt đầu ăn dặm', 'text' => 'Hỗ trợ giữ quần áo sạch hơn trong giai đoạn bé làm quen với thức ăn.'],
                ['title' => 'Bé tập tự xúc', 'text' => 'Máng hứng hỗ trợ giữ lại một phần thức ăn rơi trong quá trình bé tự ăn.'],
                ['title' => 'Mang theo khi ra ngoài', 'text' => 'Yếm silicone có thể cuộn hoặc gấp nhẹ để đặt trong túi đồ của bé.'],
            ],

            $isMotorbikeBelt => [
                ['title' => 'Di chuyển quãng ngắn', 'text' => 'Sử dụng như một sản phẩm hỗ trợ tư thế khi bé ngồi cùng người lớn.'],
                ['title' => 'Cần thêm phần đỡ cổ', 'text' => 'Phù hợp khi ba mẹ muốn có thêm bộ phận hỗ trợ phía sau theo thiết kế sản phẩm.'],
                ['title' => 'Cần dây điều chỉnh', 'text' => 'Có thể căn chỉnh theo người lớn và tư thế bé trước khi di chuyển.'],
            ],

            default => [
                ['title' => 'Sử dụng hằng ngày', 'text' => 'Phù hợp nhu cầu chăm sóc mẹ và bé trong gia đình.'],
                ['title' => 'Mang theo khi ra ngoài', 'text' => 'Thiết kế hướng tới sự thuận tiện khi cần di chuyển.'],
                ['title' => 'Dễ sắp xếp', 'text' => 'Có thể kết hợp cùng các vật dụng khác tùy nhu cầu sử dụng.'],
            ],
        }
    );


    /*
     * Hướng dẫn: ưu tiên DB, thiếu thì dùng fallback trung tính.
     */
    $motherBabyUsageFallback = collect(
        match (true) {
            $isBabyCarrier => [
                'Kiểm tra khóa, dây đeo và các bộ phận trước khi sử dụng.',
                'Điều chỉnh dây đeo phù hợp với cơ thể người lớn.',
                'Đặt bé đúng tư thế theo hướng dẫn của sản phẩm.',
                'Kiểm tra lại độ chắc chắn trước khi di chuyển.',
            ],
            $isMotherBag => [
                'Phân loại vật dụng theo từng nhóm trước khi xếp vào túi.',
                'Đặt vật nặng ở vị trí cân bằng và ngăn phù hợp.',
                'Đóng khóa túi trước khi di chuyển.',
                'Vệ sinh và để túi khô sau khi sử dụng.',
            ],
            $isHighChair => [
                'Đặt ghế trên bề mặt phẳng và kiểm tra độ ổn định.',
                'Đặt bé ngồi đúng tư thế và cố định theo thiết kế ghế.',
                'Sử dụng khay ăn trong suốt bữa ăn của bé.',
                'Vệ sinh ghế ngay sau khi sử dụng.',
            ],
            $isFoodBlender => [
                'Rửa sạch các bộ phận tiếp xúc với thực phẩm trước khi dùng.',
                'Cho lượng thực phẩm phù hợp vào cối.',
                'Đậy kín nắp và vận hành theo hướng dẫn của thiết bị.',
                'Ngắt nguồn điện trước khi tháo rửa.',
            ],
            $isFoodTray => [
                'Rửa sạch khay trước lần sử dụng đầu tiên.',
                'Chia thức ăn vào từng ô với lượng phù hợp.',
                'Đậy nắp và bảo quản theo nhu cầu.',
                'Không đậy nắp khi dùng trong lò vi sóng theo dữ liệu sản phẩm.',
            ],
            $isFormulaDispenser => [
                'Rửa sạch và làm khô hộp trước khi sử dụng.',
                'Chia sẵn lượng sữa bột hoặc thức ăn khô vào từng ngăn.',
                'Đậy kín nắp trước khi mang theo.',
                'Vệ sinh hộp sau mỗi lần sử dụng.',
            ],
            $isBib => [
                'Rửa sạch yếm trước khi sử dụng.',
                'Điều chỉnh phần cổ vừa vặn với bé.',
                'Đeo yếm trong suốt bữa ăn.',
                'Rửa sạch và làm khô sau khi sử dụng.',
            ],
            $isMotorbikeBelt => [
                'Kiểm tra đai, khóa và đường may trước khi dùng.',
                'Điều chỉnh đai phù hợp với người lớn và bé.',
                'Cố định đúng vị trí theo hướng dẫn sản phẩm.',
                'Kiểm tra lại toàn bộ khóa trước khi di chuyển.',
            ],
            default => [
                'Kiểm tra sản phẩm trước khi sử dụng.',
                'Chuẩn bị và điều chỉnh theo nhu cầu.',
                'Sử dụng đúng hướng dẫn của sản phẩm.',
                'Vệ sinh và bảo quản sau khi sử dụng.',
            ],
        }
    );

    $motherBabyUsageSlots = collect(range(0, 3))
        ->map(function ($index) use ($usageSteps, $motherBabyUsageFallback) {
            return $usageSteps->get($index)
                ?: $motherBabyUsageFallback->get($index);
        });

    $motherBabyStepTitles = collect(
        match (true) {
            $isFoodBlender => ['Vệ sinh', 'Chuẩn bị', 'Vận hành', 'Làm sạch'],
            $isFoodTray => ['Rửa sạch', 'Chia khẩu phần', 'Bảo quản', 'Sử dụng'],
            $isFormulaDispenser => ['Rửa sạch', 'Chia sữa', 'Đậy kín', 'Vệ sinh'],
            $isBib => ['Rửa sạch', 'Điều chỉnh', 'Sử dụng', 'Vệ sinh'],
            default => ['Kiểm tra', 'Điều chỉnh', 'Sử dụng', 'Hoàn tất'],
        }
    );

    $motherBabyStorageList = $storageItems->isNotEmpty()
        ? $storageItems
        : collect([
            'Bảo quản sản phẩm nơi khô ráo, sạch sẽ.',
            'Làm sạch và để khô hoàn toàn trước khi cất giữ.',
        ]);

    $motherBabyWarningList = $warningItems->isNotEmpty()
        ? $warningItems
        : collect([
            'Luôn kiểm tra sản phẩm trước mỗi lần sử dụng.',
            'Ngưng sử dụng nếu phát hiện bộ phận hư hỏng, lỏng hoặc bất thường.',
        ]);

    $motherBabyCareItems = collect([
        'Vệ sinh định kỳ theo đặc điểm của từng sản phẩm.',
        'Ưu tiên làm sạch ngay sau khi sử dụng để giữ sản phẩm gọn và sạch.',
    ]);
@endphp

<section class="product-long-content product-mother-baby-detail">

    {{-- =========================================================
         OVERVIEW
    ========================================================== --}}
    <article class="product-mother-baby-overview">

        <div class="product-mother-baby-overview-grid">

            <div class="product-mother-baby-overview-spec">

                <div class="product-mother-baby-section-heading">
                    <span class="product-mother-baby-heading-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24">
                            <path d="M7 4h10v16H7z"></path>
                            <path d="M9 8h6M9 12h6M9 16h4"></path>
                        </svg>
                    </span>

                    <div>
                        <span>Thông tin sản phẩm</span>
                        <h2>Chi tiết đồ dùng mẹ &amp; bé</h2>
                    </div>
                </div>

                <div class="product-spec-table product-mother-baby-spec-table">

                    <div class="product-spec-row">
                        <strong>Tên sản phẩm</strong>
                        <span>{{ $product->name }}</span>
                    </div>

                    @if ($brandTag?->name)
                        <div class="product-spec-row">
                            <strong>Thương hiệu</strong>
                            <span>{{ $brandTag->name }}</span>
                        </div>
                    @endif

                    <div class="product-spec-row">
                        <strong>Loại sản phẩm</strong>
                        <span>{{ $motherBabyTypeLabel }}</span>
                    </div>

                    <div class="product-spec-row">
                        <strong>Đặc điểm nổi bật</strong>
                        <span>{{ $motherBabyPrimaryFeature }}</span>
                    </div>

                    @if ($motherBabyMaterial)
                        <div class="product-spec-row">
                            <strong>Chất liệu</strong>
                            <span>{{ $motherBabyMaterial }}</span>
                        </div>
                    @endif

                    @if ($motherBabyCapacity)
                        <div class="product-spec-row">
                            <strong>Quy cách</strong>
                            <span>{{ $motherBabyCapacity }}</span>
                        </div>
                    @endif

                    @if ($motherBabyDimensions)
                        <div class="product-spec-row">
                            <strong>Kích thước</strong>
                            <span>{{ $motherBabyDimensions }}</span>
                        </div>
                    @endif

                    @if ($motherBabyAgeText)
                        <div class="product-spec-row">
                            <strong>Độ tuổi</strong>
                            <span>{{ $motherBabyAgeText }}</span>
                        </div>
                    @endif

                    @if ($product->origin)
                        <div class="product-spec-row">
                            <strong>Xuất xứ</strong>
                            <span>{{ $product->origin }}</span>
                        </div>
                    @endif

                    <div class="product-spec-row">
                        <strong>Tình trạng</strong>
                        <span class="{{ $product->stock > 0 ? 'is-stock' : 'is-out' }}">
                            {{ $product->stock > 0
                                ? 'Còn ' . $product->stock . ' sản phẩm'
                                : 'Hết hàng' }}
                        </span>
                    </div>

                </div>

            </div>

            <div class="product-mother-baby-overview-description">

                <div class="product-mother-baby-section-heading">
                    <span class="product-mother-baby-heading-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24">
                            <path d="M5 4h14v16H5z"></path>
                            <path d="M8 8h8M8 12h8M8 16h5"></path>
                        </svg>
                    </span>

                    <div>
                        <span>Tổng quan</span>
                        <h2>Mô tả sản phẩm</h2>
                    </div>
                </div>

                <div class="product-mother-baby-description-text">
                    {{ $product->description ?: 'Thông tin mô tả sản phẩm đang được cập nhật.' }}
                </div>

                <div class="product-mother-baby-fact-grid">
                    @foreach ($motherBabyFacts as $fact)
                        <div class="product-mother-baby-fact-item">
                            <span class="product-mother-baby-fact-number">
                                {{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}
                            </span>

                            <span class="product-mother-baby-fact-icon" aria-hidden="true">
                                @if (in_array($fact['icon'], ['baby', 'heart']))
                                    <svg viewBox="0 0 24 24">
                                        <circle cx="12" cy="8" r="3"></circle>
                                        <path d="M6 20c.8-4 3-6 6-6s5.2 2 6 6"></path>
                                    </svg>
                                @elseif (in_array($fact['icon'], ['bag', 'container', 'grid', 'box']))
                                    <svg viewBox="0 0 24 24">
                                        <path d="M5 8h14v11H5z"></path>
                                        <path d="M9 8V6a3 3 0 0 1 6 0v2"></path>
                                    </svg>
                                @elseif (in_array($fact['icon'], ['shield', 'belt']))
                                    <svg viewBox="0 0 24 24">
                                        <path d="M12 3 5 6v5c0 4.7 2.8 8.3 7 10 4.2-1.7 7-5.3 7-10V6l-7-3Z"></path>
                                        <path d="m9 12 2 2 4-4"></path>
                                    </svg>
                                @elseif (in_array($fact['icon'], ['clean', 'snow']))
                                    <svg viewBox="0 0 24 24">
                                        <path d="M12 3v18M4.2 7.5l15.6 9M19.8 7.5l-15.6 9"></path>
                                    </svg>
                                @elseif (in_array($fact['icon'], ['blender', 'blade']))
                                    <svg viewBox="0 0 24 24">
                                        <path d="M7 4h10l-1 10H8L7 4Z"></path>
                                        <path d="M9 14v5h6v-5"></path>
                                        <path d="M10 8h4"></path>
                                    </svg>
                                @elseif (in_array($fact['icon'], ['chair', 'carrier', 'move']))
                                    <svg viewBox="0 0 24 24">
                                        <path d="M8 4v9h8"></path>
                                        <path d="M8 9h8v8"></path>
                                        <path d="M7 20v-7M17 20v-7"></path>
                                    </svg>
                                @else
                                    <svg viewBox="0 0 24 24">
                                        <circle cx="12" cy="12" r="8"></circle>
                                        <path d="m8 12 2.5 2.5L16 9"></path>
                                    </svg>
                                @endif
                            </span>

                            <div>
                                <strong>{{ $fact['title'] }}</strong>
                                <span>{{ $fact['subtitle'] }}</span>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="product-mother-baby-trust-banner">
                    <span class="product-mother-baby-trust-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24">
                            <path d="M12 3 5 6v5c0 4.7 2.8 8.3 7 10 4.2-1.7 7-5.3 7-10V6l-7-3Z"></path>
                            <path d="m9 12 2 2 4-4"></path>
                        </svg>
                    </span>

                    <div>
                        <strong>{{ $product->name }}</strong>
                        <span>Đọc kỹ hướng dẫn và kiểm tra sản phẩm trước khi sử dụng.</span>
                    </div>
                </div>

            </div>

        </div>

    </article>

    {{-- =========================================================
         CẤU TẠO / TIỆN ÍCH
    ========================================================== --}}
    <article class="product-mother-baby-section-card">

        <div class="product-mother-baby-card-heading">
            <div class="product-mother-baby-section-heading">
                <span class="product-mother-baby-heading-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24">
                        <circle cx="6" cy="6" r="2"></circle>
                        <circle cx="18" cy="6" r="2"></circle>
                        <circle cx="6" cy="18" r="2"></circle>
                        <circle cx="18" cy="18" r="2"></circle>
                        <path d="M8 6h8M6 8v8M18 8v8M8 18h8"></path>
                    </svg>
                </span>

                <div>
                    <span>Cấu tạo sản phẩm</span>
                    <h2>Cấu tạo &amp; tiện ích</h2>
                </div>
            </div>

            <p>Các bộ phận chính được trình bày theo từng nhóm chức năng để ba mẹ dễ hình dung cấu tạo, cách thao tác và những điểm cần kiểm tra trước khi sử dụng.</p>
        </div>

        <div class="product-mother-baby-structure-grid">
            @foreach ($motherBabyStructureItems as $item)
                <div class="product-mother-baby-structure-item">
                    <span class="product-mother-baby-structure-number">
                        {{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}
                    </span>

                    <span class="product-mother-baby-structure-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24">
                            <path d="M6 5h12v14H6z"></path>
                            <path d="M9 9h6M9 13h6"></path>
                        </svg>
                    </span>

                    <div>
                        <strong>{{ $item['title'] }}</strong>
                        <span>{{ $item['text'] }}</span>
                    </div>
                </div>
            @endforeach
        </div>

    </article>


    {{-- =========================================================
         ĐIỂM NỔI BẬT / LỢI ÍCH
    ========================================================== --}}
    <article class="product-mother-baby-section-card product-mother-baby-benefit-section">

        <div class="product-mother-baby-card-heading">
            <div class="product-mother-baby-section-heading">
                <span class="product-mother-baby-heading-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24">
                        <path d="m12 3 2.4 4.9 5.4.8-3.9 3.8.9 5.4-4.8-2.5-4.8 2.5.9-5.4-3.9-3.8 5.4-.8L12 3Z"></path>
                    </svg>
                </span>

                <div>
                    <span>Giá trị sử dụng</span>
                    <h2>Điểm nổi bật &amp; lợi ích</h2>
                </div>
            </div>

            <p>Những lợi ích dưới đây được viết riêng theo từng loại sản phẩm, giúp ba mẹ hiểu rõ giá trị sử dụng thực tế thay vì chỉ nhìn vào tên hoặc hình thức bên ngoài.</p>
        </div>

        <div class="product-mother-baby-benefit-grid">
            @foreach ($motherBabyBenefitItems as $item)
                <div class="product-mother-baby-benefit-item">
                    <span class="product-mother-baby-benefit-number">
                        {{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}
                    </span>

                    <span class="product-mother-baby-benefit-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24">
                            @if (in_array($item['icon'], ['clean', 'snow']))
                                <path d="M12 3v18M4.2 7.5l15.6 9M19.8 7.5l-15.6 9"></path>
                            @elseif (in_array($item['icon'], ['bag', 'grid', 'box']))
                                <path d="M5 8h14v11H5z"></path>
                                <path d="M9 8V6a3 3 0 0 1 6 0v2"></path>
                            @elseif (in_array($item['icon'], ['shield', 'belt']))
                                <path d="M12 3 5 6v5c0 4.7 2.8 8.3 7 10 4.2-1.7 7-5.3 7-10V6l-7-3Z"></path>
                                <path d="m9 12 2 2 4-4"></path>
                            @elseif (in_array($item['icon'], ['blade', 'capacity']))
                                <path d="M7 4h10l-1 10H8L7 4Z"></path>
                                <path d="M9 14v5h6v-5"></path>
                            @elseif (in_array($item['icon'], ['chair', 'tray', 'bib']))
                                <path d="M7 6h10v8H7z"></path>
                                <path d="M8 14v6M16 14v6"></path>
                            @elseif (in_array($item['icon'], ['move', 'rotate', 'adjust', 'balance']))
                                <circle cx="12" cy="12" r="8"></circle>
                                <path d="M8 12h8M12 8v8"></path>
                            @else
                                <circle cx="12" cy="12" r="8"></circle>
                                <path d="m8 12 2.5 2.5L16 9"></path>
                            @endif
                        </svg>
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
    <article class="product-mother-baby-section-card product-mother-baby-suitable-section">

        <div class="product-mother-baby-card-heading">
            <div class="product-mother-baby-section-heading">
                <span class="product-mother-baby-heading-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24">
                        <circle cx="12" cy="8" r="3"></circle>
                        <path d="M5 20c.8-4.4 3.2-6.5 7-6.5s6.2 2.1 7 6.5"></path>
                    </svg>
                </span>

                <div>
                    <span>Gợi ý sử dụng</span>
                    <h2>Phù hợp sử dụng cho</h2>
                </div>
            </div>

            <p>Các tình huống dưới đây giúp ba mẹ hình dung sản phẩm thường được dùng khi nào, phù hợp với nhu cầu nào và nên kết hợp với thói quen chăm sóc bé ra sao.</p>
        </div>

        <div class="product-mother-baby-suitable-grid">
            @foreach ($motherBabySuitableItems as $item)
                <div class="product-mother-baby-suitable-item">
                    <span class="product-mother-baby-suitable-index">
                        {{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}
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
    <article class="product-mother-baby-section-card">

        <div class="product-mother-baby-card-heading">
            <div class="product-mother-baby-section-heading">
                <span class="product-mother-baby-heading-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24">
                        <path d="M7 4h10v16H7z"></path>
                        <path d="M10 2h4v3h-4z"></path>
                        <path d="m9 12 2 2 4-4"></path>
                    </svg>
                </span>

                <div>
                    <span>Sử dụng đúng cách</span>
                    <h2>Hướng dẫn sử dụng</h2>
                </div>
            </div>

            <p>Thực hiện theo từng bước và ưu tiên hướng dẫn cụ thể của nhà sản xuất.</p>
        </div>

        <div class="product-mother-baby-steps">
            @foreach ($motherBabyUsageSlots as $step)
                <div class="product-mother-baby-step">

                    <div class="product-mother-baby-step-head">
                        <span class="product-mother-baby-step-number">
                            {{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}
                        </span>

                        <span class="product-mother-baby-step-icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24">
                                @if ($loop->iteration === 1)
                                    <path d="M5 4h14v16H5z"></path>
                                    <path d="M8 8h8M8 12h8"></path>
                                @elseif ($loop->iteration === 2)
                                    <path d="M4 12h16"></path>
                                    <path d="m8 8-4 4 4 4M16 8l4 4-4 4"></path>
                                @elseif ($loop->iteration === 3)
                                    <circle cx="12" cy="12" r="8"></circle>
                                    <path d="m8 12 2.5 2.5L16 9"></path>
                                @else
                                    <path d="M12 3 5 6v5c0 4.7 2.8 8.3 7 10 4.2-1.7 7-5.3 7-10V6l-7-3Z"></path>
                                    <path d="m9 12 2 2 4-4"></path>
                                @endif
                            </svg>
                        </span>
                    </div>

                    <strong>{{ $motherBabyStepTitles->get($loop->index) }}</strong>
                    <p>{{ $step }}</p>

                </div>
            @endforeach
        </div>

    </article>


    {{-- =========================================================
         BOTTOM INFO
    ========================================================== --}}
    <div class="product-mother-baby-info-grid">

        <article class="product-mother-baby-info-card">
            <div class="product-mother-baby-info-title">
                <span class="product-mother-baby-info-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24">
                        <path d="M12 3 5 6v5c0 4.7 2.8 8.3 7 10 4.2-1.7 7-5.3 7-10V6l-7-3Z"></path>
                        <path d="m9 12 2 2 4-4"></path>
                    </svg>
                </span>

                <div>
                    <span>Bảo quản</span>
                    <h3>Giữ sản phẩm đúng cách</h3>
                </div>
            </div>

            <ul>
                @foreach ($motherBabyStorageList as $item)
                    <li>{{ $item }}</li>
                @endforeach
            </ul>
        </article>

        <article class="product-mother-baby-info-card is-care">
            <div class="product-mother-baby-info-title">
                <span class="product-mother-baby-info-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24">
                        <path d="M12 3v18M4.2 7.5l15.6 9M19.8 7.5l-15.6 9"></path>
                    </svg>
                </span>

                <div>
                    <span>Vệ sinh</span>
                    <h3>Chăm sóc sản phẩm</h3>
                </div>
            </div>

            <ul>
                @foreach ($motherBabyCareItems as $item)
                    <li>{{ $item }}</li>
                @endforeach
            </ul>
        </article>

        <article class="product-mother-baby-info-card is-warning">
            <div class="product-mother-baby-info-title">
                <span class="product-mother-baby-info-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24">
                        <path d="M12 3 2.8 20h18.4L12 3Z"></path>
                        <path d="M12 9v5"></path>
                        <path d="M12 17h.01"></path>
                    </svg>
                </span>

                <div>
                    <span>Lưu ý</span>
                    <h3>An toàn khi sử dụng</h3>
                </div>
            </div>

            <ul>
                @foreach ($motherBabyWarningList as $item)
                    <li>{{ $item }}</li>
                @endforeach
            </ul>
        </article>

    </div>

</section>
