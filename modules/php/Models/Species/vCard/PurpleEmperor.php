<?php

namespace FOS\Models\Species\vCard;

use FOS\Managers\Players;
use FOS\Managers\Cards;

/*
 * Purple Emperor
 */

class PurpleEmperor extends \FOS\Models\Species
{
    public $name = 'Purple Emperor';
    public $nb = 4;
    public $tags = [INSECT, BUTTERFLY];
    public $cost = 0;

    //
    public function effect()
    {
    }

    //
    public function bonus()
    {
    }

    //{table butterflies}
    public function score($playerId, $forests)
    {
        return $this->scoreButterfly($playerId);
    }
}
