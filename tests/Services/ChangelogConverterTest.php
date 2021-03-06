<?php

/*
 * This file is part of anahkiasen/versioner
 *
 * (c) madewithlove <heroes@madewithlove.be>
 *
 * For the full copyright and license information, please view the LICENSE
 */

namespace Versioner\Services;

use Versioner\TestCase;

class ChangelogConverterTest extends TestCase
{
    public function testCanConvertChangelogToMarkdown()
    {
        $changelog = [
            [
                'name' => '1.1.1',
                'date' => '2015-12-12',
                'changes' => [
                    'added' => [
                        'aa',
                    ],
                    'security' => [
                        'a',
                    ],
                ],
            ],
            [
                'name' => '0.2.1',
                'date' => '2015-01-01',
            ],
            [
                'name' => '0.2.0',
                'date' => '2015-01-01',
                'changes' => [
                    'added' => [
                        'Initial <code>release</code>',
                    ],
                ],
            ],
            [
                'name' => '0.1.0',
                'date' => '2015-01-01',
                'changes' => [
                    'added' => [
                        'Initial <code>release</code>',
                    ],
                ],
            ],
        ];

        $converter = new ChangelogConverter($changelog, '<strong>lol</strong>');
        $markdown = $converter->getMarkdown();

        $expected = <<<'MARKDOWN'
# CHANGELOG

**lol**

## [1.1.1] - 2015-12-12

### Added

- aa

### Security

- a

## [0.2.0] - 2015-01-01

### Added

- Initial `release`

## [0.1.0] - 2015-01-01

### Added

- Initial `release`

[1.1.1]: https://github.com/anahkiasen/versioner/compare/0.2.0...1.1.1
[0.2.0]: https://github.com/anahkiasen/versioner/compare/0.1.0...0.2.0
MARKDOWN;

        $this->assertEquals($expected, $markdown);
    }

    public function testDoesntLeaveSpaceForDescriptionIfNone()
    {
        $changelog = [
            [
                'name' => '0.1.0',
                'date' => '2015-01-01',
                'changes' => [
                    'added' => [
                        'Initial <code>release</code>',
                    ],
                ],
            ],
        ];

        $converter = new ChangelogConverter($changelog);
        $markdown = $converter->getMarkdown();

        $expected = <<<'MARKDOWN'
# CHANGELOG

## [0.1.0] - 2015-01-01

### Added

- Initial `release`
MARKDOWN;

        $this->assertEquals($expected, $markdown);
    }
}
