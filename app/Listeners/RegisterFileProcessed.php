<?php

namespace App\Listeners;

use App\Events\FileProcessed;
use App\Infrastructure\Persistence\FileRepository;
use App\Models\File;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Storage;

class RegisterFileProcessed
{
    /**
     * Create the event listener.
     */
    public function __construct(private FileRepository $fileRepository)
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(FileProcessed $event): void
    {
        //Get proprieties from running event
        $user = $event->user;
        $meaning = $event->meaning;

        //Send Notification
        /* instructions here */
        //End the event
    }
}
