<?php

namespace FOS\Models\Species\vCard;

use FOS\Managers\Players;
use FOS\Managers\Cards;

/*
 * Large Tortoiseshell
 */

class LargeTortoiseshell extends \FOS\Models\Species
{
    public $name = 'Large Tortoiseshell';
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
