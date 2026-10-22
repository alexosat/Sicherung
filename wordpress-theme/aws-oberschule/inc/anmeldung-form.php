<?php
/**
 * Aller-Weser-Oberschule – Online-Anmeldung (Aufnahmebogen)
 * Shortcode [aws_anmeldung] rendert das Formular; der Handler versendet die
 * Anmeldung per E-Mail (wp_mail) an das Sekretariat. Datei-Uploads werden als
 * Anhang mitgesendet und danach vom Server gelöscht (keine dauerhafte Speicherung).
 *
 * Hinweis Transportverschlüsselung: Der tatsächliche TLS-Versand hängt von der
 * SMTP-Konfiguration der Website ab (siehe Dokumentation zum Theme).
 *
 * @package aws-oberschule
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** Empfängeradresse (per Filter/Konstante anpassbar). */
if ( ! defined( 'AWS_ANMELDUNG_TO' ) ) {
	define( 'AWS_ANMELDUNG_TO', 'sekretariat@schulzentrum-doerverden.de' );
}

/** Absenderadresse (sollte zu Ihrem SMTP-Postfach passen). */
if ( ! defined( 'AWS_ANMELDUNG_FROM' ) ) {
	define( 'AWS_ANMELDUNG_FROM', 'anmeldung@obs-doerverden.de' );
}

/** Kleine Escape-Hilfe für Ausgaben. */
function aws_e( $s ) {
	return esc_attr( (string) $s );
}

/** Ja/Nein-Radiogruppe. */
function aws_yesno( $name ) {
	return '<div class="radio"><label><input type="radio" name="' . aws_e( $name ) . '" value="ja"> ja</label>'
		. '<label><input type="radio" name="' . aws_e( $name ) . '" value="nein"> nein</label></div>';
}

/**
 * Rendert das Anmeldeformular als Shortcode.
 */
