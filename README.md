# api-platform-test-api

API de test commune aux cinq boilerplates front (Next, Nuxt, SvelteKit, Symfony Twig, HTML vanilla). Elle sert des tâches, des articles, un formulaire de contact et un catalogue à facettes, avec des données réalistes, en JSON simple ou en JSON-LD, et sait simuler une erreur serveur ou une liste vide pour tester les trois états des fronts.

| Ressource        | Opération                    | Ce qu'elle renvoie                                                 |
| ---------------- | ---------------------------- | ------------------------------------------------------------------ |
| `Task`           | `GET /api/tasks`             | Les 3 tâches de démonstration (`id`, `title`, `done`)              |
| `Article`        | `GET /api/articles`          | 3 articles (`id`, `slug`, `title`, `excerpt`, `publishedAt`), du plus récent au plus ancien |
| `ContactMessage` | `POST /api/contact_messages` | 201 et le message enregistré, ou 422 avec les violations en français |
| `Category`       | `GET /api/categories`        | L'arbre des 14 catégories du catalogue (`slug`, `name`, `parent`, `children`) |
| `ProductList`    | `GET /api/products`          | Une page des 24 produits, filtrée et triée, avec le total et les compteurs de facettes |

Tout est en lecture seule sauf le contact (un `POST` répond 405). Les collections ne sont pas paginées, sauf les produits qui ont leur propre pagination.

## Stack

| Brique                              | Rôle                                                  |
| ----------------------------------- | ----------------------------------------------------- |
| PHP 8.4, FrankenPHP 1 (Caddy)       | Serveur HTTP et PHP dans un seul conteneur            |
| Symfony 7.4 (LTS)                   | Framework                                             |
| API Platform 4.4                    | Ressources, formats, OpenAPI, erreurs `problem+json`  |
| Doctrine ORM 3, Migrations, Fixtures | Persistance, schéma versionné, données de démonstration |
| PostgreSQL 17                       | Base de données, non exposée sur la machine           |
| nelmio/cors-bundle                  | CORS pour les origines locales des fronts             |
| PHPUnit 13 + ApiTestCase            | Tests fonctionnels de chaque endpoint                 |
| PHPStan 2 (niveau max), PHP-CS-Fixer | Analyse statique et format                            |

## Prérequis

- Docker avec Compose v2. Rien d'autre : PHP et Composer tournent dans le conteneur.
- Le port 8090 libre sur `127.0.0.1`.

## Démarrage

```sh
git clone https://github.com/kewinMarchand/api-platform-test-api.git
cd api-platform-test-api
make install    # image, conteneurs, dépendances Composer
make db-reset   # base, migrations, fixtures
```

L'API répond sur http://localhost:8090/api. La documentation interactive (Swagger UI) est sur http://localhost:8090/api/docs dans un navigateur.

Le projet Compose s'appelle `api-platform-test-api` : ses conteneurs, son réseau et son volume (`api-platform-test-api_database_data`) ne touchent à aucun autre projet. Le conteneur PHP tourne avec l'UID de l'utilisateur courant, les fichiers créés dans le dépôt lui appartiennent.

## Commandes

`make help` liste toutes les commandes. Les principales :

| Commande        | Effet                                                                     |
| --------------- | ------------------------------------------------------------------------- |
| `make install`  | Construit l'image, lance les conteneurs, installe les dépendances         |
| `make up` / `make down` | Lance ou arrête les conteneurs (la base est conservée)            |
| `make db-reset` | Recrée la base de dev, joue les migrations, charge les fixtures           |
| `make qa`       | Lint, architecture, format, PHPStan, contrôle de `openapi.json`, tests    |
| `make arch`     | deptrac : règles de dépendance entre modules et couches (`deptrac.yaml`)  |
| `make arch-selftest` | Écrit une violation volontaire, exige l'échec de deptrac, puis la retire |
| `make lint`     | `composer validate`, `lint:container`, `lint:yaml`, `doctrine:schema:validate` |
| `make format`   | PHP-CS-Fixer en écriture (`make format-check` pour vérifier)              |
| `make typecheck` | PHPStan, niveau max, extensions Symfony et Doctrine                      |
| `make test`     | Recrée la base de test, puis PHPUnit                                      |
| `make openapi`  | Exporte le schéma OpenAPI dans `openapi.json`                             |
| `make logs` / `make sh` | Journaux des conteneurs, shell dans le conteneur PHP              |
| `make docker-build-prod` | Image de production (`api-platform-test-api:prod`)               |

## Consommer l'API depuis un front

### Format

Le **JSON simple** (`application/json`) est le format par défaut : une requête sans en-tête `Accept` (ou avec `Accept: */*`) le reçoit. Une collection est un tableau, sans enveloppe Hydra :

```sh
curl http://localhost:8090/api/tasks
# [{"id":1,"title":"Brancher la vraie API","done":false}, ...]
```

