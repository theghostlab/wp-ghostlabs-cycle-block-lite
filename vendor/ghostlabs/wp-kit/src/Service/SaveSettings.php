<?php

namespace GhostLabs\WPKit\Service;

use Exception;
use Generator;

class SaveSettings
{
    /**
     * @param array<string, class-string> $handlerMap
     */
    public function __construct(private readonly array $handlerMap)
    {
    }

    /** @var array<string, mixed> */
    private array $payload = [];

    /**
     * @param array<string, mixed> $payload
     * @return $this
     */
    public function setPayload(array $payload): static
    {
        $this->payload = $payload;
        return $this;
    }

    /**
     * @throws Exception
     */
    public function run(): Generator
    {
        foreach ($this->payload as $key => $data) {
            try {
                $class = $this->handlerMap[$key] ?? null;

                if ($class === null) {
                    throw new Exception("No save handler registered for key '$key'.");
                }

                $instance = new $class();
                yield [$key => $instance->save($data)];
            } catch (Exception $exception) {
                yield [
                    'error' => [
                        $key => $exception->getMessage(),
                    ],
                ];
            }
        }
    }
}
