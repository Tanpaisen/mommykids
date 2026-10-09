<?php 

namespace App\Http\Controllers; 

use App\Models\Category; 
use App\Models\Product; 
use App\Models\Setting; 
use App\Models\Faq;
use App\Services\CampaignService; 

class HomeController extends Controller 
{ 
    public function index(
        CampaignService $campaignService
    ) { 

        /*
        |--------------------------------------------------------------------------
        | Cấu hình chung hệ thống
        |--------------------------------------------------------------------------
        */
        $settings = Setting::first(); 


        /*
        |--------------------------------------------------------------------------
        | Sản phẩm nổi bật
        |--------------------------------------------------------------------------
        */
        $featuredProducts = Product::query() 
            ->active() 
            ->featured() 
            ->withReviewStats() 
            ->latest() 
            ->limit(10) 
            ->get() 
            ->map(
                fn (Product $product) => 
                    $this->toCampaignCard(
                        $product,
                        $campaignService
                    )
            ); 


        /*
        |--------------------------------------------------------------------------
        | Các section sản phẩm theo danh mục
        |--------------------------------------------------------------------------
        */
        $sections = Category::query() 
            ->active() 
            ->with([
                'products' => fn ($query) => 
                    $query
                        ->active()
                        ->withReviewStats()
                        ->latest()
                        ->limit(10),
            ])
            ->get()
            ->filter(
                fn (Category $category) =>
                    $category->products->isNotEmpty()
            )
            ->map(
                function (
                    Category $category
                ) use ($campaignService) {

                    return [
                        'title' => $category->name,

                        'icon' => $category->icon,

                        'url' => route(
                            'category.show',
                            $category->slug
                        ),

                        'products' => $category
                            ->products
                            ->map(
                                fn (Product $product) =>
                                    $this->toCampaignCard(
                                        $product,
                                        $campaignService
                                    )
                            ),
                    ];
                }
            )
            ->values(); 



        /*
        |--------------------------------------------------------------------------
        | Danh mục sidebar Home
        |--------------------------------------------------------------------------
        */
        $homeCategories = Category::query()
            ->active()
            ->orderBy('id')
            ->limit(10)
            ->get();



        /*
        |--------------------------------------------------------------------------
        | FAQ - Câu hỏi thường gặp
        |--------------------------------------------------------------------------
        */
        $faqs = Faq::query()
            ->where('is_active', 1)
            ->orderBy('sort_order')
            ->get();



        /*
        |--------------------------------------------------------------------------
        | Trả dữ liệu sang trang khách hàng
        |--------------------------------------------------------------------------
        */
        return view('client.home', [

            'featuredProducts' => $featuredProducts,

            'sections' => $sections,

            'homeCategories' => $homeCategories,

            'settings' => $settings,

            'faqs' => $faqs,

        ]); 
    } 



    /*
    |--------------------------------------------------------------------------
    | Chuyển Product thành dữ liệu card có Campaign
    |--------------------------------------------------------------------------
    */
    private function toCampaignCard(
        Product $product,
        CampaignService $campaignService
    ): array { 

        $card = $product->toCardArray();



        $campaign = $campaignService
            ->getBestCampaignForProduct(
                $product
            );



        /*
         * Không có Campaign
         */
        if (!$campaign) {

            $card['is_campaign'] = false;
            $card['campaign_id'] = null;
            $card['campaign_type'] = null;

            return $card;
        }



        $basePrice = (int) $product->price;



        $effectivePrice = (int) $campaignService
            ->getCampaignPrice(
                $campaign,
                $product
            );



        /*
         * Campaign không giảm giá thật
         */
        if (
            $basePrice <= 0 ||
            $effectivePrice >= $basePrice
        ) {

            $card['is_campaign'] = false;
            $card['campaign_id'] = null;
            $card['campaign_type'] = null;

            return $card;
        }



        $discountPercent = (int) round(
            (
                ($basePrice - $effectivePrice)
                / $basePrice
            ) * 100
        );



        /*
         * Field hiển thị card sản phẩm
         */
        $card['price'] = $effectivePrice;

        $card['old_price'] = $basePrice;

        $card['discount'] = $discountPercent;

        $card['is_campaign'] = true;

        $card['campaign_id'] = $campaign->id;

        $card['campaign_type'] = $campaign->type;



        return $card;
    } 
}