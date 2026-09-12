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
        // 1. Khai báo rules đầy đủ (Bổ sung type và scope)
        $rules = [
            'type'                 => 'required|in:order,shipping',
            'code'                 => 'required|string|max:50|unique:vouchers,code',
            'name'                 => 'required|string|max:255',
            'description'          => 'nullable|string',
            'discount_type'        => 'required|in:percent,fixed,free_shipping',
            'discount_value'       => 'required|numeric|min:0',
            'max_discount_amount'  => 'nullable|numeric|min:0',
            'min_order_amount'     => 'nullable|numeric|min:0',
            'total_quantity'       => 'nullable|integer|min:1',
            'usage_limit_per_user' => 'required|integer|min:1',
            'starts_at'            => 'nullable|date',
            'expires_at'           => 'nullable|date|after_or_equal:starts_at',
            
            // Các trường Đối tượng & Phạm vi
            'apply_to'             => 'required|in:all,new_user,specific_tiers,specific_users',
            'status'               => 'required|in:draft,active',
            'scope'                => 'required|in:all,specific_categories',
            'tiers'                => 'nullable|array',
            'tiers.*'              => 'in:member,silver,gold,diamond',
            'category_ids'         => 'nullable|array',
            'user_emails'          => 'nullable|string',
        ];

        // 2. Tùy chỉnh thông báo lỗi
        $messages = [
            'code.unique'               => 'Mã Voucher này đã tồn tại trên hệ thống.',
            'code.required'             => 'Vui lòng nhập mã Voucher.',
            'expires_at.after_or_equal' => 'Thời gian kết thúc phải diễn ra sau thời gian bắt đầu.',
        ];

        $validated = $request->validate($rules, $messages);

        // 3. Logic chặn lỗi nhập liệu chuyên sâu
        if ($validated['discount_type'] === 'percent' && $validated['discount_value'] > 100) {
            return back()->withErrors(['discount_value' => 'Mức giảm phần trăm không được vượt quá 100%'])->withInput();
        }

        // Tự động IN HOA mã code
        $validated['code'] = Str::upper($validated['code']);
        
        // Xử lý các Checkbox
        $validated['is_public'] = $request->has('is_public');
        $validated['auto_apply'] = $request->has('auto_apply');

        // BẮT BUỘC: Xóa các trường ảo trước khi lưu vào bảng chính
        unset($validated['tiers'], $validated['category_ids'], $validated['user_emails'], $validated['scope']);

        // 4. Lưu bảng chính Voucher
        $voucher = Voucher::create($validated);

        // 5. Lưu bảng phụ: Xử lý dữ liệu Hạng thẻ
        if ($request->apply_to === 'specific_tiers' && $request->has('tiers')) {
            $conditions = [];
            foreach ($request->tiers as $tier) {
                $conditions[] = ['type' => 'tier', 'operator' => 'eq', 'value' => $tier, 'is_include' => true];
            }
            $voucher->conditions()->createMany($conditions);
        }

        // 6. Lưu bảng phụ: Xử lý dữ liệu Tặng khách hàng cụ thể
        if ($request->apply_to === 'specific_users' && $request->filled('user_emails')) {
            $emails = array_map('trim', explode(',', $request->user_emails));
            $userIds = User::whereIn('email', $emails)->pluck('id')->toArray();
            
            if (!empty($userIds)) {
                $voucher->allowedUsers()->syncWithoutDetaching($userIds);
            }
        }

        // 7. Xử lý dữ liệu Danh mục sản phẩm (Chỉ áp dụng nếu là mã Đơn hàng)
        if ($request->type === 'order' && $request->scope === 'specific_categories' && $request->has('category_ids')) {
            $categoryConditions = [];
            foreach ($request->category_ids as $catId) {
                $categoryConditions[] = ['type' => 'category', 'operator' => 'in', 'value' => $catId, 'is_include' => true];
            }
            $voucher->conditions()->createMany($categoryConditions);
        }

        return redirect()->route('admin.vouchers.index')->with('success', 'Đã tạo thành công Voucher: ' . $validated['code']);
    }

    public function edit(string $id)
    {
        $voucher = Voucher::with(['conditions', 'allowedUsers'])->findOrFail($id);
        $categories = Category::where('is_active', 1)->orderBy('sort_order')->get();

        $selectedTiers = $voucher->conditions->where('type', 'tier')->pluck('value')->toArray();
        $selectedCategories = $voucher->conditions->where('type', 'category')->pluck('value')->toArray();
        $selectedEmails = $voucher->allowedUsers->pluck('email')->implode(', ');

        $currentScope = count($selectedCategories) > 0 ? 'specific_categories' : 'all';

        return view('admin.vouchers.edit', compact(
            'voucher', 'categories', 'selectedTiers', 'selectedCategories', 'selectedEmails', 'currentScope'
        ));
    }

    public function update(Request $request, string $id)
    {
        $voucher = Voucher::findOrFail($id);

        $rules = [
            'type'                 => 'required|in:order,shipping',
            'code'                 => 'required|string|max:50|unique:vouchers,code,' . $id,
            'name'                 => 'required|string|max:255',
            'description'          => 'nullable|string',
            'discount_type'        => 'required|in:percent,fixed,free_shipping',
            'discount_value'       => 'required|numeric|min:0',
            'max_discount_amount'  => 'nullable|numeric|min:0',
            'min_order_amount'     => 'nullable|numeric|min:0',
            'total_quantity'       => 'nullable|integer|min:1',
            'usage_limit_per_user' => 'required|integer|min:1',
            'starts_at'            => 'nullable|date',
            'expires_at'           => 'nullable|date|after_or_equal:starts_at',
            'apply_to'             => 'required|in:all,new_user,specific_tiers,specific_users',
            'status'               => 'required|in:draft,active',
            'scope'                => 'required|in:all,specific_categories',
            'tiers'                => 'nullable|array',
            'tiers.*'              => 'in:member,silver,gold,diamond',
            'category_ids'         => 'nullable|array',
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

        // BẮT BUỘC: Xóa các trường ảo trước khi update
        unset($validated['tiers'], $validated['category_ids'], $validated['user_emails'], $validated['scope']);

        // 1. Cập nhật bảng chính
        $voucher->update($validated);

        // 2. Cập nhật bảng Điều kiện (Xóa cũ, chèn mới)
        $voucher->conditions()->delete();
        $conditionsToInsert = [];

        if ($request->apply_to === 'specific_tiers' && $request->has('tiers')) {
            foreach ($request->tiers as $tier) {
                $conditionsToInsert[] = ['type' => 'tier', 'operator' => 'eq', 'value' => $tier, 'is_include' => true];
            }
        }

        if ($request->type === 'order' && $request->scope === 'specific_categories' && $request->has('category_ids')) {
            foreach ($request->category_ids as $catId) {
                $conditionsToInsert[] = ['type' => 'category', 'operator' => 'in', 'value' => $catId, 'is_include' => true];
            }
        }

        if (!empty($conditionsToInsert)) {
            $voucher->conditions()->createMany($conditionsToInsert);
        }

        // 3. Cập nhật User cho phép
        if ($request->apply_to === 'specific_users' && $request->filled('user_emails')) {
            $emails = array_map('trim', explode(',', $request->user_emails));
            $userIds = User::whereIn('email', $emails)->pluck('id')->toArray();
            $voucher->allowedUsers()->sync($userIds);
        } else {
            $voucher->allowedUsers()->detach();
        }

        return redirect()->route('admin.vouchers.index')->with('success', 'Đã cập nhật Voucher thành công!');
    }
}