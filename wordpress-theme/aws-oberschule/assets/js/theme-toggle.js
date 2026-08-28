/**
 * Hell-/Dunkelmodus-Umschalter für die Aller-Weser-Oberschule.
 * Setzt data-theme auf <html>, merkt sich die Wahl (localStorage).
 * Läuft im <head>, damit die gespeicherte Ansicht ohne Aufblitzen greift.
 */
(function () {
  "use strict";
  var root = document.documentElement;
  var KEY = "aws-theme";

  // Gespeicherte Wahl sofort anwenden (vor dem ersten Rendern).
  try {
    var saved = localStorage.getItem(KEY);
    if (saved === "dark" || saved === "light") root.setAttribute("data-theme", saved);
  } catch (e) {}

  function isDark() {
    var attr = root.getAttribute("data-theme");
    if (attr === "dark") return true;
    if (attr === "light") return false;
    return window.matchMedia && window.matchMedia("(prefers-color-scheme: dark)").matches;
  }

  function sync(btn) {
    var dark = isDark();
    btn.setAttribute("aria-pressed", dark ? "true" : "false");
    btn.setAttribute("aria-label", dark ? "Zur hellen Ansicht wechseln" : "Zur dunklen Ansicht wechseln");
  }

  function init() {
    var btn = document.querySelector(".aws-theme-toggle");
    if (!btn) return;
    sync(btn);
    btn.addEventListener("click", function () {
      var next = isDark() ? "light" : "dark";
      root.setAttribute("data-theme", next);
      try { localStorage.setItem(KEY, next); } catch (e) {}
      sync(btn);
    });
  }

  if (document.readyState !== "loading") init();
  else document.addEventListener("DOMContentLoaded", init);
})();
