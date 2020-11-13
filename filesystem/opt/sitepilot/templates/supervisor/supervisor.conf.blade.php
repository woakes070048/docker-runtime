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
command = nginx -p /var/lib/nginx -g 'daemon off;' -c /opt/sitepilot/etc/nginx/nginx.conf
process_name = nginx
autorestart=true
stopasgroup=true
stdout_logfile = /dev/stdout
stdout_logfile_maxbytes=0
stderr_logfile = /dev/stderr
stderr_logfile_maxbytes=0

[program:php-fpm]
command = php-fpm{{ env('PHP_VERSION', '7.4') }} -y /opt/sitepilot/etc/php/php-fpm.conf
autorestart=true
stopasgroup=true
stdout_logfile = /dev/stdout
stdout_logfile_maxbytes=0
stderr_logfile = /dev/stderr
stderr_logfile_maxbytes=0

@if($user['name'])
[program:sshd]
command=/usr/sbin/sshd -D -f /opt/sitepilot/etc/sshd/sshd_config -e
process_name = sshd
autorestart=true
stopasgroup=true
stdout_logfile = /dev/stdout
stdout_logfile_maxbytes=0
stderr_logfile = /dev/stderr
stderr_logfile_maxbytes=0
@endif

@if($deploy['token'])
[program:webhook]
command = webhook -urlprefix -/webhooks -hooks /opt/sitepilot/etc/webhook/hooks.json -verbose
utorestart=true
stopasgroup=true
stdout_logfile = /dev/stdout
stdout_logfile_maxbytes=0
stderr_logfile = /dev/stderr
stderr_logfile_maxbytes=0
@endif

[group:web]
programs=nginx,php-fpm

[include]
files=/opt/sitepilot/etc/supervisor/conf.d/*.conf
