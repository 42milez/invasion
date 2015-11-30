<?php namespace Invasion\Actor;

use Invasion\Config\Config;
use Invasion\Math\Point2D;
use Invasion\Object\Bullet;

class Player extends Actor {
    private $attackInterval;

    function move(Point2D $pos) {
        $this->setPos($pos);
        $this->attackInterval = microtime(true);
    }

    function isReadyToAttack() {
        $currentTime = microtime(true);
        if (($currentTime - $this->attackInterval) >= Config::PLAYER_ATTACK_INTERVAL) {
            $this->attackInterval = $currentTime;
            return true;
        } else {
            return false;
        }
    }

    function attack() {
        $bullet = new Bullet($this->id);
        $bullet->setPos(new Point2D($this->pos->x, $this->pos->y + Config::MOVING_AMOUNT_UP));
        return $bullet;
    }
}
