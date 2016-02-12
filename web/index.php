<?php

error_reporting(1);
ini_set('display_errors', 1);

require_once __DIR__ . '/../vendor/autoload.php';

$app = new Silex\Application();

//default definitions
$app->get('/', function () use ($blogPosts) {
            return '<h3>Welcome to Eztrackit OCR Application </h3>';
        });

//load the "callback" controller
$app->mount('/process', include __DIR__ . '/../src/controllers/ocr.php');

$app['debug'] = true;

$app->run();