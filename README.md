# Sitepilot Runtime

Docker runtime image used for running applications on the Sitepilot managed hosting platform.

## Environment Variables

|Variable|Default|Description|
|--------|-------------|-----------|
|`APP_ENV`|`production`|The application environment|
|`DEPLOY_TOKEN`|`random`|The webhook deploy secret / token|
|`DEPLOY_BRANCH`|`main`|The branch which needs to be cloned|
|`DEPLOY_SSH_KEY`|`none`|The repository deploy key, necessary when cloning a repository over SSH|
|`DEPLOY_REPOSITORY`|`none`|The repository which will be cloned on container startup or incoming webhook|

## Volumes

To persist your data you need to mount `/opt/sitepilot/app/public` to a Docker volume or local folder.

## Ports

The Nginx webserver is listening to port `8080`.

## Configuration 

You can override the runtime configuration by creating / mounting a runtime configuration file to `public/.sitepilot/runtime.yml` or by adding the file to the root of the deployed repository `<repository-root>/.sitepilot/runtime.yml`. The application runtime file wil be merged with the default configuration.

[You can use the default runtime configuration file for reference.](filesystem/opt/sitepilot/runtime.yml)

## Auto Deployment

To automatically deploy your application after each push to GitHub, GitLab or BitBucket you need to configure a webhook. You can point your webhook to the following URL:

```
# GitHub
https://<url>/-/webhooks/github

# GitLab
https://<url>/-/webhooks/gitlab

# BitBucket
https://<url>/-/webhooks/bitbucket
```

Your code will be cloned after each push (to the configured `$DEPLOY_BRANCH`) and your deployment script from `runtime.yml` will be executed. After a successfull deployment the webserver wil be pointed to the new deployment directory and your updated code is served to your visitors.
