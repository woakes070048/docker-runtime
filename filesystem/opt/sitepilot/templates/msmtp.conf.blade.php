defaults
auth           on
tls            on
tls_trust_file /etc/ssl/certs/ca-certificates.crt
syslog         off
logfile        /dev/stderr
logfile_time_format time="%Y-%m-%dT%H:%M:%SZ"

account        primary
host           {{ $smtp['host'] }}
port           {{ $smtp['port'] }}
tls            {{ $smtp['tls'] }}
tls_starttls   {{ $smtp['tlsStarttls'] }}

@if(!empty($smtp['user']) && !empty($smtp['password']))
auth           on
user           {{ $smtp['user'] }}
password       {{ $smtp['password'] }}
@endif

account default : primary
aliases        /etc/aliases
