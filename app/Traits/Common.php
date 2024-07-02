<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse as Response;

trait Common
{

    /**
     * return a success response
     *
     * @param mixed $data
     * @param integer $code
     * @param string $status
     * @return Response
     */
    final protected function success(mixed $data=[], int $code = 200, string $status = 'success'): Response
    {
        return response()->json([
            'data' => $data,
            'status' => $status
        ], $code);
    }

    /**
     * return an error response
     *
     * @param string $message
     * @param array $errors
     * @param integer $code
     * @param string $status
     * @return Response
     */
    final protected function error(string $message = 'Server Error', array $errors = ['Internal Server Error Occurred.'], int $code = 500, string $status = 'error'): Response
    {
        return response()->json([
            'message' => $message,
            'errors' => $errors,
            'status' => $status
        ], $code);
    }
}
