<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Exceptions\CatalogDeletionException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreBrandRequest;
use App\Http\Requests\Admin\UpdateBrandRequest;
use App\Models\Brand;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class BrandController extends Controller
{
    public function index(): View
    {
        return view('admin.brands.index', [
            'title' => 'Brands',
            'brands' => Brand::query()->orderBy('name')->get(),
        ]);
    }

    public function create(): View
    {
        return view('admin.brands.create', [
            'title' => 'Create brand',
        ]);
    }

    public function store(StoreBrandRequest $request): RedirectResponse
    {
        $brand = Brand::query()->create([
            'name' => $request->validated('name'),
        ]);

        return redirect()
            ->route('admin.brands.edit', $brand)
            ->with('status', 'Brand created.');
    }

    public function edit(Brand $brand): View
    {
        return view('admin.brands.edit', [
            'title' => 'Edit brand',
            'brand' => $brand,
        ]);
    }

    public function update(UpdateBrandRequest $request, Brand $brand): RedirectResponse
    {
        $brand->fill(['name' => $request->validated('name')]);
        $brand->save();

        return redirect()
            ->route('admin.brands.edit', $brand)
            ->with('status', 'Brand updated.');
    }

    public function destroy(Brand $brand): RedirectResponse
    {
        try {
            $brand->delete();
        } catch (CatalogDeletionException $exception) {
            return redirect()
                ->back()
                ->withErrors(['delete' => $exception->getMessage()]);
        }

        return redirect()
            ->route('admin.brands.index')
            ->with('status', 'Brand deleted.');
    }
}
