<?php

use FOS\Core\CheatModule;

/**
 *------
 * BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
 * ForestShuffle implementation : © Emmanuel Albisser <emmanuel.albisser@gmail.com>
 *
 * This code has been produced on the BGA studio platform for use on https://boardgamearena.com.
 * See http://en.doc.boardgamearena.com/Studio for more information.
 * -----
 * 
 * forestshuffle.action.php
 *
 * ForestShuffle main action entry point
 *
 *
 * In this file, you are describing all the methods that can be called from your
 * user interface logic (javascript).
 *       
 * If you define a method "myAction" here, then you can call it from your javascript code with:
 * this.ajaxcall( "/forestshuffle/forestshuffle/myAction.html", ...)
 *
 */


class action_forestshuffle extends APP_GameAction
{
  // Constructor: please do not modify
  public function __default()
  {
    if (self::isArg('notifwindow')) {
      $this->view = "common_notifwindow";
      $this->viewArgs['table'] = self::getArg("table", AT_posint, true);
    } else {
      $this->view = "forestshuffle_forestshuffle";
      self::trace("Complete reinitialization of board game");
    }
  }

  public function changeCards()
  {
    self::setAjaxMode();

    $this->game->actMulligan(true);

    self::ajaxResponse();
  }

  public function passMulligan()
  {
    self::setAjaxMode();

    $this->game->actMulligan(false);

    self::ajaxResponse();
  }

  public function pass()
  {
    self::setAjaxMode();

    $this->game->actPass();

    self::ajaxResponse();
  }

  public function takeCard()
  {
    self::setAjaxMode();

    $cardId = self::getArg("cardId", AT_posint, false);
    if ($cardId) {
      $this->game->actTakeCardFromClearing($cardId);
    } else {
      $this->game->actTakeCardFromDeck();
    }

    self::ajaxResponse();
  }

  public function playCard()
  {
    self::setAjaxMode();

    $cardId = self::getArg("cardId", AT_posint, true);
    $cards = self::getArg('cards', AT_json, false) ?? [];
    $position = self::getArg("position", AT_alphanum, true);
    $treeId = self::getArg("treeId", AT_posint, false);
    $this->validateJSonAlphaNum($cards, 'cards');
    $this->game->actPlayCard($cardId, $cards, $treeId, $position);

    self::ajaxResponse();
  }

  public function actChooseCard()
  {
    self::setAjaxMode();

    $cards = self::getArg('cards', AT_json, true);
    $this->validateJSonAlphaNum($cards, 'cards');
    $this->game->actChooseCard($cards);

    self::ajaxResponse();
  }

  public function hibernateGypaetus() //deprecated
  {
    self::setAjaxMode();

    $cards = self::getArg('cards', AT_json, true);
    $this->validateJSonAlphaNum($cards, 'cards');
    $this->game->actHibernateGypaetus($cards);

    self::ajaxResponse();
  }

  public function hibernate()
  {
    self::setAjaxMode();

    $cards = self::getArg('cards', AT_json, true);
    $this->validateJSonAlphaNum($cards, 'cards');
    $this->game->actHibernate($cards);

    self::ajaxResponse();
  }

  public function undo()
  {
    self::setAjaxMode();

    $this->game->actUndo();

    self::ajaxResponse();
  }

  public function actGiveCards()
  {
    self::setAjaxMode();

    $cards = self::getArg('cards', AT_json, true);
    $this->validateJSonAlphaNum($cards, 'cards');
    $this->game->actGiveCards($cards);

    self::ajaxResponse();
  }

  public function actChangeMind()
  {
    self::setAjaxMode();

    $this->game->actChangeMind();

    self::ajaxResponse();
  }

  //   █████████  █████   █████ ██████████   █████████   ███████████
  //  ███░░░░░███░░███   ░░███ ░░███░░░░░█  ███░░░░░███ ░█░░░███░░░█
  // ███     ░░░  ░███    ░███  ░███  █ ░  ░███    ░███ ░   ░███  ░ 
  //░███          ░███████████  ░██████    ░███████████     ░███    
  //░███          ░███░░░░░███  ░███░░█    ░███░░░░░███     ░███    
  //░░███     ███ ░███    ░███  ░███ ░   █ ░███    ░███     ░███    
  // ░░█████████  █████   █████ ██████████ █████   █████    █████   
  //  ░░░░░░░░░  ░░░░░   ░░░░░ ░░░░░░░░░░ ░░░░░   ░░░░░    ░░░░░    
  //                                                                
  //                                                                
  //                                                                

  public function cheat()
  {
    self::setAjaxMode();
    $data = self::getArg('data', AT_json, true);
    $this->validateJSonAlphaNum($data, 'data');

    CheatModule::actCheat($data);
    self::ajaxResponse();
  }

  public function loadBugSQL()
  {
    self::setAjaxMode();
    $reportId = (int) self::getArg('report_id', AT_int, true);
    $this->game->loadBugSQL($reportId);
    self::ajaxResponse();
  }

  public function validateJSonAlphaNum($value, $argName = 'unknown')
  {
    if (is_array($value)) {
      foreach ($value as $key => $v) {
        $this->validateJSonAlphaNum($key, $argName);
        $this->validateJSonAlphaNum($v, $argName);
      }
      return true;
    }
    if (is_int($value)) {
      return true;
    }
    $bValid = preg_match("/^[_0-9a-zA-Z- ]*$/", $value) === 1;
    if (!$bValid) {
      throw new BgaSystemException("Bad value for: $argName", true, true, FEX_bad_input_argument);
    }
    return true;
  }
}
