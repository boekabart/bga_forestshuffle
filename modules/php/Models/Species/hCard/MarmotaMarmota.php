<?php

namespace FOS\Models\Species\hCard;

use FOS\Managers\Players;
use FOS\Managers\Cards;

/*
 * Marmota Marmota
 */

class MarmotaMarmota extends \FOS\Models\Species
{
    public $name = 'Marmota Marmota';
    public $nb = 4;
    public $tags = [PAW, MOUNTAIN];
    public $cost = 1;

    //
    public function effect()
    {
    }

    //
    public function bonus()
    {
    }

    //Gain 3 points for each different plants
    public function score($playerId, $forests)
    {
        return 3 * $this->countDifferentPlants($playerId);
    }
}