function aws_anmeldung_form() {
	$status = isset( $_GET['aws_anmeldung'] ) ? sanitize_key( wp_unslash( $_GET['aws_anmeldung'] ) ) : '';

	ob_start();

	if ( 'ok' === $status ) {
		echo '<div class="aws-form-ok" role="status">'
			. '<h2>Vielen Dank – Ihre Anmeldung ist eingegangen.</h2>'
			. '<p>Wir haben Ihre Angaben an das Sekretariat übermittelt und melden uns bei Rückfragen. '
			. 'Bei Bedarf können Sie fehlende Nachweise jederzeit im Sekretariat nachreichen.</p></div>';
		return ob_get_clean();
	}

	if ( 'error' === $status ) {
		echo '<div class="aws-form-err" role="alert">'
			. '<strong>Die Anmeldung konnte nicht gesendet werden.</strong> '
			. 'Bitte prüfen Sie die Pflichtfelder (mit * markiert), die Sicherheitsfrage und die Datenschutz-Einwilligung und versuchen Sie es erneut.</div>';
	}

	$action = esc_url( admin_url( 'admin-post.php' ) );
	?>
	<form class="aws-anmeldung" method="post" enctype="multipart/form-data" action="<?php echo $action; ?>">
		<input type="hidden" name="action" value="aws_anmeldung">
		<?php wp_nonce_field( 'aws_anmeldung', 'aws_nonce' ); ?>
		<div class="aws-hp" aria-hidden="true"><label>Bitte dieses Feld leer lassen<input type="text" name="aws_hp" tabindex="-1" autocomplete="off"></label></div>

		<div class="aws-note">
			<strong>Aufnahme für das Schuljahr 2026/27.</strong> Bitte je eine <strong>Kopie</strong> beifügen:
			Versetzungszeugnis Klasse 3, Halbjahreszeugnis Klasse 4, Geburtsurkunde. Mit <span class="req">*</span> markierte Felder sind Pflichtfelder.
		</div>

		<!-- 1 Schulkind -->
		<fieldset><legend><span class="n">1</span> Angaben zum Schulkind</legend>
			<div class="grid">
				<div><label>Klassenstufe <span class="req">*</span></label>
					<select name="klassenstufe" required><option value="5">5</option><option value="6">6</option><option value="7">7</option><option value="8">8</option><option value="9">9</option><option value="10">10</option></select></div>
				<div></div>
				<div><label>Familienname <span class="req">*</span></label><input type="text" name="familienname" required></div>
				<div><label>Vorname(n) <span class="req">*</span></label><input type="text" name="vorname" required></div>
				<div><label>Geschlecht <span class="req">*</span></label>
					<div class="radio"><label><input type="radio" name="geschlecht" value="männlich" required> männlich</label><label><input type="radio" name="geschlecht" value="weiblich"> weiblich</label></div></div>
				<div><label>Staatsangehörigkeit</label><input type="text" name="staatsangehoerigkeit"></div>
				<div><label>Geburtstag <span class="req">*</span></label><input type="date" name="geburtstag" required></div>
				<div><label>Geburtsort <span class="req">*</span></label><input type="text" name="geburtsort" required></div>
				<div><label>Herkunftssprache</label><input type="text" name="herkunftssprache"></div>
				<div><label>Konfession</label>
					<div class="radio"><label><input type="radio" name="konfession" value="evangelisch"> evangelisch</label><label><input type="radio" name="konfession" value="katholisch"> katholisch</label><label><input type="radio" name="konfession" value="islamisch"> islamisch</label><label><input type="radio" name="konfession" value="ohne"> ohne</label></div>
					<input type="text" name="konfession_sonstige" placeholder="sonstige:" style="margin-top:.4rem"></div>
				<div class="full"><label>Anschrift – Straße, Haus-Nr. <span class="req">*</span></label><input type="text" name="anschrift" required></div>
				<div><label>PLZ, Ort <span class="req">*</span></label><input type="text" name="plz_ort" required></div>
				<div><label>Telefon <span class="req">*</span></label><input type="tel" name="telefon" required></div>
				<div><label>E-Mail-Adresse</label><input type="email" name="email"></div>
				<div><label>Handy-Nummer / Notfall-Telefon</label><input type="tel" name="handy"></div>
			</div>
		</fieldset>

		<!-- 2 Religion -->
		<fieldset><legend><span class="n">2</span> Teilnahme am Religionsunterricht</legend>
			<?php echo aws_yesno( 'rel_teilnahme' ); ?>
			<div class="subhead">Mein Kind nimmt teil an:</div>
			<div class="radio"><label><input type="radio" name="rel_art" value="Christliche Religion"> Christliche Religion</label><label><input type="radio" name="rel_art" value="Werte und Normen"> Werte und Normen</label></div>
		</fieldset>

		<!-- 3 Schullaufbahn -->
		<fieldset><legend><span class="n">3</span> Schullaufbahn &amp; Förderung</legend>
			<div class="subhead">Liegt ein festgestellter Förderbedarf vor?</div>
			<?php echo aws_yesno( 'foerderbedarf' ); ?>
			<div class="cond"><label>Wenn ja, welcher Schwerpunkt?</label><input type="text" name="foerder_schwerpunkt"></div>
			<div class="subhead">Wiederholung einer Klasse</div>
			<?php echo aws_yesno( 'wiederholung' ); ?>
			<div class="cond"><label>Wenn ja, welche?</label><input type="text" name="wiederholung_welche"></div>
			<div class="grid" style="margin-top:1rem">
				<div><label>Aufnahme an unserer Schule ab</label><input type="text" name="aufnahme_ab" placeholder="z. B. 01.08.20XX"></div>
				<div><label>von Schule (Name)</label><input type="text" name="aufnahme_vonschule"></div>
				<div><label>Einschulungsjahr in die Grundschule</label><input type="text" name="einschulungsjahr" placeholder="Jahr"></div>
				<div><label>Name der Einrichtung (Grundschule)</label><input type="text" name="grundschule_name"></div>
			</div>
		</fieldset>

		<!-- 4 Beförderung -->
		<fieldset><legend><span class="n">4</span> Schülerbeförderung</legend>
			<div class="subhead">Schülerbeförderung erforderlich?</div>
			<?php echo aws_yesno( 'befoerderung' ); ?>
			<div class="check stack" style="margin-top:.8rem">
				<label><input type="checkbox" name="landkreis_einwilligung" value="ja"> Wir/Ich willige(n) in die Weitergabe unserer personenbezogenen Daten an den Landkreis Verden zum Zwecke der Anspruchsprüfung ein.</label>
			</div>
		</fieldset>

		<!-- 5 Erziehungsberechtigte -->
		<fieldset><legend><span class="n">5</span> Angaben zu den Erziehungsberechtigten</legend>
			<div class="subhead">Mutter</div>
			<div class="grid">
				<div class="full"><label>Name und Vorname der Mutter</label><input type="text" name="mutter_name"></div>
				<div class="full"><label>Anschrift (falls abweichend) – Straße, Haus-Nr., PLZ, Ort</label><input type="text" name="mutter_anschrift"></div>
				<div><label>Telefon</label><input type="tel" name="mutter_telefon"></div>
				<div><label>Erreichbarkeit in Notfällen</label><input type="tel" name="mutter_notfall"></div>
			</div>
			<div class="subhead">Vater</div>
			<div class="grid">
				<div class="full"><label>Name und Vorname des Vaters</label><input type="text" name="vater_name"></div>
				<div class="full"><label>Anschrift (falls abweichend) – Straße, Haus-Nr., PLZ, Ort</label><input type="text" name="vater_anschrift"></div>
				<div><label>Telefon</label><input type="tel" name="vater_telefon"></div>
				<div><label>Erreichbarkeit in Notfällen</label><input type="tel" name="vater_notfall"></div>
			</div>
		</fieldset>

		<!-- 6 Sorgerecht -->
		<fieldset><legend><span class="n">6</span> Angaben zur Sorgeberechtigung</legend>
			<p class="hint">In der Regel üben die Erziehungsberechtigten die gemeinsame Sorge aus. Zutreffendes bitte ausfüllen; Nachweise ggf. im Abschnitt „Nachweise" beifügen.</p>
			<div class="subhead">Bei unverheirateten Partnern (§ 1626a, d BGB)</div>
			<div class="grid">
				<div><label>Liegt ein gemeinsames Sorgerecht vor?</label><?php echo aws_yesno( 'sr_gemeinsam' ); ?></div>
				<div><label>Vorlage einer Sorgerechtserklärung des Kindesvaters?</label><?php echo aws_yesno( 'sr_erklaerung_vater' ); ?></div>
			</div>
			<div class="subhead">Bei getrennt lebenden Sorgeberechtigten</div>
			<div class="grid">
				<div><label>Haben Sie das alleinige Sorgerecht?</label><?php echo aws_yesno( 'sr_allein' ); ?></div>
				<div><label>Gerichtsurteil/Sorgerechtserklärung vorgelegt?</label><?php echo aws_yesno( 'sr_urteil' ); ?></div>
				<div class="full"><label>Die Schülerin / der Schüler lebt bei</label>
					<div class="radio"><label><input type="radio" name="lebt_bei" value="Mutter"> der Mutter</label><label><input type="radio" name="lebt_bei" value="Vater"> dem Vater</label><label><input type="radio" name="lebt_bei" value="andere"> andere:</label></div>
					<input type="text" name="lebt_bei_andere" placeholder="falls andere Person/Einrichtung" style="margin-top:.4rem"></div>
			</div>
			<div class="subhead">Bei Heimunterbringung</div>
			<div class="grid">
				<div><label>Alleiniges Sorgerecht?</label><?php echo aws_yesno( 'heim_allein' ); ?></div>
				<div><label>Gerichtsurteil vorgelegt?</label><?php echo aws_yesno( 'heim_urteil' ); ?></div>
				<div><label>Verfügung vom Jugendamt vorhanden/vorgelegt?</label><?php echo aws_yesno( 'heim_jugendamt' ); ?></div>
			</div>
		</fieldset>

		<!-- 7 Mitschuelerwunsch -->
		<fieldset><legend><span class="n">7</span> Mitschüler/in-Wunsch <span class="opt">(optional)</span></legend>
			<p class="hint">Wir versuchen, Wünsche unter Berücksichtigung pädagogischer und schulorganisatorischer Aspekte zu realisieren – ein Anspruch auf Erfüllung entsteht daraus nicht.</p>
			<div class="subhead">Bisherige Grundschule</div>
			<div class="check stack">
				<label><input type="radio" name="gs" value="Grundschule Dörverden"> Grundschule Dörverden</label>
				<label><input type="radio" name="gs" value="Grundschule Dörverden/Barme"> Grundschule Dörverden/Barme</label>
				<label><input type="radio" name="gs" value="Grundschule Westen"> Grundschule Westen</label>
				<label><input type="radio" name="gs" value="andere"> andere Grundschule</label>
			</div>
			<div class="grid" style="margin-top:.7rem">
				<div><label>Klasse</label><input type="text" name="gs_klasse"></div>
				<div><label>Name der anderen Grundschule (falls zutreffend)</label><input type="text" name="gs_andere"></div>
				<div><label>Wunsch 1 (Vor- und Nachname)</label><input type="text" name="wunsch1"></div>
				<div><label>Wunsch 2 (Vor- und Nachname)</label><input type="text" name="wunsch2"></div>
			</div>
		</fieldset>

		<!-- 8 Gesundheit -->
		<fieldset><legend><span class="n">8</span> Mitteilung über Erkrankungen <span class="opt">(optional)</span></legend>
			<p class="hint">Nur ausfüllen, wenn zutreffend. Angaben werden vertraulich behandelt.</p>
			<div class="grid">
				<div class="full"><label>In Behandlung wegen</label><input type="text" name="krank_behandlung"></div>
				<div><label>bei Frau/Herrn Dr.</label><input type="text" name="krank_arzt"></div>
				<div><label>Adresse / Tel.-Nr. der Praxis</label><input type="text" name="krank_adresse"></div>
				<div class="full"><label>Erkrankungen / Allergien</label><textarea name="krank_erkrankungen"></textarea></div>
				<div class="full"><label>Notfallmaßnahme</label><input type="text" name="krank_notfallmassnahme"></div>
				<div><label>Notfallmedikament</label><input type="text" name="krank_notfallmedikament"></div>
				<div><label>Verabreichung/Handhabung im Notfall</label><input type="text" name="krank_handhabung"></div>
			</div>
		</fieldset>

		<!-- 9 Impfstatus / Masernschutz -->
		<fieldset><legend><span class="n">9</span> Impfstatus – Masernschutz</legend>
			<p class="hint">Für die Aufnahme ist nach dem Masernschutzgesetz (§&nbsp;20 IfSG) ein Nachweis über den Masernschutz erforderlich. Bitte machen Sie eine Angabe und laden Sie den entsprechenden Nachweis (Impfpass o.&nbsp;Ä.) hoch – die Vorlage ist auch später im Sekretariat möglich.</p>
			<div class="subhead">Masernschutz meines Kindes <span class="req">*</span></div>
			<div class="check stack">
				<label><input type="radio" name="masern_status" value="Zwei Masernimpfungen" required> Zwei Masernimpfungen erfolgt</label>
				<label><input type="radio" name="masern_status" value="Immunität ärztlich bestätigt"> Immunität ärztlich bestätigt</label>
				<label><input type="radio" name="masern_status" value="Ärztliche Kontraindikation"> Ärztlich bescheinigte Kontraindikation (Impfung nicht möglich)</label>
				<label><input type="radio" name="masern_status" value="Nachweis wird nachgereicht"> Nachweis wird nachgereicht</label>
			</div>
			<div class="grid" style="margin-top:.8rem">
				<div class="full file"><label>Nachweis Masernschutz / Impfpass hochladen <span class="opt">(PDF, JPG, PNG)</span></label><input type="file" name="datei_impfnachweis" accept=".pdf,.jpg,.jpeg,.png"></div>
			</div>
		</fieldset>

		<!-- 10 Schulbuchausleihe -->
		<fieldset><legend><span class="n">10</span> Schulbuchausleihe</legend>
			<p class="hint">Die Schule bietet die entgeltliche Ausleihe der Schulbücher an. Für neue Schülerinnen und Schüler können Sie die Teilnahme gleich hier mit anmelden – die genauen Unterlagen (Bücherliste, Beträge, Bankverbindung/SEPA) sendet Ihnen das Sekretariat anschließend zu.</p>
			<div class="subhead">Möchten Sie am Schulbuchausleih-Verfahren teilnehmen?</div>
			<div class="radio"><label><input type="radio" name="buch_teilnahme" value="ja"> ja, wir möchten teilnehmen</label><label><input type="radio" name="buch_teilnahme" value="nein"> nein, wir kaufen die Bücher selbst</label></div>
			<div class="subhead">Ermäßigung / Befreiung <span class="opt">(falls zutreffend)</span></div>
			<div class="check stack">
				<label><input type="checkbox" name="buch_ermaessigung" value="ja"> Anspruch auf <strong>Ermäßigung</strong> (mehrere schulpflichtige Kinder im Ausleihverfahren).</label>
				<label><input type="checkbox" name="buch_befreiung" value="ja"> Anspruch auf <strong>Befreiung</strong> (z.&nbsp;B. Bezug von Sozialleistungen) – Nachweis füge ich bei / reiche ich nach.</label>
			</div>
			<p class="hint">Einen Nachweis für Ermäßigung/Befreiung können Sie unten im Abschnitt „Nachweise" hochladen.</p>
		</fieldset>

		<!-- 11 Nachweise -->
		<fieldset><legend><span class="n">11</span> Nachweise (Upload)</legend>
			<p class="hint">Erlaubte Formate: PDF, JPG, PNG (max. 8&nbsp;MB je Datei). Unterlagen können auch später im Sekretariat nachgereicht werden.</p>
			<div class="grid">
				<div class="full file"><label>Kopie Versetzungszeugnis Klasse 3</label><input type="file" name="datei_zeugnis3" accept=".pdf,.jpg,.jpeg,.png"></div>
				<div class="full file"><label>Kopie Halbjahreszeugnis Klasse 4</label><input type="file" name="datei_zeugnis4" accept=".pdf,.jpg,.jpeg,.png"></div>
				<div class="full file"><label>Kopie Geburtsurkunde</label><input type="file" name="datei_geburtsurkunde" accept=".pdf,.jpg,.jpeg,.png"></div>
				<div class="full file"><label>Nachweis Ermäßigung / Befreiung (falls zutreffend)</label><input type="file" name="datei_ermaessigung" accept=".pdf,.jpg,.jpeg,.png"></div>
				<div class="full file"><label>Nachweis Sorgerecht / gerichtliche Entscheidung (falls zutreffend)</label><input type="file" name="datei_sorgerecht" accept=".pdf,.jpg,.jpeg,.png"></div>
			</div>
		</fieldset>

		<!-- 12 Einwilligungen -->
		<fieldset><legend><span class="n">12</span> Einwilligungen &amp; Kenntnisnahmen</legend>
			<div class="subhead">Fotos auf der Homepage der Schule <span class="opt">(freiwillig, jederzeit widerrufbar)</span></div>
			<div class="check stack">
				<label><input type="checkbox" name="ew_homepage_fotos" value="ja"> Veröffentlichung von <strong>Fotos</strong> meines Kindes</label>
				<label><input type="checkbox" name="ew_homepage_name" value="ja"> Veröffentlichung des <strong>Vor- und Zunamens</strong></label>
			</div>
			<div class="subhead">Bilder in der lokalen Zeitung/Presse <span class="opt">(freiwillig, jederzeit widerrufbar)</span></div>
			<div class="check stack">
				<label><input type="checkbox" name="ew_zeitung_fotos" value="ja"> Veröffentlichung von <strong>Fotos</strong> (z. B. Abschlussfeier, Projektwoche, Einschulung)</label>
				<label><input type="checkbox" name="ew_zeitung_name" value="ja"> Veröffentlichung des <strong>Vor- und Zunamens</strong></label>
			</div>
			<div class="subhead">IServ (Kommunikationsplattform)</div>
			<div class="check stack">
				<label><input type="checkbox" name="iserv_benutzerordnung" value="ja"> Ich habe die <strong>Benutzerordnung für IServ</strong> gelesen und verstanden.</label>
			</div>
			<div style="margin-top:.5rem"><label>Mein Kind darf im Adressbuch weitere Daten eintragen:</label><?php echo aws_yesno( 'iserv_adressbuch' ); ?></div>
			<div class="subhead">Informationen zur Einschulung – zur Kenntnis genommen und akzeptiert</div>
			<div class="check stack">
				<label><input type="checkbox" name="kn_schulregeln" value="ja"> Schulregeln &amp; Schulordnung der Aller-Weser-Oberschule</label>
				<label><input type="checkbox" name="kn_waffen" value="ja"> Verbot des Mitbringens von Waffen, Munition u.&nbsp;Ä.</label>
				<label><input type="checkbox" name="kn_infektion" value="ja"> Infektionsschutzbelehrung / Kopflausbefall</label>
				<label><input type="checkbox" name="kn_turnhalle" value="ja"> Sport- und Turnhallennutzungsordnung</label>
				<label><input type="checkbox" name="kn_webuntis" value="ja"> Informationen zum Stundenplanprogramm WebUntis</label>
				<label><input type="checkbox" name="kn_mittagspause" value="ja"> Erlaubnis zum Verlassen des Schulgeländes in der Mittagspause</label>
				<label><input type="checkbox" name="kn_entschuldigung" value="ja"> Entschuldigungsvordruck im Krankheitsfall</label>
			</div>
			<div class="subhead">Datenschutz</div>
			<div class="check stack">
				<label><input type="checkbox" name="datenschutz" value="ja" required> Ich habe die <strong>Datenschutzhinweise</strong> gelesen und willige in die Verarbeitung der angegebenen Daten zum Zweck der Anmeldung/Aufnahme ein. <span class="req">*</span></label>
			</div>
			<p class="hint" style="margin-top:1rem">Bei gemeinsamem Sorgerecht ist die Bestätigung beider Erziehungsberechtigten erforderlich. Verantwortlich: Aller-Weser-Oberschule Dörverden, Am Sünderberg 6, 27313 Dörverden.</p>
			<?php
			$cap_a   = wp_rand( 1, 9 );
			$cap_b   = wp_rand( 1, 9 );
			$cap_ts  = time();
			$cap_tok = hash_hmac( 'sha256', ( $cap_a + $cap_b ) . '|' . $cap_ts, wp_salt( 'auth' ) );
			?>
			<div class="subhead">Sicherheitsfrage <span class="req">*</span></div>
			<p class="hint">Zum Schutz vor automatisierten Anmeldungen bitte kurz bestätigen.</p>
			<div class="grid"><div><label>Wie viel ist <?php echo (int) $cap_a; ?> + <?php echo (int) $cap_b; ?>?</label><input type="number" name="aws_cap" inputmode="numeric" autocomplete="off" required></div></div>
			<input type="hidden" name="aws_cap_ts" value="<?php echo (int) $cap_ts; ?>">
			<input type="hidden" name="aws_cap_tok" value="<?php echo esc_attr( $cap_tok ); ?>">
			<div class="actions"><button type="submit" class="btn">Anmeldung absenden</button></div>
		</fieldset>
	</form>
	<?php
	return ob_get_clean();
}
add_shortcode( 'aws_anmeldung', 'aws_anmeldung_form' );

