<?php

namespace DriveLink;

use DriveLink\Security\Authentication\DriveProvider;
use DriveLink\Security\Firewall\DriveListener;
use DriveLink\Security\User\DriveUserProvider;
use Silex\Application;
use Silex\Provider\SecurityServiceProvider;
use Silex\Provider\SessionServiceProvider;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\HttpKernelInterface;

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
    return new GoogleClient($app['session'], $app['parameters']['google-auth']);
});

/**
 * Service to manage google drive
 */
$app['google.drive'] = $app->share(function () use ($app) {
    return new GoogleDrive($app['google.client']);
});

/**
 * Callback url to execute after login
 */
$app->get('/auth/callback', function () use ($app) {
    // redirect to home or path used before login
    $url = $app['session']->get('_security.default.target_path', '/');

    return $app->redirect($url);
});

/**
 * Root page
 */
$app->get('/', function () use ($app) {
    if ($app['security.authorization_checker']->isGranted('IS_AUTHENTICATED_FULLY')) {
        return $app['security.token_storage']->getToken()->getUser()->getUsername().' <a href="/logout">logout</a>';
    } else {
        return '<a href="/auth">authenticate</a>';
    }
});

/**
 * Open url file
 */
$app->get('/open/{ids}', function ($ids) use ($app) {
    $fileId = explode(',', $ids)[0];
    echo 'open: ' . $fileId;

    $meta = $app['google.drive']->getDrive()->files->get($fileId);
    $contents = $app['google.drive']->getDrive()->files->get($fileId, ['alt' => 'media']);

    if ($meta['fileExtension'] == 'webloc') {
        // is .webloc
        $xml = simplexml_load_string($contents);

        $url = (string)$xml->dict->string;
    } else {
        $matches = [];
        // is .url
        preg_match('/URL=([^\n]*)/mi', $contents, $matches);

        $url = $matches[1];
    }

    return $app->redirect($url);

});

return $app;
