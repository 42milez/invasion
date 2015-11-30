<?php namespace Invasion\Actor;

use Invasion\Config\Config;
use Invasion\Math\Point2D;
use Invasion\Object\Bullet;

class Enemy extends Actor {
    function attack() {
        $bullet = new Bullet($this->id);
        $bullet->setPos(new Point2D($this->pos->x, $this->pos->y + Config::MOVING_AMOUNT_DOWN));
        return $bullet;
    }
}
