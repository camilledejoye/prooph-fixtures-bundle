# Prooph Fixtures Bundle
[![Build Status](https://travis-ci.org/elythyr/prooph-fixtures-bundle.svg?branch=master)](https://travis-ci.org/elythyr/prooph-fixtures-bundle)
[![Coverage Status](https://coveralls.io/repos/github/elythyr/prooph-fixtures-bundle/badge.svg?branch=master)](https://coveralls.io/github/elythyr/prooph-fixtures-bundle?branch=master)

Symfony's Bundle for [Prooph Fixtures](https://github.com/elythyr/prooph-fixtures).

Provides an easy integration of the library inside symfony by auto configuring the fixtures
and providing a console command to load all your fixtures.


## Installation

```shell
composer require --dev elythyr/prooph-fixtures-bundle
```


## Configuration

There is nothing to configure!

Just make sure that your fixtures are defined as services and implements
`Prooph\Fixtures\Fixture\Fixture` so they will be autoconfigured by the Bundle.


If you do not use autoconfiguration, then you must add the tag to all of your fixtures:

```yaml
# config/services.yaml
services:
    # On a per class basis
    App\DataFixtures\MyAllNewFixtures:
        tags: ['prooph_fixtures.fixtures']

    # For an entire directory
    App\DataFixtures\:
        resource: '../src/DataFixtures'
        tags: ['prooph_fixtures.fixtures']
```


## Usage

Simply go to your terminal and type:

```shell
php bin/console event-store:fixtures:load
```


## Todo

- [x] Adds CI with Travis
- [x] Adds tests coverage
- [x] Make a first release
- [x] Publish to packagist
- [ ] \(Wondering) Adds events before/after: cleaning, loading all fixtures, loagin each fixture
