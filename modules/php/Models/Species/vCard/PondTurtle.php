<?php

namespace FOS\Models\Species\vCard;

use FOS\Managers\Players;
use FOS\Managers\Cards;

/*
 * Pond Turtle
 */

class PondTurtle extends \FOS\Models\Species
{
    public $name = 'Pond Turtle';
    public $nb = 2;
    public $tags = [AMPHIBIAN];
    public $cost = 2;

    //[1 in card]
    public function effect()
    {
        $this->takeOneCardEffect();
    }

    //
    public function bonus()
    {
    }

    //5 [points]
    public function score($playerId, $forests)
    {
        return 5;
    }
}
