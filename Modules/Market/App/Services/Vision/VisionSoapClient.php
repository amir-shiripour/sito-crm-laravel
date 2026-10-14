<?php

namespace Modules\Market\App\Services\Vision;

use Exception;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Modules\Market\Entities\VendorVisionSetting;
use SoapClient;
use SoapFault;
use SoapHeader;

class VisionSoapClient
{
    protected VendorVisionSetting $setting;
    protected string $endpoint;
    protected ?string $token = null;
    protected ?int $userId = null;
    protected string $cachePrefix;

    const SOAP_ACTION_BASE = 'http://tempuri.org/';

    public function __construct(VendorVisionSetting $setting)
    {
        $this->setting = $setting;
        $this->endpoint = rtrim($setting->server_url ?: '', '/');
        $this->cachePrefix = 'vision_session_vendor_' . $setting->vendor_id;
    }

    /**
     * Get or initialize SOAP client instance
     */
    protected function getRawSoapClient(): SoapClient
    {
        if (!extension_loaded('soap')) {
            throw new Exception('اکستنشن PHP SOAP فعال نیست.');
        }

        $wsdlUrl = $this->endpoint . (str_ends_with(strtolower($this->endpoint), '?wsdl') ? '' : '?WSDL');

        return new SoapClient($wsdlUrl, [
            'trace' => true,
            'exceptions' => true,
            'cache_wsdl' => WSDL_CACHE_NONE,
            'connection_timeout' => 15,
            'stream_context' => stream_context_create([
                'ssl' => [
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true,
                ],
            ]),
        ]);
    }

