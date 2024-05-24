.PHONY: all
all: help

.PHONY: help
help:
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[36m%-30s\033[0m %s\n", $$1, $$2}'

.PHONY: setup
setup: ## Install the project dependencies
	@echo "+ $@"
	@rm -f .env
	@cp .env.example .env
	@docker compose run --user $$(id -u):$$(id -g) worker composer install --no-scripts
	@docker compose run --user $$(id -u):$$(id -g) worker php artisan key:generate --force

.PHONY: up
up: ## Run the all container services
	@echo "+ $@"
	@docker compose up -d --wait --build -d

.PHONY: down
down: ## Shut down all the running container services
	@echo "+ $@"
	@docker compose down

.PHONY: migrate
migrate: ## Bootstrap the database
	@docker compose run --user $$(id -u):$$(id -g) worker php artisan migrate:fresh --seed

.PHONY: php-unit
php-unit: ## Run PHPUnit tests
	@docker compose run --user $(id -u):$(id -g) worker php vendor/bin/phpunit

.PHONY: psalm
psalm: ## Run Psalm analysis
	@docker compose run worker vendor/bin/psalm.phar

.PHONY: psalm-baseline
psalm-baseline: ## Save errors you wanted to skip
	@docker compose run worker vendor/bin/psalm.phar --set-baseline=psalm-baseline.xml

.PHONY: tinker
tinker: ## Run Laravel tinker
	@docker compose run worker php artisan tinker