Le JSON-LD reste disponible avec `Accept: application/ld+json` (collection dans `member`, `totalItems`).

`publishedAt` est une date au format `AAAA-MM-JJ`. Les chaînes accentuées sont échappées (`é`) en JSON simple : c'est du JSON valide, tout décodeur les restitue.

### Formulaire de contact

```sh
curl -X POST http://localhost:8090/api/contact_messages \
  -H 'Content-Type: application/json' \
  -d '{"name":"Camille Martin","email":"camille.martin@example.fr","message":"Bonjour, je souhaite un devis."}'
```

Règles et messages, identiques au schéma zod des boilerplates (`contactSchema.ts`) :

| Champ     | Règle                       | Message                                                           |
| --------- | --------------------------- | ----------------------------------------------------------------- |
| `name`    | 2 caractères minimum, espaces ignorés | Indiquez votre nom (2 caractères minimum).              |
| `email`   | adresse valide              | Indiquez une adresse e-mail valide, par exemple nom@domaine.fr.   |
| `message` | 10 caractères minimum, espaces ignorés | Votre message doit contenir au moins 10 caractères.    |

Un payload invalide renvoie **422** en `application/problem+json`. Le tableau `violations` donne un message par champ, à afficher sous le champ correspondant (`propertyPath`) :

```json
{
  "status": 422,
  "violations": [
    { "propertyPath": "email", "message": "Indiquez une adresse e-mail valide, par exemple nom@domaine.fr.", "code": "bd79c0ab-ddba-46cc-a703-a7a4b08de310" }
  ],
  "detail": "email: Indiquez une adresse e-mail valide, par exemple nom@domaine.fr.",
  "title": "An error occurred"
}
```

