<?php

namespace App\Http\Controllers;

use App\Models\Lesson;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use App\Models\Classes;      // NEW: To fetch the list of classes
use App\Models\LessonProgress; // NEW: To save the progress records

class LessonController extends Controller
{
    // --- READ (INDEX) - UC004: Display list of lessons for the current teacher ---
    public function index(): View
    {
        // Fetch lessons created ONLY by the currently authenticated teacher
        $lessons = Lesson::where('teacher_id', Auth::id())->latest()->get(); 
        
        return view('lessons.index', compact('lessons'));
    }

    // --- CREATE (CREATE): Show the add form ---
    public function create(): View
    {
        return view('lessons.create');
    }

    // --- CREATE (STORE): Handle form submission and save to DB (UC003) ---
    public function store(Request $request): RedirectResponse
    {
        // 1. Validation 
        $request->validate([
            'title' => 'required|string|max:255',
            'topic' => 'required|string|in:HCI,HCI_SCREEN,Algorithm',
            'duration' => 'nullable|integer|min:5',
            'material_file' => 'nullable|file|mimes:pdf,doc,docx|max:5120', 
        ]);
        
        $filePath = null;
        
        // 2. File Upload and Storage
        if ($request->hasFile('material_file')) {
            $storagePath = $request->file('material_file')->store('public/lessons');
            $filePath = str_replace('public/', 'storage/', $storagePath); 
        }
        
        // 3. Create the Lesson Record in MySQL (Using validated data)
        Lesson::create([
            'title' => $request->title,
            'topic' => $request->topic,
            'material_path' => $filePath,
            'duration' => $request->duration,
            'teacher_id' => Auth::id(), 
            'is_published' => true, 
        ]);

        return redirect()->route('lessons.index')->with('success', 'Lesson saved and material uploaded successfully!');
    }

    // --- READ (SHOW) - UC004: Display a single lesson for student view ---
    public function show(Lesson $lesson): View
    {
        // This is the student content consumption view
        return view('lessons.show', compact('lesson'));
    }

    // --- UPDATE (EDIT): Show the pre-filled form (UC003) ---
    public function edit(Lesson $lesson): View
    {
        // Authorization check
        if (Auth::id() !== $lesson->teacher_id) {
            return redirect()->route('lessons.index')->with('error', 'Unauthorized.');
        }
        return view('lessons.edit', compact('lesson'));
    }

    // --- UPDATE (UPDATE): Handle form submission and save changes (UC003) ---
    public function update(Request $request, Lesson $lesson): RedirectResponse
    {
        // Authorization check
        if (Auth::id() !== $lesson->teacher_id) {
            return redirect()->route('lessons.index')->with('error', 'Unauthorized.');
        }

        // 1. Validation (CRITICAL: Validation must be done before update)
        $validatedData = $request->validate([
            'title' => 'required|string|max:255',
            'topic' => 'required|string|in:HCI,HCI_SCREEN,Algorithm',
            'duration' => 'nullable|integer|min:5',
            'material_file' => 'nullable|file|mimes:pdf,doc,docx|max:5120', 
        ]);
        
        $filePath = $lesson->material_path; 
        
        // 2. Handle File Replacement
        if ($request->hasFile('material_file')) {
            // Delete old file from storage
            if ($lesson->material_path) {
                Storage::delete(str_replace('storage/', 'public/', $lesson->material_path));
            }
            
            // Upload new file
            $storagePath = $request->file('material_file')->store('public/lessons');
            $filePath = str_replace('public/', 'storage/', $storagePath);
        }
        
        // 3. Update the Lesson Record (Using ONLY validated data + file path)
        $lesson->update([
            'title' => $validatedData['title'],
            'topic' => $validatedData['topic'],
            'duration' => $validatedData['duration'],
            'material_path' => $filePath, 
        ]);

        return redirect()->route('lessons.index')->with('success', 'Lesson updated successfully!');
    }

    // --- DELETE (DESTROY): Handle deletion request (US003) ---
    public function destroy(Lesson $lesson): RedirectResponse
    {
        if (Auth::id() !== $lesson->teacher_id) {
            return redirect()->route('lessons.index')->with('error', 'Unauthorized.');
        }

        // 1. Delete the physical file from storage (if it exists)
        if ($lesson->material_path) {
            Storage::delete(str_replace('storage/', 'public/', $lesson->material_path));
        }

        // 2. Delete the database record
        $lesson->delete();

        return redirect()->route('lessons.index')->with('success', 'Lesson deleted successfully!');
    }

    // --- STUDENT VIEW: List all published lessons ---
    public function studentIndex(): View
    {
        $lessons = Lesson::where('is_published', true)->latest()->get();
        return view('lessons.student-index', compact('lessons'));
    }

    // --- STUDENT VIEW: Show lesson content for students ---
    public function studentShow(Lesson $lesson): View
    {
        // Check if lesson is published
        if (!$lesson->is_published) {
            abort(404);
        }
        return view('lessons.student-show', compact('lesson'));
    }

    // --- Custom Route: Show the Assignment Form ---
public function showAssignForm(): View
{
    // Fetch all lessons created by the current teacher
    $lessons = Lesson::where('teacher_id', Auth::id())->get();
    
    // Fetch all classes managed by the current teacher (Vanee's module)
    $classes = Classes::where('teacher_id', Auth::id())->get();
    
    // You will need to create a view named 'lessons.assign-form'
    return view('lessons.assign-form', compact('lessons', 'classes'));
}


// --- Custom Route: Handle Assignment Submission (UC006) ---
public function assignLessons(Request $request): RedirectResponse
{
    // 1. Validation (CRITICAL: Ensure mandatory fields are selected)
    $request->validate([
        'lesson_ids' => 'required|array',       // Array of lessons to assign
        'lesson_ids.*' => 'exists:lessons,id',  // Check if each lesson ID is valid
        'class_id' => 'required|exists:classes,id', // Check if the selected class ID is valid
    ]);

    $classId = $request->input('class_id');
    $lessonIds = $request->input('lesson_ids');

    // 2. Fetch all students in the selected class (from Vanee's module/table)
    // NOTE: This assumes you have a relationship on the Classes model to get students.
    $class = Classes::with('students')->findOrFail($classId); // Assuming students relation is defined
    $studentIds = $class->students->pluck('student_id')->toArray();
    
    $recordsCreated = 0;

    // 3. Loop through students and lessons to create lesson_progress records (M6)
    foreach ($studentIds as $studentId) {
        foreach ($lessonIds as $lessonId) {
            
            // Insert the record, ignoring duplicates (in case of re-assignment)
            LessonProgress::updateOrCreate(
                [
                    'student_id' => $studentId, 
                    'lesson_id' => $lessonId
                ],
                [
                    'class_id' => $classId,
                    'is_mandatory' => true, // Set as mandatory as per the US
                    'progress_percent' => 0,
                    'status' => 'enrolled'
                ]
            );
            $recordsCreated++;
        }
    }

    return redirect()->route('lessons.index')
        ->with('success', "Successfully assigned $recordsCreated lessons as mandatory for " . $class->name . ".");
}
}