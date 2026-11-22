<?php

declare(strict_types=1);

if (! defined('ABSPATH')) {
    exit;
}

final class Tv_Yar_Intake_Api
{
    /**
     * @param  array{full_name: string, mobile: string, brand: string, problem: string, address: string}  $payload
     * @return array{ok: bool, message: string, request_number?: string}
     */
    public static function submit(array $payload): array
    {
        $settings = Tv_Yar_Intake_Settings::get();
        $apiUrl = trim($settings['api_url']);
        $apiKey = trim($settings['api_key']);

        if ($apiUrl === '' || $apiKey === '') {
            return [
                'ok' => false,
                'message' => 'تنظیمات API پلاگین کامل نیست.',
            ];
        }

        $response = wp_remote_post($apiUrl, [
            'timeout' => 20,
            'headers' => [
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
                'X-Intake-Key' => $apiKey,
            ],
            'body' => wp_json_encode($payload),
        ]);

        if (is_wp_error($response)) {
            return [
                'ok' => false,
                'message' => 'ارتباط با سامانه برقرار نشد.',
            ];
        }

        $code = (int) wp_remote_retrieve_response_code($response);
        $body = json_decode((string) wp_remote_retrieve_body($response), true);
        $message = is_array($body) ? (string) ($body['message'] ?? '') : '';

        if ($code >= 200 && $code < 300) {
            return [
                'ok' => true,
                'message' => $message !== '' ? $message : $settings['success_message'],
                'request_number' => is_array($body) ? (string) ($body['request_number'] ?? '') : '',
            ];
        }

        if (is_array($body) && isset($body['errors']) && is_array($body['errors'])) {
            $first = reset($body['errors']);
            if (is_array($first) && isset($first[0])) {
                $message = (string) $first[0];
            }
        }

        return [
            'ok' => false,
            'message' => $message !== '' ? $message : 'ثبت درخواست ناموفق بود.',
        ];
    }
}
