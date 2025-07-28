<?php

namespace MahdiHejazi\LaravelGhasedakSms\Notifications;

use MahdiHejazi\LaravelGhasedakSms\Channels\GhasedakChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class OtpSmsNotification extends Notification
{
    use Queueable;

    private $receptors;
    private $template;
    private $inputs;
    private $sendDate;
    private $isVoice;
    private $udh;

    /**
     * Create a new OTP SMS notification.
     *
     * @param array $receptors Array of ['mobile' => '09123456789', 'clientReferenceId' => 'unique_id']
     * @param string $template Template name
     * @param array $inputs Array of parameter names and values ['Code' => '1234', 'Name' => 'John']
     * @param string|null $sendDate ISO 8601 format date string
     * @param bool $isVoice Whether this is a voice message
     * @param bool $udh UDH setting
     */
    public function __construct(
        array $receptors,
        string $template,
        array $inputs = [],
        string $sendDate = null,
        bool $isVoice = false,
        bool $udh = false
    ) {
        $this->receptors = $receptors;
        $this->template = $template;
        $this->inputs = $inputs;
        $this->sendDate = $sendDate;
        $this->isVoice = $isVoice;
        $this->udh = $udh;
    }

    public function via($notifiable)
    {
        return [GhasedakChannel::class];
    }

    public function toGhasedakOtpSms($notifiable)
    {
        return [
            'receptors' => $this->receptors,
            'template' => $this->template,
            'inputs' => $this->inputs,
            'sendDate' => $this->sendDate,
            'isVoice' => $this->isVoice,
            'udh' => $this->udh,
        ];
    }

    // Static factory methods for common use cases

    /**
     * Send verification code to single recipient
     */
//    public static function verificationCode(string $phone, string $code, string $clientReferenceId = null)
//    {
//        $receptors = [
//            [
//                'mobile' => $phone,
//                'clientReferenceId' => $clientReferenceId ?? uniqid()
//            ]
//        ];
//
//        return new self($receptors, 'phoneVerifyCode', ['Code' => $code]);
//    }


}
