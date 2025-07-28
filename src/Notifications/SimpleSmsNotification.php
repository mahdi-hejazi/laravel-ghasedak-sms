<?php

namespace MahdiHejazi\LaravelGhasedakSms\Notifications;

use MahdiHejazi\LaravelGhasedakSms\Channels\GhasedakChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class SimpleSmsNotification extends Notification
{
    use Queueable;

    private $phoneNumber;
    private $message;
    private $sender;
    private $sendDate;
    private $checkingIds;

    public function __construct($phone, $message, $sender = null, $sendDate = null, $checkingIds = null)
    {
        $this->phoneNumber = $phone;
        $this->message = $message;
        $this->sender = $sender;
        $this->sendDate = $sendDate;
        $this->checkingIds = $checkingIds;
    }

    public function via($notifiable)
    {
        return [GhasedakChannel::class]; // Uses same channel
    }

    public function toGhasedakSimpleSms($notifiable)
    {
        $data = [
            'number' => $this->phoneNumber,
            'message' => $this->message,
        ];

        // Add optional fields if provided
        if ($this->sender) {
            $data['sender'] = $this->sender;
        }

        if ($this->sendDate) {
            $data['senddate'] = $this->sendDate;
        }

        if ($this->checkingIds) {
            $data['checkingids'] = $this->checkingIds;
        }

        return $data;
    }

    // Static factory methods
    public static function create($phone, $message, $sender = null)
    {
        return new self($phone, $message, $sender);
    }

    public static function scheduled($phone, $message, $sendDate, $sender = null)
    {
        return new self($phone, $message, $sender, $sendDate);
    }
}