/** XML-Escape für den DaNiS-Export. */
function aws_xml_esc( $s ) {
	return htmlspecialchars( (string) $s, ENT_QUOTES | ENT_XML1, 'UTF-8' );
}

/** Erzeugt eine UUID (v4) für DaNiS-GUIDs. */
function aws_uuid4() {
	$d    = random_bytes( 16 );
	$d[6] = chr( ( ord( $d[6] ) & 0x0f ) | 0x40 );
	$d[8] = chr( ( ord( $d[8] ) & 0x3f ) | 0x80 );
	$h    = bin2hex( $d );
	return substr( $h, 0, 8 ) . '-' . substr( $h, 8, 4 ) . '-' . substr( $h, 12, 4 ) . '-' . substr( $h, 16, 4 ) . '-' . substr( $h, 20, 12 );
}

/** Zerlegt eine freie "PLZ Ort"-Angabe grob in array( plz, ort ). */
function aws_split_plz_ort( $s ) {
	$s   = trim( (string) $s );
	$plz = '';
	if ( preg_match( '/(\d{5})/', $s, $m ) ) {
		$plz = $m[1];
		$s   = trim( str_replace( $m[1], '', $s ), " ,\t" );
	}
	return array( $plz, trim( $s, " ,\t" ) );
}

/** Zerlegt eine freie Gesamtadresse ("Straße, PLZ Ort") grob in array( strasse, plz, ort ). */
function aws_split_address( $s ) {
	$s = trim( (string) $s );
	if ( preg_match( '/^(.*?)(\d{5})\s*(.*)$/', $s, $m ) ) {
		return array( trim( $m[1], " ,\t" ), $m[2], trim( $m[3], " ,\t" ) );
	}
	return array( $s, '', '' );
}

