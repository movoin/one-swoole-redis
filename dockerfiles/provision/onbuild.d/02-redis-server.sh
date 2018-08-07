source /opt/docker/bin/functions.sh

function replaceRedisConfs()
{
	find /usr/local/redis/etc/ -iname '*.conf' -print0 | xargs -0 -r docker-replace --quiet "${1}" "${2}"
}

cd /tmp
wget http://download.redis.io/releases/redis-4.0.10.tar.gz
tar xvf redis-4.0.10.tar.gz
cd ./redis-4.0.10/

make
make PREFIX=/usr/local/redis install

mkdir /usr/local/redis/etc/
mkdir /usr/local/redis/logs/
mkdir -p ${REDIS_DB_PATH}

copyFileTo "/opt/docker/etc/redis/redis.conf" "/usr/local/redis/etc/redis.conf"

if [ ! -d "/opt/docker/.cache/redis" ];then
	mkdir -p /opt/docker/.cache/redis
	# Backup
	cp /opt/docker/etc/redis/redis.conf /opt/docker/.cache/redis
else
	cp -f /opt/docker/.cache/redis/redis.conf /opt/docker/etc/redis/redis.conf
fi

replaceRedisConfs "<REDIS_DB_NAME>" "${REDIS_DB_NAME}"
replaceRedisConfs "<REDIS_DB_HOST>" "${REDIS_DB_HOST}"
replaceRedisConfs "<REDIS_DB_PORT>" "${REDIS_DB_PORT}"
replaceRedisConfs "<REDIS_DB_PATH>" "${REDIS_DB_PATH}"

rm -f /opt/docker/etc/redis/redis.conf
rm -rf /tmp/redis-4.0.10/
