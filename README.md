# Console

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

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information.

## Running Tests

```bash
$ composer test
```

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for more details.

## License

This library is licensed under the MIT license. Please see [License file](LICENSE.md) for more information.
