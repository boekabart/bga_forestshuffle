<?php

namespace FOS\Models\Species\vCard;

use FOS\Core\Notifications;
use FOS\Managers\Players;
use FOS\Managers\Cards;

/*
 * Beech
 */

class GreatGreenBushCricket extends \FOS\Models\Species
{
    public $name = 'Great Green Bush-Cricket';
    public $nb = 3;
    public $tags = [WOODLAND, INSECT];
    public $cost = 1;

    //[bird ↴]  
    public function effect()
    {
        return $this->freePlay(BIRD);
    }

    //
    public function bonus()
    {
    }

    //1 [points] x [insect]
    public function score($playerId, $forests)
    {
        return $this->pointsByTag($playerId, 1, INSECT);
    }
}
