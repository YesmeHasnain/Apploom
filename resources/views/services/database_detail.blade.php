@php
  $html_tag_data = [];
  $title = 'Database: '.$db->name;
  $description = 'Tables & Columns';
@endphp
@extends('layout',['html_tag_data'=>$html_tag_data,'title'=>$title,'description'=>$description])

@section('content')
<div class="col">
  <div class="page-title-container mb-3">
    <div class="row">
      <div class="col mb-2">
        <h1 class="mb-2 pb-0 display-4">{{ $title }}</h1>
        <div class="text-muted font-heading text-small">Engine: <strong>{{ strtoupper($db->engine) }}</strong></div>
        @if($error)
          <div class="alert alert-warning mt-2 mb-0 py-2 px-3">Live inspect failed: {{ $error }}</div>
        @endif
      </div>
    </div>
  </div>

  @if(count($tables))
    <h2 class="small-title">Tables</h2>
    <div class="row g-2">
      @foreach($tables as $t)
        <div class="col-12">
          <div class="card">
            <div class="card-body">
              <h5 class="mb-2">{{ $t['name'] }}</h5>
              <div class="table-responsive">
                <table class="table table-sm mb-0">
                  <thead>
                    <tr><th>Column</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th></tr>
                  </thead>
                  <tbody>
                  @foreach($t['columns'] as $c)
                    <tr>
                      <td>{{ $c['name'] ?? ($c['column_name'] ?? '') }}</td>
                      <td>{{ $c['type'] ?? ($c['data_type'] ?? '') }}</td>
                      <td>{{ $c['null'] ?? ($c['nullable'] ?? '') }}</td>
                      <td>{{ $c['key'] ?? '' }}</td>
                      <td>{{ $c['default'] ?? '' }}</td>
                    </tr>
                  @endforeach
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>
      @endforeach
    </div>
  @elseif($metaSchema)
    <h2 class="small-title">Stored Schema (metadata)</h2>
    <div class="card">
      <div class="card-body">
        <pre class="mb-0" style="white-space:pre-wrap">{{ is_string($metaSchema) ? $metaSchema : json_encode($metaSchema, JSON_PRETTY_PRINT) }}</pre>
      </div>
    </div>
  @else
    <div class="alert alert-info">No tables to display.</div>
  @endif
</div>
@endsection
