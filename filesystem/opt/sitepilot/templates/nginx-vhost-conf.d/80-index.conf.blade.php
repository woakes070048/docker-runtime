# vim: set ft=nginx:

add_header X-Powered-By "Sitepilot";

location ^~ /.sitepilot {
    index index.php;
    alias  /opt/sitepilot/etc/sitepilot;

    location ~ \.php$ {
        if (!-f $request_filename) { return 404; }

        set $proxy_https '';
        if ($http_x_forwarded_proto = 'https') {
            set $proxy_https 'on';
        }
       
        fastcgi_pass         $upstream;
        fastcgi_read_timeout {{ $nginx['readTimeout'] }};
        fastcgi_index        index.php;
        include              /usr/local/openresty/nginx/conf/fastcgi.conf;
        fastcgi_param        HTTPS $proxy_https if_not_empty;
        fastcgi_param        SCRIPT_FILENAME $request_filename;
    }
}

location / {
    try_files $uri $uri/ /index.php$is_args$args;
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

    set $proxy_https '';
    if ($http_x_forwarded_proto = 'https') {
        set $proxy_https 'on';
    }

    fastcgi_pass         $upstream;
    fastcgi_read_timeout {{ $nginx['readTimeout'] }};
    fastcgi_index        index.php;
    include              /usr/local/openresty/nginx/conf/fastcgi.conf;
    fastcgi_param        HTTPS $proxy_https if_not_empty;
}
