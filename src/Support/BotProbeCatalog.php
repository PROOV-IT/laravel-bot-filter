<?php

declare(strict_types=1);

namespace Proovit\BotFilter\Support;

use Illuminate\Support\Collection;

final class BotProbeCatalog
{
    /**
     * @param  array<int, BotProbeDefinition>  $definitions
     */
    public function __construct(private array $definitions = []) {}

    public static function fromConfig(array $definitions): self
    {
        return new self(
            array_map(static fn (array $definition) => BotProbeDefinition::fromArray($definition), $definitions)
        );
    }

    public function withDefaults(): self
    {
        return new self($this->definitions);
    }

    public function withDefinitions(array $definitions): self
    {
        $merged = array_merge($this->definitions, array_map(static fn (array|BotProbeDefinition $definition) => $definition instanceof BotProbeDefinition ? $definition : BotProbeDefinition::fromArray($definition), $definitions));

        return new self($merged);
    }

    public function withoutKeys(array|string $keys): self
    {
        $keys = (array) $keys;

        return new self(array_values(array_filter(
            $this->definitions,
            static fn (BotProbeDefinition $definition) => ! in_array($definition->key, $keys, true)
        )));
    }

    public function all(): Collection
    {
        return collect($this->definitions)->values();
    }

    public function match(string $normalizedPath): ?BotProbeDefinition
    {
        foreach ($this->definitions as $definition) {
            if ($definition->enabled && $definition->matches($normalizedPath)) {
                return $definition;
            }
        }

        return null;
    }

    public static function defaults(): self
    {
        return self::fromConfig((array) config('bot-filter.probes', []));
    }
}
