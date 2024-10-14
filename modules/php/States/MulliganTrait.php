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
use FOS\Models\Card;
use FOS\Models\Player;

trait MulliganTrait
{
	public function stChooseSetup()
	{
		if (Globals::isDraftMode()) {
			Globals::setDraftTurn(1);
			Game::transition("draft");
		} else {
			Game::transition("normal");
		}
	}

	public function argDraft()
	{
		$players = Players::getAll();
		$data = [];
		$choices = Globals::getPlayerChoices();
		$data['suffix'] = count($players) == 2 ? "Duo" : "";
		$data['nKeep'] = DRAFT[count($players)]['keep'];
		$data['nRight'] = DRAFT[count($players)]['giveRight'];
		$data['nLeft'] = DRAFT[count($players)]['giveLeft'];
		$data['nClearing'] = DRAFT[count($players)]['clearing'];
		$data['nTrash'] = DRAFT[count($players)]['remove'];
		$data['nRounds'] = DRAFT[count($players)]['rounds'];
		$data['draftTurn'] = Globals::getDraftTurn();


		foreach (Players::getAll() as $pId => $player) {
			$data['_private'][$pId] = [
				'choices' => array_key_exists($pId, $choices) ? $choices[$pId] : [],
				'cards' => Cards::getInLocation("draft", $pId),
				'leftPlayer' => Players::get(Players::getnextId($pId))->getName(),
				'rightPlayer' => Players::get(Players::getPreviousId($pId))->getName(),
			];
		}
		return $data;
	}

	public function actAutomaticGiveCards($pId)
	{
		$args = $this->getArgs();
		$cards = $args['_private'][$pId]['cards']->toArray();

		$givenCards = [];
		foreach (['keep', 'right', 'left', 'clearing', 'trash'] as $location) {
			$givenCards[$location] = [];
			for ($i = 0; $i < $args['n' . ucfirst($location)]; $i++) {
				$givenCards[$location][] = array_shift($cards)->getId();
			}
		}

		Notifications::message(clienttranslate('${player_name} is ready to draft'), ['player' => Players::get($pId)]);
		Globals::addPlayerChoice($pId, $givenCards);
	}

	public function actGiveCards($cards)
	{
		//get infos
		$pId = Game::get()->getCurrentPlayerId();

		//check rules
		Game::checkAction('actGiveCards');

		$args = $this->getArgs();
		foreach (['keep', 'right', 'left', 'clearing', 'trash'] as $location) {
			$givenCards = $cards[$location];
			if (count($givenCards) != $args['n' . ucfirst($location)]) {
				throw new \BgaVisibleSystemException("You didn't give the right number of card for $location");
			}
			foreach ($givenCards as $cardId) {
				if (!in_array($cardId, $args['_private'][$pId]['cards']->getIds())) {
					throw new \BgaVisibleSystemException("This card $cardId is not in your draft hand. Should not happen.");
				}
			}
		}

		Notifications::message(clienttranslate('${player_name} is ready to draft'), ['player' => Players::get($pId)]);
		Globals::addPlayerChoice($pId, $cards);
	}

	public function actChangeMind()
	{
		//get infos
		$pId = Game::get()->getCurrentPlayerId();

		//check rules
		Game::isPossibleAction('actChangeMind');

		$args = $this->getArgs();

		if (!$args['_private'][$pId]['choices']) {
			throw new \BgaVisibleSystemException("You should not been allowed to change mind now. Should not happen.");
		} else {
			Globals::resetPlayerChoice($pId);
			Notifications::message(clienttranslate('${player_name} changes mind'), ['player' => Players::get($pId)]);
		}
	}

	public function stConfirm()
	{
		//for each player move cards
		$choices = Globals::getPlayerChoices();

		foreach ($choices as $pId => $cards) {

			foreach ($cards as $location => $cardIds) {
				switch ($location) {
					case 'keep':
						Cards::move($cardIds, 'hand', $pId);
						Notifications::receiveCards($cardIds, $pId);
						break;
					case 'left':
						Cards::move($cardIds, 'pre_left', $pId);
						break;
					case 'right':
						Cards::move($cardIds, 'pre_right', $pId);
						break;
					case 'clearing':
						Cards::move($cardIds, 'pre_clearing');
						break;
					case 'trash':
						Cards::move($cardIds, 'trash');
						break;
				}
			}
		}

		// if draft turn = nRounds go to start
		$nRounds = DRAFT[count($choices)]['rounds'];

		if ($nRounds == Globals::getDraftTurn()) {
			$this->stReveal();
			Game::transition(END_TURN);
		} else {
			Globals::incDraftTurn(1);
			Globals::resetPlayerChoice();
			Game::setAllPlayersActive();
			$players = Players::getAll();
			$nbCards = DRAFT[count($players)]['draw'];
			foreach ($players as $pId => $player) {
				Cards::pickForLocation($nbCards, 'deck', 'draft', $pId);
			}
			Game::transition('keepDrafting');
		}
	}

	public function stReveal()
	{
		$players = Players::getAll();

		foreach ($players as $pId => $player) {
			//pre_right -> to hand of the player on the right
			$leftPId = Players::getnextId($pId);
			$rightPId = Players::getPreviousId($pId);

			$cards = Cards::getInLocation('pre_right', $pId)->getIds();
			Cards::moveAllInLocation('pre_right', 'hand', $pId, $rightPId);

			Notifications::giveCards($cards, $pId, $rightPId);

			$cards = Cards::getInLocation('pre_left', $pId)->getIds();
			Cards::moveAllInLocation('pre_left', 'hand', $pId, $leftPId);

			Notifications::giveCards($cards, $pId, $leftPId);
		}

		$cardsToClearing = Cards::getInLocation('pre_clearing');
		foreach ($cardsToClearing as $cardId => $card) {
			Cards::move($cardId, 'clearing');
			Notifications::putCardOnClearing($card);
		}

		Notifications::refreshCounters();
	}


	public function argMulligan()
	{
		$players = Players::getAll();
		$data = [];
		foreach ($players as $id => $player) {
			$data[$id] = [
				'canMulligan' => $player->hasNoTree()
			];
		}
		return [
			'_private' => $data,
		];
	}

	public function stMulligan()
	{
		$players = Players::getAll();
		foreach ($players as $pId => $player) {
			if (!$player->hasNoTree())
				$this->gamestate->setPlayerNonMultiactive($pId, '');
		}
	}

	public function actMulligan($changeCard)
	{

		$pId = Game::get()->getCurrentPlayerId();
		self::checkAction(($changeCard) ? CHANGE_CARDS : PASS_MULLIGAN);

		//if pass -> ok return
		if ($changeCard) {
			$currentPlayer = Players::get($pId);

			$args = $this->argMulligan();

			if (!$args['_private'][$pId]['canMulligan']) {
				throw new \BgaVisibleSystemException("You can't change your cards.");
			}

			Cards::moveAllInLocation('hand', 'trash', $pId);

			$cards = Cards::pickForLocation(6, 'deck', 'hand', $pId);

			Notifications::mulligan($currentPlayer, $cards);
		}

		$this->gamestate->setPlayerNonMultiactive($pId, '');
	}
}
