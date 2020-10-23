# vim: set ft=nginx:
@if($cache['enabled'])

set $skip 0;

if ($request_method = POST) {
    set $skip 1;
}
if ($query_string != "") {
    set $skip 1;
}

if ($request_uri ~* "(/wp-admin/|/xmlrpc.php|wp-.*.php|index.php|/feed/|sitemap(_index)?.xml|[a-z0-9_-]+-sitemap([0-9]+)?.xml)") {
    set $skip 1;
}

if ($http_cookie ~* "comment_author|wordpress_[a-f0-9]+|wp-postpass|wordpress_no_cache|wordpress_logged_in|woocommerce_items_in_cart") {
    set $skip 1;
}

set $key "nginx-cache:http$request_method$host$request_uri";

if ($HTTP_X_FORWARDED_PROTO = "https") {
    set $key "nginx-cache:https$request_method$host$request_uri";
}

srcache_fetch_skip $skip;
srcache_store_skip $skip;

srcache_response_cache_control off;

set_escape_uri $escaped_key $key;

srcache_fetch GET /.stack-cache-fetch $key;
srcache_store PUT /.stack-cache-store key=$escaped_key;

more_set_headers "X-Cache $srcache_fetch_status";

@if($cache['debug'])
more_set_headers "X-Cache-Key $key";
@endif
@endif

