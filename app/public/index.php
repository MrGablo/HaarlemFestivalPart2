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
 * Serve static assets for /dance when all requests hit this front controller (e.g. Aiven).
 * GET /dance/assets/... -> serve from app/public/assets/... (CSS, images, etc.)
 */
$uri = (string) ($_SERVER['REQUEST_URI'] ?? '/');
$uri = strtok($uri, '?');
if ($_SERVER['REQUEST_METHOD'] === 'GET' && strpos($uri, '/dance/assets/') === 0) {
    $assetPath = substr($uri, strlen('/dance/assets/'));
    $assetPath = str_replace(['..', "\0"], '', $assetPath);
    $file = __DIR__ . '/assets/' . $assetPath;
    if (is_file($file)) {
        $fileReal = realpath($file);
        $assetsDirReal = realpath(__DIR__ . '/assets');

        if ($fileReal !== false && $assetsDirReal !== false && str_starts_with($fileReal, $assetsDirReal)) {
            $mimes = [
                'png' => 'image/png', 'jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg', 'gif' => 'image/gif',
                'webp' => 'image/webp', 'svg' => 'image/svg+xml', 'ico' => 'image/x-icon',
                'css' => 'text/css', 'js' => 'application/javascript', 'woff2' => 'font/woff2', 'woff' => 'font/woff',
            ];
            $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
            if (isset($mimes[$ext])) {
                header('Content-Type: ' . $mimes[$ext]);
            }
            header('Content-Length: ' . filesize($file));
            readfile($file);
            return;
        }
    }
}

/**
 * Define the routes for the application.
 */
