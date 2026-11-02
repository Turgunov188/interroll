<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // pages are tested without the built CSS/JS (npm run build is not needed for the PHP tests)
        $this->withoutVite();
    }
}
