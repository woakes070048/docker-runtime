date.timezone="{{ $php['timezone'] }}"
post_max_size={{ $php['uploadLimit'] }}M
upload_max_filesize={{ $php['uploadLimit'] }}M
memory_limit={{ $php['memoryLimit'] }}M
max_input_vars=3000

opcache.fast_shutdown=1
opcache.memory_consumption=128
opcache.max_accelerated_files=6000
opcache.interned_strings_buffer=16

sendmail_path=/usr/bin/msmtp -C /opt/sitepilot/etc/msmtp/msmtp.conf --read-envelope-from -t

mail.log=/opt/sitepilot/app/logs/php_mail.log 
error_log=/opt/sitepilot/app/logs/php_error.log
disable_functions=