<?php

namespace App\Http\Controllers\Traits;

use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Log;

trait HandlesFormErrors
{
    /**
     * Handle validation errors and return appropriate response
     */
    protected function handleValidationErrors(ValidationException $e, $request)
    {
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => false,
                'errors' => $e->errors()
            ], 422);
        }
        
        return redirect()->back()
            ->withErrors($e->errors())
            ->withInput();
    }

    /**
     * Handle general exceptions and return appropriate response
     */
    protected function handleException(\Exception $e, $request, $context = 'data')
    {
        Log::error("Error saving {$context}: " . $e->getMessage(), [
            'exception' => $e,
            'request_data' => $request->except(['_token', 'primary_photo']),
            'trace' => $e->getTraceAsString()
        ]);
        
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => false,
                'message' => "Terjadi kesalahan saat menyimpan {$context}: " . $e->getMessage()
            ], 500);
        }
        
        return redirect()->back()
            ->with('error', "Terjadi kesalahan saat menyimpan {$context}: " . $e->getMessage())
            ->withInput();
    }
}


