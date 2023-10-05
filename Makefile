ENV?=dev
# Compatibility with non TTY devices (ENV=ci).
MODE?=exec $(if $(findstring ci,$(ENV)),-T,)
COMPOSE=docker-compose -f docker-compose.yml

CONTAINER?=php
FRAMEWORK='./active-framework'
# Execute container as root.
EXEC=$(COMPOSE) $(MODE) $(CONTAINER)
# Execute container as current user.
EXEC_U=$(COMPOSE) $(MODE) $(CONTAINER) su app -c

# Application commands.
YARN=yarn --cwd=$(FRAMEWORK)
COMPOSER=COMPOSER_MEMORY_LIMIT=-1 composer --working-dir=$(FRAMEWORK)


.PHONY: start
start: erase build up setup ## delete and start from scratch

.PHONY: stop
stop: ## stop environment
		$(COMPOSE) stop

.PHONY: erase
erase: stop ## delete containers
		$(COMPOSE) rm -v -f

.PHONY: build
build: ## build environment from the docker files
		DOCKER_BUILDKIT=1 $(COMPOSE) build

.PHONY: up
up: ## spin up environment
		$(COMPOSE) up -d
# Start ssh server for 'app' user to log in.
		$(EXEC) /usr/sbin/sshd

.PHONY: setup
setup: ## configure the containers and install dependencies
# Create 'app' user from current user id and group.
		$(EXEC) adduser -Du $$(id -u) -h /app app app
		$(EXEC) /bin/sh -c "yes app | passwd app"
# Make sure /app/files folder is writable by www-data
		$(EXEC) chown app:www-data /app/files
		$(EXEC) chmod g+wr /app/files
# Install project dependencies
		$(EXEC_U) "$(COMPOSER) update symfony/flex --no-plugins --no-scripts"
		$(EXEC_U) "$(COMPOSER) install"
		$(EXEC_U) "$(YARN) install"
		$(EXEC_U) "$(YARN) encore dev"

.PHONY: composer
composer: ## Execute composer
		$(EXEC_U) "$(COMPOSER) $(filter-out $@,$(MAKECMDGOALS))"

.PHONY: yarn
yarn: ## Execute yarn
		$(EXEC_U) "$(YARN) $(filter-out $@,$(MAKECMDGOALS))"

.PHONY: test
test: ## execute project tests
		$(EXEC_U) "php $(FRAMEWORK)/vendor/bin/phpunit"

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
		docker-compose exec php sh -lc "export PHP_IDE_CONFIG='serverName=localingo' && php -dxdebug.client_host=host.docker.internal ./bin/console $(filter-out $@,$(MAKECMDGOALS))"

.PHONY: logs
logs: ## look for 's' service logs, make s=php logs
		docker-compose logs -f $(s)

.PHONY: help
help: ## Display this help message
	@cat $(MAKEFILE_LIST) | grep -e "^[a-zA-Z_\-]*: *.*## *" | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[36m%-30s\033[0m %s\n", $$1, $$2}'
