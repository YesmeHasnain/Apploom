<div class="modal fade" id="databasePickerModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Choose a database</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div id="db-list" class="list-group mb-3"></div>
        <a href="{{ url('/Services/DatabaseAdd') }}" class="btn btn-outline-primary w-100">Create a new database</a>
      </div>
    </div>
  </div>
</div>

<script>
  // Call when you want to open picker
  async function openDatabasePicker(onPick){
    const modalEl = document.getElementById('databasePickerModal');
    const modal = new bootstrap.Modal(modalEl);
    const list = document.getElementById('db-list');
    list.innerHTML = 'Loading...';

    const res = await fetch(`{{ route('services.database.list') }}`);
    const j = await res.json();
    list.innerHTML = '';

    (j.items || []).forEach(db => {
      const a = document.createElement('button');
      a.className = 'list-group-item list-group-item-action d-flex justify-content-between align-items-center';
      a.innerHTML = `<span>${db.name} <span class="text-muted small">(${db.engine})</span></span>
                     <span class="badge bg-light text-body">ID: ${db.id}</span>`;
      a.onclick = () => { modal.hide(); onPick && onPick(db); };
      list.appendChild(a);
    });

    modal.show();
  }
</script>
