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
    public $tags = ['Tree'];
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
        // //TODO add new trees
        // $trees = ['Beech', 'Birch', 'Douglas Fir', 'Horse Chestnut', 'Linden', 'Oak', 'Silver Fir', 'Sycamore'];

        // foreach ($trees as $tree) {
        //     if (Players::get($playerId)->countInForest('name', $tree) == 0) return 0;
        // }
        // return 10;
        return $this->countDifferentFromSpecificTag($playerId, 'Tree') >= 8 ? 10 : 0;
    }
}
