<?php
/**
 * composer require google/recaptcha "^1.3"
 * Usage:
 *  - in blade file : GoogleRecaptcha::form(form_id)
 *  - in controller: GoogleRecaptcha::check()
 */

namespace MetaFramework\Services;

use ReCaptcha\ReCaptcha;
use ReCaptcha\RequestMethod\CurlPost;

class GoogleRecaptcha
{
    public static function check(): bool
    {
        if (!self::isActive()) {
            return true;
        }

        $gRecaptchaResponse = request('g-recaptcha-response');

        $recaptcha = new ReCaptcha(self::secretKey(), new CurlPost);
        $resp = $recaptcha
            // ->setExpectedHostname($hostname)
            ->setScoreThreshold(0.7)
            ->verify($gRecaptchaResponse, request()->ip());

        return $resp->isSuccess();
    }

    public static function isActive(): bool
    {
        return (bool) config('mfw-api.google.recaptcha.active') && (!empty(self::siteKey()) or !empty(self::secretKey()));
    }

    public static function form(string $form_id): void
    {
        if (self::isActive()) {
            ?>
            <script src='https://www.google.com/recaptcha/api.js?render=<?= self::siteKey(); ?>'></script>
            <script>
              function doRecaptcha(result) {
                grecaptcha.ready(function () {
                  grecaptcha.execute('<?= self::siteKey(); ?>', {action: 'contact_form'}).then(function (token) {
                    let recaptchaResponse = $('#<?= $form_id; ?>').find('input[name=g-recaptcha-response]');
                    if (recaptchaResponse.length) {
                      recaptchaResponse.val(token);
                    } else {
                      $('#<?= $form_id; ?>').append('<input type="hidden" name="g-recaptcha-response" value="' + token + '"/>');
                    }
                    if (result !== undefined && !result.hasOwnProperty('error')) {
                      window.dataLayer = window.dataLayer || [];
                      window.dataLayer.push({
                        'event': 'LeadFormSubmit',
                      });
                      console.log('LeadFormSubmit fired');
                    }
                  });
                });
              }

              doRecaptcha();
            </script>

            <?php
        }
    }

    private static function secretKey(): string
    {
        return config('mfw-api.google.recaptcha.site_secret');
    }

    private static function siteKey(): string
    {
        return config('mfw-api.google.recaptcha.site_key');
    }
}
