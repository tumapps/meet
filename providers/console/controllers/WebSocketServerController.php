<?php 

namespace cmd\controllers;;

 
use yii\console\Controller;
use yii\console\ExitCode;
use helpers\WebSocketHandler;
use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;
use Ratchet\App;
use Yii;

class WebSocketServerController extends Controller 
{
    public function actionStart2()
    {
    	 
        $server = IoServer::factory(
        	new WebSocketServerController(),
            8080
        );

        // Start the server
        $server->run();
    }

    public function actionStart()
    {
        // Create the WebSocket server using the custom WebSocketHandler
        $server = IoServer::factory(
            new HttpServer(
                new WsServer(
                    new WebSocketHandler()
                )
            ),
            8080 
        );

        echo "WebSocket server started on port 8080\n";
        $server->run();
    }

}
