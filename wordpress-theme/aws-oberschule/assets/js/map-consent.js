/**
 * Aller-Weser-Oberschule – Karte (OpenStreetMap) mit Klick-zum-Laden.
 * Die Karte wird erst nach ausdrücklicher Zustimmung geladen; vorher wird
 * KEINE Verbindung zu OpenStreetMap aufgebaut (keine IP-Übertragung).
 * Die Zustimmung wird pro Browser gemerkt (localStorage).
 */
(function () {
  "use strict";

  function loadMap(el) {
    var lat = parseFloat(el.getAttribute("data-lat"));
    var lon = parseFloat(el.getAttribute("data-lon"));
    var d = parseFloat(el.getAttribute("data-delta")) || 0.008;
    if (isNaN(lat) || isNaN(lon)) return;

    var bbox = [
      (lon - d).toFixed(5),
      (lat - d * 0.55).toFixed(5),
      (lon + d).toFixed(5),
      (lat + d * 0.55).toFixed(5)
    ].join("%2C");
    var src = "https://www.openstreetmap.org/export/embed.html?bbox=" + bbox +
      "&layer=mapnik&marker=" + lat.toFixed(5) + "%2C" + lon.toFixed(5);

    var frame = document.createElement("iframe");
    frame.setAttribute("src", src);
    frame.setAttribute("title", "Standort der Aller-Weser-Oberschule auf OpenStreetMap");
    frame.setAttribute("loading", "lazy");
    frame.setAttribute("referrerpolicy", "no-referrer");

    var consent = el.querySelector(".aws-map-consent");
    if (consent) consent.parentNode.removeChild(consent);
    el.appendChild(frame);
    el.classList.add("is-loaded");
  }

  function init() {
    var el = document.getElementById("awsMap");
    if (!el) return;
    var btn = document.getElementById("awsMapLoad");

    var remembered = false;
    try { remembered = localStorage.getItem("aws-map-consent") === "yes"; } catch (e) {}
    if (remembered) { loadMap(el); return; }

    if (btn) {
      btn.addEventListener("click", function () {
        try { localStorage.setItem("aws-map-consent", "yes"); } catch (e) {}
        loadMap(el);
      });
    }
  }

  if (document.readyState !== "loading") init();
  else document.addEventListener("DOMContentLoaded", init);
})();
