<?php

namespace helpers;

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
use yii\console\ExitCode;
use Ratchet\Client\Connector;
use React\EventLoop\Factory;
use scheduler\models\Appointments;
use React\EventLoop\Timer\Timer;

class WebSocketHandler implements MessageComponentInterface
{
	protected $clients;

    public function __construct()
    {
        $this->clients = new \SplObjectStorage;
    }

    public function onOpen(ConnectionInterface $conn)
    {
        $this->clients->attach($conn);
        echo("New connection!: {$conn->resourceId}\n");

    }

    public function onMessage(ConnectionInterface $from, $msg)
    {
        echo "Received message: {$msg}\n";
        echo "Number of Clients: " . count($this->clients) . "\n";
        foreach ($this->clients as $client) {
            if ($from !== $client) {
                $client->send($msg);  // Broadcast message to all other clients
            }
        }
    }

    public function onClose(ConnectionInterface $conn)
    {
        $this->clients->detach($conn);
        echo("Connection {$conn->resourceId} has disconnected\n");
    }

    public function onError(ConnectionInterface $conn, \Exception $e)
    {
        echo("An error has occurred: {$e->getMessage()}\n");
        $conn->close();
    }

     

   	public function broadcast($message)
    {
        if (!self::isWebSocketServerRunning()) {
            self::startWebSocketServer();
            sleep(1); 
        }
        foreach ($this->clients as $client) {
            echo $message;

            \Yii::info('Broadcasting message:'.$message . 'WebSocket');
            $client->send($message);
        }
    }

    // Method to check if WebSocket server is running
    protected static function isWebSocketServerRunning()
    {
        $host = '127.0.0.1';
        $port = 8080;

        $connection = @fsockopen($host, $port);

        if (is_resource($connection)) {
            fclose($connection);
            return true;
        }

        return false;
    }

    // Method to start the WebSocket server
    protected static function startWebSocketServer()
    {
        echo("Starting WebSocket server...\n");
        $command = "php yii ws/start > /dev/null 2>&1 &";
        exec($command);
    }

    public function broadcastAppointmentUpdate($data = 'hello msg')
    {
        $dataToSend = json_encode($data);

        // Create event loop
        $loop = Factory::create();

        // Create WebSocket connector
        $connector = new Connector($loop);

        $connector('ws://localhost:8080')
            ->then(function($conn) use ($dataToSend, $loop) {
                // Send the initial data
                $conn->send($dataToSend);

                // Set up a periodic timer (5-second interval)
                $loop->addPeriodicTimer(5, function() use ($conn) {
                    // Ping to keep the connection alive, or send updated data
                    $conn->send("ping");

                    // Optionally, send updated appointment data
                    // $updatedData = json_encode($newData);
                    // $conn->send($updatedData);
                });

                // Make sure we don't close the connection too early
                $conn->on('close', function() {
                    echo "Connection closed by the server\n";
                });
            }, function($e) {
                echo "Could not connect to WebSocket: {$e->getMessage()}\n";
            });

        // Run the event loop, this keeps the timers running
        $loop->run();
    }
}