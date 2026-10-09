<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\Institutions;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(): View
    {
        return view('admin.users.index', [
            'users' => User::query()->orderBy('name')->get(),
        ]);
    }

    public function create(): View
    {
        return view('admin.users.form', [
            'user' => new User(['role' => User::ROLE_KOORDINATOR, 'institution' => 'SMA']),
            'institutions' => Institutions::options(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);

        User::create([
            ...$data,
            'email_verified_at' => now(),
        ]);

        return redirect()->route('admin.users.index')->with('success', 'Pengguna berhasil ditambahkan.');
    }

    public function edit(User $user): View
    {
        return view('admin.users.form', [
            'user' => $user,
            'institutions' => Institutions::options(),
        ]);
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $data = $this->validated($request, $user);

        if ($data['password'] === null) {
            unset($data['password']);
        }

        if ($user->isInti() && $data['role'] !== User::ROLE_INTI && $this->intiCount() <= 1) {
            return back()->withErrors(['role' => 'Harus tersisa minimal satu panitia inti.'])->withInput();
        }

        $user->update($data);

        return redirect()->route('admin.users.index')->with('success', 'Pengguna berhasil diperbarui.');
    }

    public function destroy(User $user): RedirectResponse
    {
        if ($user->is(request()->user())) {
            return back()->with('error', 'Akun yang sedang dipakai tidak dapat dihapus dari sini.');
        }

        if ($user->isInti() && $this->intiCount() <= 1) {
            return back()->with('error', 'Panitia inti terakhir tidak dapat dihapus.');
        }

        $user->delete();

        return redirect()->route('admin.users.index')->with('success', 'Pengguna dihapus.');
    }

    private function intiCount(): int
    {
        return User::query()->where('role', User::ROLE_INTI)->count();
    }

    private function validated(Request $request, ?User $user = null): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user?->id)],
            'role' => ['required', Rule::in([User::ROLE_INTI, User::ROLE_KOORDINATOR])],
            'institution' => ['nullable', Rule::in(Institutions::options())],
            'password' => [$user ? 'nullable' : 'required', 'confirmed', Password::defaults()],
        ]);

        if ($data['role'] === User::ROLE_KOORDINATOR && empty($data['institution'])) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'institution' => 'Koordinator wajib dipilih lembaganya.',
            ]);
        }

        if ($data['role'] === User::ROLE_INTI) {
            $data['institution'] = null;
        }

        if (($data['password'] ?? null) === null) {
            $data['password'] = null;
        }

        return $data;
    }
}
