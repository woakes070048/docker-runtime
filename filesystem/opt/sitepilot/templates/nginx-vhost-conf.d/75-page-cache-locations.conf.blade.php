# vim: set ft=nginx:

@if($cache['enabled'])

@if($cache['backend'] == 'redis')
location =  /.stack-cache-fetch {
    internal;

    set $redis_key $args;

    redis_pass {{ $cache['host'] }}:{{ $cache['port'] }};
}

location = /.stack-cache-store {
    internal;

    set_unescape_uri $exptime $arg_exptime;
    set_unescape_uri $key $arg_key;

    redis2_query set $key $echo_request_body;
    redis2_query expire $key $exptime;
    redis2_pass {{ $cache['host'] }}:{{ $cache['port'] }};
}
@endif

@endif