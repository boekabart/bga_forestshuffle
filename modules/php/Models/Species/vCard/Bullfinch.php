<?php

namespace FOS\Models\Species\vCard;

use FOS\Managers\Players;
use FOS\Managers\Cards;

/*
 * Bullfinch
 */

class Bullfinch extends \FOS\Models\Species
{
    public $name = 'Bullfinch';
    public $nb = 4;
    public $tags = [BIRD];
    public $cost = 1;

    //
    public function effect()
    {
    }

    //
    public function bonus()
    {
    }

    //2 [points] × [insect]
    public function score($playerId, $forests)
    {
        return $this->pointsByTag($playerId, 2, INSECT);
    }
}
