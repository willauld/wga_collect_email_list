
$cmd = @"
cp -r /mnt/c/home/auld/src/wga-collect-email-list /mnt/c/laragon/www/forms/wp-content/plugins
rm -rf /mnt/c/laragon/www/forms/wp-content/plugins/wga-collect-email-list/.git
"@

bash -c $cmd