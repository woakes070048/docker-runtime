# vim: set ft=nginx:

map $http_x_forwarded_proto $fastcgi_param_https_variable {
    default '';
    https 'on';
}