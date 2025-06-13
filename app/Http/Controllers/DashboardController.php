<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function penjualDashboard()
    {
        $user = Auth::user();
        $er = 5;
        return view('penjual.dashboard', compact('user', 'er'));
    }
}