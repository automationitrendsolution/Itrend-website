<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;

final class HomeController extends Controller
{
    public function index(Request $request): void
    {
        $this->view('home', [
            'page'        => 'home',
            'title'       => 'iTrend Solution — We Build & Deliver Products Across Global Marketplaces',
            'description' => 'iTrend Solution is a product-first commerce company that sources, builds and delivers quality products across five global marketplaces. 5,000+ SKUs, 7+ countries, 60+ specialists, since 2016.',
        ]);
    }
}
