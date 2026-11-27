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
        $listeners = [function (array $params) use (&$seen) {
            $seen[] = $params;

            return '<p>first</p>';
        }];
        $html = FieldExtensions::render([
            'id' => 9, 'resource_id' => '42', 'resource_type' => 'product', 'lang' => 'uk', 'domain_key' => 'shop',
            'meta_title' => 'Купити квартиру', 'meta_description' => null, 'priority' => '0.5', '__env' => new \stdClass(),
        ], fn () => $listeners);

        $this->assertSame('<p>first</p>', $html);
        $this->assertSame([[
            'resource_id' => 42,
            'resource_type' => 'product',
            'lang' => 'uk',
            'domain_key' => 'shop',
            'fields' => ['meta_title' => 'Купити квартиру', 'meta_description' => '', 'meta_keywords' => '', 'canonical_url' => '', 'robots' => ''],
        ]], $seen);
    }

    /**
     * A domain with no row of its own shows the default domain's values; the hook still
     * names the domain being edited, not the row's.
     */
    public function testANonDefaultDomainWithoutItsOwnRowIsReportedAsItself(): void
    {
        $defaultRow = ['id' => 7, 'resource_id' => 42, 'resource_type' => 'document', 'domain_key' => 'default', 'lang' => 'base', 'meta_title' => 'Shared title'];
        $vars = FieldExtensions::identify($defaultRow, 42, 'document', 'shop');
        $context = FieldExtensions::context($vars);

        $this->assertSame('shop', $context['domain_key']);
        $this->assertSame(42, $context['resource_id']);
        $this->assertSame('Shared title', $context['fields']['meta_title']);
        $this->assertSame(7, $vars['id']);
    }

    public function testAResourceWithoutAnyRowStillNamesItself(): void
    {
        $context = FieldExtensions::context(FieldExtensions::identify(['lang' => 'en'], 5, 'product', 'shop'));

        $this->assertSame(['resource_id' => 5, 'resource_type' => 'product', 'lang' => 'en', 'domain_key' => 'shop'], array_slice($context, 0, 4, true));
    }

    public function testDefaultsForMissingValues(): void
    {
        $context = FieldExtensions::context(['resource_id' => 7, 'lang' => '', 'resource_type' => ' ']);

        $this->assertSame(['resource_id' => 7, 'resource_type' => 'document', 'lang' => 'base', 'domain_key' => 'default'], array_slice($context, 0, 4, true));
    }

    public function testNoResourceCallsNoListener(): void
    {
        $called = false;
        $html = FieldExtensions::render(['meta_title' => 'x'], function () use (&$called) {
            $called = true;

            return [fn () => '<p>never</p>'];
        });

        $this->assertSame('', $html);
        $this->assertFalse($called);
    }

    public function testOnlyNonEmptyStringsAreShownInOrder(): void
    {
        $this->assertSame("<a>1</a>\n<b>2</b>", FieldExtensions::join(['<a>1</a>', null, '  ', 5, ['x'], false, '<b>2</b>']));
        $this->assertSame('', FieldExtensions::join(false));
        $this->assertSame('<i>one</i>', FieldExtensions::join('<i>one</i>'));
    }

    public function testAFailingListenerIsSkippedAndTheOnesAroundItStillShow(): void
    {
        $calls = [];
        $listeners = [
            function () use (&$calls) { $calls[] = 'before'; return '<p>before</p>'; },
            function () use (&$calls) { $calls[] = 'broken'; throw new RuntimeException('listener bug'); },
            function () use (&$calls) { $calls[] = 'after'; return '<p>after</p>'; },
        ];
        $html = FieldExtensions::render(['resource_id' => 1], fn () => $listeners);

        $this->assertSame("<p>before</p>\n<p>after</p>", $html);
        $this->assertSame(['before', 'broken', 'after'], $calls);
    }

    public function testListenersThatCannotBeListedGiveNoBlockButNoError(): void
    {
        $html = FieldExtensions::render(['resource_id' => 1], function () {
            throw new RuntimeException('no dispatcher');
        });

        $this->assertSame('', $html);
    }
}
