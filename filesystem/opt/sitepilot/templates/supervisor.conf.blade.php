[supervisord]
nodaemon = true
logfile = /dev/stderr
logfile_maxbytes = 0
pidfile = /var/run/supervisord.pid
loglevel=error

[unix_http_server]
file=/var/run/supervisor.sock
username = sitepilot
password = secret

[rpcinterface:supervisor]
supervisor.rpcinterface_factory = supervisor.rpcinterface:make_main_rpcinterface

[program:nginx]
@if($nginx['errorLogLevel'] == 'debug')
command = /usr/bin/openresty-debug -p /var/lib/nginx -g 'daemon off;' -c /opt/sitepilot/etc/nginx.conf
@else
command = /usr/bin/openresty -p /var/lib/nginx -g 'daemon off;' -c /opt/sitepilot/etc/nginx.conf
@endif
process_name = nginx
autorestart=true
stopasgroup=true
stdout_logfile = /dev/stdout
stdout_logfile_maxbytes=0
stderr_logfile = /dev/stderr
stderr_logfile_maxbytes=0

[program:php-fpm]
command = php-fpm{{ env('PHP_VERSION', '7.4') }} -y /opt/sitepilot/etc/php-fpm.conf
autorestart=true
stopasgroup=true
stdout_logfile = /dev/stdout
stdout_logfile_maxbytes=0
stderr_logfile = /dev/stderr
stderr_logfile_maxbytes=0

[program:webhook]
command = webhook -urlprefix -/webhooks -hooks /opt/sitepilot/etc/hooks.json -verbose
utorestart=true
stopasgroup=true
stdout_logfile = /dev/stdout
stdout_logfile_maxbytes=0
stderr_logfile = /dev/stderr
stderr_logfile_maxbytes=0

[group:web]
programs=nginx,php-fpm

[eventlistener:processes]
command=emergency-exit
events=PROCESS_STATE_STOPPED, PROCESS_STATE_EXITED, PROCESS_STATE_FATAL

[include]
files=/opt/sitepilot/etc/supervisor.d/*.conf