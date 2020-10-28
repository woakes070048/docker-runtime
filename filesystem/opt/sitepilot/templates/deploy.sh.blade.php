#!/bin/bash

set -a
set -e
set -o pipefail

# ----- Deploy Key ----- #

@if($deploy['key'])
runtime log "Configuring SSH-key"
echo "{{ $deploy['key'] }}" > $APP_PATH_SSH/id_rsa_deploy
chmod 600 $APP_PATH_SSH/id_rsa_deploy
@endif

# ----- Clone ----- #

@if(!empty($deploy['repository']) && !empty($deploy['path']) && !empty($deploy['branch']))
DEPLOY_PATH=$APP_PATH_DEPLOY/$(date +%s)
RELEASE_PATH=$APP_PATH_PUBLIC/{{ $deploy['path'] }}
RELEASE_DIR=${RELEASE_PATH%/*}

GIT_SSH_COMMAND="ssh -i $APP_PATH_SSH/id_rsa_deploy -o UserKnownHostsFile=/dev/null -o StrictHostKeyChecking=no" git clone --single-branch --branch {{ $deploy['branch'] }} {{ $deploy['repository'] }} $DEPLOY_PATH

# ----- Post Clone Script ----- #

cd $DEPLOY_PATH

{!! $deploy['hooks']['postCloneScript'] !!}

# ----- Linked Folders ----- #

@foreach($deploy['linkedFolders'] as $folder)
if [ ! -d $APP_PATH_PUBLIC/{{ $folder }} ]; then
  mv $DEPLOY_PATH/{{ $folder }} $APP_PATH_PUBLIC/{{ $folder }}
else
  rm -r $DEPLOY_PATH/{{ $folder }}
fi

ln -s $APP_PATH_PUBLIC/{{ $folder }} $DEPLOY_PATH/{{ $folder }}
@endforeach

# ----- Activate ----- #

rm -rf $RELEASE_PATH
mkdir -p $RELEASE_DIR
ln -s $DEPLOY_PATH $RELEASE_PATH

# ----- Post Activate Script ----- #

{!! $deploy['hooks']['postActivateScript'] !!}
@endif

# ----- Cleanup ----- #

runtime log "Cleanup old deployments"
(cd $APP_PATH_DEPLOY && ls -lt | tail -n +4 | awk '{print $9}' | xargs rm -rf)

# ----- End ----- #
