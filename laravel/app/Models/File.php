<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class File extends Model
{
    const STATUS_PENDING = 0;
    const STATUS_PROCESSING = 1;
    const STATUS_COMPLETED = 2;

    protected $fillable = [
        'user_id',
        'original_name',
        'name',
        'hash',
        'path',
        'status'
    ];

    public function getStatusLabelAttribute()
    {
        $statuses = [
            self::STATUS_PENDING => 'Pending',
            self::STATUS_PROCESSING => 'Processing',
            self::STATUS_COMPLETED => 'Completed',
        ];

        return $statuses[$this->status];
    }
}
