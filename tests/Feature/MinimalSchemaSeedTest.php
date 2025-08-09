<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class MinimalSchemaSeedTest extends TestCase
{
    use RefreshDatabase;

    public function test_seeders_create_basic_data(): void
    {
        // Run the default DatabaseSeeder
        $this->seed();

        $this->assertDatabaseHas('branches', ['code' => 'HO']);
        $this->assertDatabaseHas('branches', ['code' => 'CML']);
        $this->assertDatabaseHas('branches', ['code' => 'CTG']);

        $this->assertDatabaseHas('accounts', ['code' => 'CASH', 'category' => 'asset']);
        $this->assertDatabaseHas('accounts', ['code' => 'BANK', 'is_bank' => 1]);
        $this->assertDatabaseHas('accounts', ['code' => 'SALES', 'category' => 'income']);

        $this->assertDatabaseHas('users', ['email' => 'admin@example.com', 'role' => 'super_admin']);
    }
}
