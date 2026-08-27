<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

class BaseController extends Controller
{
    // Hàm trả về khi API thành công
    public function sendResponse($data, $message = 'Success')
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data'    => $data,
        ], 200);
    }

    // Hàm trả về khi API báo lỗi
    public function sendError($error, $errorMessages = [], $code = 404)
    {
        $response = [
            'success' => false,
            'message' => $error,
        ];

        if (!empty($errorMessages)) {
            $response['errors'] = $errorMessages;
        }

        return response()->json($response, $code);
    }
}