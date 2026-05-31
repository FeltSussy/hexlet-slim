<?php

// Подключение автозагрузки через composer
require __DIR__ . '/../vendor/autoload.php';

use Slim\Factory\AppFactory;
use DI\Container;

session_start();

$container = new Container();
$container->set('renderer', function () {
    return new \Slim\Views\PhpRenderer(__DIR__ . '/../templates');
});
$app = AppFactory::createFromContainer($container);
$app->addErrorMiddleware(true, true, true);
$router = $app->getRouteCollector()->getRouteParser();

$app->get('/users/new', function ($request, $response) use ($router) {
    $usersPostUrl = $router->urlFor('users.store');
    $params = [
        'user' => ['nickname' => '', 'email' => ''],
        'errors' => [],
        'usersPostUrl' => $usersPostUrl
        
    ];
    return $this->get('renderer')->render($response, 'users/form.phtml', $params);
})->setName('users.new');

$users = ['pavel'];

$app->post('/users', function($request, $response) use ($router) {
    $redirectUrl = $router->urlFor('users.index');
    $usersPostUrl = $router->urlFor('users.store');
    $user = $request->getParsedBodyParam('user');
    $errors = [];
    if (count($errors) === 0) {
        $path = __DIR__ . "/../txt.json";

        $content = file_exists($path) ? file_get_contents($path) : '';
        $users = json_decode($content, true) ?? [];

        $ids = array_column($users, 'id');
        $user['id'] = empty($ids) ? 1 : max($ids) + 1;

        $users[] = $user;

        file_put_contents($path, json_encode($users));

        return $response->withRedirect($redirectUrl, 302);
    }

    $params = [
        'user' => $user,
        'errors' => $errors,
        'usersPostUrl' => $usersPostUrl
    ];
    return $this->get('renderer')->render($response, "users/form.phtml", $params);
})->setName('users.store');

$app->get('/users', function ($request, $response) {
    $content = file_get_contents(__DIR__ . '/../txt.json');
    $users = json_decode($content, true) ?? [];

    $params = [
        'users' => $users
    ];
    return $this->get('renderer')->render($response, 'users/users.phtml', $params);
})->setName('users.index');

$app->get('/users/{id}', function ($request, $response, $args) {
    $content = file_get_contents(__DIR__ . '/../txt.json');
    $users = json_decode($content, true) ?? [];

    $userId = (int) $args['id'];

    foreach ($users as $userChecked) {
        if ($userChecked['id'] === $userId) {
            $user = $userChecked;
        }
    }

    if (!isset($user)) {
        return $response->withStatus(404);
    }

    $params = [
        'user' => $user
    ];
    return $this->get('renderer')->render($response, 'users/userById.phtml', $params);
})->setName('user.id');

$app->run();