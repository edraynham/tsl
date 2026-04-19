<?php

namespace App\Http\Controllers;

use App\Models\Instructor;
use Illuminate\View\View;

class InstructorController extends Controller
{
    public function index(): View
    {
        $instructors = Instructor::query()
            ->with('user')
            ->orderBy('name')
            ->get();

        return view('instructors.index', compact('instructors'));
    }

    public function show(Instructor $instructor): View
    {
        $instructor->load('user');

        return view('instructors.show', compact('instructor'));
    }
}
