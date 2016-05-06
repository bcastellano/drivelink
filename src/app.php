<?php

namespace DriveLink;

use Silex\Application;
use Silex\Provider\SessionServiceProvider;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\HttpKernelInterface;

$config = include __DIR__.'/../config/config.php';
$app = new Application();

$app->register(new SessionServiceProvider());

/**
 *
 */
$app['google.client'] = $app->share(function() use ($app, $config) {
    return new GoogleClient($app['session'], $config);
});

$app['google.drive'] = $app->share(function() use ($app) {
    return new GoogleDrive($app['google.client']);
});

/**
 *
 */
$app->get('/install', function() use($app) {

    return $app['google.client']->init($app);
});

/**
 *
 */
$app->get('/auth2callback', function(Request $request) use($app) {
    $app['google.client']->authenticate($request->query->get('code'));

    if ($ids = $app['session']->get('open_ids')) {
        $url = '/open/'.$ids;
    } else {
        $url = '/';
    }

    return $app->redirect($url);
});

/**
 *
 */
$app->get('/', function() use($app) {

    return 'web page';

});

/**
 * Open url file
 */
$app->get('/open/{ids}', function($ids) use($app) {

    if ($app['google.client']->getClient()->isAccessTokenExpired()) {
        $app['session']->set('open_ids', $ids);

        return $app->handle(Request::create('/install', 'GET'), HttpKernelInterface::SUB_REQUEST);
    }

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
