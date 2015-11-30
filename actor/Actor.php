<?php namespace Invasion\Actor;

use Invasion\Math\Point2D;

class Actor {
    protected $id;
    protected $pos;
    protected $isActivated;

    function __construct() {
        $this->id = spl_object_hash($this) . time();
        $this->pos = new Point2D(0, 0);
        $this->isActivated = true;
    }

    function getId() {
        return $this->id;
    }

    function getPos() {
        return $this->pos;
    }

    function setPos(Point2D $pos) {
        $this->pos->x = $pos->x;
        $this->pos->y = $pos->y;
    }

    function move(Point2D $pos) {
        $this->pos->x = $pos->x;
        $this->pos->y = $pos->y;
    }

    function activate() {
        $this->isActivated = true;
    }

    function deactivate() {
        $this->isActivated = false;
    }

    function isActivated() {
        return $this->isActivated;
    }
}
