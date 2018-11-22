<?php
namespace Distvan;

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
use Noodlehaus\Config;
use SplObjectStorage;
use DateTime;
use Distvan\SysInfo;

/**
 * Class App
 * @package Distvan
 */
class App implements MessageComponentInterface
{
    protected $_clients;
    protected $_loop;
    protected $_interval;
    protected $_port;
    protected $_sysinfo;

    /**
     * App constructor.
     * @param Config $config
     */
    public function __construct(Config $config)
    {
        $appConf = $config->get('App');
        $this->_interval = isset($appConf['refresh']) && $appConf['refresh'] > 0 ? (int)$appConf['refresh'] : 5;
        $this->_port = isset($appConf['port']) && $appConf['port'] > 0 ? (int)$appConf['port'] : 8080;
        $this->_clients = new SplObjectStorage;
        $this->_sysinfo = new SysInfo($config->get('Database'));
    }

    /**
     * @return int
     */
    public function getPort()
    {
        return (int)$this->_port;
    }

    /**
     * @param ConnectionInterface $conn
     */
    public function onOpen(ConnectionInterface $conn)
    {
        //TODO: token handling, $conn->httpRequest

        $this->_clients->attach($conn);
        echo 'connected:'.$this->_clients->count().PHP_EOL;
    }

    /**
     * @param $value
     */
    public function setLoop($value)
    {
        $this->_loop = $value;
        $this->_loop->addPeriodicTimer($this->_interval, function(){
            $dt = new DateTime();

            if($this->_clients->count() > 0){
                $data = $this->_sysinfo->getServerLoad();
                $loadVal = $data['load'] * 100;
                $response['load'] = $loadVal;
                $response['mysql_connections'] = $this->_sysinfo->getMysqlInfo();
                $response['memory_usage'] = $this->_sysinfo->getMemoryUsage();

                $response['when']['year'] = $dt->format('Y');
                $response['when']['month'] = $dt->format('m');
                $response['when']['day'] = $dt->format('d');
                $response['when']['hour'] = $dt->format('G');
                $response['when']['min'] = $dt->format('i');
                $response['when']['sec'] = $dt->format('s');
            }

            foreach($this->_clients as $client){
                $client->send(json_encode($response));
            }
        });
    }

    /**
     * @param ConnectionInterface $from
     * @param string $msg
     */
    public function onMessage(ConnectionInterface $from, $msg) {}

    /**
     * @param ConnectionInterface $conn
     */
    public function onClose(ConnectionInterface $conn)
    {
        $this->_clients->detach($conn);
        echo "Connection {$conn->resourceId} has disconnected\n";
        echo 'Count:'.$this->_clients->count().PHP_EOL;
    }

    /**
     * @param ConnectionInterface $conn
     * @param \Exception $e
     */
    public function onError(ConnectionInterface $conn, \Exception $e)
    {
        echo "An error has occurred: {$e->getMessage()}\n";

        $conn->close();
    }
}