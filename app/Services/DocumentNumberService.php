<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\DocumentSequence;
use App\Support\DocumentType;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use RuntimeException;
use ValueError;

class DocumentNumberService
{
    /**
     * Allocate the next document number for a type and year inside one locked transaction.
     *
     * Callers pass year explicitly. The source example uses 1405; this service does not
     * convert Gregorian dates to Jalali.
     */
    public function nextNumber(string $documentType, int $year): string
    {
        $type = $this->resolveType($documentType);

        return DB::transaction(function () use ($type, $year): string {
            $sequence = $this->lockSequence($type, $year);

            $sequence->current_number++;
            $sequence->save();

            return sprintf(
                '%s-%d-%06d',
                $sequence->prefix,
                $sequence->year,
                $sequence->current_number,
            );
        });
    }

    private function resolveType(string $documentType): DocumentType
    {
        try {
            return DocumentType::from($documentType);
        } catch (ValueError) {
            throw new InvalidArgumentException("Unsupported document type [{$documentType}].");
        }
    }

    private function lockSequence(DocumentType $type, int $year): DocumentSequence
    {
        $sequence = $this->lockExisting($type, $year);

        if ($sequence instanceof DocumentSequence) {
            return $sequence;
        }

        try {
            DocumentSequence::query()->create([
                'document_type' => $type->value,
                'prefix' => $type->prefix(),
                'year' => $year,
                'current_number' => 0,
            ]);
        } catch (QueryException) {
            // Another transaction inserted the unique (document_type, year) row first.
        }

        $sequence = $this->lockExisting($type, $year);

        if (! $sequence instanceof DocumentSequence) {
            throw new RuntimeException(
                "Unable to lock document sequence for [{$type->value}] year [{$year}].",
            );
        }

        return $sequence;
    }

    private function lockExisting(DocumentType $type, int $year): ?DocumentSequence
    {
        return DocumentSequence::query()
            ->where('document_type', $type->value)
            ->where('year', $year)
            ->lockForUpdate()
            ->first();
    }
}
