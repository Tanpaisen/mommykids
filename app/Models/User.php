<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\HasApiTokens;
use App\Models\NotificationPreference;
use App\Models\WishlistItem;
use Illuminate\Support\Facades\Cache;


class User extends Authenticatable
{

    use HasApiTokens, HasFactory, Notifiable, HasUlids;

    /**
     * Các thuộc tính được phép mass assignment.
     *
     * @var array<int, string>
     */
    protected $fillable = [

        'name',
        'email',
        'password',

        'phone',
        'dob',
        'gender',

        'loyalty_code',

        'tier',
        'points',
        'total_spent',

        'role',
        'status',
        'is_active',

        'last_seen_at',

        'provider',
        'provider_id',
        'avatar',

    ];




    protected $hidden = [

        'password',
        'remember_token',

    ];




    protected $casts = [

        'email_verified_at' => 'datetime',

        'last_seen_at' => 'datetime',

        'points' => 'integer',

        'total_spent' => 'decimal:2',

    ];





    protected static function booted(): void
    {


        static::creating(function ($user) {


            if(empty($user->loyalty_code)){


                do {

                    $code = '893'.mt_rand(1000000000,9999999999);


                } while(
                    static::where(
                        'loyalty_code',
                        $code
                    )->exists()
                );


                $user->loyalty_code = $code;

            }


        });



        /*
        |--------------------------------------------------------------------------
        | Tự động đồng bộ tier
        |--------------------------------------------------------------------------
        */

        static::saving(function($user){

            $user->tier = $user->calculateTier();

        });


    }






    public function addresses(): HasMany
    {

        return $this->hasMany(
            UserAddress::class
        )
        ->orderByDesc('is_default')
        ->latest();

    }





    public function orders()
    {

        return $this->hasMany(
            Order::class,
            'user_id'
        );

    }


    /**
     * Mối quan hệ với Danh sách yêu thích (Kiểm tra an toàn nếu chưa có Model)
     */
    public function wishlist()
    {
        if (class_exists(\App\Models\WishlistItem::class)) {
            return $this->hasMany(\App\Models\WishlistItem::class, 'user_id');
        }
        return $this->hasMany(self::class, 'id')->whereRaw('1 = 0');
    }



    public function carts()
{
    return $this->hasMany(
        Cart::class,
        'user_id'
    );
}


public function activeCart()
{
    return $this->hasOne(
        Cart::class,
        'user_id'
    )->where('status','active');
}

    public function pointLogs()
    {

        return $this->hasMany(
            PointLog::class,
            'user_id'
        )
        ->latest();

    }





    public function productReviews(): HasMany
    {

        return $this->hasMany(
            ProductReview::class
        );

    }






    /*
    |--------------------------------------------------------------------------
    | Tính hạng theo tổng chi tiêu
    |--------------------------------------------------------------------------
    */

    public function calculateTier(): string
    {


        $spent = (float)$this->total_spent;



        if($spent >= 10000000){

            return 'diamond';

        }



        if($spent >= 5000000){

            return 'gold';

        }



        if($spent >= 2000000){

            return 'silver';

        }



        return 'bronze';


    }







    /*
    |--------------------------------------------------------------------------
    | Tên hạng hiển thị
    |--------------------------------------------------------------------------
    */

    public function getTierNameAttribute(): string
    {


        return match($this->calculateTier()){


            'silver'
                => 'Hạng Bạc',



            'gold'
                => 'Hạng Vàng',



            'diamond'
                => 'Hạng Kim Cương',



            default
                => 'Hạng Đồng',


        };


    }






    /*
    |--------------------------------------------------------------------------
    | Dùng cho blade:
    | $user->current_tier_name
    |--------------------------------------------------------------------------
    */
    public function getCurrentTierNameAttribute(): string
    {
        return $this->tier_name;
    }

    /*
    |--------------------------------------------------------------------------
    | Phần trăm tiến trình nâng hạng
    |--------------------------------------------------------------------------
    */

    public function getTierProgressAttribute(): int
    {


        $spent = (float)$this->total_spent;



        if($spent >= 10000000){

            return 100;

        }



        if($spent >= 5000000){

            return round(
                (($spent-5000000)/5000000)*100
            );

        }



        if($spent >= 2000000){

            return round(
                (($spent-2000000)/3000000)*100
            );

        }



        return round(
            ($spent/2000000)*100
        );


    }







    /*
    |--------------------------------------------------------------------------
    | Thông tin nâng hạng
    |--------------------------------------------------------------------------
    */

    public function getNextTierProgressAttribute(): array
    {


        $spent = (float)$this->total_spent;



        if($spent >= 10000000){


            return [

                'percent'=>100,

                'needed'=>0,

                'next_tier'=>'Đã đạt hạng cao nhất'

            ];


        }



        if($spent >= 5000000){


            return [

                'percent'=>round(
                    (($spent-5000000)/5000000)*100
                ),

                'needed'=>10000000-$spent,

                'next_tier'=>'Hạng Kim Cương'

            ];


        }



        if($spent >= 2000000){


            return [

                'percent'=>round(
                    (($spent-2000000)/3000000)*100
                ),

                'needed'=>5000000-$spent,

                'next_tier'=>'Hạng Vàng'

            ];


        }




        return [


            'percent'=>round(
                ($spent/2000000)*100
            ),


            'needed'=>2000000-$spent,


            'next_tier'=>'Hạng Bạc'


        ];



    }







    /*
    |--------------------------------------------------------------------------
    | Cộng điểm sau đơn hàng
    |--------------------------------------------------------------------------
    */

    public function rewardLoyaltyForOrder($order): void
    {


        $orderTotal = is_object($order)
            ? (float)$order->total_amount
            : (float)$order;



        $orderId = is_object($order)
            ? $order->id
            : null;




        DB::transaction(function() use(
            $orderTotal,
            $orderId
        ){


            $pointsEarned = floor(
                $orderTotal / 10000
            );



            $this->total_spent += $orderTotal;



            $this->points += $pointsEarned;



            // tự tính lại hạng

            $this->tier = $this->calculateTier();



            $this->save();




            if(
                $pointsEarned > 0 &&
                class_exists(PointLog::class)
            ){


                $this->pointLogs()->create([


                    'order_id'=>$orderId,


                    'points'=>$pointsEarned,


                    'type'=>'earn',


                    'description'=>
                    "Tích điểm từ đơn hàng"



                ]);

            }



        });



    }







    public function savedVouchers()
    {

        return $this->belongsToMany(

            Voucher::class,

            'voucher_users',

            'user_id',

            'voucher_id'

        );

    }

    public function notificationPreferences(): HasMany
    {
        return $this->hasMany(NotificationPreference::class);
    }

    public static function unreadCacheKey(string $userId): string
    {
        return "notif:unread:{$userId}";
    }

    public static function notificationListCacheKey(
        string $userId
    ): string {
        return "notif:header:{$userId}";
    }

    public function cachedUnreadCount(): int
    {
        return Cache::remember(
            self::unreadCacheKey($this->id),
            now()->addMinutes(10),
            fn () => $this->unreadNotifications()->count()
        );
    }

    public function cachedHeaderNotifications()
    {
        return Cache::remember(
            self::notificationListCacheKey($this->id),
            now()->addMinutes(10),
            fn () => $this->notifications()
                ->latest()
                ->limit(8)
                ->get()
        );
    }
}