$dispatcher = simpleDispatcher(function (RouteCollector $r) {
    $r->addRoute('GET', '/', ['App\Controllers\HomeController', 'home']);

    // Dance Festival routes
    $r->addRoute('GET', '/dance', ['App\Controllers\DanceController', 'home']);

    //Jazz Festival routes
    $r->addRoute('GET', '/jazz', ['App\Controllers\JazzController', 'home']);
    $r->addRoute('GET', '/jazz/artist', ['App\Controllers\JazzController', 'artist']);

    // Personal Program (My Program) page
    $r->addRoute('GET', '/program', ['App\Controllers\ProgramController', 'show']);
    

    //user authorization
    $r->addRoute('GET',  '/login',    ['App\Controllers\AuthController', 'showLogin']);
    $r->addRoute('POST', '/login',    ['App\Controllers\AuthController', 'login']);
    $r->addRoute('GET',  '/logout',   ['App\Controllers\AuthController', 'logout']);
    $r->addRoute('GET',  '/forgot-password', ['App\Controllers\AuthController', 'showForgotPassword']);
    $r->addRoute('POST', '/forgot-password', ['App\Controllers\AuthController', 'requestPasswordReset']);
    $r->addRoute('GET',  '/reset-password', ['App\Controllers\AuthController', 'showResetPassword']);
    $r->addRoute('POST', '/reset-password', ['App\Controllers\AuthController', 'resetPassword']);
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

    // Payment routes (Stripe checkout)
    $r->addRoute('POST', '/payment/checkout', ['App\Controllers\PaymentController', 'checkoutRedirect']);
    $r->addRoute('POST', '/api/payment/webhook', ['App\Controllers\PaymentController', 'handleWebhook']);
    $r->addRoute('GET',  '/payment/success', ['App\Controllers\PaymentController', 'success']);
    $r->addRoute('GET',  '/payment/cancel', ['App\Controllers\PaymentController', 'cancel']);

    // Scanner routes (admin and employee access)
    $r->addRoute('GET', '/scanner', ['App\Controllers\ScannerController', 'index']);
    $r->addRoute('POST', '/scanner/process', ['App\Controllers\ScannerController', 'processScan']);

    // CMS routes (admin only)
    $r->addRoute('GET', '/cms', ['App\Controllers\CMSController', 'generalIndex']);
    $r->addRoute('GET', '/cms/pages', ['App\Controllers\CMSController', 'index']);
    $r->addRoute('GET', '/cms/page/{id:\\d+}', ['App\Controllers\CMSController', 'edit']);
    $r->addRoute('POST', '/cms/page/{id:\\d+}/update', ['App\Controllers\CMSController', 'update']);
    $r->addRoute('GET',  '/cms/events', ['App\Controllers\CMSEventController', 'index']);
    $r->addRoute('GET',  '/cms/events/{id:\\d+}', ['App\Controllers\CMSEventController', 'edit']);
    $r->addRoute('POST', '/cms/events/{id:\\d+}', ['App\Controllers\CMSEventController', 'update']);
    $r->addRoute('GET',  '/cms/artists', ['App\Controllers\CMSArtistController', 'index']);
    $r->addRoute('GET',  '/cms/artists/create', ['App\Controllers\CMSArtistController', 'createForm']);
    $r->addRoute('POST', '/cms/artists/create', ['App\Controllers\CMSArtistController', 'create']);
    $r->addRoute('GET',  '/cms/artists/{id:\\d+}', ['App\Controllers\CMSArtistController', 'edit']);
    $r->addRoute('POST', '/cms/artists/{id:\\d+}', ['App\Controllers\CMSArtistController', 'update']);
    $r->addRoute('POST', '/cms/artists/{id:\\d+}/delete', ['App\Controllers\CMSArtistController', 'delete']);
    // CMS User Control routes (admin only)
    $r->addRoute('GET',  '/cms/users', ['App\Controllers\CMSUserController', 'index']);
    $r->addRoute('GET',  '/cms/users/create', ['App\Controllers\CMSUserController', 'createForm']);
    $r->addRoute('POST', '/cms/users/create', ['App\Controllers\CMSUserController', 'create']);
    $r->addRoute('GET',  '/cms/users/{id:\\d+}', ['App\Controllers\CMSUserController', 'edit']);
    $r->addRoute('POST', '/cms/users/{id:\\d+}', ['App\Controllers\CMSUserController', 'update']);
    $r->addRoute('POST', '/cms/users/{id:\\d+}/delete', ['App\Controllers\CMSUserController', 'delete']);

    // CMS Order routes (admin only)
    $r->addRoute('GET',  '/cms/orders', ['App\Controllers\CMSOrderController', 'index']);
    $r->addRoute('GET',  '/cms/orders/export', ['App\Controllers\CMSOrderController', 'export']);
    $r->addRoute('GET',  '/cms/orders/{id:\\d+}/export', ['App\Controllers\CMSOrderController', 'exportOptions']);
    $r->addRoute('GET',  '/cms/orders/{id:\\d+}', ['App\Controllers\CMSOrderController', 'edit']);
    $r->addRoute('POST', '/cms/orders/{id:\\d+}', ['App\Controllers\CMSOrderController', 'update']);

    // Jazz CMS routes (admin only)
    $r->addRoute('GET',  '/cms/events/jazz', ['App\Controllers\CMSJazzController', 'index']);
    $r->addRoute('GET',  '/cms/events/jazz/create', ['App\Controllers\CMSJazzController', 'createForm']);
    $r->addRoute('POST', '/cms/events/jazz/create', ['App\Controllers\CMSJazzController', 'create']);
    $r->addRoute('GET',  '/cms/events/jazz/{id:\\d+}', ['App\Controllers\CMSJazzController', 'edit']);
    $r->addRoute('POST', '/cms/events/jazz/{id:\\d+}', ['App\Controllers\CMSJazzController', 'update']);
    $r->addRoute('POST', '/cms/events/jazz/{id:\\d+}/delete', ['App\Controllers\CMSJazzController', 'delete']);

    // Venue CMS routes (admin only)
    $r->addRoute('GET',  '/cms/venues', ['App\Controllers\CMSVenueController', 'index']);
    $r->addRoute('GET',  '/cms/venues/create', ['App\Controllers\CMSVenueController', 'createForm']);
    $r->addRoute('POST', '/cms/venues/create', ['App\Controllers\CMSVenueController', 'create']);
    $r->addRoute('GET',  '/cms/venues/{id:\\d+}', ['App\Controllers\CMSVenueController', 'edit']);
    $r->addRoute('POST', '/cms/venues/{id:\\d+}', ['App\Controllers\CMSVenueController', 'update']);
    $r->addRoute('POST', '/cms/venues/{id:\\d+}/delete', ['App\Controllers\CMSVenueController', 'delete']);
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

        // Pass dynamic route params from FastRoute
        call_user_func_array([$controller, $method], array_values($vars));
        break;
}
