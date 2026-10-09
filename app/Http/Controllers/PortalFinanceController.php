<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class PortalFinanceController extends Controller
{
    public function index(Request $request)
    {
        Gate::authorize('manage-finance');
        return view('portal.finance', ['metrics' => []]);
    }
}

