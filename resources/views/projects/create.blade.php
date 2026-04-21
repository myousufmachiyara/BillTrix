@extends('layouts.app')
@section('title', isset($project) ? 'Projects | Edit' : 'Projects | New')

@section('content')
<div class="row">
  <div class="col">
    <form action="{{ isset($project) ? route('projects.update',$project->id) : route('projects.store') }}"
          method="POST" onkeydown="return event.key != 'Enter';">
      @csrf
      @if(isset($project)) @method('PUT') @endif

      @if($errors->any())
        <div class="alert alert-danger">
          <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
        </div>
      @endif

      <section class="card">
        <header class="card-header">
          <h2 class="card-title">{{ isset($project) ? 'Edit Project' : 'New Project' }}</h2>
        </header>
        <div class="card-body">
          <div class="row g-3">
            <div class="col-md-3">
              <label>Project Name <span class="text-danger">*</span></label>
              <input type="text" name="name" class="form-control" required
                     value="{{ old('name', $project->name ?? '') }}">
            </div>
            <div class="col-md-2">
              <label>Project Number</label>
              <input type="text" name="project_number" class="form-control"
                     placeholder="Auto-generated if blank"
                     value="{{ old('project_number', $project->project_number ?? '') }}">
            </div>
            <div class="col-md-3">
              <label>Customer</label>
              <select name="customer_id" class="form-control select2-js">
                <option value="">No Customer</option>
                @foreach($customers ?? [] as $c)
                <option value="{{ $c->id }}" {{ old('customer_id',$project->customer_id??'')==$c->id?'selected':'' }}>{{ $c->name }}</option>
                @endforeach
              </select>
            </div>
            <div class="col-md-2">
              <label>Status</label>
              <select name="status" class="form-control">
                @foreach(['planning','active','on_hold','completed','cancelled'] as $s)
                <option value="{{ $s }}" {{ old('status',$project->status??'planning')===$s?'selected':'' }}>{{ ucfirst(str_replace('_',' ',$s)) }}</option>
                @endforeach
              </select>
            </div>
            <div class="col-md-2">
              <label>Currency</label>
              <select name="currency_code" class="form-control">
                @foreach($currencies ?? [] as $cur)
                <option value="{{ $cur->code }}" {{ old('currency_code',$project->currency_code??$defaultCurrency??'PKR')===$cur->code?'selected':'' }}>{{ $cur->code }}</option>
                @endforeach
              </select>
            </div>
            <div class="col-md-2">
              <label>Budget</label>
              <input type="number" step="any" name="budget" class="form-control"
                     value="{{ old('budget', $project->budget ?? 0) }}">
            </div>
            <div class="col-md-2">
              <label>Start Date</label>
              <input type="date" name="start_date" class="form-control"
                     value="{{ old('start_date', $project->start_date ?? '') }}">
            </div>
            <div class="col-md-2">
              <label>End Date</label>
              <input type="date" name="end_date" class="form-control"
                     value="{{ old('end_date', $project->end_date ?? '') }}">
            </div>
            <div class="col-md-6">
              <label>Notes</label>
              <textarea name="notes" class="form-control" rows="2">{{ old('notes', $project->notes ?? '') }}</textarea>
            </div>
          </div>

          {{-- Milestones --}}
          <div class="mt-4">
            <h5 class="border-bottom pb-2">Milestones</h5>
            <div id="milestonesContainer">
              @foreach(old('milestones', isset($project) ? $project->milestones->toArray() : []) as $i => $ms)
              <div class="row g-2 mb-2 milestone-row">
                <div class="col-md-4">
                  <input type="text" name="milestones[{{ $i }}][name]" class="form-control"
                         placeholder="Milestone name" value="{{ $ms['name'] ?? '' }}" required>
                </div>
                <div class="col-md-2">
                  <input type="date" name="milestones[{{ $i }}][due_date]" class="form-control"
                         value="{{ $ms['due_date'] ?? '' }}">
                </div>
                <div class="col-md-2">
                  <input type="number" step="any" name="milestones[{{ $i }}][amount]" class="form-control"
                         placeholder="Billing amount" value="{{ $ms['amount'] ?? 0 }}">
                </div>
                <div class="col-md-2">
                  <div class="form-check mt-2">
                    <input type="checkbox" name="milestones[{{ $i }}][is_billing_milestone]" value="1"
                           class="form-check-input" {{ ($ms['is_billing_milestone'] ?? 0) ? 'checked' : '' }}>
                    <label class="form-check-label">Billing Trigger</label>
                  </div>
                </div>
                <div class="col-md-2">
                  <button type="button" class="btn btn-sm btn-danger remove-milestone">
                    <i class="fas fa-times"></i> Remove
                  </button>
                </div>
              </div>
              @endforeach
            </div>
            <button type="button" class="btn btn-outline-secondary btn-sm mt-2" id="addMilestone">
              <i class="fas fa-plus"></i> Add Milestone
            </button>
          </div>
        </div>

        <footer class="card-footer text-end">
          <a href="{{ route('projects.index') }}" class="btn btn-danger">Cancel</a>
          <button type="submit" class="btn btn-primary">
            {{ isset($project) ? 'Update Project' : 'Create Project' }}
          </button>
        </footer>
      </section>
    </form>
  </div>
</div>

<script>
var msIndex = {{ count(old('milestones', isset($project) ? $project->milestones->toArray() : [])) }};

$('#addMilestone').on('click', function () {
    var row = `
    <div class="row g-2 mb-2 milestone-row">
      <div class="col-md-4"><input type="text" name="milestones[${msIndex}][name]" class="form-control" placeholder="Milestone name" required></div>
      <div class="col-md-2"><input type="date" name="milestones[${msIndex}][due_date]" class="form-control"></div>
      <div class="col-md-2"><input type="number" step="any" name="milestones[${msIndex}][amount]" class="form-control" placeholder="Billing amount" value="0"></div>
      <div class="col-md-2">
        <div class="form-check mt-2">
          <input type="checkbox" name="milestones[${msIndex}][is_billing_milestone]" value="1" class="form-check-input">
          <label class="form-check-label">Billing Trigger</label>
        </div>
      </div>
      <div class="col-md-2"><button type="button" class="btn btn-sm btn-danger remove-milestone"><i class="fas fa-times"></i> Remove</button></div>
    </div>`;
    $('#milestonesContainer').append(row);
    msIndex++;
});

$(document).on('click', '.remove-milestone', function () {
    $(this).closest('.milestone-row').remove();
});
</script>
@endsection
