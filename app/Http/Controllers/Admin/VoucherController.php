<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Voucher;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\Category;

class VoucherController extends Controller
{
    public function index()
    {
        $vouchers = Voucher::latest()->paginate(10);
        return view('admin.vouchers.index', compact('vouchers'));
    }

    public function create()
    {
        $categories = Category::where('is_active', 1)->orderBy('sort_order')->get();
        return view('admin.vouchers.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $rules = [
            'type'                => 'required|in:order,shipping',
            'code'                => 'required|string|max:50|unique:vouchers,code',
            'name'                => 'required|string|max:255',
            'description'         => 'nullable|string',
            'is_public'           => 'nullable|boolean',
            'auto_apply'          => 'nullable|boolean',
            'is_stackable'        => 'nullable|boolean',
            'priority'            => 'nullable|integer|min:0',
            'channel'             => 'nullable|in:all,web,app',
            'discount_type'       => 'required|in:percent,fixed,free_shipping',
            'discount_value'      => 'required|numeric|min:0',
            'max_discount_amount' => 'nullable|numeric|min:0',
            'min_order_amount'    => 'nullable|numeric|min:0',
            'total_quantity'      => 'nullable|integer|min:1',
            'usage_limit_per_user'=> 'required|integer|min:1',
            'total_budget'        => 'nullable|numeric|min:0',
            'starts_at'           => 'nullable|date',
            'expires_at'          => 'nullable|date|after_or_equal:starts_at',
            'require_save_to_user'=> 'nullable|boolean',
            'apply_to'            => 'required|in:all,new_user,specific_tiers,specific_users',
            'status'              => 'required|in:draft,scheduled,active,paused,expired',
            // ✅ ĐÃ BỔ SUNG specific_products
            'scope'               => 'required|in:all,specific_categories,specific_products',
            'tiers'               => 'nullable|array',
            'tiers.*'             => 'in:member,silver,gold,diamond',
            'category_ids'        => 'nullable|array',
            'product_ids'         => 'nullable|string', // Lưu dạng JSON
            'user_emails'         => 'nullable|string',
        ];

        $messages = [
            'code.unique' => 'Mã Voucher này đã tồn tại trên hệ thống.',
            'code.required' => 'Vui lòng nhập mã Voucher.',
            'expires_at.after_or_equal' => 'Thời gian kết thúc phải diễn ra sau thời gian bắt đầu.',
        ];

        $validated = $request->validate($rules, $messages);

        if ($validated['discount_type'] === 'percent' && $validated['discount_value'] > 100) {
            return back()->withErrors(['discount_value' => 'Mức giảm phần trăm không được vượt quá 100%'])->withInput();
        }

        $validated['code'] = Str::upper($validated['code']);
        $validated['is_public'] = $request->boolean('is_public', true);
        $validated['auto_apply'] = $request->boolean('auto_apply', false);
        $validated['require_save_to_user'] = $request->boolean('require_save_to_user', false);
        $validated['is_stackable'] = $request->boolean('is_stackable', false);
        $validated['priority'] = $request->input('priority', 0);
        $validated['channel'] = $request->input('channel', 'all');
        $validated['owner_type'] = $request->input('owner_type', 'platform');

        $applyTo = $validated['apply_to'];
        $scope = $validated['scope'];
        $type = $validated['type'];
        $tiers = $validated['tiers'] ?? [];
        $categoryIds = $validated['category_ids'] ?? [];
        $rawProductIds = trim($validated['product_ids'] ?? '');
        $productIds = [];

        if (!empty($rawProductIds)) {
            $decoded = json_decode($rawProductIds, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                $productIds = array_filter($decoded);
            } else {
                $productIds = array_filter(array_map('trim', explode(',', $rawProductIds)));
            }
        }
        unset($validated['tiers'], $validated['category_ids'], $validated['product_ids'], $validated['user_emails'], $validated['scope']);

        $voucher = Voucher::create($validated);

        // Lưu điều kiện hạng thành viên
        if ($applyTo === 'specific_tiers' && !empty($tiers)) {
            $conditions = [];
            foreach ($tiers as $tier) {
                $conditions[] = [
                    'type' => 'tier', 'operator' => 'eq', 'value' => $tier, 'is_include' => true,
                ];
            }
            $voucher->conditions()->createMany($conditions);
        }

        // Lưu điều kiện danh mục
        if ($type === 'order' && $scope === 'specific_categories' && !empty($categoryIds)) {
            $categoryConditions = [];
            foreach ($categoryIds as $catId) {
                $categoryConditions[] = [
                    'type' => 'category', 'operator' => 'in', 'value' => $catId, 'is_include' => true,
                ];
            }
            $voucher->conditions()->createMany($categoryConditions);
        }

        // ✅ Lưu điều kiện sản phẩm
        if ($type === 'order' && $scope === 'specific_products' && !empty($productIds)) {
            $productConditions = [];
            foreach ($productIds as $prodId) {
                $productConditions[] = [
                    'type' => 'product', 'operator' => 'in', 'value' => $prodId, 'is_include' => true,
                ];
            }
            $voucher->conditions()->createMany($productConditions);
        }

        // Lưu người dùng cụ thể
        if ($applyTo === 'specific_users' && !empty($validated['user_emails'])) {
            $emails = array_map('trim', explode(',', $validated['user_emails']));
            $userIds = User::whereIn('email', $emails)->pluck('id')->toArray();
            if (!empty($userIds)) {
                $voucher->savedUsers()->syncWithoutDetaching($userIds);
            }
        }

        return redirect()->route('admin.vouchers.index')->with('success', 'Đã tạo thành công Voucher: ' . $validated['code']);
    }

