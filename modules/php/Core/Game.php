<?php

namespace FOS\Core;

use ForestShuffle;

/*
 * Game: a wrapper over table object to allow more generic modules
 */

class Game
{
  public static function get()
  {
    return ForestShuffle::get();
  }

  public static function isStateId($stateId)
  {
    return static::get()->gamestate->state_id() == $stateId;
  }

  public static function isRemoveArgStateId()
  {
    //states that need to remove args
    $states = [ST_TAKE_CARD_FROM_CLEARING, ST_PLAY_ALL, ST_TREE_SAPLING_FROM_CLEARING];
    return in_array(static::get()->gamestate->state_id(), $states);
  }

  public static function isNoCostStateId()
  {
    return (static::isStateId(ST_FREE_PLAY) || static::isStateId(ST_FREE_PLAY_ALL));
  }

  public static function transition($transition)
  {
    if (static::isRemoveArgStateId() && $transition == 'end') {
      Globals::removeFirstArgs();
    }

    if (static::isNoCostStateId() && $transition == 'end') {
      Globals::removeFirstCardType();
    }

    static::get()->gamestate->nextState($transition);
  }

  public static function goTo($nextState)
  {
    static::get()->gamestate->jumpToState($nextState);
  }

  public static function setAllPlayersActive()
  {
    static::get()->gamestate->setAllPlayersMultiactive();
  }

  /**
   * check if the action is one of the possible actions in this state
   */
  public static function isPossibleAction($action)
  {
    static::get()->gamestate->checkPossibleAction($action);
  }

  /**
   * check if the current player is active and can perform this action
   */
  public static function checkAction($action)
  {
    static::get()->checkAction($action);
  }
}
