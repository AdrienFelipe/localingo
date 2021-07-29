ENV?=dev
# Compatibility with non TTY devices (ENV=ci).
MODE?=exec $(if $(findstring ci,$(ENV)),-T,)
COMPOSE=docker-compose -f docker-compose.yml

CONTAINER?=php
FRAMEWORK='./active-framework'
EXEC=$(COMPOSE) $(MODE) $(CONTAINER)
# Execute container as current user
EXEC_U=$(COMPOSE) $(MODE) $(CONTAINER) su app -c

# Application commands.
YARN=yarn --cwd=$(FRAMEWORK)
COMPOSER=COMPOSER_MEMORY_LIMIT=-1 composer --working-dir=$(FRAMEWORK)


.PHONY: start
start: erase build up setup ## clean current environment, recreate dependencies and spin up again

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
# Start ssh server for 'app' user to log in.
		$(EXEC) /usr/sbin/sshd

.PHONY: setup
setup: ## spin up environment.
# Create 'app' user from current user id and group.
		$(EXEC) adduser -Du $$(id -u) -h /app app app
		$(EXEC) /bin/sh -c "yes app | passwd app"
# Make sure /app/files folder is writable by www-data
		$(EXEC) chown app:www-data /app/files
		$(EXEC) chmod g+wr /app/files
# Install project dependencies
		$(EXEC_U) "$(COMPOSER) install"
		$(EXEC_U) "$(YARN) install"
		$(EXEC_U) "$(YARN) encore dev"

.PHONY: composer
composer: ## Execute composer
		$(EXEC_U) "$(COMPOSER) $(filter-out $@,$(MAKECMDGOALS))"

.PHONY: yarn
yarn: ## Execute yarn
		$(EXEC_U) "$(YARN) $(filter-out $@,$(MAKECMDGOALS))"

.PHONY: db
db: ## recreate database
		$(COMPOSE) ${MODE} $(CONTAINER) sh -lc './bin/console d:d:d --if-exists --force'
		$(COMPOSE) ${MODE} $(CONTAINER) sh -lc './bin/console d:d:c'
		$(COMPOSE) ${MODE} $(CONTAINER) sh -lc './bin/console d:m:m -n'

.PHONY: test
test: ## execute project tests
		$(EXEC_U) "php $(FRAMEWORK)/bin/phpunit $(conf)"

.PHONY: cs
cs: ## Code Style (quality)
		$(EXEC_U) '$(FRAMEWORK)/vendor/bin/php-cs-fixer --no-interaction $(if $(findstring ci,$(ENV)),--dry-run,) --diff -v fix'

.PHONY: ca
ca: ## Code Analyzers (quality)
		$(EXEC_U) '$(FRAMEWORK)/vendor/bin/phpstan analyse -c phpstan.neon src tests'
		$(EXEC_U) '$(FRAMEWORK)/vendor/bin/psalm --show-info=false'
		$(EXEC_U) 'cd $(FRAMEWORK) && vendor/bin/deptrac analyze /app/depfile.yaml'

.PHONY: sh
sh: ## gets inside a container (make sh php)
		$(COMPOSE) ${MODE} $(filter-out $@,$(MAKECMDGOALS)) sh -l

.PHONY: console
console: ## Execute framework console.
		$(EXEC_U) "$(FRAMEWORK)/bin/console $(filter-out $@,$(MAKECMDGOALS))"

.PHONY: xconsole
xconsole: ## Execute framework console. with xDebug enabled
		docker-compose exec php sh -lc "export PHP_IDE_CONFIG='serverName=localingo' && php -dxdebug.remote_host=172.17.0.1 ./bin/console $(filter-out $@,$(MAKECMDGOALS))"

.PHONY: logs
logs: ## look for 's' service logs, make s=php logs
		docker-compose logs -f $(s)

.PHONY: help
help: ## Display this help message
	@cat $(MAKEFILE_LIST) | grep -e "^[a-zA-Z_\-]*: *.*## *" | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[36m%-30s\033[0m %s\n", $$1, $$2}'
