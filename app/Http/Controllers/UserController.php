<?php

namespace App\Http\Controllers;

use App\Enums\Config as ConfigEnum;
use App\Models\Config;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return View
     */
    public function index(Request $request): View
    {
        return view('pages.user', [
            'data' => User::render($request->search),
            'search' => $request->search,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return RedirectResponse
     */
    public function store(Request $request): RedirectResponse
    {
        try {
            // Validasi input langsung
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:users,email',
                'phone' => 'required|string|max:15',
                'password' => 'sometimes|nullable|min:8|confirmed',
            ]);

            // Set password untuk user baru (default atau yang diberikan)
            $validated['password'] = isset($validated['password']) && auth()->user()->role === 'admin'
                ? Hash::make($validated['password'])
                : Hash::make(Config::getValueByCode(ConfigEnum::DEFAULT_PASSWORD));

            User::create($validated);
            return back()->with('success', __('menu.general.success'));
        } catch (\Throwable $exception) {
            return back()->with('error', $exception->getMessage());
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param User $user
     * @return RedirectResponse
     */
    public function update(Request $request, User $user): RedirectResponse
    {
        try {
            // Validasi inputan yang masuk
            $validated = $request->validate([
                'name' => 'sometimes|string|max:255',
                'email' => 'sometimes|email|unique:users,email,' . $user->id,
                'phone' => 'sometimes|string|max:15',
                'status' => 'sometimes|string|in:aktif,non aktif', // Validasi status
                'password' => 'sometimes|nullable|min:8|confirmed',
            ]);

            // Jika password diubah, hash password baru
            if ($request->filled('password')) {
                $validated['password'] = Hash::make($request->password);
            }

            // Pastikan status hanya berubah jika ada perubahan
            if ($request->filled('status')) {
                $validated['status'] = $request->input('status');
            } else {
                // Jika tidak ada perubahan status, tetap gunakan status yang lama
                unset($validated['status']);
            }

            // Update user dengan data baru
            $user->update($validated);

            return back()->with('success', __('menu.general.success'));
        } catch (\Throwable $exception) {
            return back()->with('error', $exception->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param User $user
     * @return RedirectResponse
     * @throws \Exception
     */
    public function destroy(User $user): RedirectResponse
    {
        try {
            $user->delete();
            return back()->with('success', __('menu.general.success'));
        } catch (\Throwable $exception) {
            return back()->with('error', $exception->getMessage());
        }
    }
}
