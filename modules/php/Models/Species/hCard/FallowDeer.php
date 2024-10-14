<?php

namespace FOS\Models\Species\hCard;

use FOS\Managers\Players;
use FOS\Managers\Cards;

/*
 * Fallow Deer
 */

class FallowDeer extends \FOS\Models\Species
{
    public $name = 'Fallow Deer';
    public $nb = 4;
    public $tags = [CLOVEN, DEER];
    public $cost = 2;

    //
    public function effect()
    {
    }

    //[2 in card]
    public function bonus()
    {
        if ($this->takeOneCardEffect()) $this->takeOneCardEffect();
    }

    //3 [points] × [cloven-hoofed animal]
    public function score($playerId, $forests)
    {
        return 3 * Players::get($playerId)->countInForest('tags', CLOVEN);
    }
}
