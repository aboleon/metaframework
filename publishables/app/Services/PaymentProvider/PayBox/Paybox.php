<?php

namespace App\Services\PaymentProvider\PayBox;

use App\Enum\OrderOrigin;
use App\Interfaces\PaymentProviderInterface;
use App\Models\CustomPaymentCall;
use App\Models\Order\EventDeposit;
use App\Models\PayboxReimbursementRequest;
use App\Models\PaymentCall;
use DOMDocument;
use Illuminate\Support\Facades\Http;
use Throwable;

class Paybox implements PaymentProviderInterface
{
    private const API_CREDENTIALS
        = [
            'local' => [
                'api_url'  => 'https://preprod-ppps.paybox.com/PPPS.php',
                'username' => '1100035746',
                'password' => 'zkrCEIj3XVUi6Dcz',
            ],
            'prod'  => [
                'api_url'  => 'https://ppps1.paybox.com/PPPS.php',
                'username' => '3000021345',
                'password' => 'DbQhQDAeMdvw0LGJ',
            ],
        ];

    protected array $credentials;
    protected CustomPaymentCall|PaymentCall $orderable;
    protected array $request_data = [];
    protected array $response_data = [];
    protected bool $testMode;
    protected array $config = [];
    protected ?string $paymentServer = null;
    protected string $payButtonTxt = '';
    protected string $origin;

    # Routes
    private string $routeSuccess;
    private string $routeCancel;
    private string $routeDeclined;
    private string $routeAuto;

    public function __construct(private array $params = [])
    {
        $this->credentials = static::API_CREDENTIALS[app()->environment()];
        $this->testMode    = app()->environment() !== 'production';
        $this->config      = [
            "currency"   => "CHF",
            "successUrl" => '',
            "errorUrl"   => '',
            "cancelUrl"  => request()->headers->get('referer'),
        ];
        $this->origin = OrderOrigin::FRONT->value;
        $this->payButtonTxt = __('ui.proceed_to_payment');

        $this->setFrontPaymentRoutes();
    }


    public function signature(): array
    {
        return [
            'label' => 'Paybox',
            'id'    => 1,
        ];
    }

    public function providerId(): int
    {
        return $this->signature()['id'];
    }

    public function setPayButtonTxt(string $payButtonTxt): self
    {
        $this->payButtonTxt = $payButtonTxt;

        return $this;
    }

    public function setOrderable(CustomPaymentCall|PaymentCall $orderable): static
    {
        $this->orderable = $orderable;

        return $this;
    }

    public function connect(): static
    {
        return $this;
    }

    public function config(array $config = []): static
    {
        $this->config = array_merge($this->config, $config);

        return $this;
    }

    public function request(): static
    {
        return $this;
    }

    public function response(): static
    {
        return $this;
    }

    public function processed(): array
    {
        return [
            'request'  => $this->request_data,
            'response' => $this->response_data,
        ];
    }

    // Refactored from PayboxUtil: Send reimbursement request
    public function sendReimbursementRequest(EventDeposit $eventDeposit): PayboxReimbursementRequest
    {
        $url         = $this->getApiUrl();


        $requestData = $this->buildReimbursementRequestData($eventDeposit);


        $response       = Http::withOptions(['verify' => false])->asForm()->post($url, $requestData);
        $parsedResponse = $this->parseResponse($response->body());

        return PayboxReimbursementRequest::create([
            'event_deposit_id' => $eventDeposit->id,
            'amount'           => $requestData['MONTANT'],
            'calling_params'   => $requestData,
            'received_data'    => $parsedResponse,
        ]);
    }

    // Refactored from PayboxUtil: Render payment form (begin)
    public function renderPaymentFormBegin(bool &$serverOk = true): string
    {
        $values = $this->getPaymentFormValues();

        $hmac   = $this->generateHmac($values);
        $server = $this->getAvailableServerDomain();

        if ( ! $server) {
            $serverOk = false;

            return "";
        }

        return $this->buildFormHtml($values, $hmac, $server);
    }

