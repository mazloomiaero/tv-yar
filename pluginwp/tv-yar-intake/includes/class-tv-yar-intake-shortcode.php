<?php

declare(strict_types=1);

if (! defined('ABSPATH')) {
    exit;
}

final class Tv_Yar_Intake_Shortcode
{
    public static function register(): void
    {
        add_shortcode('tv_yar_intake', [self::class, 'render']);
        add_action('wp_enqueue_scripts', [self::class, 'register_assets']);
        add_action('rest_api_init', [self::class, 'register_rest']);
    }

    public static function register_assets(): void
    {
        wp_register_style(
            'tv-yar-intake-form',
            TV_YAR_INTAKE_URL.'assets/form.css',
            [],
            TV_YAR_INTAKE_VERSION
        );

        wp_register_script(
            'tv-yar-intake-form',
            TV_YAR_INTAKE_URL.'assets/form.js',
            [],
            TV_YAR_INTAKE_VERSION,
            true
        );
    }

    public static function register_rest(): void
    {
        register_rest_route('tv-yar-intake/v1', '/submit', [
            'methods' => 'POST',
            'callback' => [self::class, 'handle_submit'],
            'permission_callback' => '__return_true',
        ]);
    }

    /**
     * @param  WP_REST_Request  $request
     * @return WP_REST_Response
     */
    public static function handle_submit($request)
    {
        $fullName = sanitize_text_field((string) $request->get_param('full_name'));
        $mobile = sanitize_text_field((string) $request->get_param('mobile'));
        $brand = sanitize_text_field((string) $request->get_param('brand'));
        $problem = sanitize_textarea_field((string) $request->get_param('problem'));
        $address = sanitize_textarea_field((string) $request->get_param('address'));

        if ($fullName === '' || $mobile === '' || $brand === '' || $problem === '') {
            return new WP_REST_Response([
                'ok' => false,
                'message' => 'لطفاً همه فیلدهای الزامی را پر کنید.',
            ], 422);
        }

        if (! preg_match('/^09\d{9}$/', $mobile)) {
            return new WP_REST_Response([
                'ok' => false,
                'message' => 'شماره همراه باید با ۰۹ و ۱۱ رقم باشد.',
            ], 422);
        }

        $brands = Tv_Yar_Intake_Settings::brands();
        if ($brands !== [] && ! in_array($brand, $brands, true)) {
            return new WP_REST_Response([
                'ok' => false,
                'message' => 'برند انتخاب‌شده معتبر نیست.',
            ], 422);
        }

        $result = Tv_Yar_Intake_Api::submit([
            'full_name' => $fullName,
            'mobile' => $mobile,
            'brand' => $brand,
            'problem' => $problem,
            'address' => $address,
        ]);

        return new WP_REST_Response($result, $result['ok'] ? 200 : 422);
    }

    /**
     * @param  array<string, mixed>  $atts
     */
    public static function render($atts = []): string
    {
        wp_enqueue_style('tv-yar-intake-form');
        wp_enqueue_script('tv-yar-intake-form');

        wp_localize_script('tv-yar-intake-form', 'tvYarIntake', [
            'endpoint' => esc_url_raw(rest_url('tv-yar-intake/v1/submit')),
            'nonce' => wp_create_nonce('wp_rest'),
        ]);

        $brands = Tv_Yar_Intake_Settings::brands();
        ob_start();
        ?>
        <div class="tv-yar-intake" dir="rtl" data-tv-yar-intake>
            <div class="tv-yar-intake__shell">
                <div class="tv-yar-intake__success" data-tv-yar-intake-success hidden>
                    <div class="tv-yar-intake__success-icon" aria-hidden="true">✓</div>
                    <h3 class="tv-yar-intake__success-title">ثبت شد</h3>
                    <p class="tv-yar-intake__success-text" data-tv-yar-intake-success-text>درخواست شما ثبت شد.</p>
                </div>

                <div class="tv-yar-intake__header" data-tv-yar-intake-header>
                    <p class="tv-yar-intake__eyebrow">TV Yar</p>
                    <h3 class="tv-yar-intake__title">ثبت درخواست تعمیر تلویزیون</h3>
                    <p class="tv-yar-intake__subtitle">نام، موبایل، برند و شرح ایراد را وارد کنید. آدرس اختیاری است.</p>
                </div>

                <form class="tv-yar-intake__form" data-tv-yar-intake-form novalidate>
                    <div class="tv-yar-intake__grid">
                        <div class="tv-yar-intake__field">
                            <label for="tv_yar_full_name">نام و نام خانوادگی <span>*</span></label>
                            <input id="tv_yar_full_name" name="full_name" type="text" required maxlength="200" autocomplete="name" placeholder="مثلاً علی رضایی">
                        </div>

                        <div class="tv-yar-intake__field">
                            <label for="tv_yar_mobile">شماره همراه <span>*</span></label>
                            <input id="tv_yar_mobile" name="mobile" type="tel" required maxlength="11" inputmode="numeric" autocomplete="tel" placeholder="0912xxxxxxx" dir="ltr">
                        </div>

                        <div class="tv-yar-intake__field tv-yar-intake__field--full">
                            <label for="tv_yar_brand">مدل / برند دستگاه <span>*</span></label>
                            <select id="tv_yar_brand" name="brand" required>
                                <option value="">انتخاب کنید</option>
                                <?php foreach ($brands as $brand) { ?>
                                    <option value="<?php echo esc_attr($brand); ?>"><?php echo esc_html($brand); ?></option>
                                <?php } ?>
                            </select>
                        </div>

                        <div class="tv-yar-intake__field tv-yar-intake__field--full">
                            <label for="tv_yar_problem">شرح ایراد <span>*</span></label>
                            <textarea id="tv_yar_problem" name="problem" rows="4" required maxlength="5000" placeholder="مثلاً تصویر ندارد / صدا قطع است"></textarea>
                        </div>

                        <div class="tv-yar-intake__field tv-yar-intake__field--full">
                            <label for="tv_yar_address">آدرس <small>(اختیاری)</small></label>
                            <textarea id="tv_yar_address" name="address" rows="3" maxlength="1000" placeholder="اگر مایلید آدرس را بنویسید"></textarea>
                        </div>
                    </div>

                    <div class="tv-yar-intake__actions">
                        <button type="submit" class="tv-yar-intake__submit">ثبت درخواست</button>
                        <p class="tv-yar-intake__message" data-tv-yar-intake-message hidden></p>
                    </div>
                </form>
            </div>
        </div>
        <?php

        return (string) ob_get_clean();
    }
}
