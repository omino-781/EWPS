@extends('layouts.app')

@section('title', 'System Settings')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="mb-1">System Settings</h2>
        <p class="text-muted mb-0">Configure core EPMS defaults used across events, communication, and finance.</p>
    </div>
</div>

<form method="POST" action="{{ route('settings.update') }}">
    @csrf
    @method('PATCH')

    <div class="row g-4">
        @foreach($settingsByGroup as $group => $settings)
        <div class="col-lg-6">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0 text-capitalize">{{ str_replace('_', ' ', $group) }}</h5>
                </div>
                <div class="card-body">
                    @foreach($settings as $setting)
                    <div class="mb-3">
                        @if($setting->type === 'boolean')
                        <div class="form-check form-switch">
                            <input type="hidden" name="settings[{{ $setting->key }}]" value="0">
                            <input
                                class="form-check-input"
                                type="checkbox"
                                role="switch"
                                id="setting_{{ $setting->key }}"
                                name="settings[{{ $setting->key }}]"
                                value="1"
                                {{ old("settings.{$setting->key}", $setting->value) ? 'checked' : '' }}
                            >
                            <label class="form-check-label fw-semibold" for="setting_{{ $setting->key }}">
                                {{ $setting->label }}
                            </label>
                        </div>
                        @else
                        <label class="form-label" for="setting_{{ $setting->key }}">{{ $setting->label }}</label>
                        <input
                            type="{{ $setting->type === 'number' ? 'number' : ($setting->type === 'email' ? 'email' : 'text') }}"
                            name="settings[{{ $setting->key }}]"
                            id="setting_{{ $setting->key }}"
                            class="form-control @error('value') is-invalid @enderror"
                            value="{{ old("settings.{$setting->key}", $setting->value) }}"
                            {{ $setting->type === 'number' ? 'min=0 step=1' : '' }}
                        >
                        @endif
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endforeach
    </div>

    <div class="mt-4">
        <button type="submit" class="btn btn-primary px-4">
            <i class="bi bi-save me-1"></i> Save Settings
        </button>
    </div>
</form>
@endsection
