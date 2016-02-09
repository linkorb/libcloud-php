<?php

namespace LibCloud\Compute;

abstract class Base implements ComputeInterface
{
    protected $accessToken;

    public function __construct($accessToken)
    {
        $this->accessToken = $accessToken;
    }

    abstract protected function toNode($response);
    abstract protected function toSize($response);
    abstract protected function toImage($response);
    abstract protected function toLocation($response);
}
