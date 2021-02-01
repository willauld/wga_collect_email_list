# wga_collect_email_list

This plugin provides a few simple forms (email opt in, contact form) that can be used stand alone or with Popup Maker. For email opt in, an email is sent to the user asking them to click on a button to verify their email address (two step opt in).

To do:
- Add the capability to unsubscibe that can be added to the bottom of emails
- May want to send an email if they try to put their name and email in additional times. Consider the case where they have unsubscribed and want to resubscribe. 
- Add admin function to resend an email for email verification for unvalidated email record

Do Now:
- Add a contact form.
x Fix email image url to point to the local site url.
x Copy verify.php on activation and delete on deactivation.
x Fix note for non-popup form that tells user to look for an email to verify email.
x Set updated_at time when verify.php verifies the email
- fix popup container box size not holding form (staging only)
x fix warning for header (staging only) Error message below:
Warning: Cannot modify header information - headers already sent by (output started at /home/customer/www/staging2.williama18.sg-host.com/public_html/verify.php:1) in /home/customer/www/staging2.williama18.sg-host.com/public_html/wp-content/plugins/sg-cachepress/core/Supercacher/Supercacher_Helper.php on line 79
x Add message text stays until successful submit
x Add checkbox info stays until successful submit
- Do email check on contact form only if join mailing list checkbox checked
- Add separate, specific message email to info@OregonOpenPrimaries.org
- fix the tab set to work with both form types.
- if only message don't error for email already in db
- Fix both thankyous are coming out for sending a message on the contact form
- Add subject field to contact form