<?php

namespace DriveLink;

use DriveLink\Google\Client;
use DriveLink\Google\Service\Drive;
use DriveLink\Link\LinkReader;
use DriveLink\Security\Authentication\DriveProvider;
use DriveLink\Security\Firewall\DriveListener;
use DriveLink\Security\User\DriveUserProvider;
use Silex\Application;
use Silex\Provider\SecurityServiceProvider;
use Silex\Provider\SessionServiceProvider;

/**
 * Start app and load config file
 */
$app = new Application(include __DIR__.'/../config/config.php');

/**
 * Register session service
 */
$app->register(new SessionServiceProvider());

/**
 * Security service
 */
$app->register(new SecurityServiceProvider());
$app['security.firewalls'] = [
    'default'   => [
        'pattern'   => '^/',
        'anonymous' => true,
        'drive'     => [
            'login_path'    => '/auth',
            'callback_path' => '/auth/callback',
            'failure_path'  => '/'
        ], // params for factory
        'logout'    => ['logout_path' => '/logout'],
        'users'     => $app->share(function () use ($app) {
            return new DriveUserProvider();
        }),
    ]
];
$app['security.access_rules'] = [
    ["^/$", ['IS_AUTHENTICATED_ANONYMOUSLY']],
    ["^/",  ['ROLE_USER']]
];
$app['security.authentication_listener.factory.drive'] = $app->protect(function ($name, $options) use ($app) {
    // define the authentication provider
    $app['security.authentication_provider.'.$name.'.drive'] = $app->share(function ($app) use ($name) {
        return new DriveProvider($app['google.client'], $app['security.user_provider.default']);
    });

    // define the authentication listener
    $app['security.authentication_listener.'.$name.'.drive'] = $app->share(function ($app) use ($name, $options) {

        // add fake route for auth path
        $app->get($options['login_path'])->run(null)->bind('login_path');

        return new DriveListener($app['security.token_storage'], $app['security.authentication_manager'], $app['google.client'], $app['security.http_utils'], $options);
    });

    // define the entry point that starts the authentication
    $app['security.entry_point.'.$name.'.drive'] = $app['security.entry_point.form._proto']($name, $options);

    return array(
        // the authentication provider id
        'security.authentication_provider.'.$name.'.drive',
        // the authentication listener id
        'security.authentication_listener.'.$name.'.drive',
        // the entry point id
        'security.entry_point.'.$name.'.drive',
        // the position of the listener in the stack
        'pre_auth'
    );
});

/**
 * Service to connect google services
 */
$app['google.client'] = $app->share(function () use ($app) {
    return new Client($app['session'], $app['parameters']['google-auth']);
});

/**
 * Service to manage google drive
 */
$app['google.drive'] = $app->share(function () use ($app) {
    return new Drive($app['google.client']);
});

/**
 * Service to read files with url data
 */
$app['link.reader'] = $app->share(function () use ($app) {
    return new LinkReader();
});

return $app;
