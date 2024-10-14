<?php

namespace FOS\Models\Species\hCard;

use FOS\Managers\Players;
use FOS\Managers\Cards;

/*
 * European Hare
 */

class EuropeanHare extends \FOS\Models\Species
{
    public $name = 'European Hare';
    public $nb = 11;
    public $tags = [PAW];
    public $cost = 0;

    //any number of European Hares may share this spot
    public function effect()
    {
    }

    //
    public function bonus()
    {
    }

    //1 [points] per European Hare
    public function score($playerId, $forests)
    {
        return $this->countHares($playerId);
    }
}
