#!/bin/bash

# Update nginx to match worker_processes to no. of cpu's
procs=$(cat /proc/cpuinfo | grep processor | wc -l)
sed -i -e "s/worker_processes  1/worker_processes $procs/" /etc/nginx/nginx.conf

# Always chown webroot for better mounting
chown -Rf nginx.nginx /usr/share/nginx/html
chmod +w /home/nginx/.acme.sh/ssl.dockbox.nl
chown -Rf nginx.nginx /home/nginx/.acme.sh/ssl.dockbox.nl

#echo "CF_Token=$CF_Token"
#echo "CF_Account_ID=$CF_Account_ID"
#echo "CF_Zone_ID=$CF_Zone_ID"


# export CF_Token=""
# export CF_Account_ID=""
# export CF_Zone_ID=""
if [ "$CF_Token" != "" ];then
  if [ ! -f /home/nginx/.acme.sh/ssl.dockbox.nl/ssl.dockbox.nl.cer ];then
    su - nginx -c "export CF_Token="$CF_Token" && export CF_Account_ID="$CF_Account_ID" && export CF_Zone_ID="$CF_Zone_ID" && /home/nginx/.acme.sh/acme.sh --log --issue -d ssl.dockbox.nl  -d '*.ssl.dockbox.nl'  --dns dns_cf"
  fi
fi

# Start supervisord and services
/usr/local/bin/supervisord -n -c /etc/supervisord.conf
