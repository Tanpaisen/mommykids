@php
    /*
     * Template riêng cho category: vitamin-suc-khoe
     * Dùng dữ liệu Product + brand/attribute tags hiện có.
     * Không dựng rating, lượt bán hoặc tuyên bố điều trị.
     */

    $vitaminAttributeNames = $attributeTags
        ->pluck('name')
        ->filter()
        ->map(fn ($name) => trim($name))
        ->values();

    $vitaminAttributeSlugs = $attributeTags
        ->pluck('slug')
        ->filter()
        ->map(fn ($slug) => trim($slug))
        ->values();

    $hasVitaminAttribute = function (array $slugs) use ($vitaminAttributeSlugs) {
        return $vitaminAttributeSlugs->contains(
            fn ($slug) => in_array($slug, $slugs, true)
        );
    };

    $findVitaminAttribute = function (array $keywords) use ($vitaminAttributeNames) {
        return $vitaminAttributeNames->first(function ($name) use ($keywords) {
            foreach ($keywords as $keyword) {
                if (mb_stripos($name, $keyword) !== false) {
                    return true;
                }
            }

            return false;
        });
    };

    $vitaminProductName = mb_strtolower(trim($product->name ?? ''));

    /*
     * Nhận diện subtype.
     * Thứ tự quan trọng vì một sản phẩm có thể có nhiều dưỡng chất.
     */
    $isVitaminD3K2 =
        $hasVitaminAttribute(['vitamin-d3', 'vitamin-k2'])
        || str_contains($vitaminProductName, 'd3')
        || str_contains($vitaminProductName, 'k2');

    $isVitaminProbiotic =
        $hasVitaminAttribute(['men-vi-sinh', 'l-reuteri-dsm-17938'])
        || str_contains($vitaminProductName, 'biogaia')
        || str_contains($vitaminProductName, 'men vi sinh');

    $isVitaminMulti =
        $hasVitaminAttribute(['vitamin-tong-hop'])
        || str_contains($vitaminProductName, 'multi-vitamin')
        || str_contains($vitaminProductName, 'multivitamin');

    $isVitaminDha =
        $hasVitaminAttribute(['dha', 'omega-3'])
        || str_contains($vitaminProductName, 'dha')
        || str_contains($vitaminProductName, 'omega');

    $isVitaminIron =
        $hasVitaminAttribute(['sat'])
        || str_contains($vitaminProductName, 'sắt');

    $isVitaminCalcium =
        $hasVitaminAttribute(['canxi'])
        || str_contains($vitaminProductName, 'calcium')
        || str_contains($vitaminProductName, 'canxi');

    $isVitaminNutritionSupport =
        !$isVitaminD3K2
        && !$isVitaminProbiotic
        && !$isVitaminMulti
        && !$isVitaminDha
        && !$isVitaminIron
        && !$isVitaminCalcium;

    /*
     * Thông tin chung từ attribute.
     */
    $vitaminForm = $findVitaminAttribute([
        'dạng nhỏ giọt',
        'dạng xịt',
        'dạng siro',
        'viên nhai',
        'viên nang mềm',
        'dạng lỏng',
    ]);

    $vitaminPack = $vitaminAttributeNames->first(function ($name) {
        return preg_match(
            '/\b\d+(?:[.,]\d+)?\s*(?:ml|mL|ML|viên|vien)\b/u',
            $name
        );
    });

    $vitaminAgeAttribute = $findVitaminAttribute([
        '0M+',
        '4M+',
        '2Y+',
    ]);

    $vitaminAgeText =
        $ageText
        ?: $vitaminAgeAttribute;

    $vitaminPrimaryNutrient = match (true) {
        $isVitaminD3K2 => collect([
            $findVitaminAttribute(['Vitamin D3']),
            $findVitaminAttribute(['Vitamin K2']),
        ])->filter()->join(' + ') ?: 'Vitamin D3 + K2',

        $isVitaminProbiotic =>
            $findVitaminAttribute(['L. reuteri DSM 17938', 'Men vi sinh'])
            ?: 'Men vi sinh',

        $isVitaminMulti =>
            $findVitaminAttribute(['Vitamin tổng hợp'])
            ?: 'Vitamin tổng hợp',

        $isVitaminDha =>
            $findVitaminAttribute(['DHA'])
            ?: 'DHA',

        $isVitaminIron =>
            $findVitaminAttribute(['Sắt'])
            ?: 'Sắt',

        $isVitaminCalcium =>
            $findVitaminAttribute(['Canxi'])
            ?: 'Canxi',

        default =>
            $findVitaminAttribute(['Hỗ trợ dinh dưỡng'])
            ?: 'Hỗ trợ dinh dưỡng',
    };

    $vitaminTypeLabel = match (true) {
        $isVitaminD3K2 => 'Vitamin D3 / K2',
        $isVitaminProbiotic => 'Men vi sinh',
        $isVitaminMulti => 'Vitamin tổng hợp',
        $isVitaminDha => 'DHA / Omega-3',
        $isVitaminIron => 'Sắt',
        $isVitaminCalcium => 'Canxi',
        default => 'Dinh dưỡng bổ sung',
    };

    /*
     * Highlight: ưu tiên dữ liệu highlights thật nếu có,
     * thiếu thì dùng mô tả trung tính theo subtype.
     */
    $vitaminHighlightData = is_array($product->highlights)
        ? $product->highlights
        : [];

    $vitaminBenefits = collect(data_get($vitaminHighlightData, 'items', []))
        ->filter(fn ($item) => filled(data_get($item, 'title')))
        ->take(4)
        ->map(fn ($item) => [
            'title' => data_get($item, 'title'),
            'subtitle' => data_get($item, 'subtitle') ?: 'Thông tin sản phẩm',
            'icon' => data_get($item, 'icon') ?: 'check',
        ])
        ->values();

    $vitaminFallbackBenefits = collect(
        match (true) {
            $isVitaminD3K2 => [
                ['title' => 'D3 & K2', 'subtitle' => 'Dưỡng chất chính', 'icon' => 'drop'],
                ['title' => $vitaminForm ?: 'Dạng dùng tiện lợi', 'subtitle' => 'Dạng sản phẩm', 'icon' => 'bottle'],
                ['title' => $vitaminAgeText ?: 'Theo nhãn sản phẩm', 'subtitle' => 'Độ tuổi sử dụng', 'icon' => 'user'],
                ['title' => $vitaminPack ?: 'Theo quy cách', 'subtitle' => 'Quy cách', 'icon' => 'box'],
            ],

            $isVitaminProbiotic => [
                ['title' => 'Men vi sinh', 'subtitle' => 'Nhóm sản phẩm', 'icon' => 'microbe'],
                ['title' => $findVitaminAttribute(['L. reuteri DSM 17938']) ?: 'Chủng lợi khuẩn', 'subtitle' => 'Thông tin chủng', 'icon' => 'shield'],
                ['title' => $vitaminForm ?: 'Dạng dùng tiện lợi', 'subtitle' => 'Dạng sản phẩm', 'icon' => 'bottle'],
                ['title' => $vitaminAgeText ?: 'Theo nhãn sản phẩm', 'subtitle' => 'Độ tuổi sử dụng', 'icon' => 'user'],
            ],

            $isVitaminMulti => [
                ['title' => 'Vitamin tổng hợp', 'subtitle' => 'Nhóm sản phẩm', 'icon' => 'spark'],
                ['title' => $vitaminForm ?: 'Dạng dùng tiện lợi', 'subtitle' => 'Dạng sản phẩm', 'icon' => 'bottle'],
                ['title' => $vitaminAgeText ?: 'Theo nhãn sản phẩm', 'subtitle' => 'Độ tuổi sử dụng', 'icon' => 'user'],
                ['title' => $vitaminPack ?: 'Theo quy cách', 'subtitle' => 'Quy cách', 'icon' => 'box'],
            ],

            $isVitaminDha => [
                ['title' => 'DHA', 'subtitle' => 'Dưỡng chất chính', 'icon' => 'drop'],
                ['title' => $findVitaminAttribute(['Omega-3']) ?: 'Omega-3', 'subtitle' => 'Nhóm dưỡng chất', 'icon' => 'wave'],
                ['title' => $vitaminForm ?: 'Viên nang mềm', 'subtitle' => 'Dạng sản phẩm', 'icon' => 'capsule'],
                ['title' => $vitaminPack ?: 'Theo quy cách', 'subtitle' => 'Quy cách', 'icon' => 'box'],
            ],

            $isVitaminIron => [
                ['title' => 'Sắt', 'subtitle' => 'Dưỡng chất chính', 'icon' => 'drop'],
                ['title' => $vitaminForm ?: 'Dạng dùng tiện lợi', 'subtitle' => 'Dạng sản phẩm', 'icon' => 'bottle'],
                ['title' => $vitaminAgeText ?: 'Theo nhãn sản phẩm', 'subtitle' => 'Độ tuổi sử dụng', 'icon' => 'user'],
                ['title' => 'Đúng liều lượng', 'subtitle' => 'Theo hướng dẫn trên nhãn', 'icon' => 'check'],
            ],

            $isVitaminCalcium => [
                ['title' => 'Canxi', 'subtitle' => 'Dưỡng chất chính', 'icon' => 'spark'],
                ['title' => 'Sản phẩm bổ sung', 'subtitle' => 'Nhóm sản phẩm', 'icon' => 'shield'],
                ['title' => $vitaminForm ?: 'Theo sản phẩm', 'subtitle' => 'Dạng sử dụng', 'icon' => 'bottle'],
                ['title' => 'Đúng liều lượng', 'subtitle' => 'Theo hướng dẫn trên nhãn', 'icon' => 'check'],
            ],

            default => [
                ['title' => 'Dinh dưỡng bổ sung', 'subtitle' => 'Nhóm sản phẩm', 'icon' => 'spark'],
                ['title' => $vitaminForm ?: 'Dạng dùng tiện lợi', 'subtitle' => 'Dạng sản phẩm', 'icon' => 'bottle'],
                ['title' => $vitaminAgeText ?: 'Theo nhãn sản phẩm', 'subtitle' => 'Đối tượng sử dụng', 'icon' => 'user'],
                ['title' => 'Dùng đúng hướng dẫn', 'subtitle' => 'Theo thông tin trên bao bì', 'icon' => 'check'],
            ],
        }
    );

    foreach ($vitaminFallbackBenefits as $benefit) {
        if ($vitaminBenefits->count() >= 4) {
            break;
        }

        $vitaminBenefits->push($benefit);
    }

    $vitaminBenefits = $vitaminBenefits->take(4)->values();

    $vitaminHighlightMessage = data_get($vitaminHighlightData, 'message')
        ?: $product->name;

    $vitaminHighlightSubmessage = data_get($vitaminHighlightData, 'submessage')
        ?: 'Thông tin sử dụng cần được đối chiếu với nhãn và hướng dẫn của sản phẩm.';

    /*
     * Thành phần / dưỡng chất:
     * ưu tiên từng dòng ingredients đã lưu trong DB.
     */
    $vitaminIngredientLines = collect(
        preg_split('/\r\n|\r|\n/', trim($product->ingredients ?? ''))
    )
        ->map(fn ($item) => trim($item))
        ->filter()
        ->take(4)
        ->values();

    $vitaminIngredientFallback = collect(
        match (true) {
            $isVitaminD3K2 => [
                'Vitamin D3',
                'Vitamin K2',
                'Chất nền theo công bố sản phẩm',
                'Phụ liệu theo nhãn sản phẩm',
            ],
            $isVitaminProbiotic => [
                'Chủng lợi khuẩn',
                'Thành phần nền',
                'Phụ liệu theo công bố sản phẩm',
                'Thông tin chi tiết trên bao bì',
            ],
            $isVitaminMulti => [
                'Vitamin',
                'Khoáng chất',
                'Thành phần nền',
                'Thông tin chi tiết trên bao bì',
            ],
            $isVitaminDha => [
                'DHA',
                'Omega-3',
                'Thành phần viên nang',
                'Thông tin chi tiết trên bao bì',
            ],
            $isVitaminIron => [
                'Sắt',
                'Thành phần nền',
                'Phụ liệu theo công bố sản phẩm',
                'Thông tin chi tiết trên bao bì',
            ],
            $isVitaminCalcium => [
                'Canxi',
                'Thành phần nền',
                'Phụ liệu theo công bố sản phẩm',
                'Thông tin chi tiết trên bao bì',
            ],
            default => [
                'Thành phần chính',
                'Dưỡng chất',
                'Thành phần nền',
                'Thông tin chi tiết trên bao bì',
            ],
        }
    );

    while ($vitaminIngredientLines->count() < 4) {
        $fallback = $vitaminIngredientFallback->get($vitaminIngredientLines->count());

        if (!$fallback) {
            break;
        }

        $vitaminIngredientLines->push($fallback);
    }

    /*
     * 4 bước sử dụng.
     * Nếu DB có ít hơn 4 dòng thì bổ sung hướng dẫn trung tính,
     * không tự dựng liều lượng.
     */
    $vitaminUsageFallback = collect(
        match (true) {
            $isVitaminProbiotic => [
                'Đọc kỹ hướng dẫn và kiểm tra hạn sử dụng.',
                'Chuẩn bị sản phẩm đúng theo dạng dùng.',
                'Sử dụng đúng liều lượng ghi trên nhãn.',
                'Đậy kín và bảo quản đúng hướng dẫn sau khi dùng.',
            ],
            $isVitaminD3K2 || $isVitaminIron => [
                'Đọc kỹ hướng dẫn và kiểm tra hạn sử dụng.',
                'Lấy sản phẩm theo đúng dạng nhỏ giọt hoặc dạng xịt.',
                'Sử dụng đúng liều lượng theo nhóm tuổi trên nhãn.',
                'Đậy kín và bảo quản đúng hướng dẫn sau khi dùng.',
            ],
            $isVitaminMulti => [
                'Lắc sản phẩm nếu hướng dẫn trên nhãn yêu cầu.',
                'Đo lượng sử dụng bằng dụng cụ phù hợp.',
                'Dùng đúng liều lượng theo độ tuổi ghi trên nhãn.',
                'Đậy kín và bảo quản đúng hướng dẫn sau khi dùng.',
            ],
            $isVitaminDha => [
                'Kiểm tra bao bì và hạn sử dụng.',
                'Xác định liều dùng phù hợp theo độ tuổi trên nhãn.',
                'Sử dụng sản phẩm theo đúng hướng dẫn.',
                'Bảo quản sản phẩm đúng điều kiện sau khi mở.',
            ],
            default => [
                'Đọc kỹ hướng dẫn và kiểm tra bao bì.',
                'Chuẩn bị sản phẩm theo đúng dạng sử dụng.',
                'Dùng đúng liều lượng được ghi trên nhãn.',
                'Bảo quản đúng hướng dẫn sau khi sử dụng.',
            ],
        }
    );

    $vitaminUsageSlots = collect(range(0, 3))
        ->map(function ($index) use ($usageSteps, $vitaminUsageFallback) {
            return $usageSteps->get($index)
                ?: $vitaminUsageFallback->get($index);
        });

    $vitaminStepTitles = collect(
        match (true) {
            $isVitaminMulti => [
                'Chuẩn bị',
                'Đo lượng dùng',
                'Sử dụng',
                'Bảo quản',
            ],
            $isVitaminDha => [
                'Kiểm tra',
                'Xác định liều',
                'Sử dụng',
                'Bảo quản',
            ],
            default => [
                'Kiểm tra',
                'Chuẩn bị',
                'Sử dụng đúng liều',
                'Bảo quản',
            ],
        }
    );

    /*
     * Các khối thông tin bên dưới được viết dài hơn để trang chi tiết
     * có chiều sâu hơn, nhưng vẫn ưu tiên dữ liệu thật trong DB.
     */
    $vitaminStorageFallback = collect(
        match (true) {
            $isVitaminProbiotic => [
                'Đậy kín sản phẩm ngay sau mỗi lần sử dụng và bảo quản theo đúng điều kiện nhiệt độ được ghi trên nhãn.',
                'Tránh để sản phẩm ở nơi có nhiệt độ cao, ánh nắng trực tiếp hoặc khu vực thường xuyên thay đổi nhiệt độ.',
                'Không để đầu nhỏ giọt, miệng chai hoặc viên sản phẩm tiếp xúc với bề mặt bẩn để hạn chế nhiễm bẩn trong quá trình dùng.',
                'Để sản phẩm ngoài tầm với của trẻ nhỏ và luôn kiểm tra hạn sử dụng sau khi mở nếu nhà sản xuất có quy định riêng.',
            ],
            $isVitaminMulti => [
                'Đậy kín nắp sau khi lấy đủ lượng sử dụng; lau sạch phần miệng chai hoặc dụng cụ đo nếu có sản phẩm bám lại.',
                'Bảo quản tại nơi khô ráo, thoáng mát và tuân thủ điều kiện nhiệt độ cụ thể được in trên bao bì.',
                'Không để sản phẩm gần bếp, cửa sổ có nắng gắt hoặc khu vực ẩm cao vì có thể ảnh hưởng đến chất lượng bảo quản.',
                'Nếu sản phẩm có yêu cầu dùng trong một khoảng thời gian sau khi mở nắp, nên ghi nhớ ngày mở để dễ theo dõi.',
            ],
            $isVitaminDha => [
                'Đậy kín hộp hoặc chai ngay sau khi lấy viên để hạn chế sản phẩm tiếp xúc lâu với không khí và độ ẩm.',
                'Bảo quản nơi khô ráo, tránh ánh nắng trực tiếp và tránh để gần nguồn nhiệt cao.',
                'Không để viên nang trong cốp xe, ô tô đóng kín hoặc những nơi có nhiệt độ tăng cao trong thời gian dài.',
                'Giữ sản phẩm ngoài tầm với của trẻ nhỏ và kiểm tra tình trạng viên trước mỗi lần sử dụng.',
            ],
            $isVitaminIron => [
                'Đậy kín nắp ngay sau khi sử dụng và lau sạch đầu nhỏ giọt nếu có dung dịch bám bên ngoài.',
                'Bảo quản theo điều kiện trên nhãn, tránh ánh nắng trực tiếp và tránh để sản phẩm ở nơi quá nóng hoặc quá ẩm.',
                'Không chuyển dung dịch sang chai hoặc dụng cụ chứa khác nếu không cần thiết vì có thể làm mất thông tin nhận diện và liều dùng.',
                'Đặt sản phẩm ngoài tầm với của trẻ nhỏ; đây là nhóm sản phẩm cần đặc biệt chú ý đến lượng sử dụng.',
            ],
            $isVitaminCalcium => [
                'Đóng kín nắp hộp sau khi lấy viên để hạn chế độ ẩm ảnh hưởng đến bề mặt và chất lượng viên nang.',
                'Bảo quản sản phẩm ở nơi khô ráo, thoáng mát, tránh ánh nắng trực tiếp và nguồn nhiệt cao.',
                'Không để viên trong môi trường ẩm như gần bồn rửa hoặc phòng tắm; luôn dùng tay khô khi lấy viên.',
                'Giữ sản phẩm trong bao bì gốc để dễ kiểm tra hạn sử dụng, thành phần và hướng dẫn theo từng nhóm tuổi.',
            ],
            default => [
                'Đậy kín sản phẩm ngay sau khi sử dụng và bảo quản theo đúng điều kiện được ghi trên bao bì.',
                'Tránh ánh nắng trực tiếp, nhiệt độ cao và nơi có độ ẩm lớn trong thời gian dài.',
                'Giữ sản phẩm trong bao bì gốc để thuận tiện đối chiếu thành phần, liều lượng và hạn sử dụng.',
                'Để ngoài tầm với của trẻ nhỏ và kiểm tra tình trạng sản phẩm trước mỗi lần dùng.',
            ],
        }
    );

    $vitaminStorageList = $storageItems->values();

    foreach ($vitaminStorageFallback as $item) {
        if ($vitaminStorageList->count() >= 4) {
            break;
        }

        if (!$vitaminStorageList->contains($item)) {
            $vitaminStorageList->push($item);
        }
    }

    $vitaminWarningFallback = collect(
        match (true) {
            $isVitaminD3K2 => [
                'Không tự ý tăng số giọt, số lần xịt hoặc số lần sử dụng vượt quá hướng dẫn trên nhãn sản phẩm.',
                'Nếu trẻ đang dùng thêm sản phẩm có chứa vitamin D hoặc vitamin K, nên kiểm tra tổng lượng bổ sung để hạn chế dùng trùng.',
                'Sản phẩm bổ sung không thay thế chế độ ăn đa dạng, vận động và các khuyến nghị chăm sóc phù hợp theo độ tuổi.',
                'Ngưng sử dụng và tham khảo ý kiến chuyên môn nếu trẻ xuất hiện biểu hiện bất thường trong quá trình dùng.',
            ],
            $isVitaminProbiotic => [
                'Không pha men vi sinh vào thức ăn hoặc đồ uống đang quá nóng nếu hướng dẫn của nhà sản xuất không cho phép.',
                'Không tự ý tăng liều với mục đích rút ngắn thời gian sử dụng hoặc mong muốn tác dụng nhanh hơn.',
                'Với trẻ có tình trạng sức khỏe đặc biệt, sinh non hoặc đang được theo dõi y tế, nên hỏi ý kiến chuyên môn trước khi dùng.',
                'Ngưng sử dụng nếu sản phẩm thay đổi bất thường về mùi, màu, trạng thái hoặc bao bì không còn nguyên vẹn.',
            ],
            $isVitaminMulti => [
                'Không vượt quá lượng sử dụng theo độ tuổi vì công thức vitamin tổng hợp thường có nhiều dưỡng chất trong cùng một khẩu phần.',
                'Kiểm tra các sản phẩm bổ sung khác trẻ đang dùng để hạn chế trùng lặp vitamin hoặc khoáng chất không cần thiết.',
                'Sản phẩm bổ sung không thay thế một chế độ ăn đa dạng và cân bằng phù hợp với từng giai đoạn phát triển.',
                'Nếu trẻ đang dùng thuốc hoặc có cơ địa đặc biệt, nên tham khảo ý kiến chuyên môn trước khi sử dụng thường xuyên.',
            ],
            $isVitaminDha => [
                'Dùng đúng số viên hoặc lượng sản phẩm theo nhóm tuổi; không tự ý tăng liều chỉ vì sản phẩm ở dạng viên nang mềm.',
                'Với trẻ chưa thể nhai hoặc nuốt viên, chỉ xử lý viên nang theo đúng cách được nhà sản xuất hướng dẫn.',
                'Đọc kỹ thành phần và thông tin dị ứng trên bao bì, đặc biệt khi trẻ có tiền sử nhạy cảm với một số nguyên liệu.',
                'Ngưng sử dụng nếu viên bị chảy, biến dạng, có mùi bất thường hoặc bao bì bị hở.',
            ],
            $isVitaminIron => [
                'Không tự ý tăng liều sắt hoặc dùng đồng thời nhiều sản phẩm có chứa sắt khi chưa kiểm tra tổng lượng bổ sung.',
                'Dùng đúng dụng cụ đo hoặc số giọt được hướng dẫn để hạn chế sai lệch lượng sử dụng.',
                'Nếu trẻ đang sử dụng thuốc, có bệnh lý nền hoặc đang được theo dõi về dinh dưỡng, nên hỏi ý kiến chuyên môn trước khi dùng.',
                'Ngưng sử dụng và tham khảo ý kiến chuyên môn khi xuất hiện biểu hiện bất thường trong quá trình sử dụng.',
            ],
            $isVitaminCalcium => [
                'Dùng đúng số viên theo nhóm tuổi được ghi trên nhãn; không tự ý tăng lượng canxi hoặc vitamin D3.',
                'Kiểm tra các sản phẩm bổ sung canxi hoặc vitamin D khác đang sử dụng để hạn chế trùng thành phần.',
                'Với trẻ nhỏ chưa thể nhai hoặc nuốt viên, chỉ dùng theo phương pháp được nhà sản xuất hướng dẫn cho đúng độ tuổi.',
                'Sản phẩm bổ sung không thay thế chế độ ăn đa dạng; nếu trẻ có tình trạng sức khỏe đặc biệt nên tham khảo ý kiến chuyên môn.',
            ],
            default => [
                'Không tự ý vượt quá liều lượng được khuyến nghị trên nhãn sản phẩm.',
                'Đọc kỹ thành phần nếu trẻ có tiền sử dị ứng hoặc đang sử dụng nhiều sản phẩm bổ sung cùng lúc.',
                'Không xem sản phẩm bổ sung là sự thay thế hoàn toàn cho chế độ ăn cân bằng và chăm sóc phù hợp.',
                'Ngưng sử dụng và tham khảo ý kiến chuyên môn nếu xuất hiện phản ứng hoặc biểu hiện bất thường.',
            ],
        }
    );

    $vitaminWarningList = $warningItems->values();

    foreach ($vitaminWarningFallback as $item) {
        if ($vitaminWarningList->count() >= 4) {
            break;
        }

        if (!$vitaminWarningList->contains($item)) {
            $vitaminWarningList->push($item);
        }
    }

    $vitaminAudienceItems = collect(
        match (true) {
            $isVitaminD3K2 => [
                $vitaminAgeText
                    ? 'Độ tuổi tham khảo của sản phẩm: ' . $vitaminAgeText . '; liều dùng cụ thể cần đối chiếu theo từng nhóm tuổi trên nhãn.'
                    : 'Đối tượng sử dụng cần được xác định theo hướng dẫn cụ thể của nhà sản xuất.',
                'Phù hợp với gia đình đang tìm sản phẩm bổ sung vitamin D3 và K2 ở dạng nhỏ giọt hoặc dạng xịt thuận tiện cho việc chia liều.',
                'Nếu trẻ đang sử dụng thêm sản phẩm có vitamin D, vitamin K hoặc công thức đa vi chất, nên kiểm tra thành phần để hạn chế bổ sung trùng.',
                'Ba mẹ nên đọc kỹ hướng dẫn, thành phần, độ tuổi và cách bảo quản ngay từ lần sử dụng đầu tiên.',
            ],
            $isVitaminProbiotic => [
                $vitaminAgeText
                    ? 'Độ tuổi tham khảo của sản phẩm: ' . $vitaminAgeText . '; cách dùng và số lần dùng cần tuân thủ thông tin trên nhãn.'
                    : 'Đối tượng sử dụng theo hướng dẫn cụ thể trên bao bì.',
                'Phù hợp khi gia đình muốn lựa chọn một sản phẩm men vi sinh có thông tin về chủng lợi khuẩn và dạng sử dụng rõ ràng.',
                'Với trẻ có cơ địa đặc biệt hoặc đang được theo dõi y tế, việc sử dụng sản phẩm bổ sung nên được trao đổi với người có chuyên môn.',
                'Không sử dụng sản phẩm đã quá hạn, bao bì hở hoặc có dấu hiệu thay đổi trạng thái bất thường.',
            ],
            $isVitaminMulti => [
                $vitaminAgeText
                    ? 'Độ tuổi tham khảo của sản phẩm: ' . $vitaminAgeText . '; nên dùng đúng lượng đo và số lần dùng được ghi trên bao bì.'
                    : 'Đối tượng sử dụng theo hướng dẫn cụ thể của sản phẩm.',
                'Phù hợp với gia đình đang tìm sản phẩm vitamin và khoáng chất tổng hợp với cách dùng thuận tiện theo từng khẩu phần.',
                'Do công thức có nhiều vitamin và khoáng chất, cần kiểm tra các sản phẩm bổ sung khác trẻ đang sử dụng để tránh trùng lặp không cần thiết.',
                'Sản phẩm chỉ đóng vai trò bổ sung; bữa ăn đa dạng và chế độ sinh hoạt phù hợp vẫn là nền tảng chính.',
            ],
            $isVitaminDha => [
                $vitaminAgeText
                    ? 'Độ tuổi tham khảo của sản phẩm: ' . $vitaminAgeText . '; số viên sử dụng có thể thay đổi theo từng nhóm tuổi.'
                    : 'Độ tuổi và lượng sử dụng cần được đối chiếu trực tiếp với hướng dẫn trên nhãn.',
                'Phù hợp với gia đình đang tìm sản phẩm DHA/Omega-3 có hàm lượng được công bố theo từng viên hoặc từng khẩu phần.',
                'Với trẻ nhỏ chưa thể nhai hoặc nuốt viên, chỉ xử lý viên nang theo phương pháp mà nhà sản xuất hướng dẫn.',
                'Luôn kiểm tra thành phần và thông tin dị ứng trước khi sử dụng, nhất là khi trẻ từng có phản ứng với thực phẩm hoặc sản phẩm bổ sung.',
            ],
            $isVitaminIron => [
                $vitaminAgeText
                    ? 'Độ tuổi tham khảo của sản phẩm: ' . $vitaminAgeText . '; lượng bổ sung sắt cần tuân thủ hướng dẫn theo tuổi hoặc cân nặng nếu nhãn có quy định.'
                    : 'Đối tượng và lượng sử dụng cần căn cứ theo thông tin trên sản phẩm.',
                'Phù hợp khi gia đình cần một sản phẩm bổ sung sắt dạng nhỏ giọt, thuận tiện cho việc chia lượng sử dụng.',
                'Không tự ý tăng liều hoặc dùng nhiều sản phẩm chứa sắt cùng lúc khi chưa kiểm tra tổng lượng bổ sung.',
                'Nếu trẻ có tình trạng sức khỏe đặc biệt hoặc đang dùng thuốc, nên tham khảo ý kiến chuyên môn trước khi sử dụng.',
            ],
            $isVitaminCalcium => [
                $vitaminAgeText
                    ? 'Độ tuổi tham khảo của sản phẩm: ' . $vitaminAgeText . '; số viên sử dụng thay đổi theo từng nhóm tuổi nên cần đọc kỹ hướng dẫn.'
                    : 'Độ tuổi và lượng sử dụng cần được đối chiếu trực tiếp với nhãn sản phẩm.',
                'Phù hợp với gia đình đang tìm sản phẩm bổ sung canxi có thêm vitamin D3, dạng viên nang mềm và quy cách dùng theo nhóm tuổi.',
                'Với trẻ nhỏ chưa thể nhai hoặc nuốt viên, chỉ xử lý viên nang theo đúng phương pháp được nhà sản xuất hướng dẫn.',
                'Nên kiểm tra các sản phẩm bổ sung canxi hoặc vitamin D khác đang dùng để hạn chế trùng thành phần không cần thiết.',
            ],
            default => [
                $vitaminAgeText
                    ? 'Độ tuổi tham khảo của sản phẩm: ' . $vitaminAgeText . '; cách dùng chi tiết cần đối chiếu trên nhãn.'
                    : 'Đối tượng sử dụng theo hướng dẫn cụ thể trên bao bì.',
                'Phù hợp khi gia đình cần thêm một sản phẩm bổ sung dinh dưỡng với quy cách sử dụng rõ ràng.',
                'Đọc kỹ thành phần trước khi dùng nếu trẻ có tiền sử dị ứng hoặc đang sử dụng sản phẩm bổ sung khác.',
                'Nên duy trì chế độ ăn và sinh hoạt phù hợp thay vì phụ thuộc hoàn toàn vào sản phẩm bổ sung.',
            ],
        }
    );

    /*
     * Hai đoạn giải thích dài hơn để phần Tổng quan không bị quá ngắn.
     */
    $vitaminContextParagraphs = collect(
        match (true) {
            $isVitaminD3K2 => [
                'Nhóm sản phẩm D3/K2 thường được trình bày theo dạng dùng, hàm lượng và độ tuổi phù hợp. Khi xem sản phẩm, ba mẹ nên đối chiếu đồng thời cả hàm lượng dưỡng chất, số lần dùng và cách lấy sản phẩm để tránh nhầm giữa các quy cách khác nhau.',
                'Thông tin trên trang giúp tóm tắt các dữ liệu chính đang có trong hệ thống; liều lượng và khuyến nghị cụ thể trên nhãn sản phẩm vẫn là nguồn cần được ưu tiên khi sử dụng thực tế.',
            ],
            $isVitaminProbiotic => [
                'Với men vi sinh, thông tin về chủng lợi khuẩn, dạng sản phẩm và điều kiện bảo quản là những yếu tố quan trọng cần đọc cùng nhau. Hai sản phẩm cùng thuộc nhóm men vi sinh vẫn có thể khác nhau về chủng, số lượng dùng và cách bảo quản.',
                'Ba mẹ nên giữ thói quen kiểm tra nhãn trước mỗi đợt sử dụng, đặc biệt nếu sản phẩm đã mở nắp hoặc được bảo quản trong một khoảng thời gian dài.',
            ],
            $isVitaminMulti => [
                'Vitamin tổng hợp thường kết hợp nhiều dưỡng chất trong cùng một sản phẩm, vì vậy việc đọc bảng thành phần giúp ba mẹ biết sản phẩm đang cung cấp những nhóm vitamin và khoáng chất nào thay vì chỉ nhìn tên sản phẩm.',
                'Khi trẻ đang sử dụng thêm sản phẩm bổ sung khác, nên so sánh thành phần giữa các sản phẩm để hạn chế việc dùng trùng nhiều dưỡng chất mà không cần thiết.',
            ],
            $isVitaminDha => [
                'Với sản phẩm DHA/Omega-3, ba mẹ nên chú ý hàm lượng tính theo mỗi viên hoặc mỗi khẩu phần, dạng viên nang và hướng dẫn theo từng nhóm tuổi. Đây là những thông tin giúp sử dụng đúng quy cách hơn.',
                'Nếu sản phẩm dành cho trẻ nhỏ nhưng ở dạng viên nang, cần đọc kỹ hướng dẫn về cách dùng viên thay vì tự áp dụng cách dùng của người lớn.',
            ],
            $isVitaminIron => [
                'Sản phẩm bổ sung sắt cần được đọc kỹ về hàm lượng, dạng nhỏ giọt và lượng sử dụng theo từng nhóm tuổi hoặc cân nặng nếu nhà sản xuất có hướng dẫn. Việc đong hoặc nhỏ đúng lượng đặc biệt quan trọng với nhóm sản phẩm này.',
                'Khi trẻ đang dùng nhiều sản phẩm bổ sung, ba mẹ nên kiểm tra thành phần để tránh bổ sung sắt trùng lặp và nên tham khảo ý kiến chuyên môn nếu có nhu cầu sử dụng kéo dài.',
            ],
            $isVitaminCalcium => [
                'Sản phẩm canxi thường được xem cùng thông tin về vitamin D3, hàm lượng tính theo viên, dạng viên nang và số viên dùng theo độ tuổi. Đọc đủ các thông tin này giúp ba mẹ hiểu rõ quy cách sản phẩm hơn.',
                'Với trẻ nhỏ, cách sử dụng viên nang có thể khác người lớn; vì vậy nên tuân thủ chính xác phương pháp được nhà sản xuất ghi trên nhãn thay vì tự thay đổi cách dùng.',
            ],
            default => [
                'Mỗi sản phẩm bổ sung có công thức, dạng dùng và đối tượng sử dụng khác nhau. Ba mẹ nên xem đồng thời thành phần, quy cách, độ tuổi và hướng dẫn thay vì chỉ dựa vào tên nhóm sản phẩm.',
                'Thông tin trên trang mang tính tóm tắt để hỗ trợ tra cứu; hướng dẫn trên bao bì và khuyến nghị chuyên môn phù hợp vẫn được ưu tiên khi sử dụng thực tế.',
            ],
        }
    );

    $vitaminIngredientNotes = collect(
        match (true) {
            $isVitaminD3K2 => [
                'Đây là nhóm dưỡng chất chính được nhà sản xuất công bố cho sản phẩm; hàm lượng cụ thể nên được đọc theo mỗi liều dùng trên nhãn.',
                'Thành phần bổ sung đi cùng công thức chính và cần được xem cùng đơn vị hàm lượng để tránh hiểu nhầm giữa các sản phẩm.',
                'Chất nền giúp tạo dạng sản phẩm phù hợp như nhỏ giọt hoặc dạng xịt; thành phần cụ thể được đối chiếu trên bao bì.',
                'Phụ liệu có thể khác nhau giữa từng sản phẩm và từng thị trường, vì vậy người dùng có cơ địa nhạy cảm nên đọc đầy đủ nhãn thành phần.',
            ],
            $isVitaminProbiotic => [
                'Tên chủng lợi khuẩn là thông tin quan trọng giúp phân biệt giữa các sản phẩm men vi sinh có công thức khác nhau.',
                'Thành phần nền giúp tạo dạng nhỏ giọt hoặc viên; cần xem cùng hướng dẫn bảo quản vì một số công thức nhạy với nhiệt độ.',
                'Các phụ liệu đi kèm cần được đọc kỹ khi trẻ có tiền sử dị ứng hoặc nhạy cảm với một số thành phần.',
                'Bao bì sản phẩm là nơi cung cấp đầy đủ hơn về hàm lượng, chủng, số lượng sử dụng và các thành phần đi kèm.',
            ],
            $isVitaminMulti => [
                'Nhóm vitamin chính cần được đọc cùng hàm lượng của từng vitamin thay vì chỉ dựa vào tổng số dưỡng chất trong công thức.',
                'Khoáng chất bổ sung có thể có đơn vị đo khác vitamin, vì vậy nên đối chiếu trực tiếp bảng thành phần trên nhãn.',
                'Thành phần nền giúp tạo dạng lỏng hoặc siro; với sản phẩm dạng lỏng cần dùng đúng dụng cụ đo để chia lượng chính xác hơn.',
                'Danh sách thành phần chi tiết trên bao bì giúp kiểm tra các chất phụ, hương liệu hoặc thành phần mà trẻ có thể nhạy cảm.',
            ],
            $isVitaminDha => [
                'DHA là dưỡng chất chính của nhóm sản phẩm này; hàm lượng nên được xem theo mỗi viên hoặc mỗi khẩu phần sử dụng.',
                'Omega-3 là nhóm chất béo có thể bao gồm nhiều thành phần khác nhau; cần đọc bảng công bố để biết sản phẩm ghi cụ thể những gì.',
                'Vỏ viên nang và chất nền là một phần của dạng viên mềm; người dùng có tiền sử dị ứng nên kiểm tra nguồn nguyên liệu được công bố.',
                'Thông tin đầy đủ trên nhãn giúp xác định hàm lượng, quy cách viên và hướng dẫn theo từng nhóm tuổi.',
            ],
            $isVitaminIron => [
                'Sắt là thành phần chính cần được quan tâm về hàm lượng và đơn vị đo trong mỗi lượng dùng.',
                'Thành phần nền tạo dạng nhỏ giọt và ảnh hưởng đến cách đong hoặc nhỏ sản phẩm khi sử dụng.',
                'Phụ liệu cần được kiểm tra nếu trẻ có cơ địa nhạy cảm hoặc đang dùng nhiều sản phẩm bổ sung.',
                'Thông tin trên bao bì giúp xác định lượng sử dụng theo tuổi hoặc cân nặng nếu nhà sản xuất có hướng dẫn.',
            ],
            $isVitaminCalcium => [
                'Canxi là dưỡng chất chính; hàm lượng nên được đọc theo mỗi viên hoặc mỗi khẩu phần thay vì chỉ nhìn tổng số viên trong hộp.',
                'Vitamin D3 là thành phần đi kèm được công bố trong công thức; nên xem cùng hàm lượng cụ thể trên nhãn sản phẩm.',
                'Thành phần nền của viên nang giúp tạo dạng sử dụng; người dùng cần kiểm tra nếu có tiền sử dị ứng với thành phần của vỏ viên.',
                'Danh sách thành phần chi tiết giúp ba mẹ đối chiếu thêm các chất bổ sung khác trước khi dùng song song nhiều sản phẩm.',
            ],
            default => [
                'Đây là thành phần được xếp vào nhóm chính của sản phẩm và cần được xem cùng hàm lượng công bố.',
                'Thành phần bổ sung giúp hoàn thiện công thức; thông tin cụ thể cần đối chiếu trên nhãn.',
                'Thành phần nền phụ thuộc dạng sản phẩm như lỏng, siro, viên hoặc nhỏ giọt.',
                'Bao bì sản phẩm cung cấp danh sách thành phần đầy đủ hơn để người dùng kiểm tra trước khi sử dụng.',
            ],
        }
    );

    $vitaminUsageNotes = collect(
        match (true) {
            $isVitaminCalcium => [
                'Trước khi dùng, kiểm tra hạn sử dụng, tình trạng viên và đối chiếu đúng nhóm tuổi của trẻ.',
                'Xác định đúng số viên theo hướng dẫn; với trẻ nhỏ cần làm đúng phương pháp xử lý viên nang được nhà sản xuất nêu.',
                'Duy trì lượng dùng theo hướng dẫn thay vì tự tăng số viên khi bỏ quên một lần sử dụng trước đó.',
                'Sau khi dùng, đóng kín hộp và cất lại đúng điều kiện để hạn chế ẩm, nóng và ánh nắng ảnh hưởng đến sản phẩm.',
            ],
            $isVitaminDha => [
                'Kiểm tra bao bì, hạn sử dụng và tình trạng viên trước khi lấy sản phẩm cho trẻ.',
                'Đối chiếu đúng nhóm tuổi để xác định số viên hoặc cách dùng phù hợp.',
                'Sử dụng đúng cách được hướng dẫn cho dạng viên nang, đặc biệt với trẻ chưa thể tự nhai hoặc nuốt.',
                'Đóng kín hộp sau khi dùng và tránh để viên trong môi trường nhiệt độ cao trong thời gian dài.',
            ],
            $isVitaminMulti => [
                'Nếu nhãn yêu cầu lắc chai, nên lắc đều để thành phần phân bố đồng đều trước khi lấy sản phẩm.',
                'Dùng đúng cốc, thìa hoặc dụng cụ đo đi kèm để hạn chế lấy sai lượng trong mỗi lần sử dụng.',
                'Cho trẻ dùng đúng lượng theo nhóm tuổi và không tự tăng lượng chỉ vì một lần trước đó đã quên sử dụng.',
                'Đậy kín nắp, vệ sinh dụng cụ đo và bảo quản theo điều kiện được in trên bao bì.',
            ],
            default => [
                'Đọc kỹ nhãn để kiểm tra đúng sản phẩm, hạn sử dụng và nhóm tuổi trước khi lấy lượng dùng.',
                'Chuẩn bị sản phẩm đúng với dạng sử dụng như nhỏ giọt, xịt, siro hoặc viên nang để tránh thao tác sai.',
                'Duy trì lượng dùng theo đúng hướng dẫn trên nhãn; không tự tăng lượng hoặc tăng số lần dùng.',
                'Đóng kín sản phẩm sau khi sử dụng và bảo quản theo điều kiện nhà sản xuất khuyến nghị.',
            ],
        }
    );

