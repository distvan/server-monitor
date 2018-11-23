<?php
namespace Distvan;

use mysqli;

/**
 * Class SysInfo
 * @package Distvan
 */
class SysInfo
{
    private $_mysqli;
    protected $_os;

    /**
     * SysInfo constructor.
     * @param array $config
     */
    public function __construct($config)
    {
        $this->_os = strtolower(PHP_OS);
        if(isset($config['host']) && !empty($config['host']) &&
            isset($config['user']) && !empty($config['user']) &&
            isset($config['pass']) && !empty($config['pass']) &&
            isset($config['db']) && !empty($config['db'])
        ){
            $this->_mysqli = new mysqli($config['host'], $config['user'], $config['pass'], $config['db']);
        }
    }

    public function getMemoryUsage()
    {
        return memory_get_usage(true);
    }

    public function getMysqlInfo()
    {
        if($this->_mysqli)
        {
            $stats = mysqli_get_connection_stats($this->_mysqli);
        }

        return array(
            'active_connections' => isset($stats['active_connections']) ? $stats['active_connections'] : -1,
            'active_persistent_connections' => isset($stats['active_persistent_connections']) ?
                $stats['active_persistent_connections'] : -1 ,
            'slow_queries' => isset($stats['slow_queries']) ? $stats['slow_queries'] : -1
        );
    }

    public function getServerLoad($windows = false)
    {
        if(strpos($this->_os, 'win') === false)
        {
            if(file_exists('/proc/loadavg'))
            {
                $load = file_get_contents('/proc/loadavg');
                $load = explode(' ', $load);
                $load = $load[0];
            }
            elseif(function_exists('shell_exec'))
            {
                $load = explode(' ', `uptime`);
                $load = $load[count($load)-1];
            }
            else
            {
                return false;
            }

            if(function_exists('shell_exec'))
            {
                $cpu_count = shell_exec('cat /proc/cpuinfo | grep processor | wc -l');
            }

            return array('load' => $load, 'procs' => $cpu_count);
        }
        elseif($windows)
        {
            if(class_exists('COM'))
            {
                $wmi=new COM('WinMgmts:\\\\.');
                $cpus=$wmi->InstancesOf('Win32_Processor');
                $load=0;
                $cpu_count=0;
                if(version_compare('4.50.0', PHP_VERSION) == 1)
                {
                    while($cpu = $cpus->Next())
                    {
                        $load += $cpu->LoadPercentage;
                        $cpu_count++;
                    }
                }
                else
                {
                    foreach($cpus as $cpu)
                    {
                        $load += $cpu->LoadPercentage;
                        $cpu_count++;
                    }
                }

                return array('load' => $load, 'procs' => $cpu_count);
            }
            return false;
        }
        return false;
    }

    public function getNetWorkConnections($port=80)
    {
        $active_connections = -1;

        if(strpos($this->_os, 'win') === false)
        {
            if(function_exists('shell_exec'))
            {
                $active_connections = shell_exec('netstat -an | grep :' . $port . ' | grep -v ESTABILISHED | wc -l');
            }
        }

        return $active_connections;
    }
}