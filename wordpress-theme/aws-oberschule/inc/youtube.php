<?php
/**
 * Aller-Weser-Oberschule – YouTube-„Neueste Folge“ (Schulhofgeflüster)
 *
 * Shortcode [aws_youtube]: zeigt eine klar als YouTube erkennbare Karte im
 * Footer. Aus Datenschutzgründen (Klick-zum-Laden) wird KEINE Verbindung zu
 * YouTube/Google aufgebaut, bevor die Besucher:innen auf „Folge abspielen“
 * klicken. Erst dann lädt ein youtube-nocookie.com-Player.
 *
 * Welches Video startet, wird serverseitig ermittelt (Server ↔ YouTube,
 * ohne Besucher-IP): die neueste Folge des Kanals. Danach läuft die
 * Uploads-Playlist (ältere Folgen) weiter.
 *
 * @package aws-oberschule
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Kanal-Handle (ohne @). Per Filter überschreibbar.
 */
function aws_youtube_handle() {
	return (string) apply_filters( 'aws_youtube_handle', 'Schulhofgeflüster' );
}

/**
 * Kanal-ID (UC…) ermitteln. Reihenfolge:
 *  1. fest vorgegeben (Konstante AWS_YOUTUBE_CHANNEL_ID oder Filter) – kein Abruf,
 *  2. aus dem Cache (1 Woche),
 *  3. einmalig serverseitig von der Kanalseite lesen.
 *
 * @return string Kanal-ID oder '' bei Misserfolg.
 */
function aws_youtube_channel_id() {
	$override = (string) apply_filters(
		'aws_youtube_channel_id',
		defined( 'AWS_YOUTUBE_CHANNEL_ID' ) ? AWS_YOUTUBE_CHANNEL_ID : ''
	);
	if ( '' !== $override ) {
		return $override;
	}

	$cached = get_transient( 'aws_yt_channel' );
	if ( false !== $cached ) {
		return (string) $cached; // Kann auch '' sein (negatives Caching).
	}

	$res = wp_remote_get(
		'https://www.youtube.com/@' . rawurlencode( aws_youtube_handle() ),
		array(
			'timeout'     => 6,
			'redirection' => 3,
			'headers'     => array( 'Accept-Language' => 'de' ),
		)
	);

	$id = '';
	if ( ! is_wp_error( $res ) && 200 === (int) wp_remote_retrieve_response_code( $res ) ) {
		$body = wp_remote_retrieve_body( $res );
		if ( preg_match( '/"(?:channelId|externalId|browseId)":"(UC[A-Za-z0-9_-]{22})"/', $body, $m ) ) {
			$id = $m[1];
		}
	}

	// Erfolg: 1 Woche cachen. Misserfolg: nur 1 Stunde (repariert sich von selbst).
	set_transient( 'aws_yt_channel', $id, $id ? WEEK_IN_SECONDS : HOUR_IN_SECONDS );
	return $id;
}

/**
 * Uploads-Playlist-ID (UU…) aus der Kanal-ID ableiten.
 *
 * @param string $channel_id Kanal-ID (UC…).
 * @return string Playlist-ID oder ''.
 */
function aws_youtube_uploads_id( $channel_id ) {
	return ( 0 === strpos( (string) $channel_id, 'UC' ) ) ? 'UU' . substr( $channel_id, 2 ) : '';
}

/**
 * ID des neuesten Videos über den öffentlichen RSS-Feed des Kanals ermitteln.
 *
 * @param string $channel_id Kanal-ID (UC…).
 * @return string Video-ID (11 Zeichen) oder ''.
 */
function aws_youtube_latest_video_id( $channel_id ) {
	if ( '' === (string) $channel_id ) {
		return '';
	}

	$cached = get_transient( 'aws_yt_latest' );
	if ( false !== $cached ) {
		return (string) $cached;
	}

	$res = wp_remote_get(
		'https://www.youtube.com/feeds/videos.xml?channel_id=' . rawurlencode( $channel_id ),
		array(
			'timeout'     => 6,
			'redirection' => 3,
		)
	);

	$vid = '';
	if ( ! is_wp_error( $res ) && 200 === (int) wp_remote_retrieve_response_code( $res ) ) {
		$body = wp_remote_retrieve_body( $res );
		// Der Feed ist chronologisch (neuestes zuerst) – erster Treffer genügt.
		if ( preg_match( '/<yt:videoId>([A-Za-z0-9_-]{11})<\/yt:videoId>/', $body, $m ) ) {
			$vid = $m[1];
		}
	}

	// Neue Folgen erscheinen so innerhalb von max. 6 Stunden.
	set_transient( 'aws_yt_latest', $vid, $vid ? 6 * HOUR_IN_SECONDS : HOUR_IN_SECONDS );
	return $vid;
}

/**
 * Shortcode [aws_youtube]: datenschutzfreundliche YouTube-Karte.
 */
function aws_youtube_shortcode() {
	$handle      = aws_youtube_handle();
	$channel_url = 'https://www.youtube.com/@' . rawurlencode( $handle );
	$channel_id  = aws_youtube_channel_id();
	$uploads     = aws_youtube_uploads_id( $channel_id );
	$video       = aws_youtube_latest_video_id( $channel_id );
	$can_embed   = ( '' !== $video || '' !== $uploads );

	ob_start();
	?>
	<div class="aws-yt<?php echo $can_embed ? '' : ' is-link'; ?>"
		<?php echo $video ? ' data-video="' . esc_attr( $video ) . '"' : ''; ?>
		<?php echo $uploads ? ' data-list="' . esc_attr( $uploads ) . '"' : ''; ?>>
		<div class="aws-yt-facade">
			<div class="aws-yt-top">
				<span class="aws-yt-logo" aria-hidden="true">
					<svg viewBox="0 0 28 20" width="28" height="20" focusable="false"><rect width="28" height="20" rx="5" fill="#FF0000"/><path d="M11 6 L19 10 L11 14 Z" fill="#fff"/></svg>
				</span>
				<span class="aws-yt-kicker">YouTube&#8201;·&#8201;Podcast</span>
			</div>
			<p class="aws-yt-title"><a href="<?php echo esc_url( $channel_url ); ?>" target="_blank" rel="noopener noreferrer">Schulhofgeflüster</a></p>
			<p class="aws-yt-sub"><?php echo $can_embed ? 'Unsere neueste Folge – direkt hier abspielen.' : 'Unser Schul-Podcast auf YouTube.'; ?></p>
			<?php if ( $can_embed ) : ?>
				<button type="button" class="aws-yt-load">
					<span class="aws-yt-play" aria-hidden="true"></span>
					Neueste Folge abspielen
				</button>
				<p class="aws-yt-note">Erst beim Klick wird YouTube geladen – vorher werden keine Daten an Google übertragen.</p>
			<?php else : ?>
				<a class="aws-yt-load aws-yt-link" href="<?php echo esc_url( $channel_url ); ?>" target="_blank" rel="noopener noreferrer">
					<span class="aws-yt-play" aria-hidden="true"></span>
					Zum YouTube-Kanal
				</a>
			<?php endif; ?>
		</div>
	</div>
	<?php
	return trim( ob_get_clean() );
}
add_shortcode( 'aws_youtube', 'aws_youtube_shortcode' );
