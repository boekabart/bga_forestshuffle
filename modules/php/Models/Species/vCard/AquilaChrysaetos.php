<?php

namespace FOS\Models\Species\vCard;

use FOS\Managers\Players;
use FOS\Managers\Cards;

/*
 * Aquila Chrysaetos
 */

class AquilaChrysaetos extends \FOS\Models\Species
{
    public $name = 'Aquila Chrysaetos';
    public $nb = 3;
    public $tags = [BIRD, MOUNTAIN];
    public $cost = 1;

    //
    public function effect()
    {
    }

    //
    public function bonus()
    {
    }

    //Gain 1 point for each card with a paw or amphibian symbol
    public function score($playerId, $forests)
    {
        return $this->pointsByTag($playerId, 1, PAW) + $this->pointsByTag($playerId, 1, AMPHIBIAN);
    }
}
