<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    public function create(): View
    {
        return view('auth.register', [
            'roleOptions' => $this->roleOptions(),
        ]);
    }

    public function store(RegisterRequest $request): RedirectResponse
    {
        $user = User::create([
            'role_id' => $request->roleId(),
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => $request->password,
        ]);

        Auth::login($user);

        return redirect(route('dashboard'));
    }

    private function roleOptions(): Collection
    {
        return collect(config('epms.auth_roles'))
            ->map(fn (string $label, string $slug) => [
                'slug' => $slug,
                'label' => $label,
            ])
            ->values();
    }
}