    /**
     * Perform login to Vision ERP WebService 3.6.1 with user, password, and adminPass
     *
     * @return array ['success' => bool, 'token' => string|null, 'userId' => int|null, 'message' => string]
     */
    public function login(): array
    {
        $cached = Cache::get($this->cachePrefix);
        if ($cached && !empty($cached['token'])) {
            $this->token = $cached['token'];
            $this->userId = $cached['userId'] ?? null;
            return [
                'success' => true,
                'token' => $this->token,
                'userId' => $this->userId,
                'message' => 'نشست از کش بارگذاری شد.',
            ];
        }

        $username = $this->setting->ws_username;
        $password = $this->setting->ws_password;
        $adminPass = $this->setting->admin_pass;

        if (empty($this->endpoint) || empty($username) || empty($password)) {
            return [
                'success' => false,
                'token' => null,
                'userId' => null,
                'message' => 'اطلاعات سرور یا نام کاربری و کلمه عبور وب‌سرویس تکمیل نشده است.',
            ];
        }

        try {
            // Priority 1: native PHP SoapClient if loaded
            if (extension_loaded('soap')) {
                $client = $this->getRawSoapClient();

                $payload = json_encode([
                    'user' => $username,
                    'password' => $password,
                    'adminPass' => $adminPass ?: '',
                ], JSON_UNESCAPED_UNICODE);

                $response = $client->__soapCall('Login', [[
                    'inputStringJson' => $payload,
                ]]);

                $resultKey = 'LoginResult';
                $rawResult = is_object($response) && isset($response->$resultKey) ? $response->$resultKey : $response;

                return $this->processLoginResult($rawResult);
            }

            // Priority 2: HTTP SOAP 1.1 / 1.2 Raw Fallback (in case soap ext is disabled)
            return $this->loginViaHttpFallback($username, $password, $adminPass);

        } catch (SoapFault $sf) {
            Log::error('Vision SOAP Login Fault: ' . $sf->getMessage(), ['vendor_id' => $this->setting->vendor_id]);
            return [
                'success' => false,
                'token' => null,
                'userId' => null,
                'message' => 'خطای SOAP: ' . $sf->getMessage(),
            ];
        } catch (Exception $e) {
            Log::error('Vision Login Exception: ' . $e->getMessage(), ['vendor_id' => $this->setting->vendor_id]);
            return [
                'success' => false,
                'token' => null,
                'userId' => null,
                'message' => 'خطا در ارتباط با وب‌سرویس: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Fallback HTTP-based SOAP request when soap extension is not enabled in PHP environment
     */
    protected function loginViaHttpFallback(string $username, string $password, ?string $adminPass): array
    {
        $payload = json_encode([
            'user' => $username,
            'password' => $password,
            'adminPass' => $adminPass ?: '',
        ], JSON_UNESCAPED_UNICODE);

        $envelope = '<?xml version="1.0" encoding="utf-8"?>
<soap:Envelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/">
  <soap:Body>
    <Login xmlns="http://tempuri.org/">
      <inputStringJson>' . htmlspecialchars($payload, ENT_XML1) . '</inputStringJson>
    </Login>
  </soap:Body>
</soap:Envelope>';

        $response = Http::withHeaders([
            'Content-Type' => 'text/xml; charset=utf-8',
            'SOAPAction' => 'http://tempuri.org/Login',
        ])->timeout(20)->send('POST', $this->endpoint, ['body' => $envelope]);

        if (!$response->successful()) {
            return [
                'success' => false,
                'token' => null,
                'userId' => null,
                'message' => 'خطای HTTP در ارتباط با وب‌سرویس (کد ' . $response->status() . ')',
            ];
        }

        $xml = simplexml_load_string($response->body());
        $xml->registerXPathNamespace('soap', 'http://schemas.xmlsoap.org/soap/envelope/');
        $xml->registerXPathNamespace('ns', 'http://tempuri.org/');
        $resultNodes = $xml->xpath('//ns:LoginResult');

        if (empty($resultNodes)) {
            return [
                'success' => false,
                'token' => null,
                'userId' => null,
                'message' => 'پاسخ معتبری از متد Login دریافت نشد.',
            ];
        }

        $rawResult = (string) $resultNodes[0];
        $decoded = json_decode($rawResult, true);

        return $this->processLoginResult($decoded ?: ['raw' => $rawResult]);
    }

    /**
     * Parse decoded login JSON or raw error string
     */
    protected function processLoginResult($data): array
    {
        if (is_string($data)) {
            $trimmed = trim($data);
            if ($trimmed === 'NOT-VALID') {
                return [
                    'success' => false,
                    'token' => null,
                    'userId' => null,
                    'message' => 'اطلاعات ورود نامعتبر است (NOT-VALID): نام کاربری یا کلمه عبور یا رمز ادمین اشتباه است.',
                ];
            }

            if (str_starts_with($trimmed, 'error:')) {
                return [
                    'success' => false,
                    'token' => null,
                    'userId' => null,
                    'message' => 'پاسخ سرور ویژن: ' . $trimmed,
                ];
            }

            $decoded = json_decode($trimmed, true);
            if (is_array($decoded)) {
                $data = $decoded;
            }
        }

        if (is_array($data)) {
            // Checking for common Vision ERP return keys (lic or token, UserId or user_id)
            $token = $data['lic'] ?? $data['token'] ?? $data['LicenseKey'] ?? null;
            $userId = $data['UserId'] ?? $data['user_id'] ?? $data['id'] ?? null;
            $status = $data['status'] ?? $data['success'] ?? true;
            $message = $data['message'] ?? $data['msg'] ?? 'ورود به وب‌سرویس موفقیت‌آمیز بود.';

            if ($token) {
                $this->token = $token;
                $this->userId = $userId ? (int) $userId : 1;

                Cache::put($this->cachePrefix, [
                    'token' => $this->token,
                    'userId' => $this->userId,
                ], now()->addHours(6));

                return [
                    'success' => true,
                    'token' => $this->token,
                    'userId' => $this->userId,
                    'message' => $message,
                ];
            }

            if (isset($data['error']) || isset($data['message'])) {
                return [
                    'success' => false,
                    'token' => null,
                    'userId' => null,
                    'message' => $data['error'] ?? $data['message'],
                ];
            }
        }

        return [
            'success' => false,
            'token' => null,
            'userId' => null,
            'message' => is_string($data) ? ('پاسخ وب‌سرویس: ' . $data) : 'فرمت پاسخ احراز هویت وب‌سرویس نامعتبر است.',
        ];
    }

    /**
     * Call an authenticated SOAP action with required Vision SOAP Headers:
     * - LicesnseKey
     * - Type: 5
     * - UserId
     */
    public function callAction(string $actionName, array $params = []): array
    {
        $loginResult = $this->login();
        if (!$loginResult['success']) {
            return [
                'success' => false,
                'data' => null,
                'message' => $loginResult['message'],
            ];
        }

        try {
            if (extension_loaded('soap')) {
                $client = $this->getRawSoapClient();

                // Inject required headers
                $headerData = [
                    'LicesnseKey' => $this->token,
                    'Type' => 5,
                    'UserId' => $this->userId,
                ];

                $soapHeaders = [];
                foreach ($headerData as $headerKey => $headerVal) {
                    $soapHeaders[] = new SoapHeader(self::SOAP_ACTION_BASE, $headerKey, $headerVal);
                }
                $client->__setSoapHeaders($soapHeaders);

                $response = $client->__soapCall($actionName, [$params]);
                $resultProp = $actionName . 'Result';
                $rawResult = is_object($response) && isset($response->$resultProp) ? $response->$resultProp : $response;

                $decoded = is_string($rawResult) ? json_decode($rawResult, true) : (array) $rawResult;

                return [
                    'success' => true,
                    'data' => $decoded ?? $rawResult,
                    'message' => 'فراخوانی با موفقیت انجام شد.',
                ];
            }

            // HTTP Fallback
            return $this->callActionViaHttpFallback($actionName, $params);

        } catch (SoapFault $sf) {
            // In case token expired, clear cache and retry once
            Cache::forget($this->cachePrefix);
            return [
                'success' => false,
                'data' => null,
                'message' => 'خطای SOAP هنگام اجرای ' . $actionName . ': ' . $sf->getMessage(),
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'data' => null,
                'message' => 'خطا در ارتباط با متد ' . $actionName . ': ' . $e->getMessage(),
            ];
        }
    }

    /**
     * HTTP Fallback for Authenticated Actions with Headers
     */
    protected function callActionViaHttpFallback(string $actionName, array $params = []): array
    {
        $paramXml = '';
        foreach ($params as $k => $v) {
            $paramXml .= "<{$k}>" . htmlspecialchars(is_array($v) ? json_encode($v, JSON_UNESCAPED_UNICODE) : (string)$v, ENT_XML1) . "</{$k}>";
        }

        $envelope = '<?xml version="1.0" encoding="utf-8"?>
<soap:Envelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/">
  <soap:Header>
    <LicesnseKey xmlns="http://tempuri.org/">' . htmlspecialchars($this->token, ENT_XML1) . '</LicesnseKey>
    <Type xmlns="http://tempuri.org/">5</Type>
    <UserId xmlns="http://tempuri.org/">' . htmlspecialchars((string)$this->userId, ENT_XML1) . '</UserId>
  </soap:Header>
  <soap:Body>
    <' . $actionName . ' xmlns="http://tempuri.org/">' . $paramXml . '</' . $actionName . '>
  </soap:Body>
</soap:Envelope>';

        $response = Http::withHeaders([
            'Content-Type' => 'text/xml; charset=utf-8',
            'SOAPAction' => 'http://tempuri.org/' . $actionName,
        ])->timeout(30)->send('POST', $this->endpoint, ['body' => $envelope]);

        if (!$response->successful()) {
            return [
                'success' => false,
                'data' => null,
                'message' => 'خطای HTTP در فراخوانی متد ' . $actionName . ' (کد ' . $response->status() . ')',
            ];
        }

        $xml = simplexml_load_string($response->body());
        $xml->registerXPathNamespace('soap', 'http://schemas.xmlsoap.org/soap/envelope/');
        $xml->registerXPathNamespace('ns', 'http://tempuri.org/');
        $resultNodes = $xml->xpath('//ns:' . $actionName . 'Result');

        if (empty($resultNodes)) {
            return [
                'success' => false,
                'data' => null,
                'message' => 'پاسخ متد ' . $actionName . ' خالی است.',
            ];
        }

        $rawResult = (string) $resultNodes[0];
        $decoded = json_decode($rawResult, true);

        return [
            'success' => true,
            'data' => $decoded ?? $rawResult,
            'message' => 'عملیات با موفقیت انجام شد.',
        ];
    }

    /**
     * Test connection to Vision WebService
     */
    public function testConnection(): array
    {
        Cache::forget($this->cachePrefix);
        return $this->login();
    }
}
