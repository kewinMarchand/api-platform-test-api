qa: lint format-check typecheck openapi-check test ## QA complète : lint, format, types, OpenAPI à jour, tests

lint: ## Lint du conteneur de services, du YAML, du mapping Doctrine et de composer.json
	$(COMPOSER) validate --strict
	$(CONSOLE) lint:container
	$(CONSOLE) lint:yaml config --parse-tags
	$(CONSOLE) doctrine:schema:validate

format: ## PHP-CS-Fixer (écriture)
	$(EXEC) vendor/bin/php-cs-fixer fix

format-check: ## PHP-CS-Fixer (vérification)
	$(EXEC) vendor/bin/php-cs-fixer fix --dry-run --diff

typecheck: ## PHPStan, niveau max
	$(CONSOLE) cache:warmup --env=dev --quiet
	$(EXEC) vendor/bin/phpstan analyse --memory-limit=512M

openapi-check: ## Vérifie que openapi.json correspond au schéma servi
	$(CONSOLE) api:openapi:export --output=var/openapi.json
	diff -q openapi.json var/openapi.json

test-db: ## Recrée la base de test et charge les fixtures
	$(CONSOLE) doctrine:database:drop --force --if-exists --env=test
	$(CONSOLE) doctrine:database:create --env=test
	$(CONSOLE) doctrine:migrations:migrate --no-interaction --env=test
	$(CONSOLE) doctrine:fixtures:load --no-interaction --env=test

test: test-db ## Tests unitaires et fonctionnels (PHPUnit + ApiTestCase)
	$(EXEC) php bin/phpunit

.PHONY: qa lint format format-check typecheck openapi-check test-db test
