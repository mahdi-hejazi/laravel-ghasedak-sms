<?php

namespace MahdiHejazi\LaravelGhasedakSms\Channels;

use MahdiHejazi\LaravelGhasedakSms\Exceptions\GhasedakSmsException;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GhasedakChannel
{
    use Queueable;

    private $apikey;
    private $defaultSender;
    private $templates;
    private $config;

    /**
     * @throws GhasedakSmsException
     */
    public function __construct()
    {
        // Load all config once in constructor
        $this->config = config('ghasedak');
        $this->apikey = $this->config['api_key'];
        $this->defaultSender = $this->config['sender'];
        $this->templates = $this->config['templates'] ?? [];

        // Validate API key exists
        if (empty($this->apikey)) {
            throw new GhasedakSmsException('apikey_missing', __('ghasedak::errors.apikey_missing'));
        }
    }

    /**
     * @throws GhasedakSmsException
     */
    public function send($notifiable, Notification $notification)
    {
        // Check if notification supports template-based SMS (OTP)
        if (method_exists($notification, 'toGhasedakSms')) {
            return $this->sendTemplateSms($notifiable, $notification);
        }

        // Check if notification supports simple SMS
        if (method_exists($notification, 'toGhasedakSimpleSms')) {
            return $this->sendSimpleSms($notifiable, $notification);
        }

        throw new GhasedakSmsException('method_not_found', __('ghasedak::errors.method_not_found'));
    }

    /**
     * Send template-based SMS (for OTP, verification codes, etc.)
     * @throws GhasedakSmsException
     */
    private function sendTemplateSms($notifiable, Notification $notification)
    {
        $data = $notification->toGhasedakSms($notifiable);
        $receptor = $data['number'];
        $template = $this->getTemplate($data['template']);
        $parameters = $data['parameters'] ?? []; // Support up to 10 parameters

        foreach ($parameters as $key => $param) {
            $text = strval($param);
            $text = preg_replace('/[^\p{L}\p{N}\s\-_.]/u', '', $text);
            $text = preg_replace('/\s+/', '.', $text);
            $parameters[$key] = str_replace('_', '.', $text);
        }

        $receptor = \MahdiHejazi\LaravelGhasedakSms\Helpers\PhoneHelper::clean($receptor);

        try {
            $requestData = [
                'receptors' => [
                    [
                        'mobile' => $receptor,
                        'clientReferenceId' => uniqid()
                    ]
                ],
                'templateName' => $template,
                'isVoice' => false,
                'udh' => false
            ];

            // Support up to 10 parameters
            for ($i = 0; $i < min(count($parameters), 10); $i++) {
                $requestData['param' . ($i + 1)] = $parameters[$i];
            }


            // Log request data for debugging
            if ($this->shouldLog()) {
                Log::info('Ghasedak Template SMS Request', [
                    'url' => $this->config['api']['verify_url'],
                    'data' => $requestData,
                    'receptor' => $receptor
                ]);
            }


            // Send HTTP request to Ghasedak API
            $response = Http::timeout($this->config['api']['timeout'] ?? 30)
                ->withHeaders([
                    'ApiKey' => $this->apikey,
                    'Content-Type' => 'application/json',
                ])->asJson()->post($this->config['api']['verify_url'], $requestData);

            // Check if request was successful
            if (!$response->successful()) {
                if ($this->shouldLog()) {
                    Log::error('Ghasedak Template SMS HTTP Error', [
                        'status' => $response->status(),
                        'body' => $response->body(),
                        'receptor' => $receptor,
                        'request_data' => $requestData
                    ]);
                }
                throw new GhasedakSmsException('http_error', __('ghasedak::errors.http_error', ['status' => $response->status()]));
            }

            $responseBody = $response->json();

            // Log response for debugging
            if ($this->shouldLog()) {
                Log::info('Ghasedak Template SMS Response', [
                    'response' => $responseBody,
                    'receptor' => $receptor
                ]);
            }

            // Check API response
            if (!isset($responseBody['IsSuccess']) || $responseBody['IsSuccess'] !== true) {
                $errorCode = $responseBody['StatusCode'] ?? 'unknown';
                if ($this->shouldLog()) {
                    Log::error('Ghasedak Template SMS API Error', [
                        'error' => $errorCode,
                        'response' => $responseBody,
                        'receptor' => $receptor
                    ]);
                }

                throw new GhasedakSmsException($errorCode, __('ghasedak::errors.template_send_failed'));
            }

            // Get message ID from response
            $messageIds = $responseBody['Data']['Items'][0]['MessageId'] ?? 0;
            if ($messageIds <= 0) {
                throw new GhasedakSmsException('send_failed', __('ghasedak::errors.send_failed'));
            }

            if ($this->shouldLog()) {
                Log::info('Template SMS sent successfully', [
                    'messageids' => $messageIds,
                    'receptor' => $receptor,
                    'template' => $template
                ]);
            }

            return $responseBody;

        } catch (GhasedakSmsException $e) {
            throw $e;
        } catch (\Exception $e) {
            if ($this->shouldLog()) {
                Log::error('Failed to send template SMS', [
                    'error' => $e->getMessage(),
                    'receptor' => $receptor,
                    'template' => $template
                ]);
            }
            throw new GhasedakSmsException('system_error', $e->getMessage());
        }
    }

    /**
     * Send simple SMS (free text)
     * @throws GhasedakSmsException
     */
    private function sendSimpleSms($notifiable, Notification $notification)
    {
        $data = $notification->toGhasedakSimpleSms($notifiable);
        $receptor = $data['number'];
        $message = $data['message'];
        $sender = $data['sender'] ?? $this->defaultSender;

        // Validate required fields
        if (empty($message)) {
            throw new GhasedakSmsException('empty_message', __('ghasedak::errors.empty_message'));
        }

        if (empty($receptor)) {
            throw new GhasedakSmsException('empty_receptor', __('ghasedak::errors.empty_receptor'));
        }

        $receptor = \MahdiHejazi\LaravelGhasedakSms\Helpers\PhoneHelper::clean($receptor);

        try {
            // Prepare request data
            $requestData = [
                'message' => $message,
                'receptor' => $receptor,
                'lineNumber' => $sender,
                'clientReferenceId' => uniqid(),
                'udh' => false
            ];

            // Add optional send date if provided
            if (isset($data['senddate']) && !empty($data['senddate'])) {
                $requestData['sendDate'] = $data['senddate']; // ISO 8601 format
            }

            // Log request data
            if ($this->shouldLog()) {
                Log::info('Ghasedak Simple SMS Request', [
                    'url' => $this->config['api']['simple_url'],
                    'data' => $requestData,
                    'receptor' => $receptor
                ]);
            }

            // Send HTTP request to Ghasedak API
            $response = Http::timeout($this->config['api']['timeout'] ?? 30)
                ->withHeaders([
                    'ApiKey' => $this->apikey,
                    'Content-Type' => 'application/json',
                ])->asJson()->post($this->config['api']['simple_url'], $requestData);


            // Check if request was successful
            if (!$response->successful()) {
                if ($this->shouldLog()) {
                    Log::error('Ghasedak Simple SMS HTTP Error', [
                        'status' => $response->status(),
                        'body' => $response->body(),
                        'receptor' => $receptor,
                        'request_data' => $requestData
                    ]);
                }
                throw new GhasedakSmsException('http_error', __('ghasedak::errors.http_error', ['status' => $response->status()]));
            }

            $responseBody = $response->json();
            // Log response
            if ($this->shouldLog()) {
                Log::info('Ghasedak Simple SMS Response', [
                    'response' => $responseBody,
                    'receptor' => $receptor
                ]);
            }


            // Check API response
            if (!isset($responseBody['IsSuccess']) || $responseBody['IsSuccess'] !== true) {
                $errorCode = $responseBody['StatusCode'] ?? 'unknown';
                if ($this->shouldLog()) {
                    Log::error('Ghasedak Simple SMS API Error', [
                        'error' => $errorCode,
                        'response' => $responseBody,
                        'receptor' => $receptor,
                        'message' => $message
                    ]);
                }

                throw new GhasedakSmsException($errorCode, __('ghasedak::errors.simple_send_failed'));
            }

            // Get message ID from response
            $messageIds = $responseBody['Data']['MessageId'] ?? 0;
            if ($messageIds <= 0) {
                throw new GhasedakSmsException('send_failed', __('ghasedak::errors.send_failed'));
            }

            if ($this->shouldLog()) {
                Log::info('Simple SMS sent successfully', [
                    'messageids' => $messageIds,
                    'receptor' => $receptor,
                    'message_length' => strlen($message)
                ]);
            }

            return $responseBody;

        } catch (GhasedakSmsException $e) {
            throw $e;
        } catch (\Exception $e) {
            if ($this->shouldLog()) {
                Log::error('Failed to send simple SMS', [
                    'error' => $e->getMessage(),
                    'receptor' => $receptor,
                    'message' => $message,
                    'sender' => $sender
                ]);
            }
            throw new GhasedakSmsException('system_error', $e->getMessage());
        }
    }

    /**
     * Get template name from configuration
     * @throws GhasedakSmsException
     */
    private function getTemplate($templateKey)
    {
        $template = $this->templates[$templateKey] ?? '';

        if (empty($template)) {
            throw new GhasedakSmsException('template_not_found', __('ghasedak::errors.template_not_found', ['template' => $templateKey]));
        }

        return $template;
    }

    /**
     * Check if logging is enabled
     */
    private function shouldLog(): bool
    {
        return $this->config['logging']['enabled'] ?? true;
    }
}
