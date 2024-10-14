<?php

namespace FOS\Core;

use FOS\Managers\Cards;
use FOS\Managers\Players;
use FOS\Helpers\Utils;
use FOS\Core\Globals;

class Notifications
{
  public static function cancelPlayAll($player, $cards)
  {
    $data = [
      'player' => $player,
      'cards' => $cards
    ];
    $message = clienttranslate('${player_name} cancels his turn and gets his cards back');
    static::notifyAll('undo', $message, $data);
  }

  public static function mulligan($player, $cards)
  {
    $data = [
      'player' => $player,
    ];
    $msg = clienttranslate('${player_name} changes his cards');
    static::notifyAll('mulligan', $msg, $data);
    $data = [
      'cards' => array_keys($cards->toAssoc()),
      'player_name' => $player->getName()
    ];
    static::notify($player, 'mulligan', $msg, $data);
  }

  public static function newScores($scores, $scoresByCards)
  {
    static::notifyAll('newScores', '', [
      'scores' => $scores,
      'scoresByCards' => $scoresByCards
    ]);
  }

  public static function newWinterCard($cardId)
  {
    $data = [
      'wCardId' => $cardId
    ];
    static::notifyAll('newWinterCard', clienttranslate('A new winter card has been revealed.'), $data);
  }

  public static function playCard($currentPlayer, $card, $specie, $cards, $treeId, $position)
  {
    $data = [
      'player' => $currentPlayer,
      'card' => $card,
      'specie' => $specie->name,
      'cards' => $cards,
      'cost' => count($cards),
      'treeId' => $treeId,
      'position' => $position,
      'minicard' => '',
      'i18n' => ['specie'],
      'preserve' => ['card', 'position']
    ];
    $msg = clienttranslate('By paying ${cost} card(s), ${player_name} adds a ${specie} in his forest ${minicard}');
    static::notifyAll(PLAY_CARD, $msg, $data);
  }

  public static function giveCards($cards, $fromPId, $toPId)
  {
    if (!$cards) return;
    $data = [
      'player' => Players::get($fromPId),
      'cards' => $cards,
      'n' => count($cards),
      'minicards' => '',
      'preserve' => ['cards']
    ];
    $message = clienttranslate('${player_name} gives you ${n} card(s) ${minicards}');
    static::notify($toPId, 'receiveCards', $message, $data);
  }

  public static function receiveCards($cards, $toPId)
  {
    if (!$cards) return;
    $data = [
      'cards' => $cards,
      'preserve' => ['cards']
    ];
    static::notify($toPId, 'receiveCards', '', $data);
  }

  public static function takeAllCardsFromClearing($player, $cardIds, $specie = BROWN_BEAR)
  {
    $message = clienttranslate('Thanks to ${specie}, ${player_name} put ${nb} cards from clearing to his cave');
    $data = [
      'player' => $player,
      'specie' => $specie,
      'nb' => count($cardIds),
      'cards' => $cardIds,
      'i18n' => ['specie']

    ];
    self::notifyAll("takeAllClearing", $message, $data);
  }

  public static function hibernate($player, $cardsIds, $newCards)
  {
    $message = clienttranslate('${player_name} put ${nb} cards from his hand to his cave');
    $data = [
      'player' => $player,
      'nb' => count($cardsIds)
    ];
    static::notifyAll("hibernate", $message, $data);
    $data = [
      'player' => $player,
      'cards' => $cardsIds,
      'nb' => count($cardsIds),
      'newCards' => $newCards
    ];
    static::notify($player, "myHibernate", $message, $data);
  }

  public static function hibernateGypaetus($player, $cardIds)
  {
    $message = clienttranslate('${player_name} put ${nb} cards from the clearing to his cave');
    $data = [
      'player' => $player,
      'nb' => count($cardIds),
      'cards' => $cardIds
    ];
    static::notifyAll("hibernateGypaetus", $message, $data);
  }

  public static function takeCardFromDeck($player, $card)
  {
    $message = clienttranslate('${player_name} picks a card from deck');
    $data = [
      'player' => $player,
    ];
    self::notifyAll("takeCardFromDeck", $message, $data);
    $message = clienttranslate('${player_name} picks a ${cardName} card from deck');
    $data = [
      'player_name' => $player->getName(),
      'card' => $card,
      'minicard' => ''
    ];
    self::notify($player, "takeCardFromDeck", $message, $data);
  }

