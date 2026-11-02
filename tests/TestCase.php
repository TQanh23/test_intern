<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Test không phụ thuộc vào việc frontend đã được build hay chưa.
        $this->withoutVite();
    }
}
