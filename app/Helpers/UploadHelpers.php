<?php

use App\Models\Storage\StorageModel;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;
use Illuminate\Http\Request;

if (!function_exists('UploadFile')) {
    function UploadFile(Request $request, $folder, $fileKey = 'file', $allowedExtensions = [])
    {
        // Perform custom validation here
        $validator = validator($request->all(), [
            $fileKey => [
                'required',
                'file',
                function ($attribute, $value, $fail) use ($allowedExtensions) {
                    // Check if the file extension is allowed
                    $extension = $value->getClientOriginalExtension();
                    if (!empty($allowedExtensions) && !in_array($extension, $allowedExtensions)) {
                        $fail("The $attribute must have one of the following extensions: " . implode(', ', $allowedExtensions));
                    }
                },
            ],
            // Add any other custom validation rules you need
        ]);

        if ($validator->fails()) {
            return [
                'status' => false,
                'message' => $validator->errors()->first(),
                'data' => null,
            ];
        }

        if ($request->file($fileKey)) {
            $file = $request->file($fileKey);
            $fileName = time() . '_' . $file->getClientOriginalName();

            $folderPath = 'uploads/' . $folder;

            if (!Storage::exists($folderPath)) {
                Storage::makeDirectory($folderPath);
            }

            $filePath = $file->storeAs($folderPath, $fileName, 'public');

            $file = new StorageModel([
                'id' => Str::uuid()->toString(),
                'filename' => $fileName,
                'path' => Config::get('app.url') . '/storage/' . $filePath,
                'folder' => $folder,
            ]);

            $file->save();


            return [
                'status' => true,
                'message' => 'File uploaded successfully',
                'data' => $file,
            ];
        }

        return [
            'status' => false,
            'message' => 'File not found',
            'data' => null,
        ];
    }
}
