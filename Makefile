##################
# Variables
##################

DOCKER_COMPOSE = docker compose --env-file ./docker/.env -f ./docker/docker-compose.yml
DOCKER_COMPOSE_80 = docker compose --env-file ./docker/.env.mysql80 -f ./docker/docker-compose.yml
DOCKER_COMPOSE_84 = docker compose --env-file ./docker/.env.mysql84 -f ./docker/docker-compose.yml
DOCKER_COMPOSE_PHP_FPM_EXEC = ${DOCKER_COMPOSE} exec -u www-data php-fpm
DOCKER_COMPOSE_TEST = docker compose --env-file ./docker/.env.test -f ./docker/docker-compose.yml -f ./docker/docker-compose.test.yml
DOCKER_COMPOSE_TEST_PHP = ${DOCKER_COMPOSE_TEST} exec -u www-data php-fpm
DOCKER_COMPOSE_TEST_NODE = ${DOCKER_COMPOSE_TEST} exec node

.PHONY: build build80 build84 start stop up up80 up84 down restart dc_ps dc_logs dc_down dc_restart \
	app_bash php test test-up test-deps test-migrate test-check test-down test-reset cache db_migrate migrate \
	db_diff diff db_drop users_file_to_db users_db_to_file phpstan deptrac cs_fix linter cs_fix_diff composer_validate

##################
# Docker compose
##################

build:
	${DOCKER_COMPOSE} build

build80:
	${DOCKER_COMPOSE_80} build

build84:
	${DOCKER_COMPOSE_84} build

start:
	${DOCKER_COMPOSE} start

stop:
	${DOCKER_COMPOSE} stop

up:
	${DOCKER_COMPOSE} up -d --remove-orphans

up80:
	${DOCKER_COMPOSE_80} up -d --remove-orphans

up84:
	${DOCKER_COMPOSE_84} up -d --remove-orphans

down:
	${DOCKER_COMPOSE} down

restart: stop start

dc_ps:
	${DOCKER_COMPOSE} ps

dc_logs:
	${DOCKER_COMPOSE} logs -f

dc_down:
	${DOCKER_COMPOSE} down -v --rmi=all --remove-orphans

dc_restart:
	make dc_stop dc_start


##################
# App
##################

app_bash:
	${DOCKER_COMPOSE} exec -u www-data php-fpm bash
php: app_bash

test: test-up test-deps test-migrate test-check

test-up:
	${DOCKER_COMPOSE_TEST} up -d --build --wait mysql-pinba php-fpm node
	${DOCKER_COMPOSE_TEST} exec -u root php-fpm sh -lc 'mkdir -p var/cache var/log var/phpstan var/sessions && chown -R www-data:www-data var'

test-deps:
	${DOCKER_COMPOSE_TEST_PHP} env COMPOSER_HOME=/tmp/composer composer install --no-interaction --prefer-dist --no-scripts
	${DOCKER_COMPOSE_TEST_NODE} sh -lc 'COREPACK_HOME=/tmp/corepack corepack pnpm install --frozen-lockfile --store-dir=/tmp/pnpm-store'

test-migrate:
	${DOCKER_COMPOSE_TEST_PHP} php bin/console doctrine:migrations:migrate --no-interaction --allow-no-migration

test-check:
	${DOCKER_COMPOSE_TEST_PHP} php bin/console lint:twig
	${DOCKER_COMPOSE_TEST_PHP} vendor/bin/php-cs-fixer fix --dry-run --diff --using-cache=no
	${DOCKER_COMPOSE_TEST_PHP} env APP_ENV=dev APP_DEBUG=1 php bin/console cache:warmup
	${DOCKER_COMPOSE_TEST_PHP} vendor/bin/phpstan analyse --no-progress --memory-limit=1G
	${DOCKER_COMPOSE_TEST_PHP} vendor/bin/phpunit --no-progress
	${DOCKER_COMPOSE_TEST_NODE} sh -lc 'COREPACK_HOME=/tmp/corepack corepack pnpm build'

test-down:
	${DOCKER_COMPOSE_TEST} down --remove-orphans

test-reset:
	${DOCKER_COMPOSE_TEST} down -v --remove-orphans

cache:
	docker-compose -f ./docker/docker-compose.yml exec -u www-data php-fpm bin/console cache:clear
	docker-compose -f ./docker/docker-compose.yml exec -u www-data php-fpm bin/console cache:clear --env=test

##################
# Database
##################

db_migrate:
	${DOCKER_COMPOSE} exec -u www-data php-fpm bin/console doctrine:migrations:migrate --no-interaction
migrate: db_migrate

db_diff:
	${DOCKER_COMPOSE} exec -u www-data php-fpm bin/console doctrine:migrations:diff --no-interaction
diff: db_diff

db_drop:
	docker-compose -f ./docker/docker-compose.yml exec -u www-data php-fpm bin/console doctrine:schema:drop --force

users_file_to_db:
	${DOCKER_COMPOSE} exec -u www-data php-fpm bin/console users:migrate-file-to-db --no-interaction

users_db_to_file:
	${DOCKER_COMPOSE} exec -u www-data php-fpm bin/console users:migrate-db-to-file --no-interaction


##################
# Static code analysis
##################

phpstan:
	${DOCKER_COMPOSE_PHP_FPM_EXEC} vendor/bin/phpstan analyse -c phpstan.neon; \
 	${DOCKER_COMPOSE_PHP_FPM_EXEC} vendor/bin/phpstan clear-result-cache

deptrac:
	${DOCKER_COMPOSE_PHP_FPM_EXEC} vendor/bin/deptrac analyze deptrac-layers.yaml
	${DOCKER_COMPOSE_PHP_FPM_EXEC} vendor/bin/deptrac analyze deptrac-modules.yaml

cs_fix:
	${DOCKER_COMPOSE_PHP_FPM_EXEC} vendor/bin/php-cs-fixer fix
linter: cs_fix

cs_fix_diff:
	${DOCKER_COMPOSE_PHP_FPM_EXEC} vendor/bin/php-cs-fixer fix --dry-run --diff

composer_validate:
	${DOCKER_COMPOSE_PHP_FPM_EXEC} composer validate
