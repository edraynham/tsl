<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OwnerCompetitionsController extends Controller
{
    public function index(Request $request): View
    {
        $grounds = $request->user()
            ->ownedShootingGrounds()
            ->with(['competitions' => fn ($q) => $q->orderBy('starts_at')])
            ->orderBy('name')
            ->get();

        return view('owner.competitions.index', [
            'grounds' => $grounds,
            'highlightGroundSlug' => $request->query('ground'),
        ]);
    }
}