/** Zerlegt einen freien Namen grob in array( vorname, nachname ). */
function aws_split_name( $full ) {
	$full = trim( preg_replace( '/\s+/', ' ', (string) $full ) );
	if ( '' === $full ) {
		return array( '', '' );
	}
	if ( false !== strpos( $full, ',' ) ) {
		$p = array_map( 'trim', explode( ',', $full, 2 ) );
		return array( isset( $p[1] ) ? $p[1] : '', $p[0] ); // Format "Nachname, Vorname".
	}
	$parts = explode( ' ', $full );
	if ( count( $parts ) === 1 ) {
		return array( '', $parts[0] );
	}
	$nach = array_pop( $parts );
	return array( implode( ' ', $parts ), $nach );
}

/** Baut einen <person>-Block für DaNiS. */
function aws_danis_person( $ruf, $vornamen, $nach, $gebdat, $geschlecht, $strasse, $plz, $ort, $tel, $email ) {
	$x  = "    <person>\n";
	$x .= '      <rufname>' . aws_xml_esc( $ruf ) . "</rufname>\n";
	$x .= "      <vorname></vorname>\n";
	$x .= '      <vornamen>' . aws_xml_esc( $vornamen ) . "</vornamen>\n";
	$x .= '      <nachname>' . aws_xml_esc( $nach ) . "</nachname>\n";
	if ( '' !== $gebdat ) {
		$x .= '      <geburtsdatum>' . aws_xml_esc( $gebdat ) . "</geburtsdatum>\n";
	}
	if ( '' !== $geschlecht ) {
		$x .= '      <geschlecht>' . aws_xml_esc( $geschlecht ) . "</geschlecht>\n";
	}
	$x .= '      <guid>' . aws_uuid4() . "</guid>\n";
	$x .= "      <adressen>\n        <adresse>\n          <hauptwohnsitz>true</hauptwohnsitz>\n          <ort>\n";
	$x .= '            <name>' . aws_xml_esc( $ort ) . "</name>\n";
	$x .= '            <plz>' . aws_xml_esc( $plz ) . "</plz>\n";
	$x .= "          </ort>\n          <strasse>" . aws_xml_esc( $strasse ) . "</strasse>\n        </adresse>\n      </adressen>\n";
	$k = '';
	if ( '' !== $tel ) {
		$k .= "        <kontakt typ=\"kaTelefon\">\n          <text>" . aws_xml_esc( $tel ) . "</text>\n        </kontakt>\n";
	}
	if ( '' !== $email ) {
		$k .= "        <kontakt typ=\"kaEMail\">\n          <text>" . aws_xml_esc( $email ) . "</text>\n        </kontakt>\n";
	}
	$x .= ( '' !== $k ) ? "      <kontakte>\n" . $k . "      </kontakte>\n" : "      <kontakte/>\n";
	$x .= "    </person>\n";
	return $x;
}