  public static function takeCardThanksTo($player, $card, $name)
  {
    //card is null if the card picking is postponed at the end of the turn (mole effect)
    if ($card == null) {
      $message = clienttranslate('Thanks to ${name}, ${player_name} will pick a card from deck at the end of the turn');
      $data = [
        'player' => $player,
        'name' => $name,
        'i18n' => ['name']
      ];
      self::message($message, $data);
      return;
    }
    $message = clienttranslate('Thanks to ${name}, ${player_name} picks a card from deck');
    $data = [
      'player' => $player,
      'name' => $name,
      'i18n' => ['name']
    ];
    self::notifyAll("takeCardFromDeck", $message, $data);
    $message = clienttranslate('Thanks to ${name}, ${player_name} picks a ${cardName} card from deck');
    $data = [
      'player_name' => $player->getName(),
      'card' => $card,
      'name' => $name,
      'minicard' => '',
      'i18n' => ['name']
    ];
    self::notify($player, "takeCardFromDeck", $message, $data);
  }

  public static function tooManyCards($player)
  {
    $data = [
      'player' => $player
    ];
    static::message(clienttranslate('${player_name} has already 10 cards and can\'t take a new one.'), $data);
  }

  public static function refreshCounters()
  {
    $data = [
      'cards' => Cards::getUiData(),
      'players' => Players::getUiData(-1),
    ];
    static::notifyAll('refreshCounters', '', $data);
  }

  //          █████                          █████     ███                     
  //         ░░███                          ░░███     ░░░                      
  //  ██████  ░███████    ██████   ██████   ███████   ████  ████████    ███████
  // ███░░███ ░███░░███  ███░░███ ░░░░░███ ░░░███░   ░░███ ░░███░░███  ███░░███
  //░███ ░░░  ░███ ░███ ░███████   ███████   ░███     ░███  ░███ ░███ ░███ ░███
  //░███  ███ ░███ ░███ ░███░░░   ███░░███   ░███ ███ ░███  ░███ ░███ ░███ ░███
  //░░██████  ████ █████░░██████ ░░████████  ░░█████  █████ ████ █████░░███████
  // ░░░░░░  ░░░░ ░░░░░  ░░░░░░   ░░░░░░░░    ░░░░░  ░░░░░ ░░░░ ░░░░░  ░░░░░███
  //                                                                   ███ ░███
  //                                                                  ░░██████ 
  //                                                                   ░░░░░░  

  public static function cheat()
  {
    static::notifyAll('refresh', "", []);
  }

  public static function invitePlayersToAlpha($name, $message, $data)
  {
    static::notify(Players::getCurrent(), $name, $message, $data);
  }
  /*
                                                        █████             ████                                ███                     
                                                       ░░███             ░░███                               ░░░                      
  ██████   ████████   ██████  █████ ████ ████████    ███████      ██████  ░███   ██████   ██████   ████████  ████  ████████    ███████
 ░░░░░███ ░░███░░███ ███░░███░░███ ░███ ░░███░░███  ███░░███     ███░░███ ░███  ███░░███ ░░░░░███ ░░███░░███░░███ ░░███░░███  ███░░███
  ███████  ░███ ░░░ ░███ ░███ ░███ ░███  ░███ ░███ ░███ ░███    ░███ ░░░  ░███ ░███████   ███████  ░███ ░░░  ░███  ░███ ░███ ░███ ░███
 ███░░███  ░███     ░███ ░███ ░███ ░███  ░███ ░███ ░███ ░███    ░███  ███ ░███ ░███░░░   ███░░███  ░███      ░███  ░███ ░███ ░███ ░███
░░████████ █████    ░░██████  ░░████████ ████ █████░░████████   ░░██████  █████░░██████ ░░████████ █████     █████ ████ █████░░███████
 ░░░░░░░░ ░░░░░      ░░░░░░    ░░░░░░░░ ░░░░ ░░░░░  ░░░░░░░░     ░░░░░░  ░░░░░  ░░░░░░   ░░░░░░░░ ░░░░░     ░░░░░ ░░░░ ░░░░░  ░░░░░███
                                                                                                                              ███ ░███
                                                                                                                             ░░██████ 
                                                                                                                              ░░░░░░  
*/
  public static function putCardOnClearing($card)
  {
    $data = [
      'card' => $card,
      // 'minicard' => ''
    ];
    static::notifyAll('putCardOnClearing', '', $data);
  }

