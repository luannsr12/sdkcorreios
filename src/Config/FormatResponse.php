<?php

namespace Sdkcorreios\Config;

class FormatResponse
{
    public array $formatTracking = [];

    public function __construct(array $formatTracking = [])
    {
        $this->formatTracking = [
            "code" => "",
            "status" => "",
            "data" =>  []
        ];
    }

}

