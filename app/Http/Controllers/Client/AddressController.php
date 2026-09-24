<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\UserAddress;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Services\GHNService;
class AddressController extends Controller
{
    public function __construct(
    protected GHNService $ghn
) {
}
    public function index()
{
    $addresses = auth()->user()
        ->addresses()
        ->get();

    $provinceResponse = $this->ghn->getProvinces();

    if (
        isset($provinceResponse['data'])
        && is_array($provinceResponse['data'])
    ) {
        $provinceResponse = $provinceResponse['data'];
    }

    if (
        isset($provinceResponse['ProvinceID'])
        && isset($provinceResponse['ProvinceName'])
    ) {
        $provinceResponse = [$provinceResponse];
    }

    $provinces = collect($provinceResponse)
        ->filter(function ($province) {
            return is_array($province)
                && isset($province['ProvinceID'])
                && isset($province['ProvinceName']);
        })
        ->values();

    return view(
        'client.addresses.index',
        compact('addresses', 'provinces')
    );
}

    public function store(Request $request)
    {
        $data = $this->validateAddress($request);

        DB::transaction(function () use ($data) {
            $user = Auth::user();

            $hasAddress = $user->addresses()->exists();

            if (!$hasAddress || !empty($data['is_default'])) {
                $user->addresses()->update([
                    'is_default' => false,
                ]);

                $data['is_default'] = true;
            }

            $user->addresses()->create($data);
        });

        return back()->with(
            'success',
            'Đã thêm địa chỉ mới.'
        );
    }

    public function update(
        Request $request,
        string $address
    ) {
        $userAddress = $this->findOwnAddress($address);

        $data = $this->validateAddress($request);

        DB::transaction(function () use ($userAddress, $data) {
            if (!empty($data['is_default'])) {
                Auth::user()
                    ->addresses()
                    ->whereKeyNot($userAddress->getKey())
                    ->update([
                        'is_default' => false,
                    ]);
            }

            $userAddress->update($data);
        });

        return back()->with(
            'success',
            'Đã cập nhật địa chỉ.'
        );
    }
    
    public function create()
    {
        $provinceResponse = $this->ghn->getProvinces();
        
        $provinceData = $provinceResponse['data'] ?? [];
        if (isset($provinceData['ProvinceID'], $provinceData['ProvinceName'])) {
            $provinceData = [$provinceData];
        }
        
        $provinces = collect($provinceData)
            ->filter(fn ($p) => 
                is_array($p) &&
                isset($p['ProvinceID'], $p['ProvinceName'])
            )
            ->values();

        return view('client.addresses.create', compact('provinces'));
    }

    public function edit(int $address)
    {
        $address = $this->findOwnAddress($address);
        
        $provinceResponse = $this->ghn->getProvinces();
        $provinceData = $provinceResponse['data'] ?? [];
        if (isset($provinceData['ProvinceID'], $provinceData['ProvinceName'])) {
            $provinceData = [$provinceData];
        }
        
        $provinces = collect($provinceData)
            ->filter(fn ($p) => 
                is_array($p) &&
                isset($p['ProvinceID'], $p['ProvinceName'])
            )
            ->values();

        return view('client.addresses.edit', compact('address', 'provinces'));
    }

    public function destroy(string $address)
    {
        $userAddress = $this->findOwnAddress($address);

        DB::transaction(function () use ($userAddress) {
            $wasDefault = $userAddress->is_default;

            $userAddress->delete();

            if ($wasDefault) {
                $next = Auth::user()
                    ->addresses()
                    ->latest()
                    ->first();

                if ($next) {
                    $next->update([
                        'is_default' => true,
                    ]);
                }
            }
        });

        return back()->with(
            'success',
            'Đã xoá địa chỉ.'
        );
    }

    public function setDefault(string $address)
    {
        $userAddress = $this->findOwnAddress($address);

        DB::transaction(function () use ($userAddress) {
            Auth::user()
                ->addresses()
                ->update([
                    'is_default' => false,
                ]);

            $userAddress->update([
                'is_default' => true,
            ]);
        });

        return back()->with(
            'success',
            'Đã đặt làm địa chỉ mặc định.'
        );
    }

    private function findOwnAddress(string $id): UserAddress
    {
        return Auth::user()
            ->addresses()
            ->whereKey($id)
            ->firstOrFail();
    }

    private function validateAddress(Request $request): array
    {
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
}