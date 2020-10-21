# Sitepilot Runtime

Docker runtime image used for running PHP applications on the Sitepilot managed hosting platform.

## Environment Variables

### Nginx

|Variable|Default|Description|
|--------|-------------|-----------|
|`NGINX_ERROR_LOG`|`dev/stderr`|Where to write Nginx's error log|
|`NGINX_ACCESS_LOG`|`off`|Where to write Nginx's access log|

### PHP

|Variable|Default|Description|
|--------|-------------|-----------|
|`PHP_PM`|`dynamic`|Can be set to `dynamic` `static` or `ondemand`|
|`PHP_MAX_CHILDREN`|`5`|The number of child processes to be created when `pm` is set to static and the maximum number of child processes to be created when pm is set to `dynamic`|
|`PHP_MAX_REQUESTS`|`500`|The number of requests each child process should execute before respawning|
|`PHP_MEMORY_LIMIT`|`128`|PHP request memory limit in megabytes|
|`PHP_ACCESS_LOG`|`off`|Where to write php's access log. Can be set to `off` to disable it entirely.|
|`PHP_ACCESS_LOG_FORMAT`|`%R - %u %t \"%m %r\" %s`|PHP access log format|
|`PHP_LIMIT_EXTENSIONS`|`.php`|Space separated list of file extensions for which to allow execution of php code|
|`PHP_PROCESS_IDLE_TIMEOUT`|`10`|Time in seconds to wait until killing an idle worker (only used when `PHP_PM` is set to `ondemand`).|
|`PHP_REQUEST_TIMEOUT`|`30`|Time in seconds for serving a single request. PHP `max_execution_time` is set to this value and can only be set to a lower value. If set to a higher one, the request will still be killed after this timeout.|
|`PHP_SLOW_REQUEST_TIMEOUT`|`0`|Time in seconds after which a request is logged as slow. Set to `0` to disable slow logging.
|`PHP_WORKER_CLEAR_ENV`|`no`|Clear the environment for php workers|

### Cache
|Variable|Default|Description|
|--------|-------------|-----------|
|`RUNTIME_CACHE_ENABLED`|`false`|Toggles full page caching|
|`RUNTIME_CACHE_REDIS_HOST`|`redis`|The redis host which will be used for caching|
|`RUNTIME_CACHE_REDIS_PORT`|`6379`|The redis port which will be used for caching|
|`RUNTIME_CACHE_KEY_PREFIX`|`nginx-cache:`|The prefix for the cache keys|
|`RUNTIME_CACHE_DEBUG`|`false`|Toggles extra response headers for debugging|

### Security
|Variable|Default|Description|
|--------|-------------|-----------|
|`RUNTIME_UPLOAD_SIZE`|`32`|Set the maximum PHP upload size and Nginx's max request size|
|`RUNTIME_BLOCK_NON_WP_PATHS`|`false`|Block non standard WordPress paths|

### SMTP

|Variable|Default|Description|
|--------|-------------|-----------|
|`SMTP_HOST`|`localhost`|SMTP relay host|
|`SMTP_USER`|``|SMTP relay username|
|`SMTP_PASSWORD`|``|SMTP relay password|
|`SMTP_PORT`|`587`|SMTP relay port|
|`SMTP_TLS`|`on`|SMTP relay TLS|
|`SMTP_STARTTLS`|`on`|SMTP relay STARTTLS|
