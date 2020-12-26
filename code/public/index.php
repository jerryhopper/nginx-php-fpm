<?php

declare(strict_types=1);

use App\ContainerFactory;
use App\Controllers\Api\DeviceSetupController;
use App\Controllers\Api\LocalDnsController;
use App\Controllers\Api\LocalSslController;
use App\Controllers\Api\RegisteredDeviceController;
use App\Controllers\Api\StatusController;
use App\Controllers\Api\UnregisteredDeviceController;
use App\Controllers\ExceptionDemoController;
use App\Controllers\HelloController;
use App\Controllers\HomeController;
use App\Controllers\LoginController;
use App\Controllers\DashboardController;
use App\Controllers\MyBoxesController;
use App\Controllers\MyAppsController;
use App\Controllers\MyDeviceController;
use App\Controllers\OauthCallbackController;


#use App\Action\LogoutAction;

use App\Controllers\TestController;
use App\Middleware\AuthorizationMiddleware;
use App\Middleware\HttpExceptionMiddleware;
use App\Middleware\TokenHeaderMiddleware;
use App\Preferences;
use Slim\Factory\AppFactory;
use Slim\Routing\RouteCollectorProxy;
use Slim\Views\Twig;
use Slim\Views\TwigMiddleware;

use App\Middleware\HttpsMiddleware;
use App\Middleware\SessionMiddleware;
use App\Middleware\UserAuthMiddleware;
use App\Middleware\OauthMiddleware;
use Symfony\Component\HttpFoundation\Session\Session;

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

$app->add(HttpExceptionMiddleware::class); // <--- here



//
$headersToInspect = ['X-Forwarded-For','X-Real-IP','HTTP_X_FORWARDED_FOR','Forwarded',];
$checkProxyHeaders = true; // Note: Never trust the IP address for security processes!
$trustedProxies = $app->getContainer()->get(Preferences::class)->getTrustedProxies(); // Note: Never trust the IP address for security processes!
// Ip Middleware
$app->add(new RKA\Middleware\IpAddress($checkProxyHeaders, $trustedProxies,'ip_address', $headersToInspect ));



// Add the routing middleware.
$app->addRoutingMiddleware();

//$app->add(OauthMiddleware::class); // <-- here
//$app->add(AuthorizationMiddleware::class); // <-- here


// Add the twig middleware.
$app->addMiddleware(
    TwigMiddleware::create($app, $container->get(Twig::class))
);

// Add error handling middleware.
$displayErrorDetails = true;
$logErrors = true;
$logErrorDetails = false;

$app->addErrorMiddleware($displayErrorDetails, $logErrors, $logErrorDetails);


$app->group('/', function (RouteCollectorProxy $group) {
    # home
    $group->get('', HomeController::class)->setName('home')->setArgument("allow","all");

    # login
    $group->get( 'login',  LoginController::class)->setName('login')->setArgument("allow","all");
    # logout
    $group->get( 'logout', \App\Action\LogoutAction::class)->setName('logout');


})->add(AuthorizationMiddleware::class);

$app->group('/dashboard/', function (RouteCollectorProxy $group) {
    // ...
    $group->get('device/{id}', MyDeviceController::class)->setName('mydevice')->setArgument("allow","session");

    $group->get('myapps', MyAppsController::class)->setName('myapps')->setArgument("allow","session");;

    $group->get('myboxes', MyBoxesController::class)->setName('myboxes')->setArgument("allow","session");;
    $group->get('', DashboardController::class)->setName('dashboard')->setArgument("allow","session");


})->add(AuthorizationMiddleware::class)->add(HttpsMiddleware::class);






$app->group('/tests', function (RouteCollectorProxy $group) {

    $group->get('', TestController::class)->setName('home')->setArgument("allow","all");
    //->setArgument('permission', 'canReadWidgets');
    //->add( AuthorizationMiddleware::class,AuthorizationMiddleware::allow('all'));
    $group->get('token',   TestController::class)->setName('home-token')->setArgument("allow","token");
    $group->get('session', TestController::class)->setName('home-session')->setArgument("allow","session");
    $group->get('both', TestController::class)->setName('home-both')->setArgument("allow","both");
    //$group->get('exception-demo', ExceptionDemoController::class)->setName('exception-demo');



})->add(AuthorizationMiddleware::class);



$app->group('/oauth2/', function (RouteCollectorProxy $group) {
    $group->get('callback', TestController::class )->setName('oauth2-callback')->setArgument("allow","all");

})->add(AuthorizationMiddleware::class);



//->add(AuthorizationMiddleware::class,);
/*

// Define the app routes.
$app->group('/', function (RouteCollectorProxy $group) {

    # Login
    #$group->get( 'login',  LoginController::class)->setName('login');
    #$group->post('login',  \App\Action\LoginSubmitAction::class)->setName('login-submit');

    # Logout
    #$group->get( 'logout', \App\Action\LogoutAction::class)->setName('logout');


    # Homwpage - landingpage.
    $group->get('', DashboardController::class)->setName('home');




    #$group->get('', HomeController::class)->setName('home');
    #$group->get('users-get', HomeController::class)->setName('users-get');
    #$group->get('hello/{name}', HelloController::class)->setName('hello');
    #$group->get('exception-demo', ExceptionDemoController::class)->setName('exception-demo');

});//->add(HttpsMiddleware::class);//->add(OauthMiddleware::class);


*/

$app->group('/api/', function (RouteCollectorProxy $group) {

    # Post to update local osboxip, and updates 'running' status online
    $group->post('registereddevice', RegisteredDeviceController::class)->setName('api-registereddevice')->setArgument("allow","token");
    # Get list of local osbox-ip based on owner id
    $group->get('registereddevice', RegisteredDeviceController::class)->setName('api-registereddevice')->setArgument("allow","token");

    # Post local osbox-ip if unregistred.
    $group->post('unregistereddevice', UnregisteredDeviceController::class)->setName('api-unregistereddevice')->setArgument("allow","all");
    # Get list of local osbox-ip based on external ip
    $group->get('unregistereddevice', UnregisteredDeviceController::class)->setName('api-unregistereddevice')->setArgument("allow","all");



    # Creation of dns-name for local ip
    $group->get('localdns', LocalDnsController::class)->setName('api-localdns')->setArgument("allow","all");

    # Starts download of certificate.
    $group->get('localssl', LocalSslController::class)->setName('api-localssl')->setArgument("allow","all");




    $group->get('devicesetup', DeviceSetupController::class)->setName('api-devicesetup')->setArgument("allow","all");





    $group->get('status', StatusController::class)->setName('api-status');//->setArgument("allow","all");



})->add(AuthorizationMiddleware::class)->add(HttpsMiddleware::class);





// Run the app.
$app->run();

