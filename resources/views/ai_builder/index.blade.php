@php
  $html_tag_data = [];
  $title = 'AI App Builder';
  $description = 'Describe any app (mobile, web, backend). We’ll scaffold UI, API, and files for you.';
@endphp

@extends('layout', ['html_tag_data'=>$html_tag_data, 'title'=>$title, 'description'=>$description])

@section('content')
<div class="col">
  <div class="page-title-container mb-3">
    <div class="row">
      <div class="col mb-2">
        <h1 class="mb-2 pb-0 display-4">{{ $title }}</h1>
        <div class="text-muted font-heading text-small">
          Describe <strong>any kind of app</strong>. We’ll scaffold and preview it live.
        </div>
      </div>
    </div>
  </div>

  <div class="row g-3">
    <!-- LEFT: form -->
    <div class="col-12 col-xl-6">
      <div class="card"><div class="card-body">
        <form id="aiBuilderForm" onsubmit="return false;">
          @csrf
          <div class="mb-3">
            <label class="form-label fw-semibold">Describe your app</label>
            <textarea name="prompt" rows="6" class="form-control"
              placeholder="Example: E-commerce app with products, cart, checkout">{{ old('prompt') }}</textarea>
          </div>

          <div class="row g-3">
            <div class="col-md-4">
              <label class="form-label">Model</label>
              <select name="model" class="form-select">
                <option value="gemini-1.5-flash">Gemini 1.5 Flash</option>
                <option value="gemini-1.5-pro">Gemini 1.5 Pro</option>
              </select>
            </div>
            <div class="col-md-4">
              <label class="form-label">Visibility</label>
              <select name="visibility" class="form-select">
                <option value="private" selected>Private</option>
                <option value="public">Public</option>
              </select>
            </div>
            <div class="col-md-4">
              <label class="form-label d-block">Targets</label>
              <label class="form-check me-3">
                <input class="form-check-input" type="checkbox" name="targets[]" value="web" checked>
                <span class="form-check-label">Web</span>
              </label>
              <label class="form-check me-3">
                <input class="form-check-input" type="checkbox" name="targets[]" value="backend" checked>
                <span class="form-check-label">Backend</span>
              </label>
              <label class="form-check">
                <input class="form-check-input" type="checkbox" name="targets[]" value="react-native" checked>
                <span class="form-check-label">React-Native</span>
              </label>
            </div>
          </div>

          <div class="mt-4 d-flex align-items-center gap-3">
            <button id="btnPlan"  type="button" class="btn btn-outline-primary">Plan with AI</button>
            <button id="btnStart" type="button" class="btn btn-primary">Start Build</button>
          </div>
        </form>

        <div class="mt-4">
          <div class="progress" style="height:10px;">
            <div id="bar" class="progress-bar" style="width:0%"></div>
          </div>
          <div class="small text-muted mt-2">
            <span id="statusText">Idle</span> — <span id="pctText">0%</span>
          </div>
        </div>
      </div></div>
    </div>

    <!-- RIGHT: live preview & logs -->
    <div class="col-12 col-xl-6">
      <div class="card"><div class="card-body">
        <h5 class="mb-3">Live preview</h5>
        <iframe id="previewFrame" src="about:blank" style="width:100%;height:420px;border:1px solid #e5e7eb;border-radius:12px;"></iframe>
        <div class="mt-3">
          <h6 class="mb-2">Live logs</h6>
          <pre id="log" class="bg-light p-3 rounded border small" style="height:140px;overflow:auto;margin:0"></pre>
        </div>
      </div></div>
    </div>
  </div>
</div>
@endsection

@section('js_page')
<script>
(function (){
  const URL_START  = "{{ route('ai.builder.start') }}";
  const URL_PLAN   = "{{ route('ai.builder.plan') }}";
  const URL_STREAM = "{{ url('/AIBuilder/stream') }}";
  const URL_PLIVE  = "{{ url('/AIBuilder/preview-live') }}";

  const bar  = document.getElementById('bar');
  const pct  = document.getElementById('pctText');
  const stat = document.getElementById('statusText');
  const log  = document.getElementById('log');
  const ifr  = document.getElementById('previewFrame');

  function setP(p,msg){
    bar.style.width = p + '%';
    pct.textContent = p + '%';
    if (msg) stat.textContent = msg;
  }
  function appendLog(x){
    log.textContent += x + "\\n";
    log.scrollTop = log.scrollHeight;
  }

  async function plan(){
    const prompt = document.querySelector('textarea[name=prompt]').value.trim();
    if (!prompt) return alert('Please enter a prompt.');
    const res = await fetch(URL_PLAN, {
      method:'POST',
      headers:{'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Content-Type':'application/json'},
      body: JSON.stringify({prompt})
    });
    const j = await res.json();
    if (!j.ok) return alert('Plan failed');
    alert('Plan created');
  }

  async function start(){
    const fd = new FormData(document.getElementById('aiBuilderForm'));
    const payload = Object.fromEntries(fd.entries()); // simple
    payload.targets = fd.getAll('targets[]');

    setP(1,'Starting…'); ifr.src='about:blank'; log.textContent='';

    const res = await fetch(URL_START, {
      method:'POST',
      headers:{'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Content-Type':'application/json'},
      body: JSON.stringify(payload)
    });
    const j = await res.json();
    if(!j.ok) return alert('Start failed');

    stream(j.build_id);
  }

  function stream(id){
    // SSE
    const es = new EventSource(`${URL_STREAM}/${id}`);

    es.addEventListener('progress', (e)=>{
      const d = JSON.parse(e.data);
      setP(d.progress, d.message || 'Working…');
      appendLog(`${d.progress}% — ${d.message||''}`);
    });

    es.addEventListener('preview', (e)=>{
      const d = JSON.parse(e.data);
      ifr.src = d.preview_url; // attach live preview as soon as available
      appendLog('Preview ready.');
    });

    es.addEventListener('error', (e)=>{
      appendLog('Stream error.'); es.close();
    });
  }

  document.getElementById('btnPlan').addEventListener('click', plan);
  document.getElementById('btnStart').addEventListener('click', start);
})();
</script>
@endsection
