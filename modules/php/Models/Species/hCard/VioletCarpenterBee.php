<?php

namespace FOS\Models\Species\hCard;

use FOS\Managers\Players;
use FOS\Managers\Cards;

/*
 * Violet Carpenter Bee
 */

class VioletCarpenterBee extends \FOS\Models\Species
{
    public $name = 'Violet Carpenter Bee';
    public $nb = 4;
    public $tags = [INSECT];
    public $cost = 1;
    public $first_name = 'carpenter';

    public function __construct($tree_symbol, $card)
    {
        $this->tree_symbol = $tree_symbol;
        $this->card = $card;
        if ($card->getLocation() == 'table' && $card->getPosition() != SAPLING) {
            $tree = $this->getOwnTree();
            if ($tree) {
                $this->name = $tree->getVisible('species')->name;
                //if it's on a shrub, loose his function
                if (!$tree->isRealTree()) {
                    $this->first_name = "";
                }
            }
        }
    }

    //scores no points itself, but the tree this bee occupies counts as one additional tree of ist type
    public function effect()
    {
    }

    //
    public function bonus()
    {
    }
}
