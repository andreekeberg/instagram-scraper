# 📷 Instagram Scraper

[![Latest Stable Version](https://poser.pugx.org/andreekeberg/instagram-scraper/v/stable)](https://packagist.org/packages/andreekeberg/instagram-scraper) [![Total Downloads](https://poser.pugx.org/andreekeberg/instagram-scraper/downloads)](https://packagist.org/packages/andreekeberg/instagram-scraper) [![License](https://poser.pugx.org/andreekeberg/instagram-scraper/license)](https://packagist.org/packages/andreekeberg/instagram-scraper)

Instagram scraper, with support for users and tags.

Get a public users media, or search for a specific tag, without having to register an app.

Since this library uses the web version of Instagram to scrape content, it can break at any time
should the returned source code for these pages change. Use in production is therefore discouraged.

This library is provided "as is", and without warranty of any kind.

## Requirements

- PHP 5.6.0 or higher

## Installation

```
composer require andreekeberg/instagram-scraper
```

## Basic usage

### Getting a public users media

```php
$feed = Instagram::getUser('github');
```

### Getting public media with a specified tag

```php
$feed = Instagram::getTag('opensource');
```

### Limiting results

```php
$feed = Instagram::getUser('github', 4);
```

```php
$feed = Instagram::getTag('opensource', 6);
```

## Documentation

* [Instagram](docs/Instagram.md)

## Contributing

Read the [contribution guidelines](CONTRIBUTING.md).

## Changelog

Refer to the [changelog](CHANGELOG.md) for a full history of the project.

## License

Instagram Scraper is licensed under the [MIT license](LICENSE).
