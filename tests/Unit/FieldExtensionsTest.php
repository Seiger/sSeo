<?php

namespace Seiger\sSeo\Tests\Unit;

use PHPUnit\Framework\TestCase;
use RuntimeException;
use Seiger\sSeo\Support\FieldExtensions;

class FieldExtensionsTest extends TestCase
{
    public function testListenersGetTheResourceAndTheCurrentValuesOnly(): void
    {
        $seen = [];
        $html = FieldExtensions::render([
            'id' => 9, 'resource_id' => '42', 'resource_type' => 'product', 'lang' => 'uk', 'domain_key' => 'shop',
            'meta_title' => 'Купити квартиру', 'meta_description' => null, 'priority' => '0.5', '__env' => new \stdClass(),
        ], function (string $event, array $params) use (&$seen) {
            $seen[] = [$event, $params];

            return ['<p>first</p>'];
        });

        $this->assertSame('<p>first</p>', $html);
        $this->assertSame([[FieldExtensions::EVENT, [
            'resource_id' => 42,
            'resource_type' => 'product',
            'lang' => 'uk',
            'domain_key' => 'shop',
            'fields' => ['meta_title' => 'Купити квартиру', 'meta_description' => '', 'meta_keywords' => '', 'canonical_url' => '', 'robots' => ''],
        ]]], $seen);
    }

    public function testDefaultsForAResourceWithoutASavedRow(): void
    {
        $context = FieldExtensions::context(['resource_id' => 7, 'lang' => '', 'resource_type' => ' ']);

        $this->assertSame(['resource_id' => 7, 'resource_type' => 'document', 'lang' => 'base', 'domain_key' => 'default'], array_slice($context, 0, 4, true));
    }

    public function testNoResourceFiresNothing(): void
    {
        $fired = false;
        $html = FieldExtensions::render(['meta_title' => 'x'], function () use (&$fired) {
            $fired = true;

            return ['<p>never</p>'];
        });

        $this->assertSame('', $html);
        $this->assertFalse($fired);
    }

    public function testOnlyNonEmptyStringsAreShownInOrder(): void
    {
        $this->assertSame("<a>1</a>\n<b>2</b>", FieldExtensions::join(['<a>1</a>', null, '  ', 5, ['x'], '<b>2</b>']));
        $this->assertSame('', FieldExtensions::join(false));
        $this->assertSame('<i>one</i>', FieldExtensions::join('<i>one</i>'));
    }

    public function testAFailingListenerNeverBreaksTheForm(): void
    {
        $html = FieldExtensions::render(['resource_id' => 1], function () {
            throw new RuntimeException('listener bug');
        });

        $this->assertSame('', $html);
    }
}
