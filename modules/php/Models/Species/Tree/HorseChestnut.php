<?php

namespace FOS\Models\Species\tree;

use FOS\Managers\Players;
use FOS\Managers\Cards;

/*
 * Horse Chestnut
 */

class HorseChestnut extends \FOS\Models\Species
{
    public $name = 'Horse Chestnut';
    public $nb = 11;
    public $tags = [TREE];
    public $cost = 1;

    //
    public function effect()
    {
    }

    //
    public function bonus()
    {
    }

    //[Table Horse Chestnut]
    public function score($playerId, $forests)
    {
        $count = min(7, Players::get($playerId)->countInForest('name', $this->name));
        return $count * $count;
    }
}
