<?php

namespace FOS\Models\Species\hCard;

use FOS\Managers\Players;
use FOS\Managers\Cards;

/*
 * Roe Deer
 */

class RoeDeer extends \FOS\Models\Species
{
    public $name = 'Roe Deer';
    public $nb = 5;
    public $tags = [CLOVEN, DEER];
    public $cost = 2;

    //
    public function effect()
    {
    }

    //[1 in card]
    public function bonus()
    {
        $this->takeOneCardEffect();
    }

    //3 [points] × [matching tree symbol]
    public function score($playerId, $forests)
    {
        return 3 * Players::get($playerId)->countInForest('tree_symbol', $this->tree_symbol);
    }
}
