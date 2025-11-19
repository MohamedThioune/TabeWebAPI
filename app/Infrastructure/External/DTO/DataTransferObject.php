<?php

namespace App\Infrastructure\External\DTO;

interface DataTransferObject
{
   public static function fromArray(array $data);
}
