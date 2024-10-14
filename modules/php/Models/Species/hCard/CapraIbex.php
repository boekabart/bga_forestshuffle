<?php

namespace FOS\Models\Species\hCard;

use FOS\Managers\Players;
use FOS\Managers\Cards;

/*
 * Capra Ibex
 */

class CapraIbex extends \FOS\Models\Species
{
    public $name = 'Capra Ibex';
    public $nb = 3;
    public $tags = [CLOVEN, MOUNTAIN];
    public $cost = 3;

    //
    public function effect()
    {
        return $this->playAgain();
    }

    //Take another turn after this one
    public function bonus()
    {
    }

    //Gain 10 points
    public function score($playerId, $forests)
    {
        return 10;
    }
}
