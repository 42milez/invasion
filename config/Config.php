<?php namespace Invasion\Config;

define('ROOT', realpath('.'));

class Config {
    // ゲーム難易度
    const DIFFICULTY_EASY       = 1;
    const DIFFICULTY_NORMAL     = 2;
    const DIFFICULTY_HARD       = 3;
    const DIFFICULTY_INSANE     = 4;
    const DIFFICULTY_CAMOUFLAGE = 5;

    // ゲームステータス
    const GAME_STATUS_RUNNING = 0;
    const GAME_STATUS_CLEAR   = 1;
    const GAME_STATUS_OVER    = 2;

    // 各種ファイル
    const FILE_TITLE      = ROOT . '/files/title.txt';       // ゲームタイトル
    const FILE_MENU       = ROOT . '/files/menu.txt';        // メニュー
    const FILE_LEVEL_01   = ROOT . '/files/lv01.txt';        // レベルデータ
    const FILE_LEVEL_MASK = ROOT . '/files/lv01_mask.txt';   // レベルマスク

    // ゲームパラメータ
    const LEVEL_MARGIN                   = 5;      // レベルのマージン（左右）
    const SYMBOL_PLAYER                  = 'P';    // プレイヤーシンボル
    const SYMBOL_ENEMY                   = 'E';    // 敵シンボル
    const SYMBOL_SHIELD                  = '#';    // シールドシンボル
    const SYMBOL_PLAYER_BULLET           = '^';    // 弾丸（プレイヤー）シンボル
    const SYMBOL_ENEMY_BULLET            = '|';    // 弾丸（敵）シンボル
    const RENDERING_INTERVAL             = 0.03;   // 30fps
    const ENEMY_MOVE_INTERVAL            = 1;      // 敵の移動インターバル
    const MOVING_AMOUNT_LEFT             = -1;     // 左方向への移動量
    const MOVING_AMOUNT_RIGHT            = 1;      // 右方向への移動量
    const MOVING_AMOUNT_UP               = -1;     // 上方向への移動量
    const MOVING_AMOUNT_DOWN             = 1;      // 下方向への移動量
    const FPS_COUNT_INTERVAL             = 1;      // 1 sec
    const KEY_MOVE_LEFT                  = 'a';    // プレイヤーを左に移動するキー
    const KEY_MOVE_RIGHT                 = 'd';    // プレイヤーを右に移動するキー
    const KEY_ATTACK                     = 'l';    // 攻撃キー
    const MOVE_DIRECTION_LEFT            = 'l';    // 移動方向（左）
    const MOVE_DIRECTION_RIGHT           = 'r';    // 移動方向（右）
    const RATIO_OF_ATTACK_TO_MOVE_EASY   = 10;     // 移動に対する攻撃の比率 [%]
    const RATIO_OF_ATTACK_TO_MOVE_NORMAL = 20;     // 移動に対する攻撃の比率 [%]
    const RATIO_OF_ATTACK_TO_MOVE_HARD   = 30;     // 移動に対する攻撃の比率 [%]
    const RATIO_OF_ATTACK_TO_MOVE_INSANE = 90;     // 移動に対する攻撃の比率 [%]
    const UNIT_IS_STAYING                = 0;      // Enemies are staying at row respectively.
    const UNIT_IS_ADVANCING              = 1;      // Enemies are advancing.
    const UNIT_HAS_ARRIVED               = 2;      // Enemies has arrived at destination.
    const ENEMY_BULLET_INTERVAL          = 0.6;    // 弾速（敵）
    const PLAYER_ATTACK_INTERVAL         = 0.3;    // プレイヤーの攻撃インターバル
    const PLAYER_BULLET_INTERVAL         = 0.3;    // 弾速（プレイヤー）
    const MASK_SCROLL_INTERVAL           = 1;      // レベルマスクのスクロールインターバル
}
