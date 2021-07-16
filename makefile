PHP_STAN_LEVEL=7
DOCKER_MODE?=exec -T
env=dev
compose=docker-compose -f docker-compose.yml

.PHONY: start
start: erase build up ## clean current environment, recreate dependencies and spin up again

.PHONY: stop
stop: ## stop environment
		$(compose) stop

.PHONY: erase
erase: ## stop and delete containers, clean volumes.
		$(compose) stop
		$(compose) rm -v -f

.PHONY: build
build: ## build environment and initialize composer and project dependencies
		$(compose) build
		#$(compose) run --rm php sh -lc 'COMPOSER_MEMORY_LIMIT=-1 composer install'

.PHONY: update
update: ## Update project dependencies
		$(compose) ${DOCKER_MODE} php sh -lc 'COMPOSER_MEMORY_LIMIT=-1 composer update'

.PHONY: up
up: ## spin up environment
		docker-compose up -d

.PHONY: db
db: ## recreate database
		$(compose) ${DOCKER_MODE} php sh -lc './bin/console d:d:d --if-exists --force'
		$(compose) ${DOCKER_MODE} php sh -lc './bin/console d:d:c'
		$(compose) ${DOCKER_MODE} php sh -lc './bin/console d:m:m -n'

.PHONY: schema-validate
schema-validate: ## validate database schema
		$(compose) ${DOCKER_MODE} php sh -lc './bin/console d:s:v'

.PHONY: test
test: ## execute project tests
		$(compose) ${DOCKER_MODE} php sh -lc "php ./bin/phpunit $(conf)"

.PHONY: cs
cs: ## Code Style (quality)
		$(compose) ${DOCKER_MODE} php sh -lc './vendor/bin/php-cs-fixer --no-interaction --diff -v fix'

 .PHONY: cs-check
cs-check: ## executes php cs fixer in dry run mode
		$(compose) ${DOCKER_MODE} php sh -lc './vendor/bin/php-cs-fixer --no-interaction --dry-run --diff -v fix'

.PHONY: ca
ca: ## Code Analyzers (quality)
		$(compose) ${DOCKER_MODE} php sh -lc './vendor/bin/phpstan analyse -l ${PHP_STAN_LEVEL} -c phpstan.neon src tests'
		$(compose) ${DOCKER_MODE} php sh -lc './vendor/bin/psalm --show-info=false'
		$(compose) ${DOCKER_MODE} php sh -lc 'php bin/deptrac.phar analyze --formatter-graphviz=0'

.PHONY: sh
sh: ## gets inside a container (make sh php)
		docker-compose exec $(filter-out $@,$(MAKECMDGOALS)) sh -l

.PHONY: console
console: ## Execute Symfony console.
		$(compose) ${DOCKER_MODE} php sh -lc "./bin/console $(filter-out $@,$(MAKECMDGOALS))"

.PHONY: xconsole
xconsole: ## Execute Symfony console. with xDebug enabled
		docker-compose exec php sh -lc "export PHP_IDE_CONFIG='serverName=localingo' && php -dxdebug.remote_host=172.17.0.1 ./bin/console $(filter-out $@,$(MAKECMDGOALS))"

.PHONY: logs
logs: ## look for 's' service logs, make s=php logs
		docker-compose logs -f $(s)

.PHONY: help
help: ## Display this help message
	@cat $(MAKEFILE_LIST) | grep -e "^[a-zA-Z_\-]*: *.*## *" | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[36m%-30s\033[0m %s\n", $$1, $$2}'