/** Erzeugt die DaNiS-Importdatei (XML 1.5) aus den Anmeldedaten. */
function aws_anmeldung_build_danis_xml( $v ) {
	$sj_start = apply_filters( 'aws_anmeldung_schuljahr_start', '2026-08-01' );
	$sj_label = apply_filters( 'aws_anmeldung_schuljahr_label', '26/27' );

	$klasse       = $v( 'klassenstufe' );
	$vorname_full = $v( 'vorname' );
	$ruf          = trim( (string) strtok( $vorname_full, ' ' ) );
	$nach         = $v( 'familienname' );
	$gebdat       = $v( 'geburtstag' );
	$g            = $v( 'geschlecht' );
	$geschlecht   = ( 'weiblich' === $g ) ? 'gWeiblich' : ( ( 'männlich' === $g ) ? 'gMaennlich' : '' );
	list( $plz, $ort ) = aws_split_plz_ort( $v( 'plz_ort' ) );
	$strasse      = $v( 'anschrift' );
	$tel          = $v( 'telefon' );
	$email        = $v( 'email' );

	$x  = '<?xml version="1.0"?>' . "\n";
	$x .= '<danis xmlns="http://www.nils.nibis.de/DaNiS" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.nils.nibis.de/DaNiS danis.xsd" version="1.5" programmversion="3.10" dbversion="3.10">' . "\n";
	$x .= "  <schueler>\n";
	$x .= aws_danis_person( $ruf, $vorname_full, $nach, $gebdat, $geschlecht, $strasse, $plz, $ort, $tel, $email );
	$x .= '    <aufnahmedatum>' . aws_xml_esc( $sj_start ) . "</aufnahmedatum>\n";

	$vonschule = $v( 'aufnahme_vonschule' );
	if ( '' === $vonschule ) {
		$vonschule = $v( 'grundschule_name' );
	}
	if ( '' === $vonschule ) {
		$gs        = $v( 'gs' );
		$vonschule = ( 'andere' === $gs ) ? $v( 'gs_andere' ) : $gs;
	}
	if ( '' !== $vonschule ) {
		$x .= '    <vonschule>' . aws_xml_esc( $vonschule ) . "</vonschule>\n";
	}

	$einschul = $v( 'einschulungsjahr' );
	if ( '' !== $einschul ) {
		$x .= '    <ersteinschulJahr>' . aws_xml_esc( $einschul ) . "</ersteinschulJahr>\n";
		$x .= '    <beginnschulpflicht>' . aws_xml_esc( $einschul ) . "</beginnschulpflicht>\n";
	}

	$konf = $v( 'konfession' );
	if ( '' === $konf ) {
		$konf = $v( 'konfession_sonstige' );
	}
	if ( '' !== $konf ) {
		$kz = array( 'evangelisch' => '1', 'katholisch' => '2' );
		if ( isset( $kz[ $konf ] ) ) {
			$x .= '    <konfession kennzahl="' . $kz[ $konf ] . '">' . aws_xml_esc( $konf ) . "</konfession>\n";
		} else {
			$x .= '    <konfession>' . aws_xml_esc( $konf ) . "</konfession>\n";
		}
	}

	$gebort = $v( 'geburtsort' );
	if ( '' !== $gebort ) {
		$x .= '    <geburtsort>' . aws_xml_esc( $gebort ) . "</geburtsort>\n";
	}

	$staat = $v( 'staatsangehoerigkeit' );
	if ( '' === $staat || false !== stripos( $staat, 'deutsch' ) ) {
		$x .= '    <staatsangehoerigkeitiso code="DE">Deutschland</staatsangehoerigkeitiso>' . "\n";
	} else {
		$x .= '    <staatsangehoerigkeitiso>' . aws_xml_esc( $staat ) . "</staatsangehoerigkeitiso>\n";
	}

	// Verantwortliche (Mutter / Vater), soweit angegeben.
	$resp    = '';
	$parents = array(
		array( $v( 'mutter_name' ), $v( 'mutter_anschrift' ), $v( 'mutter_telefon' ), 'vaMutter', 'gWeiblich' ),
		array( $v( 'vater_name' ), $v( 'vater_anschrift' ), $v( 'vater_telefon' ), 'vaVater', 'gMaennlich' ),
	);
	foreach ( $parents as $p ) {
		if ( '' === trim( $p[0] ) ) {
			continue;
		}
		list( $pvor, $pnach ) = aws_split_name( $p[0] );
		if ( '' === $pnach ) {
			$pnach = $nach;
		}
		$pstr = $strasse;
		$pplz = $plz;
		$port = $ort;
		if ( '' !== trim( $p[1] ) ) {
			list( $astr, $aplz, $aort ) = aws_split_address( $p[1] );
			$pstr = ( '' !== $astr ) ? $astr : $p[1];
			if ( '' !== $aplz ) {
				$pplz = $aplz;
				$port = ( '' !== $aort ) ? $aort : $ort;
			}
		}
		$prolle = ( 'vaMutter' === $p[3] ) ? 'Mutter' : 'Vater';
		$resp  .= "    <verantwortlicher>\n";
		$resp  .= aws_danis_person( ( '' !== $pvor ? $pvor : $prolle ), $pvor, $pnach, '', $p[4], $pstr, $pplz, $port, $p[2], '' );
		$resp  .= "      <erziehungsberechtigt>true</erziehungsberechtigt>\n";
		$resp  .= '      <art>' . $p[3] . "</art>\n";
		$resp  .= "      <erziehungsberechtigtdefault>true</erziehungsberechtigtdefault>\n";
		$resp  .= '      <artdefault>' . $p[3] . "</artdefault>\n";
		$resp  .= "    </verantwortlicher>\n";
	}
	if ( '' !== $resp ) {
		$x .= "    <verantwortliche>\n" . $resp . "    </verantwortliche>\n";
	}

	if ( '5' === $klasse ) {
		$x .= '    <herkunft kennzahl="01">Versetzung/Zugang aus dem vorhergehenden SJG - Grundschule</herkunft>' . "\n";
	}

	if ( 'Zwei Masernimpfungen' === $v( 'masern_status' ) ) {
		$x .= "    <infektionsschutz>\n";
		$x .= '      <nachweisdatum>' . aws_xml_esc( current_time( 'Y-m-d' ) ) . "</nachweisdatum>\n";
		$x .= "      <nachweisart>Geimpft</nachweisart>\n";
		$x .= "    </infektionsschutz>\n";
	}

	$x .= "    <jahrgangsdatenliste>\n      <jahrgangsdaten>\n";
	$x .= "        <schulname>Aller-Weser-Oberschule Dörverden</schulname>\n";
	$x .= '        <schulgliederungsbezeichnung>' . aws_xml_esc( $sj_label ) . "</schulgliederungsbezeichnung>\n";
	$x .= '        <schulform kennzahl="40">Oberschule</schulform>' . "\n";
	$x .= "        <gruppenbezeichnung></gruppenbezeichnung>\n";
	$x .= '        <jahrgangsstufe>' . aws_xml_esc( $klasse ) . "</jahrgangsstufe>\n";
	$x .= "        <foerderung>keine Förderung</foerderung>\n";
	$x .= "        <belegungen/>\n        <sonderpaedbedarfe/>\n";
	$x .= "      </jahrgangsdaten>\n    </jahrgangsdatenliste>\n";
	$x .= "  </schueler>\n</danis>\n";
	return $x;
}

