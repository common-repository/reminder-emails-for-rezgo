=== Reminder Emails for Rezgo ===
Contributors: schwarzhund
Donate link: http://alexvp.elance.com
Tags:  tour operator software, tour booking system, activity booking software, tours, activities, events, attractions, booking, reservation, ticketing, e-commerce, business, rezgo, custom email, emails, notifications, reminders, web hook, api
Requires at least: 3.0.0
Tested up to: 3.6
Stable tag: 0.1

Send custom text or html reminder email messages to customers when they make a booking through your Rezgo online booking engine.

== Description ==

> This plugin is completely free to use, but it requires an active <a href="http://www.rezgo.com">Rezgo account</a>.

**Rezgo** is an online booking engine for tour and activity operators that helps you manage inventory, accept reservations, and process credit card payments. This plugin allows you to send custom html or text email reminder messages to your customers after they make a booking through your Rezgo account.


The plugin gives you the ability to create a custom reminder email for every tour and option combination available in your account.  You can set the reminder email to send based on the number of days in advance, the status of the booking, and the tour/option that was booked.  The plugin will support an unlimited number of reminders however, each reminder triggers an API request, so please keep this in mind.

= Plugin features include =

* Pull current tours/activities from your Rezgo account.

* Set your own "From email" and "From Name".

* Create reminders for each tour/activity option based on days before and status.
* Supports both text and html emails (complete with WYSIWYG editor).
* Logs plugin activities in a separate log.

= Support for this plugin =

This plugin was developed by AlexVP.  There is no support provided for this plugin.  It is available as-is with no guarantees.  If you would like the plugin customized or modified for your needs, please feel free to send a proposal or hire Alex through Elance.

[http://alexvp.elance.com](http://alexvp.elance.com)

== Installation ==

= Install the Reminder Emails for Rezgo plugin =

1. Install the Reminder Emails for Rezgo plugin in your WordPress admin by going
to 'Plugins / Add New' and searching for 'Rezgo' **OR** upload the
'wp-rezgo-reminders' folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress

= Plugin Configuration and Settings =

In order to use the Reminder Emails for Rezgo plugin, your Rezgo account must be activated.  This means that you **must** have a valid credit card on file with Rezgo before your plugin can connect to your Rezgo account.

1. Make sure the Reminder Emails for Rezgo plugin is activated in WordPress.
2. Add your Rezgo account CID and API KEY in the plugin settings and click the 'Save Changes' button.

3. Add your From name and email address and click the 'Save Changes' button.
4. If you want the reminders to run automatically you will need to add a CRON JOB to your server.  For details on how to do this, [check out this article](http://www.thesitewizard.com/general/set-cron-job.shtml).  If you want to manually send reminders on a daily basis, check the Send Reminders Manually box.
5. To synchronize your tour list, click on the 	'Update Tour/Option List' button.

6. Click on the 'Reminders' link in the side bar.

7. Click the 'Add Reminder' button to create a new reminder.

8. Choose a tour from the drop down list.

9. Select when you'd like to send the reminder.
10. Select what status the booking should have in order to send the reminder.  If it is a PENDING booking for example, you may want to remind the customer that their payment will be due when they arrive.
11. Complete the subject, text message, and/or the html message, click the 'Save Changes' button to save your new reminder.  
12. To test your reminder, create a new front-end booking on your Rezgo account that is the correct number of days from today.  Manually run the reminders.

== Frequently Asked Questions ==

= Can I contact Rezgo for support for this plugin? =


No. Rezgo did not create this plugin and does not support it.



= I added a booking but no reminder was sent, what should I do?  =

Check the Log in the plugin to see what error was received.  Make sure that your bookings have the status you want or try switching to ALL status.



= Does this work for back-office or point of sale bookings? =

Yes.  This plugin will work for all bookings made in the system that have a billing address email set.  Bookings that do not have a billing email will not receive reminders.

= Can I send attachments with the notifications? =

No, attachments are not supported.  You can include links in the reminders, so it would be best to link to any documents that you want to include.

= I want the plugin to do something that it doesn't do now, who should I contact? =


You can contact Alex at Elance : [http://alexvp.elance.com](http://alexvp.elance.com)

Please note, there is NO FREE SUPPORT for this plugin.  Any changes or modifications will be charged.

== Screenshots ==

1. Once you activate the Reminder Emails for Rezgo plugin, you will need to enter 
in your Rezgo API credentials on the settings page located in your 
WordPress Admin.  Look for Rezgo Reminders in the sidebar.
2. Add a reminder. You can select the tour/option, when to send the reminder, and what booking status as well as customize the subject, text message, and html message.
3. Plugin activity is displayed in the log.  This is a great place to check to make sure your reminders are going out as planned. 

== Changelog ==

= 1.0 =
* Initial release.

== Upgrade Notice ==

= You have the most recent version =