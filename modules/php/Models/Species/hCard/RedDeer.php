<?php

namespace FOS\Models\Species\hCard;

use FOS\Managers\Players;
use FOS\Managers\Cards;

/*
 * Red Deer
 */

class RedDeer extends \FOS\Models\Species
{
    public $name = 'Red Deer';
    public $nb = 5;
    public $tags = [CLOVEN, DEER];
    public $cost = 2;

    //
    public function effect()
    {
    }

    //[cloven-hoofed animal card ↴]
    public function bonus()
    {
        return $this->freePlay(DEER);
    }

    //1 [points] × [tree], [plant] 
    public function score($playerId, $forests)
    {
        return Players::get($playerId)->countInForest('tags', TREE) + Players::get($playerId)->countInForest('tags', PLANT);
    }
}