`title` reste en anglais (valeur d'API Platform) : il ne doit pas être affiché, seuls les `message` le sont.

### Catalogue

Jardinerie tropicale fictive : 3 catégories racines, 14 catégories sur 3 niveaux, 24 produits dans les feuilles. `GET /api/categories` renvoie les racines, chacune avec ses `children` imbriqués (`parent` vaut `null` à la racine).

`GET /api/products` renvoie un **objet**, pas une collection :

```json
{
  "items": [{ "id": 1, "slug": "monstera-deliciosa", "name": "Monstera deliciosa", "categorySlug": "monstera", "price": 3490, "exposure": "mi-ombre", "size": "M", "inStock": true, "image": 1 }],
  "totalItems": 24, "page": 1, "itemsPerPage": 12, "totalPages": 2,
  "facets": {
    "exposure": [{ "value": "soleil", "count": 14 }, { "value": "mi-ombre", "count": 7 }, { "value": "ombre", "count": 3 }],
    "size": [{ "value": "S", "count": 6 }, { "value": "M", "count": 9 }, { "value": "L", "count": 9 }],
    "category": [{ "slug": "plantes-interieur", "name": "Plantes d'intérieur", "count": 12 }, ...]
  }
}
```

| Paramètre | Effet |
| --------- | ----- |
| `category` | Slug : produits de toute la branche. Slug inconnu : 404 (`Catégorie introuvable : <slug>`) |
| `exposure[]` | `soleil`, `mi-ombre`, `ombre`, clé répétée, valeurs combinées en OU |
| `size[]` | `S`, `M`, `L`, clé répétée, valeurs combinées en OU |
| `priceMin`, `priceMax` | Bornes incluses, en **centimes** |
| `inStock` | `true` ou `1` : produits en stock seulement |
| `order[price]`, `order[name]` | `asc` ou `desc`. Le prix l'emporte si les deux sont donnés. Sans tri : ordre de pertinence (ordre des fixtures). Tri par nom en collation française |
| `page`, `itemsPerPage` | Page à partir de 1, 12 produits par défaut, 48 au plus. Au-delà de la dernière page, `items` est vide |

Les facettes sont **disjonctives** : le compteur d'une valeur d'exposition applique tous les filtres actifs sauf l'exposition, idem pour la taille. `facets.category` liste les sous-catégories directes de la catégorie filtrée (les racines sans `category`, vide sur une feuille), comptées avec tous les autres filtres. Toutes les expositions et tailles sont toujours présentes, y compris à 0.

Une valeur invalide (exposition inconnue, prix non numérique, page hors bornes) est **ignorée**, comme dans l'URL des fronts : pas de 422. `image` est l'index du visuel partagé (`product-{1..12}`).

### Simuler un état : en-tête `X-Scenario`

En dev et en test uniquement. En prod, la classe n'est pas enregistrée et l'en-tête n'a aucun effet.

| Valeur  | Effet                                                                |
| ------- | -------------------------------------------------------------------- |
| `error` | 500 en `application/problem+json`, sur toutes les opérations         |
| `empty` | Collection vide (`[]`) sur les tâches, articles et catégories. Sur `/api/products` : `items` vide, totaux à 0, compteurs à 0 |

Toute autre valeur est ignorée.

```sh
curl -i http://localhost:8090/api/tasks -H 'X-Scenario: empty'
```

### CORS

Les origines autorisées sont une expression régulière dans `CORS_ALLOW_ORIGIN` (`.env`, surchargeable dans `.env.local` ou par variable d'environnement). Par défaut : `http://localhost` sur les ports 3000, 3010, 3100, 3110, 3120, 3130, 3150, 3200, 3210, 3220, 3230, 3250, 5173 et 8095. En-têtes autorisés : `Content-Type`, `Accept`, `X-Scenario`.

### Générer les types TypeScript

Le schéma OpenAPI est servi à **`http://localhost:8090/api/docs.jsonopenapi`** (OpenAPI 3.2, `application/vnd.openapi+json`). Il est aussi versionné dans **`openapi.json`** à la racine, pour générer les types sans lancer l'API :

```sh
npx openapi-typescript ../api-platform-test-api/openapi.json -o src/core/api/schema.d.ts
# ou, API lancée :
npx openapi-typescript http://localhost:8090/api/docs.jsonopenapi -o src/core/api/schema.d.ts
```

Les schémas utiles côté front sont `Task`, `Article`, `ContactMessage`, `ConstraintViolation`, `Category`, `ProductList`, `ProductItem`, `ProductFacets`, `FacetValue` et `CategoryFacetValue` (les variantes `.jsonld` concernent le JSON-LD). `make qa` échoue si `openapi.json` n'est plus à jour : relancer `make openapi` après toute modification d'une ressource.

### Front dans un conteneur Docker

L'API n'écoute que sur `127.0.0.1:8090`. Un front servi par Docker n'y accède pas par `localhost` : le brancher sur le réseau `api-platform-test-api_default` et viser `http://php:8080`, ou changer la publication du port dans `compose.yaml`.

## Architecture

Hexagonale légère. L'API est un seul contexte borné, découpé en un module par sous-domaine :

```
src/
  Task/                     tâches
    Domain/                 modèle (Task) et port (TaskRepository), sans framework
    Infrastructure/
      ApiPlatform/          ressource exposée (TaskResource) et son provider
      Doctrine/             adaptateur du port, mapping XML, fixtures
  Blog/                     articles, même découpage
  Contact/                  messages de contact, ressource + processor
  Catalog/                  catégories et produits
    Domain/                 Price, CategoryTree (value objects), ProductSearch : filtre,
                            facettes disjonctives, tri, pagination (PHP pur)
    Application/            SearchProducts : charge par les ports, délègue à ProductSearch
  Shared/Infrastructure/ApiPlatform/Scenario/   en-tête X-Scenario
tests/
  Unit/                     value objects et cas d'utilisation du catalogue, sans framework
  Functional/               un fichier par ressource, plus X-Scenario et CORS
  Integration/              enregistrement de X-Scenario selon l'environnement
```

Les règles sont vérifiées par deptrac (`make arch`) : `Domain` ne dépend que de lui-même, `Application` du `Domain` de son module, `Infrastructure` de son module, de `Shared` et des frameworks, et aucun module n'en importe un autre. Le domaine ne dépend ni de Doctrine ni d'API Platform : le mapping Doctrine est en XML dans l'infrastructure, et les ressources API Platform sont des classes à part, construites depuis le modèle (`fromModel`). La validation porte sur la ressource, frontière de l'API.

### Ajouter une ressource en lecture

1. Créer `src/<Module>/Domain/<Modele>.php` et l'interface du dépôt.
2. Ajouter le mapping `src/<Module>/Infrastructure/Doctrine/Mapping/<Modele>.orm.xml`, le dépôt Doctrine et la fixture.
3. Déclarer le mapping dans `config/packages/doctrine.yaml` et le dossier de ressources dans `config/packages/api_platform.yaml`.
4. Créer la ressource et son provider dans `Infrastructure/ApiPlatform/`.
5. `bin/console doctrine:migrations:diff`, `make db-reset`, `make openapi`, puis un test dans `tests/Functional/`.

## Production

`make docker-build-prod` construit une image multi-étapes : dépendances sans `require-dev`, `.env` compilé (`composer dump-env prod`), cache préchauffé, utilisateur `www-data`, port 8080. Elle attend `APP_SECRET` et `DATABASE_URL` en variables d'environnement. Les migrations se lancent à part (`bin/console doctrine:migrations:migrate`).

## Intégration continue

`.github/workflows/ci.yml` lance un PostgreSQL 17 en service, installe PHP 8.4 sur le runner, puis `make db-reset` et `make qa` avec `EXEC=` : le Makefile exécute alors les commandes directement, sans Docker.

## Licence

MIT
