<?php

declare(strict_types=1);

namespace App\Http\Controllers\Customer;

use App\Actions\Customer\UpdateCustomer;
use App\Http\Controllers\Controller;
use App\Http\Requests\Customer\UpdateProfileRequest;
use App\Models\Customer;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    public function edit(Request $request): View
    {
        $customer = $this->customer($request);
        $customer->load('user');

        return view('customer.profile.edit', [
            'title' => 'Profile',
            'customer' => $customer,
        ]);
    }

    public function update(UpdateProfileRequest $request, UpdateCustomer $updateCustomer): RedirectResponse
    {
        $customer = $this->customer($request);
        $validated = $request->validated();

        $updateCustomer->execute($customer, [
            'customer_code' => (string) $customer->customer_code,
            'first_name' => (string) $validated['first_name'],
            'last_name' => (string) $validated['last_name'],
            'mobile' => (string) $validated['mobile'],
            'mobile_secondary' => (string) ($validated['mobile_secondary'] ?? ''),
            'email' => (string) ($validated['email'] ?? ''),
            'national_id' => isset($validated['national_id']) && $validated['national_id'] !== ''
                ? (string) $validated['national_id']
                : null,
            'notes' => (string) ($customer->notes ?? ''),
            'status' => (string) $customer->status,
            'portal_username' => isset($validated['portal_username']) && $validated['portal_username'] !== ''
                ? (string) $validated['portal_username']
                : null,
            'portal_password' => isset($validated['portal_password']) && $validated['portal_password'] !== ''
                ? (string) $validated['portal_password']
                : null,
        ]);

        return redirect()
            ->route('customer.profile.edit')
            ->with('status', 'مشخصات ذخیره شد.');
    }

    private function customer(Request $request): Customer
    {
        $customer = $request->user('customer');
        if (! $customer instanceof Customer) {
            abort(403);
        }

        return $customer;
    }
}
