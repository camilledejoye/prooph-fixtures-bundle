# Prooph Fixtures Bundle

Symfony's Bundle for [Prooph Fixtures](https://github.com/elythyr/prooph-fixtures).

Provides an easy integration of the library inside symfony by auto configuring the fixtures
and providing a console command to load all your fixtures.


## Installation

```shell
composer require --dev elythyr/prooph-fixtures-bundle
```

If you have an error related to the minimum stability, it's because I haven't created
a stable release yet... :innocent:
In this case, if you are feeling adventurous just require the latest dev commit:

```shell
composer require --dev elythyr/prooph-fixtures-bundle:*@dev
```

Note: With some shell, ZSH for instance, you might have an error with the last one.
Just surround the package name (and version) with quotes.


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

- [ ] Adds CI with Travis
- [ ] Adds tests coverage
- [ ] Make a first release
- [ ] Publish to packagist
- [ ] \(Wondering) Adds events before/after: cleaning, loading all fixtures, loagin each fixture
