<?php
namespace Tests;

trait SeedDatabase
{
    public function setUp(): void
    {
        parent::setUp();
        $this->artisan('db:seed');
    }
}
