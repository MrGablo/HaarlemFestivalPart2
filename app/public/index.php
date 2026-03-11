<?php

/**
 * This is the central route handler of the application.
 * It uses FastRoute to map URLs to controller methods.
 * 
 * See the documentation for FastRoute for more information: https://github.com/nikic/FastRoute
 */

require __DIR__ . '/../vendor/autoload.php';

use FastRoute\RouteCollector;
use function FastRoute\simpleDispatcher;
use App\Utils\Env;
use App\Utils\Session;

Env::load();

/**
 * Define the routes for the application.
 */
$dispatcher = simpleDispatcher(function (RouteCollector $r) {
    $r->addRoute('GET', '/', handler: ['App\Controllers\AuthController', 'showLogin']);
    $r->addRoute('GET', '/hello/{name}', ['App\Controllers\HelloController', 'greet']);

    //Stories routes
    $r->addRoute('GET', '/stories', ['App\Controllers\StoriesController', 'index']);

    //Jazz Festival routes
    $r->addRoute('GET', '/jazz', ['App\Controllers\JazzController', 'home']);
    $r->addRoute('GET', '/jazz/artist', ['App\Controllers\JazzController', 'artist']);

    //user authorization
    $r->addRoute('GET',  '/login',    ['App\Controllers\AuthController', 'showLogin']);
    $r->addRoute('POST', '/login',    ['App\Controllers\AuthController', 'login']);
    $r->addRoute('GET',  '/logout',   ['App\Controllers\AuthController', 'logout']);
    //user registration
    $r->addRoute('GET',  '/register', ['App\Controllers\AuthController', 'showRegister']);
    $r->addRoute('POST', '/register', ['App\Controllers\AuthController', 'register']);

    // account management (backend only)
    $r->addRoute('POST', '/account/update', ['App\Controllers\UserController', 'updateAccount']);
    $r->addRoute('POST', '/account/delete', ['App\Controllers\UserController', 'deleteAccount']);

    // account management (view + form submit)
    $r->addRoute('GET', '/account/manage', ['App\Controllers\UserController', 'showManageAccount']);
    $r->addRoute('POST', '/account/manage/update', ['App\Controllers\UserController', 'updateAccountForm']);
    $r->addRoute('POST', '/account/manage/delete', ['App\Controllers\UserController', 'deleteAccountForm']);

    //image upload route (backend only)
    $r->addRoute('POST', '/upload/image', ['App\Controllers\UploadController', 'image']);

    // order/cart routes (logged-in users)
    $r->addRoute('POST', '/order/item/add', ['App\Controllers\OrderController', 'addItem']);
    $r->addRoute('POST', '/order/item/remove', ['App\Controllers\OrderController', 'removeItem']);
    $r->addRoute('POST', '/order/item/quantity', ['App\Controllers\OrderController', 'updateItemQuantity']);

    // CMS routes (admin only)
    $r->addRoute('GET', '/cms', ['App\Controllers\CMSController', 'generalIndex']);
    $r->addRoute('GET', '/cms/pages', ['App\Controllers\CMSController', 'index']);
    $r->addRoute('GET', '/cms/page/{id:\\d+}', ['App\Controllers\CMSController', 'edit']);
    $r->addRoute('POST', '/cms/page/{id:\\d+}/update', ['App\Controllers\CMSController', 'update']);
    $r->addRoute('GET',  '/cms/artists', ['App\Controllers\CMSArtistController', 'index']);
    $r->addRoute('GET',  '/cms/artists/create', ['App\Controllers\CMSArtistController', 'createForm']);
    $r->addRoute('POST', '/cms/artists/create', ['App\Controllers\CMSArtistController', 'create']);
    $r->addRoute('GET',  '/cms/artists/{id:\\d+}', ['App\Controllers\CMSArtistController', 'edit']);
    $r->addRoute('POST', '/cms/artists/{id:\\d+}', ['App\Controllers\CMSArtistController', 'update']);
    $r->addRoute('POST', '/cms/artists/{id:\\d+}/delete', ['App\Controllers\CMSArtistController', 'delete']);
    // Jazz CMS routes (admin only)
    $r->addRoute('GET',  '/cms/events/jazz', ['App\Controllers\CMSJazzController', 'index']);
    $r->addRoute('GET',  '/cms/events/jazz/create', ['App\Controllers\CMSJazzController', 'createForm']);
    $r->addRoute('POST', '/cms/events/jazz/create', ['App\Controllers\CMSJazzController', 'create']);
    $r->addRoute('GET',  '/cms/events/jazz/{id:\\d+}', ['App\Controllers\CMSJazzController', 'edit']);
    $r->addRoute('POST', '/cms/events/jazz/{id:\\d+}', ['App\Controllers\CMSJazzController', 'update']);
    $r->addRoute('POST', '/cms/events/jazz/{id:\\d+}/delete', ['App\Controllers\CMSJazzController', 'delete']);
});


/**
 * Get the request method and URI from the server variables and invoke the dispatcher.
 */
$httpMethod = $_SERVER['REQUEST_METHOD'];
$uri = strtok($_SERVER['REQUEST_URI'], '?');
$routeInfo = $dispatcher->dispatch($httpMethod, $uri);
Session::ensureStarted();

/**
 * Switch on the dispatcher result and call the appropriate controller method if found.
 */
/**
 * Switch on the dispatcher result and call the appropriate controller method if found.
 */
switch ($routeInfo[0]) {
    // Handle not found routes
    case FastRoute\Dispatcher::NOT_FOUND:
        http_response_code(404);
        echo 'Not Found';
        break;

    // Handle routes that were invoked with the wrong HTTP method
    case FastRoute\Dispatcher::METHOD_NOT_ALLOWED:
        http_response_code(405);
        echo 'Method Not Allowed';
        break;

    // Handle found routes
    case FastRoute\Dispatcher::FOUND:
        [$controllerClass, $method] = $routeInfo[1];
        $vars = $routeInfo[2] ?? [];

        if (!class_exists($controllerClass)) {
            http_response_code(500);
            echo "Controller not found: " . htmlspecialchars($controllerClass);
            break;
        }

        $controller = new $controllerClass();

        if (!method_exists($controller, $method)) {
            http_response_code(500);
            echo "Method not found: " . htmlspecialchars($controllerClass . '::' . $method);
            break;
        }

        // Pass dynamic route params (e.g. /hello/{name})
        call_user_func_array([$controller, $method], array_values($vars));
        break;
}
