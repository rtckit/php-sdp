<?php

declare(strict_types = 1);

namespace RTCKit\SDP;

use stdClass;

/**
 * SDP Serializer class
 */
class Serializer
{
    public const DEFAULT_OUTER_ORDER = ['v', 'o', 's', 'i', 'u', 'e', 'p', 'c', 'b', 't', 'r', 'z', 'a'];

    public const DEFAULT_INNER_ORDER = ['i', 'c', 'b', 'a'];

    /** @var array<string, array<int<0, max>, array<string, mixed>>> */
    private array $grammar;

    public function __construct()
    {
        $this->grammar = (new Grammar)->compile();
    }

    /**
     * Serializes a stdClass object into a SDP string
     *
     * @param stdClass $session
     * @param array<string, string> $outerOrder
     * @param array<string, string> $innerOrder
     * @return string
     */
    public function serialize(stdClass $session, array $outerOrder = null, array $innerOrder = null): string
    {
        $outerOrder = $outerOrder ?? self::DEFAULT_OUTER_ORDER;
        $innerOrder = $innerOrder ?? self::DEFAULT_INNER_ORDER;

        $session->version ??= 0;
        $session->name ??= ' ';
        $session->media ??= [];

        foreach ($session->media as $mid => $media) {
            $session->media[$mid]->payloads ??= '';
        }

        $sdp = [];

        foreach ($outerOrder as $field) {
            foreach ($this->grammar[$field] as $obj) {
                if (isset($obj['name'])) {
                    if (isset($session->{$obj['name']})) {
                        $sdp[] = self::makeLine($field, $obj, $session);
                    }
                } elseif (isset($obj['push'])) {
                    if (isset($session->{$obj['push']})) {
                        foreach ($session->{$obj['push']} as $el) {
                            $sdp[] = self::makeLine($field, $obj, $el);
                        }
                    }
                }
            }
        }

        foreach ($session->media as $mLine) {
            $sdp[] = self::makeLine("m", $this->grammar["m"][0], $mLine);

            foreach ($innerOrder as $field) {
                foreach ($this->grammar[$field] as $obj) {
                    if (isset($obj['name'], $mLine->{$obj['name']})) {
                        $sdp[] = self::makeLine($field, $obj, $mLine);
                    } elseif (isset($obj['push'], $mLine->{$obj['push']})) {
                        foreach ($mLine->{$obj['push']} as $el) {
                            $sdp[] = self::makeLine($field, $obj, $el);
                        }
                    }
                }
            }
        }

        return implode("\r\n", array_merge($sdp, ['']));
    }

    /**
     * Builds a single line of SDP
     *
     * @param string $field
     * @param array<string, mixed> $obj
     * @param stdClass $location
     */
    private function makeLine(string $field, array $obj, stdClass $location): string
    {
        $string = $field . "=" . (
            is_callable($obj['format'])
                ? $obj['format'](
                    (isset($obj['push']) ? $location : $location->{$obj['name']})
                )
                : $obj['format']
        );

        $args = [];

        if (isset($obj['names']) && is_array($obj['names'])) {
            foreach ($obj['names'] as $i => $name) {
                if (isset($obj['name'])) {
                    $args[] = $location->{$obj['name']}->{$name} ?? '';
                } else {
                    if (isset($location->{$name})) {
                        $args[] = $location->{$name};
                    }
                }
            }
        } else {
            $args[] = $location->{$obj['name']};
        }

        return sprintf($string, ...$args);
    }
}
