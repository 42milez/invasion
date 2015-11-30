<?php namespace Invasion\Animation;

class ScrollBuffer extends Animation {
    function scroll(&$buffer) {
        $firstRow = array_shift($buffer);
        array_push($buffer, $firstRow);
    }
}