/** Baut die E-Mail als druckbares Bestätigungs-Dokument (HTML) mit Unterschriftszeilen. */
function aws_anmeldung_build_confirmation_html( $sections, $v, $vb, $attached_names ) {
	$sj_label = apply_filters( 'aws_anmeldung_schuljahr_label', '26/27' );

	$rows = '';
	foreach ( $sections as $title => $fields ) {
		$rows .= '<tr><td colspan="2" style="background:#e4eef3;color:#0b4680;font-weight:bold;padding:8px 10px;border-top:1px solid #cbdce6;">' . esc_html( $title ) . '</td></tr>';
		foreach ( $fields as $key => $label ) {
			if ( 0 === strpos( $key, '_cb_' ) ) {
				$value = $vb( substr( $key, 4 ) );
			} elseif ( 'krank_erkrankungen' === $key ) {
				$value = sanitize_textarea_field( wp_unslash( isset( $_POST[ $key ] ) ? $_POST[ $key ] : '' ) );
			} else {
				$value = $v( $key );
			}
			if ( '' === $value ) {
				$value = '—';
			}
			$rows .= '<tr><td style="padding:5px 10px;border-top:1px solid #eef2f4;color:#51707a;width:45%;vertical-align:top;">' . esc_html( $label ) . '</td><td style="padding:5px 10px;border-top:1px solid #eef2f4;color:#0f2a33;">' . nl2br( esc_html( $value ) ) . '</td></tr>';
		}
	}

	$nach   = esc_html( $v( 'familienname' ) );
	$vor    = esc_html( $v( 'vorname' ) );
	$klasse = esc_html( $v( 'klassenstufe' ) );
	$zeit   = esc_html( current_time( 'd.m.Y H:i' ) );
	$nachw  = $attached_names ? esc_html( implode( ', ', $attached_names ) ) : 'Keine Dateien hochgeladen (ggf. Nachreichung).';

	$h  = '<!DOCTYPE html><html lang="de"><head><meta charset="UTF-8"></head>';
	$h .= '<body style="margin:0;background:#ffffff;color:#0f2a33;font-family:Arial,Helvetica,sans-serif;font-size:14px;line-height:1.5;">';
	$h .= '<div style="max-width:720px;margin:0 auto;padding:24px;">';
	$h .= '<div style="border-bottom:3px solid #1B6CA3;padding-bottom:10px;margin-bottom:16px;">';
	$h .= '<div style="font-size:18px;font-weight:bold;color:#0b4680;">Aller-Weser-Oberschule Dörverden</div>';
	$h .= '<div style="color:#51707a;font-size:12px;">Am Sünderberg 6, 27313 Dörverden · Tel. 04234 1034</div>';
	$h .= '</div>';
	$h .= '<h1 style="font-size:20px;margin:0 0 4px;">Anmeldung – Bestätigung der Angaben</h1>';
	$h .= '<p style="margin:0 0 14px;color:#51707a;">Schuljahr ' . esc_html( $sj_label ) . ' · ' . $vor . ' ' . $nach . ' · Klasse ' . $klasse . ' · eingegangen am ' . $zeit . '</p>';
	$h .= '<p style="margin:0 0 16px;">Bitte prüfen Sie die folgenden Angaben sorgfältig auf ihre Richtigkeit und bestätigen Sie diese mit Ihrer Unterschrift.</p>';
	$h .= '<table style="width:100%;border-collapse:collapse;border:1px solid #cbdce6;font-size:13px;">' . $rows . '</table>';
	$h .= '<p style="margin:16px 0 0;font-size:13px;color:#51707a;"><strong>Beigefügte Nachweise:</strong> ' . $nachw . '</p>';
	$h .= '<div style="margin-top:22px;padding:14px 16px;border:1px solid #cbdce6;border-radius:6px;background:#f6fafb;">';
	$h .= '<p style="margin:0 0 20px;"><strong>Bestätigung:</strong> Ich/Wir bestätige(n) die Richtigkeit und Vollständigkeit der oben gemachten Angaben.</p>';
	$h .= '<table style="width:100%;border-collapse:collapse;"><tr>';
	$h .= '<td style="width:50%;padding:22px 10px 0 0;vertical-align:bottom;"><div style="border-top:1px solid #0f2a33;padding-top:4px;color:#51707a;font-size:12px;">Ort, Datum</div></td>';
	$h .= '<td style="width:50%;padding:22px 0 0 10px;vertical-align:bottom;"><div style="border-top:1px solid #0f2a33;padding-top:4px;color:#51707a;font-size:12px;">Unterschrift Erziehungsberechtigte/r 1</div></td>';
	$h .= '</tr><tr>';
	$h .= '<td style="padding:30px 10px 0 0;"></td>';
	$h .= '<td style="padding:30px 0 0 10px;vertical-align:bottom;"><div style="border-top:1px solid #0f2a33;padding-top:4px;color:#51707a;font-size:12px;">Unterschrift Erziehungsberechtigte/r 2</div></td>';
	$h .= '</tr></table>';
	$h .= '</div>';
	$h .= '<p style="margin:18px 0 0;font-size:11px;color:#8aa3ac;">Dieses Dokument wurde automatisch aus der Online-Anmeldung der Website erzeugt. Eine DaNiS-Importdatei (XML) für das Schulverwaltungsprogramm ist dieser E-Mail beigefügt. Bei gemeinsamem Sorgerecht ist die Unterschrift beider Erziehungsberechtigten erforderlich.</p>';
	$h .= '</div></body></html>';
	return $h;
}

