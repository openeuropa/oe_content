# OpenEuropa Content

This is a Drupal module that contains the EC corporate entity types.

The main entity type is the RDF Entity which has the following bundles:

* Event
* Announcement

All bundles of this entity type have predicate mappings for RDF shared storage.
In order to determine the provenance of an entity, a "provenance URI" can be set
at the site level, which gets automatically saved with the entity. Access rules also
depend on this URI.

When installing the module, the provenance URI is set based on the base URL of the site.
However, it can be overridden by adding the following line to the settings.php file (with the
your respective URI):

```
$config['oe_content.settings']['provenance_uri'] = 'http://example.com';
```

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

Copy docker-compose.yml.dist into docker-compose.yml.

You can make any alterations you need for your local Docker setup. However, the defaults should be enough to set the project up.

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
