DOCKER := $(if $(shell [ -w /var/run/docker.sock ] && echo yes),docker,sudo docker)


build:
	$(DOCKER) build -t isrp-api .

start: build
	$(DOCKER) run -d --rm \
		-v $(shell pwd)/src:/app/src \
		-v $(shell pwd)/service-account.json:/app/service-account.json \
		--add-host smtp-server:172.17.0.1 \
		--env-file=.secrets \
		--name isrp-api isrp-api
	bash -c "trap '${DOCKER} kill isrp-api' EXIT; ${DOCKER} logs -f isrp-api"

stop:
	$(DOCKER) kill isrp-api
