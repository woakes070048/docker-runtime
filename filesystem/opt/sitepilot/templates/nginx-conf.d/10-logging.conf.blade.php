# vim: set ft=nginx:

access_log {{ $nginx['accessLog'] }};
error_log {{ $nginx['errorLog'] }} {{ $nginx['errorLogLevel'] }};
