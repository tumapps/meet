<?php

namespace helpers;

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
use yii\console\ExitCode;
use Ratchet\Client\Connector;
use React\EventLoop\Factory;
use scheduler\models\Appointments;
use React\EventLoop\Timer\Timer;
use React\EventLoop\LoopInterface;

class WebSocketHandler implements MessageComponentInterface
{
    protected $clients;
    protected $loop;

    // public function __construct()
    // {
    //     $this->clients = new \SplObjectStorage;
    //     // $this->startAppointmentBroadcastTimer();
    // }

    public function __construct(LoopInterface $loop)
    {
        $this->clients = new \SplObjectStorage();
        $this->loop = $loop;

        $this->loop->addPeriodicTimer(10, function () {
            echo "Fetching upcoming appointments at " . date('Y-m-d H:i:s') . "\n";

            $data = $this->fetchUpcomingAppointments();
            echo "Fetched appointments: " . json_encode($data) . "\n";

            $this->broadcast([
                'event' => 'upcoming_appointments',
                'data' => $data,
            ]);
        });
    }

    public function onOpen(ConnectionInterface $conn)
    {
        $this->clients->attach($conn);
        echo ("New connection!: {$conn->resourceId}\n");
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
        echo ("Connection {$conn->resourceId} has disconnected\n");
    }

    public function onError(ConnectionInterface $conn, \Exception $e)
    {
        echo ("An error has occurred: {$e->getMessage()}\n");
        $conn->close();
    }

    // public function broadcast($message)
    // {
    //     if (!self::isWebSocketServerRunning()) {
    //         self::startWebSocketServer();
    //         sleep(1);
    //     }
    //     foreach ($this->clients as $client) {
    //         echo $message;

    //         \Yii::info('Broadcasting message:' . $message . 'WebSocket');
    //         $client->send($message);
    //     }
    // }

    public function broadcast($message)
    {
        if (!self::isWebSocketServerRunning()) {
            self::startWebSocketServer();
            sleep(1);
        }

        if (is_array($message) || is_object($message)) {
            $message = json_encode($message, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        }

        foreach ($this->clients as $client) {
            echo "Broadcasting message: " . $message . "\n";
            \Yii::info('Broadcasting message: ' . $message, 'WebSocket');

            $client->send($message);
        }
    }


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

    protected static function startWebSocketServer()
    {
        echo ("Starting WebSocket server...\n");
        $command = "php yii ws/start > /dev/null 2>&1 &";
        exec($command);
    }

    protected function startAppointmentBroadcastTimer()
    {
        $loop = Factory::create();

        $loop->addPeriodicTimer(10, function () {
            echo "Fetching upcoming appointments at " . date('Y-m-d H:i:s') . "\n";
            $appointments = $this->fetchUpcomingAppointments();
            echo "Fetched appointments: " . json_encode($appointments) . "\n";
            if (!empty($appointments)) {
                $message = json_encode(['event' => 'upcoming_appointments', 'data' => $appointments]);
                $this->broadcast($message);
                echo "Broadcasted upcoming appointments.\n";
            }
        });

        $loop->run();
    }

    protected function fetchUpcomingAppointments()
    {
        $model = new Appointments();
        $upcomingAppointments =   $model->upComingAppointments();

        $result = [];
        foreach ($upcomingAppointments as $appointment) {
            $result[] = [
                'id' => $appointment->id,
                'date' => $appointment->appointment_date,
                'start_time' => $appointment->start_time,
                'end_time' => $appointment->end_time,
                'contact_name' => $appointment->contact_name,
            ];
        }

        return $result;
    }

    public function broadcastAppointmentUpdate($data = 'hello msg')
    {
        $dataToSend = json_encode($data);

        $loop = Factory::create();

        $connector = new Connector($loop);

        $connector('ws://localhost:8080')
            ->then(function ($conn) use ($dataToSend, $loop) {
                $conn->send($dataToSend);

                $loop->addPeriodicTimer(5, function () use ($conn) {
                    $conn->send("ping");

                    // $updatedData = json_encode($newData);
                    // $conn->send($updatedData);
                });

                $conn->on('close', function () {
                    echo "Connection closed by the server\n";
                });
            }, function ($e) {
                echo "Could not connect to WebSocket: {$e->getMessage()}\n";
            });

        $loop->run();
    }
}