    public function edit(string $id)
    {
        $voucher = Voucher::with(['conditions', 'savedUsers'])->findOrFail($id);
        $categories = Category::where('is_active', 1)->orderBy('sort_order')->get();

        $selectedTiers = $voucher->conditions->where('type', 'tier')->pluck('value')->toArray();
        $selectedCategories = $voucher->conditions->where('type', 'category')->pluck('value')->toArray();
        $selectedProducts = $voucher->conditions->where('type', 'product')->pluck('value')->toArray();
        $selectedEmails = $voucher->savedUsers->pluck('email')->implode(', ');

        // Xác định phạm vi hiện tại
        if (count($selectedProducts) > 0) {
            $currentScope = 'specific_products';
        } elseif (count($selectedCategories) > 0) {
            $currentScope = 'specific_categories';
        } else {
            $currentScope = 'all';
        }

        return view('admin.vouchers.edit', compact(
            'voucher', 'categories', 'selectedTiers',
            'selectedCategories', 'selectedProducts',
            'selectedEmails', 'currentScope'
        ));
    }

    public function update(Request $request, string $id)
    {
        $voucher = Voucher::findOrFail($id);

        $rules = [
            'type'                 => 'required|in:order,shipping',
            'code'                 => 'required|string|max:50|unique:vouchers,code,' . $id,
            'name'                 => 'required|string|max:255',
            'is_public'            => 'nullable|boolean',
            'auto_apply'           => 'nullable|boolean',
            'is_stackable'         => 'nullable|boolean',
            'priority'             => 'nullable|integer|min:0',
            'channel'              => 'nullable|in:all,web,app',
            'description'          => 'nullable|string',
            'discount_type'        => 'required|in:percent,fixed,free_shipping',
            'discount_value'       => 'required|numeric|min:0',
            'max_discount_amount'  => 'nullable|numeric|min:0',
            'min_order_amount'     => 'nullable|numeric|min:0',
            'total_quantity'       => 'nullable|integer|min:1',
            'usage_limit_per_user' => 'required|integer|min:1',
            'total_budget'         => 'nullable|numeric|min:0',
            'starts_at'            => 'nullable|date',
            'expires_at'           => 'nullable|date|after_or_equal:starts_at',
            'apply_to'             => 'required|in:all,new_user,specific_tiers,specific_users',
            'status'               => 'required|in:draft,scheduled,active,paused,expired',
            'scope'                => 'required|in:all,specific_categories,specific_products',
            'tiers'                => 'nullable|array',
            'tiers.*'              => 'in:member,silver,gold,diamond',
            'category_ids'         => 'nullable|array',
            'product_ids'          => 'nullable|string',
            'user_emails'          => 'nullable|string',
        ];

        $validated = $request->validate($rules, [
            'code.unique' => 'Mã Voucher này đã tồn tại trên hệ thống.',
            'expires_at.after_or_equal' => 'Thời gian kết thúc phải diễn ra sau thời gian bắt đầu.',
        ]);

        if ($validated['discount_type'] === 'percent' && $validated['discount_value'] > 100) {
            return back()->withErrors(['discount_value' => 'Mức giảm phần trăm không vượt quá 100%'])->withInput();
        }

        $validated['code'] = Str::upper($validated['code']);
        $validated['is_public'] = $request->has('is_public');
        $validated['auto_apply'] = $request->has('auto_apply');
        $validated['require_save_to_user'] = $request->boolean('require_save_to_user', false);

        $type = $validated['type'];
        $scope = $validated['scope'];
        $applyTo = $validated['apply_to'];
        $tiers = $validated['tiers'] ?? [];
        $categoryIds = $validated['category_ids'] ?? [];
        $rawProductIds = trim($validated['product_ids'] ?? '');
        $productIds = [];

        if (!empty($rawProductIds)) {
            $decoded = json_decode($rawProductIds, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                // Định dạng JSON hợp lệ
                $productIds = array_filter($decoded);
            } else {
                // Định dạng chuỗi phân tách dấu phẩy
                $productIds = array_filter(array_map('trim', explode(',', $rawProductIds)));
            }
        }

        unset($validated['tiers'], $validated['category_ids'], $validated['product_ids'], $validated['user_emails'], $validated['scope']);

        // Cập nhật thông tin chính
        $voucher->update($validated);

        // Xóa toàn bộ điều kiện cũ → ghi mới
        $voucher->conditions()->delete();

        // Hạng thành viên
        if ($applyTo === 'specific_tiers' && !empty($tiers)) {
            foreach ($tiers as $tier) {
                $voucher->conditions()->create([
                    'type' => 'tier', 'operator' => 'eq', 'value' => $tier, 'is_include' => true,
                ]);
            }
        }

        // Danh mục
        if ($type === 'order' && $scope === 'specific_categories' && !empty($categoryIds)) {
            foreach ($categoryIds as $catId) {
                $voucher->conditions()->create([
                    'type' => 'category', 'operator' => 'in', 'value' => $catId, 'is_include' => true,
                ]);
            }
        }

        // Sản phẩm cụ thể
        if ($type === 'order' && $scope === 'specific_products' && !empty($productIds) && is_iterable($productIds)) {
            foreach ($productIds as $prodId) {
                $voucher->conditions()->create([
                    'type' => 'product', 'operator' => 'in', 'value' => $prodId, 'is_include' => true,
                ]);
            }
        }

        // Người dùng cụ thể
        if ($applyTo === 'specific_users' && $request->filled('user_emails')) {
            $emails = array_map('trim', explode(',', $request->user_emails));
            $userIds = User::whereIn('email', $emails)->pluck('id')->toArray();
            $voucher->savedUsers()->sync($userIds);
        } else {
            $voucher->savedUsers()->detach();
        }

        return redirect()->route('admin.vouchers.index')->with('success', 'Đã cập nhật Voucher thành công!');
    }
}