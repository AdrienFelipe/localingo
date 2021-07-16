PHP_STAN_LEVEL=7
DOCKER_MODE?=exec
env=dev
COMPOSE=docker-compose -f docker-compose.yml

CONTAINER=php
PLATFORM='./active-framework'
EXEC=$(COMPOSE) $(DOCKER_MODE) $(CONTAINER)
# Execute container as current user
EXEC_U=$(COMPOSE) $(DOCKER_MODE) $(CONTAINER) su app -c


.PHONY: start
start: erase build up setup install ## clean current environment, recreate dependencies and spin up again

.PHONY: stop
stop: ## stop environment
		$(COMPOSE) stop

.PHONY: erase
erase: stop ## stop and delete containers, clean volumes.
		$(COMPOSE) rm -v -f

.PHONY: build
build: ## build environment and initialize composer and project dependencies
		$(COMPOSE) build

.PHONY: up
up: ## spin up environment
		$(COMPOSE) up -d

.PHONY: setup
setup: ## spin up environment
		$(EXEC) adduser -Du $$(id -u) -h /app app app

.PHONY: install
install: ## Install project dependencies
		$(EXEC_U) 'COMPOSER_MEMORY_LIMIT=-1 composer install --working-dir=$(PLATFORM)'

.PHONY: composer
composer: ## Execute composer
		$(EXEC_U) "COMPOSER_MEMORY_LIMIT=-1 composer --working-dir=$(PLATFORM) $(filter-out $@,$(MAKECMDGOALS))"

.PHONY: db
db: ## recreate database
		$(COMPOSE) ${DOCKER_MODE} php sh -lc './bin/console d:d:d --if-exists --force'
		$(COMPOSE) ${DOCKER_MODE} php sh -lc './bin/console d:d:c'
		$(COMPOSE) ${DOCKER_MODE} php sh -lc './bin/console d:m:m -n'

.PHONY: schema-validate
schema-validate: ## validate database schema
		$(COMPOSE) ${DOCKER_MODE} php sh -lc './bin/console d:s:v'

.PHONY: test
test: ## execute project tests
		$(EXEC_U) "php $(PLATFORM)/bin/phpunit $(conf)"

.PHONY: cs
cs: ## Code Style (quality)
		$(EXEC_U) '$(PLATFORM)/vendor/bin/php-cs-fixer --no-interaction --diff -v fix'

 .PHONY: cs-check
cs-check: ## executes php cs fixer in dry run mode
		$(COMPOSE) ${DOCKER_MODE} php sh -lc './vendor/bin/php-cs-fixer --no-interaction --dry-run --diff -v fix'

.PHONY: ca
ca: ## Code Analyzers (quality)
		$(COMPOSE) ${DOCKER_MODE} php sh -lc './vendor/bin/phpstan analyse -l ${PHP_STAN_LEVEL} -c phpstan.neon src tests'
		$(COMPOSE) ${DOCKER_MODE} php sh -lc './vendor/bin/psalm --show-info=false'
		$(COMPOSE) ${DOCKER_MODE} php sh -lc 'php bin/deptrac.phar analyze --formatter-graphviz=0'

.PHONY: sh
sh: ## gets inside a container (make sh php)
		docker-compose exec $(filter-out $@,$(MAKECMDGOALS)) sh -l

.PHONY: console
console: ## Execute Symfony console.
		$(EXEC_U) "$(PLATFORM)/bin/console $(filter-out $@,$(MAKECMDGOALS))"

.PHONY: xconsole
xconsole: ## Execute Symfony console. with xDebug enabled
		docker-compose exec php sh -lc "export PHP_IDE_CONFIG='serverName=localingo' && php -dxdebug.remote_host=172.17.0.1 ./bin/console $(filter-out $@,$(MAKECMDGOALS))"

.PHONY: logs
logs: ## look for 's' service logs, make s=php logs
		docker-compose logs -f $(s)

.PHONY: help
help: ## Display this help message
	@cat $(MAKEFILE_LIST) | grep -e "^[a-zA-Z_\-]*: *.*## *" | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[36m%-30s\033[0m %s\n", $$1, $$2}'
