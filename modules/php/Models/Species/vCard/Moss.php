<?php

namespace FOS\Models\Species\vCard;

use FOS\Managers\Players;
use FOS\Managers\Cards;

/*
 * Moss
 */

class Moss extends \FOS\Models\Species
{
    public $name = 'Moss';
    public $nb = 3;
    public $tags = [PLANT];
    public $cost = 0;

    //
    public function effect()
    {
    }

    //
    public function bonus()
    {
    }

    //10 [points] there are at least 10 trees in your forest
    public function score($playerId, $forests)
    {
        $count = Players::get($playerId)->countInForest('tags', TREE)
            + Players::get($playerId)->countInForest('first_name', 'carpenter');

        return ($count >= 10) ? 10 : 0;
    }
}
