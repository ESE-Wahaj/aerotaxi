<?php

namespace App\Http\Controllers;

use App\Models\AdminNotification;
use App\Models\ContactMessage;
use Illuminate\Http\Request;

class ContactController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string',
            'email' => 'required|email',
            'message' => 'required|string',
            'subject' => 'nullable|string',
        ]);

        ContactMessage::create($validated);

        AdminNotification::create([
            'type' => 'contact',
            'message' => "New contact message from {$validated['name']} - " . ($validated['subject'] ?? 'No subject'),
            'read' => false,
        ]);

        return redirect()->back()->with('success', 'Your message has been sent successfully!');
    }
}
