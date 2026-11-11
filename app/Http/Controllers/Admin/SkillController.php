<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreSkillRequest;
use App\Http\Requests\Admin\UpdateSkillRequest;
use App\Models\Skill;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class SkillController extends Controller
{
    public function index(): View
    {
        return view('admin.skills.index', [
            'title' => 'Skills',
            'skills' => Skill::query()->orderBy('name')->get(),
        ]);
    }

    public function create(): View
    {
        return view('admin.skills.create', [
            'title' => 'Create skill',
        ]);
    }

    public function store(StoreSkillRequest $request): RedirectResponse
    {
        $skill = Skill::query()->create([
            'name' => $request->validated('name'),
        ]);

        return redirect()
            ->route('admin.skills.edit', $skill)
            ->with('status', 'Skill created.');
    }

    public function edit(Skill $skill): View
    {
        return view('admin.skills.edit', [
            'title' => 'Edit skill',
            'skill' => $skill,
        ]);
    }

    public function update(UpdateSkillRequest $request, Skill $skill): RedirectResponse
    {
        $skill->fill(['name' => $request->validated('name')]);
        $skill->save();

        return redirect()
            ->route('admin.skills.edit', $skill)
            ->with('status', 'Skill updated.');
    }

    public function destroy(Skill $skill): RedirectResponse
    {
        $skill->delete();

        return redirect()
            ->route('admin.skills.index')
            ->with('status', 'Skill deleted.');
    }
}
