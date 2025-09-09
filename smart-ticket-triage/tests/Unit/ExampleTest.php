<?php

declare(strict_types=1);

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Services\RandomClassifier;

final class ExampleTest extends TestCase
{
    public function test_random_classifier_contract(): void
    {
        $svc = new RandomClassifier();
        $out = $svc->predict('ignored');

        $this->assertIsArray($out);
        $this->assertArrayHasKey('category', $out);
        $this->assertArrayHasKey('explanation', $out);
        $this->assertArrayHasKey('confidence', $out);

        $allowed = ['Billing','Bug','Access','Feature Request','Outage','Other'];
        $this->assertContains($out['category'], $allowed);

        $this->assertIsFloat($out['confidence']);
        $this->assertGreaterThanOrEqual(0.30, $out['confidence']);
        $this->assertLessThanOrEqual(0.90, $out['confidence']);

        $this->assertIsString($out['explanation']);
        $this->assertNotSame('', trim($out['explanation']));
    }
}