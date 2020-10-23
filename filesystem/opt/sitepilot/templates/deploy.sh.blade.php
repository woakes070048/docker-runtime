#!/bin/bash

set -a
set -e
set -o pipefail

deployPath=$1
releasePath=$APP_PATH/public/{{ $deploy['path'] }}
releaseDir=${releasePath%/*}

{!! $deploy['hooks']['postCloneScript'] !!}

@foreach($deploy['linkedFolders'] as $folder)
if [ ! -d $APP_PATH/public/{{ $folder }} ]; then 
    mv $deployPath/{{ $folder }} $APP_PATH/public/{{ $folder }}
else 
    rm -rf $deployPath/{{ $folder }}
fi

ln -s $APP_PATH/public/{{ $folder }} $deployPath/{{ $folder }} 
@endforeach

if ! openresty -p /var/lib/nginx -c /opt/sitepilot/etc/nginx.conf -t; then
    echo "Nginx config failed"
    exit 1
fi

if ! php-fpm{{ env('PHP_VERSION', '7.4') }} -y /opt/sitepilot/etc/php-fpm.conf -t; then 
    echo "PHP config test failed"
    exit 1
fi

rm -rf $releasePath
mkdir -p $releaseDir
ln -s $deployPath $releasePath

if [ $2 != '--skip-reload' ]; then
    supervisorctl --username sitepilot --password secret restart web:php-fpm
    supervisorctl --username sitepilot --password secret restart web:nginx
fi

{!! $deploy['hooks']['postActivateScript'] !!}
