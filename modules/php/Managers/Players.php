<?php

namespace FOS\Managers;

use FOS\Core\Game;
use FOS\Core\Globals;
use FOS\Core\Notifications;
use FOS\Core\Stats;
use FOS\Helpers\Utils;

/*
 * Players manager : allows to easily access players ...
 *  a player is an instance of Player class
 */

class Players extends \FOS\Helpers\DB_Manager
{
  protected static $table = 'player';
  protected static $primary = 'player_id';
  protected static function cast($row)
  {
    return new \FOS\Models\Player($row);
  }

  public static function setupNewGame($players, $options)
  {
    // Create players
    $gameInfos = Game::get()->getGameinfos();
    $colors = $gameInfos['player_colors'];
    $query = self::DB()->multipleInsert([
      'player_id',
      'player_color',
      'player_canal',
      'player_name',
      'player_avatar',
      'pendingAction'
    ]);

    $values = [];
    foreach ($players as $pId => $player) {
      $color = array_shift($colors);

      $values[] = [
        $pId,
        $color,
        $player['player_canal'],
        $player['player_name'],
        $player['player_avatar'],
        "[]"
      ];
    }

    $query->values($values);

    Game::get()->reattributeColorsBasedOnPreferences($players, $gameInfos['player_colors']);
    Game::get()->reloadPlayersBasicInfos();
  }

  public static function getActiveId()
  {
    return Game::get()->getActivePlayerId();
  }

  public static function getCurrentId()
  {
    return (int) Game::get()->getCurrentPId();
  }

  public static function getAll()
  {
    return self::DB()->get(false);
  }

  /*
   * get : returns the Player object for the given player ID
   */
  public static function get($pId = null)
  {
    $pId = $pId ?: self::getActiveId();
    return self::DB()
      ->where($pId)
      ->getSingle();
  }

  public static function getActive()
  {
    return self::get();
  }

  public static function getCurrent()
  {
    return self::get(self::getCurrentId());
  }

  public static function getNextId($player = null)
  {
    $player = $player ?? Players::getCurrent();
    $pId = is_int($player) ? $player : $player->getId();
    $table = Game::get()->getNextPlayerTable();
    return $table[$pId];
  }

  public static function getPreviousId($player = null)
  {
    $player = $player ?? Players::getCurrent();
    $pId = is_int($player) ? $player : $player->getId();
    $table = Game::get()->getNextPlayerTable();
    return array_search($pId, $table);
  }



  /*
   * Return the number of players
   */
  public static function count()
  {
    return self::DB()->count();
  }

  /*
   * getUiData : get all ui data of all players
   */
  public static function getUiData($pId)
  {
    return self::getAll()
      ->map(function ($player) use ($pId) {
        return $player->getUiData($pId);
      })
      ->toAssoc();
  }

  /**
   * Get current turn order according to first player variable
   */
  public static function getTurnOrder($firstPlayer = null)
  {
    $firstPlayer = $firstPlayer ?? Globals::getFirstPlayer();
    $order = [];
    $p = $firstPlayer;
    do {
      $order[] = $p;
      $p = self::getNextId($p);
    } while ($p != $firstPlayer);
    return $order;
  }

  /**
   * This allow to change active player
   */
  public static function changeActive($pId)
  {
    Game::get()->gamestate->changeActivePlayer($pId);
  }

  /*
  █████████                               ███     ██████   ███                  
 ███░░░░░███                             ░░░     ███░░███ ░░░                   
░███    ░░░  ████████   ██████   ██████  ████   ░███ ░░░  ████   ██████   █████ 
░░█████████ ░░███░░███ ███░░███ ███░░███░░███  ███████   ░░███  ███░░███ ███░░  
 ░░░░░░░░███ ░███ ░███░███████ ░███ ░░░  ░███ ░░░███░     ░███ ░███ ░░░ ░░█████ 
 ███    ░███ ░███ ░███░███░░░  ░███  ███ ░███   ░███      ░███ ░███  ███ ░░░░███
░░█████████  ░███████ ░░██████ ░░██████  █████  █████     █████░░██████  ██████ 
 ░░░░░░░░░   ░███░░░   ░░░░░░   ░░░░░░  ░░░░░  ░░░░░     ░░░░░  ░░░░░░  ░░░░░░  
             ░███                                                               
             █████                                                              
            ░░░░░                                                               
*/
}
