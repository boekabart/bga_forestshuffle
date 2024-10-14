<?php

namespace FOS\Models\Species\tree;

use FOS\Managers\Players;
use FOS\Managers\Cards;

/*
 * Silver Fir
 */

class SilverFir extends \FOS\Models\Species
{
    public $name = 'Silver Fir';
    public $nb = 6;
    public $tags = [TREE];
    public $cost = 2;

    //
    public function effect()
    {
    }

    //[pawed animal card ↴] 
    public function bonus()
    {
        return $this->freePlay(PAW);
    }

    //2 [points] per card attached to this Silver Fir
    public function score($playerId, $forests)
    {
        $nbCards = count(Cards::getInLocationQ('table', $playerId)
            ->where('tree', $this->card->getTree())
            ->get()) - 1;
        return $nbCards * 2;
    }
}
