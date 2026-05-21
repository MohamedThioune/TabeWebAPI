<?php

namespace App\Domain\Users\DTO;

class Node
{
    public function __construct(?string $content, ?string $contentVariables, ?string $level, ?string $model, ?string $title, ?string $body)
    {
        $this->content = $content;
        $this->contentVariables = $contentVariables;

        $this->level = $level; // Important, Urgent, Info
        $this->model = $model;
        $this->title = $title;
        $this->body = $body;   // transaction, card, profile, maintenance
    }
}
