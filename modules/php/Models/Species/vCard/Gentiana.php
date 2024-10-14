<?php

namespace FOS\Models\Species\vCard;

use FOS\Managers\Players;
use FOS\Managers\Cards;

/*
 * Gentiana
 */

class Gentiana extends \FOS\Models\Species
{
    public $name = 'Gentiana';
    public $nb = 3;
    public $tags = [PLANT, MOUNTAIN];
    public $cost = 0;

    //
    public function effect()
    {
        return $this->freePlay(BUTTERFLY);
    }

    //
    public function bonus()
    {
    }

    //Gain 2 points for each card with a butterfly symbol
    public function score($playerId, $forests)
    {
        return $this->pointsByTag($playerId, 3, BUTTERFLY);
    }
}
