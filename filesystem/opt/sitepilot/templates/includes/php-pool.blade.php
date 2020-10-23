
security.limit_extensions = {{ implode(' ', $php['limitExtensions'])}}

chdir = {{ env('APP_PATH') }}
clear_env = {{ $php["clearEnv"] }}

; Ensure worker stdout and stderr are sent to the main error log.
catch_workers_output = yes
@if(version_compare(env('PHP_VERSION'), "7.3.0", ">="))
decorate_workers_output = no
@endif

@if($php['accessLog'] != "off")
; Access logging from php
access.log = {{ $php['accessLog'] }}
access.format = "{{ $php['accessLogFormat'] }}"
@endif

; Log slow requests
slowlog = /proc/self/fd/2
request_slowlog_timeout = {{ $php['slowLogTimeout'] }}s

@if($php['requestTimeout'] > 0)
; Per request limits
request_terminate_timeout = {{ $php['requestTimeout'] }}s
@endif

php_admin_value[error_log] = /dev/stderr
php_value[max_execution_time] = {{ $php['requestTimeout'] }}
php_value[memory_limit] = {{ $php['memoryLimit'] }}M
php_value[post_max_size] = {{ $php['uploadSize'] }}M
php_value[upload_max_filesize] = {{ $php['uploadSize'] }}M