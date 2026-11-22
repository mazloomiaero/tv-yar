<?php

declare(strict_types=1);

namespace App\Support;

use Illuminate\Support\Facades\Storage;
use JsonException;

/**
 * Login-page copy for the auth brand panel. Stored outside the 41-table schema.
 */
final class AuthBrandingStore
{
    public const PATH = 'auth-branding.json';

    /**
     * @return array{kicker: string, title: string, lead: string, trust_1: string, trust_2: string, trust_3: string}
     */
    public function resolved(): array
    {
        $defaults = $this->defaults();
        $stored = $this->readStored();

        return [
            'kicker' => $stored['kicker'] !== '' ? $stored['kicker'] : $defaults['kicker'],
            'title' => $stored['title'] !== '' ? $stored['title'] : $defaults['title'],
            'lead' => $stored['lead'] !== '' ? $stored['lead'] : $defaults['lead'],
            'trust_1' => $stored['trust_1'] !== '' ? $stored['trust_1'] : $defaults['trust_1'],
            'trust_2' => $stored['trust_2'] !== '' ? $stored['trust_2'] : $defaults['trust_2'],
            'trust_3' => $stored['trust_3'] !== '' ? $stored['trust_3'] : $defaults['trust_3'],
        ];
    }

    /**
     * @param  array{kicker?: string|null, title?: string|null, lead?: string|null, trust_1?: string|null, trust_2?: string|null, trust_3?: string|null}  $input
     */
    public function update(array $input): void
    {
        $current = $this->resolved();

        foreach (['kicker', 'title', 'lead', 'trust_1', 'trust_2', 'trust_3'] as $field) {
            if (array_key_exists($field, $input)) {
                $current[$field] = trim((string) ($input[$field] ?? ''));
            }
        }

        foreach ($this->defaults() as $field => $default) {
            if ($current[$field] === '') {
                $current[$field] = $default;
            }
        }

        Storage::disk('local')->put(
            self::PATH,
            json_encode($current, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT)
        );
    }

    /**
     * @return array{kicker: string, title: string, lead: string, trust_1: string, trust_2: string, trust_3: string}
     */
    public function defaults(): array
    {
        return [
            'kicker' => 'سامانه سازمانی',
            'title' => 'سامانه مدیریت خدمات فنی',
            'lead' => 'مدیریت درخواست خدمت، ارجاع تکنسین، گزارش تعمیر، فاکتور و پرداخت در یک جریان امن و یکپارچه.',
            'trust_1' => 'ورود امن با نشست محافظت‌شده',
            'trust_2' => 'پیگیری شفاف وضعیت خدمت',
            'trust_3' => 'حفاظت از داده و تاریخچه عملیات',
        ];
    }

    /**
     * @return array{kicker: string, title: string, lead: string, trust_1: string, trust_2: string, trust_3: string}
     */
    private function readStored(): array
    {
        $empty = [
            'kicker' => '',
            'title' => '',
            'lead' => '',
            'trust_1' => '',
            'trust_2' => '',
            'trust_3' => '',
        ];

        if (! Storage::disk('local')->exists(self::PATH)) {
            return $empty;
        }

        try {
            $decoded = json_decode(Storage::disk('local')->get(self::PATH) ?? '', true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException|\TypeError) {
            return $empty;
        }

        if (! is_array($decoded)) {
            return $empty;
        }

        return [
            'kicker' => trim((string) ($decoded['kicker'] ?? '')),
            'title' => trim((string) ($decoded['title'] ?? '')),
            'lead' => trim((string) ($decoded['lead'] ?? '')),
            'trust_1' => trim((string) ($decoded['trust_1'] ?? '')),
            'trust_2' => trim((string) ($decoded['trust_2'] ?? '')),
            'trust_3' => trim((string) ($decoded['trust_3'] ?? '')),
        ];
    }
}
