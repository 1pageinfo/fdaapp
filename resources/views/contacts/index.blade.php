@extends('layouts.app')

@section('content')
<div class="container-fluid mt-4">

    <div class="d-flex flex-column flex-sm-row align-items-sm-center justify-content-between gap-2 mb-3">
        <h2 class="mb-0 d-flex align-items-center">
            <i class="fa fa-address-book mr-2 d-none d-sm-inline"></i> Contacts
        </h2>
    </div>
    <p class="text-muted mb-3">Directory of everyone in the organization — reach out by email or phone directly from here.</p>

    <div class="mb-3">
        <div class="input-group" style="max-width: 420px">
            <div class="input-group-prepend">
                <span class="input-group-text"><i class="icon-search"></i></span>
            </div>
            <input id="contactFilter" type="text" class="form-control" placeholder="Search by name, designation, email…" autocomplete="off">
        </div>
    </div>

    <div class="row g-3" id="contactList">
        @forelse($contacts as $contact)
            @php
                $initial = mb_strtoupper(mb_substr($contact->name ?? 'U', 0, 1));
                $palette = ['bg-primary', 'bg-success', 'bg-info', 'bg-warning', 'bg-danger', 'bg-secondary'];
                $color = $palette[($contact->id ?? 0) % count($palette)];
                $searchKey = Str::lower($contact->name . ' ' . $contact->designation . ' ' . $contact->email);
            @endphp
            <div class="col-sm-6 col-lg-4 col-xl-3 mb-3 contact-item" data-name="{{ $searchKey }}">
                <div class="card shadow-sm h-100 border-0 contact-card">
                    <div class="card-body d-flex flex-column align-items-center text-center">
                        @if($contact->photo_path)
                            <img src="{{ asset('storage/' . $contact->photo_path) }}" alt="{{ $contact->name }}"
                                 class="contact-photo mb-2">
                        @else
                            <div class="avatar {{ $color }} text-white mb-2">{{ $initial }}</div>
                        @endif

                        <h6 class="mb-0 text-truncate w-100" title="{{ $contact->name }}">{{ $contact->name }}</h6>
                        @if($contact->designation)
                            <div class="small text-muted text-truncate w-100 mb-2">{{ $contact->designation }}</div>
                        @else
                            <div class="mb-2"></div>
                        @endif

                        <div class="w-100 text-left small">
                            @if($contact->email)
                                <div class="d-flex align-items-center mb-1">
                                    <i class="ti-email mr-2 text-muted"></i>
                                    <a href="mailto:{{ $contact->email }}" class="text-truncate text-reset">{{ $contact->email }}</a>
                                </div>
                            @endif
                            @if($contact->phone)
                                <div class="d-flex align-items-center mb-1">
                                    <i class="ti-mobile mr-2 text-muted"></i>
                                    <a href="tel:{{ $contact->phone }}" class="text-reset">{{ $contact->phone }}</a>
                                </div>
                            @endif
                            @if($contact->address)
                                <div class="d-flex align-items-start">
                                    <i class="ti-location-pin mr-2 text-muted mt-1"></i>
                                    <span class="text-muted text-truncate-2">{{ $contact->address }}</span>
                                </div>
                            @endif
                        </div>

                        <div class="w-100 mt-3 d-flex gap-2">
                            @if($contact->email)
                                <a href="mailto:{{ $contact->email }}" class="btn btn-sm btn-outline-primary flex-fill">
                                    <i class="ti-email mr-1"></i>Email
                                </a>
                            @endif
                            @if($contact->phone)
                                <a href="tel:{{ $contact->phone }}" class="btn btn-sm btn-outline-success flex-fill">
                                    <i class="ti-mobile mr-1"></i>Call
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="text-center text-muted py-5">
                    <i class="fa fa-address-book" style="font-size: 2.5rem;"></i>
                    <p class="mt-2 mb-0">No contacts found.</p>
                </div>
            </div>
        @endforelse
    </div>
</div>

<style>
    .avatar {
        width: 64px;
        height: 64px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 1.4rem;
    }

    .contact-photo {
        width: 64px;
        height: 64px;
        border-radius: 50%;
        object-fit: cover;
    }

    .contact-card {
        border-radius: 14px;
        transition: transform .15s ease, box-shadow .15s ease;
    }

    .contact-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 .5rem 1.25rem rgba(0, 0, 0, .1) !important;
    }

    .text-truncate-2 {
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
</style>

<script>
    (function () {
        const filter = document.getElementById('contactFilter');
        const list = document.getElementById('contactList');
        if (!filter || !list) return;

        filter.addEventListener('input', function () {
            const term = (this.value || '').trim().toLowerCase();
            list.querySelectorAll('.contact-item').forEach(item => {
                item.style.display = item.dataset.name.includes(term) ? '' : 'none';
            });
        });
    })();
</script>
@endsection
