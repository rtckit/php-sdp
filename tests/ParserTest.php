<?php

declare(strict_types = 1);

namespace RTCKit\SDP;

use PHPUnit\Framework\TestCase;
use stdClass;

class ParserTest extends TestCase
{
    private Parser $parser;

    protected function setUp(): void
    {
        $this->parser = new Parser;
    }

    public function testConstruct(): void
    {
        $this->assertInstanceOf(Parser::class, $this->parser);
    }

    public function testParseSimple(): void
    {
        $sdp = "v=0\r\no=- 0 0 IN IP4 127.0.0.1\r\ns=-\r\nc=IN IP4 127.0.0.1\r\nt=0 0\r\nm=audio 5004 RTP/AVP 0\r\na=rtpmap:0 PCMU/8000\r\n";

        $result = $this->parser->parse($sdp);
        $properties = get_object_vars($result);

        $this->assertInstanceOf(stdClass::class, $result);
        $this->assertArrayHasKey('media', $properties);
        $this->assertIsArray($result->media);
        $this->assertCount(1, $result->media);
        $this->assertEquals('audio', $result->media[0]->type);
    }

    public function testParseWithMultipleMedia(): void
    {
        $sdp = file_get_contents(__DIR__ . '/fixtures/multiple-media.sdp');

        $result = $this->parser->parse($sdp);

        $this->assertInstanceOf(stdClass::class, $result);
        $this->assertIsArray($result->media);
        $this->assertCount(2, $result->media);
        $this->assertEquals('audio', $result->media[0]->type);
        $this->assertEquals('video', $result->media[1]->type);
    }

    public function testParseWithInvalidSDP(): void
    {
        $invalidSdp = "Invalid SDP data";

        $result = $this->parser->parse($invalidSdp);
        $properties = get_object_vars($result);

        $this->assertInstanceOf(stdClass::class, $result);
        $this->assertCount(0, $properties);
    }
}
