<?php namespace Invasion\System;

use Invasion\Actor\EnemyCommander;
use Invasion\Actor\Enemy;
use Invasion\Actor\Player;
use Invasion\Animation\ScrollBuffer;
use Invasion\Config\Config;
use Invasion\Math\Point2D;
use Invasion\Object\Shield;

class Assembler {
    private $levelBuffer;      // レベルデータ
    private $levelMask;        // レベルマスク（カムフラージュモード用）
    private $levelHeight;
    private $levelWidth;
    private $player;
    private $enemyCommander;
    private $shields;
    private $renderer;
    private $playerBullets;
    private $isCamouflageMode;
    private $scrollBuffer;

    function __construct() {
        $this->levelBuffer = [];
        $this->levelMask = [];
        $this->levelHeight = 0;
        $this->levelWidth = 0;
        $this->player;
        $this->enemyCommander = new EnemyCommander();
        $this->shields = [];
        $this->renderer = new Renderer();
        $this->playerBullets = [];
        $this->isCamouflageMode = false;
        $this->scrollBuffer = new ScrollBuffer();
    }

    function initialize($file, $difficulty) {
        // レベルデータ、およびゲームオブジェクトを初期化する
        $handle = fopen($file, 'r');
        $this->levelWidth = strlen(fgets($handle)) + Config::LEVEL_MARGIN;
        rewind($handle);
        $currentRow = 0;
        while (($buffer = fgets($handle, 4096)) !== false) {
            // Actor / Object の生成
            for ($i = 0, $len = strlen($buffer); $i < $len; $i++) {
                switch ($buffer[$i]) {
                    case Config::SYMBOL_PLAYER:
                        $player = new Player();
                        $player->setPos(new Point2D($i, $currentRow));
                        $this->player = $player;
                        break;
                    case Config::SYMBOL_ENEMY:
                        $enemy = new Enemy();
                        $enemy->setPos(new Point2D($i, $currentRow));
                        $this->enemyCommander->addToUnit($enemy);
                        break;
                    case Config::SYMBOL_SHIELD:
                        $shield = new Shield();
                        $shield->setPos(new Point2D($i, $currentRow));
                        $this->shields[] = $shield;
                        break;
                }
            }

            ++$currentRow;
        }
        $this->levelHeight = $currentRow;
        fclose($handle);

        // レベルデータを格納する配列を初期化する
        for ($y = 0, $levelHeight = $this->levelHeight; $y < $levelHeight; $y++) {
            $this->levelBuffer[$y] = [];
            for ($x = 0, $levelWidth = $this->levelWidth; $x < $levelWidth; $x++) {
                $this->levelBuffer[$y][$x] = '';
            }
        }

        // 敵の攻撃比率を設定
        switch ($difficulty) {
            case Config::DIFFICULTY_EASY:
                $this->enemyCommander->setRatioOfAttackToMove(Config::RATIO_OF_ATTACK_TO_MOVE_EASY);
                break;
            case Config::DIFFICULTY_NORMAL:
                $this->enemyCommander->setRatioOfAttackToMove(Config::RATIO_OF_ATTACK_TO_MOVE_NORMAL);
                break;
            case Config::DIFFICULTY_HARD:
                $this->enemyCommander->setRatioOfAttackToMove(Config::RATIO_OF_ATTACK_TO_MOVE_HARD);
                break;
            case Config::DIFFICULTY_INSANE:
                $this->enemyCommander->setRatioOfAttackToMove(Config::RATIO_OF_ATTACK_TO_MOVE_INSANE);
                break;
            case Config::DIFFICULTY_CAMOUFLAGE:
                $this->enemyCommander->setRatioOfAttackToMove(Config::RATIO_OF_ATTACK_TO_MOVE_EASY);

                // レベルマスクをロードする
                $handle = fopen(Config::FILE_LEVEL_MASK, 'r');
                $currentRow = 0;
                while (($buffer = fgets($handle, 4096)) !== false) {
                    $buffer = rtrim($buffer);
                    $this->levelMask[$currentRow] = [];
                    for ($i = 0, $len = strlen($buffer); $i < $len; $i++) {
                        $this->levelMask[$currentRow][$i] = $buffer[$i];
                    }
                    ++$currentRow;
                }

                // カムフラージュモードを有効にする
                $this->isCamouflageMode = true;

                break;
        }
    }

