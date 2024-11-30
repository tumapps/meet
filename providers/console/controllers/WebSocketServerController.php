<?php

namespace cmd\controllers;;


use yii\console\Controller;
use yii\console\ExitCode;
use helpers\WebSocketHandler;
use Ratchet\Server\IoServer;
use React\EventLoop\Factory;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;
use Ratchet\App;
use Yii;

class WebSocketServerController extends Controller
{
    // public function actionStart()
    // {
    //     // Create the WebSocket server using the custom WebSocketHandler
    //     $server = IoServer::factory(
    //         new HttpServer(
    //             new WsServer(
    //                 new WebSocketHandler()
    //             )
    //         ),
    //         8080 
    //     );

    //     echo "WebSocket server started on port 8080\n";
    //     $server->run();
    // }

    public function actionStart()
    {
        $loop = Factory::create();

        $webSocketHandler = new WebSocketHandler($loop);  

        $server = new IoServer(
            new HttpServer(
                new WsServer($webSocketHandler)
            ),
            new \React\Socket\Server('127.0.0.1:8080', $loop),
            $loop
        );

        echo "WebSocket server started on port 8080\n";

        $loop->run();
    }
}
