<img src="assets/images/logo.png" alt="CritiPixel" width="200" />

# CritiPixel

## Pré-requis
* PHP >= 8.2
* Composer
* Extension PHP Xdebug
* Symfony (binaire)

## Installation

### 1. Cloner le projet

Clonez le dépôt du projet avec la commande suivante :

git clone <URL_DU_DEPOT>
cd <NOM_DU_DOSSIER>

### 2. Installer les dépendances

Installez les dépendances du projet en utilisant Composer avec la commande suivante :

```bash
composer install
```

### 3. Configurer la base de données 

#### Configurer l’environnement

Créez un fichier `.env.local` à la racine du projet avec la configuration suivante :

```bash
DATABASE_URL="postgresql://<utilisateur>:<mot_de_passe>@127.0.0.1:5432/criti-pixel?serverVersion=16&charset=utf8"
```

*Note : Cette configuration doit être adaptée à votre environnement local en fonction du type de base de données utilisé et des paramètres d'accès.*

#### Création de la base de données 

```bash
php bin/console doctrine:database:create
```

#### Appliquer les migrations 

```bash
php bin/console doctrine:migrations:migrate --no-interaction 
```

#### Générer les fixtures

```bash
php bin/console doctrine:fixtures:load --no-interaction 
```

### 3. Compiler les fichiers SASS

```bash
symfony console sass:build
```
*Note : le fichier `.symfony.local.yaml` est configuré pour surveiller les fichiers SASS et les compiler automatiquement quand vous lancez le serveur web de Symfony.*

## Tests

### 1. Configurer l’environnement de test

#### Installer la base de données de test

Créez un fichier `.env.test` à la racine du projet avec la configuration suivante :

```bash
KERNEL_CLASS='App\Kernel'
APP_SECRET='$ecretf0rt3st'
SYMFONY_DEPRECATIONS_HELPER=999999

DATABASE_URL="postgresql://<utilisateur>:<mot_de_passe>@127.0.0.1:5432/criti-pixel?serverVersion=16&charset=utf8"
```
*Note : Cette configuration doit être adaptée à votre environnement local en fonction du type de base de données utilisé et des paramètres d'accès.*

#### Création de la base de données de test 

```bash
php bin/console doctrine:database:create --env=test
```

#### Appliquer les migrations dans l'environnement de test

```bash
php bin/console doctrine:migrations:migrate --env=test --no-interaction 
```

#### Générer les fixtures dans l'environnement de test

```bash
php bin/console doctrine:fixtures:load --env=test --no-interaction
```

### 2. Lancer les tests

```bash
vendor/bin/phpunit 
```
*Note : Penser à charger les fixtures avant chaque exécution des tests.*


