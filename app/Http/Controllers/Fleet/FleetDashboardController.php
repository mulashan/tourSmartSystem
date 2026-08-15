<?php

namespace App\Http\Controllers\Fleet;

use App\Http\Controllers\Controller;

class FleetDashboardController extends Controller
{
    public function index()
    {
        return $this->nicePage('templates.fleet.dashboard', 'fleet.dashboard', []);
    }
}