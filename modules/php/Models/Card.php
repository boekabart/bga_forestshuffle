<?php

namespace FOS\Models;

use FOS\Managers\Cards;
use FOS\Managers\Players;
use FOS\Models\Species\Sapling;
/*
 * Card
 */

class Card extends \FOS\Helpers\DB_Model
{
    protected $table = 'cards';
    protected $primary = 'card_id';
    protected $attributes = [
        'id' => ['card_id', 'int'],
        'location' => 'card_location',
        'state' => ['card_state', 'int'],
        'extraDatas' => ['extra_datas', 'obj'],
        'tree' => ['tree', 'int'],
        'position' => 'position',
    ];

    protected $staticAttributes = [
        'type',
        ['species', 'obj'],
        ['tree_symbol', 'obj'],
    ];

    public function __construct($row, $datas)
    {
        parent::__construct($row);
        foreach ($datas as $attribute => $value) {
            if ($attribute == 'species') {
                $species = [];
                foreach ($value as $id => $specie) {
                    $type = $datas['type'];
                    if ($type == 'wCard') continue;
                    $sp = str_replace('\'', '', str_replace('-', '', str_replace(' ', '', $specie)));
                    $cla = '\\FOS\\Models\\Species\\' . $type . '\\' . $sp;
                    $species[] = new $cla($datas['tree_symbol'][$id], $this);
                }
                $this->$attribute = $species;
            } else $this->$attribute = $value;
        }
    }

    public function getVisible($askedType, $position = null)
    {
        //askedType = 'species' or 'tree_symbol'
        if ($askedType != 'species' && $askedType != 'tree_symbol')
            throw new \BgaVisibleSystemException("SHOULD NOT HAPPEN Bad request on GetVisible, $askedType !");

        $position = $position ?? $this->position;

        if ($position) {
            if ($position == SAPLING) {
                return $askedType == 'species' ? new Sapling($position, $this) : SAPLING;
            }

            if (
                $this->type == TREE
                || ($this->type == V_CARD && $position == TOP)
                || ($this->type == H_CARD && $position == LEFT)
            ) {
                return $this->$askedType[0];
            }
            if (($this->type == V_CARD && $position == BOTTOM)
                || ($this->type == H_CARD && $position == RIGHT)
            ) {
                return $this->$askedType[1];
            }
        }
        return false;
    }

    public function getTranslatableName()
    {
        if (count($this->getSpecies()) == 2) {
            return [
                'log' => clienttranslate('${specie1} / ${specie2}'),
                'args' => [
                    'specie1' => $this->getSpecies()[0]->name,
                    'specie2' => $this->getSpecies()[1]->name,
                    'i18n' => ['specie1', 'specie2']
                ]
            ];
        } else {
            return $this->getSpecies()[0]->name;
        }
    }

    public function getName()
    {
        return implode(' / ', $this->getSpecies());
    }

    /**
     * to distinguish with shrub
     */
    public function isRealTree()
    {
        return in_array(TREE, $this->getSpecies()[0]->tags) || $this->getPosition() == SAPLING;
    }

    public function isOnARealTree()
    {
        return $this->getOwnTree()->isRealTree();
    }

    public function getOwnTree()
    {
        return Cards::getInLocationQ('table', $this->getState())
            ->where('tree', $this->getTree())
            ->whereIn('position', [TREE, SAPLING])
            ->get()->first();
    }

    public function hasTreeSymbol($tree_symbol)
    {
        foreach ($this->tree_symbol as $symbol) {
            if ($symbol == $tree_symbol) return true;
        }
        return false;
    }

    public function isSupported($players, $options)
    {
        return true; // Useful for expansion/ban list/ etc...
    }
}
