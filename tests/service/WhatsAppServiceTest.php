<?php

namespace App\Tests\Service;

use App\Service\WhatsAppService;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class WhatsAppServiceTest extends TestCase
{
    private $whatsAppService;
    private $logger;

    protected function setUp(): void
    {
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->whatsAppService = new WhatsAppService(
            'test_sid',
            'test_token',
            '+1234567890',
            $this->logger
        );
    }

    public function testFormatPhoneNumberWithPlus(): void
    {
        $phone = '+21612345678';
        $formatted = $this->whatsAppService->formatPhoneNumber($phone);
        $this->assertEquals('+21612345678', $formatted);
    }

    public function testFormatPhoneNumberWithZero(): void
    {
        $phone = '012345678';
        $formatted = $this->whatsAppService->formatPhoneNumber($phone);
        $this->assertEquals('+21612345678', $formatted);
    }

    public function testFormatPhoneNumberWithInternationalPrefix(): void
    {
        $phone = '0021612345678';
        $formatted = $this->whatsAppService->formatPhoneNumber($phone);
        $this->assertEquals('+21612345678', $formatted);
    }

    public function testFormatPhoneNumberWithSpaces(): void
    {
        $phone = '+216 12 345 678';
        $formatted = $this->whatsAppService->formatPhoneNumber($phone);
        $this->assertEquals('+21612345678', $formatted);
    }

    public function testFormatPhoneNumberWithDashes(): void
    {
        $phone = '+216-12-345-678';
        $formatted = $this->whatsAppService->formatPhoneNumber($phone);
        $this->assertEquals('+21612345678', $formatted);
    }
}