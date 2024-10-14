<?php

namespace FOS\Models\Species;

use FOS\Core\Globals;
use FOS\Core\Notifications;
use FOS\Managers\Cards;
use FOS\Managers\Players;
use FOS\Models\Species;

/*
 * Species
 */

class Sapling extends Species
{
    public $name = "Tree sapling";
    public $nb = 0;
    public $tree_symbol;
    public $tags = [TREE];
    public $cost = 0;
    public $card; //card in db

    // public function __construct($tree_symbol, $card)
    // {
    //     $this->tree_symbol = SAPLING;
    //     $this->card = $card;
    // }

    public function __toString()
    {
        return $this->name;
    }

    public function is($tag)
    {
        return in_array($tag, $this->tags) || $this->name == $tag;
    }
}
