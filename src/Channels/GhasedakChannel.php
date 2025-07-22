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

        // Clean parameters as before
        foreach ($parameters as $key => $param) {
            $text = preg_replace('/[^\w\s]+/u', '', $param);
            $text = preg_replace('/\s+/', '.', $text);
            $parameters[$key] = str_replace('_', '.', $text);
        }

        try {
            // Note: Ghasedak template API actually supports only 3 parameters (param1, param2, param3)
            // But we'll handle up to 10 for backward compatibility, using only first 3
            $requestData = [
                'type' => 1, // 1 for text message, 2 for voice
                'receptor' => $receptor,
                'template' => $template,
            ];

            // Add parameters (Ghasedak API supports max 3 params for templates)
            if (isset($parameters[0])) $requestData['param1'] = $parameters[0];
            if (isset($parameters[1])) $requestData['param2'] = $parameters[1];
            if (isset($parameters[2])) $requestData['param3'] = $parameters[2];

            // Log if more than 3 parameters provided (for debugging)
            if (count($parameters) > 3 && $this->shouldLog()) {
                Log::warning('Ghasedak template SMS: More than 3 parameters provided, using only first 3', [
                    'total_params' => count($parameters),
                    'template' => $template
                ]);
            }

            // Send HTTP request to Ghasedak API
            $response = Http::timeout($this->config['api']['timeout'] ?? 30)
                ->withHeaders([
                    'apikey' => $this->apikey,
                    'Content-Type' => 'application/x-www-form-urlencoded',
                    'cache-control' => 'no-cache',
                ])->asForm()->post($this->config['api']['verify_url'], $requestData);

            // Check if request was successful
            if (!$response->successful()) {
                if ($this->shouldLog()) {
                    Log::error('Ghasedak Template SMS HTTP Error', [
                        'status' => $response->status(),
                        'body' => $response->body(),
                        'receptor' => $receptor
                    ]);
                }
                throw new GhasedakSmsException('http_error', __('ghasedak::errors.http_error', ['status' => $response->status()]));
            }

            $responseBody = $response->json();

            // Check API response
            if (!isset($responseBody['result']) || $responseBody['result'] !== 'success') {
                $errorCode = $responseBody['message'] ?? $responseBody['messageids'] ?? 'unknown';
                if ($this->shouldLog()) {
                    Log::error('Ghasedak Template SMS API Error', [
                        'error' => $errorCode,
                        'response' => $responseBody,
                        'receptor' => $receptor
                    ]);
                }

                throw new GhasedakSmsException($errorCode, __('ghasedak::errors.template_send_failed'));
            }

            // Check if messageids is greater than 1000 (successful send indicator)
            $messageIds = $responseBody['messageids'] ?? 0;
            if ($messageIds <= 1000) {
                throw new GhasedakSmsException($messageIds, __('ghasedak::errors.send_failed'));
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

        try {
            // Prepare request data
            $requestData = [
                'message' => $message,
                'receptor' => $receptor,
                'sender' => $sender,
            ];

            // Add optional send date if provided
            if (isset($data['senddate']) && !empty($data['senddate'])) {
                $requestData['senddate'] = $data['senddate'];
            }

            // Add optional checking IDs if provided
            if (isset($data['checkingids']) && !empty($data['checkingids'])) {
                $requestData['checkingids'] = $data['checkingids'];
            }

            // Send HTTP request to Ghasedak API
            $response = Http::timeout($this->config['api']['timeout'] ?? 30)
                ->withHeaders([
                    'apikey' => $this->apikey,
                    'Content-Type' => 'application/x-www-form-urlencoded',
                    'cache-control' => 'no-cache',
                ])->asForm()->post($this->config['api']['simple_url'], $requestData);

            // Check if request was successful
            if (!$response->successful()) {
                if ($this->shouldLog()) {
                    Log::error('Ghasedak Simple SMS HTTP Error', [
                        'status' => $response->status(),
                        'body' => $response->body(),
                        'receptor' => $receptor
                    ]);
                }
                throw new GhasedakSmsException('http_error', __('ghasedak::errors.http_error', ['status' => $response->status()]));
            }

            $responseBody = $response->json();

            // Check API response
            if (!isset($responseBody['result']) || $responseBody['result'] !== 'success') {
                $errorCode = $responseBody['messageids'] ?? $responseBody['message'] ?? 'unknown';
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

            // Check if messageids is greater than 1000 (successful send indicator)
            $messageIds = $responseBody['messageids'] ?? 0;
            if ($messageIds <= 1000) {
                throw new GhasedakSmsException($messageIds, __('ghasedak::errors.send_failed'));
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
