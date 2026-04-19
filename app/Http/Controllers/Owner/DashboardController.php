<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $grounds = $request->user()
            ->ownedShootingGrounds()
            ->orderBy('name')
            ->get();

        return view('owner.dashboard', compact('grounds'));
    }
}
