/**
 * Aller-Weser-Oberschule – YouTube „Neueste Folge“ mit Klick-zum-Laden.
 * Der Player wird erst nach ausdrücklichem Klick geladen; vorher wird KEINE
 * Verbindung zu YouTube/Google aufgebaut (keine IP-Übertragung, kein Autoplay
 * beim Seitenaufruf). Es wird youtube-nocookie.com verwendet.
 */
(function () {
  "use strict";

  function buildSrc(el) {
    // Nativer WordPress-Embed-Block: fertige (bereits auf nocookie umgestellte) URL.
    var direct = el.getAttribute("data-src");
    if (direct) {
      if (direct.indexOf("autoplay=") === -1) {
        direct += (direct.indexOf("?") === -1 ? "?" : "&") + "autoplay=1";
      }
      return direct;
    }
    var video = el.getAttribute("data-video");
    var list = el.getAttribute("data-list");
    var base = "https://www.youtube-nocookie.com/embed/";
    var params = "autoplay=1&rel=0";
    if (video) {
      // Neueste Folge zuerst; Uploads-Playlist (ältere Folgen) läuft weiter.
      if (list) params += "&list=" + encodeURIComponent(list);
      return base + encodeURIComponent(video) + "?" + params;
    }
    if (list) {
      return base + "videoseries?list=" + encodeURIComponent(list) + "&" + params;
    }
    return "";
  }

  function load(el) {
    var src = buildSrc(el);
    if (!src) return;

    var frame = document.createElement("iframe");
    frame.setAttribute("src", src);
    frame.setAttribute(
      "title",
      el.getAttribute("data-src") ? "YouTube-Video" : "YouTube-Kanal Schulhofgeflüster – neueste Folge"
    );
    frame.setAttribute(
      "allow",
      "accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
    );
    frame.setAttribute("allowfullscreen", "");
    frame.setAttribute("loading", "lazy");
    frame.setAttribute("referrerpolicy", "strict-origin-when-cross-origin");

    var facade = el.querySelector(".aws-yt-facade");
    if (facade) facade.parentNode.removeChild(facade);
    el.appendChild(frame);
    el.classList.add("is-loaded");
  }

  function init() {
    var nodes = document.querySelectorAll(".aws-yt[data-video], .aws-yt[data-list], .aws-yt[data-src]");
    Array.prototype.forEach.call(nodes, function (el) {
      var btn = el.querySelector(".aws-yt-load");
      if (!btn || btn.tagName === "A") return; // Reiner Kanal-Link: nichts zu laden.
      btn.addEventListener("click", function (e) {
        e.preventDefault();
        load(el);
      });
    });
  }

  if (document.readyState !== "loading") init();
  else document.addEventListener("DOMContentLoaded", init);
})();
