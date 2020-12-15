<?php

declare(strict_types=1);

use App\ContainerFactory;
use App\Controllers\Api\DeviceSetupController;
use App\Controllers\Api\LocalDnsController;
use App\Controllers\Api\LocalSslController;
use App\Controllers\Api\StatusController;
use App\Controllers\Api\UnregisteredDeviceController;
use App\Controllers\ExceptionDemoController;
use App\Controllers\HelloController;
use App\Controllers\HomeController;
use App\Controllers\LoginController;
use App\Controllers\DashboardController;
use App\Controllers\MyBoxesController;
use App\Controllers\MyAppsController;
use App\Controllers\OauthCallbackController;


#use App\Action\LogoutAction;

use Slim\Factory\AppFactory;
use Slim\Routing\RouteCollectorProxy;
use Slim\Views\Twig;
use Slim\Views\TwigMiddleware;

use App\Middleware\HttpsMiddleware;
use App\Middleware\SessionMiddleware;
use App\Middleware\UserAuthMiddleware;
use App\Middleware\OauthMiddleware;
#use Slim\Routing\RouteCollectorProxy;

#error_log($_SERVER['OAUTH_DISCOVERY']);
// Set the default timezone.
date_default_timezone_set('Europe/Amsterdam');

// Set the absolute path to the root directory.
$rootPath = realpath(__DIR__ . '/..');

// Include the composer autoloader.
include_once($rootPath . '/vendor/autoload.php');



// Crazy workaround for some unknown autoloader issue.
require_once($rootPath . '/vendor/cloudflare/sdk/src/Auth/APIToken.php');
require_once($rootPath . '/vendor/cloudflare/sdk/src/Endpoints/Zones.php');


// Create the container for dependency injection.
try {
    $container = ContainerFactory::create($rootPath);
} catch (Exception $e) {
    die($e->getMessage());
}

// Set the container to create the App with AppFactory.
AppFactory::setContainer($container);

$app = AppFactory::create();

// Set the cache file for the routes. Note that you have to delete this file
// whenever you change the routes.
$app->getRouteCollector()->setCacheFile(
    $rootPath . '/cache/routes.cache'
);


// Start the session
$app->add(SessionMiddleware::class); // <-- here

//
$headersToInspect = [
    'X-Real-IP',
    'X-Forwarded-For',
    'Forwarded',
];
$checkProxyHeaders = true; // Note: Never trust the IP address for security processes!
$trustedProxies = ['10.0.0.1', '172.24.0.1']; // Note: Never trust the IP address for security processes!
$app->add(new RKA\Middleware\IpAddress($checkProxyHeaders, $trustedProxies,'ip_address', $headersToInspect ));

// Add the routing middleware.
$app->addRoutingMiddleware();

$app->add(OauthMiddleware::class); // <-- here

// Add the twig middleware.
$app->addMiddleware(
    TwigMiddleware::create($app, $container->get(Twig::class))

);

// Add error handling middleware.
$displayErrorDetails = true;
$logErrors = true;
$logErrorDetails = false;
$app->addErrorMiddleware($displayErrorDetails, $logErrors, $logErrorDetails);

// Define the app routes.
$app->group('/', function (RouteCollectorProxy $group) {

    //$group->get('login', \App\Action\LoginAction::class)->setName('login');
    $group->get( 'login',  LoginController::class)->setName('login');
    $group->post('login',  \App\Action\LoginSubmitAction::class)->setName('login-submit');
    $group->get( 'logout', \App\Action\LogoutAction::class)->setName('logout');




    $group->get('', DashboardController::class)->setName('home')->add(UserAuthMiddleware::class);

    #$group->get('', HomeController::class)->setName('home');

    #$group->get('users-get', HomeController::class)->setName('users-get');
    #$group->get('hello/{name}', HelloController::class)->setName('hello');
    #$group->get('exception-demo', ExceptionDemoController::class)->setName('exception-demo');
})->add(HttpsMiddleware::class);


$app->group('/api/', function (RouteCollectorProxy $group) {

    $group->post('unregistereddevice', UnregisteredDeviceController::class)->setName('api-devicesetup');

    $group->get('devicesetup', DeviceSetupController::class)->setName('api-devicesetup');

    $group->get('status', StatusController::class)->setName('api-status');

    # Creation of dns-name for local ip
    $group->get('localdns', LocalDnsController::class)->setName('api-localdns');

    # Starts download of certificate.
    $group->get('localssl', LocalSslController::class)->setName('api-localssl');
})->add(HttpsMiddleware::class);






$app->group('/dashboard/', function (RouteCollectorProxy $group) {
    // ...
    $group->get('myapps', MyAppsController::class)->setName('myapps');
    $group->get('myboxes', MyBoxesController::class)->setName('myboxes');
    $group->get('', DashboardController::class)->setName('dashboard');


})->add(UserAuthMiddleware::class);
// ->add(HttpsMiddleware::class)






$app->group('/users/', function (RouteCollectorProxy $group) {
    // ...
    $group->get('get', LoggedinController::class)->setName('users-get');

})->add(UserAuthMiddleware::class);






$app->group('/oauth2/', function (RouteCollectorProxy $group) {

    //$group->get('login', \App\Action\OauthLoginAction::class)->setName('oauth-login');

    $group->get('callback', \App\Action\OauthCallbackAction::class)->setName('oauth2-callback');

    //$group->get('', HomeController::class)->setName('home');
    //$group->get('callback', OauthCallbackController::class)->setName('oauth-callback');
});



// Run the app.
$app->run();

