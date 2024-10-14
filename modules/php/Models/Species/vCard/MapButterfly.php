<?php

namespace FOS\Models\Species\vCard;

use FOS\Core\Notifications;
use FOS\Managers\Players;
use FOS\Managers\Cards;

/*
 * Beech
 */

class MapButterfly extends \FOS\Models\Species
{
    public $name = 'Map Butterfly';
    public $nb = 4;
    public $tags = [WOODLAND, INSECT, BUTTERFLY];
    public $cost = 0;

    //
    public function effect()
    {
    }

    //
    public function bonus()
    {
    }

    //{table butterflies 3}
    public function score($playerId, $forests)
    {
        return $this->scoreButterfly($playerId);
    }
}
