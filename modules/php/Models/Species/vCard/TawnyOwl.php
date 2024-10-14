<?php

namespace FOS\Models\Species\vCard;

use FOS\Managers\Players;
use FOS\Managers\Cards;

/*
 * Tawny Owl
 */

class TawnyOwl extends \FOS\Models\Species
{
    public $name = 'Tawny Owl';
    public $nb = 4;
    public $tags = [BIRD];
    public $cost = 2;

    //[1 in card]
    public function effect()
    {
        $this->takeOneCardEffect();
    }

    //[2 in card]
    public function bonus()
    {
        if ($this->takeOneCardEffect()) $this->takeOneCardEffect();
    }

    //5 [points]
    public function score($playerId, $forests)
    {
        return 5;
    }
}
