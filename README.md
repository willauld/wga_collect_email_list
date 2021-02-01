# wga_collect_email_list

This plugin provides a few simple forms (email opt in, contact form) that can be used stand alone or with Popup Maker. For email opt in, an email is sent to the user asking them to click on a button to verify their email address (two step opt in).

To do:
- Add the capability to unsubscibe that can be added to the bottom of emails
- May want to send an email if they try to put their name and email in additional times. Consider the case where they have unsubscribed and want to resubscribe. Or have tried subscribing but didn't verify. They repeat but are not allowed because the email is already in db.
- Add admin function to resend an email for email verification for unvalidated email record

Do Now:
x Add a contact form.
x Fix email image url to point to the local site url.
x Copy verify.php on activation and delete on deactivation.
x Fix note for non-popup form that tells user to look for an email to verify email.
x Set updated_at time when verify.php verifies the email
x fix warning for header (staging only) Error message below:
Warning: Cannot modify header information - headers already sent by (output started at /home/customer/www/staging2.williama18.sg-host.com/public_html/verify.php:1) in /home/customer/www/staging2.williama18.sg-host.com/public_html/wp-content/plugins/sg-cachepress/core/Supercacher/Supercacher_Helper.php on line 79
x Add message text stays until successful submit
x Add checkbox info stays until successful submit
x Do email check on contact form only if join mailing list checkbox checked
x Add separate, specific message email to info@OregonOpenPrimaries.org
x if only message don't error for email already in db
x Fix both thankyous are coming out for sending a message on the contact form
x Fix not getting "check your email message from contact form"
- Add subject field to contact form
- fix popup container box size not holding form (staging only)
- fix only getting contact form email messages when not remember so no verifiy email.
- fix should not get verify message on contact form when not remember