<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;

use App\Models\UserAddress;

use App\Services\GHNService;

use Illuminate\Http\Request;

use Illuminate\Support\Facades\Auth;

use Illuminate\Support\Facades\DB;

use Illuminate\Validation\ValidationException;

class AddressController extends Controller

{

    public function __construct(

        protected GHNService $ghn

    ) {

    }

    /**

     * Trang Sổ địa chỉ.

     *

     * Chỉ lấy các tỉnh/thành có ít nhất

     * một quận/huyện GHN có thể giao tới.

     */

    public function index()

    {

        $addresses = Auth::user()

            ->addresses()

            ->orderByDesc('is_default')

            ->latest()

            ->get();

        $provinceResponse =

            $this->ghn

                ->getDeliverableProvinces();

        $provinceData =

            $provinceResponse['data']

            ?? $provinceResponse;

        if (

            isset(

                $provinceData['ProvinceID'],

                $provinceData['ProvinceName']

            )

        ) {

            $provinceData = [

                $provinceData

            ];

        }

        $provinces =

            collect($provinceData)

                ->filter(

                    function ($province) {

                        return

                            is_array($province)

                            &&

                            isset(

                                $province['ProvinceID'],

                                $province['ProvinceName']

                            );

                    }

                )

                ->values();

        return view(

            'client.addresses.index',

            compact(

                'addresses',

                'provinces'

            )

        );

    }

    /**

     * Lưu địa chỉ mới.

     */

    public function store(

        Request $request

    ) {

        $data =

            $this->validateAddress(

                $request

            );

        /*

         * Backend kiểm tra lại:

         * không cho POST thủ công địa chỉ

         * nằm ngoài tuyến GHN.

         */

        $this->validateDeliverableAddress(

            $data

        );

        DB::transaction(

            function () use ($data) {

                $user = Auth::user();

                $hasAddress =

                    $user

                        ->addresses()

                        ->exists();

                /*

                 * Địa chỉ đầu tiên luôn là mặc định.

                 * Hoặc user chủ động tick mặc định.

                 */

                if (

                    !$hasAddress ||

                    !empty(

                        $data['is_default']

                    )

                ) {

                    $user

                        ->addresses()

                        ->update([

                            'is_default' =>

                                false,

                        ]);

                    $data['is_default'] =

                        true;

                } else {

                    $data['is_default'] =

                        false;

                }

                $user

                    ->addresses()

                    ->create($data);

            }

        );

        return back()->with(

            'success',

            'Đã thêm địa chỉ mới.'

        );

    }

    /**

     * Cập nhật địa chỉ.

     */

    public function update(

        Request $request,

        string $address

    ) {

        $userAddress =

            $this->findOwnAddress(

                $address

            );

        $data =

            $this->validateAddress(

                $request

            );

        $this->validateDeliverableAddress(

            $data

        );

        DB::transaction(

            function () use (

                $userAddress,

                $data

            ) {

                /*

                 * Nếu tick mặc định:

                 * bỏ mặc định các địa chỉ khác.

                 */

                if (

                    !empty(

                        $data['is_default']

                    )

                ) {

                    Auth::user()

                        ->addresses()

                        ->whereKeyNot(

                            $userAddress

                                ->getKey()

                        )

                        ->update([

                            'is_default' =>

                                false,

                        ]);

                    $data['is_default'] =

                        true;

                } else {

                    /*

                     * Không cho vô tình bỏ mặc định

                     * của địa chỉ đang là mặc định

                     * mà khiến user không còn

                     * địa chỉ mặc định.

                     */

                    if (

                        $userAddress

                            ->is_default

                    ) {

                        $data['is_default'] =

                            true;

                    } else {

                        $data['is_default'] =

                            false;

                    }

                }

                $userAddress

                    ->update($data);

            }

        );

        return back()->with(

            'success',

            'Đã cập nhật địa chỉ.'

        );

    }

    /**

     * Route create chỉ đưa về

     * Sổ địa chỉ và tự mở modal.

     */

    public function create()

    {

        return redirect()

            ->route(

                'profile.addresses.index',

                [

                    'add' => 1,

                ]

            );

    }

    /**

     * Trang sửa địa chỉ.

     */

    public function edit(
    string $address
) {
    $address =
        $this->findOwnAddress(
            $address
        );

    $provinceResponse =
        $this->ghn
            ->getDeliverableProvinces();

    $provinceData =
        $provinceResponse['data']
        ?? $provinceResponse;

    if (
        isset(
            $provinceData['ProvinceID'],
            $provinceData['ProvinceName']
        )
    ) {
        $provinceData = [
            $provinceData
        ];
    }

    $provinces =
        collect($provinceData)
            ->filter(
                fn ($province) =>
                    is_array($province)
                    &&
                    isset(
                        $province['ProvinceID'],
                        $province['ProvinceName']
                    )
            )
            ->values();

    return view(
        'client.addresses.edit',
        compact(
            'address',
            'provinces'
        )
    );
}

