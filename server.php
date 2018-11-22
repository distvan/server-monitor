<?php
require __DIR__ . '/vendor/autoload.php';

use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;
use Noodlehaus\Config;
use Distvan\App;

$configname = dirname(__FILE__) . '/config.ini';
$config = Config::load($configname);
$app = new App($config);

$server = IoServer::factory(new HttpServer(new WsServer($app)), $app->getPort());
$app->setLoop($server->loop);
$server->run();