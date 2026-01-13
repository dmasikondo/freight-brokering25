<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use App\Models\User;
use App\Models\ActivityLog;
use App\Models\Buslocation;
use App\Policies\UserPolicy;
use App\Actions\ApproveUserAction;



class UserController extends Controller
{
    public function index()
    {
        Gate::authorize('viewAny', auth()->user());
        return view('users.index');
    }

    public function create()
    {
        $authenticatedUser = auth()->user();
        Gate::authorize('create', $authenticatedUser);
        return view('users.create');
    }

    public function edit(User $user)
    {
        $slug = $user->slug;
        $authenticatedUser = auth()->user();
        Gate::authorize('create', $authenticatedUser);
        return view('users.edit', compact('slug'));
    }

    public function show(User $user, ApproveUserAction $approveAction)
    {
        Gate::authorize('view', $user);

        // 1. Determine UI Capability States
        $isShipper = $user->hasRole('shipper');
        $isCarrier = $user->hasRole('carrier');
        $isAdmin = auth()->user()->hasAnyRole(['admin', 'superadmin']);
        $isLeadRole = $user->hasAnyRole([
            'marketing logistics associate',
            'procurement logistics associate',
            'operations logistics associate',
            'logistics operations executive'
        ]);

        // 2. Resolve Profile Theme (Identity Hub Design)
        $role = $user->roles->first();
        $roleName = $role?->name;
        $theme = match ($roleName) {
            'shipper' => ['bg' => 'bg-blue-100', 'text' => 'text-blue-600', 'icon' => 'cube'],
            'carrier' => ['bg' => 'bg-emerald-100', 'text' => 'text-emerald-600', 'icon' => 'truck'],
            'marketing logistics associate' => ['bg' => 'bg-purple-100', 'text' => 'text-purple-600', 'icon' => 'megaphone'],
            'procurement logistics associate' => ['bg' => 'bg-orange-100', 'text' => 'text-orange-600', 'icon' => 'clipboard-document-list'],
            'operations logistics associate' => ['bg' => 'bg-sky-100', 'text' => 'text-sky-600', 'icon' => 'cursor-arrow-ripple'],
            'logistics operations executive' => ['bg' => 'bg-teal-100', 'text' => 'text-teal-600', 'icon' => 'archive-box'],
            'admin' => ['bg' => 'bg-emerald-50', 'text' => 'text-emerald-600', 'icon' => 'shield-check'],
            default => ['bg' => 'bg-zinc-100', 'text' => 'text-zinc-600', 'icon' => 'user']
        };

        // 3. Aggregate Temporal Trace (Audit Logs for User + Geospatial Links)
        $busLocationIds = $user->buslocation->pluck('id');
        $activityLogs = ActivityLog::where(function ($q) use ($user) {
            $q->where('auditable_type', User::class)->where('auditable_id', $user->id);
        })
            ->orWhere(function ($q) use ($busLocationIds) {
                $q->where('auditable_type', BusLocation::class)->whereIn('auditable_id', $busLocationIds);
            })
            ->with('actor')
            ->latest()
            ->get();

        $approvalStatus =   $approveAction->validate($user);
        return view('users.show', compact(
            'user',
            'isShipper',
            'isCarrier',
            'isAdmin',
            'isLeadRole',
            'theme',
            'activityLogs',
            'approvalStatus',
        ));
    }


    public function suspend(Request $request, User $user)
    {
        Gate::authorize('suspend', $user);

        $request->validate([
            'suspension_reason' => 'required|string|max:255',
        ]);

        $user->update([
            'suspended_at' => now(),
            'suspension_reason' => $request->suspension_reason,
            'suspended_by_id' => auth()->id(),
        ]);

        return back()->with('status', 'User account has been suspended.');
    }

    public function unsuspend(User $user)
    {
        Gate::authorize('update', $user);

        $user->update([
            'suspended_at' => null,
            'suspension_reason' => null,
        ]);

        return back()->with('status', 'User account access has been restored.');
    }




    /**
     * Approve the specified user.
     */
    public function approve(User $user, ApproveUserAction $approveUserAction)
    {
        // 1. Check Authorization (Permissions)
        Gate::authorize('view', $user);

        try {
            // 2. Delegate the heavy lifting to the Action
            // We pass the user and the ID of the person doing the approving
            $approveUserAction->execute($user, auth()->id());

            // 3. Return success response
            return back()->with('success', 'User has been approved and notified.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            // 4. Catch validation errors (like missing fleet info) and send them back to the UI
            return back()->withErrors($e->validator->getMessageBag())->withInput();
        } catch (\Exception $e) {
            // 5. Catch unexpected system errors
            return back()->with('error', 'An unexpected error occurred: ' . $e->getMessage());
        }
    }
}
