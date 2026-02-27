<?php

namespace App\Http\Controllers;

class HomeController extends Controller
{
    public function index()
    {
        // Per ora dati finti; poi li prenderemo dal DB
        $categories = [
            ['key' => 'panini',     'title' => 'Panini',     'desc' => 'Classici e gourmet'],
            ['key' => 'birre',      'title' => 'Birre',      'desc' => 'Artigianali e alla spina'],
            ['key' => 'sfiziosita', 'title' => 'Sfiziosità', 'desc' => 'Fritti e stuzzichini'],
        ];

        return view('home', compact('categories'));
    }
}
