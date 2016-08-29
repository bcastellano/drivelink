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

    // get file data from google drive
    $meta = $app['google.drive']->getDrive()->files->get($fileId);
    $contents = $app['google.drive']->getDrive()->files->get($fileId, ['alt' => 'media']);

    // read url from file
    $url = $app['link.reader']->getUrlFromFile($meta, $contents);

    if (null == $url) {
        $url = '/error';
    }

    return $app->redirect($url);
});

/**
 * Error page
 */
$app->get('/error', function () use ($app) {
    return '<p>Url not valid or not found</p>';
});
