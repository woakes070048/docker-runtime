docRoot                   $VH_ROOT/public{{ !empty($server['root']) ? '/' . $server['root'] : '' }}
cgroups                   0
user                      10000
group                     10000

rewrite  {
    enable                1
    autoLoadHtaccess      1
}

errorlog $VH_ROOT/logs/error.log {
    useServer             0
    logLevel              NOTICE
    rollingSize           10M
    keepDays              7
}

accesslog $VH_ROOT/logs/access.log {
    useServer             0
    logFormat             %a %l %u %t "%r" %>s %O "%{Referer}i" "%{User-Agent}i"
    logHeaders            5
    rollingSize           10M
    keepDays              7
}

accesslog $VH_ROOT/logs/transfer.log {
    useServer             0
    logFormat             %O
    rollingSize           0
}

accesslog $VH_ROOT/logs/visitors.log {
    useServer             0
    logFormat             %a
    rollingSize           0
}

@foreach($server['basicAuth'] as $key=>$auth) 
realm {{ $key }}-htpasswd {
    userDB  {
        location          /opt/sitepilot/app/.auth/{{ $key }}-htpasswd
    }
}

context {{ $auth['location'] }} {
    realm                 {{ $key }}-htpasswd
    allowBrowse           1
}
@endforeach

context /-/health/ {
    location              /opt/sitepilot/etc/health/
    allowBrowse           1
    rewrite  {
      enable              1
      inherit             0
    }
}

context /-/webhooks/ {
    type                    proxy
    handler                 webhooks
    addDefaultCharset       off
}
