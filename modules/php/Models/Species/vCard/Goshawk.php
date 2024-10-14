<?php

namespace FOS\Models\Species\vCard;

use FOS\Managers\Players;
use FOS\Managers\Cards;

/*
 * Goshawk
 */

class Goshawk extends \FOS\Models\Species
{
    public $name = 'Goshawk';
    public $nb = 4;
    public $tags = [BIRD];
    public $cost = 2;

    //
    public function effect()
    {
    }

    //
    public function bonus()
    {
    }

    //3 [points] × [bird]
    public function score($playerId, $forests)
    {
        return $this->pointsByTag($playerId, 3, BIRD);
    }
}
