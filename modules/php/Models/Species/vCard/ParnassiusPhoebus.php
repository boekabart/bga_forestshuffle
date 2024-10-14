<?php

namespace FOS\Models\Species\vCard;

use FOS\Managers\Players;
use FOS\Managers\Cards;

/*
 * Parnassius Phoebus
 */

class ParnassiusPhoebus extends \FOS\Models\Species
{
    public $name = PARNASSIUS_PHOEBUS;
    public $nb = 4;
    public $tags = [BUTTERFLY, INSECT, MOUNTAIN];
    public $cost = 0;

    //
    public function effect()
    {
    }

    //
    public function bonus()
    {
    }

    //Gain points for each set of different butterflies
    public function score($playerId, $forests)
    {
        return $this->scoreButterfly($playerId);
    }
}
