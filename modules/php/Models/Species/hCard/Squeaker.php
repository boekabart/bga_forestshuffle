<?php

namespace FOS\Models\Species\hCard;

use FOS\Managers\Players;
use FOS\Managers\Cards;

/*
 * Squeaker
 */

class Squeaker extends \FOS\Models\Species
{
    public $name = 'Squeaker';
    public $nb = 4;
    public $tags = [CLOVEN];
    public $cost = 0;

    //
    public function effect()
    {
    }

    //
    public function bonus()
    {
    }

    //1 [points]
    public function score($playerId, $forests)
    {
        return 1;
    }
}
