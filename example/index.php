<?php
/**
 * https://dl2.tech - DL2 IT Services
 * Owlsome solutions. Owltstanding results.
 */

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/modules/Example/Index.php';

use Modules\Example\Index;

/*
 * Instantiate a Slim application by bootstraping our FronController.
 */
$app = DL2\Slim\Controller::bootstrap();

/*
 * Define the Slim application routes
 */
DL2\Slim\Controller::map(Index::class);

/*
 * Run the Slim application
 *
 * This method should be called last. This executes the Slim application
 * and returns the HTTP response to the HTTP client.
 */
$app->run();
