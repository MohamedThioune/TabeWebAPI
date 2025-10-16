<?php

namespace App\Jobs;

use App\Events\FileProcessed;
use App\Infrastructure\Persistence\FileRepository;
use App\Models\File;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class UploadFileToS3 implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(public string $temp, public string $type, public string $path, public string $filename, public string $originalName, public string $meaning, public string $description, public string $user_id, private FileRepository $fileRepository){}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            // Get content of the local file
            $contents = Storage::disk('local')->get($this->temp);

            // Put the temp local file in S3
            Storage::disk('s3')->put($this->path . $this->filename, $contents);

            // Delete the local file after uploading to S3
            Storage::disk('local')->delete($this->temp);

            DB::beginTransaction();
            //Delete former file if exists with the same meaning for the user
            $file = File::whereMeaning($this->meaning)->whereUserId($this->user_id)->first();
            if ($file):
                Storage::disk('s3')->delete($this->path . $file->id);
                $file->delete();
            endif;

            //Insert new record file on database
            $this->fileRepository->create([
                'id' => $this->filename,
                'type' => $this->type,
                'path' => $this->path,
                'meaning' => $this->meaning,
                'description' => $this->description ?: $this->originalName,
                'user_id' => $this->user_id,
            ]);

            //Start the event
            $user = User::find($this->user_id);
            event(new FileProcessed($this->meaning, $this->path, $user));
            DB::commit();
        }
        catch (\Exception $e){
            Log::error('Error uploading file to S3: ' . $e->getMessage());
            DB::rollBack();
        }

    }


}
