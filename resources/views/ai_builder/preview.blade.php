@php
  $html_tag_data = [];
  $title = 'Preview';
  $description = 'Sandbox preview';
@endphp

@extends('layout', ['html_tag_data'=>$html_tag_data, 'title'=>$title, 'description'=>$description])

@section('content')
<div class="col">
  <div class="page-title-container mb-3">
    <h1 class="display-6">Preview</h1>
    <div class="text-muted">Build ID: {{ $build->id }}</div>
  </div>

  <div class="card">
    <div class="card-body">
      <p class="mb-3">This is a sandbox preview placeholder. (Hook your real preview here.)</p>
      <pre class="small bg-light p-3 rounded border">{{ json_encode($build->only(['status','progress','artifacts','error']), JSON_PRETTY_PRINT) }}</pre>
    </div>
  </div>

  <a class="btn btn-outline-primary mt-3" href="{{ route('ai.builder') }}">Back to Builder</a>
</div>
@endsection
