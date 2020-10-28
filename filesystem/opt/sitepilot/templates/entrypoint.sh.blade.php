#!/bin/bash

set -a
set -e
set -o pipefail

# ----- Filesystem ----- #

mkdir -p $COMPOSER_HOME
mkdir -p $APP_PATH_PUBLIC
mkdir -p $APP_PATH_DEPLOY
mkdir -p $APP_PATH_SSH

# ----- SSH-Keys ----- #

SSH_HOST_DSA_KEY=$APP_PATH_SSH/ssh_host_dsa_key
SSH_HOST_RSA_KEY=$APP_PATH_SSH/ssh_host_rsa_key
SSH_HOST_ED25519_KEY=$APP_PATH_SSH/ssh_host_ed25519_key

if [ ! -f "$SSH_HOST_DSA_KEY" ]; then runtime log "Generating dsa key to $SSH_HOST_DSA_KEY" && ssh-keygen -q -N "" -t dsa -f $SSH_HOST_DSA_KEY; fi
if [ ! -f "$SSH_HOST_RSA_KEY" ]; then runtime log "Generating rsa key to $SSH_HOST_RSA_KEY" && ssh-keygen -q -N "" -t rsa -b 4096 -f $SSH_HOST_RSA_KEY; fi
if [ ! -f "$SSH_HOST_ED25519_KEY" ]; then runtime log "Generating ed25519 key to $SSH_HOST_ED25519_KEY" && ssh-keygen -q -N "" -t ed25519 -f $SSH_HOST_ED25519_KEY; fi

@if(!empty($user['privateKey']))
runtime log "Saving user private key"
echo "{{ $user['privateKey'] }}" > /opt/sitepilot/app/.ssh/id_rsa
chmod 600 /opt/sitepilot/app/.ssh/id_rsa
@endif

runtime log "Saving user authorized keys"
@foreach($user['authorizedKeys'] as $key)
echo "{{ $key }}" >> /opt/sitepilot/app/.ssh/authorized_keys
chmod 600 /opt/sitepilot/app/.ssh/authorized_keys
@endforeach

# ----- User Mods ----- #

@if(!empty($user['password']))
runtime log "Updating user password"
echo "www-data:{{ $user['password'] }}" | sudo -S chpasswd
@endif

@if(!empty($user['name']))
runtime log "Updating user name"
sudo usermod -l {{ $user['name'] }} www-data
@else 
runtime log "Removing sudo privileges"
sudo sed -i '$ d' /etc/sudoers
@endif

# ----- End ----- #