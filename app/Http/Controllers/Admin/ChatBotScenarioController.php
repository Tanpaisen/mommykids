<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ChatBotScenario;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ChatBotScenarioController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);

        ChatBotScenario::create($data);

        return redirect()
            ->route('admin.customer-care.index', [
                'tab' => 'scenarios',
            ])
            ->with(
                'success',
                'Đã tạo kịch bản Chat Bot.'
            );
    }

    public function update(
        Request $request,
        ChatBotScenario $scenario
    ): RedirectResponse {
        $scenario->update(
            $this->validated($request)
        );

        return redirect()
            ->route('admin.customer-care.index', [
                'tab' => 'scenarios',
            ])
            ->with(
                'success',
                'Đã cập nhật kịch bản Chat Bot.'
            );
    }

    public function toggle(
        ChatBotScenario $scenario
    ): RedirectResponse {
        $scenario->update([
            'is_active' => !$scenario->is_active,
        ]);

        return redirect()
            ->route('admin.customer-care.index', [
                'tab' => 'scenarios',
            ])
            ->with(
                'success',
                $scenario->is_active
                    ? 'Đã bật kịch bản.'
                    : 'Đã tắt kịch bản.'
            );
    }

    public function destroy(
        ChatBotScenario $scenario
    ): RedirectResponse {
        $scenario->delete();

        return redirect()
            ->route('admin.customer-care.index', [
                'tab' => 'scenarios',
            ])
            ->with(
                'success',
                'Đã xóa kịch bản Chat Bot.'
            );
    }

    private function validated(
        Request $request
    ): array {
        $data = $request->validate([
            'name' => [
                'required',
                'string',
                'max:150',
            ],

            'keywords_text' => [
                'required',
                'string',
                'max:2000',
            ],

            'response' => [
                'required',
                'string',
                'max:5000',
            ],

            'priority' => [
                'required',
                'integer',
                'min:1',
                'max:9999',
            ],

            'is_active' => [
                'nullable',
                'boolean',
            ],

            'handoff_to_staff' => [
                'nullable',
                'boolean',
            ],
        ]);

        $keywords = collect(
            preg_split(
                '/[\r\n,]+/',
                $data['keywords_text']
            )
        )
            ->map(
                fn ($keyword) =>
                    trim((string) $keyword)
            )
            ->filter()
            ->unique()
            ->values()
            ->all();

        return [
            'name' => $data['name'],

            'keywords' => $keywords,

            'response' => $data['response'],

            'priority' => (int) $data['priority'],

            'is_active' =>
                $request->boolean('is_active'),

            'handoff_to_staff' =>
                $request->boolean(
                    'handoff_to_staff'
                ),
        ];
    }
}