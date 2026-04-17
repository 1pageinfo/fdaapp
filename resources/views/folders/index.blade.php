@extends('layouts.app')

@section('content')
    <div class="container mt-4">
        <h2 class="d-flex justify-content-between align-items-center mb-3">
            <span>File Manager</span>
            <a href="{{ route('folders.create') }}" class="btn btn-sm btn-primary">
                ➕ Create Folder
            </a>
        </h2>
        <hr>
        <div class="row g-3">

            @foreach ($folders as $folder)
                <div class="col-md-3">
                    <div class="card shadow-sm h-100 text-center position-relative border-0 rounded-3">

                        <!-- 3 DOT DROPDOWN BUTTON -->
                        <div class="position-absolute top-0 end-0 p-2" style="z-index: 10;">
                            <div class="dropdown">
                                <button class="btn btn-sm btn-light rounded-circle text-dark custom-icon-btn"
                                    data-bs-toggle="dropdown" aria-expanded="false"
                                    style="padding:5px; display:flex;align-items:center;justify-content:center;">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="25" height="25" fill="currentColor"
                                        class="bi bi-three-dots-vertical" viewBox="0 0 16 16">
                                        <path
                                            d="M9.5 13a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0m0-5a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0m0-5a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0" />
                                    </svg>
                                </button>

                                <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                                    <li>
                                        <a class="dropdown-item" href="{{ route('folders.edit', $folder->id) }}">
                                            ✏️ Edit
                                        </a>
                                    </li>
                                    <li>
                                        <form action="{{ route('folders.destroy', $folder->id) }}" method="POST"
                                            onsubmit="return confirm('Delete this folder?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="dropdown-item text-danger">
                                                🗑 Delete
                                            </button>
                                        </form>
                                    </li>
                                </ul>
                            </div>
                        </div>

                        <!-- Card Body -->
                        <div class="card-body d-flex flex-column align-items-center justify-content-center py-4">
                            <a href="{{ route('folders.show', $folder->id) }}"
                                class="stretched-link text-decoration-none text-reset d-flex flex-column align-items-center">
                                <div class="fs-1">📁</div>
                                <h6 class="mt-2 mb-0 w-100"
                                    style="display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;">
                                    {{ $folder->name }}
                                </h6>
                            </a>
                        </div>

                    </div>
                </div>
            @endforeach

        </div>
    </div>
<style @endsection