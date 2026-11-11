<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Customer;
use App\Models\CustomerAddress;
use App\Models\ServiceReportPart;
use App\Models\ServiceRequest;
use Illuminate\Support\Collection;
use InvalidArgumentException;

class InvoiceService
{
    /**
     * Current report parts on the request. Copying these into invoice_items is confirmation.
     *
     * @return Collection<int, ServiceReportPart>
     */
    public function reportParts(ServiceRequest $serviceRequest): Collection
    {
        return ServiceReportPart::query()
            ->whereHas(
                'serviceReport',
                fn ($query) => $query->where('service_request_id', $serviceRequest->id),
            )
            ->orderBy('id')
            ->get();
    }

    /**
     * Snapshot live customer fields at issue time. Address text may be empty:
     * customer_addresses has no street/city columns in the source schema.
     *
     * @return array{customer_name: string, customer_mobile: string, customer_address: string}
     */
    public function customerSnapshot(Customer $customer, ?CustomerAddress $address): array
    {
        $name = trim($customer->first_name.' '.$customer->last_name);

        return [
            'customer_name' => $this->limit($name, 200),
            'customer_mobile' => $this->limit((string) $customer->mobile, 30),
            'customer_address' => $this->addressText($address),
        ];
    }

    public function addressText(?CustomerAddress $address): string
    {
        unset($address);

        return '';
    }

    /**
     * @return array{title: string, description: string, quantity: string, unit_price: string, total: string}
     */
    public function itemFromPart(ServiceReportPart $part): array
    {
        $quantity = (string) $part->quantity;
        $unitPrice = $this->money((string) $part->unit_price);

        return [
            'title' => $this->limit((string) $part->name, 150),
            'description' => '',
            'quantity' => $quantity,
            'unit_price' => $unitPrice,
            'total' => $this->lineTotal($quantity, $unitPrice),
        ];
    }

    /**
     * @param  list<string>  $lineTotals
     */
    public function subtotal(array $lineTotals): string
    {
        $sum = 0.0;

        foreach ($lineTotals as $lineTotal) {
            $sum += (float) $lineTotal;
        }

        return $this->money((string) $sum);
    }

    public function invoiceTotal(string $subtotal, string $discount, string $tax): string
    {
        $total = (float) $subtotal - (float) $discount + (float) $tax;

        if ($total < 0) {
            throw new InvalidArgumentException('Invoice total cannot be negative.');
        }

        return $this->money((string) $total);
    }

    public function money(string $amount): string
    {
        return number_format((float) $amount, 2, '.', '');
    }

    public function lineTotal(string $quantity, string $unitPrice): string
    {
        return $this->money((string) ((float) $quantity * (float) $unitPrice));
    }

    private function limit(string $value, int $max): string
    {
        if (mb_strlen($value) <= $max) {
            return $value;
        }

        return mb_substr($value, 0, $max);
    }
}
