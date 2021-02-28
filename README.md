# wga_collect_email_list

This plugin provides a few simple forms (email opt in, contact form) that can be used stand alone or with Popup Maker. For email opt in, an email is sent to the user asking them to click on a button to verify their email address (two step opt in). Similarly, the contact form provides a checkbox that allows the user to opt in to joining the email list. If checked the user will get an email requesting them to click a button taking them to the site for verification. Additionally, an email is sent to the site admin with the user message in the case of the contact form.  

In each of these cases a thank you message telling them about the the email to come is displayed. However, when the contact form is used without email opt in (checkbox is not checked) There will be no thank you message. (*Maybe there should be a different message*). As the thank you message is displayed the form is cleared.

The three current short codes are:
- [wga_popup_email_form] - join email list form used with popup maker
- [wga_on_page_email_form] - join email list form place directly on the web page
- [wga_1st_contact_form] - contact form placed directly on the web page
- [wga_pancake_email_form] - join email list from for on page and as vertically short as possible. 

To do:
x May want to send an email if they try to put their name and email in additional times. Consider the case where they have unsubscribed and want to resubscribe. Or have tried subscribing but didn't verify. They repeat but are not allowed because the email is already in db.
x On checking if email already exists in db, let it pass if it is there but has not been confirmed or was confirmed and unsubscribed. (makesure to reuse the existing hash)
x Add the capability to unsubscibe that can be added to the bottom of emails
x Add admin method to download / upload csv file to email collection
x Add admin page for email campain 
x Add admin method to review email collection, updating as needed. (edit by upload changes)
  x filter for active / unsubscribed / unverified email
  x Add mode to restore backup that retains the created_at, updated_at and id from the csvfile.
  - Add report that gives a list of all new email since xxx, maybe emai to list
- Add admin function to resend an email for email verification for unvalidated email record
- Currently unsubscribe happens whenever you click the link, should add a verification form that the user really wants to unsubscribe.
- Add subject field to contact form
- Add admin method to send email (campaign) to active email collection
- Add admin page for subscription, unsubscribe and contact form email specification (this is in the code today - move to admin)
  - This should include define message, subject, to, cc, bcc, headers background color, acceptance thankyou message.
- Add functionality for admin to specify deletion or not of db table on uninstall (commented out for now, used to delete)
- Add bulk action for email contact list to include resend verification, delete, ...

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
x fix only getting contact form email messages when not remember so no verifiy email. That is only one email is currently being sent even thou there should be two.
x fix should not get verify message on contact form when not remember. At this point the thank you messages are not coming out when they should and it seems like the variables in the code do not have the values they should. 
x fix verify.php to better show OOP and message, especially for mobile
- fix popup container box size not holding form (staging only)
- clean up the code / remove excess comments...
- Fix contact form to better work with mobile
x Fix csvfile upload double adding new record (record with no id value)
x Delete upload file after done processing.
- add nonce to each form input
  - pancake input
  - contact input
  - download csvfile
  x upload csvfile
  - add nonce for message input
Message related
- Add edit message id...
- Add new message
- if changing something and message not saved ask user if they want to save first.

