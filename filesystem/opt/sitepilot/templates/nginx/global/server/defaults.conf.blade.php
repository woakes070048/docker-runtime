# Should be included for most sites, as contains sensible defaults
# for file exclusions, security and static file caching.

# Exclusions
include global/server/exclusions.conf;

# Security
include global/server/security.conf;

# Static Content
include global/server/static-files.conf;

# Forwarded Proto
if ($http_x_forwarded_proto = 'https') {
    set $forwarded_https 'on';
}
