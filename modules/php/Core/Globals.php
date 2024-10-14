<?php

namespace FOS\Core;

use FOS\Core\Game;
use FOS\Managers\Players;

/*
 * Globals
 */

class Globals extends \FOS\Helpers\DB_Manager
{
  protected static $initialized = false;
  protected static $variables = [
    'turn' => 'int',
    'firstPlayer' => 'int',
    'cardTypes' => 'obj',
    'cardType' => 'str', //useless now
    'winterCards' => 'int',
    'playableCards' => 'obj',
    'cardsToTake' => 'obj',
    'undoable' => 'int',
    'cardsInClearing' => 'obj',
    'cardsNumber' => 'int',
    //game options
    'isScoreVisible' => 'obj',
    'alpine' => 'bool',
    'edge' => 'bool',
    'draftMode' => 'bool',
    'cheatMode' => 'bool',

    'draftTurn' => 'int',
    'playerChoices' => 'obj',
    'args' => 'obj',
    'expert' => 'bool', //useless now

    //undo
    'storeArgs' => 'obj',
    'storeCardTypes' => 'obj',
    'storePendingAction' => 'obj',
    'storeCardsToTake' => 'obj',
  ];

  protected static $table = 'global_variables';
  protected static $primary = 'name';
  protected static function cast($row)
  {
    $val = json_decode(\stripslashes($row['value']), true);
    return self::$variables[$row['name']] == 'int' ? ((int) $val) : $val;
  }

  public static function setupNewGame($players, $options)
  {
    static::setFirstPlayer(array_keys($players)[0]);
    static::setIsScoreVisible($options[OPTION_SCORE] == OPTION_VISIBLE_SCORE);
    static::setAlpine(isset($options[OPTION_ALPINE_VARIANT]) && $options[OPTION_ALPINE_VARIANT] == OPTION_ALPINE);
    static::setEdge(isset($options[OPTION_EDGE_VARIANT]) && $options[OPTION_EDGE_VARIANT] == OPTION_EDGE);
    static::setDraftMode(isset($options[OPTION_DRAFT_VARIANT]) && $options[OPTION_DRAFT_VARIANT] == OPTION_DRAFT);
    static::setCheatMode(Game::get()->getBgaEnvironment() === 'studio');
  }

  public static function addArgs($args)
  {
    $recordedArgs = static::getArgs();
    $recordedArgs[] = $args;
    static::setArgs($recordedArgs);
  }

  public static function removeFirstArgs()
  {
    $recordedArgs = static::getArgs();
    array_shift($recordedArgs);
    static::setArgs($recordedArgs);
  }

  public static function getFirstArgs()
  {
    $recordedArgs = static::getArgs();
    return count($recordedArgs) ? $recordedArgs[0] : null;
  }

  public static function addCardType($tag)
  {
    $tags = static::getCardTypes();
    $tags[] = $tag;
    static::setCardTypes($tags);
  }

  public static function removeFirstCardType()
  {
    $tags = static::getCardTypes();
    array_shift($tags);
    static::setCardTypes($tags);
  }

  public static function getFirstCardType()
  {
    $tags = static::getCardTypes();
    return count($tags) ? $tags[0] : null;
  }

  //undo
  public static function savePoint($player)
  {
    static::setUndoable(0);
    $cardsIds = array_keys($player->getCardsInHand(true)->toAssoc());
    static::setPlayableCards($cardsIds);
    static::setStoreArgs(static::getArgs());
    static::setStoreCardTypes(static::getCardTypes());
    static::setStorePendingAction($player->getPendingAction());
    static::setStoreCardsToTake(static::getCardsToTake());
  }

  public static function restoreSavePoint($player)
  {
    Globals::setUndoable(0);
    static::setArgs(static::getStoreArgs());
    static::setCardTypes(static::getStoreCardTypes());
    $player->setPendingAction(static::getStorePendingAction());
    Globals::setCardsToTake(static::getStoreCardsToTake());
  }

  public static function resetSavePoint()
  {
    static::setStoreArgs([]);
    static::setStoreCardTypes([]);
    static::setStorePendingAction([]);
  }


  /*
   * Fetch all existings variables from DB
   */
  protected static $data = [];
  public static function fetch()
  {
    // Turn of LOG to avoid infinite loop (Globals::isLogging() calling itself for fetching)
    $tmp = self::$log;
    self::$log = false;

    foreach (
      self::DB()
        ->select(['value', 'name'])
        ->get(false)
      as $name => $variable
    ) {
      if (\array_key_exists($name, self::$variables)) {
        self::$data[$name] = $variable;
      }
    }
    self::$initialized = true;
    self::$log = $tmp;
  }

