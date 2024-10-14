<?php

namespace FOS\Models\Species\vCard;

use FOS\Managers\Players;
use FOS\Managers\Cards;

/*
 * Fire Salamander
 */

class FireSalamander extends \FOS\Models\Species
{
    public $name = 'Fire Salamander';
    public $nb = 3;
    public $tags = [AMPHIBIAN];
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

    //{table Fire Salamander}
    public function score($playerId, $forests)
    {
        return $this->countSalamanders($playerId);
    }
}
