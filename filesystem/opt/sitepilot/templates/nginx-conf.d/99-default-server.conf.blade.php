# vim: set ft=nginx:

server {
    listen       8080;
    server_name  $hostname;
    root         {{ env('APP_PATH_PUBLIC') }}/{{ $nginx['root'] }};
    index        index.html index.htm index.php;

    set $app_root {{ env('APP_PATH') }};

    include /opt/sitepilot/etc/nginx-vhost-conf.d/*.conf;
}
