<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

class HomeController extends Controller
{
    /**
     * Home pengguna: sapaan + redeem + MiniApp milik (PRD §5.3).
     */
    public function index(Request $request): View
    {
        return view('home', [
            'miniapps' => $request->user()->ownedProducts('miniapp'),
        ]);
    }
}
