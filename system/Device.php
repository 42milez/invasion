<?php namespace Invasion\System;

class Device {
    function setupTerminalCommandInterface() {
        system('stty -icanon');
        system('stty -echo');
    }

    function getKey() {
        $key = '';
        if ($this->non_blocking_read(STDIN, $key)) {
            return $key;
        }
        return $key;
    }

    function non_blocking_read($fd, &$key) {
        $r = [$fd];
        $w = [];
        $e = [];
        $r = stream_select($r, $w, $e, 0);
        if ($r === 0 || $r === false) {
            return false;
        } else {
            $key = stream_get_line($fd, 1);
            return true;
        }
    }
}
