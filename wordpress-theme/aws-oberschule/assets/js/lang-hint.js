/**
 * Aller-Weser-Oberschule – Sprach-Hinweis (Browser-Übersetzung).
 * Öffnet/schließt das Popover mit der Anleitung zur Browser-Übersetzung.
 * Es werden keine Inhalte an Dritte gesendet – die Übersetzung macht der Browser.
 */
(function () {
  "use strict";
  function init() {
    var btn = document.getElementById("awsLangToggle");
    var pop = document.getElementById("awsLangPop");
    if (!btn || !pop) return;

    function open() {
      pop.hidden = false;
      btn.setAttribute("aria-expanded", "true");
    }
    function close() {
      pop.hidden = true;
      btn.setAttribute("aria-expanded", "false");
    }
    function toggle() { pop.hidden ? open() : close(); }

    btn.addEventListener("click", function (e) {
      e.stopPropagation();
      toggle();
    });
    // Klick außerhalb schließt das Popover
    document.addEventListener("click", function (e) {
      if (!pop.hidden && !pop.contains(e.target) && e.target !== btn) close();
    });
    // Escape schließt das Popover
    document.addEventListener("keydown", function (e) {
      if (e.key === "Escape" || e.key === "Esc") close();
    });
  }
  if (document.readyState !== "loading") init();
  else document.addEventListener("DOMContentLoaded", init);
})();
