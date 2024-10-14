<?php

namespace FOS\Models\Species\vCard;

use FOS\Core\Notifications;
use FOS\Managers\Players;
use FOS\Managers\Cards;

/*
 * Beech
 */

class HazelDoormouse extends \FOS\Models\Species
{
    public $name = HAZEL_DOORMOUSE;
    public $nb = 4;
    public $tags = [WOODLAND, PAW];
    public $cost = 1;

    //
    public function effect()
    {
    }

    //[ card ↴]  Hazel Doormouse
    public function bonus()
    {
        return $this->freePlay(HAZEL_DOORMOUSE);
    }

    //5 [points] x [shrub]
    public function score($playerId, $forests)
    {
        return $this->pointsByTag($playerId, 5, SHRUB);
    }
}
