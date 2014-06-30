<?php
/**
 * Attendees RSS feed template
 *
 * @package WordPress
 * @subpackage Camptix_Events_Calendar
 */

// Hide all errors
error_reporting(0);

// Get feed data
$title = get_bloginfo( 'name' ) . ' | ' . __( 'Attendees', 'camptix-events-calendar' );

$description = get_bloginfo( 'description' );

$language = get_bloginfo( 'language' );

$copyright = '&#xA9; ' . date( 'Y' ) . ' ' . get_bloginfo( 'name' );

// Set RSS header
header( 'Content-Type: ' . feed_content_type( 'rss-http' ) . '; charset=' . get_option( 'blog_charset' ), true );

// Echo first line to prevent any extra characters at start of document
echo '<?xml version="1.0" encoding="' . get_option('blog_charset') . '"?'.'>'; ?>

<rss version="2.0"
	xmlns:content="http://purl.org/rss/1.0/modules/content/"
	xmlns:wfw="http://wellformedweb.org/CommentAPI/"
	xmlns:dc="http://purl.org/dc/elements/1.1/"
	xmlns:atom="http://www.w3.org/2005/Atom"
	xmlns:sy="http://purl.org/rss/1.0/modules/syndication/"
	xmlns:slash="http://purl.org/rss/1.0/modules/slash/"
	xmlns:itunes="http://www.itunes.com/dtds/podcast-1.0.dtd"
	<?php do_action( 'rss2_ns' ); ?>
>

<channel>
	<title><?php echo esc_html( $title ); ?></title>
	<atom:link href="<?php esc_url( self_link() ); ?>" rel="self" type="application/rss+xml" />
	<link><?php esc_url( bloginfo_rss('url') ) ?></link>
	<description><?php echo esc_html( $description ); ?></description>
	<lastBuildDate><?php echo esc_html( mysql2date( 'D, d M Y H:i:s +0000', get_lastpostmodified( 'GMT' ), false ) ); ?></lastBuildDate>
	<language><?php echo esc_html( $language ); ?></language>
	<copyright><?php echo esc_html( $copyright ); ?></copyright><?php

	// Fetch attendees
	$args = array(
		'post_type' => 'tix_attendee',
		'post_status' => 'publish',
		'posts_per_page' => -1
	);

	// Fetch attendees for specific event if specified
	if( isset( $_GET['event'] ) && intval( $_GET['event'] ) > 0 ) {
		$tickets = '';
		$tickets = get_post_meta( intval( $_GET['event'] ), '_event_tickets', true );
		if( is_array( $tickets ) && 0 < count( $tickets ) ) {
			$args['meta_query'][] = array(
				'key' => 'tix_ticket_id',
				'value' => $tickets,
				'compare' => 'IN',
			);
		}
	}

	// Run query
	$qry = new WP_Query( $args );

	if( $qry->have_posts() ) :
		while( $qry->have_posts()) : $qry->the_post();

		// Item author
		$author = esc_html( get_the_title() );

		// Item content
		$content = sprintf( __( '%1$s has booked', 'camptix-events-calendar' ), esc_html( get_the_title() ) );

		// Add ticket info
		$ticket_id = intval( get_post_meta( get_the_ID(), 'tix_ticket_id', true ) );
		$ticket = get_post( $ticket_id );
		if ( $ticket ) {
			$content .= sprintf( __( ' for %1$s', 'camptix-events-calendar' ), esc_html( $ticket->post_title ) );
		}

	?>
	<item>
		<title><?php esc_html( the_title_rss() ); ?></title>
		<link><?php esc_url( the_permalink_rss() ); ?></link>
		<pubDate><?php echo esc_html( mysql2date( 'D, d M Y H:i:s +0000', get_post_time( 'Y-m-d H:i:s', true ), false ) ); ?></pubDate>
		<dc:creator><?php echo $author; ?></dc:creator>
		<guid isPermaLink="false"><?php esc_html( the_guid() ); ?></guid>
		<content:encoded><![CDATA[<?php echo $content; ?>]]></content:encoded>
	</item><?php endwhile; endif; ?>
</channel>
</rss><?php exit; ?>