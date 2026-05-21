<?php

namespace App\Listeners;

use App\Domain\Users\DTO\Node;
use App\Events\FileProcessed;
use App\Infrastructure\Persistence\FileRepository;
use App\Models\File;
use App\Notifications\ProfileUpdateNotification;

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
        // Get proprieties from running event
        $user = $event->user;
        $meaning = $event->meaning;

        // Notify the user that the file has been processed
        $node = new Node(
            content: null,
            contentVariables: null,
            level: 'Info',
            model: 'profile',
            title: 'Profil mis à jour',
            body: 'Votre fichier de profil a été traité avec succès et votre profil a été mis à jour en conséquence.'
        );
        $user->notify(new ProfileUpdateNotification(node: $node));

        // Send Notification
        /* instructions here */
        // End the event
    }
}
