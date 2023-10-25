<?php

namespace App\Jobs;

use App\Events\ProgressUpdateEvent;
use App\Models\Product;
use App\Models\File;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ProcessFile implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 300;
    protected $exists;
    protected $file;

    /**
     * Create a new job instance.
     */
    public function __construct($exists, $file)
    {
        $this->exists = $exists;
        $this->file = File::find($file);
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        DB::beginTransaction();
        try {
//            $step = 1;
//            $totalSteps = 0;
            $csvPath = $this->file->path;
            //get total steps for progress tracking
//            if (($handle = fopen($csvPath, 'r')) !== false) {
//                 // Initialize total steps
//
//                while (fgetcsv($handle) !== false) {
//                    $totalSteps++; // Increment total steps for each row
//                }
//
//                // Close the CSV file
//                fclose($handle);
//            }

            // Process the CSV file and save fields
            if (($handle = fopen($csvPath, 'r')) !== false) {
                $this->file->status = File::STATUS_PROCESSING;
                $this->file->save();
                // Assuming the first row is the header
                $header = fgetcsv($handle);

                // Loop through each row and save the data
                while (($data = fgetcsv($handle)) !== false) {
                    $filteredHeaders = [];
                    foreach ($header as $column) {
                        $column = $this->removeNonUTF8($column);
                        $filteredHeaders[] = $column;
                    }
                    $row = array_combine($filteredHeaders, $data); // Create an associative array using the header
                    $productData = [
                        'user_id' => $this->file->user_id,
                        'unique_key' => $this->removeNonUTF8($row['UNIQUE_KEY']),
                        'title' => $this->removeNonUTF8($row['PRODUCT_TITLE']),
                        'description' => $this->removeNonUTF8($row['PRODUCT_DESCRIPTION']),
                        'color_name' => $this->removeNonUTF8($row['COLOR_NAME']),
                        'size' => $this->removeNonUTF8($row['SIZE']),
                        'style#' => $this->removeNonUTF8($row['STYLE#']),
                        'piece_price' => $this->removeNonUTF8($row['PIECE_PRICE']),
                        'sanmar_mainframe_color' => $this->removeNonUTF8($row['SANMAR_MAINFRAME_COLOR'])
                    ];
                    if ($product = Product::all()->where('user_id', $this->file->user_id)->where('unique_key', $row['UNIQUE_KEY'])->first()) {
                        $product->update($productData);
                    } else {
                        Product::create($productData);
                    }
//                    $progressData = [
//                        'current_step' => $step,
//                        'total_steps' => $totalSteps,
//                    ];
//                    event(new ProgressUpdateEvent($progressData));
//                    $step++;
                }
                // Close the CSV file
                $this->file->fresh();
                $this->file->status = File::STATUS_COMPLETED;
                $this->file->save();
                fclose($handle);

                DB::commit();
            }
        } catch (\Exception $e) {
            DB::rollBack();
            info($e);
        }
    }

    function removeNonUTF8($str): string
    {
        $str = preg_replace('/\p{Cf}/u', '', $str);
        return mb_convert_encoding($str, 'UTF-8', 'UTF-8');
    }
}
