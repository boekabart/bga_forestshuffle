<?php

namespace FOS\Models\Species\vCard;

use FOS\Managers\Players;
use FOS\Managers\Cards;

/*
 * Stag Beetle
 */

class StagBeetle extends \FOS\Models\Species
{
    public $name = 'Stag Beetle';
    public $nb = 2;
    public $tags = [INSECT];
    public $cost = 2;

    //
    public function effect()
    {
    }

    //[bird card ↴]
    public function bonus()
    {
        return $this->freePlay(BIRD);
    }

    //1 [points] × [pawed animal]
    public function score($playerId, $forests)
    {
        return $this->pointsByTag($playerId, 1, PAW);
    }
}
