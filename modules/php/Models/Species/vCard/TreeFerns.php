<?php

namespace FOS\Models\Species\vCard;

use FOS\Managers\Players;
use FOS\Managers\Cards;

/*
 * Tree Ferns
 */

class TreeFerns extends \FOS\Models\Species
{
    public $name = 'Tree Ferns';
    public $nb = 3;
    public $tags = [PLANT];
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

    //6 [points] × [amphibian]
    public function score($playerId, $forests)
    {
        return $this->pointsByTag($playerId, 6, AMPHIBIAN);
    }
}
