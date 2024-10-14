<?php

namespace FOS\Models\Species\vCard;

use FOS\Managers\Players;
use FOS\Managers\Cards;

/*
 * Chaffinch
 */

class Chaffinch extends \FOS\Models\Species
{
    public $name = 'Chaffinch';
    public $nb = 4;
    public $tags = [BIRD];
    public $cost = 1;

    //
    public function effect()
    {
    }

    //
    public function bonus()
    {
    }

    //5 [points] if on a Beech
    public function score($playerId, $forests)
    {
        $tree = $this->getOwnTree();
        // var_dump("tree", $tree);
        // var_dump('nom', $tree->getSpecies()[0]->name);
        return ($tree->getSpecies()[0]->name == 'Beech') ? 5 : 0;
    }
}
