<?php

use PHPUnit\Framework\TestCase;

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| Bind PHPUnit's TestCase to every test in this directory so Pest's
| expectation API and assertions are available everywhere. Shared helper
| functions live in test/helpers.php, loaded via composer autoload-dev.
|
*/

uses( TestCase::class )->in( __DIR__ );
