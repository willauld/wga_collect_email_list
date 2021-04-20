<?php //config.php

// Google reCAPTCHA v3 keys
// For reducing spam contact form submissions

// Site key (public)
$reCAPTCHA_site_key = '6LfxMa4aAAAAAFyzjjM2qqlzwzpRNR1ec8r8uNYs';

// Secret key
$reCAPTCHA_secret_key = '6LfxMa4aAAAAAMZuwj_mX-OjheY3VdQ2DagwUc-J';

// Min score returned from reCAPTCHA to allow form submission
$g_recaptcha_allowable_score = 0.5; //Number between 0 and 1. You choose this. Setting a number closer to 0 will let through more spam, closer to 1 and you may start to block valid submissions.