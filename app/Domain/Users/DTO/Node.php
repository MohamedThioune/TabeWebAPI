<?php

namespace App\Domain\Users\DTO;

class Node
{
    public function __construct(?String $content, ?String $contentVariables, ?String $level, ?String $model, ?String $title, ?String $body)
    {
        $this->content = $content;
        $this->contentVariables = $contentVariables;

        $this->level = $level; //Important, Urgent, Info
        $this->model = $model;
        $this->title = $title;
        $this->body = $body;   //transaction, card, profile, maintenance
    }
}
