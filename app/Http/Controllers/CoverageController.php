<?php

namespace App\Http\Controllers;

use App\Models\Airport;
use Illuminate\Http\Request;

class CoverageController extends Controller
{
    public function index()
    {
        $airports = Airport::orderBy('sort_order')->get();

        return view('coverage', compact('airports'));
    }
}
