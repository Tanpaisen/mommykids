<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductReviewRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'rating' => [
                'required',
                'integer',
                'between:1,5',
            ],

            'comment' => [
                'nullable',
                'string',
                'max:2000',
            ],

            'images' => [
                'nullable',
                'array',
                'max:5',
            ],

            'images.*' => [
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:4096',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'rating.required' =>
                'Vui lòng chọn số sao đánh giá.',

            'rating.integer' =>
                'Số sao đánh giá không hợp lệ.',

            'rating.between' =>
                'Đánh giá phải từ 1 đến 5 sao.',

            'comment.max' =>
                'Nội dung đánh giá không được vượt quá 2000 ký tự.',

            'images.max' =>
                'Chỉ được tải tối đa 5 ảnh.',

            'images.*.image' =>
                'File tải lên phải là hình ảnh.',

            'images.*.mimes' =>
                'Ảnh chỉ nhận JPG, JPEG, PNG hoặc WEBP.',

            'images.*.max' =>
                'Mỗi ảnh không được lớn hơn 4MB.',
        ];
    }
}