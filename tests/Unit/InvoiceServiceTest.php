<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\Customer;
use App\Models\CustomerAddress;
use App\Models\ServiceReportPart;
use App\Services\InvoiceService;
use InvalidArgumentException;
use Tests\TestCase;

class InvoiceServiceTest extends TestCase
{
    public function test_it_snapshots_customer_name_and_mobile_and_empty_address_text(): void
    {
        $service = new InvoiceService;
        $customer = new Customer([
            'first_name' => 'Sara',
            'last_name' => 'Karimi',
            'mobile' => '09120000001',
        ]);
        $address = new CustomerAddress(['customer_id' => 1]);

        $snapshot = $service->customerSnapshot($customer, $address);

        $this->assertSame('Sara Karimi', $snapshot['customer_name']);
        $this->assertSame('09120000001', $snapshot['customer_mobile']);
        $this->assertSame('', $snapshot['customer_address']);
        $this->assertSame('', $service->addressText($address));
    }

    public function test_it_maps_part_name_to_title_and_recomputes_line_total(): void
    {
        $service = new InvoiceService;
        $part = new ServiceReportPart([
            'name' => 'Compressor',
            'quantity' => '2.5',
            'unit_price' => '10.00',
            'total_price' => '1.00',
        ]);

        $item = $service->itemFromPart($part);

        $this->assertSame('Compressor', $item['title']);
        $this->assertSame('', $item['description']);
        $this->assertSame('2.500', $item['quantity']);
        $this->assertSame('10.00', $item['unit_price']);
        $this->assertSame('25.00', $item['total']);
    }

    public function test_it_computes_subtotal_and_total_from_discount_and_tax(): void
    {
        $service = new InvoiceService;

        $subtotal = $service->subtotal(['25.00', '6.50']);
        $this->assertSame('31.50', $subtotal);
        $this->assertSame('26.50', $service->invoiceTotal($subtotal, '10.00', '5.00'));
    }

    public function test_it_rejects_a_negative_invoice_total(): void
    {
        $this->expectException(InvalidArgumentException::class);

        (new InvoiceService)->invoiceTotal('10.00', '20.00', '0.00');
    }
}
