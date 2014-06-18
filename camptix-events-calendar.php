<?php
/*
 * Plugin Name: CampTix & Events Calendar for Meetup Groups
 * Version: 1.0
 * Plugin URI: http://www.hughlashbrooke.com/
 * Description: Making CampTix and The Events Calendar work in harmony for the benefit of your meetup group.
 * Author: Hugh Lashbrooke
 * Author URI: http://www.hughlashbrooke.com/
 * Requires at least: 3.8
 * Tested up to: 3.9.1
 *
 * @package WordPress
 * @author Hugh Lashbrooke
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit;

// Include plugin class files
require_once( 'includes/class-camptix-events-calendar.php' );

/**
 * Returns the main instance of CampTix_Events_Calendar to prevent the need to use globals.
 *
 * @since  1.0.0
 * @return object CampTix_Events_Calendar
 */
function CampTix_Events_Calendar () {
	$instance = CampTix_Events_Calendar::instance( __FILE__, '1.0.0' );
	return $instance;
}

CampTix_Events_Calendar();