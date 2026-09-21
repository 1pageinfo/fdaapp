<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class ContactController extends Controller
{
    /**
     * A read-only staff directory — every user's contact details, so members
     * can find and reach each other. Not scoped by ownership like the other
     * resources (that wouldn't make sense for a directory); just gated by
     * the contacts.view permission, granted to everyone by default.
     */
    public function index(Request $request)
    {
        $contacts = User::orderBy('name')->get([
            'id', 'name', 'email', 'phone', 'designation', 'address', 'photo_path',
        ]);

        return view('contacts.index', compact('contacts'));
    }
}
