<?php

namespace App\Accessors;


use ReCaptcha\ReCaptcha;

class GoogleRecaptcha
{
    private static bool $active = false;
    private static string $secret_key = '6LchelkjAAAAAGLRtaQo3ATbvgdbbjT82m0FDs3_';
    public static string $site_key = '6LchelkjAAAAAC-H5M_1DqvHwMGk2INEn_-uhWnR';

    public static function check(): bool
    {
        if (!self::isActive()) {
            return true;
        }

        if (!request()->filled('g-recaptcha-response')) {
            return false;
        }

        $gRecaptchaResponse = request('g-recaptcha-response');

        $recaptcha = new ReCaptcha(self::$secret_key);
        $resp = $recaptcha
            //->setExpectedHostname($hostname)
            ->verify($gRecaptchaResponse, request()->ip());


        return $resp->isSuccess();
    }

    public static function isActive(): bool
    {
        return self::$active === true;
    }

    public static function button(string $label = 'Envoyer', string $class = ''): string
    {
        if (self::isActive()) {

            return '<button
                    class="g-recaptcha ' . $class . '"
                    data-sitekey="' . self::$site_key . '"
                    data-callback="onSubmit"
                    data-action="submit"
                    type="submit">' . $label . '
                </button>';
        }
        return '<button
                    class="' . $class . '"
                    type="submit">' . $label . '
                </button>';
    }

    public static function form(string $form_id): string
    {
        if (self::isActive()) {
            return "<script>
                    function onSubmit(token) {
                        document . getElementById('".$form_id."') . submit();
                    }
                </script>
                <script src='https://www.google.com/recaptcha/api.js'></script>";
        }
        return '';
    }
}
