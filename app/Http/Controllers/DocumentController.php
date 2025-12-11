<?php

namespace App\Http\Controllers;

use App\Models\Classroom;
use App\Models\Document;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class DocumentController extends Controller
{
    /**
     * Display the document management page for a class.
     */
    public function show(Request $request, $class_id)
    {
        $class = Classroom::findOrFail($class_id);

        // Authorization - teacher or enrolled student
        $enrolled_student_ids = $class->students()->pluck('id')->toArray();

        if (Auth::id() !== $class->teacher_id && !in_array(Auth::id(), $enrolled_student_ids)) {
            return abort(403, 'Anda tidak dibenarkan melihat halaman ini.');
        }

        return view('class_documents', ['class' => $class]);
    }

    /**
     * Store a new document upload.
     */
    public function store(Request $request, $class_id)
    {
        $class = Classroom::findOrFail($class_id);

        // Only teacher can upload
        if (Auth::id() !== $class->teacher_id) {
            return back()->with('error', 'Anda tidak dibenarkan memuat naik dokumen.');
        }

        $request->validate([
            'documents' => 'required|array',
            'documents.*' => [
                'required',
                'file',
                'mimes:pdf,doc,docx,pptx',
                'max:10240', // 10MB
            ],
        ], [
            'documents.*.mimes' => 'Fail mestilah PDF, DOCX, atau PPTX.',
            'documents.*.max' => 'Fail tidak boleh melebihi 10MB.',
        ]);

        foreach ($request->file('documents') as $file) {
            $path = $file->store('class_documents', 'private');

            Document::create([
                'class_id' => $class_id,
                'uploader_id' => Auth::id(),
                'original_filename' => $file->getClientOriginalName(),
                'storage_path' => $path,
                'mime_type' => $file->getMimeType(),
                'file_size_bytes' => $file->getSize(),
            ]);
        }

        return back()->with('success', 'Dokumen berjaya dimuat naik!');
    }

    /**
     * Remove a document.
     */
    public function destroy(Request $request, $document_id)
    {
        $document = Document::findOrFail($document_id);

        if (Auth::id() !== $document->uploader_id) {
            return back()->with('error', 'Anda tidak dibenarkan memadam dokumen ini.');
        }

        Storage::disk('private')->delete($document->storage_path);
        $document->delete();

        return back()->with('success', 'Dokumen berjaya dipadam.');
    }

    /**
     * Download a specific document.
     */
    public function download(Request $request, $document_id)
    {
        $document = Document::findOrFail($document_id);
        $class = $document->class;
        $enrolled_student_ids = $class->students()->pluck('id')->toArray();

        if (Auth::id() !== $class->teacher_id && !in_array(Auth::id(), $enrolled_student_ids)) {
            return abort(403, 'Anda tidak dibenarkan memuat turun fail ini.');
        }

        $absolutePath = storage_path('app/private/' . $document->storage_path);
        return response()->download($absolutePath, $document->original_filename);
    }

    /**
     * Get documents for AI analysis
     */
    public function getForAi(Request $request, $class_id)
    {
        $class = Classroom::findOrFail($class_id);
        
        // Authorization
        $enrolled_student_ids = $class->students()->pluck('id')->toArray();
        if (Auth::id() !== $class->teacher_id && !in_array(Auth::id(), $enrolled_student_ids)) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $documents = Document::where('class_id', $class_id)
            ->where('is_active', true)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'documents' => $documents,
        ]);
    }
}
