<?php

declare(strict_types=1);

class Controller
{
    protected function view(string $file, array $data = []): void
    {
        View::render($file, $data);
    }
}
