<?php

namespace Tests\Unit;

use App\Models\Branch;
use App\Services\VoucherService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class VoucherServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_generates_incrementing_voucher_numbers_per_branch_and_day(): void
    {
        $ho = Branch::create(['name' => 'Head Office', 'code' => 'HO']);
        $ctg = Branch::create(['name' => 'Chittagong', 'code' => 'CTG']);

        $svc = new VoucherService();

        $date = '2025-01-02';

        $v1 = $svc->generate($ho->id, $date);
        $v2 = $svc->generate($ho->id, $date);
        $v3 = $svc->generate($ctg->id, $date);
        $vNextDay = $svc->generate($ho->id, '2025-01-03');

        $this->assertSame('BR-HO-20250102-0001', $v1);
        $this->assertSame('BR-HO-20250102-0002', $v2);
        $this->assertSame('BR-CTG-20250102-0001', $v3); // different branch resets
        $this->assertSame('BR-HO-20250103-0001', $vNextDay); // next day resets
    }
}
