@php
    use Illuminate\Support\Str;

    $html_tag_data = [];
    $title = 'Settings';
    $description = 'Service Provider Settings';

    $avatarUrl = $user->avatar
        ? (Str::startsWith($user->avatar, ['http://','https://','data:'])
            ? $user->avatar
            : asset('storage/'.$user->avatar))
        : '/img/profile/profile-9.webp';
@endphp

@extends('layout', ['html_tag_data'=>$html_tag_data, 'title'=>$title, 'description'=>$description])

@section('css')
    <link rel="stylesheet" href="/css/vendor/select2.min.css"/>
    <link rel="stylesheet" href="/css/vendor/select2-bootstrap4.min.css"/>
@endsection

@section('js_vendor')
    <script src="/js/vendor/select2.full.min.js"></script>
    {{-- IMPORTANT: isay hata diya gaya hai taa-ke file dialog 2 dafa na khulay --}}
    {{-- <script src="/js/vendor/singleimageupload.js"></script> --}}
@endsection

@section('js_page')
    <script src="/js/pages/account.settings.js"></script>

    {{-- Single click + preview for avatar --}}
    <script>
      (function () {
        const trigger = document.getElementById('avatarTrigger');
        const input   = document.getElementById('avatarInput');
        const img     = document.getElementById('avatarPreview');

        if (trigger && input) {
          trigger.addEventListener('click', function (e) {
            e.preventDefault();
            e.stopPropagation();
            input.click();
          }, { passive: false });
        }

        if (input && img) {
          input.addEventListener('change', function () {
            const f = this.files && this.files[0];
            if (!f) return;
            if (!/^image\//.test(f.type)) {
              alert('Please select an image file.');
              this.value = '';
              return;
            }
            const reader = new FileReader();
            reader.onload = e => { img.src = e.target.result; };
            reader.readAsDataURL(f);
          });
        }
      })();
    </script>
@endsection

