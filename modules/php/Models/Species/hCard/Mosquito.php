<?php

namespace FOS\Models\Species\hCard;

use FOS\Core\Notifications;
use FOS\Managers\Players;
use FOS\Managers\Cards;

/*
 * Beech
 */

class Mosquito extends \FOS\Models\Species
{
    public $name = MOSQUITO;
    public $nb = 3;
    public $tags = [WOODLAND, INSECT];
    public $cost = 1;

    //[bat ↴]  any number
    public function effect()
    {
        return $this->freePlayAll(BAT);
    }

    //Take all bats from the clearing in your hand 
    public function bonus()
    {
        return $this->takeCardsFromClearing(0, [BAT], HAND, MOSQUITO);
    }

    //1 [points] x [bat]
    public function score($playerId, $forests)
    {
        return $this->pointsByTag($playerId, 1, BAT);
    }
}
