<?php

namespace FOS\Models\Species\hCard;

use FOS\Managers\Players;
use FOS\Managers\Cards;

/*
 * Brown Long-Eared Bat
 */

class BrownLongEaredBat extends \FOS\Models\Species
{
    public $name = 'Brown Long-Eared Bat';
    public $nb = 3;
    public $tags = [BAT];
    public $cost = 1;

    //
    public function effect()
    {
    }

    //
    public function bonus()
    {
    }

    //5 [points] if at least 3 different [bats]
    public function score($playerId, $forests)
    {
        return $this->countBats($playerId);
    }
}
