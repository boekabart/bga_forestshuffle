<?php

namespace FOS\Models\Species\tree;

use FOS\Managers\Players;
use FOS\Managers\Cards;

/*
 * Linden 
 */

class Linden extends \FOS\Models\Species
{
    public $name = 'Linden';
    public $nb = 9;
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

    //[1 point] - or - [3 points] if no other forest has more Lindens
    public function score($playerId, $forests)
    {
        $count = Players::get($playerId)->countInForest('name', $this->name);
        foreach ($forests as $opponentId => $forest) {
            if (Players::get($opponentId)->countInForest('name', $this->name) > $count) return 1;
        }
        return 3;
    }
}
