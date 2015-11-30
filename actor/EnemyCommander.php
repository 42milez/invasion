<?php namespace Invasion\Actor;

use Invasion\Config\Config;
use Invasion\Math\Point2D;
use Invasion\Math\Rand;

class EnemyCommander {
    private $unit;
    private $unitPos;
    private $moveAmountX;
    private $isAdvancing;
    private $bullets;
    private $ratioOfAttackToMove;

    function __construct() {
        $this->unit = [];
        $this->unitPos = new Point2D(5, 0);
        $this->moveAmountX = Config::MOVING_AMOUNT_LEFT;
        $this->isAdvancing = Config::UNIT_IS_STAYING;
        $this->bullets = [];
        $this->ratioOfAttackToMove = Config::RATIO_OF_ATTACK_TO_MOVE_EASY;
    }

    function setRatioOfAttackToMove($ratio) {
        $this->ratioOfAttackToMove = $ratio;
    }

    function addToUnit(Enemy $enemy) {
        $this->unit[] = $enemy;
    }

    function switchUnitDirection($direction) {
        switch ($direction) {
            case Config::MOVE_DIRECTION_LEFT:
                $this->moveAmountX = Config::MOVING_AMOUNT_LEFT;
                $this->isAdvancing = Config::UNIT_IS_ADVANCING;
                break;
            case Config::MOVE_DIRECTION_RIGHT:
                $this->moveAmountX = Config::MOVING_AMOUNT_RIGHT;
                $this->isAdvancing = Config::UNIT_IS_ADVANCING;
                break;
        }
    }

    function updateUnit() {
        // 部隊の X 座標を更新
        if ($this->isAdvancing === Config::UNIT_IS_STAYING) {
            $this->unitPos->x += $this->moveAmountX;
        }
        // 部隊を進める（進軍 or 平行移動）
        foreach ($this->unit as $ke => $enemy) {
            $currentPos = $enemy->getPos();
            $nextPosX = $currentPos->x;
            $nextPosY = $currentPos->y;
            if ($this->isAdvancing) {
                $nextPosY = $nextPosY + Config::MOVING_AMOUNT_DOWN;
                $this->isAdvancing = Config::UNIT_HAS_ARRIVED;
            } else {
                $nextPosX = $nextPosX + $this->moveAmountX;
            }
            // 座標を更新
            $this->unit[$ke]->move(new Point2D($nextPosX, $nextPosY));
        }
    }

    function updateBullet($limitY) {
        foreach ($this->bullets as $k => $b) {
            $currentPos = $b->getPos();
            $nextPos = new Point2D($currentPos->x, $currentPos->y + Config::MOVING_AMOUNT_DOWN);
            if ($nextPos->y >= $limitY) {
                unset($this->bullets[$k]);
            } else {
                $this->bullets[$k]->move($nextPos);
            }
        }
    }

    function getUnitPos() {
        return $this->unitPos;
    }

    function getUnit() {
        return $this->unit;
    }

    function getBullets() {
        return $this->bullets;
    }

    function isAdvancing() {
        return $this->isAdvancing;
    }

    function stopAdvance() {
        $this->isAdvancing = Config::UNIT_IS_STAYING;
    }

    function attackOrder() {
        $unit = $this->unit;
        for ($i = 0, $len = count($unit); $i < $len; $i++) {
            $e = array_shift($unit);
            $posWillBeCarriedOutAttack = new Point2D($e->getPos()->x, $e->getPos()->y + Config::MOVING_AMOUNT_DOWN);
            $isVacantPos = true;
            foreach ($unit as $k => $enemy) {
                $pos = $enemy->getPos();
                if (($posWillBeCarriedOutAttack->x === $pos->x) && ($posWillBeCarriedOutAttack->y === $pos->y)) {
                    $isVacantPos = false;
                }
            }
            if ($isVacantPos) {
                $n = Rand::rand0to100();
                if ($n < $this->ratioOfAttackToMove) {
                    $bullet = $e->attack();
                    $isActorIdExist = false;
                    foreach ($this->bullets as $k => $b) {
                        if ($bullet->getActorId() === $b->getActorId()) {
                            //$isActorIdExist = true;
                        }
                    }
                    if (!$isActorIdExist) {
                        $this->bullets[] = $bullet;
                    }
                }
            }
        }
    }

    function removeEnemy($key) {
        unset($this->unit[$key]);
    }

    function removeBullet($key) {
        unset($this->bullets[$key]);
    }

    function isUnitDestroyed() {
        if (count($this->unit) === 0) {
            return true;
        } else {
            return false;
        }
    }
}
