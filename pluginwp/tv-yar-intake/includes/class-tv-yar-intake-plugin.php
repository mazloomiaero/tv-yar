<?php

declare(strict_types=1);

if (! defined('ABSPATH')) {
    exit;
}

final class Tv_Yar_Intake_Plugin
{
    private static ?self $instance = null;

    public static function instance(): self
    {
        return self::$instance ??= new self;
    }

    public function boot(): void
    {
        Tv_Yar_Intake_Settings::register();
        Tv_Yar_Intake_Shortcode::register();
    }
}
