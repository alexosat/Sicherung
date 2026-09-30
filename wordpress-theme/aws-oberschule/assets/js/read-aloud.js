/**
 * Aller-Weser-Oberschule – Vorlesefunktion
 * Liest den Hauptinhalt der Seite mit der im Browser eingebauten Sprachausgabe
 * (Web Speech API) vor. Keine externen Dienste, keine Datenübertragung.
 * Steuerung: Start / Pause-Weiter / Stopp. Der gerade gelesene Satz wird hervorgehoben.
 */
(function () {
  "use strict";
  if (!("speechSynthesis" in window) || typeof SpeechSynthesisUtterance === "undefined") return;

  function init() {
    var wrap = document.getElementById("awsTts");
    var playBtn = document.getElementById("awsTtsPlay");
    var pauseBtn = document.getElementById("awsTtsPause");
    var stopBtn = document.getElementById("awsTtsStop");
    if (!wrap || !playBtn || !pauseBtn || !stopBtn) return;
    var main = document.querySelector("main.aws-main") || document.querySelector("main");
    if (!main) return;

    wrap.hidden = false; // Steuerung nur zeigen, wenn Vorlesen unterstützt wird

    var units = null;          // [{ text, el }]
    var speaking = false, paused = false, voice = null;

    function pickVoice() {
      var vs = window.speechSynthesis.getVoices() || [];
      voice = vs.filter(function (v) { return /^de(-|_|$)/i.test(v.lang); })[0]
           || vs.filter(function (v) { return /de/i.test(v.lang); })[0] || null;
    }
    pickVoice();
    window.speechSynthesis.onvoiceschanged = pickVoice;

    function escapeHtml(s) {
      return s.replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;");
    }
    function splitSentences(t) {
      t = (t || "").replace(/\s+/g, " ").trim();
      if (!t) return [];
      var parts = t.match(/[^.!?…]+[.!?…]+(?:\s|$)|[^.!?…]+$/g);
      return parts ? parts.map(function (s) { return s.trim(); }).filter(Boolean) : [t];
    }

    function build() {
      units = [];
      var blocks = main.querySelectorAll("h1, h2, h3, h4, p, li, blockquote, figcaption");
      Array.prototype.forEach.call(blocks, function (b) {
        if (b.closest(".aws-tts")) return;
        if (b.getAttribute("aria-hidden") === "true") return;
        var txt = (b.textContent || "").replace(/\s+/g, " ").trim();
        if (!txt) return;
        var rich = b.querySelector("a,strong,em,span,br,code,b,i,mark");
        var sentences = splitSentences(txt);
        if (!rich && sentences.length > 1) {
          b.innerHTML = sentences.map(function (s) {
            return '<span class="aws-tts-s">' + escapeHtml(s) + "</span>";
          }).join(" ");
          var spans = b.querySelectorAll(".aws-tts-s");
          Array.prototype.forEach.call(spans, function (sp, i) {
            units.push({ text: sentences[i], el: sp });
          });
        } else {
          sentences.forEach(function (s) { units.push({ text: s, el: b }); });
        }
      });
    }

    function clearHighlight() {
      var cur = main.querySelectorAll(".aws-reading");
      Array.prototype.forEach.call(cur, function (e) { e.classList.remove("aws-reading"); });
    }
    function scrollTo(el) {
      var r = el.getBoundingClientRect();
      if (r.top < 90 || r.bottom > window.innerHeight - 40) {
        el.scrollIntoView({ block: "center", behavior: "smooth" });
      }
    }

    function speakFrom(i) {
      if (!speaking) return;
      if (i >= units.length) { finish(); return; }
      var u = units[i];
      clearHighlight();
      if (u.el) { u.el.classList.add("aws-reading"); scrollTo(u.el); }
      var utt = new SpeechSynthesisUtterance(u.text);
      utt.lang = "de-DE";
      if (voice) utt.voice = voice;
      utt.onend = function () { if (speaking && !paused) speakFrom(i + 1); };
      utt.onerror = function () { if (speaking && !paused) speakFrom(i + 1); };
      window.speechSynthesis.speak(utt);
    }

    function setState(s) {
      if (s === "idle") {
        playBtn.hidden = false; pauseBtn.hidden = true; stopBtn.hidden = true;
      } else {
        playBtn.hidden = true; pauseBtn.hidden = false; stopBtn.hidden = false;
        pauseBtn.setAttribute("aria-label", s === "paused" ? "Weiter vorlesen" : "Vorlesen pausieren");
        pauseBtn.setAttribute("title", s === "paused" ? "Weiter" : "Pause");
        pauseBtn.classList.toggle("is-paused", s === "paused");
      }
    }

    function start() {
      if (units === null) build();
      if (!units.length) return;
      window.speechSynthesis.cancel();
      speaking = true; paused = false;
      setState("playing");
      speakFrom(0);
    }
    function togglePause() {
      if (!speaking) return;
      if (!paused) { window.speechSynthesis.pause(); paused = true; setState("paused"); }
      else { window.speechSynthesis.resume(); paused = false; setState("playing"); }
    }
    function stop() {
      speaking = false; paused = false;
      window.speechSynthesis.cancel();
      clearHighlight();
      setState("idle");
    }
    function finish() {
      speaking = false; paused = false;
      clearHighlight();
      setState("idle");
    }

    playBtn.addEventListener("click", start);
    pauseBtn.addEventListener("click", togglePause);
    stopBtn.addEventListener("click", stop);
    window.addEventListener("pagehide", function () { window.speechSynthesis.cancel(); });
  }

  if (document.readyState !== "loading") init();
  else document.addEventListener("DOMContentLoaded", init);
})();
