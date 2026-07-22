<?php

namespace App\Http\Controllers;

use App\Services\MenuService;

abstract class Controller
{
    protected function nicePage(string $view, string $active, array $data = [])
    {
        return app(MenuService::class)->page($view, $active, $data);
    }

    protected function permissionMenus(): array
    {
        return app(MenuService::class)->permissionMenus();
    }
}