@section('content')
    <div class="col">
        <!-- Title -->
        <div class="page-title-container mb-3">
            <div class="row">
                <div class="col mb-2">
                    <h1 class="mb-2 pb-0 display-4" id="title">{{ $title }}</h1>
                    <div class="text-muted font-heading text-small">
                        Let us manage the database engines for your applications so you can focus on building.
                    </div>

                    @if(session('status'))
                        <div class="alert alert-success mt-2 mb-0 py-2 px-3">{{ session('status') }}</div>
                    @endif

                    @if($errors->any())
                        <div class="alert alert-danger mt-2 mb-0 py-2 px-3">
                            <ul class="mb-0">
                                @foreach($errors->all() as $e)
                                    <li>{{ $e }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Public Info -->
        <h2 class="small-title">Public Info</h2>
        <div class="card mb-5">
            <div class="card-body">
                <form class="d-flex flex-column mb-4"
                      method="POST"
                      action="{{ route('account.settings.update') }}"
                      enctype="multipart/form-data">
                    @csrf

                    <div class="mb-3 mx-auto position-relative" id="avatarUpload">
                        <img
                            src="{{ $avatarUrl }}"
                            alt="avatar"
                            class="rounded-xl border border-separator-light border-4 sw-12 sh-12"
                            id="avatarPreview"
                            style="object-fit: cover;"
                        />
                        <button class="btn btn-sm btn-icon btn-icon-only btn-separator-light position-absolute rounded-xl e-0 b-0"
                                type="button"
                                id="avatarTrigger"
                                aria-label="Upload"
                                title="Upload">
                            <i data-acorn-icon="upload"></i>
                        </button>
                        {{-- NOTE: 'file-upload' class hata di gayi hai taa-ke vendor plugin attach na ho --}}
                        <input id="avatarInput" name="avatar" class="d-none" type="file" accept="image/*" />
                        @error('avatar')
                            <div class="text-danger mt-2 small">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3 filled w-100 d-flex flex-column">
                        <i data-acorn-icon="user"></i>
                        <input class="form-control" name="name" placeholder="Name" value="{{ old('name', $user->name) }}" />
                    </div>

                    <div class="mb-3 filled w-100 d-flex flex-column">
                        <i data-acorn-icon="tag"></i>
                        <input class="form-control" name="username" placeholder="User Name" value="{{ old('username', $defaults['username']) }}" />
                    </div>

                    <div class="mb-3 filled w-100 d-flex flex-column">
                        <i data-acorn-icon="email"></i>
                        <input class="form-control" placeholder="Email" value="{{ $user->email }}" disabled />
                    </div>

                    <div class="mb-3 filled w-100 d-flex flex-column">
                        <i data-acorn-icon="phone"></i>
                        <input class="form-control" name="phone" placeholder="Phone" value="{{ old('phone', $defaults['phone']) }}" />
                    </div>

                    <div class="filled mb-0 w-100">
                        <i data-acorn-icon="gender"></i>
                        <select class="select-single-no-search-filled" name="gender" data-placeholder="Gender">
                            <option label="&nbsp;"></option>
                            <option value="Male"   {{ old('gender', $defaults['gender'])=='Male'   ? 'selected' : '' }}>Male</option>
                            <option value="Female" {{ old('gender', $defaults['gender'])=='Female' ? 'selected' : '' }}>Female</option>
                            <option value="Other"  {{ old('gender', $defaults['gender'])=='Other'  ? 'selected' : '' }}>Other</option>
                        </select>
                    </div>

                    <div class="mt-3">
                        <button class="btn btn-primary">Update</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Email Settings -->
        <h2 class="small-title">Email Settings</h2>
        <div class="card mb-5">
            <div class="card-body">
                <form class="mb-4" method="POST" action="{{ route('account.settings.update') }}">
                    @csrf

                    <div class="mb-3 filled custom-control-container">
                        <i data-acorn-icon="shield"></i>
                        <div class="form-check form-switch">
                            <input type="checkbox" class="form-check-input" id="securityCheck" name="notify_security" {{ old('notify_security', $defaults['notify_security']) ? 'checked' : '' }} />
                            <label class="form-check-label" for="securityCheck">Security Warnings</label>
                        </div>
                    </div>

                    <div class="mb-3 filled custom-control-container">
                        <i data-acorn-icon="money"></i>
                        <div class="form-check form-switch">
                            <input type="checkbox" class="form-check-input" id="budgetCheck" name="notify_budget" {{ old('notify_budget', $defaults['notify_budget']) ? 'checked' : '' }} />
                            <label class="form-check-label" for="budgetCheck">Over Budget</label>
                        </div>
                    </div>

                    <div class="mb-3 filled custom-control-container">
                        <i data-acorn-icon="server"></i>
                        <div class="form-check form-switch">
                            <input type="checkbox" class="form-check-input" id="quotaCheck" name="notify_quota" {{ old('notify_quota', $defaults['notify_quota']) ? 'checked' : '' }} />
                            <label class="form-check-label" for="quotaCheck">Quota Warnings</label>
                        </div>
                    </div>

                    <div class="mb-3 filled custom-control-container">
                        <i data-acorn-icon="bell"></i>
                        <div class="form-check form-switch">
                            <input type="checkbox" class="form-check-input" id="generalCheck" name="notify_general" {{ old('notify_general', $defaults['notify_general']) ? 'checked' : '' }} />
                            <label class="form-check-label" for="generalCheck">General Notifications</label>
                        </div>
                    </div>

                    <div class="mb-3 filled custom-control-container">
                        <i data-acorn-icon="news"></i>
                        <div class="form-check form-switch">
                            <input type="checkbox" class="form-check-input" id="newsletterCheck" name="notify_newsletter" {{ old('notify_newsletter', $defaults['notify_newsletter']) ? 'checked' : '' }} />
                            <label class="form-check-label" for="newsletterCheck">Monthly Newsletter</label>
                        </div>
                    </div>

                    <div><button class="btn btn-primary">Update</button></div>
                </form>
            </div>
        </div>

        <!-- Language -->
        <h2 class="small-title">Language Settings</h2>
        <div class="card mb-5">
            <div class="card-body">
                <form class="mb-4" method="POST" action="{{ route('account.settings.update') }}">
                    @csrf
                    <div class="filled mb-0 w-100">
                        <i data-acorn-icon="web"></i>
                        <select class="select-single-no-search-filled" name="language" data-placeholder="Language">
                            <option label="&nbsp;"></option>
                            <option value="English" {{ old('language', $defaults['language'])=='English' ? 'selected' : '' }}>English</option>
                            <option value="Spanish" {{ old('language', $defaults['language'])=='Spanish' ? 'selected' : '' }}>Spanish</option>
                        </select>
                    </div>
                    <div class="mt-3"><button class="btn btn-primary">Update</button></div>
                </form>
            </div>
        </div>
    </div>
@endsection
