# Laravel Ghasedak SMS

Modern Laravel package for Ghasedak SMS API with template and simple SMS support.

## Features

- ✅ **Template-based SMS** (OTP, verification codes, etc.)
- ✅ **Simple SMS** (free text messages)
- ✅ **Queue support** with Laravel notifications
- ✅ **Persian error messages**
- ✅ **Configurable templates**
- ✅ **Logging support**

## Installation

### Requirements

- PHP 8.1, 8.2, or 8.3
- Laravel 9, 10, 11, or 12
- Composer

### Install via Composer

```bash
composer require mahdi-hejazi/laravel-ghasedak-sms
```

### Docker Development

If you're developing with Docker, you can use the included Docker setup:

```bash
# Clone the package
git clone https://github.com/mahdi-hejazi/laravel-ghasedak-sms.git
cd laravel-ghasedak-sms

# Build and start containers
docker-compose up -d --build

# Install dependencies
docker-compose exec php composer install

# Run tests
docker-compose exec php vendor/bin/phpunit

# Stop containers
docker-compose down
```

### Docker Files Included

The package includes a lightweight Docker setup for development:

**Dockerfile:**
```dockerfile
FROM php:8.2-apache

# Install system dependencies
RUN apt-get update && apt-get install -y \
    curl \
    unzip \
    && rm -rf /var/lib/apt/lists/*

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php && \
    mv composer.phar /usr/local/bin/composer

WORKDIR /app
```

**docker-compose.yml:**
```yaml
version: '3.8'

services:
    php:
        build:
            context: .
            dockerfile: Dockerfile
        volumes:
            - ./:/app
        working_dir: /app
        command: tail -f /dev/null

networks:
    default:
        driver: bridge
```

### Publish Configuration

```bash
php artisan vendor:publish --provider="MahdiHejazi\LaravelGhasedakSms\GhasedakSmsServiceProvider" --tag="ghasedak-config"
```

## Configuration

Add your Ghasedak credentials to your `.env` file:

```env
GHASEDAK_API_KEY=your_api_key_here
GHASEDAK_SENDER=10008566
GHASEDAK_TEMPLATE_VERIFY_CODE=your_template_name
```

## Usage

### Template-based SMS (OTP/Verification)

```php
use MahdiHejazi\LaravelGhasedakSms\Notifications\SendSmsNotification;

// Using Static factory methods in your notification
$user->notify(SendSmsNotification::verificationCode('1234', '09123456789'));

// Or generic approach
$user->notify(new SendSmsNotification('phoneVerifyCode', '09123456789', ['1234']));

// Using facade
use MahdiHejazi\LaravelGhasedakSms\Facades\GhasedakSms;

GhasedakSms::sendVerificationCode('09123456789', '1234');
```

### Simple SMS

```php
use MahdiHejazi\LaravelGhasedakSms\Notifications\SimpleSmsNotification;

// Using notification
$user->notify(new SimpleSmsNotification('09123456789', 'Your custom message'));

// Using facade
GhasedakSms::sendSimple('09123456789', 'Your custom message');
```

### Available Factory Methods

```php
// Built-in template SMS factory methods
SendSmsNotification::verificationCode($code, $phone);
SendSmsNotification::orderConfirmed($phone, $orderId, $amount, $date);
SendSmsNotification::thankYou($phone, $customerName);
SendSmsNotification::passwordReset($phone, $resetCode);
SendSmsNotification::welcome($phone, $userName);

// Simple SMS factory methods
SimpleSmsNotification::create($phone, $message, $sender);
SimpleSmsNotification::scheduled($phone, $message, $sendDate, $sender);
```

### Adding Custom Factory Methods

You can extend the notification classes to add your own factory methods:

#### Method 1: Extend the Notification Class