    // Refactored from PayboxUtil: Render payment form (end)
    public function renderPaymentFormEnd(): string
    {
        return '</form>';
    }

    // Refactored from PayboxUtil: Build request data for reimbursement
    private function buildReimbursementRequestData(EventDeposit $eventDeposit): array
    {
        $amount = ($eventDeposit->total_net + $eventDeposit->total_vat) * 100;

        return [
            'VERSION'     => '00103',
            'TYPE'        => '00014',
            'SITE'        => config('payment_providers.paybox.site'),
            'RANG'        => config('payment_providers.paybox.rang'),
            'IDENTIFIANT' => config('payment_providers.paybox.id'),
            'CLE'         => config('payment_providers.paybox.hmac'),
            'NUMQUESTION' => $this->generateNumQuestion(),
            'MONTANT'     => $amount,
            'DEVISE'      => '978',
            'NUMTRANS'    => $eventDeposit->paymentCall->transaction->transaction_id,
            'NUMAPPEL'    => $eventDeposit->paymentCall->transaction->transaction_call_id,
            'REFERENCE'   => $eventDeposit->paymentCall->transaction->details['uuid'],//$reference,
            'DATEQ'       => date('dmYHis'),
        ];
    }

    // Refactored from PayboxUtil: Generate HMAC
    private function generateHmac(array $values): string
    {
        $msg = $this->buildQueryString($values);
        //   de($msg);
        $keyTest = config('payment_providers.paybox.hmac');
        $binKey  = pack("H*", $keyTest);

        return strtoupper(hash_hmac('sha512', $msg, $binKey));
    }

    // Refactored from PayboxUtil: Build query string
    private function buildQueryString(array $values): string
    {
        $queryString = '';
        foreach ($values as $key => $value) {
            $queryString .= $key.'='.$value.'&';
        }

        return rtrim($queryString, '&');
        //return rtrim(http_build_query($values), '&');
    }

    // Refactored from PayboxUtil: Build form HTML
    private function buildFormHtml(array $values, string $hmac, string $server): string
    {
        $server = $this->testMode ? 'preprod-'.$server : $server;

        $form = '<form id="payboxForm" method="POST" action="https://'.$server.'/php/">';

        foreach ($values as $key => $value) {
            $form .= '<input type="hidden" name="'.$key.'" value="'.$value.'">';
        }

        $form .= '<input type="hidden" name="PBX_HMAC" value="'.$hmac.'">';

        return $form;
    }

    public function getPaymentServer(): ?string
    {
        return $this->paymentServer;
    }

    // A.M. refactored
    public function generateForm(): ?string
    {
        if ( ! $this->paymentServer) {
            return null;
        }

        if ($this->testMode) {
            $this->paymentServer = 'preprod-'.$this->paymentServer;
        }

        $values = $this->getPaymentFormValues();

        $form = '<form id="payboxForm" method="POST" action="https://'.$this->paymentServer.'/php/">';

        foreach ($values as $key => $value) {
            $form .= '<input type="hidden" name="'.$key.'" value="'.$value.'">';
        }

        $form .= '<input type="hidden" name="PBX_HMAC" value="'.$this->generateHmac($values).'">';
        $form .= '<button type="submit" class="btn btn-primary btn-lg" type="button">'.$this->payButtonTxt.'</button>';
        $form .= '</form>';

        return $form;
    }

