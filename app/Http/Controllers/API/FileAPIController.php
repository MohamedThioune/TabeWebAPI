<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\AppBaseController;
use App\Http\Requests\API\StoreFileAPIRequest;
use App\Jobs\UploadFileToS3;
use App\Models\File;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Infrastructure\Persistence\FileRepository;

class FileAPIController extends AppBaseController
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(File $file)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, File $file)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(File $file)
    {
        //
    }

    public function sample_upload(User $user, Request $request) : String
    {
        $inputs = $request->only('file', 'path', 'meaning', 'description');
        $meaning = $inputs['meaning'];
        $description = $inputs['description'];
        $fileRepository = new FileRepository();
        $path_images = ['avatar', 'banner'];

        $file = $request->file('file');
        $type = $file->getClientOriginalExtension();
        $originalNameWithoutEx = basename($file->getClientOriginalName() , '.'. $type);
        $originalName = Str::slug(Str::words($originalNameWithoutEx, 15));
        $filename = Str::uuid()->toString() . '-' . $originalName;
        $prefix = config('app.prefix_aws');
        $path = (in_array($meaning, $path_images)) ? $prefix . 'users/images/' : $prefix . 'users/documents/';

        $temp = $file->store('temp');

        UploadFileToS3::dispatch($temp, $type, $path, $filename, $originalName, $meaning, $description, $user->id, $fileRepository);

        return $filename;
    }

    /**
     * @OA\Post(
     *      path="/upload",
     *      summary="uploadFile",
     *      tags={"Document"},
     *      description="Upload file",
     *      @OA\RequestBody(
     *        @OA\MediaType(
     *          mediaType="multipart/form-data",
     *           @OA\Schema(
     *             @OA\Property(
     *                 property="file",
     *                 type="string",
     *                 format="binary"
     *             ),
     *             @OA\Property(
     *                 property="path",
     *                 type="string",
     *             ),
     *             @OA\Property(
     *                 property="meaning",
     *                 type="string",
     *             ),
     *             @OA\Property(
     *                 property="description",
     *                 type="string",
     *             ),
     *           ),
     *        ),
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="successful operation !",
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(
     *                  property="success",
     *                  type="boolean"
     *              ),
     *              @OA\Property(
     *                  property="message",
     *                  type="Success",
     *              )
     *          )
     *      )
     * )
     */
    public function upload(StoreFileAPIRequest $request): JsonResponse
    {
        $filename_id = $this->sample_upload($request->user(), $request);

        return $this->sendResponse(['id' => $filename_id], 'File received, being processed...');
    }

}
