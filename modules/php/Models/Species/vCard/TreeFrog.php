<?php

namespace FOS\Models\Species\vCard;

use FOS\Managers\Players;
use FOS\Managers\Cards;

/*
 * Tree Frog
 */

class TreeFrog extends \FOS\Models\Species
{
    public $name = 'Tree Frog';
    public $nb = 3;
    public $tags = [AMPHIBIAN];
    public $cost = 0;

    //
    public function effect()
    {
    }

    //
    public function bonus()
    {
    }

    //5 [points] per Gnat
    public function score($playerId, $forests)
    {
        return $this->pointsByName($playerId, 5, GNAT);
    }
}