```php
<?php

namespace App\Notifications;

use MahdiHejazi\LaravelGhasedakSms\Notifications\SendSmsNotification;

class CustomSmsNotification extends SendSmsNotification
{
    // Add your custom factory methods
    public static function appointmentReminder($phone, $doctorName, $date, $time)
    {
        return new self('appointmentReminder', $phone, [$doctorName, $date, $time]);
    }

    public static function paymentConfirmation($phone, $amount, $transactionId)
    {
        return new self('paymentConfirmed', $phone, [$amount, $transactionId]);
    }

    public static function productAvailable($phone, $productName, $price)
    {
        return new self('productAvailable', $phone, [$productName, $price]);
    }
}
```

**Usage:**
```php
use App\Notifications\CustomSmsNotification;

$user->notify(CustomSmsNotification::appointmentReminder(
    '09123456789', 
    'Dr. Smith', 
    '1403/10/15', 
    '14:30'
));
```

#### Method 2: Create Your Own Notification Service

```php
<?php

namespace App\Services;

use MahdiHejazi\LaravelGhasedakSms\Notifications\SendSmsNotification;
use Illuminate\Support\Facades\Notification;

class BusinessSmsService
{
    public function sendAppointmentReminder($phone, $doctorName, $date, $time)
    {
        return Notification::route('sms', $phone)
            ->notify(new SendSmsNotification('appointmentReminder', $phone, [
                $doctorName, $date, $time
            ]));
    }

    public function sendLowStockAlert($phone, $productName, $currentStock)
    {
        return Notification::route('sms', $phone)
            ->notify(new SendSmsNotification('lowStock', $phone, [
                $productName, $currentStock
            ]));
    }
}
```

**Usage:**
```php
use App\Services\BusinessSmsService;

$smsService = new BusinessSmsService();
$smsService->sendAppointmentReminder('09123456789', 'Dr. Smith', '1403/10/15', '14:30');
```

#### Method 3: Using Macros (Advanced)

Add to your `AppServiceProvider.php`:

```php
use MahdiHejazi\LaravelGhasedakSms\Notifications\SendSmsNotification;

public function boot()
{
    SendSmsNotification::macro('courseEnrollment', function ($phone, $courseName, $startDate) {
        return new SendSmsNotification('courseEnrollment', $phone, [$courseName, $startDate]);
    });
}
```

**Usage:**
```php
$user->notify(SendSmsNotification::courseEnrollment('09123456789', 'Laravel Course', '1403/11/01'));
```

### Template Requirements

When adding custom templates, remember:

