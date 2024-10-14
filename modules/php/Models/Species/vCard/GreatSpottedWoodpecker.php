<?php

namespace FOS\Models\Species\vCard;

use FOS\Managers\Players;
use FOS\Managers\Cards;

/*
 * Great Spotted Woodpecker
 */

class GreatSpottedWoodpecker extends \FOS\Models\Species
{
    public $name = 'Great Spotted Woodpecker';
    public $nb = 4;
    public $tags = [BIRD];
    public $cost = 1;

    //[1 in card]
    public function effect()
    {
        $this->takeOneCardEffect();
    }

    //
    public function bonus()
    {
    }

    //10 [points] if no other forest has more trees
    public function score($playerId, $forests)
    {
        $count = Players::get($playerId)->countInForest('tags', TREE)
            + Players::get($playerId)->countInForest('first_name', 'carpenter');
        foreach ($forests as $opponentId => $forest) {
            $opponentCount = Players::get($opponentId)->countInForest('tags', TREE)
                + Players::get($opponentId)->countInForest('first_name', 'carpenter');
            if ($opponentCount > $count) return 0;
        }
        return 10;
    }
}
