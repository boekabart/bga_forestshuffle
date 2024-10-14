<?php

namespace FOS\Models\Species\{TYPE};

use FOS\Managers\Players;
use FOS\Managers\Cards;

/*
 * {NAME}
 */

class {NAME_PC} extends \FOS\Models\Species
{
    public $name = '{NAME}';
    public $nb = {NB};
    public $tags = {TAGS};
    public $cost = {COST};

    //{EFFECT}
    public function effect()
    {
    }

    //{BONUS}
    public function bonus()
    {
    }

    //{POINTS}
    public function score($playerId, $forests)
    {
        
    }
}
