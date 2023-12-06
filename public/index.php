<?php

use DI\Container;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

require_once '../vendor/autoload.php';

$loader = new FilesystemLoader('../templates');
$twig = new Environment($loader);
$container = new Container();
$container->set('renderer', $twig);

$menu = [
    'main' => [
        'name' => 'Главная',
        'href' => '/'
    ],
    'sites' => [
        'name' => 'Сайты',
        'href' => '/urls',
    ]
];

$container->set('menu', $menu);

$app = AppFactory::createFromContainer($container);
$app->addErrorMiddleware(true, true, true);

$app->get('/', function(Request $request, Response $response) {
    $data = [
        'selected_menu' => 'main',
        'this' => $this,
    ];

    return $response->write($this->get('renderer')->render('index.html.twig', $data));
});

$app->run();
