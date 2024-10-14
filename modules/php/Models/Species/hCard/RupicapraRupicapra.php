<?php

namespace FOS\Models\Species\hCard;

use FOS\Managers\Players;
use FOS\Managers\Cards;

/*
 * Rupicapra Rupicapra
 */

class RupicapraRupicapra extends \FOS\Models\Species
{
    public $name = 'Rupicapra Rupicapra';
    public $nb = 3;
    public $tags = [CLOVEN, MOUNTAIN];
    public $cost = 1;

    //
    public function effect()
    {
    }

    //Gain 3 point for each Pinus symbol
    public function score($playerId, $forests)
    {
        return 3 * Players::get($playerId)->countInForest('tree_symbol', $this->tree_symbol);
    }
}
