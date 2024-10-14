<?php

namespace FOS\Models\Species\tree;

use FOS\Managers\Players;
use FOS\Managers\Cards;

/*
 * Larix Decidua
 */

class LarixDecidua extends \FOS\Models\Species
{
    public $name = 'Larix Decidua';
    public $nb = 7;
    public $tags = ['Tree', MOUNTAIN];
    public $cost = 1;

    //Play a card with a mountain symbol for free (you can’t use its effect or bonus)
    public function effect()
    {
    }

    //
    public function bonus()
    {
        return $this->freePlay(MOUNTAIN);
    }

    //Gain 3 points
    public function score($playerId, $forests)
    {
        return 3;
    }
}
