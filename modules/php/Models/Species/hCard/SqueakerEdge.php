<?php

namespace FOS\Models\Species\hCard;

use FOS\Managers\Players;
use FOS\Managers\Cards;

/*
 * Squeaker
 */

class SqueakerEdge extends \FOS\Models\Species
{
    public $name = 'Squeaker';
    public $nb = 4;
    public $tags = [CLOVEN, WOODLAND];
    public $cost = 0;

    //1 [points]
    public function score($playerId, $forests)
    {
        return 1;
    }
}
