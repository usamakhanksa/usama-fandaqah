<?php

namespace Tests\Unit\Foundation;

use App\Services\Foundation\ReservationService;
use PHPUnit\Framework\TestCase;

class ReservationServiceTest extends TestCase
{
    public function test_it_calculates_reservation_totals(): void
    {
        $service = new ReservationService();
        $reflection = new \ReflectionClass($service);
        $method = $reflection->getMethod('hydrateCalculated');
        $method->setAccessible(true);
        $result = $method->invoke($service, [
            'check_in_date' => '2026-05-01',
            'check_out_date' => '2026-05-04',
            'night_rate_sar' => 100,
            'tax_sar' => 45,
            'status' => 'pending',
        ]);

        $this->assertSame(3, $result['nights']);
        $this->assertSame(300.0, $result['subtotal_sar']);
        $this->assertSame(345.0, $result['total_sar']);
    }
}
