<?php

namespace FOS\Models\Species\vCard;

use FOS\Managers\Players;
use FOS\Managers\Cards;

/*
 * VacciniumMyrtillus
 */

class VacciniumMyrtillus extends \FOS\Models\Species
{
    public $name = 'Vaccinium Myrtillus';
    public $nb = 2;
    public $tags = [PLANT, MOUNTAIN];
    public $cost = 1;

    //
    public function effect()
    {
        return $this->freePlay(AMPHIBIAN);
    }

    //
    public function bonus()
    {
    }

    //Gain 2 points for each different cloven-hoofed animal
    public function score($playerId, $forests)
    {
        return 2 * $this->countDifferentFromSpecificTag($playerId, BIRD);
    }
}