    // 状態の更新：敵部隊
    function updateEnemy() {
        $currentUnitPos = $this->enemyCommander->getUnitPos();
        $isAdvancing = $this->enemyCommander->isAdvancing();
        if ($currentUnitPos->x <= 0 && $isAdvancing === Config::UNIT_IS_STAYING) {
            $this->enemyCommander->switchUnitDirection(Config::MOVE_DIRECTION_RIGHT);
        } else if ($currentUnitPos->x >= Config::LEVEL_MARGIN * 2 && $isAdvancing === Config::UNIT_IS_STAYING) {
            $this->enemyCommander->switchUnitDirection(Config::MOVE_DIRECTION_LEFT);
        }
        if ($isAdvancing === Config::UNIT_HAS_ARRIVED) {
            $this->enemyCommander->stopAdvance();
        }
        $this->enemyCommander->updateUnit();

        // 攻撃命令
        $this->enemyCommander->attackOrder();
    }

    // 状態の更新：弾丸
    function updateEnemyBullet() {
        $this->enemyCommander->updateBullet($this->levelHeight);

        // 衝突判定
        $bullets = $this->enemyCommander->getBullets();
        foreach ($bullets as $kb => $bullet) {
            // 対シールド
            foreach ($this->shields as $ks => $shield) {
                if (Physics::detectCollision($bullet->getPos(), $shield->getPos())) {
                    $this->enemyCommander->removeBullet($kb);
                    $this->removeShield($ks);
                    break;
                }
            }
            // 対プレイヤー
            if (Physics::detectCollision($bullet->getPos(), $this->player->getPos())) {
                $this->enemyCommander->removeBullet($kb);
                $this->player->deactivate();
                break;
            }
        }
    }

    // 状態の更新：プレイヤー
    function updatePlayer($key) {
        // 移動
        $move = function ($dx) {
            $currentPos = $this->player->getPos();
            $nextPos = new Point2D($currentPos->x + $dx, $currentPos->y);
            if (!Physics::detectOutOfLevel($nextPos, $this->levelWidth, $this->levelHeight)) {
                $this->player->move($nextPos);
            }
        };

        // 弾丸の射出
        $attack = function () {
            $posWillBeCarriedOutAttack = new Point2D($this->player->getPos()->x, $this->player->getPos()->y + Config::MOVING_AMOUNT_DOWN);
            $isVacantPos = true;
            foreach ($this->shields as $k => $shield) {
                $pos = $shield->getPos();
                if (($posWillBeCarriedOutAttack->x === $pos->x) && ($posWillBeCarriedOutAttack->y === $pos->y)) {
                    $isVacantPos = false;
                }
            }
            if ($isVacantPos) {
                $this->playerBullets[] = $this->player->attack();
            }
        };

        // 弾丸の削除
        $removeBullet = function ($key) {
            unset($this->playerBullets[$key]);
        };

        // 弾丸位置の更新と衝突判定
        $updateBullet = function () use ($removeBullet) {
            // 更新
            foreach ($this->playerBullets as $kb => $bullet) {
                $currentPos = $bullet->getPos();
                $nextPos = new Point2D($currentPos->x, $currentPos->y + Config::MOVING_AMOUNT_UP);
                if ($nextPos->y <= 0) {
                    $removeBullet($kb);
                } else {
                    $this->playerBullets[$kb]->move($nextPos);
                }
            }
            // 衝突判定
            $enemies = $this->enemyCommander->getUnit();
            foreach ($this->playerBullets as $kb => $bullet) {
                // 敵
                foreach ($enemies as $ke => $enemy) {
                    if (Physics::detectCollision($bullet->getPos(), $enemy->getPos())) {
                        $this->enemyCommander->removeEnemy($ke);
                        $removeBullet($kb);
                        break;
                    }
                }
                // シールド
                foreach ($this->shields as $ks => $shield) {
                    if (Physics::detectCollision($bullet->getPos(), $shield->getPos())) {
                        $removeBullet($kb);
                        break;
                    }
                }
            }
        };

        // 入力キー別更新操作
        switch ($key) {
            case Config::KEY_MOVE_LEFT:
                $move(Config::MOVING_AMOUNT_LEFT);
                break;
            case Config::KEY_MOVE_RIGHT:
                $move(Config::MOVING_AMOUNT_RIGHT);
                break;
            case Config::KEY_ATTACK:
                if ($this->player->isReadyToAttack()) {
                    $attack();
                }
                break;
        }

        $updateBullet();
    }

