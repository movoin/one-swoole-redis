ARGS = $(filter-out $@,$(MAKECMDGOALS))
MAKEFLAGS += --silent

#############################
# Docker machine states
#############################

rebuild:
	docker build -t one/swoole/redis $$(pwd)/

up:	clean_ds run

run:
	docker run -it -d --name one_swoole_redis -v $$(pwd):/app one/swoole/redis

down:
	docker stop one_swoole_redis && docker rm one_swoole_redis

start:
	docker start one_swoole_redis

stop:
	docker stop one_swoole_redis

ssh:
	docker exec -it -u app one_swoole_redis bash

root:
	docker exec -it one_swoole_redis bash

tail:
	docker logs -f one_swoole_redis

clean_ds:
	find . -name .DS_Store -print0 | xargs -0 rm -f


#############################
# Argument fix workaround
#############################
%:
	@:
