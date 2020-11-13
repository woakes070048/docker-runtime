[{
        "id": "github",
        "execute-command": "/opt/sitepilot/etc/deploy.sh",
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
                        "secret": "{{ !empty($deploy['token']) ? $deploy['token'] : uniqid() }}",
                        "parameter": {
                            "source": "header",
                            "name": "X-Hub-Signature"
                        }
                    }
                },
                {
                    "match": {
                        "type": "value",
                        "value": "refs/heads/{{ $deploy['branch'] }}",
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
        "execute-command": "/opt/sitepilot/etc/deploy.sh",
        "command-working-directory": "{{ env('APP_PATH') }}",
        "pass-arguments-to-command": [{
            "source": "payload",
            "name": "actor.username"
        }],
        "trigger-rule": {
            "match": {
                "type": "{{ !empty($deploy['token']) ? $deploy['token'] : uniqid() }}",
                "ip-range": "104.192.143.0/24"
            }
        }
    },
    {
        "id": "gitlab",
        "execute-command": "/opt/sitepilot/etc/deploy.sh",
        "command-working-directory": "{{ env('APP_PATH') }}",
        "pass-arguments-to-command": [{
            "source": "payload",
            "name": "user_name"
        }],
        "response-message": "Executing redeploy script",
        "trigger-rule": {
            "match": {
                "type": "value",
                "value": "{{ !empty($deploy['token']) ? $deploy['token'] : uniqid() }}",
                "parameter": {
                    "source": "header",
                    "name": "X-Gitlab-Token"
                }
            }
        }
    }
]