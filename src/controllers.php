<?php

namespace DriveLink;

/**
 * @var \Silex\Application $app
 */

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
