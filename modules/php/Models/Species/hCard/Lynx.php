<?php

namespace FOS\Models\Species\hCard;

use FOS\Managers\Players;
use FOS\Managers\Cards;

/*
 * Lynx
 */

class Lynx extends \FOS\Models\Species
{
    public $name = 'Lynx';
    public $nb = 6;
    public $tags = [PAW];
    public $cost = 1;

    //
    public function effect()
    {
    }

    //
    public function bonus()
    {
    }

    //10 [points] if at least 1 Roe Deer
    public function score($playerId, $forests)
    {
        return (Players::get($playerId)->countInForest('name', 'Roe Deer')) ? 10 : 0;
    }
}
