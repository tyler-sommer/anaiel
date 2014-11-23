<?php

use Nice\Extension\DoctrineDbalExtension;
use Nice\Extension\SecurityExtension;
use Nice\Extension\SessionExtension;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Nice\Application;
use Nice\Router\RouteCollector;

require __DIR__ . '/../../vendor/autoload.php';

$app = new Application();
$app->appendExtension(new SessionExtension());
$app->appendExtension(new SecurityExtension([
    'firewall' => '^/manage',
    'authenticator' => [
        'type' => 'username',
        'username' => 'admin',
        'password' => 'password'
    ]
]));
$app->appendExtension(new DoctrineDbalExtension([
    'database' => [
        'driver'   => 'pdo_mysql',
        'user'     => 'root',
        'password' => '',
        'database' => 'anaiel'
    ]
]));
$app->set('routes', function (RouteCollector $r) {
    $r->map('/', 'home', function (Request $request) {
        return new Response('Hello, world');
    });
});

$app->run();
