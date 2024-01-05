<?php

declare(strict_types = 1);

namespace RTCKit\SDP;

use stdClass;

/**
 * SDP Parser class
 */
class Parser
{
    private stdClass $session;

    /** @var array<string, array<int<0, max>, array<string, mixed>>> */
    private array $grammar;

    private stdClass $cursor;

    public function __construct()
    {
        $this->grammar = (new Grammar)->compile();
    }

    /**
     * Parses a SDP string into a stdClass object
     *
     * @param string $sdp
     * @return stdClass
     */
    public function parse(string $sdp): stdClass
    {
        $this->cursor = $this->session = new stdClass;

        $raw = preg_split("/\r\n|\n|\r/", $sdp);

        if (!is_array($raw)) {
            // @codeCoverageIgnoreStart
            return $this->session;
            // @codeCoverageIgnoreEnd
        }

        $lines = array_filter($raw, function (string $line): int|false {
            return preg_match('/^([a-z])=(.*)/', $line);
        });

        foreach ($lines as $line) {
            $field = $line[0];
            $content = substr($line, 2);

            if ($field === 'm') {
                if (!isset($this->session->media)) {
                    $this->session->media = [];
                }

                $type = explode(' ', $content, 2)[0];
                $this->cursor = new stdClass;
                $this->session->media[] = $this->cursor;
                $this->cursor->rtp = [];
                $this->cursor->fmtp = [];
            }

            $this->parseLine($field, $content);
        }

        return $this->session;
    }

    /**
     * Parses a SDP line
     *
     * @param string $field
     * @param string $content
     */
    private function parseLine(string $field, string $content): void
    {
        if (!isset($this->grammar[$field])) {
            return;
        }

        foreach ($this->grammar[$field] as $obj) {
            if (preg_match('/' . $obj['reg'] . '/', $content, $matches)) {
                $this->parseReg($obj, $content, $matches);
                break;
            }
        }
    }

    /**
     * Consumes a SDP line broken into its components
     *
     * @param array<string, mixed> $obj
     * @param string $content
     * @param array<int, string|null> $matches
     */
    private function parseReg(array $obj, string $content, array $matches): void
    {
        $needsBlank = isset($obj['name']) && isset($obj['names']);

        if (isset($obj['push'])) {
            if (!isset($this->cursor->{$obj['push']})) {
                $this->cursor->{$obj['push']} = [];
            }
        } elseif ($needsBlank) {
            if (!isset($this->cursor->{$obj['name']})) {
                $this->cursor->{$obj['name']} = new stdClass;
            }
        }

        $keyLocation = isset($obj['push'])
            ? new stdClass
            : (
                $needsBlank
                ? $this->cursor->{$obj['name']}
                : $this->cursor
            );

        /** @var array<string>|null $names */
        $names = isset($obj['names']) && is_array($obj['names'])
            ? $obj['names']
            : null;

        /** @var string|null $rawName */
        $rawName = isset($obj['name']) && is_string($obj['name'])
            ? $obj['name']
            : null;

        $this->attachProperties($matches, $keyLocation, $names, $rawName);

        if (isset($obj['push'])) {
            $this->cursor->{$obj['push']}[] = $keyLocation;
        }
    }

    /**
     * Attaches parsed components properties to a stdClass object
     *
     * @param array<int, string|null> $matches
     * @param stdClass $keyLocation
     * @param null|array<string> $names
     * @param null|string $rawName
     */
    private function attachProperties(array $matches, stdClass $keyLocation, ?array $names, ?string $rawName): void
    {
        if ($rawName && !$names) {
            $keyLocation->{$rawName} = $this->recast($matches[1]);
        } else {
            $i = 0;
            $count = count($names ?? []);
            $max = count($matches) - 1;

            while ($i < $count) {
                /** @psalm-suppress PossiblyNullArgument */
                if (($i < $max) && isset($matches[$i + 1], $names[$i]) && strlen($matches[$i + 1])) {
                    $keyLocation->{$names[$i]} = $this->recast($matches[$i + 1]);
                }

                $i++;
            }
        }
    }

    /**
     * Recasts a component to its proper type
     *
     * @param string|null $v
     * @return string|int|float|null
     */
    private function recast(?string $v): string|int|float|null
    {
        if (is_numeric($v)) {
            if (strpos($v, '.') !== false) {
                return (float) $v;
            } else {
                return (int) $v;
            }
        }

        return $v;
    }
}