    /**

     * Xóa địa chỉ.

     */

    public function destroy(

        string $address

    ) {

        $userAddress =

            $this->findOwnAddress(

                $address

            );

        DB::transaction(

            function () use (

                $userAddress

            ) {

                $wasDefault =

                    (bool)

                    $userAddress

                        ->is_default;

                $userAddress->delete();

                /*

                 * Nếu vừa xóa địa chỉ mặc định,

                 * lấy địa chỉ mới nhất còn lại

                 * làm mặc định.

                 */

                if ($wasDefault) {

                    $next =

                        Auth::user()

                            ->addresses()

                            ->latest()

                            ->first();

                    if ($next) {

                        $next->update([

                            'is_default' =>

                                true,

                        ]);

                    }

                }

            }

        );

        return back()->with(

            'success',

            'Đã xoá địa chỉ.'

        );

    }

    /**

     * Đặt địa chỉ mặc định.

     */

    public function setDefault(

        string $address

    ) {

        $userAddress =

            $this->findOwnAddress(

                $address

            );

        /*

         * Không cho đặt làm mặc định

         * nếu địa chỉ cũ hiện đã nằm ngoài

         * tuyến GHN hỗ trợ.

         */

        $this->validateDeliverableAddress([

            'province_id' =>

                $userAddress

                    ->province_id,

            'district_id' =>

                $userAddress

                    ->district_id,

            'ward_code' =>

                $userAddress

                    ->ward_code,

        ]);

        DB::transaction(

            function () use (

                $userAddress

            ) {

                Auth::user()

                    ->addresses()

                    ->update([

                        'is_default' =>

                            false,

                    ]);

                $userAddress

                    ->update([

                        'is_default' =>

                            true,

                    ]);

            }

        );

        return back()->with(

            'success',

            'Đã đặt làm địa chỉ mặc định.'

        );

    }

    /**

     * Chỉ cho user thao tác

     * trên chính địa chỉ của họ.

     */

    private function findOwnAddress(

        string $id

    ): UserAddress {

        return Auth::user()

            ->addresses()

            ->whereKey($id)

            ->firstOrFail();

    }

    /**

     * Validate dữ liệu form.

     */

    private function validateAddress(

        Request $request

    ): array {

        return $request->validate([

            'recipient_name' => [

                'required',

                'string',

                'max:100',

            ],

            'phone' => [

                'required',

                'string',

                'max:20',

            ],

            'province_id' => [

                'required',

                'integer',

            ],

            'province_name' => [

                'required',

                'string',

                'max:120',

            ],

            'district_id' => [

                'required',

                'integer',

            ],

            'district_name' => [

                'required',

                'string',

                'max:120',

            ],

            'ward_code' => [

                'required',

                'string',

                'max:30',

            ],

            'ward_name' => [

                'required',

                'string',

                'max:120',

            ],

            'address_detail' => [

                'required',

                'string',

                'max:255',

            ],

            'label' => [

                'nullable',

                'string',

                'max:50',

            ],

            'is_default' => [

                'nullable',

                'boolean',

            ],

        ]);

    }

    /**

     * Kiểm tra địa chỉ thật sự nằm

     * trong tuyến GHN đang hỗ trợ.

     */

    private function validateDeliverableAddress(

        array $data

    ): void {

        $provinceId =

            (int) (

                $data[

                    'province_id'

                ] ?? 0

            );

        $districtId =

            (int) (

                $data[

                    'district_id'

                ] ?? 0

            );

        $wardCode =

            (string) (

                $data[

                    'ward_code'

                ] ?? ''

            );

        if (

            $provinceId <= 0 ||

            $districtId <= 0 ||

            $wardCode === ''

        ) {

            throw ValidationException::withMessages([

                'district_id' =>

                    'Thông tin địa chỉ GHN không hợp lệ.',

            ]);

        }

        /*

         * Kiểm tra huyện có service.

         */

        $districts =

            collect(

                $this->ghn

                    ->getDeliverableDistricts(

                        $provinceId

                    )

            );

        $districtExists =

            $districts->contains(

                fn ($district) =>

                    (int) (

                        $district[

                            'DistrictID'

                        ] ?? 0

                    )

                    ===

                    $districtId

            );

        if (!$districtExists) {

            throw ValidationException::withMessages([

                'district_id' =>

                    'Quận/Huyện này hiện không nằm trong tuyến GHN hỗ trợ giao hàng.',

            ]);

        }

        /*

         * Kiểm tra ward.

         */

        $wards =

            collect(

                $this->ghn

                    ->getDeliverableWards(

                        $districtId

                    )

            );

        $wardExists =

            $wards->contains(

                fn ($ward) =>

                    (string) (

                        $ward[

                            'WardCode'

                        ] ?? ''

                    )

                    ===

                    $wardCode

            );

        if (!$wardExists) {

            throw ValidationException::withMessages([

                'ward_code' =>

                    'Phường/Xã này hiện không được GHN hỗ trợ giao hàng.',

            ]);

        }

    }

}