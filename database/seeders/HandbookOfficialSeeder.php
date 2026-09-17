<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\HandbookCategory;
use App\Models\Article;
use Illuminate\Support\Str;

class HandbookOfficialSeeder extends Seeder
{
    public function run(): void
    {
        $contents = [
            // PHẦN 01
            'Hành trang chuẩn bị làm mẹ' => [
                'summary' => 'Hướng dẫn chuẩn bị sức khỏe, tâm lý, tài chính và dinh dưỡng trước khi thụ thai.',
                'content' => '
                    <img src="https://images.unsplash.com/photo-1584515979956-d9f6e5d09982?auto=format&fit=crop&w=800&q=80" alt="Chuẩn bị sức khỏe trước khi mang thai" class="img-fluid rounded mb-3 shadow-sm">
                    <h3>1. Chuẩn bị về mặt Sức khỏe & Y tế</h3>
                    <p>Trước khi mang thai từ 3 - 6 tháng, cả hai vợ chồng nên thực hiện khám sức khỏe sinh sản tổng quát để phát hiện và xử lý kịp thời các nguy cơ tiềm ẩn.</p>
                    <ul>
                        <li><strong>Tiêm phòng vắc-xin:</strong> Tiêm phòng Cúm, Rubella, Thủy đậu và Viêm gan B ít nhất 1-3 tháng trước khi mang thai.</li>
                        <li><strong>Bổ sung vi chất:</strong> Uống Axit Folic (400 mcg/ngày) trước khi mang thai ít nhất 1 tháng để phòng ngừa dị tật ống thần kinh cho thai nhi.</li>
                    </ul>
                    <div class="alert alert-info"><strong>Lời khuyên y khoa:</strong> Hạn chế tối đa rượu bia, thuốc lá và tiếp xúc với hóa chất độc hại trong giai đoạn chuẩn bị.</div>
                '
            ],
            'Sự hình thành của thai nhi và các dấu hiệu nhận biết.' => [
                'summary' => 'Quá trình thụ tinh, sự phát triển thai nhi theo từng tuần và nhận biết các dấu hiệu mang thai sớm.',
                'content' => '
                    <img src="https://images.unsplash.com/photo-1516627145497-ae6968895b74?auto=format&fit=crop&w=800&q=80" alt="Sự phát triển của thai nhi" class="img-fluid rounded mb-3 shadow-sm">
                    <h3>1. Dấu hiệu nhận biết mang thai sớm</h3>
                    <p>Các dấu hiệu thường xuất hiện từ tuần thứ 2 đến tuần thứ 6 sau khi thụ tinh thành công:</p>
                    <ul>
                        <li>Trễ kinh (Chậm kinh).</li>
                        <li>Căng tức vú, nhạy cảm với mùi vị.</li>
                        <li>Mệt mỏi, buồn nôn, nôn mửa (Ốm nghén).</li>
                        <li>Thử que thử thai hiện 2 vạch rõ ràng.</li>
                    </ul>
                    <h3>2. Sự phát triển thần kỳ của thai nhi</h3>
                    <img src="https://images.unsplash.com/photo-1537673156864-5d2c72de7824?auto=format&fit=crop&w=800&q=80" alt="Phát triển thai nhi" class="img-fluid rounded mb-3 shadow-sm">
                    <p>Thai kỳ trải qua 3 tam cá nguyệt (3 tháng đầu, 3 tháng giữa, 3 tháng cuối). Trong 3 tháng đầu, các cơ quan quan trọng như tim, não, tay chân bắt đầu hình thành và hoàn thiện sơ khai.</p>
                '
            ],
            'Khám thai tại cơ sở y tế' => [
                'summary' => 'Lịch khám thai định kỳ chuẩn Bộ Y tế dành cho phụ nữ mang thai.',
                'content' => '
                    <img src="https://images.unsplash.com/photo-1666214280557-f1b5022eb634?auto=format&fit=crop&w=800&q=80" alt="Khám thai định kỳ" class="img-fluid rounded mb-3 shadow-sm">
                    <h3>Mốc lịch khám thai quan trọng</h3>
                    <div class="table-responsive mb-3">
                        <table class="table table-bordered align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th scope="col" style="width: 30%;">Thời điểm</th>
                                    <th scope="col" style="width: 70%;">Mục đích kiểm tra</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><strong>Tuần 5 - 8</strong></td>
                                    <td>Xác định vị trí thai (trong tử cung), sự sống của phôi thai và tim thai.</td>
                                </tr>
                                <tr>
                                    <td><strong>Tuần 11 - 13 tuần 6 ngày</strong></td>
                                    <td>Đo độ mờ da gáy, sàng lọc dị tật bẩm sinh (Double Test, NIPT).</td>
                                </tr>
                                <tr>
                                    <td><strong>Tuần 18 - 22</strong></td>
                                    <td>Siêu âm hình thái học phát hiện các dị tật về cấu trúc cơ quan.</td>
                                </tr>
                                <tr>
                                    <td><strong>Tuần 24 - 28</strong></td>
                                    <td>Xét nghiệm dung nạp Glucose sàng lọc Đái tháo đường thai kỳ.</td>
                                </tr>
                                <tr>
                                    <td><strong>Tuần 32 - 36</strong></td>
                                    <td>Đánh giá sự phát triển thai nhi, vị trí ngôi thai và lượng nước ối.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                '
            ],
            'Cách sử dụng Sổ theo dõi sức khỏe bà mẹ và trẻ em' => [
                'summary' => 'Hướng dẫn mẹ ghi chép chỉ số thai kỳ, lịch tiêm chủng và biểu đồ phát triển của con.',
                'content' => '
                    <img src="https://images.unsplash.com/photo-1544716278-ca5e3f4abd8c?auto=format&fit=crop&w=800&q=80" alt="Theo dõi sổ sức khỏe mẹ và bé" class="img-fluid rounded mb-3 shadow-sm">
                    <h3>1. Ý nghĩa của Sổ theo dõi Sức khỏe</h3>
                    <p>Sổ theo dõi Sức khỏe Bà mẹ và Trẻ em là công cụ ghi nhận toàn bộ quá trình từ lúc mang thai đến khi trẻ tròn 6 tuổi, do Bộ Y tế ban hành.</p>
                    <h3>2. Hướng dẫn sử dụng hiệu quả</h3>
                    <ul>
                        <li>Ghi chép kết quả các lần khám thai, huyết áp, cân nặng của mẹ.</li>
                        <li>Theo dõi biểu đồ tăng trưởng cân nặng, chiều cao của bé để phát hiện sớm suy dinh dưỡng hoặc béo phì.</li>
                        <li>Lưu trữ nhật ký tiêm chủng vắc-xin cho bé.</li>
                    </ul>
                '
            ],
            'Siêu âm và các xét nghiệm sàng lọc trước sinh' => [
                'summary' => 'Các mốc siêu âm màu, xét nghiệm NIPT, Double test, Triple test giúp phát hiện dị tật sớm.',
                'content' => '
                    <img src="https://images.unsplash.com/photo-1579684385127-1ef15d508118?auto=format&fit=crop&w=800&q=80" alt="Siêu âm sàng lọc trước sinh" class="img-fluid rounded mb-3 shadow-sm">
                    <h3>1. Các xét nghiệm sàng lọc dị tật bẩm sinh</h3>
                    <ul>
                        <li><strong>Double Test (Tuần 11-13):</strong> Kết hợp siêu âm đo độ mờ da gáy sàng lọc hội chứng Down, Patau, Edwards.</li>
                        <li><strong>NIPT (Từ tuần thứ 9):</strong> Xét nghiệm ADN tự do của thai nhi trong máu mẹ, độ chính xác đến 99%.</li>
                        <li><strong>Triple Test (Tuần 15-20):</strong> Sàng lọc dị tật ống thần kinh và các bất thường nhiễm sắc thể.</li>
                    </ul>
                '
            ],

            // PHẦN 02
            'Dinh dưỡng cho bà bầu' => [
                'summary' => 'Thực đơn cân bằng dinh dưỡng, bổ sung Sắt, Canxi, DHA cho mẹ trong từng giai đoạn thai kỳ.',
                'content' => '
                    <img src="https://images.unsplash.com/photo-1498837167922-ddd27525d352?auto=format&fit=crop&w=800&q=80" alt="Chế độ dinh dưỡng lành mạnh cho bà bầu" class="img-fluid rounded mb-3 shadow-sm">
                    <h3>1. Nguyên tắc dinh dưỡng "Đúng & Đủ"</h3>
                    <p>Mẹ bầu không cần "ăn cho 2 người" về số lượng, mà cần chú trọng chất lượng dinh dưỡng:</p>
                    <ul>
                        <li><strong>Sắt:</strong> Uống bổ sung từ khi phát hiện mang thai đến sau sinh 1 tháng (30 - 60mg Sắt nguyên tố/ngày).</li>
                        <li><strong>Canxi:</strong> Nhu cầu tăng dần từ 800mg (3 tháng đầu) lên 1200mg-1500mg/ngày (3 tháng cuối).</li>
                        <li><strong>DHA & Omega-3:</strong> Giúp hoàn thiện cấu trúc não bộ và thị giác của thai nhi.</li>
                    </ul>
                '
            ],
            'Sử dụng gia vị hợp lý cho bà mẹ mang thai' => [
                'summary' => 'Khuyến cáo giảm muối, sử dụng i-ốt và lưu ý các loại gia vị nồng trong chế biến món ăn.',
                'content' => '
                    <img src="https://images.unsplash.com/photo-1596040033229-a9821ebd058d?auto=format&fit=crop&w=800&q=80" alt="Sử dụng gia vị hợp lý" class="img-fluid rounded mb-3 shadow-sm">
                    <h3>1. Sử dụng Muối và I-ốt đúng cách</h3>
                    <ul>
                        <li>Giảm ăn mặn (dưới 5g muối/ngày) để phòng ngừa nguy cơ Tăng huyết áp thai kỳ và Tiền giật.</li>
                        <li>Sử dụng muối I-ốt trong nấu ăn hàng ngày để phòng ngừa bướu cổ và đần độn ở trẻ.</li>
                    </ul>
                    <h3>2. Hạn chế các gia vị kích thích</h3>
                    <p>Hạn chế ớt quá cay, tiêu, mù tạt vì dễ gây kích ứng dạ dày và làm trầm trọng thêm tình trạng táo bón thai kỳ.</p>
                '
            ],
            'Tham gia lớp tiền sản dành cho bà bầu' => [
                'summary' => 'Lợi ích khi tham gia lớp tiền sản: Học kỹ năng thở, rặn đẻ và chăm sóc trẻ sơ sinh.',
                'content' => '
                    <img src="https://images.unsplash.com/photo-1531983412531-1f49a365ffed?auto=format&fit=crop&w=800&q=80" alt="Lớp học tiền sản" class="img-fluid rounded mb-3 shadow-sm">
                    <h3>Lợi ích của Lớp học Tiền sản</h3>
                    <ul>
                        <li>Được bác sĩ chuyên khoa phụ sản trực tiếp tư vấn giải đáp thắc mắc.</li>
                        <li>Thực hành phương pháp hít thở và rặn đẻ giúp cuộc chuyển dạ diễn ra nhẹ nhàng, giảm đau.</li>
                        <li>Thực hành tắm bé, vệ sinh rốn và cách bế bé chuẩn kỹ thuật.</li>
                    </ul>
                '
            ],
            'Chế độ luyện tập của mẹ cho thai kỳ khỏe mạnh' => [
                'summary' => 'Các bài tập Yoga, đi bộ nhẹ nhàng giúp giảm đau lưng, dễ sinh và phục hồi nhanh.',
                'content' => '
                    <img src="https://images.unsplash.com/photo-1518611012118-696072aa579a?auto=format&fit=crop&w=800&q=80" alt="Yoga thai kỳ" class="img-fluid rounded mb-3 shadow-sm">
                    <h3>1. Các hình thức vận động phù hợp</h3>
                    <ul>
                        <li><strong>Đi bộ nhẹ nhàng:</strong> 30 phút mỗi ngày giúp lưu thông máu tốt.</li>
                        <li><strong>Yoga thai kỳ:</strong> Tăng độ dẻo dai cơ chậu, giảm đau lưng và giải tỏa căng thẳng.</li>
                        <li><strong>Bài tập Kegel:</strong> Rèn luyện sức bền cơ sàn chậu, hỗ trợ tốt cho quá trình sinh thường.</li>
                    </ul>
                '
            ],

            // PHẦN 03
            'Những vấn đề sức khỏe thường gặp' => [
                'summary' => 'Xử lý tình trạng ốm nghén, táo bón, trĩ, chuột rút và phù chân khi mang thai.',
                'content' => '
                    <img src="https://images.unsplash.com/photo-1512438248247-f0f2a5a8b7f0?auto=format&fit=crop&w=800&q=80" alt="Chăm sóc sức khỏe thai kỳ" class="img-fluid rounded mb-3 shadow-sm">
                    <h3>1. Mẹo giảm ốm nghén</h3>
                    <p>Chia nhỏ bữa ăn (5-6 bữa/ngày), tránh đồ ăn nhiều dầu mỡ. Uống trà gừng ấm hoặc ngậm kẹo gừng.</p>
                    <h3>2. Phòng chống Táo bón & Trĩ</h3>
                    <p>Uống đủ 2 - 2.5 lít nước/ngày, bổ sung nhiều rau xanh, trái cây giàu xơ như đu đủ chín, khoai lang, chuối.</p>
                '
            ],
            'Những dấu hiệu nguy hiểm trong thời kỳ mang thai' => [
                'summary' => 'Các dấu hiệu cảnh báo cần đến cấp cứu ngay: Ra máu âm đạo, đau bụng dữ dội, thai máy yếu.',
                'content' => '
                    <img src="https://images.unsplash.com/photo-1505751172876-fa1923c5c528?auto=format&fit=crop&w=800&q=80" alt="Cảnh báo sức khỏe thai kỳ" class="img-fluid rounded mb-3 shadow-sm">
                    <div class="alert alert-danger">
                        <h4>⚠️ ĐẾN CƠ SỞ Y TẾ NGAY KHI CÓ CÁC DẤU HIỆU SAU:</h4>
                        <ul>
                            <li>Ra máu bất thường ở âm đạo (dù ít hay nhiều).</li>
                            <li>Đau bụng dưới dữ dội hoặc co thắt tử cung liên tục.</li>
                            <li>Sốt cao trên 38.5°C không hạ.</li>
                            <li>Sưng phù đột ngột ở mặt, tay và chân kèm đau đầu, hoa mắt (Dấu hiệu Tiền giật).</li>
                            <li>Thai máy giảm đột ngột hoặc không thấy thai máy sau tuần thứ 20.</li>
                        </ul>
                    </div>
                '
            ],
            'Các bệnh lý nguy hiểm có thể xảy ra khi mang thai' => [
                'summary' => 'Tìm hiểu về Tiền giật, Đái tháo đường thai kỳ, Mẹ bị thiếu máu nặng.',
                'content' => '
                    <img src="https://images.unsplash.com/photo-1581594693702-f26b3e59311b?auto=format&fit=crop&w=800&q=80" alt="Theo dõi bệnh lý thai kỳ" class="img-fluid rounded mb-3 shadow-sm">
                    <h3>1. Đái tháo đường thai kỳ</h3>
                    <p>Thường xảy ra ở 3 tháng giữa và 3 tháng cuối. Cần kiểm soát chế độ ăn giảm tinh bột nhanh, tập luyện nhẹ nhàng hoặc dùng Insulin theo chỉ định của bác sĩ.</p>
                    <h3>2. Tiền giật</h3>
                    <p>Biểu hiện qua Huyết áp cao, Đạm niệu trong nước tiểu. Bệnh có thể gây biến chứng nguy hiểm cho cả mẹ và thai nhi nếu không theo dõi sát sao.</p>
                '
            ],
            'Các bệnh truyền nhiễm với bà bầu' => [
                'summary' => 'Phòng tránh và xử lý khi mắc Cúm, Rubella, Viêm gan B, Thủy đậu trong thai kỳ.',
                'content' => '
                    <img src="https://images.unsplash.com/photo-1632833239869-a37e3a5806d2?auto=format&fit=crop&w=800&q=80" alt="Phòng ngừa bệnh truyền nhiễm" class="img-fluid rounded mb-3 shadow-sm">
                    <h3>1. Bệnh Rubella</h3>
                    <p>Nếu mẹ mắc Rubella trong 3 tháng đầu thai kỳ, nguy cơ thai nhi bị hội chứng Rubella bẩm sinh (điếc, dị tật tim, đục thủy tinh thể) lên đến 90%.</p>
                    <h3>2. Viêm gan B</h3>
                    <p>Cần xét nghiệm HBsAg. Nếu mẹ dương tính, bé cần được tiêm Vắc-xin và Huyết thanh chống viêm gan B trong vòng 12 giờ đầu sau sinh.</p>
                '
            ],

            // PHẦN 04
            'Các phương pháp sinh con' => [
                'summary' => 'So sánh sinh thường (sinh qua đường âm đạo) và sinh mổ (mổ lấy thai) theo chỉ định y khoa.',
                'content' => '
                    <img src="https://images.unsplash.com/photo-1555252333-9f8e92e65df9?auto=format&fit=crop&w=800&q=80" alt="Phương pháp sinh con" class="img-fluid rounded mb-3 shadow-sm">
                    <h3>1. Sinh thường (Sinh qua đường âm đạo)</h3>
                    <p>Là phương pháp tự nhiên tốt nhất. Mẹ hồi phục nhanh, em bé khi đi qua đường sinh tự nhiên được ép xuất dịch phổi tốt hơn.</p>
                    <h3>2. Sinh mổ (Mổ lấy thai)</h3>
                    <p>Được chỉ định khi có bất thường: Thai to, ngôi thai ngược, suy thai cấp, rau tiền đạo, hoặc mẹ có vết mổ cũ chưa đủ thời gian.</p>
                '
            ],
            'Dấu hiệu nhận biết chuyển dạ' => [
                'summary' => 'Nhận biết các đợt co thắt tử cung, rỉ ối, ra nhầy hồng để chủ động nhập viện.',
                'content' => '
                    <img src="https://images.unsplash.com/photo-1584515979956-d9f6e5d09982?auto=format&fit=crop&w=800&q=80" alt="Dấu hiệu chuyển dạ" class="img-fluid rounded mb-3 shadow-sm">
                    <h3>3 Dấu hiệu chuyển dạ chính xác</h3>
                    <ol>
                        <li><strong>Cơn co tử cung dồn dập:</strong> Cơn co xuất hiện đều đặn 10-15 phút/lần, ngày càng mạnh và khoảng cách ngày càng ngắn lại.</li>
                        <li><strong>Bong nút nhầy âm đạo:</strong> Xuất hiện dịch nhầy màu hồng hoặc hơi nâu ở âm đạo.</li>
                        <li><strong>Vỡ ối / Rỉ ối:</strong> Cảm giác có nước chảy ra từ âm đạo liên tục. Cần đến viện ngay lập tức.</li>
                    </ol>
                '
            ],
            'Sự thay đổi của bà mẹ thời kỳ hậu sản' => [
                'summary' => 'Chăm sóc cơ thể mẹ sau sinh: Co hồi tử cung, sản dịch và trầm cảm sau sinh.',
                'content' => '
                    <img src="https://images.unsplash.com/photo-1519689680058-324335c77eba?auto=format&fit=crop&w=800&q=80" alt="Chăm sóc sức khỏe hậu sản" class="img-fluid rounded mb-3 shadow-sm">
                    <h3>1. Theo dõi Sản dịch</h3>
                    <p>Sản dịch sẽ giảm dần từ màu đỏ tươi sang hồng nhạt và hết hoàn toàn sau 2-4 tuần. Nếu sản dịch có mùi hôi hoặc ra máu tươi trở lại cần đi khám ngay.</p>
                    <h3>2. Chăm sóc Sức khỏe Tâm thần</h3>
                    <p>Gia đình cần lắng nghe, chia sẻ để nâng đỡ tâm lý, giúp mẹ tránh hội chứng Trầm cảm sau sinh.</p>
                '
            ],
            'Massage vú cho bà mẹ sau sinh' => [
                'summary' => 'Kỹ thuật massage vú đúng cách giúp kích thích sữa về nhanh và phòng ngừa tắc tia sữa.',
                'content' => '
                    <img src="https://images.unsplash.com/photo-1544126592-807ade215a0b?auto=format&fit=crop&w=800&q=80" alt="Massage vú kích sữa" class="img-fluid rounded mb-3 shadow-sm">
                    <h3>Các bước Massage kích sữa tại nhà</h3>
                    <ul>
                        <li>Chườm ấm ngực bằng khăn sạch trong 3-5 phút trước khi massage.</li>
                        <li>Dùng 3 ngón tay vuốt nhẹ nhàng từ phía ngoài viền vú hướng về phía núm vú.</li>
                        <li>Xoa nhẹ quanh quầng vú theo chiều kim đồng hồ để kích thích phản xạ tiết oxytocin.</li>
                    </ul>
                '
            ],
            'Biện pháp tránh thai thời kỳ sau sinh' => [
                'summary' => 'Lựa chọn phương pháp tránh thai an toàn không ảnh hưởng đến chất lượng sữa mẹ.',
                'content' => '
                    <img src="https://images.unsplash.com/photo-1584308666744-24d5c474f2ae?auto=format&fit=crop&w=800&q=80" alt="Biện pháp tránh thai sau sinh" class="img-fluid rounded mb-3 shadow-sm">
                    <h3>Phương pháp tránh thai an toàn cho mẹ cho con bú</h3>
                    <ul>
                        <li><strong>Bao cao su:</strong> Biện pháp an toàn, không ảnh hưởng tới sữa mẹ.</li>
                        <li><strong>Đặt vòng tránh thai (Dụng cụ tử cung):</strong> Thực hiện sau sinh 6 tuần (khi tử cung đã co hồi hoàn toàn).</li>
                        <li><strong>Thuốc tránh thai đơn chất (Chỉ chứa Progestin):</strong> Không ảnh hưởng đến sự tiết sữa.</li>
                    </ul>
                '
            ],

            // PHẦN 05
            'Cơ thể và đặc trưng của trẻ sơ sinh' => [
                'summary' => 'Các hiện tượng sinh lý bình thường ở trẻ sơ sinh: Vàng da sinh lý, rụng rốn, sụt cân sinh lý.',
                'content' => '
                    <img src="https://images.unsplash.com/photo-1555252333-9f8e92e65df9?auto=format&fit=crop&w=800&q=80" alt="Đặc trưng của trẻ sơ sinh" class="img-fluid rounded mb-3 shadow-sm">
                    <h3>Hiện tượng sinh lý bình thường ở trẻ mới sinh</h3>
                    <ul>
                        <li><strong>Sụt cân sinh lý:</strong> Trong 7 ngày đầu, bé có thể giảm 5-10% cân nặng lúc sinh và sẽ tăng trở lại sau 10-14 ngày.</li>
                        <li><strong>Vàng da sinh lý:</strong> Xuất hiện từ ngày thứ 2-3 sau sinh và tự hết sau 7-10 ngày mà không cần điều trị.</li>
                        <li><strong>Chăm sóc rốn:</strong> Rốn thường tự rụng sau 7 đến 14 ngày. Giữ rốn luôn khô ráo và sạch sẽ.</li>
                    </ul>
                '
            ],
            'Nuôi con bằng sữa mẹ' => [
                'summary' => 'Lợi ích của sữa đầu, tư thế ngậm bắt vú đúng cách và lịch cho bé bú theo nhu cầu.',
                'content' => '
                    <img src="https://images.unsplash.com/photo-1519689680058-324335c77eba?auto=format&fit=crop&w=800&q=80" alt="Nuôi con bằng sữa mẹ" class="img-fluid rounded mb-3 shadow-sm">
                    <h3>1. Tầm quan trọng của Sữa mẹ</h3>
                    <p>Cho trẻ bú sớm trong vòng 1 giờ đầu sau sinh để tận dụng nguồn <strong>Sữa non</strong> giàu kháng thể IgA. Cho trẻ bú hoàn toàn bằng sữa mẹ trong 6 tháng đầu đời.</p>
                    <h3>2. Khớp ngậm đúng</h3>
                    <p>Miệng trẻ mở rộng, cằm tựa vào vú mẹ, quầng vú phía trên hở nhiều hơn phía dưới, môi dưới hướng ra ngoài.</p>
                '
            ],
            'Chăm sóc cho trẻ sơ sinh. Chăm sóc trẻ ngay sau đẻ' => [
                'summary' => 'Hướng dẫn quy trình tắm bé, vệ sinh mắt mũi, giữ ấm (Phương pháp Căng-gu-ru).',
                'content' => '
                    <img src="https://images.unsplash.com/photo-1522771739844-6a9f6d5f14af?auto=format&fit=crop&w=800&q=80" alt="Chăm sóc bé ngay sau sinh" class="img-fluid rounded mb-3 shadow-sm">
                    <h3>1. Giữ ấm cho trẻ (Được tiếp xúc da-kề-da)</h3>
                    <p>Đặt trẻ nằm trên ngực mẹ ngay sau sinh giúp trẻ ổn định thân nhiệt, nhịp tim và nhịp thở.</p>
                    <h3>2. Tắm và Vệ sinh hàng ngày</h3>
                    <p>Tắm trẻ bằng nước ấm 37°C ở nơi kín gió. Nhỏ nước muối sinh lý 0.9% vệ sinh mắt và mũi cho bé.</p>
                '
            ],
            'Nhận biết một số dấu hiệu nguy hiểm ở trẻ' => [
                'summary' => 'Các dấu hiệu bất thường cần đưa trẻ sơ sinh đi cấp cứu lập tức.',
                'content' => '
                    <img src="https://images.unsplash.com/photo-1584515933487-779824d29309?auto=format&fit=crop&w=800&q=80" alt="Dấu hiệu nguy hiểm ở trẻ sơ sinh" class="img-fluid rounded mb-3 shadow-sm">
                    <div class="alert alert-danger">
                        <h4>⚠️ ĐƯA TRẺ ĐẾN BỆNH VIỆN NGAY KHI CÓ DẤU HIỆU:</h4>
                        <ul>
                            <li>Bỏ bú hoặc bú rất kém.</li>
                            <li>Sốt cao trên 37.5°C hoặc hạ thân nhiệt dưới 36.5°C.</li>
                            <li>Thở nhanh (trên 60 lần/phút), thở rút lõm ngực, tím tái môi.</li>
                            <li>Vàng da đậm xuất hiện sớm (trong 24h đầu) hoặc lan rộng xuống bàn tay, bàn chân.</li>
                            <li>Chân rốn sưng đỏ, chảy mủ hoặc có mùi hôi.</li>
                        </ul>
                    </div>
                '
            ],
            'Lịch tiêm chủng cho trẻ nhỏ' => [
                'summary' => 'Bảng tra cứu lịch tiêm chủng mở rộng từ khi sơ sinh đến 24 tháng tuổi.',
                'content' => '
                    <img src="https://images.unsplash.com/photo-1584515933487-779824d29309?auto=format&fit=crop&w=800&q=80" alt="Tiêm chủng vắc-xin cho trẻ nhỏ" class="img-fluid rounded mb-3 shadow-sm">
                    <h3>Lịch tiêm vắc-xin cho bé theo Bộ Y tế</h3>
                    <div class="table-responsive mb-3">
                        <table class="table table-bordered align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th scope="col" style="width: 30%;">Tuổi của trẻ</th>
                                    <th scope="col" style="width: 70%;">Loại vắc-xin cần tiêm</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><strong>Sơ sinh (24h đầu)</strong></td>
                                    <td>Viêm gan B (mũi 0), Vắc-xin Lao (BCG).</td>
                                </tr>
                                <tr>
                                    <td><strong>2, 3, 4 tháng tuổi</strong></td>
                                    <td>Vắc-xin 6 trong 1 (Bạch hầu, Ho gà, Uốn ván, Viêm gan B, Bại liệt, Hib), Uống vắc-xin Rota.</td>
                                </tr>
                                <tr>
                                    <td><strong>9 tháng tuổi</strong></td>
                                    <td>Vắc-xin Sởi đơn, Cúm, Viêm não Nhật Bản.</td>
                                </tr>
                                <tr>
                                    <td><strong>12 - 24 tháng</strong></td>
                                    <td>Thủy đậu, Sởi - Quai bị - Rubella (MMR), Viêm gan A.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                '
            ],
        ];

        foreach ($contents as $categoryName => $data) {
            $category = HandbookCategory::where('name', 'like', '%' . trim($categoryName) . '%')->first();

            if ($category) {
                Article::updateOrCreate(
                    ['handbook_category_id' => $category->id],
                    [
                        'title'   => $category->name,
                        'slug'    => Str::slug($category->name),
                        'content' => $data['content'],
                        'status'  => 'published',
                        'views'   => rand(100, 500),
                    ]
                );
            }
        }
    }
}