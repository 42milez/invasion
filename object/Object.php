<?php namespace Invasion\Object;

use Invasion\Math\Point2D;

class Object {
    private $id;
    private $pos;

    function __construct() {
        $this->id = spl_object_hash($this) . time();
        $this->pos = new Point2D(0, 0);
    }

    function getId() {
        return $this->id;
    }

    function getPos() {
        return $this->pos;
    }

    function setPos(Point2D $pos) {
        $this->pos = $pos;
    }

    function move(Point2D $pos) {
        $this->pos->x = $pos->x;
        $this->pos->y = $pos->y;
    }
}
