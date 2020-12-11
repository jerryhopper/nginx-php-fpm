#!/bin/bash

# Update nginx to match worker_processes to no. of cpu's
procs=$(cat /proc/cpuinfo | grep processor | wc -l)
sed -i -e "s/worker_processes  1/worker_processes $procs/" /etc/nginx/nginx.conf

# Always chown webroot for better mounting
chown -Rf nginx.nginx /usr/share/nginx/html

chmod +w /home/nginx/.acme.sh/$SSL_WILDCARD_DOM
chown -Rf nginx.nginx /home/nginx/.acme.sh/$SSL_WILDCARD_DOM

#echo "CF_Token=$CF_Token"
#echo "CF_Account_ID=$CF_Account_ID"
#echo "CF_Zone_ID=$CF_Zone_ID"


# export CF_Token=""
# export CF_Account_ID=""
# export CF_Zone_ID=""
if [ "$SSL_WILDCARD_DOM" != "" ];then
  if [ ! -f "/home/nginx/.acme.sh/$SSL_WILDCARD_DOM/$SSL_WILDCARD_DOM.cer" ];then
    su - nginx -c "export CF_Token="$CF_TOKEN" && export CF_Account_ID="$CF_ACCOUNT_ID" && export CF_Zone_ID="$CF_ZONE_ID" && /home/nginx/.acme.sh/acme.sh --log --issue -d $SSL_WILDCARD_DOM  -d *.$SSL_WILDCARD_DOM  --dns dns_cf"
  fi
fi

# Start supervisord and services
/usr/local/bin/supervisord -n -c /etc/supervisord.conf
