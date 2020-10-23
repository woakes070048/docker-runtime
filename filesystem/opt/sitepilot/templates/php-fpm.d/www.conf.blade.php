[www]
listen = /var/run/php-www.sock
listen.backlog = 65535
listen.owner = www-data
listen.group = www-data

pm = dynamic
pm.max_children = {{ $php['maxChildren'] }}
pm.max_requests = 500
pm.start_servers = 1
pm.min_spare_servers = 1
pm.max_spare_servers = 2
@include('includes/php-pool')

[www-backup]
listen = /var/run/php-www-backup.sock
listen.backlog = 65535
listen.owner = www-data
listen.group = www-data

pm = static
pm.max_children = 4
pm.max_requests = 500
@include('includes/php-pool')

[www-async]
listen = /var/run/php-www-async.sock
listen.backlog = 65535
listen.owner = www-data
listen.group = www-data

pm = static
pm.max_children = 2
pm.max_requests = 1
request_terminate_timeout = 1800s
@include('includes/php-pool')