/**
 * Verarbeitet die abgesendete Anmeldung und versendet sie per E-Mail.
 */
function aws_anmeldung_handle() {
	$back = wp_get_referer();
	if ( ! $back ) {
		$back = home_url( '/' );
	}

	// CSRF-Schutz.
	if ( ! isset( $_POST['aws_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['aws_nonce'] ) ), 'aws_anmeldung' ) ) {
		wp_safe_redirect( add_query_arg( 'aws_anmeldung', 'error', $back ) );
		exit;
	}

	// Spam-Schutz (Honeypot): stumm als "ok" behandeln.
	if ( ! empty( $_POST['aws_hp'] ) ) {
		wp_safe_redirect( add_query_arg( 'aws_anmeldung', 'ok', $back ) );
		exit;
	}

	// Sicherheitsfrage (Rechenaufgabe) + Zeitfalle – ohne externe Dienste, serverseitig geprüft.
	$cap_ans = isset( $_POST['aws_cap'] ) ? preg_replace( '/\D+/', '', (string) wp_unslash( $_POST['aws_cap'] ) ) : '';
	$cap_ts  = isset( $_POST['aws_cap_ts'] ) ? absint( $_POST['aws_cap_ts'] ) : 0;
	$cap_tok = isset( $_POST['aws_cap_tok'] ) ? (string) wp_unslash( $_POST['aws_cap_tok'] ) : '';
	$cap_cmp = ( '' !== $cap_ans && $cap_ts > 0 ) ? hash_hmac( 'sha256', $cap_ans . '|' . $cap_ts, wp_salt( 'auth' ) ) : '';
	$cap_age = time() - $cap_ts;
	if ( '' === $cap_tok || '' === $cap_cmp || ! hash_equals( $cap_cmp, $cap_tok ) || $cap_age < 4 || $cap_age > 21600 ) {
		wp_safe_redirect( add_query_arg( 'aws_anmeldung', 'error', $back ) );
		exit;
	}

	$v = function ( $key ) {
		return isset( $_POST[ $key ] ) ? sanitize_text_field( wp_unslash( $_POST[ $key ] ) ) : '';
	};
	$vb = function ( $key ) {
		return ! empty( $_POST[ $key ] ) ? 'Ja' : '—';
	};

	// Minimale Pflichtprüfung.
	if ( '' === $v( 'familienname' ) || '' === $v( 'vorname' ) || '' === $v( 'masern_status' ) || empty( $_POST['datenschutz'] ) ) {
		wp_safe_redirect( add_query_arg( 'aws_anmeldung', 'error', $back ) );
		exit;
	}

	// Textfelder (Name => Label), gruppiert für die E-Mail.
	$sections = array(
		'Angaben zum Schulkind' => array(
			'klassenstufe' => 'Klassenstufe', 'familienname' => 'Familienname', 'vorname' => 'Vorname(n)',
			'geschlecht' => 'Geschlecht', 'staatsangehoerigkeit' => 'Staatsangehörigkeit', 'geburtstag' => 'Geburtstag',
			'geburtsort' => 'Geburtsort', 'herkunftssprache' => 'Herkunftssprache', 'konfession' => 'Konfession',
			'konfession_sonstige' => 'Konfession (sonstige)', 'anschrift' => 'Anschrift', 'plz_ort' => 'PLZ, Ort',
			'telefon' => 'Telefon', 'email' => 'E-Mail', 'handy' => 'Handy / Notfall-Telefon',
		),
		'Religionsunterricht' => array(
			'rel_teilnahme' => 'Teilnahme Religionsunterricht', 'rel_art' => 'Art',
		),
		'Schullaufbahn & Förderung' => array(
			'foerderbedarf' => 'Förderbedarf', 'foerder_schwerpunkt' => 'Schwerpunkt', 'wiederholung' => 'Wiederholung einer Klasse',
			'wiederholung_welche' => 'Welche Klasse', 'aufnahme_ab' => 'Aufnahme ab', 'aufnahme_vonschule' => 'von Schule',
			'einschulungsjahr' => 'Einschulungsjahr Grundschule', 'grundschule_name' => 'Name der Grundschule',
		),
		'Schülerbeförderung' => array(
			'befoerderung' => 'Beförderung erforderlich', 'landkreis_einwilligung' => 'Einwilligung Datenweitergabe Landkreis Verden',
		),
		'Erziehungsberechtigte' => array(
			'mutter_name' => 'Mutter – Name, Vorname', 'mutter_anschrift' => 'Mutter – Anschrift', 'mutter_telefon' => 'Mutter – Telefon', 'mutter_notfall' => 'Mutter – Notfall',
			'vater_name' => 'Vater – Name, Vorname', 'vater_anschrift' => 'Vater – Anschrift', 'vater_telefon' => 'Vater – Telefon', 'vater_notfall' => 'Vater – Notfall',
		),
		'Sorgeberechtigung' => array(
			'sr_gemeinsam' => 'Gemeinsames Sorgerecht (unverh.)', 'sr_erklaerung_vater' => 'Sorgerechtserklärung Vater vorgelegt',
			'sr_allein' => 'Alleiniges Sorgerecht (getrennt)', 'sr_urteil' => 'Gerichtsurteil vorgelegt',
			'lebt_bei' => 'Kind lebt bei', 'lebt_bei_andere' => 'Kind lebt bei (andere)',
			'heim_allein' => 'Heim – alleiniges Sorgerecht', 'heim_urteil' => 'Heim – Gerichtsurteil vorgelegt', 'heim_jugendamt' => 'Heim – Verfügung Jugendamt',
		),
		'Mitschüler-Wunsch' => array(
			'gs' => 'Bisherige Grundschule', 'gs_andere' => 'Andere Grundschule', 'gs_klasse' => 'Klasse',
			'wunsch1' => 'Wunsch 1', 'wunsch2' => 'Wunsch 2',
		),
		'Mitteilung über Erkrankungen' => array(
			'krank_behandlung' => 'In Behandlung wegen', 'krank_arzt' => 'Arzt/Ärztin', 'krank_adresse' => 'Praxis Adresse/Tel.',
			'krank_erkrankungen' => 'Erkrankungen/Allergien', 'krank_notfallmassnahme' => 'Notfallmaßnahme',
			'krank_notfallmedikament' => 'Notfallmedikament', 'krank_handhabung' => 'Handhabung im Notfall',
		),
		'Impfstatus (Masernschutz)' => array(
			'masern_status' => 'Masernschutz',
		),
		'Schulbuchausleihe' => array(
			'buch_teilnahme' => 'Teilnahme am Ausleihverfahren',
			'_cb_buch_ermaessigung' => 'Anspruch auf Ermäßigung', '_cb_buch_befreiung' => 'Anspruch auf Befreiung',
		),
		'Einwilligungen & Kenntnisnahmen' => array(
			'_cb_ew_homepage_fotos' => 'Homepage: Fotos', '_cb_ew_homepage_name' => 'Homepage: Vor- und Zuname',
			'_cb_ew_zeitung_fotos' => 'Zeitung: Fotos', '_cb_ew_zeitung_name' => 'Zeitung: Vor- und Zuname',
			'_cb_iserv_benutzerordnung' => 'IServ-Benutzerordnung gelesen', 'iserv_adressbuch' => 'IServ-Adressbuch: weitere Daten',
			'_cb_kn_schulregeln' => 'Schulregeln & Schulordnung', '_cb_kn_waffen' => 'Waffenverbot', '_cb_kn_infektion' => 'Infektionsschutz',
			'_cb_kn_turnhalle' => 'Turnhallenordnung', '_cb_kn_webuntis' => 'WebUntis', '_cb_kn_mittagspause' => 'Mittagspause/Schulgelände',
			'_cb_kn_entschuldigung' => 'Entschuldigungsvordruck', '_cb_datenschutz' => 'Datenschutz-Einwilligung',
		),
	);

		// Datei-Uploads als Anhang (nur erlaubte Typen, temporär, danach löschen).
		$file_fields = array(
			'datei_zeugnis3'      => 'Versetzungszeugnis Kl. 3',
			'datei_zeugnis4'      => 'Halbjahreszeugnis Kl. 4',
			'datei_geburtsurkunde' => 'Geburtsurkunde',
			'datei_impfnachweis'  => 'Masernschutz/Impfpass',
			'datei_ermaessigung'  => 'Ermäßigung/Befreiung',
			'datei_sorgerecht'    => 'Sorgerecht',
		);
		$allowed        = array( 'pdf' => 1, 'jpg' => 1, 'jpeg' => 1, 'png' => 1 );
		$attachments    = array();
		$tmp_created    = array();
		$attached_names = array();
		foreach ( $file_fields as $field => $flabel ) {
			if ( empty( $_FILES[ $field ] ) || ! isset( $_FILES[ $field ]['error'] ) || UPLOAD_ERR_OK !== $_FILES[ $field ]['error'] ) {
				continue;
			}
			if ( $_FILES[ $field ]['size'] <= 0 || $_FILES[ $field ]['size'] > 8 * 1024 * 1024 ) {
				continue;
			}
			$name = sanitize_file_name( $_FILES[ $field ]['name'] );
			$ext  = strtolower( pathinfo( $name, PATHINFO_EXTENSION ) );
			if ( ! isset( $allowed[ $ext ] ) ) {
				continue;
			}
			$dest = trailingslashit( get_temp_dir() ) . uniqid( 'aws_', true ) . '_' . $name;
			if ( move_uploaded_file( $_FILES[ $field ]['tmp_name'], $dest ) ) {
				$attachments[]    = $dest;
				$tmp_created[]    = $dest;
				$attached_names[] = $flabel . ' (' . $name . ')';
			}
		}

		// DaNiS-Importdatei (XML) erzeugen und als Anhang beifügen.
		$xml      = aws_anmeldung_build_danis_xml( $v );
		$xml_name = 'DaNiS-Import-' . sanitize_file_name( $v( 'familienname' ) . '-' . $v( 'vorname' ) ) . '.xml';
		$xml_path = trailingslashit( get_temp_dir() ) . uniqid( 'aws_danis_', true ) . '_' . $xml_name;
		if ( false !== file_put_contents( $xml_path, $xml ) ) {
			$attachments[] = $xml_path;
			$tmp_created[] = $xml_path;
		}

		$subject = sprintf( 'Online-Anmeldung: %s, %s (Klasse %s)', $v( 'familienname' ), $v( 'vorname' ), $v( 'klassenstufe' ) );

		// E-Mail als druckbares Bestätigungs-Dokument (HTML) mit Unterschriftszeilen.
		$body = aws_anmeldung_build_confirmation_html( $sections, $v, $vb, $attached_names );

		$from    = apply_filters( 'aws_anmeldung_from', AWS_ANMELDUNG_FROM );
		$headers = array(
			'From: Online-Anmeldung <' . $from . '>',
			'Content-Type: text/html; charset=UTF-8',
		);
		$parent_mail = sanitize_email( wp_unslash( isset( $_POST['email'] ) ? $_POST['email'] : '' ) );
		if ( is_email( $parent_mail ) ) {
			$headers[] = 'Reply-To: ' . $parent_mail;
		}

		$to   = apply_filters( 'aws_anmeldung_recipient', AWS_ANMELDUNG_TO );
		$sent = wp_mail( $to, $subject, $body, $headers, $attachments );

	foreach ( $tmp_created as $f ) {
		@unlink( $f ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
	}

	wp_safe_redirect( add_query_arg( 'aws_anmeldung', $sent ? 'ok' : 'error', $back ) );
	exit;
}
add_action( 'admin_post_nopriv_aws_anmeldung', 'aws_anmeldung_handle' );
add_action( 'admin_post_aws_anmeldung', 'aws_anmeldung_handle' );
