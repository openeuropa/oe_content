# OpenEuropa Content

This is a Drupal module that contains the EC corporate entity types.

## Requirements

This depends on the following software:

* [PHP 7.1](http://php.net/)

### Requirements for OpenEuropa Content

* [drupal/rdf_entity 1.x](https://www.drupal.org/project/rdf_entity)

## Installation

* Install the package and its dependencies:

```bash
$ composer require openeuropa/oe_content
```

## Usage

### OpenEuropa Content

If you want to use OpenEuropa Content, enable the module:

```bash
$ drush en oe_content
```

## Development setup

### Requirements

* [Virtuoso 7 (Triplestore database)](https://github.com/openlink/virtuoso-opensource)

### Initial setup

You can build the test site by running the following steps.

* Install Virtuoso. For basic instructions, see [setting up
Virtuoso](https://github.com/ec-europa/rdf_entity/blob/8.x-1.x/README.md).

* Install all the composer dependencies:

```
$ composer install
```

* Customize build settings by copying `runner.yml.dist` to `runner.yml` and
changing relevant values, like your database credentials.

* Setup test site by running:

```
$ ./vendor/bin/run drupal:site-setup
```

This will symlink the theme in the proper directory within the test site and
perform token substitution in test configuration files such as `behat.yml.dist`.

* Install test site by running:

```
$ ./vendor/bin/run drupal:site-install
```

Your test site will be available at `./build`.

### Using Docker Compose

Alternatively you can build a test site using Docker and Docker-compose with the provided configuration.

Requirements:

- [Docker](https://www.docker.com/get-docker)
- [Docker-compose](https://docs.docker.com/compose/)

Run:

```
$ docker-compose up -d
```

Then:

```
$ docker-compose exec web composer install
$ docker-compose exec web ./vendor/bin/run drupal:site-install
```

Your test site will be available at [http://localhost:8080/build](http://localhost:8080/build).

To run the grumphp test:

```
$ docker-compose exec web ./vendor/bin/grumphp run
```

To run the phpunit test:

```
$ docker-compose exec web ./vendor/bin/phpunit
```

To run the behat test:

```
$ docker-compose exec web ./vendor/bin/behat
```
