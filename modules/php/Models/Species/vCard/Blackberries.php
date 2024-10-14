<?php

namespace FOS\Models\Species\vCard;

use FOS\Managers\Players;
use FOS\Managers\Cards;

/*
 * Blackberries
 */

class Blackberries extends \FOS\Models\Species
{
    public $name = 'Blackberries';
    public $nb = 3;
    public $tags = [PLANT];
    public $cost = 0;

    //
    public function effect()
    {
    }

    //
    public function bonus()
    {
    }

    //2 [points] × [plant]
    public function score($playerId, $forests)
    {
        return $this->pointsByTag($playerId, 2, PLANT);
    }
}
