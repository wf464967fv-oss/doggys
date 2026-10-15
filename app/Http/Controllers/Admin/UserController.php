<?php

namespace App\Http\Controllers\Admin;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\AdminAction;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'role' => ['nullable', Rule::enum(UserRole::class)],
        ]);

        return view('admin.users.index', [
            'filters' => $filters,
            'users' => User::query()
                ->when($filters['q'] ?? null, fn ($query, string $search) => $query->where(fn ($query) => $query
                    ->where('name', 'like', '%'.$search.'%')->orWhere('email', 'like', '%'.$search.'%')))
                ->when($filters['role'] ?? null, fn ($query, string $role) => $query->where('role', $role))
                ->withCount(['reviews', 'claimedProviders'])
                ->latest()->paginate(30)->withQueryString(),
        ]);
    }

    public function updateRole(Request $request, User $user): RedirectResponse
    {
        abort_if($user->is($request->user()), 403);

        $role = UserRole::from($request->validate(['role' => ['required', Rule::enum(UserRole::class)]])['role']);
        $previous = $user->role;
        $user->forceFill(['role' => $role])->save();
        AdminAction::record('user.role', $user, $previous->value.' → '.$role->value);

        return back()->with('status', __('admin.users.role_updated'));
    }

    /**
     * Same erasure as a self-service account deletion (User::purge()).
     */
    public function destroy(Request $request, User $user): RedirectResponse
    {
        abort_if($user->is($request->user()), 403);

        AdminAction::record('user.delete', $user, $user->email);
        $user->purge();

        return back()->with('status', __('admin.users.deleted'));
    }
}
