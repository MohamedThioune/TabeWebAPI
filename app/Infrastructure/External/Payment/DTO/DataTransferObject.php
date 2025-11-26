<?php

namespace App\Infrastructure\External\Payment\DTO;

interface DataTransferObject
{
   public static function fromArray(array $data);
}
