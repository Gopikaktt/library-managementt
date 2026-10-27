<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class UserController extends Controller
{
    /**
     * Authorize that the current user is an Admin or has user management permissions.
     */
    protected function authorizeAdmin(): void
    {
        $currentUser = auth()->user();
        if (!$currentUser || (!$currentUser->hasRole('Admin') && !$currentUser->can('manager users') && !$currentUser->can('manage users'))) {
            abort(403, 'Unauthorized access to user management.');
        }
    }

    /**
     * Display a listing of users, their roles, and their permissions.
     */
    public function index(Request $request)
    {
        $this->authorizeAdmin();

        $query = User::with(['roles', 'permissions'])
            ->withCount('borrowings')
            ->latest();

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->filled('role')) {
            $role = $request->input('role');
            $query->whereHas('roles', function ($q) use ($role) {
                $q->where('name', $role);
            });
        }

        $users = $query->get()->map(function ($u) {
            return [
                'id' => $u->id,
                'name' => $u->name,
                'email' => $u->email,
                'created_at' => $u->created_at,
                'borrowings_count' => $u->borrowings_count,
                'roles' => $u->getRoleNames()->values()->all(),
                'direct_permissions' => $u->getDirectPermissions()->pluck('name')->values()->all(),
                'all_permissions' => $u->getAllPermissions()->pluck('name')->values()->all(),
            ];
        });

        $allRoles = Role::with('permissions:name')->get()->map(function ($r) {
            return [
                'id' => $r->id,
                'name' => $r->name,
                'permissions' => $r->permissions->pluck('name')->values()->all(),
            ];
        });

        $allPermissions = Permission::orderBy('name')->pluck('name')->values()->all();

        return Inertia::render('Users/Index', [
            'users' => $users,
            'allRoles' => $allRoles,
            'allPermissions' => $allPermissions,
            'filters' => $request->only(['search', 'role']),
        ]);
    }

    /**
     * Store a newly created user with role and permissions.
     */
    public function store(Request $request)
    {
        $this->authorizeAdmin();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email',
            'password' => 'required|string|min:8',
            'role' => 'required|exists:roles,name',
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,name',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        // Assign Role
        $user->assignRole($validated['role']);

        // Sync Direct Permissions if provided
        if (!empty($validated['permissions'])) {
            $user->syncPermissions($validated['permissions']);
        }

        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        return redirect()->back()->with('success', "User '{$user->name}' created successfully.");
    }

    /**
     * Update the specified user, their role, and their permissions.
     */
    public function update(Request $request, User $user)
    {
        $this->authorizeAdmin();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'password' => 'nullable|string|min:8',
            'role' => 'required|exists:roles,name',
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,name',
        ]);

        $currentUserId = auth()->id();

        // Safety guard: prevent logged-in admin from demoting themselves from Admin role
        if ($user->id === $currentUserId && $validated['role'] !== 'Admin') {
            return redirect()->back()->with('error', 'You cannot remove your own Admin role.');
        }

        $user->name = $validated['name'];
        $user->email = $validated['email'];

        if (!empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }

        $user->save();

        // Sync Role
        $user->syncRoles([$validated['role']]);

        // Sync Direct Permissions
        $user->syncPermissions($validated['permissions'] ?? []);

        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        return redirect()->back()->with('success', "User '{$user->name}' and permissions updated successfully.");
    }

    /**
     * Remove the specified user.
     */
    public function destroy(User $user)
    {
        $this->authorizeAdmin();

        $currentUserId = auth()->id();

        if ($user->id === $currentUserId) {
            return redirect()->back()->with('error', 'You cannot delete your own account.');
        }

        // Check active borrowings
        $hasActiveBorrowings = $user->borrowings()->where('status', 'borrowed')->exists();
        if ($hasActiveBorrowings) {
            return redirect()->back()->with('error', "Cannot delete '{$user->name}' because they have active borrowed books.");
        }

        $name = $user->name;
        $user->delete();

        return redirect()->back()->with('success', "User '{$name}' deleted successfully.");
    }
}
