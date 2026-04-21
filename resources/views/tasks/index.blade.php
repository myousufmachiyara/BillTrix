@extends('layouts.app')
@section('title', 'Tasks')

@section('content')
<div class="row">
  <div class="col">
    <section class="card">
      @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif

      <header class="card-header">
        <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:.5rem;">
          <h2 class="card-title mb-0">{{ request()->routeIs('tasks.my') ? 'My Tasks' : 'All Tasks' }}</h2>
          <div class="d-flex gap-2">
            {{-- View toggle --}}
            <div class="btn-group btn-group-sm">
              <button type="button" class="btn btn-outline-secondary" id="listViewBtn" title="List view">
                <i class="fas fa-list"></i>
              </button>
              <button type="button" class="btn btn-outline-secondary" id="kanbanViewBtn" title="Kanban view">
                <i class="fas fa-columns"></i>
              </button>
            </div>
            @can('tasks.create')
            <button type="button" class="modal-with-form btn btn-primary btn-sm" href="#createTaskModal">
              <i class="fas fa-plus"></i> New Task
            </button>
            @endcan
          </div>
        </div>
      </header>

      {{-- Filters --}}
      <div class="card-body border-bottom pb-3">
        <form method="GET" action="{{ request()->routeIs('tasks.my') ? route('tasks.my') : route('tasks.index') }}"
              class="row g-2 align-items-end">
          <div class="col-md-3">
            <input type="text" name="search" class="form-control" placeholder="Task title…" value="{{ request('search') }}">
          </div>
          <div class="col-md-2">
            <select name="priority" class="form-control">
              <option value="">All Priority</option>
              @foreach(['low','medium','high','urgent'] as $p)
              <option value="{{ $p }}" {{ request('priority')==$p?'selected':'' }}>{{ ucfirst($p) }}</option>
              @endforeach
            </select>
          </div>
          <div class="col-md-2">
            <select name="status" class="form-control">
              <option value="">All Status</option>
              @foreach(['todo','in_progress','review','done','cancelled'] as $s)
              <option value="{{ $s }}" {{ request('status')==$s?'selected':'' }}>{{ ucfirst(str_replace('_',' ',$s)) }}</option>
              @endforeach
            </select>
          </div>
          @if(!request()->routeIs('tasks.my'))
          <div class="col-md-2">
            <select name="assigned_to" class="form-control select2">
              <option value="">Anyone</option>
              @foreach($users ?? [] as $u)
              <option value="{{ $u->id }}" {{ request('assigned_to')==$u->id?'selected':'' }}>{{ $u->name }}</option>
              @endforeach
            </select>
          </div>
          @endif
          <div class="col-md-3 d-flex gap-1">
            <button type="submit" class="btn btn-primary flex-fill">Filter</button>
            <a href="{{ request()->routeIs('tasks.my') ? route('tasks.my') : route('tasks.index') }}" class="btn btn-secondary">Clear</a>
          </div>
        </form>
      </div>

      {{-- List View --}}
      <div class="card-body" id="listView">
        <div class="table-scroll">
          <table class="table table-bordered table-striped mb-0" id="cust-datatable-default">
            <thead>
              <tr>
                <th>S.No</th><th>Title</th><th>Assigned To</th><th>Priority</th>
                <th>Due Date</th><th>Status</th><th>Action</th>
              </tr>
            </thead>
            <tbody>
              @forelse($tasks ?? [] as $i => $task)
              <tr class="{{ $task->is_overdue ? 'row-overdue' : '' }}">
                <td>{{ $i + 1 }}</td>
                <td>
                  <strong>{{ $task->title }}</strong>
                  @if($task->ref_type)
                    <br><small class="text-muted">{{ class_basename($task->ref_type) }} #{{ $task->ref_id }}</small>
                  @endif
                </td>
                <td>{{ $task->assignedTo->name ?? '-' }}</td>
                <td>
                  <span class="badge
                    @if($task->priority==='urgent') bg-danger
                    @elseif($task->priority==='high') bg-warning text-dark
                    @elseif($task->priority==='medium') bg-primary
                    @else bg-secondary @endif">
                    {{ ucfirst($task->priority) }}
                  </span>
                </td>
                <td class="{{ $task->is_overdue ? 'text-danger fw-bold' : '' }}">
                  {{ $task->due_date ?? '-' }}
                  @if($task->is_overdue)<i class="fas fa-exclamation-triangle text-danger ms-1"></i>@endif
                </td>
                <td>
                  <select class="form-select form-select-sm task-status-change"
                          data-task-id="{{ $task->id }}" style="min-width:110px">
                    @foreach(['todo','in_progress','review','done','cancelled'] as $s)
                    <option value="{{ $s }}" {{ $task->status===$s?'selected':'' }}>{{ ucfirst(str_replace('_',' ',$s)) }}</option>
                    @endforeach
                  </select>
                </td>
                <td>
                  <a href="{{ route('tasks.show', $task->id) }}" class="text-info"><i class="fa fa-eye"></i></a>
                  <a href="{{ route('tasks.edit', $task->id) }}" class="text-warning"><i class="fa fa-edit"></i></a>
                </td>
              </tr>
              @empty
              <tr><td colspan="7" class="text-center text-muted py-4">No tasks found.</td></tr>
              @endforelse
            </tbody>
          </table>
        </div>
        @if(method_exists($tasks ?? new \stdClass, 'links'))
          <div class="mt-3">{{ $tasks->appends(request()->query())->links() }}</div>
        @endif
      </div>

      {{-- Kanban View --}}
      <div class="card-body" id="kanbanView" style="display:none">
        <div class="row g-3">
          @foreach(['todo'=>'To Do','in_progress'=>'In Progress','review'=>'Review','done'=>'Done'] as $col => $colLabel)
          <div class="col-md-3">
            <div class="card border-0 bg-light h-100">
              <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
                <strong>{{ $colLabel }}</strong>
                <span class="badge bg-secondary">
                  {{ count(($tasks ?? collect())->where('status', $col)) }}
                </span>
              </div>
              <div class="card-body p-2" style="min-height:200px">
                @foreach(($tasks ?? collect())->where('status', $col) as $task)
                <div class="card mb-2 shadow-sm" data-task-id="{{ $task->id }}">
                  <div class="card-body p-2">
                    <div class="d-flex justify-content-between align-items-start mb-1">
                      <span class="small fw-semibold">{{ Str::limit($task->title, 40) }}</span>
                      <span class="badge ms-1
                        @if($task->priority==='urgent') bg-danger
                        @elseif($task->priority==='high') bg-warning text-dark
                        @elseif($task->priority==='medium') bg-primary
                        @else bg-secondary @endif" style="font-size:.65rem">
                        {{ ucfirst($task->priority) }}
                      </span>
                    </div>
                    <div class="text-muted" style="font-size:.75rem">
                      <i class="fas fa-user me-1"></i>{{ $task->assignedTo->name ?? '-' }}
                      @if($task->due_date)
                      &nbsp;|&nbsp;<i class="fas fa-calendar-day me-1 {{ $task->is_overdue?'text-danger':'' }}"></i>{{ $task->due_date }}
                      @endif
                    </div>
                  </div>
                </div>
                @endforeach
              </div>
            </div>
          </div>
          @endforeach
        </div>
      </div>

    </section>
  </div>
