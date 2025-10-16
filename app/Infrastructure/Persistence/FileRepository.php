<?php

namespace App\Infrastructure\Persistence;

use App\Models\File;
use App\Repositories\BaseRepository;

class FileRepository extends BaseRepository
{
    protected $fieldSearchable = [
        'type',
        'path',
        'meaning',
        'description'
    ];

    public function getFieldsSearchable(): array
    {
        return $this->fieldSearchable;
    }

    public function model(): string
    {
        return File::class;
    }
}
