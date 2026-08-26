<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Stage;
use Illuminate\Http\Request;

class StageController extends Controller
{
    public function index(Request $request)
    {
        $query = Stage::query();

        if ($request->filled('search')) {
            $search = $request->search;

            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                    ->orWhere('description', 'like', '%' . $search . '%');
            });
        }

        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->where('is_active', true);
            }

            if ($request->status === 'inactive') {
                $query->where('is_active', false);
            }
        }

        $stages = $query
            ->orderBy('sort_order')
            ->orderBy('id')
            ->paginate(10)
            ->withQueryString();

        return view('admin.stages.index', compact('stages'));
    }

    public function create()
    {
        return view('admin.stages.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'age_from' => ['nullable', 'integer', 'min:0'],
            'age_to' => ['nullable', 'integer', 'min:0', 'gte:age_from'],
            'description' => ['nullable', 'string'],
            'icon' => ['nullable', 'string', 'max:255'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable'],
        ], [
            'name.required' => 'Vui lòng nhập tên giai đoạn.',
            'name.max' => 'Tên giai đoạn không được vượt quá 255 ký tự.',
            'age_from.integer' => 'Độ tuổi bắt đầu phải là số nguyên.',
            'age_from.min' => 'Độ tuổi bắt đầu không được nhỏ hơn 0.',
            'age_to.integer' => 'Độ tuổi kết thúc phải là số nguyên.',
            'age_to.min' => 'Độ tuổi kết thúc không được nhỏ hơn 0.',
            'age_to.gte' => 'Độ tuổi kết thúc phải lớn hơn hoặc bằng độ tuổi bắt đầu.',
            'sort_order.integer' => 'Thứ tự hiển thị phải là số nguyên.',
            'sort_order.min' => 'Thứ tự hiển thị không được nhỏ hơn 0.',
        ]);

        $validated['sort_order'] = $validated['sort_order'] ?? 0;
        $validated['is_active'] = $request->boolean('is_active');

        Stage::create($validated);

        return redirect()
            ->route('admin.stages.index')
            ->with('success', 'Thêm giai đoạn thành công.');
    }

    public function edit(Stage $stage)
    {
        return view('admin.stages.edit', compact('stage'));
    }

    public function update(Request $request, Stage $stage)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'age_from' => ['nullable', 'integer', 'min:0'],
            'age_to' => ['nullable', 'integer', 'min:0', 'gte:age_from'],
            'description' => ['nullable', 'string'],
            'icon' => ['nullable', 'string', 'max:255'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable'],
        ], [
            'name.required' => 'Vui lòng nhập tên giai đoạn.',
            'name.max' => 'Tên giai đoạn không được vượt quá 255 ký tự.',
            'age_from.integer' => 'Độ tuổi bắt đầu phải là số nguyên.',
            'age_from.min' => 'Độ tuổi bắt đầu không được nhỏ hơn 0.',
            'age_to.integer' => 'Độ tuổi kết thúc phải là số nguyên.',
            'age_to.min' => 'Độ tuổi kết thúc không được nhỏ hơn 0.',
            'age_to.gte' => 'Độ tuổi kết thúc phải lớn hơn hoặc bằng độ tuổi bắt đầu.',
            'sort_order.integer' => 'Thứ tự hiển thị phải là số nguyên.',
            'sort_order.min' => 'Thứ tự hiển thị không được nhỏ hơn 0.',
        ]);

        $validated['sort_order'] = $validated['sort_order'] ?? 0;
        $validated['is_active'] = $request->boolean('is_active');

        $stage->update($validated);

        return redirect()
            ->route('admin.stages.index')
            ->with('success', 'Cập nhật giai đoạn thành công.');
    }

    public function destroy(Stage $stage)
    {
        $stage->delete();

        return redirect()
            ->route('admin.stages.index')
            ->with('success', 'Xóa giai đoạn thành công.');
    }
}