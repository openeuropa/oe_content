# OpenEuropa Content

This is a Drupal module that contains the European Commission corporate entity types.

The module uses the RDF SKOS module to provide SKOS modelling for the Publications Office taxonomy vocabularies. These are directly made available in the dependent RDF triple store.

All content types are organised into sub-modules. These content types can be set to be translatable by
overriding the language.content_setting configuration.

This module provides a sub-module (oe_content_entity) with a set of Corporate content entities to be used.
Check out the [README](/modules/oe_content_entity/README.md) of the module.

**Table of contents:**

- [Requirements](#requirements)
- [Installation](#installation)
- [Usage](#usage)
- [Development setup](#development-setup)
- [Contributing](#contributing)
- [Versioning](#versioning)

## Requirements

This depends on the following software:

* [PHP >=8.1](http://php.net/)
* Virtuoso (or equivalent) triple store which contains the RDF representations of the following Publications Office (OP) vocabularies: Corporate Bodies, Target Audiences, Organisation Types, Resource Types, Eurovoc

## Usage with Drupal 10.1

For Drupal 10.1, the drupal/core patch needs to be overridden to the new one specifically made for 10.1.
If you require this module in Drupal 10.1 you must set the patch in your composer.json.\
You can do this with the following command:
```bash
composer config --merge --json "extra.patches.drupal/core" '{"https://www.drupal.org/project/drupal/issues/2230909": "https://www.drupal.org/files/issues/2022-07-26/2230909-269.patch"}'
composer config --merge --json "extra.patches-ignore.openeuropa/oe_content" '{"drupal/core": {"Drupal 10.2.x only - see oe_content/README.md for 10.1.x - https://www.drupal.org/project/drupal/issues/2230909": "https://www.drupal.org/files/issues/2023-12-21/2230909-309.patch"}}'
```

## Installation

Install the package and its dependencies:

```bash
composer require openeuropa/oe_content
```

It is strongly recommended to use the provisioned Docker image for Virtuoso that contains already the OP vocabularies. To do this, add the image to your `docker.compose.yml` file:

```
  sparql:
    image: openeuropa/triple-store-dev
    environment:
    - SPARQL_UPDATE=true
    - DBA_PASSWORD=dba
    ports:
      - "8890:8890"
```

Otherwise, make sure you have the triple store instance running and have imported those vocabularies.

Next, if you are using the Task Runner to set up your site, add the `runner.yml` configuration for connecting to the triple store. Under the `drupal` key:

```
  sparql:
    host: "sparql"
    port: "8890"
```

Still in the `runner.yml`, add the instruction to create the Drupal settings for connecting to the triple store. Under the `drupal.settings.databases` key:

```
  sparql_default:
    default:
      prefix: ""
      host: ${drupal.sparql.host}
      port: ${drupal.sparql.port}
      namespace: 'Drupal\Driver\Database\sparql'
      driver: 'sparql'
```

Then you can proceed with the regular Task Runner commands for setting up the site.

Otherwise, ensure that in your site's `setting.php` file you have the connection information to your own triple store instance:

```
$databases["sparql_default"] = array(
  'default' => array(
    'prefix' => '',
    'host' => 'your-triple-store-host',
    'port' => '8890',
    'namespace' => 'Drupal\\Driver\\Database\\sparql',
    'driver' => 'sparql'
  )
);
```

## Usage

### OpenEuropa Content

If you want to use OpenEuropa Content, enable the module:

```bash
drush en oe_content
```

### OpenEuropa Corporate Site Info integration

This module provides an integration with the [OpenEuropa Corporate Site Information](https://github.com/openeuropa/oe_corporate_site_info) module.

When creating a new node if the fields "Content Owner" and "Authors" are avilable they will be automatically populated with the values configured
as "Default content owner" in the "Basic site settings" provided by the OpenEuropa Corporate Site Info module.

## Development setup

### Requirements

* [Virtuoso 7 (Triplestore database)](https://github.com/openlink/virtuoso-opensource)

### Initial setup

You can build the test site by running the following steps.

* Install Virtuoso. The easiest way to do this is by using the OpenEuropa [Triple store](https://github.com/openeuropa/triple-store-dev) development Docker container which also pre-imports the main Europa vocabularies.

* Install all the composer dependencies:

```bash
composer install
```

* Customize build settings by copying `runner.yml.dist` to `runner.yml` and
changing relevant values, like your database credentials.

This will also symlink the theme in the proper directory within the test site and
perform token substitution in test configuration files such as `behat.yml.dist`.

* Install test site by running:

```bash
./vendor/bin/run drupal:site-install
```

Your test site will be available at `./build`.

**Please note:** project files and directories are symlinked within the test site by using the
[OpenEuropa Task Runner's Drupal project symlink](https://github.com/openeuropa/task-runner-drupal-project-symlink) command.

If you add a new file or directory in the root of the project, you need to re-run `drupal:site-setup` in order to make
sure they are be correctly symlinked.

If you don't want to re-run a full site setup for that, you can simply run:

```
$ ./vendor/bin/run drupal:symlink-project
```

### Using Docker Compose

Alternatively, you can build a development site using [Docker](https://www.docker.com/get-docker) and
[Docker Compose](https://docs.docker.com/compose/) with the provided configuration.

Docker provides the necessary services and tools such as a web server and a database server to get the site running,
regardless of your local host configuration.

#### Requirements:

- [Docker](https://www.docker.com/get-docker)
- [Docker Compose](https://docs.docker.com/compose/)

#### Configuration

By default, Docker Compose reads two files, a `docker-compose.yml` and an optional `docker-compose.override.yml` file.
By convention, the `docker-compose.yml` contains your base configuration and it's provided by default.
The override file, as its name implies, can contain configuration overrides for existing services or entirely new
services.
If a service is defined in both files, Docker Compose merges the configurations.

Find more information on Docker Compose extension mechanism on [the official Docker Compose documentation](https://docs.docker.com/compose/extends/).

#### Usage

To start, run:

```bash
docker-compose up
```

It's advised to not daemonize `docker-compose` so you can turn it off (`CTRL+C`) quickly when you're done working.
However, if you'd like to daemonize it, you have to add the flag `-d`:

```bash
docker-compose up -d
```

Then:

```bash
docker-compose exec web composer install
docker-compose exec web ./vendor/bin/run drupal:site-install
```

Using default configuration, the development site files should be available in the `build` directory and the development site
should be available at: [http://127.0.0.1:8080/build](http://127.0.0.1:8080/build).

#### Running the tests

To run the grumphp checks:

```bash
docker-compose exec web ./vendor/bin/grumphp run
```

To run the phpunit tests:

```bash
docker-compose exec web ./vendor/bin/phpunit
```

To run the behat tests:

```bash
docker-compose exec web ./vendor/bin/behat
```

#### Step debugging

To enable step debugging from the command line, pass the `XDEBUG_SESSION` environment variable with any value to
the container:

```bash
docker-compose exec -e XDEBUG_SESSION=1 web <your command>
```

Please note that, starting from XDebug 3, a connection error message will be outputted in the console if the variable is
set but your client is not listening for debugging connections. The error message will cause false negatives for PHPUnit
tests.

To initiate step debugging from the browser, set the correct cookie using a browser extension or a bookmarklet
like the ones generated at https://www.jetbrains.com/phpstorm/marklets/.

### Working with content

The project ships with the following Task Runner commands to work with content in the RDF store, they require Docker Compose
services to be up and running.

Purge all data:

```bash
docker-compose exec sparql ./vendor/bin/robo purge
```

Or, if you can run commands on your host machine:

```bash
./vendor/bin/run sparql:purge
```

Import default data:

```bash
docker-compose exec sparql ./vendor/bin/robo import
```

Or, if you can run commands on your host machine:

```bash
./vendor/bin/run sparql:import
```

Reset all data, i.e. run purge and import:

```bash
docker-compose exec sparql ./vendor/bin/robo purge
docker-compose exec sparql ./vendor/bin/robo import
```

Or, if you can run commands on your host machine:

```bash
./vendor/bin/run sparql:reset
```

## Contributing

Please read [the full documentation](https://github.com/openeuropa/openeuropa) for details on our code of conduct, and the process for submitting pull requests to us.

## Versioning

We use [SemVer](http://semver.org/) for versioning. For the available versions, see the [tags on this repository](https://github.com/openeuropa/oe_content/tags).
