<?php

namespace App\Database;

class SubQuery
{
    private string $raw;
    private array $param;

    public function __construct(string $raw, array $param)
    {
        $this->raw = $raw;
        $this->param = $param;
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


}