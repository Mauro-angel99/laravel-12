<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ProcessingParametersController extends Controller
{
    public function index()
    {
        return view('processing-parameters.index');
    }
}
