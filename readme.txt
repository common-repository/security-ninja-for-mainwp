=== Security Ninja For MainWP ===
Contributors: lkoudal, cleverplugins, freemius
Plugin URI: https://wpsecurityninja.com/mainwp/
License: GPLv3
License URI: https://www.gnu.org/licenses/gpl.html
Tags: security, hack, malware, mainwp
Requires at least: 4.7
Tested up to: 6.6.2
Stable tag: 2.0.10
Requires PHP: 7.4
Donate link: https://wpsecurityninja.com/

See vulnerabilites and security test results in the MainWP dashboard.

== Description ==

Security Ninja is a strong plugin that helps you find vulnerabilites and improve the security on your website.

MainWP is an invaluable tool for those who manage multiple WordPress websites. 

To combine the two, you need to install this extension on your master MainWP website.

### Links and Documentation

* [Security Ninja for MainWP Extension Page](https://wpsecurityninja.com/mainwp/?utm_source=wordpress.org&utm_medium=referral&utm_campaign=SecNin&utm_content=plugin+repo)

* [Get Started with MainWP and Security Ninja](https://wpsecurityninja.com/docs/mainwp/get-started-mainwp/?utm_source=wordpress.org&utm_medium=referral&utm_campaign=SecNin&utm_content=plugin+repo)

== Installation ==

=== Install the Security Ninja MainWP extension from within the MainWP dashboard ===

1. Login to your MainWP dashboard
1. Navigate to WP > Plugins
1. Search for 'Security Ninja MainWP'
1. Install and activate the plugin

=== Install the Security Ninja MainWP extension manually ===

1. Download the plugin
1. Login to your MainWP dashboard
1. Navigate to WP > Plugins
1. Click Add New and then Upload Plugin
1. Browse to the file, select it and click Install Now
1. Click Activate Plugin once prompted.

== Frequently Asked Questions ==

= Support and Documentation =
Please refer to our [documentation pages](https://wpsecurityninja.com/docs/mainwp/?utm_source=wordpress.org&utm_medium=referral&utm_campaign=SecNin&utm_content=plugin+repo) for help and technical information on Security Ninja and the integration with MainWP.

== Screenshots ==

1. The overview in the MainWP dashboard where you can see any vulnerabilites or low scores for the security tests.
2. Remote start "Run security tests" on one or more sites.

== Changelog ==

= 2.0.10 =
* Added more strings for translation making the plugin translatable. From 8 strings to 96.
* Updated Freemius SDK.
* Added translations for Danish, Spanish, French, Italian, Japanese, Norwegian, Dutch, German, Portugese, Russian, Swedish and Chinese.
* Checked Addon works with WP 6.6.2.

= 2.0.9 =
* Simplified the global events log by removing the "Module" column.
* New feature: Logs are now automatically trimmed to maintain only the last 30 days of history or up to 10,000 of the most recent log entries.
* Bugfix: Not loading the white label popup and "Run Security Tests"
* Streamlined the interface by moving the User Agent information to a tooltip on hover over the IP address, making room for more relevant data.
* Added a helpful reminder on the global events page to synchronize websites with Security Ninja Premium for events to appear. Message: 'It looks boring here, right? Please synchronize some websites with Security Ninja Premium installed.'
* Updated to the latest Freemius SDK.

= 2.0.8 =
* Added more strings for translation making the plugin translatable.
* Fixing bug with license and "cannot detect main plugin" error. Thank you for all the feedback and help fixing the bug.

= 2.0.7 =
* Bugfixes to pages not loading correctly.
* Improved communication with MainWP Client sites with Security Ninja.
* Bugfixes to the White label feature.
* Updated language files.

= 2.0.6 =
* Fixed broken menu link that happened on some sites.
* Improve language to show what type and version of the plugin the child site is running.
* Fix deprecated code.
* NEW: Added remote control of white label setting on child sites. Enable / disable and change settings on all child sites quickly.

= 2.0.5 =
* Fix for undefined variables linking to help sections on the website.
* Add big warning to keep the main Security Ninja running as it is now a requirement for this Addon.
* Minor bugfixes.

= 2.0.4 = 
* Fix for the premium link.

= 2.0.3 = 
* Fixed the addon implementation and the bugs reported. 
* Dependency - Necessary to have Security Ninja plugin installed and activated. Free or pro, either works.

= 2.0.2 =
* Refactored the navigation system

= 2.0.1 =
* Fix - Install routines were not working and breaking sites.

= 2.0 =
* New version for MainWP v5

= 1.8 =
* Updated interface
* WP 6.4.2 compatibility.

= 1.6 =
* WP 6.2 compatibility.

= 1.5 =
* Improved security with MainWP changes to admin links.
* Improved speed loading data from websites.

= 1.4 =
* Tested up to WP 6.0
* Added Secret Access URL to the site list. Perfect if you have become logged out of a site. Suggestion by Alauddin. Note - Requires Security Ninja 5.145.

= 1.3 =
* Fix: Some sites data not loading when paginating the site list.
* Fix: PHP notice for custom reports function.

= 1.2 =
* Fix: Direct link in sidemenu still not working. Thank you Mustaasam.

= 1.1 =
* Fix: Direct link in sidemenu not working.
* Fix: Adding search and sorting to the site overview.
* Fix: Improved styling to follow MainWP styling.
* Fix: Logo in top left corner.
* New: Direct link to site Security Ninja Dashboard page.
* Cleaning up JS and CSS.

BIG thanks to Bogdan from MainWP for the help in tuning this :-)

= 1.0  =
* First public release

== Upgrade Notice ==

= 1.0 =
First public release of the plugin