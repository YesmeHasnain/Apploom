@php
    $html_tag_data = [];
    $title = 'New Database';
    $description= 'Spin up a database, generate schema with AI, and keep a query scratchpad.';
@endphp

@extends('layout',['html_tag_data'=>$html_tag_data, 'title'=>$title, 'description'=>$description])

@section('css')
  <link rel="stylesheet" href="/css/vendor/select2.min.css"/>
  <link rel="stylesheet" href="/css/vendor/select2-bootstrap4.min.css"/>
  <style>
    .codebox{font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, "Liberation Mono", monospace;}
    .ai-badge{border:1px dashed var(--bs-primary); color:var(--bs-primary); border-radius:999px; padding:.2rem .5rem; font-size:.75rem;}
    .custom-card .card:hover{box-shadow:0 12px 30px rgba(0,0,0,.06)}
  </style>
@endsection

@section('js_vendor')
  <script src="/js/vendor/select2.full.min.js"></script>
@endsection

@section('js_page')
  <script>
    document.addEventListener('DOMContentLoaded', () => {
      // select2
      $('.select-single-no-search-filled').select2({minimumResultsForSearch: Infinity, theme: 'bootstrap4'});

      // Engine quick-radio -> selects engine in <select>
      document.querySelectorAll('input[name="databaseType"]').forEach(r => {
        r.addEventListener('change', e => {
          const v = e.target.value; // same keys as select options
          const sel = document.querySelector('select[name="engine"]');
          if(sel) { sel.value = v; $(sel).trigger('change'); }
        });
      });

      // AI Suggest Schema
      document.getElementById('btnSuggest').addEventListener('click', async () => {
        const prompt = document.getElementById('aiPrompt').value.trim();
        const engine = document.querySelector('select[name="engine"]').value || 'mysql';
        if(!prompt) { alert('Write what data your app needs.'); return; }

        const btn = document.getElementById('btnSuggest');
        btn.disabled = true; btn.innerText = 'Thinking…';

        try{
          const res = await fetch(`{{ route('services.database.suggest') }}`, {
            method: 'POST',
            headers: {'Content-Type':'application/json','X-CSRF-TOKEN':'{{ csrf_token() }}'},
            body: JSON.stringify({ prompt, engine })
          });
          const j = await res.json();
          if(!j.success){ throw new Error(j.message || 'Failed'); }

          // Put into Query tab
          document.getElementById('queryEditor').value = j.sql || j.ddl || j.json || '';
          document.getElementById('schemaPreview').textContent = j.summary || '';
          // Also stash into hidden input (so it goes with Create)
          document.getElementById('schema_sql').value = j.sql || j.ddl || j.json || '';

        }catch(err){
          alert('Could not generate schema. '+err.message);
        }finally{
          btn.disabled = false; btn.innerText = 'Suggest schema';
        }
      });

      // Apply to Query (when user edits AI area and wants to push)
      document.getElementById('btnApplyToQuery').addEventListener('click', () => {
        const txt = document.getElementById('aiPrompt').value.trim();
        if(txt){ document.getElementById('queryEditor').value = txt; }
      });
    });
  </script>
@endsection

