<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class EmployeeDashboardController extends Controller
{
    public function index()
    {
        return view('employee.dashboard'); // view file: resources/views/employee/dashboard.blade.php
    }
}
