qa: lint arch arch-selftest format-check typecheck openapi-check test ## QA complète : lint, architecture, format, types, OpenAPI à jour, tests

lint: ## Lint du conteneur de services, du YAML, du mapping Doctrine et de composer.json
	$(COMPOSER) validate --strict
	$(CONSOLE) lint:container
	$(CONSOLE) lint:yaml config --parse-tags
	$(CONSOLE) doctrine:schema:validate

arch: ## Règles de dépendance entre modules et couches (deptrac)
	$(EXEC) vendor/bin/deptrac analyse --no-progress

ARCH_SELFTEST_FILE = src/Catalog/Domain/ArchSelftestViolation.php

arch-selftest: ## Écrit une violation volontaire (Domain vers Infrastructure), exige l'échec de deptrac, puis la retire
	@trap 'rm -f $(ARCH_SELFTEST_FILE)' EXIT; \
	printf '%s\n' '<?php' '' 'declare(strict_types=1);' '' 'namespace App\Catalog\Domain;' '' \
		'use App\Catalog\Infrastructure\Doctrine\DoctrineProductRepository;' '' \
		'final class ArchSelftestViolation' '{' \
		'    public function __construct(public DoctrineProductRepository $$repository)' '    {' '    }' '}' \
		> $(ARCH_SELFTEST_FILE); \
	if output=$$($(EXEC) vendor/bin/deptrac analyse --no-progress 2>&1); then \
		echo "ÉCHEC : deptrac accepte une dépendance de Domain vers Infrastructure, les règles sont débranchées."; exit 1; \
	fi; \
	if ! printf "%s\n" "$$output" | grep -q 'ArchSelftestViolation must not depend on App\\Catalog\\Infrastructure'; then \
		echo "ÉCHEC : deptrac a échoué pour une autre raison que la violation volontaire :"; printf "%s\n" "$$output"; exit 1; \
	fi; \
	echo "OK : la violation volontaire est détectée, fichier retiré."

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

.PHONY: qa lint arch arch-selftest format format-check typecheck openapi-check test-db test
