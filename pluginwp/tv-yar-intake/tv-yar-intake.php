<?php

/**
 * Plugin Name: TV Yar Intake Form
 * Description: شرت‌کد فرم ثبت درخواست خدمت برای اتصال یک‌طرفه به سامانه مدیریت خدمات فنی.
 * Version: 1.1.0
 * Author: TV Yar
 * Text Domain: tv-yar-intake
 */

declare(strict_types=1);

if (! defined('ABSPATH')) {
    exit;
}

define('TV_YAR_INTAKE_VERSION', '1.1.0');
define('TV_YAR_INTAKE_FILE', __FILE__);
define('TV_YAR_INTAKE_PATH', plugin_dir_path(__FILE__));
define('TV_YAR_INTAKE_URL', plugin_dir_url(__FILE__));

require_once TV_YAR_INTAKE_PATH.'includes/class-tv-yar-intake-settings.php';
require_once TV_YAR_INTAKE_PATH.'includes/class-tv-yar-intake-api.php';
require_once TV_YAR_INTAKE_PATH.'includes/class-tv-yar-intake-shortcode.php';
require_once TV_YAR_INTAKE_PATH.'includes/class-tv-yar-intake-plugin.php';

add_action('plugins_loaded', static function (): void {
    Tv_Yar_Intake_Plugin::instance()->boot();
});
