<?php

declare(strict_types=1);

require_once './vendor/autoload.php';

use App\CryptoKeyPair;
use App\Util;
use Swoole\WebSocket\Server;
use Swoole\Http\Request;
use Swoole\WebSocket\Frame;

$server = new Server('127.0.0.1', 9502);
$crypto = new CryptoKeyPair();
$crypto->generateKeys(0);

$server->on('start', fn() => printf("Swoole WebSocket Server is started at http://127.0.0.1:9502\n"));

$server->on('open', function (Server $server, Request $request) use ($crypto) {
    echo "connection open: {$request->fd}\n";
    usleep(100);
    $server->push($request->fd, json_encode([
        'event' => 'connect',
        'data' => [
            'publicKey' => Util::stringToBuffer($crypto->getKeys(0)[0]),
            'nonce' => Util::stringToBuffer($crypto->getNonce())
        ]
    ]));
});

$server->on('message', function (Server $server, Frame $frame) use ($crypto) {
    echo "received message: {$frame->fd}\n";
    ['event' => $event, 'data' => $data] = json_decode($frame->data, true);
    // $server->push($frame->fd, json_encode(["hello", time()]));

    if ($event === 'connect') {
        $crypto->registerKeys($frame->fd, Util::bufferToStrig($data['publicKey']));
    }

    if ($event === 'message') {
        $message = Util::bufferToStrig(str_replace("\n", "", $data));
        echo $crypto->decrypt($message, 0, $frame->fd) . PHP_EOL;
    }
});

$server->on('close', function (Server $server, int $fd) {
    echo "connection close: {$fd}\n";
});

$server->start();
