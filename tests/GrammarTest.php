<?php

declare(strict_types = 1);

namespace RTCKit\SDP;

use PHPUnit\Framework\TestCase;

class GrammarTest extends TestCase
{
    private Grammar $grammar;

    protected function setUp(): void
    {
        $this->grammar = new Grammar;
    }

    public function testConstruct(): void
    {
        $this->assertInstanceOf(Grammar::class, $this->grammar);
    }

    public function testCompile(): void
    {
        $result = $this->grammar->compile();

        $this->assertIsArray($result);
    }

    public function testCachedCompiledGrammar(): void
    {
        $result = $this->grammar->compile();
        $cached = $this->grammar->compile();

        $this->assertEquals($result, $cached);
    }
}
