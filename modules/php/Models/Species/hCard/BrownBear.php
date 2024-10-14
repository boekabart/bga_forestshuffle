<?php

namespace FOS\Models\Species\hCard;

use FOS\Core\Game;
use FOS\Core\Globals;
use FOS\Managers\Players;
use FOS\Managers\Cards;

/*
 * Brown Bear
 */

class BrownBear extends \FOS\Models\Species
{
    public $name = 'Brown Bear';
    public $nb = 3;
    public $tags = [PAW];
    public $cost = 3;

    //place all cards from the clearing in your cave
    public function effect()
    {
        if (Game::get()->gamestate->state_id() != ST_PLAY_ALL) {
            $this->takeAllCardsFromClearing(Players::getActive(), $this);
            return;
        } else {
            Players::getActive()->addActionToPendingAction(BEAR);
            Globals::setCardsInClearing(Cards::getInLocation('clearing')->getIds());
            return BEAR;
        }
    }

    //[1 in card] [action ⟳]
    public function bonus()
    {
        $this->takeOneCardEffect();
        return $this->playAgain();
    }
}
