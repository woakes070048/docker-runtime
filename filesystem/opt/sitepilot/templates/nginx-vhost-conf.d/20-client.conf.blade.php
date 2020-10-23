# vim: set ft=nginx:

client_max_body_size {{ $nginx['vhost']['maxBodySize'] }}m;
