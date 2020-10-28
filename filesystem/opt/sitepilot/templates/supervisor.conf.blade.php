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

@if(!empty($deploy['token']))
[program:webhook]
command = webhook -urlprefix -/webhooks -hooks /opt/sitepilot/etc/hooks.json -verbose
utorestart=true
stopasgroup=true
stdout_logfile = /dev/stdout
stdout_logfile_maxbytes=0
stderr_logfile = /dev/stderr
stderr_logfile_maxbytes=0
@endif

@if(!empty($user['name']))
[program:sshd]
command=/usr/sbin/sshd -D -f /opt/sitepilot/etc/sshd_config -e
process_name = sshd
autorestart=true
stopasgroup=true
stdout_logfile = /dev/stdout
stdout_logfile_maxbytes=0
stderr_logfile = /dev/stderr
stderr_logfile_maxbytes=0
@endif

[group:web]
programs=nginx,php-fpm

[include]
files=/opt/sitepilot/etc/supervisor.d/*.conf
