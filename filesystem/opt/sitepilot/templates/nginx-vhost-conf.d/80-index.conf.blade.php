# vim: set ft=nginx:

add_header X-Powered-By "Sitepilot";

location / {
    try_files $uri $uri/ /../root/$uri /index.php$is_args$args;
}

location ~ \.php$ {
    @if($cache['enabled'])
    include              /opt/sitepilot/etc/nginx-vhost-conf.d/page-cache.d/*.conf;
    @endif

    @if($nginx['blockPHPPaths'])
    if ( $is_allowed_php_uri ~* "^(|no|false|0)$" ) {
        return 403;
    }
    @endif

    fastcgi_pass         $upstream;
    fastcgi_read_timeout {{ $nginx['readTimeout'] }};
    fastcgi_index        index.php;
    include              /usr/local/openresty/nginx/conf/fastcgi.conf;
}
