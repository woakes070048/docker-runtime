# vim: set ft=nginx:

map $uri $is_allowed_php_uri {
    default                 'no';

    @foreach($nginx['allowedPHPPaths'] as $path)
    {{ $path }} 'yes';
    @endforeach

    ~^/-/webhooks/ 'yes';
}
