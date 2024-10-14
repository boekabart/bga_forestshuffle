<?php

namespace FOS\Models\Species\hCard;

use FOS\Managers\Players;
use FOS\Managers\Cards;

/*
 * Beech Marten
 */

class BeechMarten extends \FOS\Models\Species
{
    public $name = 'Beech Marten';
    public $nb = 5;
    public $tags = [PAW];
    public $cost = 1;

    //[1 in card] 
    public function effect()
    {
        $this->takeOneCardEffect();
    }

    //
    public function bonus() {}

    //5 [points] per fully occupied tree
    public function score($playerId, $forests)
    {
        $toTest = [BOTTOM, TOP, LEFT, RIGHT];
        $trees = Players::get($playerId)->getTrees(true);
        $result = 0;
        foreach ($trees as $id => $tree) {
            $full = true;
            foreach ($toTest as $value) {
                if (
                    Cards::getInLocationQ('table', $playerId)
                    ->where('tree', $tree->getTree())
                    ->where('position', $value)->get()->count() == 0
                ) {
                    // Utils::die($id);
                    $full = false;
                    break;
                }
            }
            if ($full) $result += 5;
        }

        return $result;
    }
}
