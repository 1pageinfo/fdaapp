<?php

namespace App\Http\Controllers;

use App\Models\Link;
use Illuminate\Http\Request;

class LinkController extends Controller
{
    // If you want auth:
    // public function __construct() { $this->middleware('auth'); }

    public function index()
    {
        // grouped listing
        $links = Link::orderBy('sort_order')->orderBy('id','desc')->get()
            ->groupBy('category');

        return view('links.index', compact('links'));
    }

    public function create()
    {
        // predefined platforms (social + other)
        $socialPlatforms = ['facebook', 'instagram', 'youtube', 'linkedin', 'x.com'];
        $otherPlatforms = ['mail', 'whatsapp', 'website'];

        return view('links.create', compact('socialPlatforms', 'otherPlatforms'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:191',
            'category' => 'required|in:social,other,custom',
            'platform' => 'nullable|string|max:191',
            'custom_platform' => 'nullable|string|max:191',
            'url' => 'required|string|max:2000',
            'icon' => 'nullable|string|max:255',
            'sort_order' => 'nullable|integer',
            'is_active' => 'nullable|boolean',
        ]);

        // If category is custom and user supplied custom_platform, use that
        if ($data['category'] === 'custom' && !empty($data['custom_platform'])) {
            $data['platform'] = $data['custom_platform'];
        }

        // Basic URL normalization: if user entered whatsapp number or mail, accept as-is.
        // If you require full URL validation, use: 'url' => 'required|url' but that rejects mailto:/tel: etc.
        // Optionally you can add more checks here.

        $link = Link::create([
            'user_id' => auth()->id() ?? null,
            'title' => $data['title'],
            'category' => $data['category'],
            'platform' => $data['platform'] ?? null,
            'url' => $data['url'],
            'icon' => $data['icon'] ?? null,
            'sort_order' => $data['sort_order'] ?? 0,
            'is_active' => isset($data['is_active']) ? (bool)$data['is_active'] : true,
        ]);

        return redirect()->route('links.index')->with('success', 'Link created successfully.');
    }

    public function edit(Link $link)
    {
        $socialPlatforms = ['facebook', 'instagram', 'youtube', 'linkedin', 'x.com'];
        $otherPlatforms = ['mail', 'whatsapp', 'website'];

        return view('links.edit', compact('link', 'socialPlatforms', 'otherPlatforms'));
    }

    public function update(Request $request, Link $link)
    {
        $data = $request->validate([
            'title' => 'required|string|max:191',
            'category' => 'required|in:social,other,custom',
            'platform' => 'nullable|string|max:191',
            'custom_platform' => 'nullable|string|max:191',
            'url' => 'required|string|max:2000',
            'icon' => 'nullable|string|max:255',
            'sort_order' => 'nullable|integer',
            'is_active' => 'nullable|boolean',
        ]);

        if ($data['category'] === 'custom' && !empty($data['custom_platform'])) {
            $data['platform'] = $data['custom_platform'];
        }

        $link->update([
            'title' => $data['title'],
            'category' => $data['category'],
            'platform' => $data['platform'] ?? null,
            'url' => $data['url'],
            'icon' => $data['icon'] ?? null,
            'sort_order' => $data['sort_order'] ?? 0,
            'is_active' => isset($data['is_active']) ? (bool)$data['is_active'] : true,
        ]);

        return redirect()->route('links.index')->with('success', 'Link updated successfully.');
    }

    public function destroy(Link $link)
    {
        $link->delete();
        return redirect()->route('links.index')->with('success', 'Link deleted.');
    }
}
