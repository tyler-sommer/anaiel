<?php

use Nice\Extension\DoctrineDbalExtension;
use Nice\Extension\SecurityExtension;
use Nice\Extension\SessionExtension;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Nice\Application;
use Nice\Router\RouteCollector;

require __DIR__ . '/../../vendor/autoload.php';

$app = new Application('dev', true, false);
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
        'host'     => '127.0.0.1',
        'user'     => 'root',
        'password' => ''
    ]
]));
$app->set('routes', function (RouteCollector $r) {
    $r->map('/', 'home', function (Request $request) {
        return new Response('Hello, world');
    });

    $r->map('/menu/items.json', null, function (Application $app, Request $request) {
        $conn = $app->get('doctrine.dbal.database_connection');

        $items = $conn->executeQuery('SELECT ps.id, ps.page_id, ps.title, ps.order, ps.text, ps.code, p.title as page_title FROM anaiel.page_sections ps JOIN anaiel.pages p ON ps.page_id = p.id ORDER BY ps.order')->fetchAll();

        $menus = array();
        foreach ($items as $item) {
            if (!isset($menus[$item['page_id']])) {
                $menus[$item['page_id']] = array(
                    'items' => array(),
                    'title' => $item['page_title']
                );
            }

            $menus[$item['page_id']]['items'][] = $item;
        }

        return new JsonResponse(array_values($menus));
    });
});

$app->run();
