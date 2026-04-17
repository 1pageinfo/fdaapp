@php
    $platformValue = old('platform', $link->platform ?? null);
    $categoryValue = old('category', $link->category ?? 'social');
@endphp

<div class="mb-3">
    <label class="form-label">Title</label>
    <input type="text" name="title" class="form-control" value="{{ old('title', $link->title ?? '') }}" required>
    @error('title') <div class="text-danger">{{ $message }}</div>@enderror
</div>

<div class="mb-3">
    <label class="form-label">Category</label>
    <select name="category" id="category" class="form-select" required>
        <option value="social" {{ $categoryValue === 'social' ? 'selected' : '' }}>Social</option>
        <option value="other" {{ $categoryValue === 'other' ? 'selected' : '' }}>Other</option>
        <option value="custom" {{ $categoryValue === 'custom' ? 'selected' : '' }}>Add new link (custom)</option>
    </select>
    @error('category') <div class="text-danger">{{ $message }}</div>@enderror
</div>

<div class="mb-3" id="platform-selects">
    <div id="social-select" style="{{ $categoryValue !== 'social' ? 'display:none' : '' }}">
        <label class="form-label">Platform (Social)</label>
        <select name="platform" class="form-select">
            <option value="">-- choose --</option>
            @foreach($socialPlatforms as $p)
                <option value="{{ $p }}" {{ $platformValue === $p ? 'selected' : '' }}>{{ ucfirst($p) }}</option>
            @endforeach
        </select>
    </div>

    <div id="other-select" style="{{ $categoryValue !== 'other' ? 'display:none' : '' }}">
        <label class="form-label">Platform (Other)</label>
        <select name="platform" class="form-select">
            <option value="">-- choose --</option>
            @foreach($otherPlatforms as $p)
                <option value="{{ $p }}" {{ $platformValue === $p ? 'selected' : '' }}>{{ ucfirst($p) }}</option>
            @endforeach
        </select>
    </div>

    <div id="custom-input" style="{{ $categoryValue !== 'custom' ? 'display:none' : '' }}">
        <label class="form-label">Custom Platform Name</label>
        <input type="text" name="custom_platform" class="form-control" value="{{ old('custom_platform', $link->platform ?? '') }}">
    </div>
</div>

<div class="mb-3">
    <label class="form-label">URL</label>
    <input type="text" name="url" class="form-control" value="{{ old('url', $link->url ?? '') }}" placeholder="https://example.com or mailto: or whatsapp:+919XXXXXXXXX" required>
    @error('url') <div class="text-danger">{{ $message }}</div>@enderror
</div>

<div class="mb-3">
    <label class="form-label">Icon (optional)</label>
    <input type="text" name="icon" class="form-control" value="{{ old('icon', $link->icon ?? '') }}" placeholder="e.g. fa-brands fa-facebook or custom svg class">
</div>

<div class="mb-3 row">
    <div class="col-md-6">
        <label class="form-label">Sort Order</label>
        <input type="number" name="sort_order" class="form-control" value="{{ old('sort_order', $link->sort_order ?? 0) }}">
    </div>
    <div class="col-md-6">
        <label class="form-label">Active</label>
        <select name="is_active" class="form-select">
            <option value="1" {{ (old('is_active', $link->is_active ?? 1) ? 'selected' : '') }}>Yes</option>
            <option value="0" {{ (!old('is_active', $link->is_active ?? 1) ? 'selected' : '') }}>No</option>
        </select>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const category = document.getElementById('category');
        const social = document.getElementById('social-select');
        const other = document.getElementById('other-select');
        const custom = document.getElementById('custom-input');

        function toggle() {
            const val = category.value;
            social.style.display = (val === 'social') ? '' : 'none';
            other.style.display = (val === 'other') ? '' : 'none';
            custom.style.display = (val === 'custom') ? '' : 'none';
        }

        category.addEventListener('change', toggle);
        toggle();
    });
</script>
