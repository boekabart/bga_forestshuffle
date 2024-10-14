<?php

namespace FOS\Models\Species\hCard;

use FOS\Managers\Players;
use FOS\Managers\Cards;

/*
 * Raccoon
 */

class Raccoon extends \FOS\Models\Species
{
    public $name = 'Raccoon';
    public $nb = 4;
    public $tags = [PAW];
    public $cost = 1;

    //place any number of cards from hand in your cave; draw an equal number of cards from the deck
    public function effect()
    {
        $player = Players::getActive();
        $player->addActionToPendingAction(RACCOON);
        return RACCOON;
    }
}
