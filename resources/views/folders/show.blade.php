@extends('layouts.app')

@section('content')
  <div class="content-wrapper py-4">
    <div class="container-fluid">

      <!-- Page Header -->
      <div class="row align-items-center mb-3">
        {{-- Title + Breadcrumb --}}
        <div class="col-12 col-md">
          <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
            <!-- Left: Title -->
            <h2 class="mb-0 d-flex align-items-center text-truncate" style="gap:.5rem;">
              <i class="ti-folder text-primary" aria-hidden="true"></i>
              <span class="text-truncate" title="{{ $folder->name }}">{{ $folder->name }}</span>
            </h2>

            <!-- Right: Buttons -->
            <div class="btn-group mt-2 mt-md-0" role="group" aria-label="Folder actions">
              <a href="{{ route('folders.create', ['parent_id' => $folder->id]) }}" class="btn btn-outline-primary">
                <i class="ti-plus mr-1" aria-hidden="true"></i>
                <span class="d-none d-lg-inline">Add Subfolder</span><span class="d-lg-none">Subfolder</span>
              </a>
              <a href="#" class="btn btn-primary" data-toggle="modal" data-target="#uploadModal">
                <i class="ti-upload mr-1" aria-hidden="true"></i>
                <span class="d-none d-lg-inline">Upload File</span><span class="d-lg-none">Upload</span>
              </a>
            </div>
          </div>


          {{-- Breadcrumb (scrollable on small screens) --}}
          <nav aria-label="breadcrumb">
            <ol class="breadcrumb breadcrumb-custom bg-transparent p-0 m-0 overflow-auto flex-nowrap"
              style="white-space:nowrap;">
              <li class="breadcrumb-item"><a href="{{ route('folders.index') }}">Home</a></li>
              @if($folder->parent)
                <li class="breadcrumb-item">
                  <a href="{{ route('folders.show', $folder->parent->id) }}">{{ $folder->parent->name }}</a>
                </li>
              @endif
              <li class="breadcrumb-item active" aria-current="page">{{ $folder->name }}</li>
            </ol>
          </nav>
        </div>

      </div>

      <!-- Subfolders -->
      <div class="row">
        <div class="col-12 d-flex justify-content-between align-items-center mb-2">
          <h4 class="mb-0">Subfolders ( {{ $folder->subfolders->count() }} )</h4>
        </div>

        @forelse($folder->subfolders as $sub)
          <div class="col-6 col-sm-4 col-md-3 col-xl-2 mb-4">
            <div class="card shadow-sm border-0 h-100 position-relative">

              <!-- Edit/Delete Buttons -->
              <div class="position-absolute top-0 end-0 p-2 d-flex gap-2" style="z-index:10;">
                <a href="{{ route('folders.edit', $sub->id) }}" class="text-primary small">✏️</a>

                <form action="{{ route('folders.destroy', $sub->id) }}" method="POST"
                  onsubmit="return confirm('Delete this subfolder and all its content?');">
                  @csrf @method('DELETE')
                  <button class="btn btn-sm p-0 m-0 bg-transparent text-danger small">🗑</button>
                </form>
              </div>

              <a href="{{ route('folders.show', $sub->id) }}" class="text-decoration-none text-reset h-100">
                <div class="card-body text-center d-flex flex-column align-items-center justify-content-center">

                  <div class="mt-2 text-truncate w-100" title="{{ $sub->name }}"> 📁 {{ $sub->name }}</div>
                </div>
              </a>
            </div>
          </div>
        @empty
          <div class="col-12">
            <div class="alert alert-light border d-flex align-items-center" role="alert">
              <i class="ti-info-alt mr-2" aria-hidden="true"></i>
              <span class="text-muted">No subfolders.</span>
            </div>
          </div>
        @endforelse
      </div>



      <!-- Files -->
      <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-white border-0 d-flex align-items-center justify-content-between">
          <h4 class="mb-0">Files ( {{ $folder->files->count() }} )</h4>
          <span class="badge badge-light">{{ $folder->files->count() }}</span>
        </div>

        <div class="card-body p-0">
          @forelse($folder->files as $file)
            <div class="list-group-item d-flex justify-content-between align-items-center">

              <!-- Left Section -->
              <a href="{{ asset('storage/app/public/' . $file->disk_path) }}" target="_blank" rel="noopener"
                class="text-decoration-none text-reset d-flex align-items-center flex-grow-1">

                <div class="rounded-circle bg-light d-inline-flex align-items-center justify-content-center mr-3"
                  style="width:40px;height:40px;">
                  <div class="fs-4">📄</div>
                </div>

                <div>
                  <div class="font-weight-500 text-truncate" style="max-width: 220px;" title="{{ $file->name }}">
                    {{ $file->name }}
                  </div>
                  <small class="text-muted">{{ $file->created_at->format('d M Y') }}</small>
                </div>
              </a>

              <!-- Right Section: Edit/Delete -->
              <div class="d-flex align-items-center gap-2">

                <a href="{{ route('files.edit', $file->id) }}" class="text-primary small">✏️</a>

                <form action="{{ route('files.destroy', $file->id) }}" method="POST"
                  onsubmit="return confirm('Delete this file?');">
                  @csrf @method('DELETE')
                  <button class="btn btn-sm p-0 m-0 bg-transparent text-danger small">🗑</button>
                </form>

                <i class="ti-new-window text-muted ml-2" aria-hidden="true"></i>
              </div>

            </div>
          @empty
            <div class="p-3 text-muted">No files uploaded.</div>
          @endforelse
        </div>
      </div>

    </div>
  </div>
  <!-- Upload Modal -->
  <div class="modal fade" id="uploadModal" tabindex="-1" role="dialog" aria-labelledby="uploadModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
      <div class="modal-content">

        <div class="modal-header">
          <h5 class="modal-title" id="uploadModalLabel">
            <i class="ti-upload mr-2" aria-hidden="true"></i> Upload File
          </h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>

        <form action="{{ route('files.store') }}" method="POST" enctype="multipart/form-data" class="m-0" novalidate>
          @csrf
          <input type="hidden" name="folder_id" value="{{ $folder->id }}">

          <div class="modal-body">
            <div class="form-group mb-3">
              <label for="file" class="font-weight-600">Choose a file</label>
              <input type="file" id="file" name="file" class="form-control" required>
              <small class="form-text text-muted">
                Max size per server limits. Allowed types per backend validation.
              </small>
            </div>
          </div>

          <div class="modal-footer">
            <button type="button" class="btn btn-light" data-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-success">
              <i class="ti-check mr-1" aria-hidden="true"></i> Upload
            </button>
          </div>
        </form>

      </div>
    </div>
    <div class="progress mt-3 d-none" id="uploadProgressWrapper">
      <div id="uploadProgress" class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar"
        style="width: 0%">0%</div>
    </div>
  </div><script>
