<?php
require __DIR__ . '/vendor/autoload.php';

use Backend\Endpoints\APIEndpoint;
use Backend\Endpoints\AuthEndpoint;
use Bramus\Router\Router;

define('HTTP_OK', 200);
define('HTTP_CREATED', 201);
define('HTTP_NO_CONTENT', 204);
define('HTTP_MOVED_PERMANENTLY', 301);
define('HTTP_FOUND', 302);
define('HTTP_BAD_REQUEST', 400);
define('HTTP_UNAUTHORIZED', 401);
define('HTTP_FORBIDDEN', 403);
define('HTTP_NOT_FOUND', 404);
define('HTTP_INTERNAL_SERVER_ERROR', 500);
define('HTTP_SERVICE_UNAVAILABLE', 503);

$router = new Router();

$router->options('/(api|auth)/.*', function() {
  header('Access-Control-Allow-Origin: http://localhost:5173, https://todophp-git-main-rom-vales-projects.vercel.app');
  header('Access-Control-Allow-Credentials: true');
  header('Access-Control-Allow-Methods: GET,HEAD,PUT,POST,DELETE');
  header('Access-Control-Allow-Headers: Content-Type, Accept');
});

$router->before('GET|POST|PUT|DELETE', '/(api|auth).*', function() {
  header('Access-Control-Allow-Origin: http://localhost:5173, https://todophp-git-main-rom-vales-projects.vercel.app');
  header('Access-Control-Allow-Credentials: true');
  header('Access-Control-Allow-Methods: GET,HEAD,PUT,POST,DELETE');
  header('Access-Control-Allow-Headers: Content-Type, Accept');
});

new AuthEndpoint($router);
new APIEndpoint($router);

$router->run();
