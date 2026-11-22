<?php

declare(strict_types=1);

namespace App\Support;

use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use JsonException;

/**
 * One-way WordPress intake credentials and defaults (outside the 41-table schema).
 */
final class WebsiteIntakeSettingsStore
{
    public const PATH = 'website-intake.json';

    /**
     * @return array{
     *     keys: list<array{id: string, name: string, key: string, created_at: string}>,
     *     device_type_id: int|null,
     *     service_type_id: int|null,
     *     actor_user_id: int|null
     * }
     */
    public function resolved(): array
    {
        $stored = $this->readStored();

        return [
            'keys' => $stored['keys'],
            'device_type_id' => $stored['device_type_id'] > 0 ? $stored['device_type_id'] : null,
            'service_type_id' => $stored['service_type_id'] > 0 ? $stored['service_type_id'] : null,
            'actor_user_id' => $stored['actor_user_id'] > 0 ? $stored['actor_user_id'] : null,
        ];
    }

    public function isConfigured(): bool
    {
        $settings = $this->resolved();

        return $settings['keys'] !== []
            && $settings['device_type_id'] !== null
            && $settings['service_type_id'] !== null
            && $settings['actor_user_id'] !== null;
    }

    public function apiKeyMatches(string $provided): bool
    {
        if ($provided === '') {
            return false;
        }

        foreach ($this->resolved()['keys'] as $entry) {
            if (hash_equals($entry['key'], $provided)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return array{id: string, name: string, key: string, created_at: string}
     */
    public function createKey(string $name): array
    {
        $current = $this->readStored();
        $entry = [
            'id' => (string) Str::uuid(),
            'name' => $name !== '' ? $name : 'کلید وردپرس',
            'key' => Str::random(48),
            'created_at' => now()->toIso8601String(),
        ];

        $current['keys'][] = $entry;
        $this->write($current);

        return $entry;
    }

    public function deleteKey(string $id): bool
    {
        $current = $this->readStored();
        $before = count($current['keys']);
        $current['keys'] = array_values(array_filter(
            $current['keys'],
            fn (array $entry): bool => $entry['id'] !== $id
        ));

        if (count($current['keys']) === $before) {
            return false;
        }

        $this->write($current);

        return true;
    }

    /**
     * @param  array{
     *     device_type_id?: int|null,
     *     service_type_id?: int|null,
     *     actor_user_id?: int|null
     * }  $input
     */
    public function updateDefaults(array $input): void
    {
        $current = $this->readStored();

        if (array_key_exists('device_type_id', $input)) {
            $current['device_type_id'] = max(0, (int) ($input['device_type_id'] ?? 0));
        }

        if (array_key_exists('service_type_id', $input)) {
            $current['service_type_id'] = max(0, (int) ($input['service_type_id'] ?? 0));
        }

        if (array_key_exists('actor_user_id', $input)) {
            $current['actor_user_id'] = max(0, (int) ($input['actor_user_id'] ?? 0));
        }

        $this->write($current);
    }

    /**
     * @param  array{keys: list<array{id: string, name: string, key: string, created_at: string}>, device_type_id: int, service_type_id: int, actor_user_id: int}  $payload
     */
    private function write(array $payload): void
    {
        Storage::disk('local')->put(
            self::PATH,
            Crypt::encryptString(json_encode($payload, JSON_THROW_ON_ERROR))
        );
    }

    /**
     * @return array{keys: list<array{id: string, name: string, key: string, created_at: string}>, device_type_id: int, service_type_id: int, actor_user_id: int}
     */
    private function readStored(): array
    {
        $defaults = [
            'keys' => [],
            'device_type_id' => 0,
            'service_type_id' => 0,
            'actor_user_id' => 0,
        ];

        if (! Storage::disk('local')->exists(self::PATH)) {
            return $defaults;
        }

        try {
            $plain = Crypt::decryptString(Storage::disk('local')->get(self::PATH) ?? '');
            $decoded = json_decode($plain, true, 512, JSON_THROW_ON_ERROR);
        } catch (DecryptException|JsonException|\TypeError) {
            return $defaults;
        }

        if (! is_array($decoded)) {
            return $defaults;
        }

        $keys = [];

        if (isset($decoded['keys']) && is_array($decoded['keys'])) {
            foreach ($decoded['keys'] as $entry) {
                if (! is_array($entry)) {
                    continue;
                }

                $key = trim((string) ($entry['key'] ?? ''));
                if ($key === '') {
                    continue;
                }

                $keys[] = [
                    'id' => (string) ($entry['id'] ?? Str::uuid()),
                    'name' => trim((string) ($entry['name'] ?? 'کلید وردپرس')) ?: 'کلید وردپرس',
                    'key' => $key,
                    'created_at' => (string) ($entry['created_at'] ?? now()->toIso8601String()),
                ];
            }
        } elseif (trim((string) ($decoded['api_key'] ?? '')) !== '') {
            // Migrate legacy single-key storage.
            $keys[] = [
                'id' => (string) Str::uuid(),
                'name' => 'کلید وردپرس',
                'key' => trim((string) $decoded['api_key']),
                'created_at' => now()->toIso8601String(),
            ];
        }

        $payload = [
            'keys' => $keys,
            'device_type_id' => (int) ($decoded['device_type_id'] ?? 0),
            'service_type_id' => (int) ($decoded['service_type_id'] ?? 0),
            'actor_user_id' => (int) ($decoded['actor_user_id'] ?? 0),
        ];

        if (! isset($decoded['keys']) && $keys !== []) {
            $this->write($payload);
        }

        return $payload;
    }
}
