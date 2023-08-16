<?php

if (! function_exists('Json')) {
    function Json($success, $data = null, $message = null)
    {
        $result = [
            'success' => $success
            ,'data' => $data
            ,'message' => $message ?? ($success ? __('return.success') : __('return.failed'))
        ];

        return response()->json($result, 200, [], JSON_UNESCAPED_UNICODE);
    }
}
