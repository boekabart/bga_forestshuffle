<?php

namespace FOS\Models\Species\hCard;

use FOS\Managers\Players;
use FOS\Managers\Cards;

/*
 * Tetrao Urogallus
 */

class TetraoUrogallus extends \FOS\Models\Species
{
    public $name = 'Tetrao Urogallus';
    public $nb = 4;
    public $tags = [BIRD, MOUNTAIN];
    public $cost = 1;

    //
    public function effect()
    {
        return $this->freePlay(PLANT);
    }

    //
    public function bonus()
    {
    }

    //Gain 2 points for each card with a plant symbol
    public function score($playerId, $forests)
    {
        return $this->pointsByTag($playerId, 1, PLANT);
    }
}
