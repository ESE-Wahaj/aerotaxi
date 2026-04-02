<?php

namespace App\Http\Controllers;

use App\Models\Airport;
use App\Models\Vehicle;
use App\Models\Faq;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index()
    {
        $airports = Airport::orderBy('sort_order')->limit(10)->get();
        $vehicles = Vehicle::orderBy('sort_order')->get();
        $faqs = Faq::orderBy('sort_order')->get();

        return view('home', compact('airports', 'vehicles', 'faqs'));
    }
}
