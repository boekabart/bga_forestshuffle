<?php

namespace FOS\Models\Species\tree;

use FOS\Managers\Players;
use FOS\Managers\Cards;

/*
 * Oak
 */

class Oak extends \FOS\Models\Species
{
    public $name = 'Oak';
    public $nb = 7;
    public $tags = [TREE];
    public $cost = 2;

    //
    public function effect()
    {
    }

    //[action ⟳]
    public function bonus()
    {
        return $this->playAgain();
    }

    //10 [points] if all 8 different tree species are in your forest
    public function score($playerId, $forests)
    {
        return $this->countDifferentFromSpecificTag($playerId, TREE) >= 8 ? 10 : 0;
    }
}
