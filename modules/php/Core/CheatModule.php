<?php

namespace FOS\Core;

use FOS\Core\Game;
use FOS\Helpers\Utils;
use FOS\Managers\Cards;
use FOS\Managers\Players;

/*
 * CheatModule
 */

class CheatModule
{
  protected static $root = "FOS\Managers\\";
  protected static $tables = [
    'Cards' => [
      'location' => [
        'deck' => [
          'method' => 'insertOnTop'
        ],
        'hand' => [
          'method' => 'move',
          'subField' => 'player_id'
        ],
        'clearing' => [
          'method' => 'insertOnTop'
        ]
      ],
      'sub' => [
        'player_id'
      ]
    ]
  ];

  public static function getUIData()
  {
    $result = [];
    $players = Players::getAll()->map(fn($player) => $player->getName());
    foreach (static::$tables as $table => $dataNeeded) {
      $fullClass = static::$root . $table;
      $items = $fullClass::getAll();
      $result[$table][$table] = $items->map(fn($item) => $item->getName());

      foreach ($dataNeeded as $key => $value) {
        if ($key === 'sub') {
          foreach ($value as $name) {
            if ($name = 'player_id') {
              $result[$table][$key][$name] = $players;
            }
          }
        } else {
          $result[$table][$key] = $value;
        }
      }
    }

    return $result;
  }

  public static function actCheat($data)
  {
    if (!Globals::isCheatMode()) {
      return;
    }
    $class = $data['class'];
    $fullClass = static::$root . $class;

    $location = $data['location'];

    $neededData = static::$tables[$class]['location'][$location];

    $method = $neededData['method'];
    $subField = isset($neededData['subField']) ? $neededData['subField'] : null;

    //check if we have everything
    if (!is_null($subField)) {
      if (is_null($data[$subField])) {
        die(var_dump(['infos manquantes', $data]));
      }
    }

    if ($method === 'move') {
      $fullClass::$method($data[$class], $data['location'], $data[$subField]);
    } else {
      $fullClass::insertOnTop($data[$class], $data['location']);
    }

    Notifications::cheat();
  }
}
