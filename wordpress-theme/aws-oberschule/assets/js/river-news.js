/**
 * Aller-Weser-Oberschule – Aktuelles: Fluss-Zeitstrahl "Strömung"
 * Zeichnet eine geschwungene Flusslinie, deren Strömung beim Scrollen mitfließt.
 * Stationspunkte sitzen auf der Linie; Karten blenden nacheinander ein.
 * Läuft nur im Front-End. Pixel-Koordinaten für gleichmäßige Linie.
 */
(function () {
  "use strict";

  function init() {
    var track = document.getElementById("newsTrack");
    if (!track) return;
    var svg = document.getElementById("newsRiver");
    var base = document.getElementById("riverBase");
    var draw = document.getElementById("riverDraw");
    var head = document.getElementById("newsHead");
    var nodesLayer = document.getElementById("newsNodes");
    if (!svg || !base || !draw || !nodesLayer) return;
    var items = Array.prototype.slice.call(track.querySelectorAll(".news-item"));
    if (!items.length) return;
    var reduce = window.matchMedia("(prefers-reduced-motion: reduce)").matches;
    function mobile() { return window.matchMedia("(max-width: 760px)").matches; }

    var nodes = items.map(function () {
      var n = document.createElement("div");
      n.className = "news-node";
      nodesLayer.appendChild(n);
      return n;
    });

    var W = 0, H = 0, len = 0;
    function xAt(t) {
      var amp = Math.min(58, W * 0.06);
      return W / 2 + amp * Math.sin(t * Math.PI * 2.6);
    }
    function build() {
      W = track.clientWidth; H = track.clientHeight;
      svg.setAttribute("viewBox", "0 0 " + W + " " + H);
      var d = "M " + xAt(0).toFixed(1) + " 0";
      for (var i = 1; i <= 120; i++) { var t = i / 120; d += " L " + xAt(t).toFixed(1) + " " + (t * H).toFixed(1); }
      base.setAttribute("d", d); draw.setAttribute("d", d);
      len = draw.getTotalLength();
      draw.style.strokeDasharray = len;
      items.forEach(function (item, i) {
        var cy = item.offsetTop + item.offsetHeight / 2;
        var t = H ? cy / H : 0;
        nodes[i].style.top = cy + "px";
        nodes[i].style.left = (mobile() ? 9 : xAt(t)) + "px";
      });
      onScroll();
    }
    function onScroll() {
      if (reduce) { draw.style.strokeDashoffset = 0; return; }
      var r = track.getBoundingClientRect();
      var winH = window.innerHeight;
      var p = (winH * 0.82 - r.top) / (r.height + winH * 0.30);
      p = Math.max(0, Math.min(1, p));
      draw.style.strokeDashoffset = len * (1 - p);
      if (!mobile() && head) {
        head.style.left = xAt(p) + "px";
        head.style.top = (p * H) + "px";
        head.style.opacity = (p > 0.01 && p < 0.995) ? "1" : "0";
      } else if (head) { head.style.opacity = "0"; }
    }

    if ("IntersectionObserver" in window && !reduce) {
      var io = new IntersectionObserver(function (entries) {
        entries.forEach(function (e) {
          if (e.isIntersecting) {
            e.target.classList.add("in");
            var idx = items.indexOf(e.target);
            if (idx > -1 && nodes[idx]) nodes[idx].classList.add("in");
            io.unobserve(e.target);
          }
        });
      }, { threshold: 0.35 });
      items.forEach(function (it) { io.observe(it); });
    } else {
      items.forEach(function (it, i) { it.classList.add("in"); nodes[i].classList.add("in"); });
    }

    build();
    window.addEventListener("resize", build);
    window.addEventListener("scroll", onScroll, { passive: true });
    // Nach Laden der Schriften Höhen neu berechnen
    if (document.fonts && document.fonts.ready) { document.fonts.ready.then(build); }
  }

  if (document.readyState !== "loading") init();
  else document.addEventListener("DOMContentLoaded", init);
})();
