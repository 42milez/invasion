<?php namespace Invasion\System;

class Renderer {
    private $isEnableFpsCounter;
    private $fps;

    function __construct() {
        $this->isEnableFpsCounter = false;
        $this->fps = 0;
    }

    function clearBuffer() {
        system("clear");
    }

    function renderTitle($str) {
        echo $str . "\n";
    }

    function renderMenu($str) {
        echo $str . ' ';
    }

    function renderLevel($levelBuffer) {
        // FPS カウント
        if ($this->isEnableFpsCounter) $this->fps++;

        // レベルをレンダリング
        foreach ($levelBuffer as $k1 => $line) {
            foreach ($line as $k2 => $c) echo $c;
            echo "\n";
        }
    }

    function toggleFpsCounter() {
        $this->isEnableFpsCounter = !$this->isEnableFpsCounter;
    }

    function fps() {
        $fps = $this->fps;
        $this->fps = 0;
        return $fps;
    }
}
