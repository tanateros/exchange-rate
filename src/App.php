<?php

namespace ExchangeRate;

use Pecee\SimpleRouter\SimpleRouter;

class App
{
    public function run(): void
    {
        $this->registerRoutes();
    }

    protected function registerRoutes()
    {
        Container::getCacheService()->init(CACHE_DRIVER);

        $routes = [];
        require_once CONFIG_PATH . '/routes.php';

        foreach ($routes as $route => $data) {
            SimpleRouter::{$data['method']}(
                $route, $data['controller'] . '@' . $data['action']
            )->setName($data['name']);
        }

        SimpleRouter::start();
    }
}