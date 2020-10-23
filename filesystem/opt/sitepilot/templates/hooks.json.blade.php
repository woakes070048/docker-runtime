[{
        "id": "github",
        "execute-command": "/opt/sitepilot/bin/deploy",
        "command-working-directory": "{{ env('APP_PATH') }}",
        "pass-arguments-to-command": [{
                "source": "payload",
                "name": "head_commit.id"
            },
            {
                "source": "payload",
                "name": "pusher.name"
            },
            {
                "source": "payload",
                "name": "pusher.email"
            }
        ],
        "trigger-rule": {
            "and": [{
                    "match": {
                        "type": "payload-hash-sha1",
                        "secret": "{{ env('DEPLOY_TOKEN', uniqid()) }}",
                        "parameter": {
                            "source": "header",
                            "name": "X-Hub-Signature"
                        }
                    }
                },
                {
                    "match": {
                        "type": "value",
                        "value": "refs/heads/{{ env('DEPLOY_BRANCH') }}",
                        "parameter": {
                            "source": "payload",
                            "name": "ref"
                        }
                    }
                }
            ]
        }
    },
    {
        "id": "bitbucket",
        "execute-command": "/opt/sitepilot/bin/deploy",
        "command-working-directory": "{{ env('APP_PATH') }}",
        "pass-arguments-to-command": [{
            "source": "payload",
            "name": "actor.username"
        }],
        "trigger-rule": {
            "match": {
                "type": "{{ env('DEPLOY_TOKEN', uniqid()) }}",
                "ip-range": "104.192.143.0/24"
            }
        }
    },
    {
        "id": "gitlab",
        "execute-command": "/opt/sitepilot/bin/deploy",
        "command-working-directory": "{{ env('APP_PATH') }}",
        "pass-arguments-to-command": [{
            "source": "payload",
            "name": "user_name"
        }],
        "response-message": "Executing redeploy script",
        "trigger-rule": {
            "match": {
                "type": "value",
                "value": "{{ env('DEPLOY_TOKEN', uniqid()) }}",
                "parameter": {
                    "source": "header",
                    "name": "X-Gitlab-Token"
                }
            }
        }
    }
]