<?php

namespace FOS\Models\Species\tree;

use FOS\Core\Notifications;
use FOS\Managers\Players;
use FOS\Managers\Cards;

/*
 * Beech
 */

class Beech extends \FOS\Models\Species
{
    public $name = 'Beech';
    public $nb = 10;
    public $tags = [TREE];
    public $cost = 1;

    // [1 in card] 
    public function effect()
    {
        $this->takeOneCardEffect();
    }

    //
    public function bonus()
    {
    }

    //5 [points] if there are at least 4 Beeches in your forest
    public function score($playerId, $forests)
    {
        $count = Players::get($playerId)->countInForest('name', $this->name);
        return ($count >= 4) ? 5 : 0;
    }
}
