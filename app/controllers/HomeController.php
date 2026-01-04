<?php

declare(strict_types=1);

require_once __DIR__ . '/../Core/Controller.php';

class HomeController extends Controller
{
    public function index(): void
    {
        $data = [];
        $this->view('home.php', $data);
    }
}
