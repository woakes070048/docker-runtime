date.timezone="{{ $php['timezone'] }}"
post_max_size={{ $php['uploadLimit'] }}M
upload_max_filesize={{ $php['uploadLimit'] }}M
memory_limit={{ $php['memoryLimit'] }}M

expose_php=Off
short_open_tag=On
max_input_vars=3000

opcache.enable=1
opcache.max_accelerated_files=10000
opcache.memory_consumption=128
opcache.revalidate_freq=2
opcache.save_comments=0

sendmail_path=/usr/bin/msmtp -C /opt/sitepilot/etc/msmtp/msmtp.conf --read-envelope-from -t

disable_functions="exec,proc_open,popen,system,show_source,dl,shell_exec,passthru,proc_terminate,proc_close,proc_get_status,proc_nice,pclose,posix_kill,posix_mkfifo,posix_setpgid,posix_setsid,posix_setuid,posix_getpwuid,posix_uname"
