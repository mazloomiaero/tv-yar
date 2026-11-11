<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StorePartRequest;
use App\Http\Requests\Admin\UpdatePartRequest;
use App\Models\Part;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class PartController extends Controller
{
    public function index(): View
    {
        return view('admin.parts.index', [
            'title' => 'Parts',
            'parts' => Part::query()->orderBy('name')->get(),
        ]);
    }

    public function create(): View
    {
        return view('admin.parts.create', [
            'title' => 'Create part',
        ]);
    }

    public function store(StorePartRequest $request): RedirectResponse
    {
        $part = Part::query()->create($request->catalogPayload());

        return redirect()
            ->route('admin.parts.edit', $part)
            ->with('status', 'Part created.');
    }

    public function edit(Part $part): View
    {
        return view('admin.parts.edit', [
            'title' => 'Edit part',
            'part' => $part,
        ]);
    }

    public function update(UpdatePartRequest $request, Part $part): RedirectResponse
    {
        $part->fill($request->catalogPayload());
        $part->save();

        return redirect()
            ->route('admin.parts.edit', $part)
            ->with('status', 'Part updated.');
    }

    public function destroy(Part $part): RedirectResponse
    {
        $part->delete();

        return redirect()
            ->route('admin.parts.index')
            ->with('status', 'Part deleted.');
    }
}
