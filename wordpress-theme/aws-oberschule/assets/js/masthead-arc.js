/**
 * Aller-Weser-Oberschule – Kopf-Bogen (Masthead): platziert die Icon-Buttons
 * auf einem flachen, breiten Bogen und blendet sie beim Laden ein.
 */
(function () {
  "use strict";
  function init() {
    var arc = document.getElementById("arc");
    if (!arc) return;
    var svg = document.getElementById("arcLine");
    var path = document.getElementById("arcPath");
    var items = Array.prototype.slice.call(arc.querySelectorAll(".arc-item"));
    if (!items.length) return;
    var reduce = window.matchMedia("(prefers-reduced-motion: reduce)").matches;
    function mobile() { return window.matchMedia("(max-width: 700px)").matches; }

    function layout() {
      if (mobile()) {
        if (svg) svg.style.display = "none";
        items.forEach(function (it) { it.style.left = ""; it.style.top = ""; });
        return;
      }
      if (svg) svg.style.display = "";
      var W = arc.clientWidth, H = arc.clientHeight;
      if (svg) svg.setAttribute("viewBox", "0 0 " + W + " " + H);
      var cx = W / 2, top = 92;
      var Rx = W / 2 - 96;                    // breit
      var Ry = Math.min(120, H - top - 92);   // flach
      var n = items.length, d = "";
      for (var s = 0; s <= 80; s++) {
        var aa = Math.PI * (1 - s / 80);
        d += (s ? " L " : "M ") + (cx + Rx * Math.cos(aa)).toFixed(1) + " " + (top + Ry * Math.sin(aa)).toFixed(1);
      }
      if (path) path.setAttribute("d", d);
      items.forEach(function (it, i) {
        var a = Math.PI * (1 - i / (n - 1));
        it.style.left = (cx + Rx * Math.cos(a)) + "px";
        it.style.top = (top + Ry * Math.sin(a)) + "px";
      });
    }

    function reveal() {
      arc.classList.add("in");
      items.forEach(function (it, i) { it.style.animationDelay = (i * 0.09) + "s"; });
      if (path && !mobile()) {
        var len = path.getTotalLength();
        path.style.strokeDasharray = len;
        if (reduce) { path.style.strokeDashoffset = 0; return; }
        path.style.strokeDashoffset = len;
        requestAnimationFrame(function () {
          path.style.transition = "stroke-dashoffset 1s ease .1s";
          path.style.strokeDashoffset = 0;
        });
      }
    }

    layout();
    window.addEventListener("resize", layout);
    if ("IntersectionObserver" in window && !reduce) {
      var io = new IntersectionObserver(function (entries) {
        entries.forEach(function (e) { if (e.isIntersecting) { reveal(); io.disconnect(); } });
      }, { threshold: 0.15 });
      io.observe(arc);
    } else {
      reveal();
    }
  }
  if (document.readyState !== "loading") init();
  else document.addEventListener("DOMContentLoaded", init);
})();
