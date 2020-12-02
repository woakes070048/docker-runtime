#!/bin/bash

set -a
set -e
set -o pipefail

# ----- Deploy Private Key ----- #

@if($deploy['privateKey'])
runtime log "Configuring SSH-key"
echo "{{ $deploy['privateKey'] }}" > $APP_PATH_AUTH/id_rsa_deploy
chmod 600 $APP_PATH_AUTH/id_rsa_deploy
@endif

@if($deploy['repository'] && $deploy['branch'])

# ----- Clone ----- #

DEPLOY_PATH=$APP_PATH_DEPLOY/$(date +%s)
RELEASE_PATH=$APP_PATH_PUBLIC/{{ $deploy['path'] }}
RELEASE_DIR=${RELEASE_PATH%/*}

GIT_SSH_COMMAND="ssh -i $APP_PATH_AUTH/id_rsa_deploy -o UserKnownHostsFile=/dev/null -o StrictHostKeyChecking=no" git clone --single-branch --recurse-submodules --branch {{ $deploy['branch'] }} {{ $deploy['repository'] }} $DEPLOY_PATH

# ----- Post Clone Script ----- #

cd $DEPLOY_PATH

# ----- Presistent Data ----- #

@foreach($deploy['linkedFiles'] as $file)
if [ ! -f "$APP_PATH_DEPLOY_DATA/{{ $file }}" ]; then
    if [ -f "$DEPLOY_PATH/{{ $file }}" ]; then
        mv "$DEPLOY_PATH/{{ $file }}" "$APP_PATH_DEPLOY_DATA/{{ $file }}"
    else
        touch "$APP_PATH_DEPLOY_DATA/{{ $file }}"
    fi
fi
runtime log "Creating file symlink: $DEPLOY_PATH/{{ $file }}"
rm -rf "$DEPLOY_PATH/{{ $file }}"
ln -sf "$APP_PATH_DEPLOY_DATA/{{ $file }}" "$DEPLOY_PATH/{{ $file }}"
@endforeach

@foreach($deploy['linkedFolders'] as $folder)
if [ ! -d "$APP_PATH_DEPLOY_DATA/{{ $folder }}" ]; then
    if [ -d "$DEPLOY_PATH/{{ $folder }}" ]; then
        mv "$DEPLOY_PATH/{{ $folder }}" "$APP_PATH_DEPLOY_DATA/{{ $folder }}"
    else
        mkdir "$APP_PATH_DEPLOY_DATA/{{ $folder }}"
    fi
fi
runtime log "Creating folder symlink: $DEPLOY_PATH/{{ $folder }}"
rm -rf "$DEPLOY_PATH/{{ $folder }}"
ln -sf "$APP_PATH_DEPLOY_DATA/{{ $folder }}" "$DEPLOY_PATH/{{ $folder }}"
@endforeach

# ----- Post Clone Script ----- #

{!! $deploy['hooks']['postCloneScript'] !!}

# ----- Activate ----- #

rm -rf $RELEASE_PATH
mkdir -p $RELEASE_DIR
ln -s $DEPLOY_PATH $RELEASE_PATH

@foreach($supervisor['services'] as $service)
supervisorctl -u sitepilot -p secret restart {{ $service['name'] }}
@endforeach

# ----- Post Activate Script ----- #

{!! $deploy['hooks']['postActivateScript'] !!}

@endif

# ----- Cleanup ----- #

runtime log "Cleanup old deployments"

(cd $APP_PATH_DEPLOY && ls -lt | tail -n +4 | awk '{print $9}' | xargs rm -rf)

# ----- End ----- #