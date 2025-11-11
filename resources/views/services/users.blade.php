@php
    use Illuminate\Support\Str;
    $html_tag_data = [];
    $title = $title ?? 'Users';
    $description = $description ?? 'Service Provider Users';
@endphp

@extends('layout', ['html_tag_data'=>$html_tag_data, 'title'=>$title, 'description'=>$description])

@section('css')
@endsection

@section('js_vendor')
@endsection

@section('js_page')
@endsection

@section('content')
<div class="col">
    <!-- Title and Top Buttons Start -->
    <div class="page-title-container mb-3">
        <div class="row align-items-center">
            <!-- Title Start -->
            <div class="col mb-2">
                <h1 class="mb-2 pb-0 display-4" id="title">{{ $title }}</h1>
                <div class="text-muted font-heading text-small">
                    Let us manage the database engines for your applications so you can focus on building.
                </div>
            </div>
            <!-- Title End -->

            <!-- Top Buttons + Search Start -->
            <div class="col-12 col-sm-auto d-flex align-items-center justify-content-end gap-2">
                <form method="GET" action="{{ route('services.users') }}" class="d-flex">
                    <input type="search" name="q" value="{{ $q ?? '' }}" class="form-control"
                           placeholder="Search name, email, description" style="min-width:260px">
                </form>

                <a href="#" class="btn btn-outline-primary btn-icon btn-icon-start w-100 w-md-auto">
                    <i data-acorn-icon="plus"></i>
                    <span>Add New</span>
                </a>
            </div>
            <!-- Top Buttons + Search End -->
        </div>
    </div>
    <!-- Title and Top Buttons End -->

    <!-- User Cards Start -->
    <div class="row row-cols-1 row-cols-md-3 row-cols-xxl-4 g-2">
        @forelse($users as $u)
            @php
                $img = $u->avatar
                    ? (Str::startsWith($u->avatar, ['http://','https://','data:'])
                        ? $u->avatar
                        : asset('storage/'.$u->avatar))
                    : '/img/profile/profile-9.webp';
            @endphp
            <div class="col">
                <div class="card h-100">
                    <div class="card-body pb-0">
                        <div class="d-flex flex-column align-items-center mb-5">
                            <img class="sw-9 sh-9 rounded-xl mb-3" src="{{ $img }}" alt="{{ $u->name }}" style="object-fit:cover" />
                            <h5 class="card-title mb-1">{{ $u->name }}</h5>

                            {{-- DESCRIPTION instead of username --}}
                            <p class="text-muted text-center small mb-2">
                                {{ Str::limit($u->description ?? '—', 140) }}
                            </p>

                            <p class="card-text text-muted text-center mb-0">{{ $u->email }}</p>
                        </div>
                    </div>
                    <div class="card-footer border-0 pt-0">
                        <div class="d-flex flex-row justify-content-center w-100">
                            <a href="#" class="btn btn-outline-primary me-2">Permissions</a>
                            <button class="btn btn-icon btn-icon-only btn-outline-primary" type="button">
                                <i data-acorn-icon="more-horizontal"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col">
                <div class="alert alert-info mb-0">No users found.</div>
            </div>
        @endforelse
    </div>
    <!-- User Cards End -->

    <!-- Pagination -->
    @if($users->hasPages())
        <div class="d-flex justify-content-center mt-3">
            {{ $users->withQueryString()->links('pagination::bootstrap-4') }}
        </div>
    @endif
</div>
@endsection
