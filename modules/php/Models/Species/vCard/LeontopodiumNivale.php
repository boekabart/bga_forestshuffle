<?php

namespace FOS\Models\Species\vCard;

use FOS\Managers\Players;
use FOS\Managers\Cards;

/*
 * LeontopodiumNivale
 */

class LeontopodiumNivale extends \FOS\Models\Species
{
    public $name = 'Leontopodium Nivale';
    public $nb = 2;
    public $tags = [PLANT, MOUNTAIN];
    public $cost = 1;

    //
    public function effect()
    {
        return $this->takeOneCardEffect();
    }

    //
    public function bonus()
    {
        return $this->takeOneCardEffect();
    }

    //Gain 2 points for each different cloven-hoofed animal
    public function score($playerId, $forests)
    {
        return 3;
    }
}
