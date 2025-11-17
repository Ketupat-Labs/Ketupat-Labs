<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Models\Submission;

class SubmissionController extends Controller
{
    public function show(): View
    {
        $assignmentName = "HCI Screen Design Mock-up (3.2)";
        $requiredFiles = "(.html, .zip, .png, .jpg)";
        
        // Get user's latest submission
        $submission = Submission::where('user_id', Auth::id())
            ->latest()
            ->first();
        
        $currentStatus = $submission ? $submission->status : 'Not Submitted';
        $isSubmitted = ($currentStatus === 'Submitted - Awaiting Grade');
        
        return view('submission.show', compact('assignmentName', 'requiredFiles', 'submission', 'currentStatus', 'isSubmitted'));
    }

    public function submit(Request $request): RedirectResponse
    {
        $request->validate([
            'submission_file' => 'required|file|mimes:html,zip,png,jpg,jpeg|max:10240', // 10MB max
        ]);
        
        // Check if user already submitted
        $existingSubmission = Submission::where('user_id', Auth::id())
            ->where('status', 'Submitted - Awaiting Grade')
            ->first();
            
        if ($existingSubmission) {
            return redirect()->route('submission.show')
                ->with('error', 'You have already submitted. Please wait for grading.');
        }
        
        // Store file
        $filePath = null;
        if ($request->hasFile('submission_file')) {
            $storagePath = $request->file('submission_file')->store('public/submissions');
            $filePath = str_replace('public/', 'storage/', $storagePath);
        }
        
        // Create submission record
        Submission::create([
            'user_id' => Auth::id(),
            'assignment_name' => 'HCI Screen Design Mock-up (3.2)',
            'file_path' => $filePath,
            'file_name' => $request->file('submission_file')->getClientOriginalName(),
            'status' => 'Submitted - Awaiting Grade',
        ]);
        
        return redirect()->route('submission.show')
            ->with('success', 'Your file has been submitted and is awaiting teacher grade.');
    }
}

