#!/bin/bash

set -a
set -e
set -o pipefail

# ----- Filesystem ----- #

@if(env('UPDATE_FILE_PERMISSIONS', false))
runtime log "Updating file permissions"
sudo chown -R www-data:www-data /opt/sitepilot/app
@endif

mkdir -p $COMPOSER_HOME
mkdir -p $APP_PATH_PUBLIC
mkdir -p $APP_PATH_DEPLOY
mkdir -p $APP_PATH_AUTH
mkdir -p $APP_PATH_LOGS

# ----- SSH-Keys ----- #

SSH_HOST_DSA_KEY=$APP_PATH_AUTH/ssh_host_dsa_key
SSH_HOST_RSA_KEY=$APP_PATH_AUTH/ssh_host_rsa_key
SSH_HOST_ED25519_KEY=$APP_PATH_AUTH/ssh_host_ed25519_key

if [ ! -f "$SSH_HOST_DSA_KEY" ]; then runtime log "Generating dsa key to $SSH_HOST_DSA_KEY" && ssh-keygen -q -N "" -t dsa -f $SSH_HOST_DSA_KEY; fi
if [ ! -f "$SSH_HOST_RSA_KEY" ]; then runtime log "Generating rsa key to $SSH_HOST_RSA_KEY" && ssh-keygen -q -N "" -t rsa -b 4096 -f $SSH_HOST_RSA_KEY; fi
if [ ! -f "$SSH_HOST_ED25519_KEY" ]; then runtime log "Generating ed25519 key to $SSH_HOST_ED25519_KEY" && ssh-keygen -q -N "" -t ed25519 -f $SSH_HOST_ED25519_KEY; fi

@if($user['privateKey'])
runtime log "Saving user private key"
echo "{{ $user['privateKey'] }}" > $APP_PATH_AUTH/id_rsa
chmod 600 $APP_PATH_AUTH/id_rsa
@endif

@if(count($user['authorizedKeys']) > 0)
runtime log "Saving user authorized keys"
echo "{{ implode("\n", $user['authorizedKeys']) }}" > $APP_PATH_AUTH/id_rsa
chmod 600 $APP_PATH_AUTH/id_rsa
@endif

# ----- Basic Auth ----- #

@foreach($nginx['basicAuth'] as $key=>$auth) 
runtime log "Setup htpasswd file for path '{{ $auth['location'] }}'"
echo '{{ $auth['users'] }}' > $APP_PATH_AUTH/{{ $key }}-htpasswd
@endforeach

# ----- ZSH ----- #

ZSH_RCFILE=$APP_PATH/.zshrc
rm -f $ZSH_RCFILE

echo 'compinit -d /temp/.zcompdump' >> $ZSH_RCFILE
echo 'export ZSH="/opt/sitepilot/ohmyzsh"' > $ZSH_RCFILE
echo 'export ZSH_THEME="robbyrussell"' >> $ZSH_RCFILE
echo 'export DISABLE_UPDATE_PROMPT=true' >> $ZSH_RCFILE
echo 'export DISABLE_AUTO_UPDATE=true' >> $ZSH_RCFILE
echo "export ZSH_CACHE_DIR=$APP_PATH/.cache" >> $ZSH_RCFILE
echo "export ZSH_COMPDUMP=$APP_PATH/.cache/.zcompdump" >> $ZSH_RCFILE
echo 'plugins=(git)' >> $ZSH_RCFILE
echo 'source $ZSH/oh-my-zsh.sh' >> $ZSH_RCFILE

# ----- User Mods ----- #

if [ "$1" != '--skip-usermods' ]; then
@if($user['password'])
runtime log "Updating user password"
echo 'www-data:{{ $user['password'] }}' | sudo -S chpasswd -e
@endif

@if($user['name'])
runtime log "Updating user name"
sudo usermod -l {{ $user['name'] }} -s /bin/zsh www-data
@else
runtime log "Removing sudo privileges"
sudo sed -i '$ d' /etc/sudoers
@endif
fi

# ----- End ----- #