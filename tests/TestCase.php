<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $connection = config('database.default');
        $database = config("database.connections.$connection.database");
        if ($connection !== 'mysql' || $database !== 'icc_backend_test') {
            throw new \RuntimeException('Tests must use the dedicated MySQL database icc_backend_test.');
        }
    }
}
