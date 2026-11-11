<?php

declare(strict_types=1);

namespace App\Actions\ServiceReport;

use App\Events\ServiceReportCreated;
use App\Models\Part;
use App\Models\ServiceReport;
use App\Models\ServiceRequest;
use App\Support\ServiceReportImageKind;
use App\Support\ServiceReportImageRules;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use InvalidArgumentException;
use Throwable;

final class CreateServiceReport
{
    /**
     * @param  list<array{part_id?: int|null, name?: string|null, quantity: string, unit_price?: string|null}>  $parts
     * @param  array<string, list<UploadedFile>>  $imagesByKind
     */
    public function execute(ServiceRequest $serviceRequest, array $parts, array $imagesByKind, string $description): ServiceReport
    {
        $storedPaths = [];

        try {
            $report = DB::transaction(function () use ($serviceRequest, $parts, $imagesByKind, $description, &$storedPaths): ServiceReport {
                $report = ServiceReport::query()->create([
                    'service_request_id' => $serviceRequest->id,
                    'description' => $description,
                ]);

                foreach ($parts as $line) {
                    $resolved = $this->resolveLine($line);

                    $report->parts()->create([
                        'part_id' => $resolved['part_id'],
                        'name' => $resolved['name'],
                        'quantity' => $resolved['quantity'],
                        'unit_price' => $resolved['unit_price'],
                        'total_price' => $this->lineTotal($resolved['quantity'], $resolved['unit_price']),
                    ]);
                }

                foreach ($imagesByKind as $kind => $files) {
                    $this->assertKind($kind);

                    foreach ($files as $file) {
                        $directory = ServiceReportImageRules::DIRECTORY_PREFIX.'/'.$kind;
                        $path = $file->store($directory, ServiceReportImageRules::DISK);
                        if (! is_string($path) || $path === '') {
                            throw new InvalidArgumentException('Unable to store the report image.');
                        }
                        $storedPaths[] = $path;

                        $report->images()->create([
                            'path' => $path,
                            'kind' => $kind,
                        ]);
                    }
                }

                return $report;
            });
        } catch (Throwable $exception) {
            foreach ($storedPaths as $path) {
                Storage::disk(ServiceReportImageRules::DISK)->delete($path);
            }

            throw $exception;
        }

        ServiceReportCreated::dispatch((int) $report->id);

        return $report;
    }

    /**
     * @param  array{part_id?: int|null, name?: string|null, quantity: string, unit_price?: string|null}  $line
     * @return array{part_id: int|null, name: string, quantity: string, unit_price: string}
     */
    private function resolveLine(array $line): array
    {
        $partId = isset($line['part_id']) ? (int) $line['part_id'] : 0;
        $quantity = (string) $line['quantity'];

        if ($partId > 0) {
            $part = Part::query()->whereKey($partId)->first();
            if (! $part instanceof Part) {
                throw new InvalidArgumentException('Part was not found.');
            }

            return [
                'part_id' => $part->id,
                'name' => $part->name,
                'quantity' => $quantity,
                'unit_price' => $this->money((string) $part->default_unit_price),
            ];
        }

        $name = trim((string) ($line['name'] ?? ''));
        $unitPrice = (string) ($line['unit_price'] ?? '');

        if ($name === '' || $unitPrice === '') {
            throw new InvalidArgumentException('Free-text parts require a name and unit price.');
        }

        return [
            'part_id' => null,
            'name' => $name,
            'quantity' => $quantity,
            'unit_price' => $this->money($unitPrice),
        ];
    }

    private function lineTotal(string $quantity, string $unitPrice): string
    {
        return number_format((float) $quantity * (float) $unitPrice, 2, '.', '');
    }

    private function money(string $amount): string
    {
        return number_format((float) $amount, 2, '.', '');
    }

    private function assertKind(string $kind): void
    {
        if (ServiceReportImageKind::tryFrom($kind) === null) {
            throw new InvalidArgumentException('Unknown image folder kind.');
        }
    }
}
