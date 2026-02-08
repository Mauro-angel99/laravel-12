<?php

namespace App\Http\Controllers;

use App\Models\FilePathSetting;
use Illuminate\Http\Request;

class FilePathSettingController extends Controller
{
    public function index()
    {
        $setting = FilePathSetting::first();

        return response()->json($setting ?? [
            'pdf_path' => '',
            'image_path' => '',
        ]);
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'pdf_path' => 'nullable|string',
            'image_path' => 'nullable|string',
        ]);

        $setting = FilePathSetting::first();

        if (!$setting) {
            $setting = FilePathSetting::create($validated);
        } else {
            $setting->update($validated);
        }

        return response()->json([
            'message' => 'Percorsi aggiornati con successo',
            'data' => $setting,
        ]);
    }
}
