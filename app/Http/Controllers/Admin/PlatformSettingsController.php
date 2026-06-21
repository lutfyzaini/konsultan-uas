<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PlatformSetting;
use Illuminate\Http\Request;

class PlatformSettingsController extends Controller
{
    public function index()
    {
        $settings = PlatformSetting::all();
        return view('admin.settings.index', compact('settings'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'settings' => 'required|array',
            'settings.*' => 'required|string',
        ]);

        foreach ($request->settings as $key => $value) {
            PlatformSetting::where('key', $key)->update(['value' => $value]);
        }

        return redirect()->route('admin.settings.index')->with('success', 'Pengaturan platform berhasil diperbarui.');
    }
}