1. **Create in Ghasedak Panel**: First create your template at [ghasedak.me](https://ghasedak.me)
2. **Add to Config**: Add template mapping to `config/ghasedak.php`
3. **Parameter Limit**: Maximum 3 parameters (`%param1%`, `%param2%`, `%param3%`)

**Example Custom Template Setup:**

1. **In Ghasedak Panel:**
```text
Template Name: appointmentReminder
Template Text: سلام، یادآوری قرار ملاقات شما با %param1% در تاریخ %param2% ساعت %param3%
```

2. **In config/ghasedak.php:**
```php
'templates' => [
    'appointmentReminder' => 'appointmentReminder',
    // other templates...
],
```

3. **In Your Code:**
```php
CustomSmsNotification::appointmentReminder('09123456789', 'Dr. Smith', '1403/10/15', '14:30');
```

### Queue Support

Both notifications support Laravel queues:

```php
// Make notification queueable
$user->notify((new SendSmsNotification('phoneVerifyCode', $phone, [$code]))->delay(30));
```

### Error Handling

```php
use MahdiHejazi\LaravelGhasedakSms\Exceptions\GhasedakSmsException;

try {
    $user->notify(SendSmsNotification::verificationCode('1234', '09123456789'));
} catch (GhasedakSmsException $e) {
    // Handle specific errors
    $errorCode = $e->getErrorCode();
    $message = $e->getMessage(); // Persian error message
    
    if ($errorCode == 9) {
        // Insufficient balance
    }
}
```

## Configuration

### Prerequisites

⚠️ **Important**: Before using template-based SMS, you must:

1. **Create templates in Ghasedak panel** at [ghasedak.me](https://ghasedak.me)
2. Go to `SMS Panel > Templates > Create Template`
3. Create your template with parameters like `%param1%`, `%param2%`, `%param3%`
4. Get the template name from your panel
5. Add template name to your config

### Templates

Define your SMS templates in `config/ghasedak.php`:

```php
'templates' => [
    'phoneVerifyCode' => 'your_template_name_in_ghasedak_panel', // ← Must exist in Ghasedak panel
    'orderConfirmed' => 'order_confirmed_template',              // ← Must exist in Ghasedak panel
    'passwordReset' => 'password_reset_template',                // ← Must exist in Ghasedak panel
    // Add more templates...
],
```

**Example Ghasedak Template:**
```text
Template Name: verifyCodeTemplate
Template Text: کد تایید شما: %param1% 
              این کد تا 5 دقیقه معتبر است.
              شرکت شما
```

## Step-by-Step Template Setup

### 1. Create Template in Ghasedak Panel

1. Visit [ghasedak.me](https://ghasedak.me) and login
2. Go to **SMS Panel > Templates**
3. Click **Create New Template**
4. Fill template details:
    - **Template Name**: `verifyCodeTemplate`
    - **Template Text**: `کد تایید شما: %param1% - این کد 5 دقیقه معتبر است`
    - **Category**: Select appropriate category
5. Submit and wait for approval

### 2. Add to Laravel Config

```php
// config/ghasedak.php
'templates' => [
    'phoneVerifyCode' => 'verifyCodeTemplate', // ← Exact template name from panel
],
```

### 3. Use in Code

```php
$user->notify(SendSmsNotification::verificationCode('1234', '09123456789'));
```

## Advanced Examples

## Advanced Examples

### E-commerce Notifications

```php
// Extend the notification class
class EcommerceSmsNotification extends SendSmsNotification
{
    public static function orderShipped($phone, $orderNumber, $trackingCode)
    {
        return new self('orderShipped', $phone, [$orderNumber, $trackingCode]);
    }
    
    public static function priceDropAlert($phone, $productName, $newPrice)
    {
        return new self('priceDropAlert', $phone, [$productName, $newPrice]);
    }
}

// Usage
$user->notify(EcommerceSmsNotification::orderShipped('09123456789', 'ORD-123', 'TR-456'));
```

### Medical Center Notifications

```php
class MedicalSmsNotification extends SendSmsNotification
{
    public static function appointmentConfirmed($phone, $doctorName, $date, $time)
    {
        return new self('appointmentConfirmed', $phone, [$doctorName, $date, $time]);
    }
}
```

### Bulk SMS Service

```php
use MahdiHejazi\LaravelGhasedakSms\Notifications\SimpleSmsNotification;

class BulkSmsService
{
    public function sendToMultipleUsers($phoneNumbers, $message)
    {
        foreach ($phoneNumbers as $phone) {
            Notification::route('sms', $phone)
                ->notify(new SimpleSmsNotification($phone, $message));
        }
    }
}
```

### Logging

Control SMS logging:

```php
'logging' => [
    'enabled' => env('GHASEDAK_LOGGING', true),
    'channel' => env('LOG_CHANNEL', 'stack'),
],
```

## Testing

### Mock Tests (Free - No API Key Required)
```bash
# Run all mock tests
docker-compose exec php vendor/bin/phpunit
```

### Real API Tests (Costs Money - Sends Real SMS)
 1. Set your real API credentials
```bash
  cp tests/.env.example tests/.env
   ```
   Edit tests/.env with your GHASEDAK_API_KEY and template name
 2. Run real API tests
   ```bash
 docker-compose exec php vendor/bin/phpunit --group integration
 ```
Note: Mock tests use fake responses and don't send real SMS. Integration tests send actual SMS and will cost money.
## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security

If you discover any security related issues, please email your-email@example.com instead of using the issue tracker.

## Credits

- [Seyed Mahdi Hejazi](https://github.com/mahdi-hejazi)

## License

The MIT License (MIT). Please see [License File](LICENSE) for more information.