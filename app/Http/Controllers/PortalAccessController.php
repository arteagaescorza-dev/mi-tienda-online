<?php

namespace App\Http\Controllers;

class PortalAccessController extends Controller
{
    public function userLogin()
    {
        return view('auth.user-login');
    }

    public function clientLogin()
    {
        return view('auth.client-login');
    }
}
