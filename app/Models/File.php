<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @OA\Schema(
 *      schema="File",
 *      required={"id","type","path","meaning"},
 *      @OA\Property(
 *          property="id",
 *          description="",
 *          readOnly=false,
 *          nullable=false,
 *          type="string",
 *      ),
 *      @OA\Property(
 *          property="type",
 *          description="",
 *          readOnly=false,
 *          nullable=false,
 *          type="string",
 *      ),
 *      @OA\Property(
 *          property="path",
 *          description="",
 *          readOnly=false,
 *          nullable=false,
 *          type="string",
 *      ),
 *      @OA\Property(
 *          property="meaning",
 *          description="",
 *          readOnly=false,
 *          nullable=true,
 *          type="string",
 *      ),
 *      @OA\Property(
 *          property="description",
 *          description="",
 *          readOnly=false,
 *          nullable=true,
 *          type="string",
 *      ),
 *      @OA\Property(
 *          property="created_at",
 *          description="",
 *          readOnly=true,
 *          nullable=true,
 *          type="string",
 *          format="date-time"
 *      ),
 *      @OA\Property(
 *          property="updated_at",
 *          description="",
 *          readOnly=true,
 *          nullable=true,
 *          type="string",
 *          format="date-time"
 *      )
 * )
 */
class File extends Model
{
    use SoftDeletes, HasUuids;
    public $table = 'files';

    public $fillable = [
        'id', // Unique identifier for the file
        'type', // Extension of the file
        'path', // Directory path of the file
        'meaning', // Meaning of the file (Avatar, CIN, NINEA etc.)
        'description', // Description of the file
        'user_id' //Attached user
    ];

    protected $casts = [
        'id' => 'string',
        'type' => 'string',
        'path' => 'string',
        'meaning' => 'string',
        'description' => 'string'
    ];

    public static array $rules = [
        'type' => 'required',
        'path' => 'required',
        'meaning' => 'required|string|in:avatar,cin,ninea,others',
        'user_id' => 'required|exists:users,id'
    ];

    public static function rulesUpload(): array
    {
        return [
            'file' => [
                'required',
                'file',
                'max:2000', // 2 MB max
                function ($attribute, $value, $fail) {
                    $forbidden = ['exe', 'php', 'sh'];
                    $extension = strtolower($value->getClientOriginalExtension());

                    if (in_array($extension, $forbidden))
                        $fail("The $attribute must not be of type: " . implode(', ', $forbidden));

                },
            ],
            'meaning' => 'required|string|in:avatar,banner,cin,passport,ninea,other',
            'description' => 'required|string'
             // 'user_id' => 'required|integer|exists:users,id',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
