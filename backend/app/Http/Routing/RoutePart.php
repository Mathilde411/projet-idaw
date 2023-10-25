<?php

namespace App\Http\Routing;

class RoutePart
{
    protected array $branches = [];
    protected array $enpoints = [];

    public function constructBranch(string $name) {
        if(!isset($this->branches[$name])) {
            $new = new RoutePart();
            $this->branches[$name] = $new;
            return $new;
        }

        return $this->branches[$name];
    }

    public  function addEndpoint(Endpoint $endpoint) {
        $this->enpoints[] = $endpoint;
    }

    public function getBranches(): array
    {
        return $this->branches;
    }

    public function getEnpoints(): array
    {
        return $this->enpoints;
    }


}