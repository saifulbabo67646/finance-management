<?php

namespace Database\Seeders;

use App\Models\Branch;
use Illuminate\Database\Seeder;

class BranchSeeder extends Seeder
{
    public function run(): void
    {
        $branches = [
            ['name' => 'Head Office', 'code' => 'HO'],
            ['name' => 'Cumilla', 'code' => 'CML'],
            ['name' => 'Chittagong', 'code' => 'CTG'],
        ];

        foreach ($branches as $b) {
            Branch::updateOrCreate(['code' => $b['code']], $b);
        }
    }
}
