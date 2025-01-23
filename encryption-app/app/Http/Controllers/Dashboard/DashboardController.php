<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\User;

class DashboardController extends Controller
{
    public function index(){
        return Inertia::render('Dashboard', [
            'auth' => [
                'user' => auth()->user()
            ]
        ]);
    }
}
