<?php

namespace App\Http\Controllers\Storage;

use App\Http\Controllers\Controller;
use App\Http\Requests\Storage\StorageRequest;
use App\Models\Storage\StorageModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;
use Ramsey\Uuid\Uuid;

class StorageController extends Controller
{
    public function upload(StorageRequest $request)
    {
        if (isset($request->validator) && $request->validator->fails()) {
            return response()->error($request->validator->errors(), 400);
        }

        if ($request->file('file')) {
            $file = $request->file('file');
            $fileName = time() . '_' . $file->getClientOriginalName();

            $folderPath = 'uploads/' . $request->folder;

            if (!Storage::exists($folderPath)) {
                Storage::makeDirectory($folderPath);
            }

            $filePath = $file->storeAs($folderPath, $fileName, 'public');

            $file = new StorageModel([
                'id' => Uuid::uuid4()->toString(),
                'filename' => $fileName,
                'path' => Config::get('app.url') . '/storage/' . $filePath,
                'folder' => $request->folder,
            ]);

            $file->save();

            return response()->success($file, 201, 'File uploaded successfully');
        }

        return response()->error('File not found', 404);
    }
}
