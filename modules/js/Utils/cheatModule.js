/*
 * Modal component that works like popin dialog
 * To have the same styling as the BGA ones, use the style at the end of this file
 */

define([
  "dojo",
  "dojo/_base/declare",
  "dojo/fx",
  "dojox/fx/ext-dojo/complex",
], function (dojo, declare) {
  return declare("forestshuffle.cheatModule", null, {
    cheatModuleSetup(gamedatas) {
      dojo.place(this.cheatBlock(), "player_boards");
      Object.entries(gamedatas.cheatModule).forEach(([name, data]) =>
        this.addPanel(name, data)
      );
      $("ebd-body").classList.add("debug");
    },

    notif_refresh() {
      window.location.reload();
    },

    cheatBlock() {
      return `<div class='player-board' id='cheatModuleBlock'>
		<div class="title">Cheating Machine</div>
		
		</div>`;
    },

    addPanel(name, datas) {
      debug("cheat", datas);
      let panel_tpl = `<div id="panel_${name}"><form id='form_${name}'>`;
      panel_tpl += `<input type='hidden' type="text" name="class" value="${name}" />`;
      Object.entries(datas).forEach(([label, data]) => {
        if (label !== "sub")
          panel_tpl += this.createInput(label, data, datas.sub);
      });
      Object.entries(datas.sub).forEach(([key, value]) => {
        panel_tpl += this.createInput(key, value);
      });
      panel_tpl += `<a href="#" id="send_${name}" class="action-button bgabutton bgabutton_blue" onclick="return false;">Send</a></form></div>`;
      dojo.place(panel_tpl, "cheatModuleBlock");

      this.connectClass("cheatSelect", "onchange", (e) => {
        const target = e.target.selectedOptions[0];
        if (target.classList.contains("subfield")) {
          const sub = target.dataset.subfield;
          $(sub).removeAttribute("disabled");
          e.target.dataset.toRemove = sub;
        } else {
          if (e.target.dataset.toRemove) {
            $(e.target.dataset.toRemove).setAttribute("disabled", true);
          }
        }
      });

      dojo.connect($("send_" + name), "click", (e) => {
        const data = new FormData($("form_" + name));
        debug("je vais envoyer :", {
          data: Object.fromEntries(data.entries()),
        });
        for (let value of data.values()) {
          if (value === "") {
            alert("Please use all needed field");
            return false;
          }
        }

        this.takeAction(
          "cheat",
          { data: JSON.stringify(Object.fromEntries(data.entries())) },
          false,
          false
        );
      });
    },

    createInput(label, data, subs = null) {
      debug(label, data, subs);
      const disabled = subs == null ? "disabled" : "";
      let result = `<select class="cheatSelect" id="${label}" name="${label}" ${disabled}>
		<option value="">Choose ${label}</option>`;
      let subFields = [];
      if (Array.isArray(data)) {
        //simple array data
        Object.values(data).forEach(
          (name) => (result += `<option value='${name}'>${name}</option>`)
        );
      } else {
        Object.entries(data).forEach(([id, data]) => {
          if (data.subField) {
            result += `<option class="subfield" data-subfield="${data.subField}" value='${id}'>${id} + ${data.subField}</option>`;
          } else if (data === Object(data)) {
            result += `<option value='${id}'>${id}</option>`;
          } else {
            // simple id -> name format data
            result += `<option value='${id}'>${id} : ${data}</option>`;
          }
        });
      }
      result += "</select><br>";

      return result;
    },
  });
});
