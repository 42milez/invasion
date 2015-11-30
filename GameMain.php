<?php

require_once('vendor/autoload.php');

use Invasion\Config\Config;
use Invasion\System\Assembler;
use Invasion\System\Device;
use Invasion\System\Renderer;

class GameMain {
    private $assembler;
    private $device;
    private $renderer;
    private $title;
    private $menu;
    private $gameStatus;
    private $enemyTimer;
    private $renderingTimer;
    private $fpsCountTimer;
    private $enemyBulletTimer;
    private $playerBulletTimer;
    private $fps;
    private $maskScrollTimer;

    function __construct() {
        $this->assembler = new Assembler();
        $this->device = new Device();
        $this->renderer = new Renderer();
        $this->renderer->toggleFpsCounter();
        $this->title = file_get_contents(Config::FILE_TITLE);
        $this->menu = file_get_contents(Config::FILE_MENU);
        $this->gameStatus = Config::GAME_STATUS_RUNNING;
        $this->fps = 0;
    }

    // ゲーム起動
    function run() {
        $this->renderer->renderTitle($this->title);
        $this->renderer->renderMenu($this->menu);

        $input = trim(fgets(STDIN));
        switch ($input) {
            case Config::DIFFICULTY_EASY:
                $this->assembler->initialize(Config::FILE_LEVEL_01, Config::DIFFICULTY_EASY);
                break;
            case Config::DIFFICULTY_NORMAL:
                $this->assembler->initialize(Config::FILE_LEVEL_01, Config::DIFFICULTY_NORMAL);
                break;
            case Config::DIFFICULTY_HARD:
                $this->assembler->initialize(Config::FILE_LEVEL_01, Config::DIFFICULTY_HARD);
                break;
            case Config::DIFFICULTY_INSANE:
                $this->assembler->initialize(Config::FILE_LEVEL_01, Config::DIFFICULTY_INSANE);
                break;
            case Config::DIFFICULTY_CAMOUFLAGE:
                $this->assembler->initialize(Config::FILE_LEVEL_01, Config::DIFFICULTY_CAMOUFLAGE);
                break;
        }

        $this->device->setupTerminalCommandInterface();
        $this->startLevel();
    }

    private function startLevel() {
        $currentTime = microtime(true);
        $this->enemyTimer = $currentTime;
        $this->renderingTimer = $currentTime;
        $this->fpsCountTimer = $currentTime;
        $this->enemyBulletTimer = $currentTime;
        $this->playerBulletTimer = $currentTime;
        $this->maskScrollTimer = $currentTime;
        while (1) {
            switch ($this->gameStatus) {
                case Config::GAME_STATUS_RUNNING:
                    $this->play();
                    break;
                case Config::GAME_STATUS_CLEAR:
                    $this->clear();
                    break;
                case Config::GAME_STATUS_OVER:
                    $this->over();
                    break;
            }
        }
    }

    private function play() {
        $currentTime = microtime(true);

        // FPS を取得する
        if (($currentTime - $this->fpsCountTimer) >= Config::FPS_COUNT_INTERVAL) {
            $this->fps = $this->renderer->fps();
            $this->fpsCountTimer = $currentTime;
        }

        if (($currentTime - $this->renderingTimer) >= Config::RENDERING_INTERVAL) {
            if ($this->assembler->getPlayer()->isActivated()) {
                // 更新：プレイヤー
                $this->assembler->updatePlayer($this->device->getKey());

                // 更新：敵
                if (($currentTime - $this->enemyTimer) >= Config::ENEMY_MOVE_INTERVAL) {
                    $this->assembler->updateEnemy();
                    $this->enemyTimer = $currentTime;
                }

                // 更新：敵側弾丸
                if (($currentTime - $this->enemyBulletTimer) >= Config::ENEMY_BULLET_INTERVAL) {
                    $this->assembler->updateEnemyBullet();
                    $this->enemyBulletTimer = $currentTime;
                }

                // 更新：マスクをスクロール
                if (($currentTime - $this->maskScrollTimer) >= Config::MASK_SCROLL_INTERVAL) {
                    $this->assembler->animateLevelMask();
                    $this->maskScrollTimer = $currentTime;
                }

                // 更新：レベル
                $this->assembler->updateLevel();

                // レンダリング
                $this->renderer->clearBuffer();
                $this->renderer->renderLevel($this->assembler->getLevelBuffer());
                echo 'FPS: ' . $this->fps;
                $this->renderingTimer = $currentTime;
            } else {
                $this->gameStatus = Config::GAME_STATUS_OVER;
            }

            if ($this->assembler->isAllEnemyDestroyed()) {
                $this->gameStatus = Config::GAME_STATUS_CLEAR;
            }
        }
    }

    private function clear() {
        $currentTime = microtime(true);
        if (($currentTime - $this->renderingTimer) >= Config::RENDERING_INTERVAL) {
            $this->renderer->clearBuffer();
            $this->renderer->renderLevel($this->assembler->getLevelBuffer());
            echo 'LEVEL CLEAR !';
            $this->renderingTimer = $currentTime;
        }
    }

    private function over() {
        $currentTime = microtime(true);
        if (($currentTime - $this->renderingTimer) >= Config::RENDERING_INTERVAL) {
            $this->renderer->clearBuffer();
            $this->renderer->renderLevel($this->assembler->getLevelBuffer());
            echo 'GAME OVER';
            $this->renderingTimer = $currentTime;
        }
    }
}

$main = new GameMain();
$main->run();
