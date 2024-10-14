<?php

namespace FOS\Models;

use FOS\Core\Game;
use FOS\Core\Globals;
use FOS\Core\Notifications;
use FOS\Helpers\Utils;
use FOS\Managers\Cards;
use FOS\Managers\Players;

/*
 * Species
 */

class Species
{
    public $name;
    public $nb;
    public $tree_symbol;
    public $tags;
    public $cost;
    public $card; //card in db
    public $first_name = '';

    public function __construct($tree_symbol, $card)
    {
        $this->tree_symbol = $tree_symbol;
        $this->card = $card;
    }

    public function __toString()
    {
        return $this->name;
    }

    public function specialEffect($card, $specie, $position)
    {
    }

    public function effect()
    {
    }

    public function bonus()
    {
    }

    public function score($playerId, $forests)
    {
        return 0;
    }

    public function is($tag)
    {
        return in_array($tag, $this->tags) || $this->name == $tag;
    }

    public function isStackable()
    {
        return $this->name == EUROPEAN_HARE || $this->name == COMMON_TOAD || in_array(BUTTERFLY, $this->tags);
    }

    /**
     * Get the tree card on which this card is placed
     */
    public function getOwnTree()
    {
        if ($this->card->getLocation() != 'table') return null; //this card has not been placed yet
        return $this->card->getOwnTree();
    }

    public static function takeOneCardPostponed($name)
    {
        $player = Players::getActive();

        if ($player->getCardsInHand(false) >= 10) {
            Notifications::tooManyCards($player);
            return false;
        }

        $card = Cards::pickCard();
        if (!$card) return false;
        $cardId = $card->getId();
        Cards::move($cardId, 'hand', $player->getId());
        Notifications::takeCardThanksTo($player, $card, $name);
        return true;
    }

    /**
     * can take X card from clearing
     * if nb -> choice return a state
     * if types -> auto act 
     */
    public function takeCardsFromClearing($nb, $types, $where, $specie)
    {
        Globals::addArgs([
            'nb' => $nb,
            'types' => $types,
            'where' => $where,
            'specie' => $specie,
        ]);
        Players::getActive()->addActionToPendingAction(TAKE_CARD_FROM_CLEARING);
        return TAKE_CARD_FROM_CLEARING;
    }

    //return true if the player really got one card, false otherwise
    public function takeOneCardEffect()
    {
        $player = Players::getActive();

        //hack to prevent taking card during Mole effect and just recording how many cards to take later
        if (Game::get()->gamestate->state_id() == ST_PLAY_ALL) {
            $cardsToTake = Globals::getCardsToTake();
            $cardsToTake[] = $this->name;
            Globals::setCardsToTake($cardsToTake);
            Notifications::takeCardThanksTo($player, null, $this->name);
            return true;
        }

        if ($player->getCardsInHand(false) >= 10) {
            Notifications::tooManyCards($player);
            return false;
        }

        $card = Cards::pickCard();
        if (!$card) return false;
        $cardId = $card->getId();
        Cards::move($cardId, 'hand', $player->getId());
        Notifications::takeCardThanksTo($player, $card, $this->name);
        return true;
    }

    public function pointsByName($playerId, $points, $name)
    {
        return $points * Players::get($playerId)->countInForest('name', $name);
    }

    public function pointsByTag($playerId, $points, $tag)
    {
        return $points * Players::get($playerId)->countInForest('tags', $tag);
    }

    public function playAgain()
    {
        $player = Players::getActive();
        $player->addActionToPendingAction(PLAY_AGAIN);
        return PLAY_AGAIN;
    }

    public function playAll($type = 'normal')
    {
        $player = Players::getActive();
        Globals::addArgs([
            'type' => $type
        ]);
        $player->addActionToPendingAction(PLAY_ALL);
        return PLAY_ALL;
    }

    public function freePlayAll($tag)
    {
        $player = Players::getActive();
        if (!is_array($tag)) {
            $tag = [$tag];
        }
        foreach ($tag as $t) {
            Globals::addCardType($t);
            $player->addActionToPendingAction(FREE_PLAY_ALL);
        }
        return FREE_PLAY_ALL;
    }

    public function freePlay($tag)
    {
        $player = Players::getActive();
        if (!is_array($tag)) {
            $tag = [$tag];
        }
        foreach ($tag as $t) {
            Globals::addCardType($t);
            $player->addActionToPendingAction(FREE_PLAY);
        }
        return FREE_PLAY;
    }

    public function countBats($playerId)
    {
        return ($this->countDifferentFromSpecificTag($playerId, BAT) >= 3) ? 5 : 0;
    }

    public function countSalamanders($playerId)
    {
        $count = Players::get($playerId)->countInForest('name', FIRE_SALAMANDER);

        $scoring = [0, 5, 15, 25];
        return $scoring[$count];
    }

    public function countHares($playerId)
    {
        $player = Players::get($playerId);
        return $player->countInForest('name', EUROPEAN_HARE) +
            $player->countInForest('name', LEPUS_TIMIDUS);
    }


    public function countDifferentPlants($playerId)
    {
        return $this->countDifferentFromSpecificTag($playerId, PLANT);
    }

    public function countDifferentFromSpecificTag($playerId, $tag)
    {
        $species = Players::get($playerId)->getForest();
        $plants = array_filter($species, fn ($specie) => in_array($tag, $specie->tags) && $specie->name != "Tree sapling");
        $plantNames = array_map(fn ($specie) => $specie->name, array_values($plants));

        return Utils::count_different($plantNames);
    }

    public function scoreButterfly($playerId)
    {
        $butterflies = [CAMBERWELL_BEAUTY, LARGE_TORTOISESHELL, PEACOCK_BUTTERFLY, PURPLE_EMPEROR, 'Silver-Washed Fritillary', PARNASSIUS_PHOEBUS, MAP_BUTTERFLY];

        $counts = [];
        foreach ($butterflies as $butterfly) {
            $counts[] = Players::get($playerId)->countInForest('name', $butterfly);
        }

        $scoring = [0, 0, 3, 6, 12, 20, 35, 55];
        $score = 0;
        for ($i = 1; $i <= 4; $i++) {
            $score += $scoring[count(array_filter($counts, fn ($count) => $count >= $i))];
        }
        return $score;
    }


    public function takeAllCardsFromClearing($player)
    {
        Notifications::takeAllCardsFromClearing($player, Cards::getInLocation('clearing')->getIds());
        Cards::moveAllInLocation('clearing', 'cave', null, $player->getId());
    }
}
