# Versioner

[![Latest Version on Packagist][ico-version]][link-packagist]
[![Software License][ico-license]](LICENSE.md)
[![Build Status][ico-travis]][link-travis]
[![Coverage Status][ico-scrutinizer]][link-scrutinizer]
[![Quality Score][ico-code-quality]][link-code-quality]
[![Total Downloads][ico-downloads]][link-downloads]

A package to more easily version packages.

## Installation

``` bash
$ composer global require anahkiasen/versioner
```

## Usage

Use the `create` command to create a new version:

```bash
$ versioner create 1.2.3
```

Or use the `increment` command (default is patch):

```bash
$ versioner increment
$ versioner increment [--major|--minor|--patch]
```

![](http://i.imgur.com/uOLWRUG.gif)

## Change log

Please see [CHANGELOG](CHANGELOG.md) for more information what has changed recently.

## Testing

```bash
$ composer test
```

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) and [CONDUCT](CONDUCT.md) for details.

## Security

If you discover any security related issues, please email heroes@madewithlove.be instead of using the issue tracker.

## Credits

- [Anahkiasen][link-author]
- [All Contributors][link-contributors]

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

[ico-version]: https://img.shields.io/packagist/v/Anahkiasen/versioner.svg?style=flat-square
[ico-license]: https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square
[ico-travis]: https://img.shields.io/travis/Anahkiasen/versioner/master.svg?style=flat-square
[ico-scrutinizer]: https://img.shields.io/scrutinizer/coverage/g/Anahkiasen/versioner.svg?style=flat-square
[ico-code-quality]: https://img.shields.io/scrutinizer/g/Anahkiasen/versioner.svg?style=flat-square
[ico-downloads]: https://img.shields.io/packagist/dt/Anahkiasen/versioner.svg?style=flat-square

[link-packagist]: https://packagist.org/packages/Anahkiasen/versioner
[link-travis]: https://travis-ci.org/Anahkiasen/versioner
[link-scrutinizer]: https://scrutinizer-ci.com/g/Anahkiasen/versioner/code-structure
[link-code-quality]: https://scrutinizer-ci.com/g/Anahkiasen/versioner
[link-downloads]: https://packagist.org/packages/Anahkiasen/versioner
[link-author]: https://github.com/Anahkiasen
[link-contributors]: ../../contributors
