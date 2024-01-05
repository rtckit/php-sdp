<?php

declare(strict_types = 1);

namespace RTCKit\SDP;

use PHPUnit\Framework\TestCase;
use stdClass;

class SerializerTest extends TestCase
{
    private Serializer $serializer;

    protected function setUp(): void
    {
        $this->serializer = new Serializer;
    }

    public function testConstruct(): void
    {
        $this->assertInstanceOf(Serializer::class, $this->serializer);
    }

    public function testSerializeWithDefaultOrder(): void
    {
        $session = new stdClass;
        $session->version = 0;
        $session->origin = new stdClass;
        $session->origin->username = '-';
        $session->origin->sessionId = 42;
        $session->origin->sessionVersion = 2;
        $session->origin->netType = 'IN';
        $session->origin->ipVer = 4;
        $session->origin->address = '127.0.0.1';
        $session->name = '-';
        $session->timing = new stdClass;
        $session->timing->start = 0;
        $session->timing->stop = 0;
        $session->media = [];
        $session->media[0] = new stdClass;
        $session->media[0]->type = 'audio';
        $session->media[0]->port = 5004;
        $session->media[0]->protocol = 'RTP/AVP';
        $session->media[0]->payloads = '0';
        $session->media[0]->rtp = [];
        $session->media[0]->rtp[0] = new stdClass;
        $session->media[0]->rtp[0]->payload = 0;
        $session->media[0]->rtp[0]->codec = 'PCMU';
        $session->media[0]->rtp[0]->rate = 8000;

        $expected = "v=0\r\no=- 42 2 IN IP4 127.0.0.1\r\ns=-\r\nt=0 0\r\nm=audio 5004 RTP/AVP 0\r\na=rtpmap:0 PCMU/8000\r\n";

        $result = $this->serializer->serialize($session);

        $this->assertEquals($expected, $result);
    }

    public function testSerializeWithCustomOrder(): void
    {
        $session = new stdClass;
        $session->version = 0;
        $session->origin = new stdClass;
        $session->origin->username = '-';
        $session->origin->sessionId = 42;
        $session->origin->sessionVersion = 2;
        $session->origin->netType = 'IN';
        $session->origin->ipVer = 4;
        $session->origin->address = '127.0.0.1';
        $session->name = '-';
        $session->timing = new stdClass;
        $session->timing->start = 0;
        $session->timing->stop = 0;
        $session->media = [];
        $session->media[0] = new stdClass;
        $session->media[0]->type = 'audio';
        $session->media[0]->port = 5004;
        $session->media[0]->protocol = 'RTP/AVP';
        $session->media[0]->payloads = '0';
        $session->media[0]->rtp = [];
        $session->media[0]->rtp[0] = new stdClass;
        $session->media[0]->rtp[0]->payload = 0;
        $session->media[0]->rtp[0]->codec = 'PCMU';
        $session->media[0]->rtp[0]->rate = 8000;

        $outerOrder = ['v', 's', 'o', 't', 'm', 'a'];
        $innerOrder = ['a', 'm', 't', 'c', 'o', 's'];

        $expected = "v=0\r\ns=-\r\no=- 42 2 IN IP4 127.0.0.1\r\nt=0 0\r\nm=audio 5004 RTP/AVP 0\r\na=rtpmap:0 PCMU/8000\r\n";

        $result = $this->serializer->serialize($session, $outerOrder, $innerOrder);

        $this->assertEquals($expected, $result);
    }
}
