location ~ ^/-/webhooks(.+)$ {
    proxy_pass http://127.0.0.1:9000;
}