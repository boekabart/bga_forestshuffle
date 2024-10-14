<?php

namespace FOS\Models\Species\vCard;

use FOS\Managers\Players;
use FOS\Managers\Cards;

/*
 * Fireflies
 */

class Fireflies extends \FOS\Models\Species
{
    public $name = 'Fireflies';
    public $nb = 4;
    public $tags = [INSECT];
    public $cost = 0;

    //
    public function effect()
    {
    }

    //
    public function bonus()
    {
    }

    //{table Fireflies}
    public function score($playerId, $forests)
    {
        $count = Players::get($playerId)->countInForest('name', $this->name);

        $scoring = [0, 0, 10, 15, 20];
        return $scoring[$count];
    }
}
