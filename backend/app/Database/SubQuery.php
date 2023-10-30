<?php

namespace App\Database;

class SubQuery
{
    private string $raw;
    private array $param;
    private ?string $wraper;

    public function __construct(string $raw, array $param, ?string $wraper)
    {
        $this->raw = $raw;
        $this->param = $param;
        $this->wraper = $wraper;
    }

    public function reIndex(int $start): int
    {
        $newParam = [];
        $maxIndex = 0;
        foreach (array_reverse($this->param) as $key => $value) {
            $newIndex = intval(explode('_', $key)[1]) + $start;
            $maxIndex = max($maxIndex, $newIndex);
            $newKey = 'param_' . $newIndex;
            $newParam[$newKey] = $value;
            $this->raw = str_replace(':' . $key, ':' . $newKey, $this->raw);
        }
        $this->param = array_reverse($newParam);
        return $maxIndex + 1;
    }

    public function getRaw(): RawSQL
    {
        return new RawSQL('(' . $this->raw . ')');
    }

    public function getParam(): array
    {
        return $this->param;
    }

    public function getWraper(): ?string
    {
        return $this->wraper;
    }


}