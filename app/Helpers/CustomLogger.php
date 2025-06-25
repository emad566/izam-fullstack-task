<?php
namespace App\Helpers;

use Illuminate\Support\Facades\Log;

class CustomLogger
{
    public static function info($fileName, $message, $context = [], $isLog = false)
    {
        if(!$isLog)return;

        // Set the log file path
        $logFilePath = storage_path('logs/' . $fileName);

        // // Create a log channel dynamically
        Log::build([
            'driver' => 'single',
            'path' => $logFilePath,
            'level' => 'info',
        ])->info($message, $context);
    }

    public static function error($fileName, $message, $context = [], $isLog = true)
    {
        if(!$isLog)return;

        // Set the log file path
        $logFilePath = storage_path('logs/' . $fileName);

        // // Create a log channel dynamically
        Log::build([
            'driver' => 'single',
            'path' => $logFilePath,
            'level' => 'info',
        ])->info($message, $context);
    }
}
