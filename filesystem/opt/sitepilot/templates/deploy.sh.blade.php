#!/bin/bash

set -a
set -e
set -o pipefail

# ----- Deploy Key ----- #

@if($deploy['key'])
runtime log "Configuring SSH-key"
echo "{{ $deploy['key'] }}" > $APP_PATH_AUTH/id_rsa_deploy
chmod 600 $APP_PATH_AUTH/id_rsa_deploy
@endif

# ----- Clone ----- #

@if($deploy['repository'] && $deploy['branch'])
DEPLOY_PATH=$APP_PATH_DEPLOY/$(date +%s)
RELEASE_PATH=$APP_PATH_PUBLIC/{{ $deploy['path'] }}
RELEASE_DIR=${RELEASE_PATH%/*}

GIT_SSH_COMMAND="ssh -i $APP_PATH_AUTH/id_rsa_deploy -o UserKnownHostsFile=/dev/null -o StrictHostKeyChecking=no" git clone --single-branch --branch {{ $deploy['branch'] }} {{ $deploy['repository'] }} $DEPLOY_PATH

# ----- Post Clone Script ----- #

cd $DEPLOY_PATH

{!! $deploy['postCloneScript'] !!}

# ----- Activate ----- #

rm -rf $RELEASE_PATH
mkdir -p $RELEASE_DIR
ln -s $DEPLOY_PATH $RELEASE_PATH

# ----- Post Activate Script ----- #

{!! $deploy['postActivateScript'] !!}

@endif

# ----- Cleanup ----- #

runtime log "Cleanup old deployments"

(cd $APP_PATH_DEPLOY && ls -lt | tail -n +4 | awk '{print $9}' | xargs rm -rf)

# ----- End ----- #