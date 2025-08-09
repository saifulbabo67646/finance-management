<?php

namespace App\Http\Controllers;

use App\Models\Branch;

class BranchesController extends Controller
{
    public function index()
    {
        return response()->json(
            Branch::query()->orderBy('code')->get(['id', 'name', 'code'])
        );
    }
}