    // Refactored from PayboxUtil: Get payment form values
    private function getPaymentFormValues(): array
    {
        return [
            'PBX_CMD'           => $this->orderable->uuid,
            'PBX_DEVISE'        => '978',
            'PBX_HASH'          => 'SHA512',
            'PBX_IDENTIFIANT'   => config('payment_providers.paybox.id'),
            'PBX_PORTEUR'       => $this->getOrderEmail(),
            'PBX_RANG'          => config('payment_providers.paybox.rang'),
            'PBX_RETOUR'        => 'uuid:R;t:T;a:A;c:C;e:E;q:Q;s:S',
            'PBX_SITE'          => config('payment_providers.paybox.site'),
            'PBX_TIME'          => date("c"),
            'PBX_TOTAL'         => $this->orderable->getRawOriginal('total'),
            'PBX_ANNULE'        => $this->routeCancel,
            'PBX_EFFECTUE'      => $this->routeSuccess,
            'PBX_REFUSE'        => $this->routeDeclined,
            'PBX_REPONDRE_A'    => $this->routeAuto,
            'PBX_ERRORCODETEST' => $this->params['error_code'] ?? '00000',
        ];
    }

    // Refactored from PayboxUtil: Parse response
    private function parseResponse(string $responseBody): array
    {
        parse_str($responseBody, $parsedResponse);
        $this->response_data = array_map(
            fn($item) => is_string($item) && ! mb_detect_encoding($item, 'UTF-8', true) ? utf8_encode($item) : $item,
            $parsedResponse,
        );

        return $this->response_data;
    }

    // Refactored from PayboxUtil: Get order email
    private function getOrderEmail(): string
    {
        try {
            return $this->orderable->cart->eventContact->user->email;
        } catch (Throwable $e) {
            return config('app.default_mail');
        }
    }

    // Refactored from PayboxUtil: Get available server domain

    private function getAvailableServerDomain(): ?string
    {
        $servers = ['tpeweb.paybox.com', 'tpeweb1.paybox.com'];
        foreach ($servers as $server) {
            $doc = new DOMDocument();
            @$doc->loadHTMLFile('https://'.$server.'/load.html');
            if ($doc->getElementById('server_status')?->textContent === "OK") {
                return $server;
            }
        }

        return null;
    }

    // A.M., refactored
    public function fetchAvailableServer(): self
    {
        $this->paymentServer = null;

        $servers = ['tpeweb.paybox.com', 'tpeweb1.paybox.com'];

        foreach ($servers as $server) {
            $url = 'https://'.$server.'/load.html';

            $context = stream_context_create(['http' => ['timeout' => 5]]);
            libxml_use_internal_errors(true);

            $doc  = new DOMDocument();
            $html = @file_get_contents($url, false, $context);

            if ($html && $doc->loadHTML($html)) {
                if ($doc->getElementById('server_status')?->textContent === "OK") {
                    $this->paymentServer = $server;
                    break;
                }
            }

            libxml_clear_errors();
        }

        return $this;
    }


    // Refactored from PayboxUtil: Generate NumQuestion
    private function generateNumQuestion(): string
    {
        $now                  = now();
        $dayOfYear            = $now->dayOfYear;
        $secondsSinceMidnight = $now->diffInSeconds($now->startOfDay());
        $randomSuffix         = rand(0, 99);

        return sprintf('%03d%05d%02d', $dayOfYear, $secondsSinceMidnight, $randomSuffix);
    }

    // Get Paybox API URL
    private function getApiUrl(): string
    {
        return $this->testMode ? self::API_CREDENTIALS['local']['api_url'] : self::API_CREDENTIALS['prod']['api_url'];
    }

    /*
     * Set Front Routes, default
     */
    public function setFrontPaymentRoutes(): self
    {
        $this->routeSuccess  = $this->params['route_effectue'] ?? route('paybox.receiver.effectue');
        $this->routeCancel   = route('paybox.receiver.annule');
        $this->routeDeclined = route('paybox.receiver.refuse');
        $this->routeAuto     = route('paybox.receiver.autoresponse');

        return $this;
    }

    /*
     * Set CustomPayment Routes
     */
    public function setCustomPaymentRoutes(): self
    {
        $this->routeSuccess  = route('custompayment.success');
        $this->routeCancel   = route('custompayment.cancel');
        $this->routeDeclined = route('custompayment.decline');
        $this->routeAuto     = route('custompayment.autoresponse');

        return $this;
    }
}
