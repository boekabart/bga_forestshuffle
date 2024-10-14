<?php

namespace FOS\Models\Species\vCard;

use FOS\Managers\Players;
use FOS\Managers\Cards;

/*
 * Red Squirrel
 */

class RedSquirrel extends \FOS\Models\Species
{
    public $name = 'Red Squirrel';
    public $nb = 4;
    public $tags = [PAW];
    public $cost = 0;

    //
    public function effect()
    {
    }

    //
    public function bonus()
    {
    }

    //5 [points] if on an Oak
    public function score($playerId, $forests)
    {
        $tree = $this->getOwnTree();
        return ($tree != null && $tree->hasTreeSymbol(OAK)) ? 5 : 0;
    }
}
