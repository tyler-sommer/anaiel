<?php

use Anaiel\Extension\AnaielExtension;
use Nice\Extension\DoctrineDbalExtension;
use Nice\Extension\SecurityExtension;
use Nice\Extension\SessionExtension;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
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
$app->appendExtension(new AnaielExtension());
$app->set('routes', function (RouteCollector $r) {
    $r->map('/books.json', null, function (Application $app) {
        $repo = $app->get('anaiel.book_repository');

        return new JsonResponse($repo->findAll());
    });

    $r->map('/books.json', null, function (Application $app, Request $request) {
        $repo = $app->get('anaiel.book_repository');

        $books = json_decode($request->getContent(), true);

        foreach ($books as $book) {
            $repo->save($book);
        }

        return new JsonResponse();
    }, array('POST'));
});

$app->run();
