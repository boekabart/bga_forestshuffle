<?php

namespace FOS\Models\Species\tree;

use FOS\Managers\Players;
use FOS\Managers\Cards;

/*
 * Douglas Fir
 */

class DouglasFir extends \FOS\Models\Species
{
    public $name = 'Douglas Fir';
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

    //5 [points] 
    public function score($playerId, $forests)
    {
        return 5;
    }
}
