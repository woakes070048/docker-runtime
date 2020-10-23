include=/opt/sitepilot/etc/php-fpm.d/*.conf

[global]
daemonize = no
error_log = /proc/self/fd/2

@if(version_compare(env('PHP_VERSION'), "7.3.0", ">="))
log_limit = 8192
@endif
