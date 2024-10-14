<?php

namespace FOS\Models\Species\hCard;

use FOS\Managers\Players;
use FOS\Managers\Cards;

/*
 * European Badger
 */

class EuropeanBadger extends \FOS\Models\Species
{
    public $name = 'European Badger';
    public $nb = 4;
    public $tags = [PAW];
    public $cost = 1;

    //
    public function effect()
    {
    }

    //[pawed animal card ↴]
    public function bonus()
    {
        return $this->freePlay(PAW);
    }

    //2 [points]
    public function score($playerId, $forests)
    {
        return 2;
    }
}
