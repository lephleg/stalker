<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use \Exception;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    /**
     * Logs any exceptions and handles the error returns as JSON
     * @param Exception $e
     * @param string $message
     * @param int $code
     * @return \Illuminate\Http\JsonResponse
     */
    protected function returnErrorJson(string $message, int $code, Exception $e = null)
    {
        // if there's an exception write an error log entry
        $e and \Log::error($e->getLine() . '-' . $e->getMessage());

        return response()->json([
            'status' => 'error',
            'message' => $message
        ], $code);
    }

    /**
     * Handles success return JSON messages
     * @param string $message
     * @param int $code
     * @return \Illuminate\Http\JsonResponse
     */
    protected function returnSuccessJson(string $message, int $code)
    {
        return response()->json([
            'status' => 'success',
            'message' => $message
        ], $code);
    }

}
