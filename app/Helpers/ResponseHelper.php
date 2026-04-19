<?php

namespace App\Helpers;

class ResponseHelper {
    public static function send($success = true, $message = null, $data = null) {
        return response()->json([
            'success'   => $success,
            'message'   => $message, 
            'data'      => $data
        ], 200);
    }

    
    public static function unauthorized($message = null) {
        return response()->json([
            'success'   => false,
            'message'   => isset($message) ? __($message) : __('Sorry! You are not authorized to perform this action.'), 
            'data'      => 401
        ], 401);
    }

    public static function success($message = null, $data = null) {
        return response()->json([
            'success'   => true,
            'message'   => $message, 
            'data'      => $data
        ], 200);
    }

    public static function error($message = null, $data = null) {
        return response()->json([
            'success'   => false,
            'message'   => $message, 
            'data'      => $data
        ], 500);
    }

    
}