<?php

declare(strict_types=1);

if (! defined('ABSPATH')) {
    exit;
}

final class Tv_Yar_Intake_Settings
{
    public const OPTION_KEY = 'tv_yar_intake_settings';

    /**
     * @return array{api_url: string, api_key: string, brands: string, success_message: string}
     */
    public static function get(): array
    {
        $defaults = [
            'api_url' => '',
            'api_key' => '',
            'brands' => "Samsung\nLG\nSony",
            'success_message' => 'درخواست شما با موفقیت ثبت شد.',
        ];

        $stored = get_option(self::OPTION_KEY, []);
        if (! is_array($stored)) {
            $stored = [];
        }

        return array_merge($defaults, array_intersect_key($stored, $defaults));
    }

    /**
     * @return list<string>
     */
    public static function brands(): array
    {
        $raw = self::get()['brands'];
        $lines = preg_split('/\r\n|\r|\n/', $raw) ?: [];
        $brands = [];

        foreach ($lines as $line) {
            $name = trim((string) $line);
            if ($name !== '') {
                $brands[] = $name;
            }
        }

        return array_values(array_unique($brands));
    }

    public static function register(): void
    {
        add_action('admin_menu', [self::class, 'add_menu']);
        add_action('admin_init', [self::class, 'register_settings']);
    }

    public static function add_menu(): void
    {
        add_options_page(
            'TV Yar Intake',
            'فرم TV Yar',
            'manage_options',
            'tv-yar-intake',
            [self::class, 'render_page']
        );
    }

    public static function register_settings(): void
    {
        register_setting('tv_yar_intake_group', self::OPTION_KEY, [
            'type' => 'array',
            'sanitize_callback' => [self::class, 'sanitize'],
            'default' => [],
        ]);
    }

    /**
     * @param  mixed  $input
     * @return array{api_url: string, api_key: string, brands: string, success_message: string}
     */
    public static function sanitize($input): array
    {
        $input = is_array($input) ? $input : [];

        return [
            'api_url' => esc_url_raw((string) ($input['api_url'] ?? '')),
            'api_key' => sanitize_text_field((string) ($input['api_key'] ?? '')),
            'brands' => sanitize_textarea_field((string) ($input['brands'] ?? '')),
            'success_message' => sanitize_text_field((string) ($input['success_message'] ?? '')),
        ];
    }

    public static function render_page(): void
    {
        if (! current_user_can('manage_options')) {
            return;
        }

        $settings = self::get();
        ?>
        <div class="wrap">
            <h1>تنظیمات فرم TV Yar</h1>
            <p>شرت‌کد: <code>[tv_yar_intake]</code></p>
            <form method="post" action="options.php">
                <?php settings_fields('tv_yar_intake_group'); ?>
                <table class="form-table" role="presentation">
                    <tr>
                        <th scope="row"><label for="tv_yar_api_url">آدرس API سامانه</label></th>
                        <td>
                            <input
                                id="tv_yar_api_url"
                                class="regular-text ltr"
                                type="url"
                                name="<?php echo esc_attr(self::OPTION_KEY); ?>[api_url]"
                                value="<?php echo esc_attr($settings['api_url']); ?>"
                                placeholder="https://app.example.com/intake/website-request"
                                dir="ltr"
                            >
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="tv_yar_api_key">کلید API</label></th>
                        <td>
                            <input
                                id="tv_yar_api_key"
                                class="regular-text ltr"
                                type="text"
                                name="<?php echo esc_attr(self::OPTION_KEY); ?>[api_key]"
                                value="<?php echo esc_attr($settings['api_key']); ?>"
                                dir="ltr"
                            >
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="tv_yar_brands">برندها / مدل‌ها</label></th>
                        <td>
                            <textarea
                                id="tv_yar_brands"
                                class="large-text"
                                rows="8"
                                name="<?php echo esc_attr(self::OPTION_KEY); ?>[brands]"
                            ><?php echo esc_textarea($settings['brands']); ?></textarea>
                            <p class="description">هر خط یک گزینه برای لیست انتخاب دستگاه. این لیست فقط از همین تنظیمات وردپرس خوانده می‌شود (نه از جدول برند سامانه).</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="tv_yar_success">پیام موفقیت</label></th>
                        <td>
                            <input
                                id="tv_yar_success"
                                class="regular-text"
                                type="text"
                                name="<?php echo esc_attr(self::OPTION_KEY); ?>[success_message]"
                                value="<?php echo esc_attr($settings['success_message']); ?>"
                            >
                        </td>
                    </tr>
                </table>
                <?php submit_button('ذخیره تنظیمات'); ?>
            </form>
        </div>
        <?php
    }
}
