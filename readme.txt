=== CampTix & Events Calendar for Meetup Groups ===
Contributors: hlashbrooke
Donate link: http://www.hughlashbrooke.com/donate
Tags: CampTix, The Events Calendar, Meetups, WordCamp, tickets, events
Requires at least: 4.0
Tested up to: 4.1
Stable tag: 1.1
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Making CampTix and The Events Calendar work in harmony for the benefit of your meetup group.

== Description ==

"CampTix & Events Calendar for Meetup Groups" is an add-on for [CampTix](https://wordpress.org/plugins/camptix/) and [The Events Calendar](https://wordpress.org/plugins/the-events-calendar/) that lets you use these two great plugins to manage events for your meetup group.

"CampTix & Events Calendar for Meetup Groups" adds the following features:

* New 'Sponsor' taxonomy for events so you can specify any number of sponsors per event and display them on event pages
* Link up CampTix tickets and events allowing you to display event attendees on your event pages
* Public RSS feed of event attendees - useful for integrations with services like Slack

You can see an example of this plugin in action on a single event page from my local WordPress meetup group in Cape Town [here](http://www.wpcapetown.co.za/event/july-2014-meetup/) and [here](http://www.wpcapetown.co.za/event/april-2014-meetup/). And [here's](http://www.wpcapetown.co.za/feed/attendees/?event=518482) an example of the attendees RSS feed filtered to show a specific event's attendees.

Want to contribute? [Fork the GitHub repository](https://github.com/hlashbrooke/Camptix-Events-Calendar).

Note that this plugin requires CampTix and The Events Calendar (or Events Calendar PRO) to be installed - without them it is effectively pointless.

== Usage ==

All you need to do is install the plugin, add your sponsors and link up your events and tickets from the event edit screen.

== Installation ==

Installing "CampTix & Events Calendar for Meetup Groups" can be done either by searching for "CampTix & Events Calendar for Meetup Groups" via the "Plugins > Add New" screen in your WordPress dashboard, or by using the following steps:

1. Download the plugin via WordPress.org
1. Upload the ZIP file through the 'Plugins > Add New > Upload' screen in your WordPress dashboard
1. Activate the plugin through the 'Plugins' menu in WordPress

== Frequently Asked Questions ==

= Why did you make this plugin? =

I built these custom features for my local WordPress meetup group in Cape Town and thought it was worth sharing them for the benefit of other meetup groups around the world.

= How do I display a specific event's attendees in the RSS feed? =

To make the attendees RSS feed only display attendees for a specific event, just add '?event=EVENT_ID' to the feed URL.

= How do I customise the RSS feed? =

Copy the `templates/feed-attendees.php` file into the root folder of your active theme (or child theme) and then edit it there. If the RSS temmplate file exists in your theme then the plugin will use that one instead of the default.

= Can I request additional features? =

Yes - by all means, please post on the support forum with your feature request and I'll see what I can do to accommodate them.

= How can I contribute to this plugin? =

You can fork the GitHub repository [here](https://github.com/hlashbrooke/Camptix-Events-Calendar).

== Changelog ==

= 1.1 =
* 2014-07-01
* New: Adding event attendees RSS feed

= 1.0 =
* 2014-06-17
* Initial release #boom

== Upgrade Notice ==

= 1.1 =
* Adding event attendees RSS feed

= 1.0 =
* Initial release #boom