<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class SettingController extends Controller
{
    public function edit(): View
    {
        $settings = Setting::allCached();

        return view('admin.settings.edit', [
            'settings' => $settings,
            'logoUrl' => Setting::siteLogoUrl(),
            'qrisUrl' => Setting::qrisUrl(),
            'donationTotal' => Setting::donationTotal(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'site_name' => ['required', 'string', 'max:120'],
            'meta_description' => ['nullable', 'string', 'max:180'],
            'bank_info' => ['nullable', 'string', 'max:255'],
            'wa_bendahara' => ['required', 'string', 'max:30'],
            'site_logo' => ['nullable', 'image', 'max:2048'],
            'remove_logo' => ['nullable', 'boolean'],
            'qris_image' => ['nullable', 'image', 'max:2048'],
            'remove_qris' => ['nullable', 'boolean'],
        ]);

        $pairs = [
            'site_name' => $data['site_name'],
            'meta_description' => trim($data['meta_description'] ?? ''),
            'bank_info' => $data['bank_info'] ?? '',
            'wa_bendahara' => preg_replace('/\D+/', '', $data['wa_bendahara']),
        ];

        if ($request->boolean('remove_logo')) {
            $old = Setting::getValue('site_logo');
            if ($old) {
                Storage::disk('public')->delete($old);
            }
            $pairs['site_logo'] = null;
        }

        if ($request->hasFile('site_logo')) {
            $old = Setting::getValue('site_logo');
            if ($old) {
                Storage::disk('public')->delete($old);
            }
            $pairs['site_logo'] = $request->file('site_logo')->store('branding', 'public');
        }

        if ($request->boolean('remove_qris')) {
            $old = Setting::getValue('qris_image');
            if ($old) {
                Storage::disk('public')->delete($old);
            }
            $pairs['qris_image'] = null;
        }

        if ($request->hasFile('qris_image')) {
            $old = Setting::getValue('qris_image');
            if ($old) {
                Storage::disk('public')->delete($old);
            }
            $pairs['qris_image'] = $request->file('qris_image')->store('branding', 'public');
        }

        Setting::setMany($pairs);

        return back()->with('success', 'Pengaturan berhasil disimpan.');
    }
}
