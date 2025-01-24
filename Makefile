PHP ?= php
MYSQL ?= mysql
MYSQLDUMP ?= mysqldump
COMPOSER ?= composer
SYMFONY ?= SYMFONY

DATABASE ?= sfinternetofthings
LOCAL_PORT ?= 3336
SSH_PORT ?= 8122
LOCAL_MYSQL_USER ?= root

.PHONY: install
install:
	$(COMPOSER) install
	$(PHP) bin/console importmap:install

.PHONY: prepare-db
prepare-db:
	$(PHP) bin/console doctrine:database:create --if-not-exists
	$(PHP) bin/console doctrine:migrations:migrate --no-interaction

.PHONY: backup-remote-db
backup-remote-db:
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
	  	$(MYSQLDUMP) --column-statistics=0 -h 127.0.0.1 -v --port=$(LOCAL_PORT) -u root -p $(DATABASE) > "$(DATABASE).sql"; \
		kill $$PID; \
	fi


.PHONY: import-remote-db
import-remote-db:
	@if [ -e "$(DATABASE).sql" ]; then \
		$(MYSQL) -v -u $(LOCAL_MYSQL_USER) -p -h 127.0.0.1 -e "CREATE DATABASE IF NOT EXISTS $(DATABASE)"; \
		$(MYSQL) -v -u $(LOCAL_MYSQL_USER) -p -h 127.0.0.1 $(DATABASE) < $(DATABASE).sql; \
	else \
		echo "Please backup the remote database first."; \
		exit 1; \
	fi

.PHONY: migrate-remote-db
migrate-remote-db:
	@if [ -z "$(CURRENT_DATABASE)" ]; then \
		echo "Error: CURRENT_DATABASE is not set."; \
		exit 1; \
	else \
		envsubst < scripts/db_migrate.sql | $(MYSQL) -v -u $(LOCAL_MYSQL_USER) -p -h 127.0.0.1 $(CURRENT_DATABASE); \
	fi

.PHONY: run
run:
	$(SYMFONY) server:start

.PHONY: clean
clean:
	rm -rf var/cache/*
	rm -rf var/log/*
	rm -rf var/tailwind/*