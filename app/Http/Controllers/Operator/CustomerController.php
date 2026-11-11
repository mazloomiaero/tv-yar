<?php

declare(strict_types=1);

namespace App\Http\Controllers\Operator;

use App\Actions\Customer\CreateCustomer;
use App\Actions\Customer\UpdateCustomer;
use App\Http\Controllers\Controller;
use App\Http\Requests\Operator\SearchCustomerRequest;
use App\Http\Requests\Operator\StoreCustomerRequest;
use App\Http\Requests\Operator\UpdateCustomerRequest;
use App\Models\Customer;
use App\Support\OpsRoute;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    use AuthorizesRequests;

    public function search(SearchCustomerRequest $request): View
    {
        $validated = $request->validated();
        $query = trim((string) ($validated['q'] ?? $validated['mobile'] ?? ''));
        $searched = $query !== '';
        $phonePrefill = $this->phonePrefill($query);

        $customers = $searched
            ? $this->matchingCustomers($query)
            : Customer::query()->orderByDesc('id')->limit(100)->get();

        return view('operator.customers.search', [
            'title' => 'Search customers',
            'query' => $query,
            'mobile' => $phonePrefill,
            'searched' => $searched,
            'customers' => $customers,
        ]);
    }

    public function create(Request $request): View
    {
        $this->authorize('create', Customer::class);

        return view('operator.customers.create', [
            'title' => 'Create customer',
            'mobile' => (string) $request->query('mobile', ''),
        ]);
    }

    public function store(StoreCustomerRequest $request, CreateCustomer $createCustomer): RedirectResponse
    {
        $customer = $createCustomer->execute($this->customerAttributes($request->validated()));

        return redirect()
            ->route(OpsRoute::name('customers.edit'), $customer)
            ->with('status', 'Customer created.');
    }

    public function edit(Customer $customer): View
    {
        $this->authorize('update', $customer);

        $customer->load(['devices.deviceType', 'devices.brand', 'serviceRequests.status', 'user']);

        return view('operator.customers.edit', [
            'title' => 'Edit customer',
            'customer' => $customer,
        ]);
    }

    public function update(
        UpdateCustomerRequest $request,
        Customer $customer,
        UpdateCustomer $updateCustomer,
    ): RedirectResponse {
        $updateCustomer->execute($customer, $this->customerAttributes($request->validated()));

        return redirect()
            ->route(OpsRoute::name('customers.edit'), $customer)
            ->with('status', 'Customer updated.');
    }

    /**
     * @param  array<string, mixed>  $validated
     * @return array{
     *     customer_code: string,
     *     first_name: string,
     *     last_name: string,
     *     mobile: string,
     *     mobile_secondary: string,
     *     email: string,
     *     national_id: ?string,
     *     notes: string,
     *     status: string,
     *     portal_username: ?string,
     *     portal_password: ?string
     * }
     */
    private function customerAttributes(array $validated): array
    {
        return [
            'customer_code' => (string) $validated['customer_code'],
            'first_name' => (string) $validated['first_name'],
            'last_name' => (string) $validated['last_name'],
            'mobile' => (string) $validated['mobile'],
            'mobile_secondary' => (string) ($validated['mobile_secondary'] ?? ''),
            'email' => (string) ($validated['email'] ?? ''),
            'national_id' => isset($validated['national_id']) && $validated['national_id'] !== ''
                ? (string) $validated['national_id']
                : null,
            'notes' => (string) ($validated['notes'] ?? ''),
            'status' => (string) $validated['status'],
            'portal_username' => isset($validated['portal_username']) && $validated['portal_username'] !== ''
                ? (string) $validated['portal_username']
                : null,
            'portal_password' => isset($validated['portal_password']) && $validated['portal_password'] !== ''
                ? (string) $validated['portal_password']
                : null,
        ];
    }

    /**
     * @return Collection<int, Customer>
     */
    private function matchingCustomers(string $query)
    {
        $like = '%'.$this->likeValue($query).'%';

        return Customer::query()
            ->where(function ($builder) use ($like, $query): void {
                $builder
                    ->where('mobile', 'like', $like)
                    ->orWhere('first_name', 'like', $like)
                    ->orWhere('last_name', 'like', $like);

                $parts = preg_split('/\s+/u', $query, 2, PREG_SPLIT_NO_EMPTY);

                if (is_array($parts) && count($parts) === 2) {
                    $first = '%'.$this->likeValue($parts[0]).'%';
                    $last = '%'.$this->likeValue($parts[1]).'%';

                    $builder->orWhere(function ($name) use ($first, $last): void {
                        $name->where('first_name', 'like', $first)
                            ->where('last_name', 'like', $last);
                    });
                }
            })
            ->orderBy('id')
            ->get();
    }

    private function likeValue(string $value): string
    {
        return addcslashes($value, '%_\\');
    }

    private function phonePrefill(string $query): string
    {
        $digits = preg_replace('/\D+/', '', $query) ?? '';

        return strlen($digits) >= 8 ? $query : '';
    }
}
