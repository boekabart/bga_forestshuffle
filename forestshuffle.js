/**
 *------
 * BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
 * ForestShuffle implementation : © Emmanuel Albisser <emmanuel.albisser@gmail.com>
 *
 * This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
 * See http://en.boardgamearena.com/#!doc/Studio for more information.
 * -----
 *
 * forestshuffle.js
 *
 * forestshuffle user interface script
 *
 * In this file, you are describing the logic of your user interface, in Javascript language.
 *
 */

/**This help to console log differently on studio or on production */
var isDebug =
  window.location.host == "studio.boardgamearena.com" ||
  window.location.hash.indexOf("debug") > -1;
var debug = isDebug ? console.info.bind(window.console) : function () {};

define([
  "dojo",
  "dojo/_base/declare",
  "ebg/core/gamegui",
  "ebg/counter",
  g_gamethemeurl + "modules/js/Core/game.js",
  g_gamethemeurl + "modules/js/Core/modal.js",
  g_gamethemeurl + "modules/js/card.js",
  g_gamethemeurl + "modules/js/Utils/utils.js",
  g_gamethemeurl + "modules/js/Utils/cheatModule.js",
], function (dojo, declare) {
  return declare(
    "bgagame.forestshuffle",
    [customgame.game, forestshuffle.utils, forestshuffle.cheatModule],
    {
      constructor() {
        debug("forestshuffle constructor");

        this._activeStates = [
          "playerTurn",
          "mulligan",
          "retake",
          "freePlayAll",
          "freePlay",
          "hibernate",
          "playAll",
          "takeCardFromClearing",
          "hibernateGypaetus",
        ];
        this._notifications = [
          ["clearClearing", 1000],
          ["takeCardFromClearing", 500],
          ["takeAllClearing", 500],
          ["newCardOnClearing", 500],
          ["newWinterCard", 4000],
          ["playCard", 500],
          ["newScores", 0],
          ["myHibernate", 500],
          ["hibernateGypaetus", 500],
          ["hibernate", 500, (notif) => notif.args.player_id == this.player_id],
          ["mulligan", 1000, (notif) => notif.args.player_id == this.player_id],
          [
            "takeCardFromDeck",
            500,
            (notif) => notif.args.player_id == this.player_id,
          ],
          ["willTakeCardFromDeck", 500],
          ["undo", 500],
          ["refresh", 0],
          ["putCardOnClearing", 300],
          ["receiveCards", 300],
          ["refreshCounters", 0],
        ];

        // Fix mobile viewport (remove CSS zoom)
        this.default_viewport = "width=800";

        // this._settingsSections = [];
        this._settingsConfig = {};
      },

      /*
  █████████  ██████████ ███████████ █████  █████ ███████████ 
 ███░░░░░███░░███░░░░░█░█░░░███░░░█░░███  ░░███ ░░███░░░░░███
░███    ░░░  ░███  █ ░ ░   ░███  ░  ░███   ░███  ░███    ░███
░░█████████  ░██████       ░███     ░███   ░███  ░██████████ 
 ░░░░░░░░███ ░███░░█       ░███     ░███   ░███  ░███░░░░░░  
 ███    ░███ ░███ ░   █    ░███     ░███   ░███  ░███        
░░█████████  ██████████    █████    ░░████████   █████       
 ░░░░░░░░░  ░░░░░░░░░░    ░░░░░      ░░░░░░░░   ░░░░░        
                                                             
                                                             
                                                             
        */

      setup(gamedatas) {
        debug("setup", gamedatas);

        // this._notifications = [];
        // this._activeStates = [];
        // this._connections = [];
        // this._selectableNodes = [];
        // this._activeStatus = null;
        // this._customTooltipIdCounter = 0;
        // this._registeredCustomTooltips = {};

        onresize = (event) => {
          this.adaptWidth();
        };

        //create decks + deckInfos
        this.counters = [];
        this.counters["caves"] = [];
        this.counters["hands"] = [];
        this.counters["deck"] = this.addCounterOnDeck(
          "deck",
          gamedatas.cards.deck_count
        );
        this.counters["discard"] = this.addCounterOnDeck(
          "discard",
          gamedatas.cards.discard_count
        );
        this.counters["clearing"] = this.addCounterOnDeck(
          "board",
          Object.values(gamedatas.cards.clearing).length,
          false
        );

        for (const cardId in gamedatas.cards.clearing) {
          this.createCardInClearing(cardId);
        }

        // Setting up player boards
        for (const playerId in gamedatas.players) {
          const player = gamedatas.players[playerId];

          // add a player panel
          this.place(
            "tplPlayerPanel",
            player,
            `overall_player_board_${player.id}`
          );
          this.counters["caves"][player.id] = this.createCounter(
            `cave-counter-${player.id}`,
            player.cave
          );
          this.counters["hands"][player.id] = this.createCounter(
            `card-counter-${player.id}`,
            this.player_id == player.id
              ? Object.values(player.hand).length
              : player.hand
          );

          // add a player board
          this.place("table_tpl", player, "tables");

          if (this.player_id == playerId) {
            for (const cardId in player.hand) {
              this.createCardInHand(cardId);
            }
          }

          for (const cardId in player.trees) {
            this.addCardToTable(
              cardId,
              playerId,
              player.trees[cardId].tree,
              player.trees[cardId].position,
              false
            );
          }

          for (const cardId in player.table) {
            this.addCardToTable(
              cardId,
              playerId,
              player.table[cardId].tree,
              player.table[cardId].position,
              false
            );
          }
        }

        this.myUpdatePlayerOrdering("table", "tables");

        //add general tooltips
        this.addTooltipToClass("cards-counter", _("Cards in player hand"), "");
        this.addTooltipToClass(
          "cave-counter",
          _("Cards in player cave (score 1 point each)"),
          ""
        );

        //make a second turn to display nicely hare and toads and butterflies
        for (const cardId in gamedatas.scoresByCards) {
          this.createTooltip(cardId, gamedatas.scoresByCards[cardId]);
        }

        // add shortcut and navigation
        dojo.connect($("pin"), "onclick", (e) => {
          dojo.toggleClass("cards", "changed");
          window.localStorage.setItem(
            "pinned",
            $("cards").classList.contains("changed")
          );
        });

        if (window.localStorage.getItem("pinned") == "true") {
          $("pin").click();
        }

        //add and setup winter panel
        this.place("winter_tpl", gamedatas.cards.winterCards, "player_boards");

        gamedatas.cards.winterCards.forEach((cardId) => {
          this.place("card_tpl", cardId, "wCardStorage");
          this.createTooltip(cardId);
        });

        this.counters["winterCard"] = this.createCounter(
          `counter-wCard`,
          gamedatas.cards.winterCards.length
        );
        dojo.connect($("zoom_value"), "oninput", () => {
          // debug('zoom changed', $('zoom_value').value);
          window.localStorage.setItem("FOS_zoom", $("zoom_value").value);
          this.adaptWidth();
        });
        this.adaptWidth();
        if (gamedatas.cards.winterCards.length == 2) {
          this.displayCaution();
        }

        let chk = $("help-mode-chk");

        dojo.connect(chk, "onchange", () => {
          this.toggleHelpMode(chk.checked);
        });
        this.addTooltip("help-mode-switch", "", _("Toggle help mode."));

        //add cheat block if cheatModule is active
        if (gamedatas.cheatModule) {
          this.cheatModuleSetup(gamedatas);
        }

        this.inherited(arguments);
        debug("Ending game setup");
      },

      /**
█████████  ███████████   █████████   ███████████ ██████████  █████████ 
███░░░░░███░█░░░███░░░█  ███░░░░░███ ░█░░░███░░░█░░███░░░░░█ ███░░░░░███
░███    ░░░ ░   ░███  ░  ░███    ░███ ░   ░███  ░  ░███  █ ░ ░███    ░░░ 
░░█████████     ░███     ░███████████     ░███     ░██████   ░░█████████ 
░░░░░░░░███    ░███     ░███░░░░░███     ░███     ░███░░█    ░░░░░░░░███
███    ░███    ░███     ░███    ░███     ░███     ░███ ░   █ ███    ░███
░░█████████     █████    █████   █████    █████    ██████████░░█████████ 
 ░░░░░░░░░     ░░░░░    ░░░░░   ░░░░░    ░░░░░    ░░░░░░░░░░  ░░░░░░░░░  
                                                              
 */
      onUpdateActivityDraft(args) {
        this.activateDraftButtons();
      },

      onLeavingStateDraft() {
        if (this._helpMode) $("help-mode-chk").click();
        dojo.query("#forestShuffle-choose-card .card").forEach((elem) => {
          this.smartDestroy(elem.id);
        });
      },

      onEnteringStateDraft(args) {
        if (!this._helpMode) $("help-mode-chk").click();
        this.addPrimaryActionButton("btn_show", _("Show cards"), () =>
          this.modal.show()
        );
        this.modal = new customgame.modal("showCards", {
          class: "popin",
          closeIcon: "fa-times",
          title: this.fsr(this.gamedatas.gamestate.descriptionmyturn, args),
          closeAction: "hide",
          autoShow: true,
          contentsTpl: `<div id='forestShuffle-choose-card'></div><div id="forestShuffle-choose-card-footer" class="active"></div>`,
        });

        const draftSelection = [
          "nothing",
          "keep",
          "left",
          "right",
          "clearing",
          "trash",
        ].filter(
          (place) => place == "nothing" || args["n" + ucFirst(place)] > 0
        );

        //place some hint in the footer
        draftSelection.forEach((element) => {
          if (element == "nothing") return;
          dojo.place(
            `<div id='hint_${element}' class='tinyHint ${element}'>${
              args["n" + ucFirst(element)]
            }</div>`,
            "forestShuffle-choose-card-footer"
          );
        });

        this.addTooltipToClass("trash", _("cards to trash"), "");
        this.addTooltipToClass("keep", _("cards to keep"), "");

        this.addTooltipToClass(
          "left",
          this.fsr("cards to give to ${leftPlayer}", {
            leftPlayer: args._private.leftPlayer,
          }),
          ""
        );
        this.addTooltipToClass(
          "right",
          this.fsr("cards to give to ${rightPlayer}", {
            rightPlayer: args._private.rightPlayer,
          }),
          ""
        );
        this.addTooltipToClass(
          "clearing",
          _("cards to put in the clearing"),
          ""
        );

        Object.entries(args._private.cards).forEach(([cardId, card]) => {
          this.createCard(cardId, "topbar");
          this.genericMove(
            "card_" + cardId,
            "forestShuffle-choose-card",
            false
          );
          const elem = $(`card_${cardId}`);

          //add a default class (will be changed later if a choice has already been made)
          elem.classList.add(draftSelection[0]);

          this.onClick(`card_${cardId}`, () => {
            //can't change anything if you are not active
            if (!this.isCurrentPlayerActive()) {
              return;
            }

            let classe = "";
            for (let index = 0; index < draftSelection.length; index++) {
              classe = draftSelection[index];
              if (elem.classList.contains(classe)) {
                let newClasse = "";
                for (let i = 1; i <= draftSelection.length; i++) {
                  newClasse =
                    draftSelection[(index + i) % draftSelection.length];
                  //if new classe already validated, skip it
                  if (
                    !$("hint_" + newClasse) ||
                    !$("hint_" + newClasse).classList.contains("validated")
                  ) {
                    break;
                  }
                }
                elem.classList.remove(classe);
                elem.classList.add(newClasse);
                break;
              }
            }
            //check if all choices have been done
            this.checkDraftChoices(args, draftSelection);
          });
        });

        //if a choice has been made, display it, and allow 'change mind' else allow 'draft'
        Object.entries(args._private.choices).forEach(([classe, ids]) => {
          ids.forEach((cardId) => {
            $(`card_${cardId}`).classList.add(classe);
          });
        });

        this.addPrimaryActionButton(
          "btn_draft",
          _("Draft"),
          () => {
            //take each card ID and attribute a location
            let cards = {};
            ["keep", "left", "right", "clearing", "trash"].forEach(
              (location) => {
                cards[location] = dojo
                  .query(".card." + location)
                  .map((elem) => elem.dataset.id);
              }
            );
            this.takeAction(
              "actGiveCards",
              { cards: JSON.stringify(cards) },
              false
            );
            this.modal.hide();
          },
          "forestShuffle-choose-card-footer"
        );

        this.addPrimaryActionButton(
          "btn_reset",
          _("Change mind"),
          () => {
            this.takeAction("actChangeMind", {}, false);
            //clearDraft
            draftSelection.forEach((element) => {
              if (element == "nothing") return;
              dojo
                .query(".card." + element)
                .removeClass(element)
                .addClass("nothing");
            });
            this.checkDraftChoices(args, draftSelection);
          },
          "forestShuffle-choose-card-footer"
        );

        dojo.addClass("btn_draft", "disabled");

        //check if all choices have been done
        this.checkDraftChoices(args, draftSelection);
      },

      onEnteringStateFreePlayAll(args) {
        this.onEnteringStateFreePlay(args);
      },

      onEnteringStateFreePlay(args) {
        Object.keys(args._private.playableSpecies).forEach((elem) => {
          this.onClick(
            "card_" + elem,
            (e) => {
              this.selectCardToPlay(e, args._private.playableSpecies[elem]);
            },
            true
          );
        });
        if (args.suffix == "sapling") {
          this.addPrimaryActionButton(
            "btn-sapling",
            _("Play as tree sapling"),
            () => {
              const cardId = this.getCardIdFromDiv(
                dojo.query("#cards .selected")[0]
              );
              this.takeAction("playCard", {
                cardId: cardId,
                position: "sapling",
              });
            }
          );
          dojo.addClass("btn-sapling", "disabled");
        } else {
          this.addPrimaryActionButton("btn-pay", _("Play Card"), () => {
            this.onPressButtonPlayCard();
          });
          dojo.addClass("btn-pay", "disabled");
        }

        this.addDangerActionButton("btn_pass", _("Pass"), () => {
          this.takeAction("pass");
        });
      },

      onEnteringStateHibernate(args) {
        args._private.playableCards.forEach((cardId) => {
          this.onClick(
            "card_" + cardId,
            (e) => {
              this.selectCardToDiscard(e);
            },
            true
          );
        });

        this.addPrimaryActionButton("btn-discard", _("Send to Cave"), () => {
          const cardIds = this.buildJSONIds(".selected");
          debug(cardIds);
          this.takeAction("hibernate", { cards: cardIds });
        });

        dojo.addClass("btn-discard", "disabled");

        this.addDangerActionButton("btn_pass", _("Pass"), () => {
          this.takeAction("pass");
        });
      },

      onEnteringStateTakeCardFromClearing(args) {
        args.playableCards.forEach((cardId) => {
          this.onClick(
            "card_" + cardId,
            (e) => {
              this.selectCardToDiscardGypaetus(e, args.nb);
            },
            true
          );
        });

        const label =
          args.where == "cave" ? _("Send to Cave") : _("Take in hand");

        this.addPrimaryActionButton("btn-discard", label, () => {
          const cardIds = this.buildJSONIds(".selected");
          this.takeAction("actChooseCard", { cards: cardIds });
        });

        dojo.addClass("btn-discard", "disabled");

        this.addDangerActionButton("btn_pass", _("Pass"), () => {
          this.takeAction("pass");
        });
      },

      /**
       * evolution of OnEnteringStateHibernateGypaetus
       * @param args
       */
      onEnteringStateActChooseCard(args) {
        this.onEnteringStateHibernateGypaetus(args);
      },

      /**
       * deprecated, keeped only to avoid bugs
       * @param {*} args
       */
      onEnteringStateHibernateGypaetus(args) {
        args.playableCards.forEach((cardId) => {
          this.onClick(
            "card_" + cardId,
            (e) => {
              this.selectCardToDiscardGypaetus(e);
            },
            true
          );
        });

        this.addPrimaryActionButton("btn-discard", _("Send to Cave"), () => {
          const cardIds = this.buildJSONIds(".selected");
          this.takeAction("actChooseCard", { cards: cardIds });
        });

        dojo.addClass("btn-discard", "disabled");

        this.addDangerActionButton("btn_pass", _("Pass"), () => {
          this.takeAction("pass");
        });
      },

      onEnteringStateMulligan(args) {
        if (args._private.canMulligan) {
          this.addPrimaryActionButton("btn_mulligan", _("Change cards"), () => {
            this.takeAction("changeCards");
          });
        }
        this.addPrimaryActionButton("btn_pass", _("Pass"), () => {
          this.takeAction("passMulligan");
        });
      },

      onUpdateActivityMulligan(args, status) {
        if (!status) {
          this.clearActionButtons();
        }
      },

      onEnteringStatePlayAll(args) {
        debug("enteringPlayAll", args);
        this.overcost = args.overcost ? args.overcost : 0;
        args._private.playableCards.forEach((cardId) => {
          this.onClick(
            "card_" + cardId,
            (e) => {
              this.selectCardToPlay(e);
            },
            true
          );
        });
        this.addPrimaryActionButton("btn-pay", _("Play Card"), () => {
          this.onPressButtonPlayCard();
        });
        this.addDangerActionButton("btn_pass", _("Pass"), () => {
          this.takeAction("pass");
        });
        if (args._private.canUndo)
          this.addDangerActionButton("btn-undo", _("Cancel"), () => {
            this.takeAction("undo");
          });
        this.addSecondaryActionButton(
          "btn-sapling",
          _("Play as tree sapling"),
          () => {
            this.confirmationDialog(
              _("Are you sure you want to play this card face down?"),
              () => {
                const cardId = this.getCardIdFromDiv(
                  dojo.query("#cards .selected")[0]
                );
                this.takeAction("playCard", {
                  cardId: cardId,
                  position: "sapling",
                });
              }
            );
          }
        );
        dojo.addClass("btn-pay", "disabled");
        dojo.addClass("btn-sapling", "disabled");
      },

      onEnteringStateRetake(args) {
        args._private.takableCards.forEach((cardId) => {
          this.onClick(
            "card_" + cardId,
            (e) => {
              this.selectCardToTake(e);
            },
            true
          );
        });
        if (args._private.canTake)
          this.onClick(
            "deck",
            (e) => {
              this.selectDeck();
            },
            true
          );

        this.addPrimaryActionButton("btn-take", _("Take Card"), () => {
          this.onPressButtonTakeCard();
        });

        dojo.addClass("btn-take", "disabled");
      },

      onEnteringStatePlayerTurn(args) {
        args._private.playableCards.forEach((cardId) => {
          this.onClick(
            "card_" + cardId,
            (e) => {
              this.selectCardToPlay(e);
            },
            true
          );
        });
        args._private.takableCards.forEach((cardId) => {
          this.onClick(
            "card_" + cardId,
            (e) => {
              this.selectCardToTake(e);
            },
            true
          );
        });
        if (args._private.canTake)
          this.onClick(
            "deck",
            (e) => {
              this.selectDeck();
            },
            true
          );

        this.addPrimaryActionButton("btn-take", _("Take Card"), () => {
          this.onPressButtonTakeCard();
        });

        this.addPrimaryActionButton("btn-pay", _("Play Card"), () => {
          this.onPressButtonPlayCard();
        });
        this.addSecondaryActionButton(
          "btn-sapling",
          _("Play as tree sapling"),
          () => {
            this.confirmationDialog(
              _("Are you sure you want to play this card face down?"),
              () => {
                const cardId = this.getCardIdFromDiv(
                  dojo.query("#cards .selected")[0]
                );
                this.takeAction("playCard", {
                  cardId: cardId,
                  position: "sapling",
                });
              }
            );
          }
        );
        dojo.addClass("btn-pay", "disabled");
        dojo.addClass("btn-take", "disabled");
        dojo.addClass("btn-sapling", "disabled");
      },

      // onEnteringStateCall(args){
      //   this.moveCaller(args.caller.id);
      //   if (this.player_id != this.getActivePlayerId()) return;

      //   args.callablePlayers.forEach(player => {
      //     this.addPrimaryActionButton('btn_'+player.id, player.name, () => this.openCardsChoices(player));
      //     // $('btn_'+player.id).style.color = "#" + player.color; illisible
      //   });
      //   Object.entries(args.uncallableCards).forEach(([id, card]) => {
      //     dojo.query('#card_choice > [data-card-color="'+card.color+'"][data-card-value='+card.value+']').addClass('hidden');
      //   });
      // },

      // onLeavingStateCall(){
      //   dojo.query('.hidden').removeClass('hidden');
      // },

      /*
█████  █████ ███████████ █████ █████        █████████ 
░░███  ░░███ ░█░░░███░░░█░░███ ░░███        ███░░░░░███
░███   ░███ ░   ░███  ░  ░███  ░███       ░███    ░░░ 
░███   ░███     ░███     ░███  ░███       ░░█████████ 
░███   ░███     ░███     ░███  ░███        ░░░░░░░░███
░███   ░███     ░███     ░███  ░███      █ ███    ░███
░░████████      █████    █████ ███████████░░█████████ 
░░░░░░░░      ░░░░░    ░░░░░ ░░░░░░░░░░░  ░░░░░░░░░  
*/

      // onLeavingState: this method is called each time we are leaving a game state.
      //                 You can use this method to perform some user interface changes at this moment.
      //

      // onUpdateActionButtons: in this method you can manage "action buttons" that are displayed in the
      //                        action status bar (ie: the HTML links in the status bar).
      //

      displayCaution() {
        let text = _("Caution: only one winter card remaining!");
        dojo.place(
          '<div id="FOS_message">' + text + "</div>",
          "FOS_caution",
          "first"
        );
        dojo.connect($("FOS_caution"), "onclick", this, () => {
          dojo.destroy("FOS_message");
        });
      },

      buildJSONIds(selector) {
        return JSON.stringify(
          dojo.query(selector).map((card) => this.getCardIdFromDiv(card))
        );
      },

      getCardIdFromDiv(div) {
        return div.id.split("_")[1];
      },

      resetSelectionToPlay() {
        dojo.query("#cards .selected").removeClass("selected");
        dojo.query(".selectedToPay").removeClass("selectedToPay");
        dojo
          .query(".futurePlace.selectable")
          .removeClass("selectable selected");
        dojo
          .query("#FOStable_" + this.player_id + " .futurePlace")
          .attr("data-id", 0);
        dojo
          .query(".hare, .toad, .butterfly")
          .removeClass("selectable selected");
        this.cost = -1;

        this.updatePageTitle();
      },

      selectDeck() {
        dojo.toggleClass("deck", "selected");
        this.resetSelectionToPlay();
        dojo.query("#clearing .selected").removeClass("selected");

        if ($("deck").classList.contains("selected")) {
          $("pagemaintitletext").innerHTML = _("You can take a card from deck");
        } else {
          this.updatePageTitle();
        }
        this.updateButtons();
      },

      selectCardToDiscard(e) {
        dojo.toggleClass(e.currentTarget.id, "selected");
        if (dojo.query(".selected").length == 0)
          dojo.addClass("btn-discard", "disabled");
        else dojo.removeClass("btn-discard", "disabled");
      },

      selectCardToDiscardGypaetus(e, nb = 2) {
        dojo.toggleClass(e.currentTarget.id, "selected");
        if (
          dojo.query(".selected").length == 0 ||
          dojo.query(".selected").length > nb
        )
          dojo.addClass("btn-discard", "disabled");
        else dojo.removeClass("btn-discard", "disabled");
      },

      selectCardToTake(e) {
        this.resetSelectionToPlay();
        $("deck").classList.remove("selected");
        const cardDiv = e.currentTarget;
        const cardId = this.getCardIdFromDiv(cardDiv);

        //if card is already selected, unselect it and reset selection
        if (cardDiv.classList.contains("selected")) {
          cardDiv.classList.remove("selected");
          this.updatePageTitle();
        } else {
          dojo.query("#clearing .selected").removeClass("selected");
          cardDiv.classList.add("selected");
          $("pagemaintitletext").innerHTML = this.fsr(
            _("You can take ${card} card from clearing"),
            this.getTranslatableName(cardId)
          );
        }

        this.updateButtons();
      },

      getTranslatableName(cardId) {
        const species = CARDS_DATA[cardId]["species"];
        if (species.length == 2) {
          return {
            card: {
              log: _("${specie1} / ${specie2}"),
              args: {
                i18n: ["specie1", "specie2"],
                specie1: CARDS_DATA[cardId]["species"][0],
                specie2: CARDS_DATA[cardId]["species"][1],
              },
            },
            i18n: ["card"],
          };
        } else {
          return {
            card: CARDS_DATA[cardId]["species"][0],
            i18n: ["card"],
          };
        }
      },

      //triggered each time player click on a selectablecard
      selectCardToPlay(e, position = null) {
        debug("selectCardToPlay", e, position);
        //first unselect cards in clearing
        dojo.query("#clearing .selected, #deck").removeClass("selected");

        const cardDiv = e.currentTarget;
        const cardId = this.getCardIdFromDiv(cardDiv);

        //if card is already selected, unselect it and reset selection
        if (cardDiv.classList.contains("selected")) {
          this.resetSelectionToPlay();
        } else if (cardDiv.classList.contains("selectedToPay")) {
          //same with selectedToPay but only unselect it (no other consequences)
          cardDiv.classList.remove("selectedToPay");
        } else {
          //if another div is already selected, select the new one to PAY
          if (dojo.query("#cards .selected").length != 0) {
            cardDiv.classList.add("selectedToPay");
          } else {
            //ELSE select this new one
            cardDiv.classList.add("selected");
            const cardType = CARDS_DATA[cardId]["type"];
            // and start a new selection phase -> search a place and pay
            if (cardType == TREE) {
              this.setNewTitle(cardId, false);
            } else {
              //special hack for hare and toad and butterfly
              this.selectCardsWhereStack(cardId, position);

              //build query
              const queryStr = position
                ? position
                    .map(
                      (str) =>
                        "#FOStable_" +
                        this.player_id +
                        " .futurePlace." +
                        cardType +
                        "." +
                        str
                    )
                    .join(",")
                : "#FOStable_" + this.player_id + " .futurePlace." + cardType;

              dojo.query(queryStr).forEach((element) => {
                element.dataset.id = cardId;
                this.onClick(
                  element,
                  (e) => {
                    this.selectWhereToPlay(e);
                  },
                  true
                );
              });
              if (dojo.query(".selectable.futurePlace").length != 0)
                $("pagemaintitletext").innerHTML = _(
                  "Choose where to place this card"
                );
              else {
                $("pagemaintitletext").innerHTML = _(
                  "You can only place this card face down"
                );
              }
            }
          }
        }
        this.updateButtons();
      },

      selectWhereToPlay(e) {
        // debug("select where to play", e)
        const futurePlaceDiv = e.currentTarget;
        if (!futurePlaceDiv.classList.contains("selectable")) {
          debug("impossible, n'est plus selectable", e);
          return;
        }
        dojo
          .query(
            ".futurePlace.selectable, .toad.selectable, .hare.selectable, .butterfly.selectable"
          )
          .removeClass("selected");
        futurePlaceDiv.classList.add("selected");
        debug(dojo.query("#cards .selected")[0].id);
        const cardId = this.getCardIdFromDiv(dojo.query("#cards .selected")[0]);
        this.setNewTitle(
          cardId,
          futurePlaceDiv.classList.contains("onBottom") ||
            futurePlaceDiv.classList.contains("onRight")
        );
      },

      checkMatchingTreeSymbols(treeSymbol) {
        const selectedCards = dojo.query(".selectedToPay");
        return selectedCards.every((card) => {
          return CARDS_DATA[card.dataset.id]["tree_symbol"].includes(
            treeSymbol
          );
        });
      },

      setNewTitle(cardId, isRightOrBottom) {
        const index = isRightOrBottom ? 1 : 0;
        const specie = this.getDataFromSpecie(
          CARDS_DATA[cardId]["species"][index]
        );

        let hasBonus = false;

        if (
          this.gamedatas.gamestate.name == "freePlay" ||
          this.gamedatas.gamestate.name == "freePlayAll"
        )
          this.cost = 0;
        else {
          // debug("species", specie);
          this.cost = specie.cost + (this.overcost ?? 0);
          hasBonus = specie.bonus != "";
          this.treeSymbol = CARDS_DATA[cardId]["tree_symbol"][index];
        }

        $("pagemaintitletext").innerHTML = this.fsr(
          _("To place ${card}, you need to pay ${nb} card(s) ${bonus}"),
          {
            card: specie.name,
            nb: this.cost,
            bonus: {
              log: hasBonus ? _("(bonus: ${valid} )") : "",
              args: {
                valid: '<i id="bonus-icon" class="fa6 fa6-square-xmark"></i>',
              },
            },
            i18n: ["card", "bonus"],
          }
        );

        if (hasBonus) {
          this.addTooltip(
            "bonus-icon",
            _("To activate bonus, you need to pay with matching tree symbol"),
            ""
          );
        }
        this.updateButtons();
      },

      updateButtons() {
        //if a card have been selected in clearing activate button take
        if ($("btn-take") != null) {
          if (
            dojo.query("#clearing .selected").length == 1 ||
            $("deck").classList.contains("selected")
          ) {
            dojo.removeClass("btn-take", "disabled");
          } else {
            // else deactivate button take and check if button pay must be activated
            dojo.addClass("btn-take", "disabled");
          }
        }

        if ($("btn-pay") != null) {
          if (dojo.query(".selectedToPay").length != this.cost) {
            dojo.addClass("btn-pay", "disabled");
            if ($("bonus-icon") != null) {
              dojo.removeClass("bonus-icon", "fa6-square-check");
              dojo.addClass("bonus-icon", "fa6-square-xmark");
            }
          } else {
            dojo.removeClass("btn-pay", "disabled");
            if ($("bonus-icon") != null) {
              if (this.checkMatchingTreeSymbols(this.treeSymbol)) {
                dojo.addClass("bonus-icon", "fa6-square-check");
                dojo.removeClass("bonus-icon", "fa6-square-xmark");
              } else {
                dojo.removeClass("bonus-icon", "fa6-square-check");
                dojo.addClass("bonus-icon", "fa6-square-xmark");
              }
            }
          }
        }

        if ($("btn-sapling") != null) {
          if (
            dojo.query("#cards .selected").length != 0 &&
            dojo.query(".selectedToPay").length == 0 &&
            dojo.query(".futurePlace.selected").length == 0 &&
            dojo.query(".treeContainer .card.selected").length == 0
          )
            dojo.removeClass("btn-sapling", "disabled");
          else dojo.addClass("btn-sapling", "disabled");
        }
      },

      selectCardsWhereStack(cardId, position) {
        const species = CARDS_DATA[cardId]["species"];
        if (
          this.getDataFromSpecie(species[0]).tags.includes(BUTTERFLY) &&
          (position == "onTop" ||
            !position ||
            (Array.isArray(position) && position.includes("onTop")))
        ) {
          dojo
            .query(
              "#FOStable_" + this.player_id + " .card.butterfly.onTop.available"
            )
            .forEach((element) => {
              this.onClick(
                element,
                (e) => {
                  this.selectWhereToPlay(e);
                },
                true
              );
            });
        }
        if (
          species[1] == COMMON_TOAD &&
          (position == "onBottom" ||
            !position ||
            (Array.isArray(position) && position.includes("onBottom")))
        ) {
          dojo
            .query(
              "#FOStable_" + this.player_id + " .card.toad.onBottom:not(.busy)"
            )
            .forEach((element) => {
              this.onClick(
                element,
                (e) => {
                  this.selectWhereToPlay(e);
                },
                true
              );
            });
        }
        if (
          species[0] == EUROPEAN_HARE &&
          (position == "onLeft" ||
            position == null ||
            (Array.isArray(position) && position.includes("onLeft")))
        ) {
          dojo
            .query("#FOStable_" + this.player_id + " .card.hare.onLeft")
            .forEach((element) => {
              // element.dataset.id = cardId; -> wrong solution
              this.onClick(
                element,
                (e) => {
                  this.selectWhereToPlay(e);
                },
                true
              );
            });
        }
        if (
          species[1] == EUROPEAN_HARE &&
          (position == "onRight" ||
            !position ||
            (Array.isArray(position) && position.includes("onRight")))
        ) {
          dojo
            .query("#FOStable_" + this.player_id + " .card.hare.onRight")
            .forEach((element) => {
              this.onClick(
                element,
                (e) => {
                  this.selectWhereToPlay(e);
                },
                true
              );
            });
        }
      },

      getDataFromSpecie(specie) {
        // debug(
        //   "getDataFromSpecie",
        //   SPECIES_DATA[specie.replace(/[()-\s']/g, "")]
        // );
        return SPECIES_DATA[specie.replace(/[()-\s']/g, "")];
      },

      createStack(cardId, specie, playerId, treeId, position) {
        let specieClass =
          specie == COMMON_TOAD
            ? "toad"
            : specie == EUROPEAN_HARE
            ? "hare"
            : specie == URTICA
            ? "urtica"
            : "butterfly";

        dojo.query("#card_" + cardId).addClass(specieClass);

        //determine if butterfly/urtica need a counter
        if (specieClass == "urtica") {
          const butterfly = dojo.query(
            "#tree_" + playerId + "_" + treeId + " .butterfly"
          );
          if (butterfly.length > 0) {
            butterfly.addClass("available");
            //urtica has been just played, add counter on butterfly
            this.counters[playerId + "_" + treeId + "_onTop"] =
              this.addCounterOnDeck(butterfly[0].id, butterfly.length);
            return;
          } else {
            return;
          }
        } else if (specieClass == "butterfly") {
          const urtica = dojo.query(
            "#tree_" + playerId + "_" + treeId + " .urtica"
          );
          if (urtica.length > 0) {
            dojo.query("#card_" + cardId).addClass("available");
          } else {
            return;
          }
        }

        //if it's the first card played here add a counter and available class
        if (
          dojo.query(
            "#tree_" + playerId + "_" + treeId + " .futurePlace." + position
          ).length == 1
        ) {
          this.counters[playerId + "_" + treeId + "_" + position] =
            this.addCounterOnDeck("card_" + cardId, 1);
        } else {
          this.counters[playerId + "_" + treeId + "_" + position].incValue(1);
          dojo
            .query(
              "#tree_" +
                playerId +
                "_" +
                treeId +
                " ." +
                specieClass +
                ".card[data-tree-id=" +
                treeId +
                "]"
            )
            .addClass("busy");
        }
      },

      /*
 █████████             █████     ███                             
███░░░░░███           ░░███     ░░░                              
░███    ░███   ██████  ███████   ████   ██████  ████████    █████ 
░███████████  ███░░███░░░███░   ░░███  ███░░███░░███░░███  ███░░  
░███░░░░░███ ░███ ░░░   ░███     ░███ ░███ ░███ ░███ ░███ ░░█████ 
░███    ░███ ░███  ███  ░███ ███ ░███ ░███ ░███ ░███ ░███  ░░░░███
█████   █████░░██████   ░░█████  █████░░██████  ████ █████ ██████ 
░░░░░   ░░░░░  ░░░░░░     ░░░░░  ░░░░░  ░░░░░░  ░░░░ ░░░░░ ░░░░░░  
                                                                 
                                                                 
                                                                 
*/

      // onTickCell(e){
      //   debug("onTickCell", e)
      //   const divId = e.currentTarget.id;
      //   if (!$(divId).classList.contains('clickable')) return false;
      //   this.takeAction('tick', {
      //     'cell' : divId
      //   });

      // },
      onPressButtonPlayCard() {
        const cardId = this.getCardIdFromDiv(dojo.query("#cards .selected")[0]);
        const cardsIds = this.buildJSONIds("#cards .selectedToPay");
        const position = dojo.query(
          ".futurePlace.selected, .toad.selected, .hare.selected, .available.selected"
        )[0]?.dataset
          ? dojo.query(
              ".futurePlace.selected, .toad.selected, .hare.selected, .available.selected"
            )[0].dataset.position
          : TREE;
        const treeId = dojo.query(
          ".futurePlace.selected, .toad.selected, .hare.selected, .available.selected"
        )[0]?.dataset.treeId;
        this.takeAction("playCard", {
          cardId: cardId,
          cards: cardsIds,
          treeId: treeId,
          position: position,
        });
      },

      onPressButtonTakeCard() {
        if ($("deck").classList.contains("selected")) {
          this.takeAction("takeCard");
        } else {
          const cardId = this.getCardIdFromDiv(
            dojo.query("#clearing .selected")[0]
          );

          this.takeAction("takeCard", {
            cardId: cardId,
          });
        }
      },

      checkDraftChoices(args, draftSelection) {
        //first validate each location
        draftSelection.forEach((elem) => {
          if (dojo.query(".card." + elem).length == args["n" + ucFirst(elem)]) {
            dojo.query("#hint_" + elem).addClass("validated");
          } else {
            dojo.query("#hint_" + elem).removeClass("validated");
          }
        });

        //then disable buttons
        this.activateDraftButtons();
      },

      activateDraftButtons() {
        if ($("btn_draft") == null) return;

        if (this.isCurrentPlayerActive()) {
          if (
            dojo
              .query("#forestShuffle-choose-card-footer div")
              .every((div) => div.classList.contains("validated"))
          ) {
            dojo.removeClass("btn_draft", "disabled");
          } else {
            dojo.addClass("btn_draft", "disabled");
          }
          dojo.addClass("btn_reset", "disabled");
        } else {
          dojo.addClass("btn_draft", "disabled");
          dojo.removeClass("btn_reset", "disabled");
        }
      },

      /*
██████   █████    ███████    ███████████ █████ ███████████  █████████ 
░░██████ ░░███   ███░░░░░███ ░█░░░███░░░█░░███ ░░███░░░░░░█ ███░░░░░███
░███░███ ░███  ███     ░░███░   ░███  ░  ░███  ░███   █ ░ ░███    ░░░ 
░███░░███░███ ░███      ░███    ░███     ░███  ░███████   ░░█████████ 
░███ ░░██████ ░███      ░███    ░███     ░███  ░███░░░█    ░░░░░░░░███
░███  ░░█████ ░░███     ███     ░███     ░███  ░███  ░     ███    ░███
█████  ░░█████ ░░░███████░      █████    █████ █████      ░░█████████ 
░░░░░    ░░░░░    ░░░░░░░       ░░░░░    ░░░░░ ░░░░░        ░░░░░░░░░  
                                                                                                         
*/

      notif_clearClearing(n) {
        debug("notif_clearClearing", n);
        this.clearClearing();
        this.counters["clearing"].toValue(0);
      },

      notif_hibernate(n) {
        debug("notif_Hibernate", n);
        this.counters["hands"][n.args.player_id].incValue(-n.args.nb);
        this.counters["caves"][n.args.player_id].incValue(n.args.nb);
        for (let index = 0; index < n.args.nb; index++) {
          this.pickFromDeck(n.args.player_id);
        }
      },

      notif_hibernateGypaetus(n) {
        debug("notif_HibernateGypaetus", n);
        this.clearClearingToCave(n.args.player_id, n.args.cards);
        this.counters["clearing"].incValue(-n.args.nb);
      },

      notif_myHibernate(n) {
        debug("notif_myHibernate", n);
        n.args.cards.forEach((cardId) => {
          this.slideToObjectAndDestroy(
            "card_" + cardId,
            "overall_player_board_" + this.player_id,
            500
          );
        });
        n.args.newCards.forEach((card) => {
          this.pickFromDeck(this.player_id, card.id);
        });
        this.counters["hands"][n.args.player_id].incValue(-n.args.nb);
        this.counters["caves"][n.args.player_id].incValue(n.args.nb);
      },

      notif_mulligan(n) {
        debug("notif_mulligan", n);
        if (n.args.player_id) {
          this.counters["hands"][n.args.player_id].toValue(0);
          for (let index = 0; index < 6; index++) {
            this.pickFromDeck(n.args.player_id);
          }
        } else {
          n.args.cards.forEach((card) => {
            this.pickFromDeck(this.player_id, card);
          });
          this.trashAllCards();
          this.counters["hands"][this.player_id].toValue(0);
        }
      },

      notif_newCardOnClearing(n) {
        debug("notif_newCardOnClearing", n);
        this.pickFromDeck("clearing", n.args.cardId);
        this.counters["clearing"].incValue(1);
      },

      notif_newScores(n) {
        debug("notif_newScores", n);
        for (const playerId in n.args.scores) {
          this.scoreCtrl[playerId].toValue(n.args.scores[playerId]);
        }
        for (const cardId in n.args.scoresByCards) {
          //display score help
          this.createTooltip(cardId, n.args.scoresByCards[cardId]);
        }
      },

      notif_newWinterCard(n) {
        debug("notif_newWinterCard", n);
        this.pickFromDeck("wCard", n.args.wCardId);
        this.counters["winterCard"].incValue(1);
        if (this.counters["winterCard"].getValue() == 2) {
          this.displayCaution();
        }
      },

      notif_playCard(n) {
        debug("notif_playCard", n);
        this.addCardToTable(
          n.args.cardId,
          n.args.player_id,
          n.args.treeId,
          n.args.position
        );
        n.args.cards.forEach((cardId) => {
          this.addCardToClearing(cardId, n.args.player_id);
          this.counters["clearing"].incValue(1);
        });
      },

      notif_putCardOnClearing(n) {
        debug("notif_putCardOnClearing", n);
        // debugger;
        this.createCardInClearing(n.args.cardId);
        this.counters["clearing"].incValue(1);
      },

      notif_receiveCards(n) {
        debug("notif_receiveCards", n);
        n.args.cards.forEach((cardId) => {
          this.receiveCard(cardId, n.args.player_id ?? this.player_id);
          this.forEachPlayer((player) =>
            this.counters["hands"][player.id].incValue(1)
          );
        });
      },

      notif_refreshCounters(n) {
        debug("notif refreshCounters", n);
        this.counters["deck"].toValue(n.args.cards.deck_count);
        this.counters["discard"].toValue(n.args.cards.discard_count);
        this.counters["clearing"].toValue(
          Object.values(n.args.cards.clearing).length
        );
        this.forEachPlayer((player) => {
          this.counters["hands"][player.id].toValue(
            n.args.players[player.id]["hand"]
          );
        });
      },

      notif_takeAllClearing(n) {
        debug("notif_takeAllClearing", n);
        this.clearClearingToCave(n.args.player_id, n.args.cards);
        this.counters["clearing"].incValue(-n.args.nb);
      },

      notif_takeCardFromClearing(n) {
        debug("notif_takeCardFromClearing", n);
        this.pickFromClearing(n.args.cardId, n.args.player_id);
        this.counters["clearing"].incValue(-1);
      },

      notif_takeCardFromDeck(n) {
        debug("notif_takeCardFromDeck", n);
        this.pickFromDeck(n.args.player_id ?? this.player_id, n.args.cardId);
      },

      notif_undo(n) {
        debug("notif_undo", n);
        let treesToDestroy = [];
        Object.values(n.args.cards).forEach((id) => {
          const card = dojo.query("#card_" + id);
          const treeId = card[0].dataset.treeId;
          const position = card[0].dataset.position;

          if (position == TREE || position == "sapling") {
            treesToDestroy.push(card[0].parentElement.id);
          }

          this.pickFromTable(id, n.args.player_id);
          dojo.removeClass(
            "card_" + id,
            "onTop onBottom onLeft onRight sapling hare toad butterfly available ready busy"
          );

          //remove counter if needed
          if (this.counters[n.args.player_id + "_" + treeId + "_" + position]) {
            delete this.counters[
              n.args.player_id + "_" + treeId + "_" + position
            ];
          }
          if ($("card_" + id + "_deckinfo")) {
            dojo.destroy("card_" + id + "_deckinfo");
          }

          dojo
            .query(
              "#tree_" +
                n.args.player_id +
                "_" +
                treeId +
                " :not([id*=card])." +
                position
            )
            .addClass("futurePlace");

          card[0].dataset.position = "";
          card[0].dataset.treeId = "";
        });

        treesToDestroy.forEach((tree) => {
          this.smartDestroy(tree);
        });

        this.counters["clearing"].toValue(dojo.query("#clearing .card").length);
        this.clearPossible();

        //reinit tooltip
        dojo.query("#cards .card").forEach((card) => {
          this.createTooltip(card.dataset.id);
          debug(card);
        });
      },

      /*
██████   ██████    ███████    █████   █████ ██████████  █████████ 
░░██████ ██████   ███░░░░░███ ░░███   ░░███ ░░███░░░░░█ ███░░░░░███
░███░█████░███  ███     ░░███ ░███    ░███  ░███  █ ░ ░███    ░░░ 
░███░░███ ░███ ░███      ░███ ░███    ░███  ░██████   ░░█████████ 
░███ ░░░  ░███ ░███      ░███ ░░███   ███   ░███░░█    ░░░░░░░░███
░███      ░███ ░░███     ███   ░░░█████░    ░███ ░   █ ███    ░███
█████     █████ ░░░███████░      ░░███      ██████████░░█████████ 
░░░░░     ░░░░░    ░░░░░░░         ░░░      ░░░░░░░░░░  ░░░░░░░░░  
*/

      createCardInHand(cardId) {
        this.createCard(cardId, "cards");
      },

      createCardInClearing(cardId) {
        this.createCard(cardId, "clearing");
      },

      createCard(cardId, containerId) {
        this.place("card_tpl", cardId, containerId);
        this.createTooltip(cardId);
      },

      receiveCard(cardId, fromPlayerId) {
        // debugger;
        this.createCard(cardId, "overall_player_board_" + fromPlayerId);
        this.genericMove("card_" + cardId, "cards");
      },

      pickFromTable(cardId, toPlayerId = null) {
        debug("pickFromTable", cardId, toPlayerId);
        if (toPlayerId != this.player_id) {
          this.slideToObjectAndDestroy(
            "card_" + cardId,
            "overall_player_board_" + toPlayerId,
            500
          );
          this.wait(500).then(() => {
            this.smartDestroy("card_" + cardId);
          });
        } else {
          this.genericMove("card_" + cardId, "cards");
        }
        //Increase cardcounter
        this.counters["hands"][toPlayerId].incValue(1);
      },

      pickFromClearing(cardId, toPlayerId = null) {
        debug("pickFromClearing", cardId, toPlayerId);
        if (toPlayerId != this.player_id) {
          this.slideToObjectAndDestroy(
            "card_" + cardId,
            "player_name_" + toPlayerId,
            300,
            0
          );
          setTimeout(() => {
            this.smartDestroy("card_" + cardId);
          }, 300);
        } else this.genericMove("card_" + cardId, "cards");
        //Increase cardcounter
        this.counters["hands"][toPlayerId].incValue(1);
      },

      clearClearingToCave(playerId, cards) {
        for (let index = 0; index < cards.length; index++) {
          const card = $("card_" + cards[index]);
          const newId = "item_" + index;
          this.flip(card, newId).then(() => {
            this.slideToObjectAndDestroy(
              "card_" + newId,
              "player_name_" + playerId,
              300,
              0
            );
            setTimeout(() => {
              this.smartDestroy("card_" + newId);
              this.counters["caves"][playerId].incValue(1);
            }, 300);
          });
        }
      },

      clearClearing() {
        const cards = dojo.query("#clearing > .card");
        for (let index = 0; index < cards.length; index++) {
          const card = cards[index];
          const newId = "item_" + index;
          this.flip(card.id, newId)
            .then(() => {
              this.genericMove("card_" + newId, "discard", false, null, () => {
                this.smartDestroy("card_" + newId);
              });
            })
            .then(() => {
              this.counters["discard"].incValue(1);
              $("discard").classList.remove("empty");
            });
        }
      },

      flip(previous, newCardId = null) {
        const new_item = this.card_tpl(newCardId);

        return this.flipAndReplace(previous, new_item, 400).then(() => {
          if (!isNaN(newCardId)) {
            this.createTooltip(newCardId);
            // this.addCustomTooltip("card_"+newCardId, this.getCardHelpDiv(newCardId));
          }
        });
      },

      addCardToClearing(cardId, playerId = null) {
        if (this.player_id == playerId && $("card_" + cardId) != null) {
          debug("addCardToClearing", cardId, playerId);
          //just move card form hand to clearing with generic move
          this.genericMove("card_" + cardId, "clearing", false);
        } else {
          // create a card on overall player panel then move it
          dojo.place(
            this.card_tpl(cardId),
            $("overall_player_board_" + playerId),
            "first"
          );

          this.createTooltip(cardId);
          // this.addCustomTooltip("card_"+cardId, this.getCardHelpDiv(cardId));
          this.genericMove("card_" + cardId, "clearing", false);
        }
        if (playerId) {
          this.counters["hands"][playerId].incValue(-1);
        }
      },

      /*
       * move a card from a player hand to his table
       */
      addCardToTable(cardId, playerId, treeId, position = "", bCount = true) {
        //create a tree placeHolder if needed
        if ($("tree_" + playerId + "_" + treeId) == null) {
          this.place(
            "tree_tpl",
            {
              treeId: treeId,
              playerId: playerId,
              cardId: position == "sapling" ? 0 : cardId,
            },
            "FOStable_" + playerId
          );
        }
        if (this.player_id == playerId && $("card_" + cardId) != null) {
          //just move card form hand to table with generic move
          dojo
            .query("#card_" + cardId)
            .addClass(position)
            .attr("data-position", position)
            .attr("data-tree-id", treeId);
        } else {
          // create a card on overall player panel then move it
          dojo.place(
            this.card_tpl(cardId, position),
            $("overall_player_board_" + playerId),
            "first"
          );
          dojo
            .query("#card_" + cardId)
            .attr("data-position", position)
            .attr("data-tree-id", treeId);
        }
        this.genericMove(
          "card_" + cardId,
          "tree_" + playerId + "_" + treeId,
          false,
          "first",
          this.setReady
        );

        if (position != "sapling") {
          this.createTooltip(cardId, this.gamedatas.scoresByCards[cardId]);
          //this.addCustomTooltip("card_"+cardId, this.getCardHelpDiv(cardId, position, this.gamedatas.scoresByCards[cardId]));
          //add Hare or TOAD or butterfly/urtica class
          const specieId =
            position == "onBottom" || position == "onRight" ? 1 : 0;
          const specie = CARDS_DATA[cardId]["species"][specieId];
          // debug(specie);
          if (
            (specie == COMMON_TOAD ||
              specie == EUROPEAN_HARE ||
              specie == URTICA ||
              this.getDataFromSpecie(specie).tags.includes(BUTTERFLY)) &&
            position != "sapling"
          ) {
            this.createStack(cardId, specie, playerId, treeId, position);
          }
        }

        //remove placeholder
        if (position != "") {
          dojo
            .query(
              "#tree_" + playerId + "_" + treeId + " .futurePlace." + position
            )
            .removeClass("futurePlace");
        }

        //decrease player hand counter
        if (bCount) this.counters["hands"][playerId].incValue(-1);
      },

      /**
       * Pick a card in deck for another player, the clearing or hand of this.player.id
       * @param {playerId or 'clearing'} to
       * @param {cardId or null} cardId
       */
      pickFromDeck(to, cardId = null) {
        let index = 0;
        while ($("card_item_" + index) != null) {
          index++;
        }

        const item = dojo.place(this.card_tpl("item_" + index), "deck");
        if (cardId == null) {
          //pick for another player
          this.slideToObjectAndDestroy(
            item.id,
            "overall_player_board_" + to,
            500
          );
          //Increase cardcounter
          this.counters["hands"][to].incValue(1);
        } else {
          //reveal and move
          this.flip(item.id, cardId).then(() => {
            if (to == this.player_id) {
              //move in hand
              this.genericMove("card_" + cardId, "cards", false);
              //Increase cardcounter
              this.counters["hands"][to].incValue(1);
            } else {
              //move on clearing
              if (to == "clearing")
                this.genericMove("card_" + cardId, "clearing");
              //move wCard
              if (to == "wCard")
                this.showCard("card_" + cardId, true, "wCardStorage");
              // this.genericMove('card_' + cardId, 'wCard', false, null, () => {
              //   setTimeout(() => {
              //     this.smartDestroy('card_' + cardId);
              //   }, 300);
              // });
            }
          });
        }
        this.counters["deck"].incValue(-1);
      },

      setReady(el) {
        el.classList.add("animate-on-transforms");
        el.classList.add("ready");
        el.addEventListener("transitionend", () => {
          el.classList.remove("animate-on-transforms");
          // el.removeEventListener('transitionend');
        });
      },

      trashAllCards() {
        let delay = 0;
        dojo.query("#cards > .card").forEach((card) => {
          this.slideToObjectAndDestroy(card.id, "topbar", 500, delay);
          delay = delay + 200;
        });
      },

      // moveCard(cardId, fromPlayerId, toPlayerId=null, fromHand = true){
      //   const toDiv = toPlayerId ? this.getDestinationDiv(cardId, toPlayerId) : 'discard' ;

      //   if (fromHand) this.cardsCounters[fromPlayerId].incValue(-1);

      //   //move from visible hand to visible table
      //   if (this.player_id != fromPlayerId && fromHand){
      //     debug("flipandreplace launched with ", dojo.query('#hand_'+fromPlayerId+' > .card')[0]);
      //     this.flipAndReplace(dojo.query('#hand_'+fromPlayerId+' > .card')[0], this.card_tpl(cardId), 500)
      //     .then(()=> {
      //       const elemId = 'card_'+cardId;
      //     //the card will leave a hand, no need of margin right
      //     $(elemId).style.marginRight = 0;

      //     if (toDiv=='discard') this.moveToDiscard(elemId);
      //     else this.genericMove(elemId, toDiv);
      //     });
      //   } else {
      //     const elemId = 'card_'+cardId;
      //     //the card will leave a hand, no need of margin right
      //     $(elemId).style.marginRight = 0;

      //     if (toDiv=='discard') this.moveToDiscard(elemId);
      //     else this.genericMove(elemId, toDiv);
      //   }

      // },

      /*
███████████ ██████████ ██████   ██████ ███████████  █████         █████████   ███████████ ██████████  █████████ 
░█░░░███░░░█░░███░░░░░█░░██████ ██████ ░░███░░░░░███░░███         ███░░░░░███ ░█░░░███░░░█░░███░░░░░█ ███░░░░░███
░   ░███  ░  ░███  █ ░  ░███░█████░███  ░███    ░███ ░███        ░███    ░███ ░   ░███  ░  ░███  █ ░ ░███    ░░░ 
 ░███     ░██████    ░███░░███ ░███  ░██████████  ░███        ░███████████     ░███     ░██████   ░░█████████ 
 ░███     ░███░░█    ░███ ░░░  ░███  ░███░░░░░░   ░███        ░███░░░░░███     ░███     ░███░░█    ░░░░░░░░███
 ░███     ░███ ░   █ ░███      ░███  ░███         ░███      █ ░███    ░███     ░███     ░███ ░   █ ███    ░███
 █████    ██████████ █████     █████ █████        ███████████ █████   █████    █████    ██████████░░█████████ 
░░░░░    ░░░░░░░░░░ ░░░░░     ░░░░░ ░░░░░        ░░░░░░░░░░░ ░░░░░   ░░░░░    ░░░░░    ░░░░░░░░░░  ░░░░░░░░░  
                                                                                                              
                                                                                                              
                                                                                                              
     */

      card_tpl(cardId, cssClass = "") {
        // debug("card_tpl", cardId, cssClass);

        if (!cardId)
          return `<div id="card_0" data-id="0" class="card back"></div>`;
        if (isNaN(cardId))
          return `<div id="card_${cardId}" data-id="0" class="card back"></div>`;
        const card = CARDS_DATA[cardId];
        // debug("j'affiche : ", card);
        return `<div id="card_${cardId}" data-id="${cardId}" data-position='' class="card ${card.type} ${card.deck} ${cssClass}"></div>`;
      },

      table_tpl(player) {
        return `<div id='FOStable_${player.id}' class="whiteblock">
      <div id='title_${player.id}'>
      ${this.format_string_recursive("${player_name}", {
        player_name: player.name,
      })}
      </div>
  </div>`;
      },

      tree_tpl(data) {
        return `<div id='tree_${data.playerId}_${data.treeId}' data-id="${data.cardId}" class='treeContainer'>
  <div data-tree-id="${data.treeId}" data-id="0" data-position='onTop' class='vCard onTop ready futurePlace'></div>
  <div data-tree-id="${data.treeId}" data-id="0" data-position='onRight' class='hCard onRight ready futurePlace'></div>
  <div data-tree-id="${data.treeId}" data-id="0" data-position='onBottom' class='vCard onBottom ready futurePlace'></div>
  <div data-tree-id="${data.treeId}" data-id="0" data-position='onLeft' class='hCard onLeft ready futurePlace'></div>
  </div>`;
      },

      winter_tpl(winterCards) {
        const text = _("Winter Cards :");
        const initialValue = window.localStorage?.getItem("FOS_zoom") ?? 100;
        return `<div class='player-board' id="player_board_config">
  <div class="player_config_row">
   <div id="help-mode-switch">
     <input type="checkbox" class="checkbox" id="help-mode-chk" />
     <label class="label" for="help-mode-chk">
       <div class="ball"></div>
     </label>

     <svg aria-hidden="true" focusable="false" data-prefix="fad" data-icon="question-circle" class="svg-inline--fa fa-question-circle fa-w-16" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><g class="fa-group"><path class="fa-secondary" fill="currentColor" d="M256 8C119 8 8 119.08 8 256s111 248 248 248 248-111 248-248S393 8 256 8zm0 422a46 46 0 1 1 46-46 46.05 46.05 0 0 1-46 46zm40-131.33V300a12 12 0 0 1-12 12h-56a12 12 0 0 1-12-12v-4c0-41.06 31.13-57.47 54.65-70.66 20.17-11.31 32.54-19 32.54-34 0-19.82-25.27-33-45.7-33-27.19 0-39.44 13.14-57.3 35.79a12 12 0 0 1-16.67 2.13L148.82 170a12 12 0 0 1-2.71-16.26C173.4 113 208.16 90 262.66 90c56.34 0 116.53 44 116.53 102 0 77-83.19 78.21-83.19 106.67z" opacity="0.4"></path><path class="fa-primary" fill="currentColor" d="M256 338a46 46 0 1 0 46 46 46 46 0 0 0-46-46zm6.66-248c-54.5 0-89.26 23-116.55 63.76a12 12 0 0 0 2.71 16.24l34.7 26.31a12 12 0 0 0 16.67-2.13c17.86-22.65 30.11-35.79 57.3-35.79 20.43 0 45.7 13.14 45.7 33 0 15-12.37 22.66-32.54 34C247.13 238.53 216 254.94 216 296v4a12 12 0 0 0 12 12h56a12 12 0 0 0 12-12v-1.33c0-28.46 83.19-29.67 83.19-106.67 0-58-60.19-102-116.53-102z"></path></g></svg>
   </div>
   <div>
   <svg xmlns="http://www.w3.org/2000/svg" height="1em" viewBox="0 0 512 512"><!--! Font Awesome Free 6.4.2 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license (Commercial License) Copyright 2023 Fonticons, Inc. --><path d="M416 208c0 45.9-14.9 88.3-40 122.7L502.6 457.4c12.5 12.5 12.5 32.8 0 45.3s-32.8 12.5-45.3 0L330.7 376c-34.4 25.2-76.8 40-122.7 40C93.1 416 0 322.9 0 208S93.1 0 208 0S416 93.1 416 208zM136 184c-13.3 0-24 10.7-24 24s10.7 24 24 24H280c13.3 0 24-10.7 24-24s-10.7-24-24-24H136z"/></svg>
   <input type="range" min="50" max="200" value="${initialValue}" class="slider" id="zoom_value">
    <svg xmlns="http://www.w3.org/2000/svg" height="1em" viewBox="0 0 512 512"><!--! Font Awesome Free 6.4.2 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license (Commercial License) Copyright 2023 Fonticons, Inc. --><path d="M416 208c0 45.9-14.9 88.3-40 122.7L502.6 457.4c12.5 12.5 12.5 32.8 0 45.3s-32.8 12.5-45.3 0L330.7 376c-34.4 25.2-76.8 40-122.7 40C93.1 416 0 322.9 0 208S93.1 0 208 0S416 93.1 416 208zM184 296c0 13.3 10.7 24 24 24s24-10.7 24-24V232h64c13.3 0 24-10.7 24-24s-10.7-24-24-24H232V120c0-13.3-10.7-24-24-24s-24 10.7-24 24v64H120c-13.3 0-24 10.7-24 24s10.7 24 24 24h64v64z"/></svg>
   </div>
   </div>
  <div id="wCard" class="winterCards">${text} <span id="counter-wCard">${winterCards.length}</span>/3
  <div id="wCardStorage"></div></div>
  </div>`;
      },

      //create tooltipable zones (if needed) and add curtomtooltip
      createTooltip(cardId, score = -1) {
        if (this.isFastMode()) return;
        // debug(cardId);
        const card = CARDS_DATA[cardId];
        //determine card type
        if ($("hint1_card_" + cardId) == null) {
          const hint =
            card.type == TREE || card.type == W_CARD
              ? `<div id="hint1_card_${cardId}" class="hint"></div>`
              : card.type == V_CARD
              ? `<div id="hint1_card_${cardId}" class="hint onTop"></div>
                <div id="hint2_card_${cardId}" class="hint onBottom"></div>`
              : `<div id="hint1_card_${cardId}" class="hint onLeft"></div>
                <div id="hint2_card_${cardId}" class="hint onRight"></div>`;

          dojo.place(hint, "card_" + cardId);
        }

        if (card.type == TREE) {
          this.addCustomTooltip(
            "hint1_card_" + cardId,
            this.getCardHelpDiv(cardId, TREE, score)
          );
        } else if (card.type == W_CARD) {
          this.addCustomTooltip(
            "hint1_card_" + cardId,
            this.getCardHelpDiv(cardId, "", score)
          );
        } else if (card.type == V_CARD) {
          this.addCustomTooltip(
            "hint1_card_" + cardId,
            this.getCardHelpDiv(cardId, "onTop", score)
          );
          this.addCustomTooltip(
            "hint2_card_" + cardId,
            this.getCardHelpDiv(cardId, "onBottom", score)
          );
        } else {
          this.addCustomTooltip(
            "hint1_card_" + cardId,
            this.getCardHelpDiv(cardId, "onLeft", score)
          );
          this.addCustomTooltip(
            "hint2_card_" + cardId,
            this.getCardHelpDiv(cardId, "onRight", score)
          );
        }
      },

      smartDestroy(id) {
        debug("smartDestroy", id);
        delete this.tooltips["hint1_" + id];
        delete this.tooltips["hint2_" + id];
        dojo.destroy(id);
      },

      getCardHelpDiv(cardId, position = "", score = -1) {
        // debug('getCardHelpDiv', cardId, position, score);
        if (!isNaN(cardId) && position == "")
          position = $("card_" + cardId).dataset.position;
        if (!position) return this.card_tpl(cardId, "superCard");

        const idCard = this.getIdCard(cardId, position, score);

        //if there are several hares or toad on the same slot put all them in the tooltip
        let card = "";
        if (
          score > 0 &&
          (dojo.hasClass("card_" + cardId, "hare") ||
            dojo.hasClass("card_" + cardId, "toad") ||
            dojo.hasClass("card_" + cardId, "available"))
        ) {
          let target = dojo.query("#card_" + cardId);
          let targets = dojo.query(
            "#" +
              target[0].parentElement.id +
              " .card[data-position=" +
              target[0].dataset.position +
              "][data-tree-id=" +
              target[0].dataset.treeId +
              "]"
          );
          let targetIds = targets.map((div) => div.dataset.id);
          // if (targetIds.length>1) debug(cardId, targetIds);
          for (let index = 0; index < targetIds.length; index++) {
            card += this.card_tpl(targetIds[index], "superCard " + position);
          }
        } else card = this.card_tpl(cardId, "superCard " + position);

        return `<div class="tooltip_container">${card}<div id="hint_${cardId}" class="hintText">
  ${idCard}   
  </div>
  </div>`;
      },

      getIdCard(cardId, position, score) {
        //determine type of played card
        const specieId =
          position == "onBottom" || position == "onRight" ? 1 : 0;
        const specie = CARDS_DATA[cardId]["species"][specieId];
        //exclude wintercard
        if (!specie) return "";
        const specieData = this.getDataFromSpecie(specie);

        // debug(specie, specieData, specie.replace(/[()-\s']/g, ""));

        const name = this.fsr(
          '<div class="name"><strong>${title}</strong> ${species}</div>',
          {
            title: _("Name:"),
            species: specieData.name,
            i18n: ["title", "species"],
          }
        );
        const effect = specieData.effect
          ? this.fsr(
              '<div class="effect"><strong>${title}</strong> ${effect}</div>',
              {
                title: _("Effect:"),
                effect: specieData.effect,
                i18n: ["title", "effect"],
              }
            )
          : "";

        const bonus = specieData.bonus
          ? this.fsr(
              '<div class="bonus"><strong>${title}</strong> ${bonus}</div>',
              {
                title: _("Bonus:"),
                bonus: specieData.bonus,
                i18n: ["title", "bonus"],
              }
            )
          : "";

        let bonusTags = {
          log: [],
          args: {
            i18n: [],
          },
        };
        let index = 1;

        specieData.tags.forEach((tag) => {
          bonusTags.log.push("${tag" + index + "}");
          bonusTags.args["tag" + index] = _(tag);
          bonusTags.args.i18n.push("tag" + index);
          index++;
        });

        bonusTags.log = bonusTags.log.join(", ");

        const tags = specieData.tags
          ? this.fsr(
              '<div class="bonus"><strong>${title}</strong> ${bonus}</div>',
              {
                title: _("Tag(s):"),
                bonus: bonusTags,
                i18n: ["title", "bonus"],
              }
            )
          : "";

        const occurence = this.fsr(
          '<div class="occurence"><strong>${title}</strong> ${occurence}</div>',
          {
            title: _("Occurences:"),
            occurence: specieData.nb,
            i18n: ["title", "occurence"],
          }
        );

        const scoring = specieData.points
          ? this.fsr(
              '<div class="scoring"><strong>${title}</strong> ${score}</div>',
              {
                title: _("Scoring:"),
                score: specieData.points,
                i18n: ["title", "score"],
              }
            )
          : "";

        let hintText = WITH_OTHERS.includes(specie)
          ? this.format_string_recursive(
              _("Your set of ${name} cards provides you ${nb} point(s)."),
              {
                nb: `<span id="scoreHint_${cardId}">${score}</span>`,
                name: specieData.tags.includes(BUTTERFLY)
                  ? _("Butterfly")
                  : specieData.name,
                i18n: ["name"],
              }
            )
          : SLOT_SCORE.includes(specie)
          ? this.format_string_recursive(
              _("Each card on this slot provides you ${nb} point(s)."),
              { nb: `<span class="scoreHint_${cardId}">${score}</span>` }
            )
          : this.format_string_recursive(
              _("This card provides you ${nb} point(s)."),
              { nb: `<span class="scoreHint_${cardId}">${score}</span>` }
            );

        hintText =
          score >= 0 ? `<br><div class="hinttext">${hintText}</div>` : "";

        return `
  <div class="idCard">
  ${name}
  ${tags}
  ${occurence}
  ${effect}
  ${bonus}
  ${scoring}
  ${hintText}
  </div>
  `;
      },

      /*
 █████████  ██████████ ██████   █████ ██████████ ███████████   █████   █████████   █████████ 
███░░░░░███░░███░░░░░█░░██████ ░░███ ░░███░░░░░█░░███░░░░░███ ░░███   ███░░░░░███ ███░░░░░███
███     ░░░  ░███  █ ░  ░███░███ ░███  ░███  █ ░  ░███    ░███  ░███  ███     ░░░ ░███    ░░░ 
░███          ░██████    ░███░░███░███  ░██████    ░██████████   ░███ ░███         ░░█████████ 
░███    █████ ░███░░█    ░███ ░░██████  ░███░░█    ░███░░░░░███  ░███ ░███          ░░░░░░░░███
░░███  ░░███  ░███ ░   █ ░███  ░░█████  ░███ ░   █ ░███    ░███  ░███ ░░███     ███ ███    ░███
░░█████████  ██████████ █████  ░░█████ ██████████ █████   █████ █████ ░░█████████ ░░█████████ 
░░░░░░░░░  ░░░░░░░░░░ ░░░░░    ░░░░░ ░░░░░░░░░░ ░░░░░   ░░░░░ ░░░░░   ░░░░░░░░░   ░░░░░░░░░  
                                                                                             
                                                                                             
                                                                                             
*/

      // a player panel to display infos about player in game
      tplPlayerPanel(player) {
        return `<div id='fos-player-infos_${player.id}' class='player-infos'>
  <div class='cards-counter counter' id='card-counter-${player.id}'>0</div>
  <div class='cave-counter counter' id='cave-counter-${player.id}'>0</div>
</div>`;
      },

      // place each player board in good order.
      myUpdatePlayerOrdering(elementName, container) {
        // debug("myUpdatePlayerOrdering", elementName, container);
        if (!elementName) return;
        let index = 0;
        for (let i in this.gamedatas.playerorder) {
          const playerId = this.gamedatas.playerorder[i];
          // debug("playerOrdering", elementName + '_' + playerId, container, index);
          dojo.place("FOStable_" + playerId, "tables", index);
          index++;
        }
      },

      /*
       *   Create and place a counter in a div container
       */
      addCounterOnDeck(containerId, initialValue, canbeEmpty = true) {
        const counterId = containerId + "_deckinfo";
        const div = `<div id="${counterId}" class="deckinfo">0</div>`;
        dojo.place(div, containerId);
        const counter = this.createCounter(counterId, initialValue);
        if (initialValue || !canbeEmpty)
          $(containerId).classList.remove("empty");
        else $(containerId).classList.add("empty");
        return counter;
      },

      /**
       * This method can be used instead of addActionButton, to add a button which is an image (i.e. resource). Can be useful when player
       * need to make a choice of resources or tokens.
       */
      addImageActionButton(
        id,
        handler,
        tooltip,
        classes = null,
        bcolor = "blue"
      ) {
        if (classes) classes.push("shadow bgaimagebutton");
        else classes = ["shadow bgaimagebutton"];

        // this will actually make a transparent button id color = blue
        this.addActionButton(id, "", handler, "customActions", false, bcolor);
        // remove border, for images it better without
        dojo.style(id, "border", "none");
        // but add shadow style (box-shadow, see css)
        dojo.addClass(id, classes.join(" "));
        dojo.removeClass(id, "bgabutton_blue");
        // you can also add additional styles, such as background
        if (tooltip) {
          dojo.attr(id, "title", tooltip);
        }
        return $(id);
      },

      /*
       *
       * To add div in logs
       *
       */

      getTokenDiv(key, args) {
        // debug("getTokenDiv", key, args);
        // ... implement whatever html you want here, example from sharedcode.js
        var token_id = args[key];
        switch (key) {
          case "minicard":
            if (args.position && args.position != TREE) {
              const card = CARDS_DATA[args.cardId];
              return `<br><div data-id="${args.cardId}" class="card logCard ${args.position} ${card.deck} ${args.cardType}"></div>`;
            }
            if (args.cardId) {
              const card = CARDS_DATA[args.cardId];
              return `<br><div data-id="${args.cardId}" class="card logCard ${card.deck} ${args.cardType}"></div>`;
            }

            return token_id;

          case "minicards":
            return (
              "<div class='logCards'>" +
              args.cards
                .map(
                  (cardId) =>
                    `<div data-id="${cardId}" class="card logCard ${CARDS_DATA[cardId]["type"]}"></div>`
                )
                .join("<br>") +
              "</div>"
            );
        }
      },

      genericMove(
        elemId,
        newContainerId,
        fastMode = false,
        position = null,
        callBack = null
      ) {
        const el = $(elemId);
        const newContainer = $(newContainerId);

        if (this.isFastMode() || (fastMode && this.isCurrentPlayerActive())) {
          if (position == "first") newContainer.prepend(el);
          else newContainer.appendChild(el);
          el.classList.add("ready");
          if (callBack) callBack(el);
          return;
        }

        const first = el.getBoundingClientRect();
        // Now set the element to the last position.
        if (position == "first") newContainer.prepend(el);
        else newContainer.appendChild(el);

        const last = el.getBoundingClientRect();

        const invertY = first.top - last.top;
        const invertX = first.left - last.left;
        const zoom = first.width / last.width;

        el.style.transform = `translate(${invertX}px, ${invertY}px) scale(${zoom}) `;

        setTimeout(function () {
          el.classList.add("animate-on-transforms");
          el.style.transform = "";
        }, 100);

        // setTimeout(function() {
        el.addEventListener("transitionend", () => {
          el.classList.remove("animate-on-transforms");
          if (callBack) callBack(el);
        });
        // }, 20);
      },

      /*
       * briefly display a card in the center of the screen
       */
      showCard(card, autoClose = false, nextContainer) {
        if (!card) return;

        dojo.place("<div id='card-overlay'></div>", "ebd-body");
        // let duplicate = card.cloneNode(true);
        // duplicate.id = duplicate.id + ' duplicate';
        this.genericMove(card, "card-overlay", false);
        // $('card-overlay').appendChild(card);
        $("card-overlay").offsetHeight;
        $("card-overlay").classList.add("active");

        let close = () => {
          this.genericMove(card, nextContainer, false);
          $("card-overlay").classList.remove("active");
          this.wait(500).then(() => {
            $("card-overlay").remove();
          });
        };

        if (autoClose) this.wait(2000).then(close);
        else $("card-overlay").addEventListener("click", close);
      },

      /**
       * Called each time the game is repaint to adapt width element
       */
      adaptWidth() {
        // debug("adaptWidth");
        const boxRect = $("page-content").getBoundingClientRect();
        //const cardWidth = Math.min(boxRect.width / 11, 220);
        const cardWidth = boxRect.width / 12;
        const cardInHandWidth = cardWidth * ($("zoom_value").value / 100);
        const r = document.querySelector(":root");
        r.style.setProperty("--card-width", cardWidth + "px");
        r.style.setProperty("--card-in-hand-width", cardInHandWidth + "px");
        r.style.setProperty("--card-on-table-width", cardInHandWidth + "px");
      },
    }
  );
});
