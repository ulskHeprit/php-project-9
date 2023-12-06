<?php

use Carbon\Carbon;
use DI\Container;
use Hexlet\Code\Db\Db;
use Hexlet\Code\Models\Url;
use Hexlet\Code\Repositories\UrlRepository;
use Hexlet\Code\Validators\UrlValidator;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;
use Slim\Flash\Messages;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

require_once '../vendor/autoload.php';

if ($_REQUEST['f']) {
    echo phpinfo();
    die();
}

$requiredEnvVariables = [
    'DB_HOST',
    'DB_PORT',
    'DB_DATABASE',
    'DB_USER',
    'DB_PASSWORD',
];
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();
$dotenv->required($requiredEnvVariables);

$container = new Container();

$loader = new FilesystemLoader('../templates');
$twig = new Environment($loader);

$menu = [
    'main' => [
        'name' => 'Главная',
        'href' => '/'
    ],
    'urls' => [
        'name' => 'Сайты',
        'href' => '/urls',
    ]
];

$db = Db::get($_ENV);

$urlRepository = new UrlRepository($db);

$container->set('renderer', $twig);
$container->set('db', $db);
$container->set('menu', $menu);
$container->set('flash', function () {
    $storage = [];
    return new Messages($storage);
});
$container->set('urlRepository', $urlRepository);

$app = AppFactory::createFromContainer($container);
$app->add(
    function ($request, $next) {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        $this->get('flash')->__construct($_SESSION);

        return $next->handle($request);
    }
);
$app->addErrorMiddleware(true, true, true);
$routeParser = $app->getRouteCollector()->getRouteParser();

$app->get('/', function (Request $request, Response $response) {
    $data = [
        'selected_menu' => 'main',
        'this' => $this,
        'flash' => $this->get('flash')->getMessages(),
    ];

    $data['content'] = $this->get('renderer')->render('main/index.html.twig', $data);
    return $response->write($this->get('renderer')->render('index.html.twig', $data));
})->setName('mainIndex');

$app->get('/urls', function (Request $request, Response $response) {
    $data = [
        'selected_menu' => 'urls',
        'this' => $this,
        'flash' => $this->get('flash')->getMessages(),
    ];
    $data['urls'] = $this->get('urlRepository')->getAll();

    $data['content'] = $this->get('renderer')->render('urls/index.html.twig', $data);
    return $response->write($this->get('renderer')->render('index.html.twig', $data));
})->setName('urlsIndex');

$app->get('/urls/{id:[0-9]+}', function (Request $request, Response $response, array $args) {
    $data = [
        'selected_menu' => 'urls',
        'this' => $this,
        'flash' => $this->get('flash')->getMessages(),
    ];
    $data['url'] = $this->get('urlRepository')->getById($args['id']);

    if (!$data['url']) {
        return $response->withStatus(404);
    }

    $data['content'] = $this->get('renderer')->render('urls/show.html.twig', $data);
    return $response->write($this->get('renderer')->render('index.html.twig', $data));
})->setName('urlsShow');

$app->post('/urls', function (Request $request, Response $response) use ($routeParser) {
    $urlData = $request->getParsedBodyParam('url');
    $urlData['created_at'] = Carbon::now()->format('Y-m-d H:i:s');
    $url = new Url($urlData);
    $validateErrors = UrlValidator::validate($url);

    if (count($validateErrors) === 0) {
        $urlRepository = $this->get('urlRepository');
        $existingUrl = $urlRepository->getByName($url->getName());

        if ($existingUrl) {
            $id = $existingUrl->getId();
            $this->get('flash')->addMessage('success', 'Страница уже существует');
        } else {
            $id = $urlRepository->save($url);
            $this->get('flash')->addMessage('success', 'Страница успешно добавлена');
        }

        $url = $routeParser->urlFor('urlsShow', ['id' => $id]);
        return $response->withRedirect($url);
    }

    foreach ($validateErrors as $error) {
        $this->get('flash')->addMessage('danger', $error);
    }

    $url = $routeParser->urlFor('mainIndex');
    return $response->withRedirect($url);
});

$app->run();
