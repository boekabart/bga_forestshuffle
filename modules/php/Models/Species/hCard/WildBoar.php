<?php

namespace FOS\Models\Species\hCard;

use FOS\Managers\Players;
use FOS\Managers\Cards;

/*
 * Wild Boar
 */

class WildBoar extends \FOS\Models\Species
{
    public $name = 'Wild Boar';
    public $nb = 5;
    public $tags = [CLOVEN];
    public $cost = 2;

    //
    public function effect()
    {
    }

    //
    public function bonus()
    {
    }

    //10 [points] if at least 1 Squeaker
    public function score($playerId, $forests)
    {
        return (Players::get($playerId)->countInForest('name', SQUEAKER) > 0) ? 10 : 0;
    }
}
