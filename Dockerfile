#
# MAINTAINER        Allen Luo <movoin@gmail.com>
# DOCKER-VERSION    1.12.3
#

FROM        movoin/devops-swoole:2
MAINTAINER  Allen Luo <movoin@gmail.com>

ENV REDIS_DB_NAME       redis.rdb
ENV REDIS_DB_HOST       127.0.0.1
ENV REDIS_DB_PORT       6379
ENV REDIS_DB_PATH       /opt/data/redis/

COPY dockerfiles/ /opt/docker/

RUN set -x \
    # Install
    && /opt/docker/bin/install.sh \
    # Bootstrap
    && /opt/docker/bin/bootstrap.sh \
    # Clean up
    && yum clean all

WORKDIR /app
