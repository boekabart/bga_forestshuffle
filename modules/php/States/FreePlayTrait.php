<?php

namespace FOS\States;

use PDO;
use FOS\Core\Game;
use FOS\Core\Globals;
use FOS\Core\Notifications;
use FOS\Core\Engine;
use FOS\Core\Stats;
use FOS\Managers\Cards;
use FOS\Managers\Players;
use FOS\Models\Player;
use FOS\Models\Species;

trait FreePlayTrait
{

	public function argFreePlay()
	{
		$currentPlayer = Players::getActive();
		$type = Globals::getFirstCardType();
		//to prevent from playing at infinite, you can play only card in hand at start of your turn when playing sapling
		$cards = ($type == SAPLING && Globals::getPlayableCards())
			? Cards::getMany(Globals::getPlayableCards())
			: $currentPlayer->getCardsInHand(true);

		$possibleCardIds = [];
		$positionsH = ['onLeft', 'onRight'];
		$positionsV = ['onTop', 'onBottom'];
		$positionTree = ['tree'];

		foreach ($cards as $cardId => $card) {
			if ($type == SAPLING) {
				$possibleCardIds[$cardId] = [SAPLING];
				continue;
			}
			$positions = ($card->getType() == TREE)
				? $positionTree
				: (($card->getType() == V_CARD)
					? $positionsV
					: $positionsH);
			$positionsOK = [];
			foreach ($positions as $id => $position) {
				if ($card->getSpecies()[$id]->is($type)) $positionsOK[] = $position;
			}
			if (count($positionsOK) > 0)
				$possibleCardIds[$cardId] = $positionsOK;
		}
		return [
			'type' => $type,
			'i18n' => ['type'],
			'suffix' => ($type == SAPLING) ? 'sapling' : '',
			'_private' => [
				'active' => [
					'type' => $type,
					'playableCards' => array_keys($possibleCardIds),
					'playableSpecies' => $possibleCardIds,
					'takableCards' => [],
					'canTake' => false,
					'i18n' => ['type'],
				]
			]
		];
	}
}