</div>

{{-- Create Task Modal --}}
<div id="createTaskModal" class="modal-block modal-block-primary mfp-hide">
  <section class="card">
    <form action="{{ route('tasks.store') }}" method="POST" onkeydown="return event.key != 'Enter';">
      @csrf
      <header class="card-header"><h2 class="card-title">New Task</h2></header>
      <div class="card-body">
        <div class="row g-3">
          <div class="col-12">
            <label>Title <span class="text-danger">*</span></label>
            <input type="text" name="title" class="form-control" required>
          </div>
          <div class="col-12">
            <label>Description</label>
            <textarea name="description" class="form-control" rows="2"></textarea>
          </div>
          <div class="col-md-6">
            <label>Assign To <span class="text-danger">*</span></label>
            <select name="assigned_to" class="form-control select2-js" required>
              <option value="">Select User</option>
              @foreach($users ?? [] as $u)
              <option value="{{ $u->id }}">{{ $u->name }}</option>
              @endforeach
            </select>
          </div>
          <div class="col-md-3">
            <label>Priority</label>
            <select name="priority" class="form-control">
              <option value="medium">Medium</option>
              <option value="low">Low</option>
              <option value="high">High</option>
              <option value="urgent">Urgent</option>
            </select>
          </div>
          <div class="col-md-3">
            <label>Due Date</label>
            <input type="date" name="due_date" class="form-control">
          </div>
        </div>
      </div>
      <footer class="card-footer text-end">
        <button type="button" class="btn btn-secondary modal-dismiss">Cancel</button>
        <button type="submit" class="btn btn-primary ms-2">Create Task</button>
      </footer>
    </form>
  </section>
</div>

<script>
$(function () {
    // Restore view preference
    var saved = localStorage.getItem('billtrix_tasks_view') || 'list';
    if (saved === 'kanban') {
        $('#kanbanView').show(); $('#listView').hide();
        $('#kanbanViewBtn').addClass('active'); $('#listViewBtn').removeClass('active');
    } else {
        $('#listView').show(); $('#kanbanView').hide();
        $('#listViewBtn').addClass('active'); $('#kanbanViewBtn').removeClass('active');
    }

    $('#listViewBtn').on('click', function () {
        $('#listView').show(); $('#kanbanView').hide();
        $(this).addClass('active'); $('#kanbanViewBtn').removeClass('active');
        localStorage.setItem('billtrix_tasks_view', 'list');
    });
    $('#kanbanViewBtn').on('click', function () {
        $('#kanbanView').show(); $('#listView').hide();
        $(this).addClass('active'); $('#listViewBtn').removeClass('active');
        localStorage.setItem('billtrix_tasks_view', 'kanban');
    });

    // Quick status change
    $(document).on('change', '.task-status-change', function () {
        var taskId = $(this).data('task-id');
        var status = $(this).val();
        $.post('/tasks/' + taskId + '/status', { status: status })
            .fail(function () { alert('Failed to update.'); location.reload(); });
    });
});
</script>
@endsection
