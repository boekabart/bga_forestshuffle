<?php

namespace FOS\Models\Species\tree;

use FOS\Managers\Players;
use FOS\Managers\Cards;

/*
 * Pinus Cembra
 */

class PinusCembra extends \FOS\Models\Species
{
    public $name = 'Pinus Cembra';
    public $nb = 7;
    public $tags = [TREE, MOUNTAIN];
    public $cost = 2;

    //Receive 1 card
    public function effect()
    {
        $this->takeOneCardEffect();
    }

    //Receive 1 card
    public function bonus()
    {
        $this->takeOneCardEffect();
    }

    //Gain 1 point for each card with a mountain symbol
    public function score($playerId, $forests)
    {
        return $this->pointsByTag($playerId, 1, MOUNTAIN);
    }
}
