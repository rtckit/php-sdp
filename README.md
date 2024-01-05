# SDP Parser and Serializer for PHP

[RFC 2327](https://tools.ietf.org/html/rfc2327), [RFC 4566](https://tools.ietf.org/html/rfc4566) and [RFC 8866](https://tools.ietf.org/html/rfc8866) compliant SDP parsing/serialization library for PHP.

[![CI Status](https://github.com/rtckit/php-sdp/workflows/CI/badge.svg)](https://github.com/rtckit/php-sdp/actions/workflows/ci.yaml)
[![Psalm Type Coverage](https://shepherd.dev/github/rtckit/php-sdp/coverage.svg)](https://shepherd.dev/github/rtckit/php-sdp)
[![Latest Stable Version](https://poser.pugx.org/rtckit/sdp/v/stable.png)](https://packagist.org/packages/rtckit/sdp)
[![Installs on Packagist](https://img.shields.io/packagist/dt/rtckit/sdp?color=blue&label=Installs%20on%20Packagist)](https://packagist.org/packages/rtckit/sdp)
[![Test Coverage](https://api.codeclimate.com/v1/badges/aff5ee8e8ef3b51689c2/test_coverage)](https://codeclimate.com/github/rtckit/php-sdp/test_coverage)
[![Maintainability](https://api.codeclimate.com/v1/badges/aff5ee8e8ef3b51689c2/maintainability)](https://codeclimate.com/github/rtckit/php-sdp/maintainability)
[![License](https://img.shields.io/badge/license-MIT-blue)](LICENSE)

## Quickstart

#### Session Description Parsing

Once [installed](#installation), you can parse SDP session description right away as follows:

```php
<?php

use RTCKit\SDP\Parser;

require_once __DIR__ . '/vendor/autoload.php';

$parser = new Parser;
$sdp = <<<SDP
v=0
o=- 20518 0 IN IP4 192.168.0.1
s=VoIP Call
c=IN IP4 192.168.0.1
t=0 0
m=audio 5004 RTP/AVP 0 8 18
a=rtpmap:0 PCMU/8000
a=rtpmap:8 PCMA/8000
a=rtpmap:18 G729/8000
a=ptime:20
SDP;

$session = $parser->parse($sdp);
echo json_encode($session, JSON_PRETTY_PRINT);
```

The `$session` variable (a `stdClass` object) will now be populated with the parsed SDP session description. Here's the output of the above example:

```json
{
    "version": 0,
    "origin": {
        "username": "-",
        "sessionId": 20518,
        "sessionVersion": 0,
        "netType": "IN",
        "ipVer": 4,
        "address": "192.168.0.1"
    },
    "name": "VoIP Call",
    "connection": {
        "version": 4,
        "ip": "192.168.0.1"
    },
    "timing": {
        "start": 0,
        "stop": 0
    },
    "media": [
        {
            "rtp": [
                {
                    "payload": 0,
                    "codec": "PCMU",
                    "rate": 8000
                },
                {
                    "payload": 8,
                    "codec": "PCMA",
                    "rate": 8000
                },
                {
                    "payload": 18,
                    "codec": "G729",
                    "rate": 8000
                }
            ],
            "fmtp": [],
            "type": "audio",
            "port": 5004,
            "protocol": "RTP\/AVP",
            "payloads": "0 8 18",
            "ptime": 20
        }
    ]
}
```
#### Session Description Serialization

Serializing is the opposite action of parsing:

```php
<?php

use RTCKit\SDP\Serializer;

require_once __DIR__ . '/vendor/autoload.php';

$session = new stdClass;

$session->version = 0;
$session->origin = new stdClass;
$session->origin->username = '-';
$session->origin->sessionId = 20518;
$session->origin->sessionVersion = 0;
$session->origin->netType = 'IN';
$session->origin->ipVer = 4;
$session->origin->address = '192.168.0.1';
$session->name = 'VoIP Call';
$session->connection = new stdClass;
$session->connection->version = 4;
$session->connection->ip = '192.168.0.1';
$session->timing = new stdClass;
$session->timing->start = 0;
$session->timing->stop = 0;
$session->media = [
    new stdClass
];
$session->media[0]->rtp = [
    new stdClass,
    new stdClass,
    new stdClass
];
$session->media[0]->rtp[0]->payload = 0;
$session->media[0]->rtp[0]->codec = 'PCMU';
$session->media[0]->rtp[0]->rate = 8000;
$session->media[0]->rtp[1]->payload = 8;
$session->media[0]->rtp[1]->codec = 'PCMA';
$session->media[0]->rtp[1]->rate = 8000;
$session->media[0]->rtp[2]->payload = 18;
$session->media[0]->rtp[2]->codec = 'G729';
$session->media[0]->rtp[2]->rate = 8000;
$session->media[0]->fmtp = [];
$session->media[0]->type = 'audio';
$session->media[0]->port = 5004;
$session->media[0]->protocol = 'RTP/AVP';
$session->media[0]->payloads = '0 8 18';
$session->media[0]->ptime = 20;

$serializer = new Serializer;
$sdp = $serializer->serialize($session);
echo $sdp;
```

The `$sdp` variable will now contain the serialized SDP session description:

```
v=0
o=- 20518 0 IN IP4 192.168.0.1
s=VoIP Call
c=IN IP4 192.168.0.1
t=0 0
m=audio 5004 RTP/AVP 0 8 18
a=rtpmap:0 PCMU/8000
a=rtpmap:8 PCMA/8000
a=rtpmap:18 G729/8000
a=ptime:20
```

## Requirements

**RTCKit\SDP** is compatible with PHP 7.4+ and has no external library and extension dependencies.

## Installation

You can add the library as project dependency using [Composer](https://getcomposer.org/):

```sh
composer require rtckit/sdp
```

If you only need the library during development, for instance when used in your test suite, then you should add it as a development-only dependency:

```sh
composer require --dev rtckit/sdp
```

## Tests

To run the test suite, clone this repository and then install dependencies via Composer:

```sh
composer install
```

Then, go to the project root and run:

```bash
php -d memory_limit=-1 ./vendor/bin/phpunit -c ./etc/phpunit.xml.dist
```

### Static Analysis

In order to ensure high code quality, **RTCKit\SDP** uses [PHPStan](https://github.com/phpstan/phpstan) and [Psalm](https://github.com/vimeo/psalm):

```sh
php -d memory_limit=-1 ./vendor/bin/phpstan analyse -c ./etc/phpstan.neon -n -vvv --ansi --level=max src
php -d memory_limit=-1 ./vendor/bin/psalm --config=./etc/psalm.xml
```

## License

MIT, see [LICENSE file](LICENSE).

### Acknowledgments

* [sdp-transform](https://github.com/clux/sdp-transform) - SDP parser and serializer for JavaScript, main source of inspiration for this library
* [RFC 2327](https://tools.ietf.org/html/rfc2327) - SDP: Session Description Protocol (April 1998)
* [RFC 4566](https://tools.ietf.org/html/rfc4566) - SDP: Session Description Protocol (July 2006)
* [RFC 8866](https://tools.ietf.org/html/rfc8866) - SDP: Session Description Protocol (January 2021)

### Contributing

Bug reports (and small patches) can be submitted via the [issue tracker](https://github.com/rtckit/php-sdp/issues). Forking the repository and submitting a Pull Request is preferred for substantial patches.
