<?php declare(strict_types=1);

use App\Serve;
use Laminas\Diactoros\ServerRequestFactory;
use Laminas\HttpHandlerRunner\Emitter\SapiEmitter;
use League\Container\Container;
use League\Route;

require_once('../vendor/autoload.php');

$request = ServerRequestFactory::fromGlobals();

$container = new Container();
$container->add(App\Serve::class);

$strategy = (new Route\Strategy\ApplicationStrategy())->setContainer($container);
$router = (new Route\Router())->setStrategy($strategy);

$router->get('/', App\Serve::class);

$response = $router->dispatch($request);

(new SapiEmitter())->emit($response);
