source /opt/docker/bin/functions.sh

pecl install redis-4.1.1

copyFileTo "/opt/docker/etc/php/extends/redis.ini" "/etc/php/conf.d/zz-redis.ini"

rm -f /opt/docker/etc/php/extends/redis.ini
