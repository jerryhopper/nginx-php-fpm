#FROM debian:buster
FROM wyveo/nginx-php-fpm:php72

LABEL maintainer="JH hopper.jerry@gmail.com"

# Let the container know that there is no tty
#ENV DEBIAN_FRONTEND noninteractive
#ENV NGINX_VERSION 1.19.5-1~buster
#ENV php_conf /etc/php/7.2/fpm/php.ini
#ENV fpm_conf /etc/php/7.2/fpm/pool.d/www.conf
#ENV COMPOSER_VERSION 1.10.17

#RUN addgroup --gid 101 nginx
#RUN adduser --home /home/nginx cron

#ADD crontab /etc/cron.d/hello-cron
# Give execution rights on the cron job
#RUN chmod 0644 /etc/cron.d/hello-cron
# Create the log file to be able to run tail
#RUN touch /var/log/cron.log

RUN apt-get update && apt-get install -q -y cron nano curl
#RUN su - nginx -c "echo ''|crontab -e"
# Add crontab file in the cron directory

RUN ls -latr /etc/cron.d

#RUN su - nginx -c "curl https://get.acme.sh | sh"

#RUN ls -latr /etc/cron.d

## ENVIROMENT VARIABLES
ARG CF_TOKEN
ENV CF_TOKEN=$CF_TOKEN

ARG CF_ACCOUNT_ID
ENV CF_ACCOUNT_ID=$CF_ACCOUNT_ID

ARG CF_ZONE_ID
ENV CF_ZONE_ID=$CF_ZONE_ID

ARG OAUTH_DISCOVERY
ENV OAUTH_DISCOVERY=$OAUTH_DISCOVERY

ARG OAUTH_CLIENT_ID
ENV OAUTH_CLIENT_ID=$OAUTH_CLIENT_ID

ARG OAUTH_CLIENT_SECRET
ENV OAUTH_CLIENT_SECRET=$OAUTH_CLIENT_SECRET

ARG OAUTH_TENANT_ID
ENV OAUTH_TENANT_ID=$OAUTH_TENANT_ID

ARG OAUTH_REDIR_URL
ENV OAUTH_REDIR_URL=$OAUTH_REDIR_URL

ARG SSL_WILDCARD_DOM
ENV SSL_WILDCARD_DOM=$SSL_WILDCARD_DOM

#COPY ./code /usr/share/nginx/html
#sudo usermod -d /var/lib/mysql/ mysql

RUN ls -latr /home
RUN mkdir -p /home/nginx
RUN chown nginx:nginx /home/nginx
RUN mkdir -p /home/nginx/.acme.sh/ssl.dockbox.nl
RUN chown -R nginx:nginx /home/nginx/.acme.sh

RUN usermod -s /bin/bash -d /home/nginx nginx
RUN su - nginx -c "curl https://get.acme.sh | sh"
RUN ls -latr /etc/cron.d \
    # Clean up
    && apt-get clean \
    && apt-get autoremove \
    && rm -rf /var/lib/apt/lists/*


# Supervisor config
ADD ./supervisord.conf /etc/supervisord.conf

# Override nginx's default config
ADD ./default.conf /etc/nginx/conf.d/default.conf

# Override default nginx welcome page
ADD ./code /usr/share/nginx/html
#COPY code /usr/share/nginx/html

RUN ls -latr /usr/share/nginx/html
RUN cd /usr/share/nginx/html && composer -vvv install
RUN mkdir -p /usr/share/nginx/html/cache
RUN chown -R nginx:nginx /usr/share/nginx/html/cache

# Add Scripts
ADD ./start.sh /start.sh
RUN chmod +x ./start.sh

EXPOSE 80

CMD ["/start.sh"]
