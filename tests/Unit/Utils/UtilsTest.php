<?php

declare(strict_types=1);

namespace App\Tests\Unit\Utils;

use App\Utils\Utils;
use PHPUnit\Framework\TestCase;

final class UtilsTest extends TestCase
{
    public function testGenerateColorReturnsSixHexDigits(): void
    {
        $color = Utils::generateColor();

        self::assertMatchesRegularExpression('/^[0-9a-f]{6}$/i', $color);
    }

    public function testUrlDecodeReturnsUtf8String(): void
    {
        self::assertSame('Pinba test', Utils::urlDecode('Pinba%20test'));
    }

    public function testParseRequestTagsReturnsStructuredTags(): void
    {
        $request = [
            'tags_cnt' => 2,
            'tags' => 'foo=bar,baz=qux',
        ];

        $parsed = Utils::parseRequestTags($request);

        self::assertIsArray($parsed);
        self::assertSame([
            'foo' => 'bar',
            'baz' => 'qux',
        ], $parsed['tags']);
    }

    public function testParseRequestTagsHonorsFilter(): void
    {
        $request = [
            'tags_cnt' => 2,
            'tags' => 'foo=bar,baz=qux',
        ];

        $parsed = Utils::parseRequestTags($request, ['foo' => 'bar']);

        self::assertIsArray($parsed);
        self::assertSame('bar', $parsed['tags']['foo']);
    }

    public function testParseRequestTagsReturnsFalseForMismatchedFilter(): void
    {
        $request = [
            'tags_cnt' => 2,
            'tags' => 'foo=bar,baz=qux',
        ];

        self::assertFalse(Utils::parseRequestTags($request, ['foo' => 'nope']));
    }
}
