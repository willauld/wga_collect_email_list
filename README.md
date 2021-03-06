# wga_collect_email_list

This plugin provides a few simple forms (email opt in, contact form) that can be used stand alone or with Popup Maker. For email opt in, an email is sent to the user asking them to click on a button to verify their email address (two step opt in). Similarly, the contact form provides a checkbox that allows the user to opt in to joining the email list. If checked the user will get an email requesting them to click a button taking them to the site for verification. Additionally, an email is sent to the site admin with the user message in the case of the contact form. Upon email verification a Welcome email can be sent. 

In each of these cases a thank you message telling them about the the email to come is displayed. However, when the contact form is used without email opt in (checkbox is not checked) There will be no thank you message. (*Maybe there should be a different message*). As the thank you message is displayed the form is cleared.

The four current short codes are:
- [wga_popup_email_form] - join email list form used with popup maker
- [wga_on_page_email_form] - join email list form place directly on the web page
- [wga_1st_contact_form] - contact form placed directly on the web page
- [wga_pancake_email_form] - join email list from for on page and as vertically short as possible.

There are a set of macros that can be used in messages to be sent by mailings. These macros corespond to the columns of the email list db. They are:
- $$id$$
- $$first_name$$
- $$last_name$$
- $$email$$
- $$source$$
- $$unsubscribed$$
- $$is_verified$$
- $$is_spam$$
- $$created_at$$
- $$updated_at$$
- $$vhash$$
- $$site_url$$

To do:
x May want to send an email if they try to put their name and email in additional times. Consider the case where they have unsubscribed and want to resubscribe. Or have tried subscribing but didn't verify. They repeat but are not allowed because the email is already in db.
x On checking if email already exists in db, let it pass if it is there but has not been confirmed or was confirmed and unsubscribed. (makesure to reuse the existing hash)
x Add the capability to unsubscibe that can be added to the bottom of emails
x Add admin method to download / upload csv file to email collection
x Add admin page for email campain 
x Add admin method to review email collection, updating as needed. (edit by upload changes)
  x filter for active / unsubscribed / unverified email
  x Add mode to restore backup that retains the created_at, updated_at and id from the csvfile.
  - Add report that gives a list of all new email since xxx, maybe email to list
- Add admin function to resend an email for email verification for unvalidated email record
  - This needs to be able to grab info from the db for every mail!! can this be added in a generic way to be able to do similar things for other data???
- Currently unsubscribe happens whenever you click the link, should add a verification form that the user really wants to unsubscribe.
- Add subject field to contact form
x Add admin method to send email (campaign) to active email collection
x Add admin page for subscription, unsubscribe and contact form email specification (this is in the code today - move to admin)
x Add admin page to define messages
  x Add subject / message editor input
  x Add wp_table to display the messages
  - Add define to, cc, bcc, headers background color, acceptance thankyou message.
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
  - email record edit
  - message record edit

Message related
x Add edit message id...
x Add new message
- if changing something and message not saved ask user if they want to save first.
- add upload / download for messages?
x Edit function is not working on staging site, test and fix
- 

IS_SPAM related
x Update and test staging - new staging the delete and restore table, test again with is_spam
x then main site
x update email table to display is_spam
x add is_spam column to download file processing
x add is_spam column to upload file processing
x add google recaptcha
- add admin page to capture the google secrets into the db as well as display the current short codes.

EMAIL LIST related:
x Add wp_table
x row actions: edit, delete, unsubscribe, 
- send verify note, 
x bulk actions include row actions that make sense, delete, 
- bulk action send verify note...
- add update to update_at field when doing verified, SPAM, Unsubscribe
- add nonce to edit email record
- redo the table filters for wp_list_table

Do "Mailings" on option page:
x Add mailings id to the edit form (TBD when not yet assigned)
x Not needed they are so simple to create - Copy a mailing that can then be edited?
x review / display list of mailings (maybe a wp_list_table?)
x Create a mailing that creates a connection between a message to be sent, with a set of email records, and then walks through and sends the emails.
x add in-table command to do a mailing and remove button from main page
- Add admin function to resend an email for email verification for unvalidated email record
  x This needs to be able to grab info from the db for every mail!! can this be added in a generic way to be able to do similar things for other data???
x Add filter codes to messages to enable update of content before mail is sent (both subject and content)
x Add filter code to wga_collect_email-admin.php ~ line 774, to replace filter codes with info from db record.
- Add message and mailings to resend email verifications. See if this is good enough that no special code is needed. -- working on this but I can't get a good email button to work all the time! 
- Fix bug: sometimes while "adding a new mailings" it just updates one. May be related to having the same message index. FIXME
- Might want to add a label for the mailings, could label on as test, one as "to all valid", one as "to unvalid only" and so one.

Do "donation function"
- See if I can get a plugin to add donations to each page?
