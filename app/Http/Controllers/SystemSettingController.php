<?php

namespace App\Http\Controllers;

use App\Models\SystemSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SystemSettingController extends Controller
{
    public function index(): View
    {
        return view('settings.index', [
            'settingsByGroup' => SystemSetting::orderBy('setting_group')
                ->orderBy('label')
                ->get()
                ->groupBy('setting_group'),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $settings = SystemSetting::orderBy('setting_group')->orderBy('label')->get();

        foreach ($settings as $setting) {
            $value = $setting->type === 'boolean'
                ? ($request->boolean("settings.{$setting->key}") ? '1' : '0')
                : $request->input("settings.{$setting->key}");

            validator(
                ['value' => $value],
                ['value' => $this->rulesFor($setting)]
            )->validate();

            $setting->update(['value' => $value]);
        }

        return back()->with('success', 'System settings updated.');
    }

    protected function rulesFor(SystemSetting $setting): array
    {
        return match ($setting->type) {
            'email' => ['nullable', 'email', 'max:255'],
            'number' => ['nullable', 'numeric', 'min:0'],
            'boolean' => ['required', 'in:0,1'],
            default => ['nullable', 'string', 'max:1000'],
        };
    }
}
