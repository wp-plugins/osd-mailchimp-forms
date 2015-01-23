=== Plugin Name ===
Contributors: osdwebdev
Tags: wordpress, mailchimp, mail chimp, subscribe, multiple subscribe forms, multiple mailchimp
Requires at least: 3.4
Tested up to: 4.1
Stable tag: 2.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

OSD MailChimp Forms allows you to add as many MailChimp subscription forms to your pages as needed.

== Description ==

OSD MailChimp Forms gives the ability to add MailChimp subscription forms to be placed anywhere on your site with a shortcode. The primary benefit of this plugin is that it allows multiple AJAX forms on one page. So, you could have a newsletter signup in the footer, a blog signup in the sidebar, and another blog subscription signup in the main content area.  The plugin allows for custom submission messages, field classes, form classes, custom submit button text, and removal of the double opt in emails! NO styling is placed on the forms by this plugin, so there is nothing to override. Use your theme's default styling or add custom styles. Simply enter your api key and start creating / placing forms in your content or Text Widgets.

Shortcode basic example:
[osd-mc-form id='osd_mc_forms_1']

Shortcode all options example:
[osd-mc-form id='osd_mc_forms_1' class='custom-class' submit_text='Sign Up!']

Form filter example:
Every form is filterable. The filter name is the form id.
The full content, the fields, the message container, and the submit button are provided.
$fields, $message, and $submit concatenated together is $content.

add_filter('osd_mc_forms_1', 'my_function', 10, 4);

function my_function($content, $fields, $message, $submit) {
    return "This appears before the content".$content."This appears after the content";
}

How To Video - Covers MailChimp set up, api key generation, list creation, OSD MC Form installation and setup
https://www.youtube.com/watch?v=gb1eQAgbw-Q

== Installation ==

1. Upload the osd-mailchimp-forms directory to your `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Navigate to the settings page
4. Add your MailChimp API key and save 
5. Navigate to the Forms subpage of OSD MailChimp on the left admin menu
6. Add a form from your MailChimp account
7. Save
8. Copy the shortcode from the top left of the form that you have just added

== Frequently Asked Questions ==

= Soon to come? =

Yes, as users ask us questions.

== Screenshots ==

1. Global Settings Menu
2. Forms Settings Menu Page
3. Text Widget Placement Example
4. Unstyled Successfully Submitted Form

== Changelog ==

= 2.0 =
* Lots of new features added (See Upgrade Notice) revolving around MailChimp error handling
* API Key Validation bug fixed
* Various other bugs addressed

= 1.7 =
Added the ability to redirect to a page on site on success

= 1.6.2 =
* Fixed bugs with PHP 5.3

= 1.6.1 =
* Fixed JavaScript bug in IE9

= 1.6 =
* Added a filter for forms based on form id
* Changed error message to be more user-friendly than "error"
* Added support for multiple messages (i.e. one above your form and one below)
* Submission event is now fired on form submit rather than submit button onClick

= 1.5 =
Updated to work with older versions of PHP

= 1.4 =
Phone and address field updates

= 1.3 =
Added some WordPress Security

= 1.2 =
Youtube how to video added
https://www.youtube.com/watch?v=gb1eQAgbw-Q

= 1.1 =
Correct Uninstall Error

= 1.0 =
* Initial creation

== Upgrade Notice ==

= 2.0 =
* Customizable error messaging and classes
* Specific errors for users already subscribed to list
* Global error message settings
* All custom messages are now run through the content filter to allow for shortcodes and better html handling

= 1.7 =
Added the ability to redirect to a page on site on success

= 1.5 =
Updated to work with older versions of PH

= 1.3 =
Added some WordPress Security

= 1.2 =
Youtube how to video added
["How To Video"](https://www.youtube.com/watch?v=gb1eQAgbw-Q)

= 1.0 =
Add multiple AJAX MailChimp forms


== A brief Feature List ==

1. Forms can be used in the Text Widget provided by WordPress
2. Forms can be used in the WordPress wysiwyg (any where that runs the content filter)
3. Does not hinder page load times, the forms are loaded in after the page is rendered
4. Lightweight
5. Shortcode usage allows for flexibility
6. Custom classes on forms and form fields
7. Placeholders on text fields
8. All actions are AJAX (no page refreshes)
9. Every form is filterable

Link to plugin page [Wordpress plugin page](http://wordpress.org/plugins/osd-mailchimp-forms/ "Link").

[markdown syntax]: http://daringfireball.net/projects/markdown/syntax
            "Markdown is what the parser uses to process much of the readme file"