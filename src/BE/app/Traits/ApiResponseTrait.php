<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;

trait ApiResponseTrait
{
    /**
     * Chuẩn hóa phản hồi thành công (Success Response).
     *
     * @param mixed $data
     * @param string $message
     * @param int $status
     * @param mixed $meta
     * @return JsonResponse
     */
    public function successResponse($data = null, string $message = 'Xử lý yêu cầu thành công.', int $status = 200, $meta = null): JsonResponse
    {
        $response = [
            'success' => true,
            'status' => $status,
            'message' => $message,
        ];

        if ($data !== null) {
            $response['data'] = $data;
        }

        if ($meta !== null) {
            $response['meta'] = $meta;
        }

        return response()->json($response, $status);
    }

    /**
     * Chuẩn hóa phản hồi lỗi (Error Response).
     *
     * @param string $message
     * @param int $status
     * @param mixed $errors
     * @return JsonResponse
     */
    public function errorResponse(string $message = 'Đã có lỗi xảy ra.', int $status = 400, $errors = null): JsonResponse
    {
        $response = [
            'success' => false,
            'status' => $status,
            'message' => $message,
        ];

        if ($errors !== null) {
            $response['errors'] = $errors;
        }

        return response()->json($response, $status);
    }
}
