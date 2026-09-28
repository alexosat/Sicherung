/**
 * Aller-Weser-Oberschule – Kopfleiste: schrumpft sanft beim Scrollen.
 * Fügt der Kopfleiste ab einer kleinen Scrolltiefe die Klasse "scrolled" hinzu.
 */
(function () {
  "use strict";
  var header = document.querySelector(".aws-header");
  if (!header) return;
  var threshold = 24;
  function onScroll() {
    if (window.scrollY > threshold) header.classList.add("scrolled");
    else header.classList.remove("scrolled");
  }
  onScroll();
  window.addEventListener("scroll", onScroll, { passive: true });
})();
