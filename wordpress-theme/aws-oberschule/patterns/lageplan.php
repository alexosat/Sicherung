<?php
/**
 * Title: Seite: Lageplan – Wo finde ich was?
 * Slug: aws/lageplan
 * Categories: aws
 * Inserter: true
 * Description: Interaktive Campus-Karte mit anklickbaren Orten. In eine neue Seite „Lageplan“ einfügen.
 */
?>
<!-- wp:group {"className":"aws-inner"} --><div class="wp-block-group aws-inner">
<!-- wp:group {"className":"aws-sec-head"} --><div class="wp-block-group aws-sec-head"><!-- wp:paragraph {"className":"aws-eyebrow"} --><p class="aws-eyebrow">Unser Campus am Sünderberg</p><!-- /wp:paragraph --><!-- wp:heading {"level":1} --><h1 class="wp-block-heading">Wo finde ich was?</h1><!-- /wp:heading --><!-- wp:paragraph --><p>Unser Schulgelände auf einen Blick. Tippen Sie auf einen Punkt — oder wählen Sie aus der Liste —, um mehr zu erfahren.</p><!-- /wp:paragraph --></div><!-- /wp:group -->

<!-- wp:html -->
<div class="aws-campus" id="awsCampus">
  <div class="aws-campus-layout">
    <div class="aws-campus-map">
      <svg class="aws-campus-scene" viewBox="0 0 400 250" preserveAspectRatio="none" aria-hidden="true">
        <rect width="400" height="250" fill="var(--c-surface-2)"/>
        <rect width="400" height="250" fill="var(--c-accent)" opacity=".12"/>
        <path d="M356 -5 L392 -5 L404 255 L370 255 Z" fill="var(--c-line)"/>
        <path d="M150 14 L300 14 L300 25 L150 25 Z" fill="var(--c-line)"/>
        <path d="M233 25 L247 25 L247 58 L233 58 Z" fill="var(--c-line)"/>
        <g transform="rotate(-32 96 150)">
          <rect x="54" y="118" width="88" height="56" rx="4" fill="var(--c-surface)" stroke="var(--c-line)" stroke-width="2"/>
          <rect x="74" y="174" width="40" height="34" rx="4" fill="var(--c-surface)" stroke="var(--c-line)" stroke-width="2"/>
        </g>
        <g fill="var(--c-surface)" stroke="var(--c-line)" stroke-width="2">
          <rect x="150" y="34" width="120" height="72" rx="4"/>
          <rect x="286" y="40" width="82" height="60" rx="4"/>
          <rect x="300" y="104" width="60" height="80" rx="4"/>
          <rect x="164" y="120" width="150" height="56" rx="4"/>
          <rect x="180" y="186" width="124" height="46" rx="4"/>
          <rect x="196" y="236" width="92" height="30" rx="4"/>
        </g>
        <g fill="var(--c-primary-deep)" opacity=".8">
          <rect x="150" y="34" width="120" height="10" rx="4"/>
          <rect x="164" y="120" width="150" height="9" rx="4"/>
          <rect x="180" y="186" width="124" height="9" rx="4"/>
        </g>
        <g fill="var(--c-accent)" opacity=".75"><circle cx="330" cy="212" r="9"/><circle cx="150" cy="208" r="8"/><circle cx="360" cy="58" r="8"/></g>
        <text x="383" y="128" fill="var(--c-muted)" font-size="10" text-anchor="middle" transform="rotate(90 383 128)">Am Sünderberg</text>
      </svg>
      <button class="aws-campus-pin" data-i="0" style="left:60.5%;top:83.6%">1</button>
      <button class="aws-campus-pin" data-i="1" style="left:52.5%;top:28.8%">2</button>
      <button class="aws-campus-pin" data-i="2" style="left:81.8%;top:28%">3</button>
      <button class="aws-campus-pin" data-i="3" style="left:59.8%;top:59.2%">4</button>
      <button class="aws-campus-pin" data-i="4" style="left:82.5%;top:57.2%">5</button>
      <button class="aws-campus-pin" data-i="5" style="left:23.75%;top:60%">6</button>
      <button class="aws-campus-pin" data-i="6" style="left:75%;top:8%">7</button>
      <button class="aws-campus-pin" data-i="7" style="left:29%;top:74%">8</button>
    </div>
    <aside class="aws-campus-panel" aria-live="polite">
      <span class="aws-campus-cat">Verwaltung</span>
      <h3 class="aws-campus-title">Verwaltungstrakt &amp; Sekretariat</h3>
      <p class="aws-campus-text">Der Verwaltungstrakt mit dem Sekretariat – Ihre erste Anlaufstelle für Anmeldung, Fragen und Formales. Auch die Schulleitung ist hier zu finden.</p>
      <p class="aws-campus-tip">Tipp: Besuchende melden sich bitte zuerst im Sekretariat.</p>
      <ul class="aws-campus-list"></ul>
    </aside>
  </div>
  <p class="aws-campus-note">Schematische Darstellung des Schulgeländes zur Orientierung.</p>
</div>
<!-- /wp:html -->
</div><!-- /wp:group -->