  public static function newCardOnClearing($card)
  {
    $message = clienttranslate('As a tree has been played, a ${cardName} card is added to the clearing');
    $data = [
      'card' => $card,
      // 'minicard' => ''
    ];
    static::notifyAll('newCardOnClearing', $message, $data);
  }


  public static function takeCardFromClearing($player, $card)
  {
    $message = clienttranslate('${player_name} picks a ${cardName} card from clearing ${minicard}');
    $data = [
      'player' => $player,
      'card' => $card,
      'minicard' => ''
    ];
    self::notifyAll("takeCardFromClearing", $message, $data);
  }

  public static function clearClearing($message = null)
  {
    $message = $message ?? clienttranslate('There are 10 cards or more in clearing, they are discarded');
    self::notifyAll('clearClearing', $message, []);
  }

  //                                                  ███          
  //                                                 ░░░           
  //  ███████  ██████  ████████    ██████  ████████  ████   ██████ 
  // ███░░███ ███░░███░░███░░███  ███░░███░░███░░███░░███  ███░░███
  //░███ ░███░███████  ░███ ░███ ░███████  ░███ ░░░  ░███ ░███ ░░░ 
  //░███ ░███░███░░░   ░███ ░███ ░███░░░   ░███      ░███ ░███  ███
  //░░███████░░██████  ████ █████░░██████  █████     █████░░██████ 
  // ░░░░░███ ░░░░░░  ░░░░ ░░░░░  ░░░░░░  ░░░░░     ░░░░░  ░░░░░░  
  // ███ ░███                                                      
  //░░██████                                                       
  // ░░░░░░                                                        
  protected static function notifyAll($name, $msg, $data)
  {
    self::updateArgs($data);
    Game::get()->notifyAllPlayers($name, $msg, $data);
  }

  protected static function notify($player, $name, $msg, $data)
  {
    $pId = is_int($player) ? $player : $player->getId();
    self::updateArgs($data);
    Game::get()->notifyPlayer($pId, $name, $msg, $data);
  }

  public static function message($txt, $args = [])
  {
    self::notifyAll('message', $txt, $args);
  }

  public static function messageTo($player, $txt, $args = [])
  {
    $pId = is_int($player) ? $player : $player->getId();
    self::notify($pId, 'message', $txt, $args);
  }

  //                          █████            █████                                                    
  //                         ░░███            ░░███                                                     
  // █████ ████ ████████   ███████   ██████   ███████    ██████      ██████   ████████   ███████  █████ 
  //░░███ ░███ ░░███░░███ ███░░███  ░░░░░███ ░░░███░    ███░░███    ░░░░░███ ░░███░░███ ███░░███ ███░░  
  // ░███ ░███  ░███ ░███░███ ░███   ███████   ░███    ░███████      ███████  ░███ ░░░ ░███ ░███░░█████ 
  // ░███ ░███  ░███ ░███░███ ░███  ███░░███   ░███ ███░███░░░      ███░░███  ░███     ░███ ░███ ░░░░███
  // ░░████████ ░███████ ░░████████░░████████  ░░█████ ░░██████    ░░████████ █████    ░░███████ ██████ 
  //  ░░░░░░░░  ░███░░░   ░░░░░░░░  ░░░░░░░░    ░░░░░   ░░░░░░      ░░░░░░░░ ░░░░░      ░░░░░███░░░░░░  
  //            ░███                                                                    ███ ░███        
  //            █████                                                                  ░░██████         
  //           ░░░░░                                                                    ░░░░░░          

  /*
   * Automatically adds some standard field about player and/or card
   */
  protected static function updateArgs(&$data)
  {
    if (isset($data['player'])) {
      $data['player_name'] = $data['player']->getName();
      $data['player_id'] = $data['player']->getId();
      unset($data['player']);
    }

    if (isset($data['player2'])) {
      $data['player_name2'] = $data['player2']->getName();
      $data['player_id2'] = $data['player2']->getId();
      unset($data['player2']);
    }

    if (isset($data['card'])) {
      $data['cardName'] = $data['card']->getTranslatableName();
      $data['i18n'][] = 'cardName';
      $data['cardId'] = $data['card']->getId();
      $data['cardType'] = $data['card']->getType();
      $data['preserve'][] = 'cardId';
      $data['preserve'][] = 'cardType';
      unset($data['card']);
    }
  }
}
