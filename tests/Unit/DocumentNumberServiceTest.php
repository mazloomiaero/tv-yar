<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\DocumentSequence;
use App\Services\DocumentNumberService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use InvalidArgumentException;
use Tests\TestCase;

class DocumentNumberServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_increments_current_number_inside_a_transaction_and_formats_the_source_pattern(): void
    {
        $service = $this->app->make(DocumentNumberService::class);

        $first = $service->nextNumber('REQUEST', 1405);
        $second = $service->nextNumber('REQUEST', 1405);

        $this->assertSame('SR-1405-000001', $first);
        $this->assertSame('SR-1405-000002', $second);

        $this->assertDatabaseHas('document_sequences', [
            'document_type' => 'REQUEST',
            'prefix' => 'SR',
            'year' => 1405,
            'current_number' => 2,
        ]);
    }

    public function test_it_isolates_sequences_by_document_type_and_year(): void
    {
        $service = $this->app->make(DocumentNumberService::class);

        $this->assertSame('INV-1405-000001', $service->nextNumber('INVOICE', 1405));
        $this->assertSame('REC-1405-000001', $service->nextNumber('RECEPTION', 1405));
        $this->assertSame('SR-1406-000001', $service->nextNumber('REQUEST', 1406));
        $this->assertSame('INV-1405-000002', $service->nextNumber('INVOICE', 1405));
    }

    public function test_it_continues_from_an_existing_locked_sequence_row(): void
    {
        DocumentSequence::query()->create([
            'document_type' => 'REQUEST',
            'prefix' => 'SR',
            'year' => 1405,
            'current_number' => 124,
        ]);

        $number = $this->app->make(DocumentNumberService::class)->nextNumber('REQUEST', 1405);

        $this->assertSame('SR-1405-000125', $number);
    }

    public function test_it_rejects_document_types_that_are_not_in_the_source(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->app->make(DocumentNumberService::class)->nextNumber('WORK_ORDER', 1405);
    }
}