@section('content')
<div class="col">
  <!-- Title -->
  <div class="page-title-container mb-3">
    <div class="row">
      <div class="col mb-2">
        <h1 class="mb-2 pb-0 display-4">{{ $title }}</h1>
        <div class="text-muted font-heading text-small">
          Let us manage the database engines for your applications so you can focus on building.
        </div>
      </div>
    </div>
  </div>

  <form method="POST" action="{{ route('services.database.store') }}">
    @csrf
    <input type="hidden" name="schema_sql" id="schema_sql" value="">

    <div class="row">
      <!-- Authentication -->
      <div class="col-12 col-xl-7 mb-5">
        <h2 class="small-title">Authentication</h2>
        <div class="card">
          <div class="card-body">
              <div class="mb-3 filled">
                <i data-acorn-icon="tag"></i>
                <input class="form-control" name="name" placeholder="Database Name" required />
              </div>

              <div class="row g-2">
                <div class="col-md-6">
                  <div class="mb-3 filled">
                    <i data-acorn-icon="user"></i>
                    <input class="form-control" name="username" placeholder="Username" />
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="mb-3 filled">
                    <i data-acorn-icon="lock-on"></i>
                    <input class="form-control" type="password" name="password" placeholder="Password" />
                  </div>
                </div>
              </div>

              <div class="row g-2">
                <div class="col-md-6">
                  <div class="mb-3 filled">
                    <i data-acorn-icon="server"></i>
                    <input class="form-control" name="host" placeholder="Host (e.g. localhost)" />
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="mb-3 filled">
                    <i data-acorn-icon="chevron-right"></i>
                    <input class="form-control" name="port" placeholder="Port (e.g. 3306)" />
                  </div>
                </div>
              </div>

              <div class="filled w-100 mb-0">
                <i data-acorn-icon="database"></i>
                <select class="select-single-no-search-filled" name="engine" data-placeholder="Engine">
                  <option label="&nbsp;"></option>
                  <option value="mysql">MySQL / MariaDB</option>
                  <option value="postgres">PostgreSQL</option>
                  <option value="mongodb">MongoDB</option>
                  <option value="redis">Redis</option>
                  <option value="neo4j">Neo4j</option>
                  <option value="riak">Riak</option>
                </select>
              </div>
          </div>
        </div>
      </div>

      <!-- AI schema + Query playground -->
      <div class="col-12 col-xl-5 mb-5">
        <h2 class="small-title">AI schema <span class="ai-badge">optional</span></h2>
        <div class="card mb-3">
          <div class="card-body">
            <div class="text-muted small mb-2">
              Describe your data & relationships. Example: “E-commerce with users, products, orders, order_items, payments”.
            </div>
            <textarea id="aiPrompt" rows="5" class="form-control codebox" placeholder="Describe tables (or JSON collections)…"></textarea>
            <div class="d-flex gap-2 mt-2">
              <button type="button" id="btnSuggest" class="btn btn-outline-primary">Suggest schema</button>
              <button type="button" id="btnApplyToQuery" class="btn btn-outline-secondary">Apply to Query</button>
            </div>
            <pre id="schemaPreview" class="text-muted small mt-3 mb-0"></pre>
          </div>
        </div>

        <h2 class="small-title mb-2">Query playground</h2>
        <div class="card">
          <div class="card-body">
            <textarea id="queryEditor" rows="10" class="form-control codebox" placeholder="Generated CREATE TABLEs (SQL) or JSON schema will appear here…"></textarea>
          </div>
        </div>
      </div>

      <!-- Type cards -->
      <div class="col-12 mb-5">
        <h2 class="small-title">Type</h2>
        <div class="row g-2">
          @php
            $types = [
              ['value'=>'mongodb','icon'=>'database','title'=>'MongoDB','ver'=>'6.x'],
              ['value'=>'redis','icon'=>'screen','title'=>'Redis','ver'=>'7.x'],
              ['value'=>'neo4j','icon'=>'code','title'=>'Neo4j','ver'=>'5.x'],
              ['value'=>'riak','icon'=>'pentagon','title'=>'Riak','ver'=>'2.x'],
            ];
          @endphp
          @foreach($types as $t)
          <div class="col-12 col-sm-6 col-lg-3">
            <label class="form-check custom-card w-100 position-relative p-0 m-0">
              <input type="radio" value="{{ $t['value'] }}" class="form-check-input position-absolute e-2 t-2 z-index-1" name="databaseType" />
              <span class="card form-check-label w-100 text-center">
                <span class="card-body">
                  <i data-acorn-icon="{{ $t['icon'] }}" class="text-primary"></i>
                  <span class="heading mt-3 text-body text-primary d-block">{{ $t['title'] }}</span>
                  <span class="text-extra-small fw-medium text-muted d-block">{{ $t['ver'] }}</span>
                </span>
              </span>
            </label>
          </div>
          @endforeach
        </div>
      </div>

      <!-- Plan -->
      <div class="col-12 mb-5">
        <h2 class="small-title">Plan</h2>
        <div class="row g-2">
          @php
            $plans = [
              ['title'=>'Developer','price'=>'$ 3.50'],
              ['title'=>'Team','price'=>'$ 7.25'],
              ['title'=>'Company','price'=>'$ 12.75'],
            ];
          @endphp
          @foreach($plans as $p)
          <div class="col-12 col-lg-4">
            <label class="form-check custom-card w-100 position-relative p-0 m-0">
              <input type="radio" class="form-check-input position-absolute e-2 t-2 z-index-1" name="databasePlan" />
              <span class="card form-check-label w-100 text-center">
                <span class="card-body">
                  <span class="heading text-body text-primary">{{ $p['title'] }}</span>
                  <span class="display-4 d-block">{{ $p['price'] }}</span>
                  <span class="text-small text-muted d-block">User/Month</span>
                </span>
              </span>
            </label>
          </div>
          @endforeach
        </div>
      </div>

      <!-- Create Button -->
      <div class="col-12 text-center mt-3">
        <div class="shadow d-inline-block">
          <button type="submit" class="btn btn-primary btn-lg btn-icon btn-icon-end">
            <span>Create</span>
            <i data-acorn-icon="chevron-right"></i>
          </button>
        </div>
      </div>
    </div>
  </form>
</div>
@endsection
