<?php

namespace FOS\Models\Species\hCard;

use FOS\Managers\Players;
use FOS\Managers\Cards;

/*
 * Gnat
 */

class Gnat extends \FOS\Models\Species
{
    public $name = 'Gnat';
    public $nb = 3;
    public $tags = [INSECT];
    public $cost = 0;

    //[bat card ↴] any number
    public function effect()
    {
        return $this->freePlayAll(BAT);
    }

    //
    public function bonus()
    {
    }

    //1 [points] × [bat]
    public function score($playerId, $forests)
    {
        return 1 * Players::get($playerId)->countInForest('tags', BAT);
    }
}
