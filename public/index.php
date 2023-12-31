<?php

use Carbon\Carbon;
use DI\ContainerBuilder;
use DiDom\Document;
use DI\Container;
use GuzzleHttp\Client;
use Hexlet\Code\Db\Db;
use Hexlet\Code\Models\Url;
use Hexlet\Code\Models\UrlCheck;
use Hexlet\Code\Repositories\UrlCheckRepository;
use Hexlet\Code\Repositories\UrlRepository;
use Hexlet\Code\Validators\UrlValidator;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;
use Slim\Flash\Messages;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

require_once '../vendor/autoload.php';

$requiredEnvVariables = [
    'DATABASE_URL',
];
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();
$dotenv->required($requiredEnvVariables);

$databaseUrl = parse_url($_ENV['DATABASE_URL']);
$dbParams['DB_USER'] = $databaseUrl['user'];
$dbParams['DB_PASSWORD'] = $databaseUrl['pass'];
$dbParams['DB_HOST'] = $databaseUrl['host'];
$dbParams['DB_PORT'] = $databaseUrl['port'];
$dbParams['DB_DATABASE'] = ltrim($databaseUrl['path'], '/');

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

$db = Db::get($dbParams);

$urlRepository = new UrlRepository($db);
$urlCheckRepository = new UrlCheckRepository($db);

$container->set('renderer', $twig);
$container->set('db', $db);
$container->set('menu', $menu);
$container->set('flash', function () {
    $storage = [];
    return new Messages($storage);
});
$container->set('urlRepository', $urlRepository);
$container->set('urlCheckRepository', $urlCheckRepository);

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
    /** @phpstan-ignore-next-line */
    return $response->write($this->get('renderer')->render('index.html.twig', $data));
})->setName('mainIndex');

$app->get('/urls', function (Request $request, Response $response) {
    $data = [
        'selected_menu' => 'urls',
        'this' => $this,
        'flash' => $this->get('flash')->getMessages(),
    ];
    $data['urls'] = $this->get('urlRepository')->getAllArray();

    foreach ($data['urls'] as &$url) {
        $url['lastCheck'] = $this->get('urlCheckRepository')->getLastByUrlId($url['id']);
    }

    $data['content'] = $this->get('renderer')->render('urls/index.html.twig', $data);
    /** @phpstan-ignore-next-line */
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

    $data['url_checks'] = $this->get('urlCheckRepository')->getByUrlId($data['url']->getId());

    $data['content'] = $this->get('renderer')->render('urls/show.html.twig', $data);
    /** @phpstan-ignore-next-line */
    return $response->write($this->get('renderer')->render('index.html.twig', $data));
})->setName('urlsShow');

$app->post('/urls', function (Request $request, Response $response) use ($routeParser) {
    /** @phpstan-ignore-next-line */
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
        /** @phpstan-ignore-next-line */
        return $response->withRedirect($url);
    }

    //Dirt fix
    $errors = [];
    foreach ($validateErrors as $error) {
        $this->get('flash')->addMessage('danger', $error);
        $errors['danger'][] = $error;
    }

    $data = [
        'selected_menu' => 'main',
        'this' => $this,
        'flash' => $errors,
    ];

    $data['content'] = $this->get('renderer')->render('main/index.html.twig', $data);
    /** @phpstan-ignore-next-line */
    return $response->withStatus(422)->write($this->get('renderer')->render('index.html.twig', $data));
});

$app->post(
    '/urls/{url_id:[0-9]+}/checks',
    function (Request $request, Response $response, array $args) use ($routeParser) {
        $url = $this->get('urlRepository')->getById($args['url_id']);

        if (!$url) {
            return $response->withStatus(404);
        }

        $client = new Client([
            'base_uri' => $url->getName(),
            'timeout'  => 3,
        ]);
        $urlResponse = $client->request('GET');
        $html = $urlResponse->getBody()->getContents();
        $document = new Document($html);
        /** @phpstan-ignore-next-line */
        $h1 = $document->first('h1')->innerHtml();
        /** @phpstan-ignore-next-line */
        $title = $document->first('title')->innerHtml();
        /** @phpstan-ignore-next-line */
        $description = $document->first("meta[name=description]")->attr('content');

        $urlCheckRepository = $this->get('urlCheckRepository');
        $urlCheck = new UrlCheck([
            'url_id'      => $url->getId(),
            'status_code' => $urlResponse->getStatusCode(),
            'h1'          => $h1,
            'title'       => $title,
            'description' => $description,
            'created_at'  => Carbon::now()->format('Y-m-d H:i:s'),
        ]);

        if ($urlCheckRepository->save($urlCheck)) {
            $url = $routeParser->urlFor('urlsShow', ['id' => $url->getId()]);
            $this->get('flash')->addMessage('success', 'Страница успешно проверена');
            /** @phpstan-ignore-next-line */
            return $response->withRedirect($url);
        } else {
            return $response->withStatus(404);
        }
    }
)->setName('urlsShow');

$app->run();
