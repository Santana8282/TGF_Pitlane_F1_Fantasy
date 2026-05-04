#!/bin/bash
echo "server {
    listen 8080;
    root /home/site/wwwroot;
    index index.php index.html;

    location / {
        try_files \$uri \$uri/ /index.php\$is_args\$args;
    }

    location ~ \.php\$ {
        fastcgi_pass 127.0.0.1:9000;
        fastcgi_index index.php;
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME \$document_root\$fastcgi_script_name;
    }
}" > /home/site/nginxconf/default.conf

service nginx reload 2>/dev/null || true