@endphp

<section class="product-long-content product-vitamin-detail">

    {{-- =========================================================
         OVERVIEW
    ========================================================== --}}
    <article class="product-vitamin-overview">

        <div class="product-vitamin-overview-grid">

            {{-- SPEC --}}
            <div class="product-vitamin-overview-spec">

                <div class="product-vitamin-section-heading">
                    <span class="product-vitamin-heading-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24">
                            <path d="M9 3h6"></path>
                            <path d="M10 3v3l-2 2v11h8V8l-2-2V3"></path>
                            <path d="M9 12h6"></path>
                        </svg>
                    </span>

                    <div>
                        <span>Thông tin sản phẩm</span>
                        <h2>Chi tiết Vitamin &amp; sức khỏe</h2>
                    </div>
                </div>

                <div class="product-spec-table product-vitamin-spec-table">

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
                        <strong>Nhóm sản phẩm</strong>
                        <span>{{ $vitaminTypeLabel }}</span>
                    </div>

                    <div class="product-spec-row">
                        <strong>Dưỡng chất chính</strong>
                        <span>{{ $vitaminPrimaryNutrient }}</span>
                    </div>

                    @if ($vitaminForm)
                        <div class="product-spec-row">
                            <strong>Dạng sản phẩm</strong>
                            <span>{{ $vitaminForm }}</span>
                        </div>
                    @endif

                    @if ($vitaminPack)
                        <div class="product-spec-row">
                            <strong>Quy cách</strong>
                            <span>{{ $vitaminPack }}</span>
                        </div>
                    @endif

                    @if ($vitaminAgeText)
                        <div class="product-spec-row">
                            <strong>Độ tuổi</strong>
                            <span>{{ $vitaminAgeText }}</span>
                        </div>
                    @endif

                    @if ($product->origin)
                        <div class="product-spec-row">
                            <strong>Xuất xứ</strong>
                            <span>{{ $product->origin }}</span>
                        </div>
                    @endif

                    @if ($product->manufacturer)
                        <div class="product-spec-row">
                            <strong>Nhà sản xuất</strong>
                            <span>{{ $product->manufacturer }}</span>
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

            {{-- DESCRIPTION --}}
            <div class="product-vitamin-overview-description">

                <div class="product-vitamin-section-heading">
                    <span class="product-vitamin-heading-icon" aria-hidden="true">
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

                <div class="product-vitamin-description-text">
                    {{ $product->description ?: 'Thông tin mô tả sản phẩm đang được cập nhật.' }}
                </div>

                <div class="product-vitamin-context-copy">
                    @foreach ($vitaminContextParagraphs as $paragraph)
                        <p>{{ $paragraph }}</p>
                    @endforeach
                </div>

                <div class="product-vitamin-benefit-grid">
                    @foreach ($vitaminBenefits as $benefit)
                        <div class="product-vitamin-benefit-item">

                            <span class="product-vitamin-benefit-icon" aria-hidden="true">
                                @if ($benefit['icon'] === 'drop')
                                    <svg viewBox="0 0 24 24">
                                        <path d="M12 3s5 5.5 5 9a5 5 0 1 1-10 0c0-3.5 5-9 5-9Z"></path>
                                    </svg>
                                @elseif ($benefit['icon'] === 'microbe')
                                    <svg viewBox="0 0 24 24">
                                        <circle cx="12" cy="12" r="5"></circle>
                                        <path d="M12 3v4M12 17v4M3 12h4M17 12h4M5.5 5.5l3 3M15.5 15.5l3 3M18.5 5.5l-3 3M8.5 15.5l-3 3"></path>
                                    </svg>
                                @elseif ($benefit['icon'] === 'capsule')
                                    <svg viewBox="0 0 24 24">
                                        <path d="M8 4a4 4 0 0 1 5.7 0l6.3 6.3a4 4 0 0 1-5.7 5.7L8 9.7A4 4 0 0 1 8 4Z"></path>
                                        <path d="m10 12 5-5"></path>
                                    </svg>
                                @elseif ($benefit['icon'] === 'user')
                                    <svg viewBox="0 0 24 24">
                                        <circle cx="12" cy="8" r="3"></circle>
                                        <path d="M6 20c.8-4 3-6 6-6s5.2 2 6 6"></path>
                                    </svg>
                                @elseif ($benefit['icon'] === 'box')
                                    <svg viewBox="0 0 24 24">
                                        <path d="m4 7 8-4 8 4-8 4-8-4Z"></path>
                                        <path d="M4 7v10l8 4 8-4V7"></path>
                                        <path d="M12 11v10"></path>
                                    </svg>
                                @elseif ($benefit['icon'] === 'shield')
                                    <svg viewBox="0 0 24 24">
                                        <path d="M12 3 5 6v5c0 4.7 2.8 8.3 7 10 4.2-1.7 7-5.3 7-10V6l-7-3Z"></path>
                                        <path d="m9 12 2 2 4-4"></path>
                                    </svg>
                                @else
                                    <svg viewBox="0 0 24 24">
                                        <circle cx="12" cy="12" r="9"></circle>
                                        <path d="m8 12 2.5 2.5L16 9"></path>
                                    </svg>
                                @endif
                            </span>

                            <div>
                                <strong>{{ $benefit['title'] }}</strong>
                                <span>{{ $benefit['subtitle'] }}</span>
                            </div>

                        </div>
                    @endforeach
                </div>

                <div class="product-vitamin-trust-banner">
                    <span class="product-vitamin-trust-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24">
                            <path d="M12 3 5 6v5c0 4.7 2.8 8.3 7 10 4.2-1.7 7-5.3 7-10V6l-7-3Z"></path>
                            <path d="m9 12 2 2 4-4"></path>
                        </svg>
                    </span>

                    <div>
                        <strong>{{ $vitaminHighlightMessage }}</strong>
                        <span>{{ $vitaminHighlightSubmessage }}</span>
                    </div>
                </div>

            </div>

        </div>

    </article>

    {{-- =========================================================
         THÀNH PHẦN / DƯỠNG CHẤT
    ========================================================== --}}
    <article class="product-vitamin-section-card">

        <div class="product-vitamin-card-heading">

            <div class="product-vitamin-section-heading">
                <span class="product-vitamin-heading-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24">
                        <path d="M12 3v18"></path>
                        <path d="M7 8c0-2 2-4 5-4"></path>
                        <path d="M17 8c0-2-2-4-5-4"></path>
                        <path d="M7 14c0 2 2 4 5 4"></path>
                        <path d="M17 14c0 2-2 4-5 4"></path>
                    </svg>
                </span>

                <div>
                    <span>Thành phần</span>
                    <h2>Dưỡng chất &amp; thành phần chính</h2>
                </div>
            </div>

            <p>Đọc theo từng nhóm để hiểu rõ dưỡng chất chính, thành phần đi kèm và những thông tin cần đối chiếu trên nhãn trước khi sử dụng.</p>

        </div>

        <div class="product-vitamin-ingredient-grid">

            @foreach ($vitaminIngredientLines as $ingredient)
                <div class="product-vitamin-ingredient-item">

                    <span class="product-vitamin-ingredient-number">
                        {{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}
                    </span>

                    <span class="product-vitamin-ingredient-icon" aria-hidden="true">
                        @if ($loop->iteration === 1)
                            <svg viewBox="0 0 24 24">
                                <path d="M12 3s5 5.5 5 9a5 5 0 1 1-10 0c0-3.5 5-9 5-9Z"></path>
                            </svg>
                        @elseif ($loop->iteration === 2)
                            <svg viewBox="0 0 24 24">
                                <circle cx="12" cy="12" r="8"></circle>
                                <path d="M8 12h8M12 8v8"></path>
                            </svg>
                        @elseif ($loop->iteration === 3)
                            <svg viewBox="0 0 24 24">
                                <path d="m4 7 8-4 8 4-8 4-8-4Z"></path>
                                <path d="M4 7v10l8 4 8-4V7"></path>
                            </svg>
                        @else
                            <svg viewBox="0 0 24 24">
                                <path d="M5 4h14v16H5z"></path>
                                <path d="M8 8h8M8 12h8M8 16h5"></path>
                            </svg>
                        @endif
                    </span>

                    <div>
                        <strong>
                            @if ($loop->iteration === 1)
                                Dưỡng chất chính
                            @elseif ($loop->iteration === 2)
                                Thành phần bổ sung
                            @elseif ($loop->iteration === 3)
                                Thành phần nền
                            @else
                                Thông tin thành phần
                            @endif
                        </strong>

                        <span>{{ $ingredient }}</span>
                        <p class="product-vitamin-ingredient-note">
                            {{ $vitaminIngredientNotes->get($loop->index) }}
                        </p>
                    </div>

                </div>
            @endforeach

        </div>

    </article>

    {{-- =========================================================
         HƯỚNG DẪN SỬ DỤNG
    ========================================================== --}}
    <article class="product-vitamin-section-card product-vitamin-guide-card">

        <div class="product-vitamin-card-heading">

            <div class="product-vitamin-section-heading">
                <span class="product-vitamin-heading-icon" aria-hidden="true">
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

            <p>Thực hiện lần lượt từng bước, dùng đúng lượng theo độ tuổi và luôn ưu tiên hướng dẫn cụ thể được in trên nhãn của chính sản phẩm.</p>

        </div>

        <div class="product-vitamin-steps">

            @foreach ($vitaminUsageSlots as $step)

                <div class="product-vitamin-step">

                    <div class="product-vitamin-step-head">
                        <span class="product-vitamin-step-number">
                            {{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}
                        </span>

                        <span class="product-vitamin-step-icon" aria-hidden="true">
                            @if ($loop->iteration === 1)
                                <svg viewBox="0 0 24 24">
                                    <path d="M5 4h14v16H5z"></path>
                                    <path d="M8 8h8M8 12h8M8 16h5"></path>
                                </svg>
                            @elseif ($loop->iteration === 2)
                                <svg viewBox="0 0 24 24">
                                    <path d="M9 3h6"></path>
                                    <path d="M10 3v3l-2 2v11h8V8l-2-2V3"></path>
                                </svg>
                            @elseif ($loop->iteration === 3)
                                <svg viewBox="0 0 24 24">
                                    <circle cx="12" cy="12" r="8"></circle>
                                    <path d="M12 8v4l3 2"></path>
                                </svg>
                            @else
                                <svg viewBox="0 0 24 24">
                                    <path d="M12 3 5 6v5c0 4.7 2.8 8.3 7 10 4.2-1.7 7-5.3 7-10V6l-7-3Z"></path>
                                    <path d="m9 12 2 2 4-4"></path>
                                </svg>
                            @endif
                        </span>
                    </div>

                    <strong>{{ $vitaminStepTitles->get($loop->index) }}</strong>
                    <p>{{ $step }}</p>
                    <p class="product-vitamin-step-note">
                        {{ $vitaminUsageNotes->get($loop->index) }}
                    </p>

                </div>

            @endforeach

        </div>

    </article>


    {{-- =========================================================
         BẢO QUẢN / LƯU Ý / ĐỐI TƯỢNG
    ========================================================== --}}
    <div class="product-vitamin-info-grid">

        <article class="product-vitamin-info-card">

            <div class="product-vitamin-info-title">
                <span class="product-vitamin-info-icon" aria-hidden="true">
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
                @foreach ($vitaminStorageList as $item)
                    <li>{{ $item }}</li>
                @endforeach
            </ul>

        </article>

        <article class="product-vitamin-info-card is-warning">

            <div class="product-vitamin-info-title">
                <span class="product-vitamin-info-icon" aria-hidden="true">
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
                @foreach ($vitaminWarningList as $item)
                    <li>{{ $item }}</li>
                @endforeach
            </ul>

        </article>

        <article class="product-vitamin-info-card is-audience">

            <div class="product-vitamin-info-title">
                <span class="product-vitamin-info-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24">
                        <circle cx="12" cy="8" r="3"></circle>
                        <path d="M6 20c.8-4 3-6 6-6s5.2 2 6 6"></path>
                    </svg>
                </span>

                <div>
                    <span>Đối tượng</span>
                    <h3>{{ $vitaminAgeText ?: 'Theo hướng dẫn sản phẩm' }}</h3>
                </div>
            </div>

            <ul>
                @foreach ($vitaminAudienceItems as $item)
                    <li>{{ $item }}</li>
                @endforeach
            </ul>

        </article>

    </div>

</section>