    // 状態の更新：レベル
    function updateLevel() {
        $this->clearLevelBuffer();

        if ($this->isCamouflageMode) {
            $this->updateLevelBufferWithMask();
        } else {
            $this->updateLevelBuffer();
        }
    }

    private function updateLevelBuffer() {
        // プレイヤーをマッピング
        $playerPos = $this->player->getPos();
        $this->levelBuffer[$playerPos->y][$playerPos->x] = Config::SYMBOL_PLAYER;

        // プレイヤーの弾丸をマッピング
        foreach ($this->playerBullets as $k => $bullet) {
            $pos = $bullet->getPos();
            $this->levelBuffer[$pos->y][$pos->x] = Config::SYMBOL_PLAYER_BULLET;
        }

        // 敵をマッピング
        $enemyUnit = $this->enemyCommander->getUnit();
        foreach ($enemyUnit as $k => $enemy) {
            $pos = $enemy->getPos();
            $this->levelBuffer[$pos->y][$pos->x] = Config::SYMBOL_ENEMY;
        }

        // 敵の弾丸をマッピング
        $enemyBullets = $this->enemyCommander->getBullets();
        foreach ($enemyBullets as $k => $b) {
            $pos = $b->getPos();
            $this->levelBuffer[$pos->y][$pos->x] = Config::SYMBOL_ENEMY_BULLET;
        }

        // シールドをマッピング
        foreach ($this->shields as $k => $shield) {
            $pos = $shield->getPos();
            $this->levelBuffer[$pos->y][$pos->x] = Config::SYMBOL_SHIELD;
        }
    }

    private function updateLevelBufferWithMask() {
        // レベルをマスクデータで満たす
        foreach ($this->levelMask as $rowIdx => $row) {
            foreach ($row as $colIdx => $col) {
                $this->levelBuffer[$rowIdx][$colIdx] = $col;
            }
        }

        // プレイヤーをマッピング
        $playerPos = $this->player->getPos();
        $c = strtoupper($this->levelBuffer[$playerPos->y][$playerPos->x]);
        $this->levelBuffer[$playerPos->y][$playerPos->x] = "\033[0;32;1m$c\033[0m";

        // プレイヤーの弾丸をマッピング
        foreach ($this->playerBullets as $k => $bullet) {
            $pos = $bullet->getPos();
            $c = strtoupper($this->levelBuffer[$pos->y][$pos->x]);
            $this->levelBuffer[$pos->y][$pos->x] = "\033[0;32;1m$c\033[0m";
        }

        // 敵をマッピング
        $enemyUnit = $this->enemyCommander->getUnit();
        foreach ($enemyUnit as $k => $enemy) {
            $pos = $enemy->getPos();
            $c = strtoupper($this->levelBuffer[$pos->y][$pos->x]);
            $this->levelBuffer[$pos->y][$pos->x] = "\033[0;31;1m$c\033[0m";
        }

        // 敵の弾丸をマッピング
        $enemyBullets = $this->enemyCommander->getBullets();
        foreach ($enemyBullets as $k => $b) {
            $pos = $b->getPos();
            $c = strtoupper($this->levelBuffer[$pos->y][$pos->x]);
            $this->levelBuffer[$pos->y][$pos->x] = "\033[0;31;1m$c\033[0m";
        }

        // シールドをマッピング
        foreach ($this->shields as $k => $shield) {
            $pos = $shield->getPos();
            $c = strtoupper($this->levelBuffer[$pos->y][$pos->x]);
            $this->levelBuffer[$pos->y][$pos->x] = "\033[0;34;1m$c\033[0m";
        }
    }

    function getLevelBuffer() {
        return $this->levelBuffer;
    }

    function getPlayer() {
        return $this->player;
    }

    function isAllEnemyDestroyed() {
        return $this->enemyCommander->isUnitDestroyed();
    }

    private function clearLevelBuffer() {
        // バッファはマスクデータで伸張するため foreach でクリアする
        foreach ($this->levelBuffer as $rowIdx => $row) {
            foreach ($row as $colIdx => $col) {
                $this->levelBuffer[$rowIdx][$colIdx] = ' ';
            }
        }
    }

    private function removeShield($key) {
        unset($this->shields[$key]);
    }

    function animateLevelMask() {
        $this->scrollBuffer->scroll($this->levelMask);
    }
}