  /*
   * Create and store a global variable declared in this file but not present in DB yet
   *  (only happens when adding globals while a game is running)
   */
  public static function create($name)
  {
    if (!\array_key_exists($name, self::$variables)) {
      return;
    }

    $default = [
      'int' => 0,
      'obj' => [],
      'bool' => false,
      'str' => '',
    ];
    $val = $default[self::$variables[$name]];
    self::DB()->insert(
      [
        'name' => $name,
        'value' => \json_encode($val),
      ],
      true
    );
    self::$data[$name] = $val;
  }

  /*
   * Magic method that intercept not defined static method and do the appropriate stuff
   */
  public static function __callStatic($method, $args)
  {
    if (!self::$initialized) {
      self::fetch();
    }

    if (preg_match('/^([gs]et|inc|is)([A-Z])(.*)$/', $method, $match)) {
      // Sanity check : does the name correspond to a declared variable ?
      $name = strtolower($match[2]) . $match[3];
      if (!\array_key_exists($name, self::$variables)) {
        throw new \InvalidArgumentException("Property {$name} doesn't exist");
      }

      // Create in DB if don't exist yet
      if (!\array_key_exists($name, self::$data)) {
        self::create($name);
      }

      if ($match[1] == 'get') {
        // Basic getters
        return self::$data[$name];
      } elseif ($match[1] == 'is') {
        // Boolean getter
        if (self::$variables[$name] != 'bool') {
          throw new \InvalidArgumentException("Property {$name} is not of type bool");
        }
        return (bool) self::$data[$name];
      } elseif ($match[1] == 'set') {
        // Setters in DB and update cache
        $value = $args[0];
        if (self::$variables[$name] == 'int') {
          $value = (int) $value;
        }
        if (self::$variables[$name] == 'bool') {
          $value = (bool) $value;
        }

        self::$data[$name] = $value;
        self::DB()->update(['value' => \addslashes(\json_encode($value))], $name);
        return $value;
      } elseif ($match[1] == 'inc') {
        if (self::$variables[$name] != 'int') {
          throw new \InvalidArgumentException("Trying to increase {$name} which is not an int");
        }

        $getter = 'get' . $match[2] . $match[3];
        $setter = 'set' . $match[2] . $match[3];
        return self::$setter(self::$getter() + (empty($args) ? 1 : $args[0]));
      }
    }
    return undefined;
  }

  /**
   *    █████████                                          ███          
   *   ███░░░░░███                                        ░░░           
   *  ███     ░░░   ██████  ████████    ██████  ████████  ████   ██████ 
   * ░███          ███░░███░░███░░███  ███░░███░░███░░███░░███  ███░░███
   * ░███    █████░███████  ░███ ░███ ░███████  ░███ ░░░  ░███ ░███ ░░░ 
   * ░░███  ░░███ ░███░░░   ░███ ░███ ░███░░░   ░███      ░███ ░███  ███
   *  ░░█████████ ░░██████  ████ █████░░██████  █████     █████░░██████ 
   *   ░░░░░░░░░   ░░░░░░  ░░░░ ░░░░░  ░░░░░░  ░░░░░     ░░░░░  ░░░░░░  
   *                                                                    
   *                                                                    
   *                                                                    
   */
  public static function addPlayerChoice($pId, $choice = null)
  {
    // Compute players that still need to select their card
    // => use that instead of BGA framework feature because in some rare case a player
    //    might become inactive eventhough the selection failed (seen in Agricola at least already)
    $choices = static::getPlayerChoices();

    if (is_null($choice)) {
      unset($choices[$pId]);
    } else {
      $choices[$pId] = $choice;
    }

    static::setPlayerChoices($choices);
    $playerIds = Players::getAll()->getIds();
    $ids = array_diff($playerIds, array_keys($choices));

    // At least one player need to make a choice
    if (!empty($ids)) {
      Game::get()->gamestate->setPlayersMultiactive($ids, '', true);
    }
    // Everyone is done => go to next state
    else {
      Game::get()->gamestate->nextState('');
    }
  }

  public static function resetPlayerChoice($pId = null)
  {
    if (is_null($pId)) {
      static::setPlayerChoices([]);
    } else {
      static::addPlayerChoice($pId);
    }
  }
}
