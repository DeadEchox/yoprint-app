<?php

namespace App\Http\Controllers;

use App\Events\ProgressUpdateEvent;
use App\Jobs\ProcessFile;
use App\Models\File;
use App\Transformers\FileTransformer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use League\Fractal\Manager;
use League\Fractal\Resource\Collection;

//use App\Models\File;

class UploadController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $files = File::all()->where('user_id', $user->id);
        $manager = new Manager();
        $resource = new Collection($files, new FileTransformer());
        $data = $manager->createData($resource)->toArray();
        return view('uploads.form', compact('data'));
    }


    public function uploadCsv(Request $request)
    {
        $user = Auth::user();
        // Validate the uploaded file
        $validator = Validator::make($request->all(), [
            'csv_file' => 'required|mimes:csv,txt',
        ]);

        if ($validator->fails()) {
            return redirect()->route('uploads.index')
                ->withErrors($validator)
                ->withInput();
        }

        if ($request->hasFile('csv_file')) {
            $update = false;
            $file = $request->file('csv_file');
            $hash = hash_file('sha256', $file->getRealPath());
            $existingFile = File::where('hash', $hash)->first();
            if (!$existingFile) {
                DB::beginTransaction();
                try {
                    // File with this hash does not exist, insert a new record
                    $originalFileName = $file->getClientOriginalName();
                    $timestamp = now()->format('U');
                    $fileName = $timestamp . '_' . Str::random(5) . '_' . $originalFileName;
                    $file->storeAs('csv', $fileName);
                    $csvPath = storage_path('app/csv/' . $fileName);
                    $hash = hash_file('sha256', $file->getRealPath());// Insert a new file record
                    $existingFile = File::create([
                        'user_id' => $user->id,
                        'original_name' => $originalFileName,
                        'name' => $fileName,
                        'hash' => $hash,
                        'path' => $csvPath,
                        'status' => File::STATUS_PENDING,
                    ]);
                    DB::commit();
                    // Dispatch the ProcessFile job
                    ProcessFile::dispatchSync($existingFile, $existingFile->id);
                } catch (\Exception $e) {
                    DB::rollBack();
                    return redirect()->route('uploads.index')->with('error', $e->getMessage());
                }
            } else {
                // File with the same hash exists, perform an upsert operation
                $this->upsertFile($file, $existingFile);
            }

            return redirect()->route('uploads.index')->with('success', 'CSV file uploaded and data saved successfully.');
        } else {
            return redirect()->route('uploads.index')->with('error', 'Please choose a CSV file to upload.');
        }
    }

    private function upsertFile($newFile, $existingFile)
    {
        // Perform the upsert operation
        DB::beginTransaction();

        try {
            // Contents are different, so update the existing file
            $originalFileName = $newFile->getClientOriginalName();
            $timestamp = now()->format('U');
            $fileName = $timestamp . '_' . Str::random(5) . '_' . $originalFileName;
            $newFile->storeAs('csv', $fileName);
            $csvPath = storage_path('app/csv/' . $fileName);
            $hash = hash_file('sha256', $newFile->getRealPath());
            $existingFile->original_name = $originalFileName;
            $existingFile->name = $fileName;
            $existingFile->path = $csvPath;
            $existingFile->hash = $hash;
            $existingFile->status = File::STATUS_PENDING;
            $existingFile->save();
            DB::commit();

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('uploads.index')->with('error', $e->getMessage());
        }

        // Dispatch the ProcessFile job
        ProcessFile::dispatchSync($existingFile, $existingFile->id);
        return null;
    }


}
