<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InstructorProfileController extends Controller
{
    public function __invoke(Request $request): View
    {
        $instructor = $request->user()->instructorProfile;

        return view('account.instructor', compact('instructor'));
    }
}
