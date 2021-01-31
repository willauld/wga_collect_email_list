# wga_collect_email_list

This plugin provides a few simple forms (email opt in, contact form) that can be used stand alone or with Popup Maker. For email opt in, an email is sent to the user asking them to click on a button to verify their email address (two step opt in).

To do:
- Add the capability to unsubscibe that can be added to the bottom of emails
- May want to send an email if they try to put their name and email in additional times. Consider the case where they have unsubscribed and want to resubscribe. 

Do Now:
- Add a contact form.
x Fix email image url to point to the local site url.
x Copy verify.php on activation and delete on deactivation.
- Fix note for non-popup form that tells user to look for an email to verify email.
- Set updated_at time when verify.php verifies the email
- fix popup container box size not holding form (staging only)
- fix warning for header (staging only) Error message below:
Warning: Cannot modify header information - headers already sent by (output started at /home/customer/www/staging2.williama18.sg-host.com/public_html/verify.php:1) in /home/customer/www/staging2.williama18.sg-host.com/public_html/wp-content/plugins/sg-cachepress/core/Supercacher/Supercacher_Helper.php on line 79