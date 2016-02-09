<?php

namespace LibCloud\Compute\Model;


class NodeState
{
    const RUNNING = 0;
    const REBOOTING = 1;
    const TERMINATED = 2;
    const PENDING = 3;
    const UNKNOWN = 4;
    const STOPPED = 5;
    const SUSPENDED = 6;
    const ERROR = 7;
    const PAUSED = 8;

    public static function toString($state)
    {
        $class = new \ReflectionClass(__CLASS__);
        foreach ($class->getConstants() as $string => $int)
        {
            if ($int == $state)
                return $string;
        }
        throw new \Exception('Undefined state '.$state.' in NodeState class');
    }
}
