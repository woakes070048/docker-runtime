# Upstream to abstract backend connection(s) for PHP.
# Additional upstreams can be added to /etc/nginx/upstreams/*.conf and then you just
# change `default php74` to whatever the new upstream is (could be php73 for example).
upstream php74 {
	server unix:/run/php/php-fpm.sock;
}

map '' $upstream {
	default php74;
}