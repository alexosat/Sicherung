/**
 * Aller-Weser-Oberschule – Lageplan / Campus-Karte „Wo finde ich was?"
 * Anklickbare Marker auf dem schematischen Lageplan; die Info-Tafel zeigt
 * Details zum gewählten Ort. Läuft ohne externe Dienste.
 */
(function () {
  "use strict";
  function init() {
    var root = document.getElementById("awsCampus");
    if (!root) return;
    var places = [
      {cat:"Verwaltung", title:"Verwaltungstrakt & Sekretariat", text:"Der Verwaltungstrakt mit dem Sekretariat – Ihre erste Anlaufstelle für Anmeldung, Fragen und Formales. Auch die Schulleitung ist hier zu finden.", tip:"Besuchende melden sich bitte zuerst im Sekretariat."},
      {cat:"Pause", title:"Schulhof", text:"Platz zum Durchatmen, Spielen und Bewegen zwischen den Stunden – der Treffpunkt in den Pausen.", tip:"Kicker und Tischtennis stehen in der Pause bereit."},
      {cat:"Sport", title:"Weser-Sporthalle", text:"Unsere Sporthalle für den Sportunterricht und die Bewegungs-AGs – von Fußball über Handball bis Badminton.", tip:"Hallenschuhe mit heller Sohle nicht vergessen."},
      {cat:"Mensa & Räume", title:"Mensa, Klassen- & Fachräume", text:"Hier sind die Mensa, Klassenräume sowie Fachräume für Musik und Naturwissenschaften untergebracht – Mittagessen und Unterricht unter einem Dach.", tip:"Mittagessen an den Ganztagstagen (Mo–Mi)."},
      {cat:"Fachräume", title:"Naturwissenschaften, Biologie & Kunst", text:"Fachräume für Naturwissenschaften, Biologie und Kunst – ausgestattet für praktisches und kreatives Arbeiten.", tip:"Viele AGs am Nachmittag finden hier statt."},
      {cat:"Sport", title:"Kurt-Poppe-Sporthalle", text:"Die große Sporthalle westlich des Schulgeländes – für Sportunterricht, Turniere und Bewegungs-AGs.", tip:"Hallenschuhe mit heller Sohle nicht vergessen."},
      {cat:"Anreise", title:"Parkplatz & Bushaltestelle", text:"An der Zufahrt am Sünderberg: Hier halten die Busse aus Dörverden, Westen und Barme; Parkplätze und überdachte Fahrrad-Stellplätze sind in der Nähe.", tip:"Fahrräder bitte abschließen und in die Ständer stellen."},
      {cat:"Schwimmen", title:"Hallenbad Dörverden", text:"Der Anbau an der Sporthalle: Hier findet der Schwimmunterricht statt – kurze Wege, kein Bustransfer nötig.", tip:"Schwimmsachen und Handtuch an Schwimmtagen einpacken."}
    ];
    var pins = Array.prototype.slice.call(root.querySelectorAll(".aws-campus-pin"));
    var elCat = root.querySelector(".aws-campus-cat"),
        elTitle = root.querySelector(".aws-campus-title"),
        elText = root.querySelector(".aws-campus-text"),
        elTip = root.querySelector(".aws-campus-tip"),
        listEl = root.querySelector(".aws-campus-list");
    if (!elTitle || !listEl) return;
    var chips = [];
    places.forEach(function (p, i) {
      var b = document.createElement("button"); b.type = "button"; b.textContent = (i + 1) + ". " + p.title;
      b.addEventListener("click", function () { select(i); });
      listEl.appendChild(b); chips.push(b);
    });
    function select(i) {
      var p = places[i];
      if (!p) return;
      elCat.textContent = p.cat; elTitle.textContent = p.title; elText.textContent = p.text; elTip.textContent = "Tipp: " + p.tip;
      pins.forEach(function (x, j) { x.classList.toggle("active", j === i); x.setAttribute("aria-pressed", j === i); });
      chips.forEach(function (x, j) { x.classList.toggle("active", j === i); });
    }
    pins.forEach(function (x) {
      x.setAttribute("type", "button");
      var idx = +x.dataset.i;
      if (places[idx]) x.setAttribute("aria-label", places[idx].title);
      x.addEventListener("click", function () { select(idx); });
    });
    select(0);
  }
  if (document.readyState !== "loading") init();
  else document.addEventListener("DOMContentLoaded", init);
})();
