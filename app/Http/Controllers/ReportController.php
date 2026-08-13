<?php

namespace App\Http\Controllers;

use App\Models\Household;
use Inertia\Inertia;
use Inertia\Response;

class ReportController extends Controller
{
    public function index(Household $household): Response
    {
        return Inertia::render('households/reports/Index', [
            'household' => $household,
        ]);
    }
}
