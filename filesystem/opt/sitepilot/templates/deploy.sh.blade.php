#!/bin/bash

set -a
set -e
set -o pipefail

@if($deploy['key'])
runtime log "Configuring SSH-key"
mkdir -p ~/.ssh
echo "{{ $deploy['key'] }}" > ~/.ssh/id_rsa
chmod 600 ~/.ssh/id_rsa
@endif

@if(!empty($deploy['repository']) && !empty($deploy['path']) && !empty($deploy['branch']))
DEPLOY_PATH=$APP_PATH_DEPLOY/$(date +%s)
RELEASE_PATH=$APP_PATH/public/{{ $deploy['path'] }}
RELEASE_DIR=${RELEASE_PATH%/*}

GIT_SSH_COMMAND="ssh -o UserKnownHostsFile=/dev/null -o StrictHostKeyChecking=no" git clone --single-branch --branch {{ $deploy['branch'] }} {{ $deploy['repository'] }} $DEPLOY_PATH

cd $DEPLOY_PATH

{!! $deploy['hooks']['postCloneScript'] !!}

@foreach($deploy['linkedFolders'] as $folder)
if [ ! -d $APP_PATH/public/{{ $folder }} ]; then
  mv $DEPLOY_PATH/{{ $folder }} $APP_PATH/public/{{ $folder }}
else
  rm -r $DEPLOY_PATH/{{ $folder }}
fi

ln -s $APP_PATH/public/{{ $folder }} $DEPLOY_PATH/{{ $folder }}
@endforeach

rm -rf $RELEASE_PATH
mkdir -p $RELEASE_DIR
ln -s $DEPLOY_PATH $RELEASE_PATH

{!! $deploy['hooks']['postActivateScript'] !!}
@endif

runtime log "Cleanup old deployments"

(cd $APP_PATH_DEPLOY && ls -lt | tail -n +4 | awk '{print $9}' | xargs rm -rf)