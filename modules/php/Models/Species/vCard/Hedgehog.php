<?php

namespace FOS\Models\Species\vCard;

use FOS\Managers\Players;
use FOS\Managers\Cards;

/*
 * Hedgehog
 */

class Hedgehog extends \FOS\Models\Species
{
    public $name = 'Hedgehog';
    public $nb = 3;
    public $tags = [PAW];
    public $cost = 1;

    //
    public function effect()
    {
    }

    //[1 in card]
    public function bonus()
    {
        $this->takeOneCardEffect();
    }

    //2 [points] × [Butterfly]
    public function score($playerId, $forests)
    {
        return $this->pointsByTag($playerId, 2, BUTTERFLY);
    }
}
