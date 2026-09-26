@extends('layouts.app')

@section('title','Lead Board')

@section('content')
    <div class="toolbar">
        <h2>Board Kanban</h2>
        <div class="actions">
            <a class="btn" href="{{ route('leads.index') }}">Lista</a>
            <a class="btn ghost" href="{{ route('leads.create') }}">Nuevo Lead</a>
        </div>
    </div>

    <div class="kanban-board">
        @foreach($statuses as $key => $label)
            <section class="kanban-column">
                <div class="kanban-column-header">
                    <h3>{{ $label }}</h3>
                    <span>{{ $grouped->get($key, collect())->count() }}</span>
                </div>
                <div class="kanban-column-body" data-status="{{ $key }}">
                    @forelse($grouped->get($key, collect()) as $lead)
                        <article class="kanban-card {{ $lead->status_class }}" draggable="true" data-lead-id="{{ $lead->id }}">
                            <strong>{{ $lead->name ?? 'Sin nombre' }}</strong>
                            <div class="muted">{{ $lead->email ?? $lead->phone ?? 'Sin contacto' }}</div>
                        </article>
                    @empty
                        <div class="kanban-empty">Sin leads</div>
                    @endforelse
                </div>
            </section>
        @endforeach
    </div>

    <script>
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        let dragged = null;

        document.querySelectorAll('.kanban-card').forEach(card => {
            card.addEventListener('dragstart', event => {
                dragged = event.target;
                event.dataTransfer.effectAllowed = 'move';
                event.dataTransfer.setData('text/plain', event.target.dataset.leadId);
            });
        });

        document.querySelectorAll('.kanban-column-body').forEach(column => {
            column.addEventListener('dragover', event => {
                event.preventDefault();
                column.classList.add('kanban-drop-over');
            });
            column.addEventListener('dragleave', () => {
                column.classList.remove('kanban-drop-over');
            });
            column.addEventListener('drop', event => {
                event.preventDefault();
                column.classList.remove('kanban-drop-over');
                const leadId = event.dataTransfer.getData('text/plain');
                const targetStatus = column.dataset.status;
                const card = dragged;

                if (!leadId || !card) return;
                if (card.closest('.kanban-column-body').dataset.status === targetStatus) return;

                fetch(`/leads/${leadId}/status`, {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                    },
                    body: JSON.stringify({ status: targetStatus }),
                })
                .then(response => {
                    if (!response.ok) throw new Error('Error al cambiar estado');
                    column.appendChild(card);
                })
                .catch(() => alert('No se pudo mover el lead.'));
            });
        });
    </script>
@endsection
