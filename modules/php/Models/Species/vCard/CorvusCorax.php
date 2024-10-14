<?php

namespace FOS\Models\Species\vCard;

use FOS\Managers\Players;
use FOS\Managers\Cards;

/*
 * Corvus Corax
 */

class CorvusCorax extends \FOS\Models\Species
{
    public $name = 'Corvus Corax';
    public $nb = 2;
    public $tags = [BIRD, MOUNTAIN];
    public $cost = 1;

    //Receive 1 card 
    public function effect()
    {
        $this->takeOneCardEffect();
    }

    //
    public function bonus()
    {
    }

    //
    public function score($playerId, $forests)
    {
        return 5;
    }
}
