/**
 * Aller-Weser-Oberschule – kompakte Mini-Navigation
 * Blendet eine schmale, fixierte Navigationsleiste ein, sobald der große
 * Kopf-Bogen beim Scrollen aus dem Blick verschwindet. Scrollt man wieder
 * nach oben, wird sie ausgeblendet.
 */
(function () {
  "use strict";
  function init() {
    var bar = document.getElementById("awsMiniNav");
    if (!bar) return;
    var arc = document.getElementById("arc");
    var shown = false;

    // Logo = „nach ganz oben“ (zuverlässig auf jeder Seite, statt Anker-Sprung).
    var brand = bar.querySelector(".aws-mininav-brand");
    if (brand) {
      brand.addEventListener("click", function (e) {
        e.preventDefault();
        window.scrollTo({ top: 0, behavior: "smooth" });
      });
    }

    function shouldShow() {
      if (arc) return arc.getBoundingClientRect().bottom <= 8;
      return (window.pageYOffset || document.documentElement.scrollTop || 0) > 400;
    }
    function onScroll() {
      var show = shouldShow();
      if (show !== shown) {
        shown = show;
        bar.classList.toggle("show", show);
        document.documentElement.classList.toggle("aws-scrolled", show);
      }
    }
    onScroll();
    window.addEventListener("scroll", onScroll, { passive: true });
    window.addEventListener("resize", onScroll);
  }
  if (document.readyState !== "loading") init();
  else document.addEventListener("DOMContentLoaded", init);
})();
