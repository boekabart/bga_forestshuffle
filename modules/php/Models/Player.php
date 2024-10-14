<?php

namespace FOS\Models;

use FOS\Core\Stats;
use FOS\Core\Preferences;
use FOS\Managers\Players;
use FOS\Managers\Cards;
use FOS\Managers\Cells;

/*
 * Player: all utility functions concerning a player
 */

class Player extends \FOS\Helpers\DB_Model
{
  protected $table = 'player';
  protected $primary = 'player_id';
  protected $attributes = [
    'id' => ['player_id', 'int'],
    'no' => ['player_no', 'int'],
    'name' => 'player_name',
    'color' => 'player_color',
    'eliminated' => 'player_eliminated',
    'score' => ['player_score', 'int'],
    'scoreAux' => ['player_score_aux', 'int'],
    'zombie' => 'player_zombie',
    'pendingAction' => ['pendingAction', 'obj']
  ];

  public function getUiData($currentPlayerId = null)
  {
    $data = parent::getUiData();
    $isCurrent = $this->id == $currentPlayerId;
    $data['hand'] = $this->getCardsInHand($isCurrent);
    $data['trees'] = $this->filterCards($this->getTrees());
    $data['table'] = $this->getSpeciesCards();
    $data['cave'] = $this->getCardsInCave();

    return $data;
  }

  protected function filterCards($cards)
  {
    $filteredCards = [];
    $index = 0;
    foreach ($cards as $id => $card) {
      if ($card->getPosition() == SAPLING) {
        $newId = $this->id . '_sapling_' . $index++;
        $filteredCards[$newId] = [
          'id' => $newId,
          'location' => $card->getLocation(),
          'state' => $card->getState(),
          'position' => $card->getPosition(),
          'tree' => $card->getTree(),
        ];
      } else $filteredCards[$id] = $card;
    }
    return $filteredCards;
  }

  public function getCardsInHand($isCurrent = true)
  {
    return ($isCurrent) ? Cards::getInLocation('hand', $this->id) : Cards::countInLocation('hand', $this->id);
  }

  //get all cards on table
  public function getCardsOnTable()
  {
    return Cards::getInLocation('table', $this->id);
  }

  //return card number in cave
  public function getCardsInCave()
  {
    return Cards::countInLocation('cave', $this->id);
  }

  //get only tree cards on table
  public function getTrees($bNotShrubs = false)
  {
    $cards = Cards::getInLocationQ('table', $this->id)
      ->whereIn('position', [TREE, 'sapling'])
      ->get();

    if ($bNotShrubs) {
      $cards = $cards->filter(fn ($card) => $card->isRealTree());
    }
    return $cards;
  }

  //get only species cards on table
  public function getSpeciesCards()
  {
    return Cards::getInLocationQ('table', $this->id)
      ->whereNotIn('position', [TREE, 'sapling'])
      ->get();
  }

  public function getForest()
  {
    $cards = $this->getCardsOnTable();

    $species = [];

    foreach ($cards as $cardId => $card) {
      $species[] = $card->getVisible('species');
    }

    return $species;
  }

  //give the number of occurence of a value as criteria
  public function countInForest($criteria, $value)
  {
    $species = $this->getForest();
    return count(array_filter(
      $species,
      fn ($specie) => (is_array($specie->$criteria)) ? in_array($value, $specie->$criteria) : $specie->$criteria == $value
    ));
  }

  //check if a player has no tree in hand
  public function hasNoTree()
  {
    $cards = $this->getCardsInHand()->toArray();
    return count(array_filter($cards, fn ($card) => $card->getType() == TREE)) == 0;
  }

  public function isAvailablePosition($treeId, $position, $specie)
  {
    //first tree must exist
    $treeExists = (0 != count(Cards::getInLocationQ('table', $this->id)
      ->where('tree', $treeId)
      ->get()));
    if (!$treeExists)  // return false;
      throw new \BgaVisibleSystemException("this tree $treeId doesn't exist !");

    //if tree exists, check if place is already taken
    $placeTaken = Cards::getInLocationQ('table', $this->id)
      ->where('position', $position)
      ->where('tree', $treeId)
      ->count();

    //if it's already taken, only 3 specific species can use it anyway
    if ($placeTaken) {
      if (!$specie->isStackable()) return false;
      else {
        $specieOnPlace = Cards::getInLocationQ('table', $this->id)
          ->where('position', $position)
          ->where('tree', $treeId)->get()->first()->getVisible('species');
        if (
          $specie->name == COMMON_TOAD && $placeTaken == 1 && $specieOnPlace->name == COMMON_TOAD
        ) return true;
        else if (
          $specie->name == $specieOnPlace->name ||
          (in_array(BUTTERFLY, $specie->tags) && in_array(BUTTERFLY, $specieOnPlace->tags))
        ) return true;
        else {
          throw new \BgaVisibleSystemException("this place is already taken !$specieOnPlace");
        }
      }
    } else return true;
  }

  public function getNextTreeId()
  {
    //next Tree ID is the number of trees in the forest + 1
    return Cards::getInLocationQ('table', $this->id)
      ->whereIn('position', [TREE, 'sapling'])
      ->count() + 1;
  }

  /*
     █████████                                          ███                  
    ███░░░░░███                                        ░░░                   
   ███     ░░░   ██████  ████████    ██████  ████████  ████   ██████   █████ 
  ░███          ███░░███░░███░░███  ███░░███░░███░░███░░███  ███░░███ ███░░  
  ░███    █████░███████  ░███ ░███ ░███████  ░███ ░░░  ░███ ░███ ░░░ ░░█████ 
  ░░███  ░░███ ░███░░░   ░███ ░███ ░███░░░   ░███      ░███ ░███  ███ ░░░░███
   ░░█████████ ░░██████  ████ █████░░██████  █████     █████░░██████  ██████ 
    ░░░░░░░░░   ░░░░░░  ░░░░ ░░░░░  ░░░░░░  ░░░░░     ░░░░░  ░░░░░░  ░░░░░░  
                                                                             
                                                                             
                                                                             
  */


  public function addActionToPendingAction($action, $bFirst = false)
  {
    // $player = is_numeric($player) ? Players::get($player) : $player;
    $pendingActions = $this->getPendingAction();

    if ($bFirst) {
      array_unshift($pendingActions, $action);
    } else {
      array_push($pendingActions, $action);
    }

    $this->setPendingAction($pendingActions);
  }

  public function getActionFromPendingAction($bFirst = true, $bDestructive = true)
  {

    $pendingActions = $this->getPendingAction();

    if ($bFirst) {
      $action = array_shift($pendingActions);
    } else {
      $action = array_pop($pendingActions);
    }

    if ($bDestructive) {
      $this->setPendingAction($pendingActions);
    }
    return $action;
  }

  /**
   * Used to know if we can launch a playAgainAction (as it should be only after all others action)
   */
  public function hasOnlyPlayAgainAction()
  {
    $pendingActions = $this->getPendingAction();
    foreach ($pendingActions as $action) {
      if ($action != PLAY_AGAIN) return false;
    }
    return true;
  }


  public function getPref($prefId)
  {
    return Preferences::get($this->id, $prefId);
  }

  public function getStat($name)
  {
    $name = 'get' . \ucfirst($name);
    return Stats::$name($this->id);
  }
}
