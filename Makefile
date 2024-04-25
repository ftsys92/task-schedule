.PHONY: all
all: help

.PHONY: down
down: ## Shut down all the running container services
	@echo "+ $@"
	@docker compose down

.PHONY: up
up: ## Run the all container services
	@echo "+ $@"
	@docker compose up -d --wait --build -d

.PHONY: setup
setup: ## Install the project dependencies
	@echo "+ $@"
	@rm -f .env
	@cp .env.example .env
	@docker compose run --user $$(id -u):$$(id -g) worker composer install --no-scripts
	@docker compose run --user $$(id -u):$$(id -g) worker php artisan key:generate --force

.PHONY: migrate
migrate: ## Bootstrap the database
	@docker compose run --user $$(id -u):$$(id -g) worker php artisan migrate:fresh --seed

.PHONY: php-unit
php-unit: ## Run PHPUnit tests
	@docker compose run --user $(id -u):$(id -g) worker php vendor/bin/phpunit
