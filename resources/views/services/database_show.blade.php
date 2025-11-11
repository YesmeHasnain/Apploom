@php
  $html_tag_data = [];
  $title = 'Database Schema';
  $description= 'Tables & columns (read-only)';
@endphp
@extends('layout',['html_tag_data'=>$html_tag_data, 'title'=>$title, 'description'=>$description])

@section('content')
<div class="col">
  <div class="page-title-container mb-3">
    <div class="row">
      <div class="col mb-2">
        <h1 class="mb-2 pb-0 display-4">{{ $title }}</h1>
        <div class="text-muted">Connection: <code>{{ $connection }}</code></div>
      </div>
    </div>
  </div>

  @forelse($schema as $table => $cols)
    <div class="card mb-3">
      <div class="card-body">
        <h5 class="mb-3">{{ $table }}</h5>
        <div class="table-responsive">
          <table class="table table-sm">
            <thead><tr>
              <th>Name</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th>
            </tr></thead>
            <tbody>
              @foreach($cols as $c)
                <tr>
                  <td>{{ $c['name'] }}</td>
                  <td>{{ $c['type'] }}</td>
                  <td>{{ $c['null'] }}</td>
                  <td>{{ $c['key'] }}</td>
                  <td>{{ $c['default'] }}</td>
                  <td>{{ $c['extra'] }}</td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      </div>
    </div>
  @empty
    <div class="alert alert-warning">No tables found.</div>
  @endforelse
</div>
@endsection
