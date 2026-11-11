<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreTechnicianRequest;
use App\Http\Requests\Admin\UpdateTechnicianRequest;
use App\Models\Branch;
use App\Models\Skill;
use App\Models\Technician;
use App\Support\LoginAccount;
use App\Support\RoleName;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;

class TechnicianController extends Controller
{
    public function index(): View
    {
        return view('admin.technicians.index', [
            'title' => 'Technicians',
            'technicians' => Technician::query()->with(['branch', 'user'])->orderBy('name')->get(),
        ]);
    }

    public function create(): View
    {
        return view('admin.technicians.create', [
            'title' => 'Create technician',
            'branches' => Branch::query()->orderBy('name')->get(),
            'skills' => Skill::query()->orderBy('name')->get(),
        ]);
    }

    public function store(StoreTechnicianRequest $request, LoginAccount $loginAccount): RedirectResponse
    {
        $payload = $request->catalogPayload();

        $technician = DB::transaction(function () use ($payload, $loginAccount) {
            $user = $loginAccount->createStaff(
                $payload['name'],
                $payload['username'],
                $payload['password'],
                RoleName::Technician,
                null,
                $payload['first_name'],
                $payload['last_name'],
                $payload['mobile'],
            );

            $technician = Technician::query()->create([
                'name' => $payload['name'],
                'branch_id' => $payload['branch_id'],
                'user_id' => $user->id,
            ]);

            $technician->skills()->sync($payload['skill_ids']);

            return $technician;
        });

        return redirect()
            ->route('admin.technicians.edit', $technician)
            ->with('status', 'تکنسین ثبت شد.');
    }

    public function edit(Technician $technician): View
    {
        $technician->load(['skills', 'user']);

        return view('admin.technicians.edit', [
            'title' => 'Edit technician',
            'technician' => $technician,
            'branches' => Branch::query()->orderBy('name')->get(),
            'skills' => Skill::query()->orderBy('name')->get(),
        ]);
    }

    public function update(
        UpdateTechnicianRequest $request,
        Technician $technician,
        LoginAccount $loginAccount,
    ): RedirectResponse {
        $payload = $request->catalogPayload();

        DB::transaction(function () use ($technician, $payload, $loginAccount): void {
            $user = $technician->user;

            if ($user === null) {
                $user = $loginAccount->createStaff(
                    $payload['name'],
                    $payload['username'],
                    (string) $payload['password'],
                    RoleName::Technician,
                    null,
                    $payload['first_name'],
                    $payload['last_name'],
                    $payload['mobile'],
                );
            } else {
                $loginAccount->update(
                    $user,
                    $payload['name'],
                    $payload['username'],
                    $payload['password'],
                    null,
                    $payload['first_name'],
                    $payload['last_name'],
                    $payload['mobile'],
                );
            }

            $technician->fill([
                'name' => $payload['name'],
                'branch_id' => $payload['branch_id'],
                'user_id' => $user->id,
            ]);
            $technician->save();
            $technician->skills()->sync($payload['skill_ids']);
        });

        return redirect()
            ->route('admin.technicians.edit', $technician)
            ->with('status', 'تکنسین به‌روز شد.');
    }

    public function destroy(Technician $technician): RedirectResponse
    {
        $technician->delete();

        return redirect()
            ->route('admin.technicians.index')
            ->with('status', 'تکنسین حذف شد.');
    }
}
