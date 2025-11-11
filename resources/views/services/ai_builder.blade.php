{{-- resources/views/services/ai_builder.blade.php --}}
@php
  $title = 'AI App Builder';
  $description = 'Describe any app. We’ll plan it, connect APIs, build a sandbox, and preview it live.';
@endphp

@extends('layout', ['title' => $title, 'description' => $description])

@section('content')
<div class="row g-3">
  {{-- Left --}}
  <div class="col-12 col-xl-8">
    <div class="card">
      <div class="card-body">
        <h1 class="h4 mb-1">{{ $title }}</h1>
        <div class="text-muted mb-3">
          Describe any app. We’ll plan it, connect APIs, build a sandbox, and preview it live.
        </div>

        <div id="flashArea"></div>

        <div class="mb-3">
          <label class="form-label fw-semibold">Describe your app</label>
          <textarea id="promptBox" rows="6" class="form-control"
            placeholder="Example: Multi-tenant SaaS with auth, subscriptions, admin panel, analytics, REST API"></textarea>
        </div>

        <div class="row g-3 align-items-end">
          <div class="col-md-4">
            <label class="form-label">Model</label>
            <select id="modelSel" class="form-select">
              <option value="gemini-1.5-flash">Gemini 1.5 Flash</option>
              <option value="gemini-1.5-pro">Gemini 1.5 Pro</option>
            </select>
          </div>
          <div class="col-md-8">
            <div class="d-flex gap-2">
              <button type="button" id="btnPlan"  class="btn btn-outline-primary">Plan with AI</button>
              <button type="button" id="btnStart" class="btn btn-primary">Start Build</button>
              <div class="flex-grow-1 d-flex align-items-center">
                <small id="statusLine" class="text-muted ms-2">Status: idle</small>
              </div>
            </div>
          </div>
        </div>

        <hr class="my-4">

        <h6 class="text-uppercase text-muted mb-2">Plan</h6>
        <pre id="planBox" class="bg-body-tertiary small p-3 rounded" style="min-height:120px;max-height:240px;overflow:auto;white-space:pre-wrap;"></pre>
      </div>
    </div>
  </div>

  {{-- Right --}}
  <div class="col-12 col-xl-4">
    <div class="card h-100">
      <div class="card-body d-flex flex-column">
        <div class="d-flex align-items-center justify-content-between mb-2">
          <h2 class="h6 mb-0">Live preview</h2>
          <span class="badge rounded-pill text-bg-info">Live</span>
        </div>

        <div id="previewShell" class="rounded border flex-grow-1 p-2"
             style="background:#0b1220;color:#d7e1ff;min-height:420px;">
          <div class="h-100 d-flex align-items-center justify-content-center opacity-75">
            Preview will appear after planning / building…
          </div>
        </div>

        <div class="mt-2 d-flex justify-content-end">
          <a id="openPreviewLink" href="#" class="btn btn-sm btn-outline-secondary d-none" target="_blank" rel="noopener">
            Open live preview
          </a>
        </div>
      </div>
    </div>
  </div>
</div>

<style>
  .btn-primary{background:#00c2d7!important;border-color:#00c2d7!important;color:#0b1220!important}
  .btn-outline-primary{color:#00c2d7!important;border-color:#00c2d7!important}
  .btn-outline-primary:hover{background:#00c2d7!important;color:#0b1220!important}
</style>

<script>
(function(){
  const csrf   = document.querySelector('meta[name=csrf-token]').content;
  const $ = (s)=>document.querySelector(s);

  const ta         = $('#promptBox');
  const modelSel   = $('#modelSel');
  const btnPlan    = $('#btnPlan');
  const btnStart   = $('#btnStart');
  const planBox    = $('#planBox');
  const statusLine = $('#statusLine');
  const shell      = $('#previewShell');
  const openLink   = $('#openPreviewLink');
  const flashArea  = $('#flashArea');

  let cachedPlan = null;

  function setFlash(msg, type='success'){
    flashArea.innerHTML =
      `<div class="alert alert-${type} py-2 px-3 mb-3">${msg}</div>`;
  }

  function setPreviewURL(url){
    shell.innerHTML = `<iframe src="${url}" style="border:0;width:100%;height:100%;border-radius:.5rem;background:#fff"></iframe>`;
    openLink.classList.remove('d-none');
    openLink.href = url;
  }

  btnPlan.addEventListener('click', async () => {
    const prompt = (ta.value || '').trim();
    if(prompt.length < 8){ alert('Write a bit more in the prompt.'); return; }
    statusLine.textContent = 'Status: planning…';
    setFlash('', ''); flashArea.innerHTML='';

    try{
      const res = await fetch("{{ route('ai.builder.plan') }}", {
        method: 'POST',
        headers: {'Content-Type':'application/json','X-CSRF-TOKEN': csrf},
        body: JSON.stringify({ prompt, model: modelSel.value })
      });
      if(!res.ok) throw new Error('Plan request failed');
      const j = await res.json();
      cachedPlan = j.plan || null;
      planBox.textContent = JSON.stringify(j, null, 2);
      statusLine.textContent = 'Status: plan ready';
      setFlash('Plan generated.');
    }catch(e){
      statusLine.textContent = 'Status: plan failed';
      setFlash(e.message || 'Plan failed', 'danger');
    }
  });

  btnStart.addEventListener('click', async () => {
    const prompt = (ta.value || '').trim();
    if(prompt.length < 8){ alert('Write a bit more in the prompt.'); return; }

    statusLine.textContent = 'Status: starting…';
    setFlash('', ''); flashArea.innerHTML='';

    shell.innerHTML =
      `<div class="h-100 d-flex align-items-center justify-content-center">
         <div class="spinner-border me-2" role="status" style="width:1.5rem;height:1.5rem"></div>
         <span>Preparing sandbox…</span>
       </div>`;
    openLink.classList.add('d-none'); openLink.href='#';

    try{
      const res = await fetch("{{ route('ai.builder.start') }}", {
        method: 'POST',
        headers: {'Content-Type':'application/json','X-CSRF-TOKEN': csrf},
        body: JSON.stringify({
          prompt,
          model: modelSel.value,
          plan_json: cachedPlan
        })
      });
      if(!res.ok) throw new Error('Start failed');

      const j = await res.json();
      if(!j.success) throw new Error(j.error || 'Start failed');

      // Prefer preview_url (iframe). If preview_html bhi aaye to use, but URL is safer.
      if(j.preview_url){
        setPreviewURL(j.preview_url);
      }else if(j.preview_html){
        shell.innerHTML = j.preview_html;
      }else{
        shell.innerHTML = `<div class="p-3">No preview returned by server.</div>`;
      }

      statusLine.textContent = 'Status: preview ready';
      setFlash('Build started! We’re preparing your sandbox…');
    }catch(e){
      statusLine.textContent = 'Status: start failed';
      setFlash(e.message || 'Start failed', 'danger');
      shell.innerHTML =
        `<div class="h-100 d-flex align-items-center justify-content-center opacity-75">
           Start failed.
         </div>`;
    }
  });
})();
</script>
@endsection
