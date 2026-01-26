PHP ?= php
MYSQL ?= mysql
MYSQLDUMP ?= mysqldump
COMPOSER ?= composer
SYMFONY ?= SYMFONY

DATABASE ?= sfinternetofthings
LOCAL_PORT ?= 3336
SSH_PORT ?= 8122
LOCAL_MYSQL_USER ?= root
REMOTE_MYSQL_USER ?= root
PROJECT_NAME ?= EnviroSenseFeatAlex

.PHONY: help
help:
	@echo "Available targets:"
	@grep -E '^[a-zA-Z0-9_-]+:.*?## .*$$' $(MAKEFILE_LIST) | awk 'BEGIN {FS = ":.*?## "}; {printf "  %-15s %s\n", $$1, $$2}'

.PHONY: install
install: ## Install the dependencies
	$(COMPOSER) install
	$(PHP) bin/console importmap:install

.PHONY: prepare-db
prepare-db: ## Prepare the database and run migrations
	$(PHP) bin/console doctrine:database:create --if-not-exists
	$(PHP) bin/console doctrine:migrations:migrate --no-interaction

.PHONY: load-test-data
load-test-data: ## Load test data into the database
	$(PHP) bin/console doctrine:fixtures:load

.PHONY: backup-remote-db
backup-remote-db: ## Copy contents of remote database to local sql file
	@PID=$$(pgrep -f "N -p $(SSH_PORT) -L $(LOCAL_PORT):"); \
	if [ -n "$$PID" ]; then \
	  echo "There is already a listener on port $(LOCAL_PORT). Please stop it first."; \
	fi

	@echo "Establishing SSH tunnel..."
	@ssh -fN -p $(SSH_PORT) -L $(LOCAL_PORT):127.0.0.1:3306 $(USER)@$(HOST); \
	if [ $$? -eq 0 ]; then \
	  echo "SSH connection successful!"; \
	else \
		echo "SSH connection failed!"; \
		exit 1; \
	fi

	@PID=$$(pgrep -f "N -p $(SSH_PORT) -L $(LOCAL_PORT):"); \
	if [ -n "$$PID" ]; then \
	  	$(MYSQLDUMP) --column-statistics=0 -h 127.0.0.1 -v --port=$(LOCAL_PORT) -u $(REMOTE_MYSQL_USER) -p $(DATABASE) > "$(DATABASE).sql"; \
		kill $$PID; \
	fi

.PHONY: import-remote-db
import-remote-db: ## Import contents of sql file to local database
	@if [ -e "$(DATABASE).sql" ]; then \
		$(MYSQL) -v -u $(LOCAL_MYSQL_USER) -p -h 127.0.0.1 -e "CREATE DATABASE IF NOT EXISTS $(DATABASE)"; \
		$(MYSQL) -v -u $(LOCAL_MYSQL_USER) -p -h 127.0.0.1 $(DATABASE) < $(DATABASE).sql; \
	else \
		echo "Could not find file $(DATABASE).sql"; \
		exit 1; \
	fi

.PHONY: migrate-remote-db
migrate-remote-db: ## Copy contents of remote database to new database
	@if [ -z "$(CURRENT_DATABASE)" ]; then \
		echo "Error: CURRENT_DATABASE is not set."; \
		exit 1; \
	else \
		envsubst < scripts/db_migrate.sql | $(MYSQL) -v -u $(LOCAL_MYSQL_USER) -p -h 127.0.0.1 $(CURRENT_DATABASE); \
	fi

.PHONY: drop-dbs
drop-dbs: ## Drop all databases
	@if [ -n "$(CURRENT_DATABASE)" ]; then \
  		$(MYSQL) -v -u $(LOCAL_MYSQL_USER) -p -h 127.0.0.1 -e "DROP DATABASE IF EXISTS $(CURRENT_DATABASE)"; \
	fi

	@if [ -n "$(TEST_DATABASE)" ]; then \
  		$(MYSQL) -v -u $(LOCAL_MYSQL_USER) -p -h 127.0.0.1 -e "DROP DATABASE IF EXISTS $(TEST_DATABASE)"; \
	fi

	$(MYSQL) -v -u $(LOCAL_MYSQL_USER) -p -h 127.0.0.1 -e "DROP DATABASE IF EXISTS $(DATABASE)"; \

.PHONY: run
run: ## Start the dev server
	$(SYMFONY) server:start

.PHONY: clean
clean: ## Clean the cache and logs
	rm -rf var/cache/*
	rm -rf var/log/*
	rm -rf var/tailwind/*
	rm -rf public/assets

.PHONY: deploy
deploy: ## Deploy the project to the remote server
	@if [ -z "$(REMOTE_USER)" ] || [ -z "$(HOST)" ] || [ -z "$(SSH_PORT)" ]; then \
  		echo "Please provide REMOTE_USER and HOST as environment variables."; \
	else \
  		echo "Deploying $(PROJECT_NAME) to $(HOST)..."; \
  		$(PHP) bin/console asset-map:compile; \
  		ssh -p $(SSH_PORT) $(REMOTE_USER)@$(HOST) "if [ -d /var/www/$(PROJECT_NAME)/public/assets ]; then rm -rf /var/www/$(PROJECT_NAME)/public/assets; fi"; \
  		rsync -e "ssh -p $(SSH_PORT) " --exclude-from "exclude-list" -avzh . $(REMOTE_USER)@$(HOST):/var/www/$(PROJECT_NAME) --delete; \
  		ssh -p $(SSH_PORT) $(REMOTE_USER)@$(HOST) "cd /var/www/$(PROJECT_NAME) && composer install --no-ansi --no-dev --no-interaction --no-progress --no-scripts --optimize-autoloader"; \
  		ssh -p $(SSH_PORT) $(REMOTE_USER)@$(HOST) "rm -rf /var/www/$(PROJECT_NAME)/var/cache/prod"; \
  		rm -rf public/assets; \
  	fi