document.addEventListener("DOMContentLoaded", function () {

    const modal = document.getElementById("uploadModal");
    const form = document.querySelector("#uploadModal form");
    const fileInput = document.getElementById("file");
    const submitBtn = form.querySelector("button[type='submit']");
    const progressWrapper = document.getElementById("uploadProgressWrapper");
    const progressBar = document.getElementById("uploadProgress");

    form.addEventListener("submit", function (e) {
        e.preventDefault();

        if (!fileInput.files.length) {
            alert("Please select a file.");
            return;
        }

        let formData = new FormData(form);
        let xhr = new XMLHttpRequest();
        xhr.open("POST", form.action, true);

        xhr.setRequestHeader("X-CSRF-TOKEN", "{{ csrf_token() }}");

        // ENABLE PROGRESS LISTENER
        xhr.upload.onprogress = function (e) {
            if (e.lengthComputable) {
                let percent = Math.floor((e.loaded / e.total) * 100);

                progressWrapper.classList.remove("d-none");

                progressBar.style.width = percent + "%";
                progressBar.innerHTML = percent + "%";

                submitBtn.disabled = true;
                submitBtn.textContent = "Uploading " + percent + "%";
            }
        };

        // SUCCESS
        xhr.onload = function () {
            if (xhr.status === 200) {
                progressBar.classList.add("bg-success");
                submitBtn.textContent = "Upload Complete";

                setTimeout(() => location.reload(), 600);
            } else {
                showError();
            }
        };

        // ERROR
        xhr.onerror = function () {
            showError();
        };

        function showError() {
            progressBar.classList.add("bg-danger");
            progressBar.innerHTML = "Failed";
            submitBtn.disabled = false;
            submitBtn.textContent = "Upload";
        }

        xhr.send(formData);
    });

    // RESET WHEN MODAL CLOSES
    $('#uploadModal').on('hidden.bs.modal', function () {
        progressWrapper.classList.add("d-none");
        progressBar.style.width = "0%";
        progressBar.innerHTML = "0%";
        progressBar.classList.remove("bg-success", "bg-danger");

        submitBtn.disabled = false;
        submitBtn.innerHTML = "<i class='ti-check mr-1'></i> Upload";
        fileInput.value = "";
    });

});
</script>


@endsection