<?php namespace Invasion\System;

use Invasion\Math\Point2D;

class Physics {
    // レベル外に出ていれば true を返す
    static function detectOutOfLevel(Point2D $pos, $width, $height) {
        if ($pos->x < 0) return true;
        if ($pos->x > $width) return true;
        if ($pos->y < 0) return true;
        if ($pos->y > $height) return true;
        return false;
    }

    // 衝突判定
    static function detectCollision($pos1, $pos2) {
        if (($pos1->x === $pos2->x) && ($pos1->y === $pos2->y)) {
            return true;
        } else {
            return false;
        }
    }
}
