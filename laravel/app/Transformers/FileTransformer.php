<?php

namespace App\Transformers;

use Illuminate\Support\Carbon;
use League\Fractal\TransformerAbstract;

class FileTransformer extends TransformerAbstract
{
    /**
     * List of resources to automatically include
     *
     * @var array
     */
    protected array $defaultIncludes = [
        //
    ];

    /**
     * List of resources possible to include
     *
     * @var array
     */
    protected array $availableIncludes = [
        //
    ];

    /**
     * A Fractal transformer.
     *
     * @return array
     */
    public function transform($file)
    {
        return [
            'name' => $file->name,
            'status_label' => $file->status_label,
            'created_at' => Carbon::parse($file->created_at)->format('Y-m-d H:i:s'),
            'created_time' => $file->created_at->diffForHumans(),
        ];
    }
}
