<?php

namespace FOS\Models\Species\vCard;

use FOS\Managers\Players;
use FOS\Managers\Cards;

/*
 * Eurasian Jay
 */

class EurasianJay extends \FOS\Models\Species
{
    public $name = 'Eurasian Jay';
    public $nb = 4;
    public $tags = [BIRD];
    public $cost = 1;

    //[action ⟳]
    public function effect()
    {
        return $this->playAgain();
    }

    //
    public function bonus()
    {
    }

    //3 [points]
    public function score($playerId, $forests)
    {
        return 3;
    }
}
