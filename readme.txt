=== PostmarkApp Email Integrator ===
Contributors: gagan0123, guillaumemolter, livearoha
Tags: postmark, email, smtp, notifications, wp_mail, wildbit
Requires at least: 5.0
Tested up to: 6.9
Stable tag: 2.5.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Enables your WordPress site to send emails via PostMarkApp API.

== Description ==

This plugin enables WordPress blogs of any size to deliver and track WordPress notification emails reliably, with minimal setup time and zero maintenance. No more SMTP errors or delivery problems with Postmark!

If you don't already have a free Postmark account, you can get one in minutes. Every account comes with thousands of free email sends.

PLEASE NOTE: This is not official PostMarkApp Plugin. This plugin is a copy of the Official Postmarkapp plugin which had several critical bugs.  The Official Plugin developers have not addressed these bugs for some time now, so I created a new plugin to solve those issues.

Issues fixed(from the official version):

 * Fixed the Connection Timeout bug
 * Fixed the Fatal Error due to incorrect usage of WP_Error object
 * Fixed the issue while parsing the headers sent as array to the wp_mail function
 * Fixed the breaking of plaintext when force html option is selected

New Features added:

 * Support for adding Cc, Bcc, and Reply-To headers
 * Support to filter the arguments by other plugins like its done in the actual wp_mail() function of WordPress
 * Auto import settings from the Postmarkapp approved WordPress plugin for easy migration

Roadmap:

 * Support for attachments
 * Handling special characters in Subject
 * Ability for dynamic "From" addresses by verifying from PostmarkApp

To know more about PostMarkApp, please visit this link: http://postmarkapp.com

To get help about PostMarkApp, please visit this link: http://support.postmarkapp.com/

To contribute to this plugin, visit the GitHub repository: https://github.com/gagan0123/postmarkapp-email-integrator

== Installation ==

1. Upload postmark directory to your /wp-content/plugins directory
1. Activate plugin in WordPress admin
1. In WordPress admin, go to Settings then Postmarkapp. You will then want to insert your Postmark details. If you don't already have a Postmark account, get one at http://postmarkapp.com
1. Verify sending by entering a recipient email address you have access to and pressing the "Send Test Email" button.
1. Once verified, then check "Enable" to override wp_mail and send using Postmark instead.

== Frequently Asked Questions ==

= What is Postmark? =

Postmark is a hosted service that expertly handles all delivery of transactional webapp and web site email. This includes welcome emails, password resets, comment notifications, and more. If you've ever installed WordPress and had issues with PHP's mail() function not working right, or your WordPress install sends comment notifications or password resets to spam, Postmark makes all of these problems vanish in seconds. Without Postmark, you may not even know you're having delivery problems. Find out in seconds by installing and configuring this plugin.

= Will this plugin work with my WordPress site? =

This plugin overrides any usage of the wp_mail() function. Because of this, if any 3rd party code or plugins send mail directly using the PHP mail function, or any other method, we cannot override it. Please contact the makers of any offending plugins and let them know that they should use wp_mail() instead of unsupported mailing functions.

= Does this cost me money? =

This Postmark plugin is 100% free. All new Postmark accounts include thousands of free email sends. Beyond your first free email sends, they will cost only $1.50 per 1000 sends with no monthly commitments and no expirations.

Postmark will send you multiple warnings as you approach running out of send credits, so you don't need to worry about paying for credits until you absolutely need them.

Sign up for your free Postmark account at http://postmarkapp.com and get started now.


== Changelog ==

= v2.5.0 =

* Security: Fixed Stored Cross-Site Scripting (XSS) vulnerability in plugin settings (CVE-2026-1043)
* Security: Added output escaping with esc_attr() on all form field values
* Security: Added input sanitization with sanitize_text_field() and sanitize_email()
* Security: Added nonce verification for settings form and AJAX requests
* Security: Added capability checks for form processing and AJAX handlers
* Security: Moved inline JavaScript to external file with proper enqueuing
* Security: Changed API endpoint from HTTP to HTTPS
* Improvement: Added text domain for internationalization support
* Improvement: Replaced extract() usage per WordPress coding standards
* Bug fix: Removed erroneous recursive wp_mail() call in error handling
* Bug fix: Used wp_json_encode() instead of json_encode()
* Bug fix: Used wp_die() instead of die() in AJAX handlers
* Dev: Added PHPCS configuration for WordPress coding standards
* Dev: Added Lando configuration for local development
* Dev: Added build script for WordPress.org distribution

= v2.4.0 =

 * Added filter as defined in the wp_mail function so as to minimize conflict with other plugins.

= v2.3.0 =

* Allow multiple Bcc & Cc in headers that are not comma separated.
* Fixed casing issues in the headers caused when some plugin/code uses lower casing for headers.

= v2.2.0 =

* Added better $var validation using isset to prevent 'Notice: Undefined' when WP_debug is activated.

= v2.1.0 =

* Fixed the breaking of plaintext when force html option is selected

= v2.0.0 =

* Error handling done through WP_Error object
* Made the "Test Email" error messages more descriptive by showing actual PostMarkApp messages
* Added support for Bcc header
* Added support for Cc header
* Added support for Reply-To header
* Removed "Powered by PostMark" append in the emails

= v1.1.0 =

* Added functionality to import the settings of official "Postmark Approved Wordpress Plugin"

= v1.0.0 =

* Fixed the Connection Timeout bug
* Fixed the Fatal Error due to incorrect usage of WP_Error object
* Fixed the issue while parsing the headers sent as array to the wp_mail function
