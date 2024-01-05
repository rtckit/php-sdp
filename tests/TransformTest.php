<?php

declare(strict_types = 1);

namespace RTCKit\SDP;

use PHPUnit\Framework\TestCase;
use stdClass;

class TransformTest extends TestCase
{
    private Parser $parser;

    private Serializer $serializer;

    protected function setUp(): void
    {
        $this->parser = new Parser;
        $this->serializer = new Serializer;
    }

    public function testConstruct(): void
    {
        $this->assertInstanceOf(Parser::class, $this->parser);
        $this->assertInstanceOf(Serializer::class, $this->serializer);
    }

    public function testTransform(): void
    {
        $dir = __DIR__ . '/fixtures';
        $files = scandir($dir);

        foreach ($files as $file) {
            if (substr($file, -4) !== '.sdp') {
                continue;
            }

            $sdp = file_get_contents($dir . '/' . $file);

            $session = $this->parser->parse($sdp);
            $this->assertInstanceOf(stdClass::class, $session);

            $serialized = $this->serializer->serialize($session);
            $this->assertIsString($serialized);

            /* Skip deliberately invalid SDP and mediaClk examples */
            if (
                in_array($file, [
                    'invalid.sdp',
                    'mediaclk-avbtp.sdp',
                    'mediaclk-ptp-v2.sdp',
                    'mediaclk-ptp-v2-w-rate.sdp',
                    'st2110-20.sdp',
                ])
            ) {
                continue;
            }

            $sdpItems = array_unique(array_filter(preg_split('/\r\n|\r|\n/', $sdp)));
            $serializedItems = array_unique(array_filter(preg_split('/\r\n|\r|\n/', $serialized)));

            sort($sdpItems);
            sort($serializedItems);

            $this->assertEquals($sdpItems, $serializedItems, 'Failed to transform ' . $file);

            $session2 = $this->parser->parse($serialized);

            $this->assertEquals($session, $session2, 'Failed to transform ' . $file);
        }
    }
}
