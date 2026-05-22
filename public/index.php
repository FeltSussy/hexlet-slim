<?php

// Подключение автозагрузки через composer
require __DIR__ . '/../vendor/autoload.php';

use Slim\Factory\AppFactory;

$app = AppFactory::create();
$app->addErrorMiddleware(true, true, true);

$companies = [
    [
        "id" => 1,
        "name" => "Hexlet1",
        "phone" => "(458) 592-8259"
    ],
    [
        "id" => 2,
        "name" => "Hexlet2",
        "phone" => "(458) 592-8259"
    ],
    [
        "id" => 3,
        "name" => "Hexlet3",
        "phone" => "(458) 592-8259"
    ]
];

$app->get('/companies/{id}', function ($request, $response, array $args) use ($companies) {
    $id = $args['id'];
    $collection = collect($companies);
    $companie = $collection->firstWhere('id', $id);

    if ($companie !== null) {
        return $response->write(json_encode($companie));
    }
    return $response->withStatus(404)->write('Page not found');
});

$app->run();