<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreStaffUserRequest;
use App\Http\Requests\Admin\UpdateStaffUserRequest;
use App\Models\Role;
use App\Models\User;
use App\Support\LoginAccount;
use App\Support\RoleName;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;

class StaffUserController extends Controller
{
    public function index(): View
    {
        return view('admin.staff.index', [
            'title' => 'Staff',
            'users' => User::query()
                ->whereHas('roles', fn ($query) => $query->whereIn('roles.name', $this->assignableRoleNames()))
                ->with('roles')
                ->orderBy('username')
                ->get(),
        ]);
    }

    public function create(): View
    {
        return view('admin.staff.create', [
            'title' => 'Create staff',
            'roles' => $this->assignableRoles(),
        ]);
    }

    public function store(StoreStaffUserRequest $request, LoginAccount $loginAccount): RedirectResponse
    {
        $payload = $request->staffPayload();

        $user = $loginAccount->createStaff(
            $payload['name'],
            $payload['username'],
            $payload['password'],
            $payload['role'],
            $payload['email'],
            $payload['first_name'],
            $payload['last_name'],
            $payload['mobile'],
        );

        return redirect()
            ->route('admin.staff.edit', $user)
            ->with('status', 'کارمند ثبت شد.');
    }

    public function edit(User $staff): View
    {
        $staff->load('roles');

        return view('admin.staff.edit', [
            'title' => 'Edit staff',
            'staff' => $staff,
            'roles' => $this->assignableRoles(),
        ]);
    }

    public function update(UpdateStaffUserRequest $request, User $staff, LoginAccount $loginAccount): RedirectResponse
    {
        $payload = $request->staffPayload();

        DB::transaction(function () use ($staff, $payload, $loginAccount): void {
            $loginAccount->update(
                $staff,
                $payload['name'],
                $payload['username'],
                $payload['password'],
                $payload['email'],
                $payload['first_name'],
                $payload['last_name'],
                $payload['mobile'],
            );

            $roleId = Role::query()->where('name', $payload['role']->value)->value('id');
            $staff->roles()->sync([(int) $roleId]);
        });

        return redirect()
            ->route('admin.staff.edit', $staff)
            ->with('status', 'کارمند به‌روز شد.');
    }

    public function destroy(User $staff): RedirectResponse
    {
        $actor = request()->user('web');

        if ($actor instanceof User && $actor->is($staff)) {
            return redirect()
                ->route('admin.staff.index')
                ->with('status', 'نمی‌توانید حساب خودتان را حذف کنید.');
        }

        $staff->delete();

        return redirect()
            ->route('admin.staff.index')
            ->with('status', 'کارمند حذف شد.');
    }

    /**
     * @return list<string>
     */
    private function assignableRoleNames(): array
    {
        return [
            RoleName::CompanyAdmin->value,
            RoleName::Operator->value,
            RoleName::TechnicalSupervisor->value,
            RoleName::Accountant->value,
        ];
    }

    /**
     * @return list<Role>
     */
    private function assignableRoles(): array
    {
        return Role::query()
            ->whereIn('name', $this->assignableRoleNames())
            ->orderBy('id')
            ->get()
            ->all();
    }
}
