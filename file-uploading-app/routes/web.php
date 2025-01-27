<?php

use Illuminate\Support\Facades\Route;
use App\Events\TestMessage;
use Illuminate\Support\Facades\Log;

Route::get('/', function () {
    return view('welcome');
})->name('home');


Route::middleware(['web'])->group(function () {
    Route::get('/test-message', function () {
        try {
            \Log::info('Test message endpoint hit');
            $message = "Test message sent at " . now();
            event(new TestMessage($message));

            return response()->json([
                'status' => 'success',
                'message' => $message
            ]);
        } catch (\Exception $e) {
            \Log::error('Broadcasting error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    });
});
