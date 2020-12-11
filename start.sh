#!/bin/bash

# Update nginx to match worker_processes to no. of cpu's
procs=$(cat /proc/cpuinfo | grep processor | wc -l)
sed -i -e "s/worker_processes  1/worker_processes $procs/" /etc/nginx/nginx.conf

# Always chown webroot for better mounting
chown -Rf nginx.nginx /usr/share/nginx/html


echo "OAUTH_DISCOVERY $OAUTH_DISCOVERY"
# export CF_Token=""
# export CF_Account_ID=""
# export CF_Zone_ID=""
su - nginx -c "/home/nginx/.acme.sh/acme.sh  --issue -d ssl.dockbox.nl  -d '*.ssl.dockbox.nl'  --dns dns_cf"


# Start supervisord and services
/usr/local/bin/supervisord -n -c /etc/supervisord.conf
