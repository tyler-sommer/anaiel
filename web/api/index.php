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
    $r->map('/docs/{id}.json', null, function (Application $app, $id) {
        $conn = $app->get('doctrine.dbal.database_connection');

        $title = $conn->executeQuery('SELECT p.title FROM anaiel.pages p WHERE p.id = :id', array('id' => $id))->fetchColumn(0);
        $items = $conn->executeQuery('SELECT ps.id, ps.order, ps.text as comment, ps.code FROM anaiel.page_sections ps WHERE ps.page_id = :id', array('id' => $id))->fetchAll();

        return new JsonResponse(array(
            'title' => $title,
            'lines' => $items
        ));
    });

    $r->map('/menu/items.json', null, function (Application $app, Request $request) {
        $conn = $app->get('doctrine.dbal.database_connection');

        $items = $conn->executeQuery('SELECT p.id, b.id as book_id, p.order, p.title, b.title as book_title FROM anaiel.pages p JOIN anaiel.books b ON p.book_id = b.id ORDER BY p.order')->fetchAll();

        $menus = array();
        foreach ($items as $item) {
            if (!isset($menus[$item['book_id']])) {
                $menus[$item['book_id']] = array(
                    'items' => array(),
                    'title' => $item['book_title']
                );
            }

            $menus[$item['book_id']]['items'][] = $item;
        }

        return new JsonResponse(array_values($menus));
    });
});

$app->run();
