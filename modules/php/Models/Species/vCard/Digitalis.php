<?php

namespace FOS\Models\Species\vCard;

use FOS\Managers\Players;
use FOS\Managers\Cards;

/*
 * Digitalis
 */

class Digitalis extends \FOS\Models\Species
{
    public $name = 'Digitalis';
    public $nb = 4;
    public $tags = [WOODLAND, PLANT];
    public $cost = 0;


    //{Tabelle Digitalis}
    public function score($playerId, $forests)
    {
        $points = [0, 1, 3, 6, 10, 15];
        return $points[min(5, $this->countDifferentFromSpecificTag($playerId, PLANT))];
    }
}
