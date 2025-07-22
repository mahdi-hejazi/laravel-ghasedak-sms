<?php

namespace MahdiHejazi\LaravelGhasedakSms\Tests\Unit;

use MahdiHejazi\LaravelGhasedakSms\Tests\TestCase;
use MahdiHejazi\LaravelGhasedakSms\Services\GhasedakSmsService;

class GhasedakSmsServiceTest extends TestCase
{
    /** @test */
    public function it_can_instantiate_service()
    {
        $service = new GhasedakSmsService();
        
        $this->assertInstanceOf(GhasedakSmsService::class, $service);
    }
}
