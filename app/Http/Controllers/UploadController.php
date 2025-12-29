<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class UploadController extends Controller
{
    public function upload(Request $request)
    {
        // Debug: Log PHP settings and return them in error response
        $phpSettings = [
            'upload_max_filesize' => ini_get('upload_max_filesize'),
            'post_max_size' => ini_get('post_max_size'),
            'max_file_uploads' => ini_get('max_file_uploads'),
            'php_ini_loaded_file' => php_ini_loaded_file(),
            'memory_limit' => ini_get('memory_limit'),
        ];
        \Log::info('PHP Upload Settings', $phpSettings);
        
        // Debug: Log what we received
        \Log::info('Upload request', [
            'has_files' => $request->hasFile('files'),
            'has_file' => $request->hasFile('file'),
            'all_files_keys' => array_keys($request->allFiles()),
            'content_type' => $request->header('Content-Type'),
        ]);
        
        // Get all files from request (handles both 'files' and 'files[]' notation)
        $allFiles = $request->allFiles();
        
        if (empty($allFiles)) {
            return response()->json([
                'status' => 400,
                'message' => 'No files uploaded',
                'debug' => [
                    'has_files' => $request->hasFile('files'),
                    'has_file' => $request->hasFile('file'),
                    'all_input_keys' => array_keys($request->all()),
                ],
            ], 400);
        }
        
        // Get files and validate
        $uploadedFiles = [];
        $errors = [];
        
        // Handle 'files' array (from FormData with files[])
        if (isset($allFiles['files'])) {
            $files = $allFiles['files'];
            if (!is_array($files)) {
                $files = [$files];
            }
            
            foreach ($files as $index => $file) {
                if (!$file) {
                    $errors[] = "File at index {$index} is null or missing";
                    continue;
                }
                
                // Check if file is an uploaded file instance
                if (!($file instanceof \Illuminate\Http\UploadedFile)) {
                    $errors[] = "File at index {$index} is not a valid uploaded file";
                    continue;
                }
                
                // Check file validity - isValid() checks for upload errors
                if (!$file->isValid()) {
                    $errorCode = $file->getError();
                    $errorMessages = [
                        UPLOAD_ERR_INI_SIZE => 'File exceeds PHP upload_max_filesize limit (' . ini_get('upload_max_filesize') . '). Current limit: ' . ini_get('upload_max_filesize') . '. Required: 50M. Please restart your Laravel server (php artisan serve) after updating php.ini at: ' . php_ini_loaded_file(),
                        UPLOAD_ERR_FORM_SIZE => 'File exceeds MAX_FILE_SIZE directive',
                        UPLOAD_ERR_PARTIAL => 'File was only partially uploaded',
                        UPLOAD_ERR_NO_FILE => 'No file was uploaded',
                        UPLOAD_ERR_NO_TMP_DIR => 'Missing temporary folder',
                        UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk',
                        UPLOAD_ERR_EXTENSION => 'A PHP extension stopped the file upload',
                    ];
                    $errorMsg = $errorMessages[$errorCode] ?? "Upload error code: {$errorCode}";
                    $errors[] = "File '{$file->getClientOriginalName()}' is invalid: {$errorMsg}";
                    continue;
                }
                
                if ($file->getSize() > 50 * 1024 * 1024) { // 50MB in bytes
                    $errors[] = "File '{$file->getClientOriginalName()}' exceeds 50MB limit";
                    continue;
                }
                
                $uploadedFiles[] = $file;
            }
        } elseif (isset($allFiles['file'])) {
            // Handle single 'file'
            $file = $allFiles['file'];
            if (!$file) {
                $errors[] = "File is null or missing";
            } elseif (!($file instanceof \Illuminate\Http\UploadedFile)) {
                $errors[] = "File is not a valid uploaded file";
            } elseif (!$file->isValid()) {
                $errorCode = $file->getError();
                $errorMessages = [
                    UPLOAD_ERR_INI_SIZE => 'File exceeds PHP upload_max_filesize limit (' . ini_get('upload_max_filesize') . '). Current limit: ' . ini_get('upload_max_filesize') . '. Required: 50M. Please restart your Laravel server (php artisan serve) after updating php.ini at: ' . php_ini_loaded_file(),
                    UPLOAD_ERR_FORM_SIZE => 'File exceeds MAX_FILE_SIZE directive',
                    UPLOAD_ERR_PARTIAL => 'File was only partially uploaded',
                    UPLOAD_ERR_NO_FILE => 'No file was uploaded',
                    UPLOAD_ERR_NO_TMP_DIR => 'Missing temporary folder',
                    UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk',
                    UPLOAD_ERR_EXTENSION => 'A PHP extension stopped the file upload',
                ];
                $errorMsg = $errorMessages[$errorCode] ?? "Upload error code: {$errorCode}";
                $errors[] = "File '{$file->getClientOriginalName()}' is invalid: {$errorMsg}";
            } elseif ($file->getSize() > 50 * 1024 * 1024) {
                $errors[] = "File '{$file->getClientOriginalName()}' exceeds 50MB limit";
            } else {
                $uploadedFiles[] = $file;
            }
        } else {
            // Try to get any file from the request
            foreach ($allFiles as $key => $fileOrArray) {
                if (is_array($fileOrArray)) {
                    foreach ($fileOrArray as $file) {
                        if ($file && $file->isValid() && $file->getSize() <= 50 * 1024 * 1024) {
                            $uploadedFiles[] = $file;
                        }
                    }
                } elseif ($fileOrArray && $fileOrArray->isValid() && $fileOrArray->getSize() <= 50 * 1024 * 1024) {
                    $uploadedFiles[] = $fileOrArray;
                }
            }
        }
        
        if (!empty($errors)) {
            return response()->json([
                'status' => 400,
                'message' => 'File validation failed',
                'errors' => $errors,
            ], 400);
        }
        
        if (empty($uploadedFiles)) {
            return response()->json([
                'status' => 400,
                'message' => 'No valid files uploaded',
            ], 400);
        }

        $uploadedFilesData = [];

        foreach ($uploadedFiles as $file) {
            try {
                // Get file info BEFORE moving (while temp file still exists)
                $originalName = $file->getClientOriginalName();
                $fileSize = $file->getSize();
                $mimeType = $file->getMimeType(); // Get mime type before moving
                $extension = $file->getClientOriginalExtension();
                
                // Generate directory path: uploads/YYYY/MM/
                $directory = public_path('uploads/' . date('Y/m/'));
                
                // Create directory if it doesn't exist
                if (!file_exists($directory)) {
                    mkdir($directory, 0755, true);
                }
                
                // Generate unique filename
                $filename = uniqid('file_', true) . '.' . $extension;
                
                // Store file directly in public/uploads/
                $file->move($directory, $filename);
                
                // Generate URL path (relative to public directory)
                $urlPath = '/uploads/' . date('Y/m/') . $filename;
                
                $uploadedFilesData[] = [
                    'url' => $urlPath, // e.g., /uploads/2025/12/file_xxx.png
                    'name' => $originalName,
                    'type' => $mimeType,
                    'size' => $fileSize,
                ];
            } catch (\Exception $e) {
                \Log::error('File upload error', [
                    'file' => $file->getClientOriginalName(),
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
                $errors[] = "Failed to process file '{$file->getClientOriginalName()}': " . $e->getMessage();
            }
        }

        if (!empty($errors)) {
            return response()->json([
                'status' => 400,
                'message' => 'File processing failed',
                'errors' => $errors,
            ], 400);
        }
        
        if (empty($uploadedFilesData)) {
            return response()->json([
                'status' => 400,
                'message' => 'No valid files uploaded',
            ], 400);
        }

        return response()->json([
            'status' => 200,
            'message' => 'Files uploaded successfully',
            'data' => [
                'files' => $uploadedFilesData,
            ],
        ], 200);
    }
}

