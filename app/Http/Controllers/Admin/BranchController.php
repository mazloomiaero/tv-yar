<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Exceptions\CatalogDeletionException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreBranchRequest;
use App\Http\Requests\Admin\UpdateBranchRequest;
use App\Models\Branch;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class BranchController extends Controller
{
    public function index(): View
    {
        return view('admin.branches.index', [
            'title' => 'Branches',
            'branches' => Branch::query()->orderBy('name')->get(),
        ]);
    }

    public function create(): View
    {
        return view('admin.branches.create', [
            'title' => 'Create branch',
        ]);
    }

    public function store(StoreBranchRequest $request): RedirectResponse
    {
        $branch = Branch::query()->create($request->catalogPayload());

        return redirect()
            ->route('admin.branches.edit', $branch)
            ->with('status', 'Branch created.');
    }

    public function edit(Branch $branch): View
    {
        return view('admin.branches.edit', [
            'title' => 'Edit branch',
            'branch' => $branch,
        ]);
    }

    public function update(UpdateBranchRequest $request, Branch $branch): RedirectResponse
    {
        $branch->fill($request->catalogPayload());
        $branch->save();

        return redirect()
            ->route('admin.branches.edit', $branch)
            ->with('status', 'Branch updated.');
    }

    public function destroy(Branch $branch): RedirectResponse
    {
        try {
            $branch->delete();
        } catch (CatalogDeletionException $exception) {
            return redirect()
                ->back()
                ->withErrors(['delete' => $exception->getMessage()]);
        }

        return redirect()
            ->route('admin.branches.index')
            ->with('status', 'Branch deleted.');
    }
}
