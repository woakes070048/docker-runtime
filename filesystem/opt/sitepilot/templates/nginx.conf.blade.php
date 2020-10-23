# vim: set ft=nginx:

pid /run/nginx.pid;

worker_processes auto;

events {
    worker_connections 768;
}

http {
    include /opt/sitepilot/etc/nginx-conf.d/*.conf;
}
