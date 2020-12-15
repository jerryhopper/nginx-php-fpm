<?php

declare(strict_types=1);

use App\Preferences;

use Monolog\Handler\RotatingFileHandler;
use Monolog\Logger;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Log\LoggerInterface;
use Slim\App;
use Slim\Factory\AppFactory;
use Slim\Views\Twig;

#use League\OAuth2\Client\Provider\GenericProvider;
use JerryHopper\OAuth2\Client\Provider\FusionAuth;
use JerryHopper\ServiceDiscovery\Discovery;

use Illuminate\Database\Capsule\Manager as Capsule;


return [
/*
    ResponseFactoryInterface::class => function (ContainerInterface $container) {
        return $container->get(App::class)->getResponseFactory();
    },

    App::class => function (ContainerInterface $container) {
        AppFactory::setContainer($container);

        return AppFactory::create();
    },
*/
    ResponseFactoryInterface::class => function (ContainerInterface $container) {
        return $container->get(App::class)->getResponseFactory();
    },
    App::class => function (ContainerInterface $container) {
        AppFactory::setContainer($container);

        return AppFactory::create();
    },
    FusionAuth::class => function (ContainerInterface $container): FusionAuth {
        // Get the preferences from the container.
        $preferences = $container->get(Preferences::class);

        $discoveryData = new Discovery( $preferences->getOauthDiscoveryurl() );

        #error_log(json_encode($discoveryData->get()));
        #error_log($preferences->getOauthClientid());
        #error_log($preferences->getOauthClientsecret());
        #error_log($preferences->getOauthRedirectUrl());
        #error_log($discoveryData->get("authorization_endpoint"));
        #error_log($discoveryData->get("token_endpoint"));
        #error_log($discoveryData->get("userinfo_endpoint"));


        $provider = new FusionAuth([
            'clientId'                => $preferences->getOauthClientid(),    // The client ID assigned to you by the provider
            'clientSecret'            => $preferences->getOauthClientsecret(),    // The client password assigned to you by the provider
            'redirectUri'             => $preferences->getOauthRedirectUrl(),
            'urlAuthorize'            => $discoveryData->get("authorization_endpoint"),
            'urlAccessToken'          => $discoveryData->get("token_endpoint"),
            'urlResourceOwnerDetails' => $discoveryData->get("userinfo_endpoint")
        ]);

        return $provider;
    },

    Capsule::class => function (ContainerInterface $container): Capsule {
        $capsule = new Capsule;

        // Get the preferences from the container.
        $preferences = $container->get(Preferences::class);

        $capsule->addConnection([
            'driver'    => 'mysql',
            'host'      => $preferences->getDbHost(),
            'database'  => $preferences->getDbName(),
            'username'  => $preferences->getDbUser(),
            'password'  => $preferences->getDbPass(),
            'charset'   => 'utf8',
            'collation' => 'utf8_unicode_ci',
            'prefix'    => '',
        ]);

        // Set the event dispatcher used by Eloquent models... (optional)
        //use Illuminate\Events\Dispatcher;
        //use Illuminate\Container\Container;
        //$capsule->setEventDispatcher(new Dispatcher(new Container));

        // Make this Capsule instance available globally via static methods... (optional)
        $capsule->setAsGlobal();

        // Setup the Eloquent ORM... (optional; unless you've used setEventDispatcher())
        //$capsule->bootEloquent();

        return $capsule;
    },

    Session::class => function (ContainerInterface $container) {
        // https://odan.github.io/2020/08/09/slim4-http-session.html
        $preferences = $container->get(Preferences::class);

        $settings = $preferences->getSessionSettings();

        if (PHP_SAPI === 'cli') {
            return new Session(new MockArraySessionStorage());
        } else {
            return new Session(new NativeSessionStorage($settings));
        }
    },


    SessionInterface::class => function (ContainerInterface $container) {
        return $container->get(Session::class);
    },


    LoggerInterface::class => function (ContainerInterface $container): LoggerInterface {
        // Get the preferences from the container.
        $preferences = $container->get(Preferences::class);

        // Instantiate a new logger and push a handler into the logger.
        $logger = new Logger('slim4-skeleton');
        $logger->pushHandler(
            new RotatingFileHandler(
                $preferences->getRootPath() . '/logs/slim4-skeleton.log'
            )
        );

        return $logger;
    },


    Twig::class => function (ContainerInterface $container): Twig {
        // Get the preferences from the container.
        $preferences = $container->get(Preferences::class);

        // Instantiate twig.
        $twig = Twig::create(
            $preferences->getRootPath() . '/application/templates',
            [
                'cache' => $preferences->getRootPath() . '/cache',
                'auto_reload' => true,
                'debug' => true,
            ]
        );
        $twig->addExtension(new \Twig\Extension\DebugExtension());
        return $twig;
    },
];
