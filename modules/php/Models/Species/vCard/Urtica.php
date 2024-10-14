<?php

namespace FOS\Models\Species\vCard;

use FOS\Core\Notifications;
use FOS\Managers\Players;
use FOS\Managers\Cards;

/*
 * Beech
 */

class Urtica extends \FOS\Models\Species
{
    public $name = 'Stinging Nettle';
    public $nb = 3;
    public $tags = [WOODLAND, PLANT];
    public $cost = 0;

    //any number of butterflies on this [tree], [shrub]
    public function effect() {}

    //
    public function bonus() {}

    //2 [points] x [butterfly]
    public function score($playerId, $forests)
    {
        return $this->pointsByTag($playerId, 2, BUTTERFLY);
    }
}
