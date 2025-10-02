<?php

namespace App\Domain\Users\DTO;

class Node
{
    public function __construct(String $body, ?String $contentVariables)
    {
        $this->body = $body;
        $this->contentVariables = $contentVariables;
    }
}
