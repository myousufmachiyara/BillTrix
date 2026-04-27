@extends('layouts.app')
@section('title', isset($role) ? 'Edit Role: '.$role->name : 'New Role')
@section('content')

<form action="{{ isset($role) ? route('roles.update', $role) : route('roles.store') }}" method="POST">
@csrf
@if(isset($role)) @method('PUT') @endif

@php
$actions = ['index'=>'View','create'=>'Create','edit'=>'Edit','delete'=>'Delete','print'=>'Print'];

// Group non-report permissions by module
$modulePerms = [];
foreach($permissions as $p) {
    $parts = explode('.', $p->name);
    if(count($parts) === 2 && $parts[0] !== 'reports') {
        $modulePerms[$parts[0]][$parts[1]] = $p->name;
    }
}
ksort($modulePerms);

// Report permissions
$reportPerms = $permissions->filter(fn($p) => str_starts_with($p->name, 'reports.'));

// Currently assigned
$assigned = isset($role) ? $role->permissions->pluck('name')->toArray() : [];
@endphp

<div class="row">

    {{-- ── Left: Role Name ── --}}
    <div class="col-12">
        <section class="card card-featured card-featured-primary mb-3">
            <header class="card-header d-flex justify-content-between align-items-center">
                <h2 class="card-title mb-0">{{ isset($role) ? 'Edit Role: '.$role->name : 'New Role' }}</h2>
                <div>
                    <a href="{{ route('roles.index') }}" class="btn btn-default btn-sm me-1">
                        <i class="fas fa-arrow-left me-1"></i> Back
                    </a>
                    <button type="submit" class="btn btn-primary btn-sm">
                        <i class="fas fa-save me-1"></i>
                        {{ isset($role) ? 'Update Role' : 'Create Role' }}
                    </button>
                </div>
            </header>
            <div class="card-body">
                @if(session('success'))
                <div class="alert alert-success alert-dismissible">
                    <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
                    {{ session('success') }}
                </div>
                @endif
                @if($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
                </div>
                @endif

                <div class="col-md-3">
                    <div class="form-group">
                        <label class="control-label">Role Name <span class="required">*</span></label>
                        <input type="text" name="name" class="form-control" required
                               value="{{ old('name', $role->name ?? '') }}"
                               placeholder="e.g. Sales Manager">
                    </div>
                </div>
            </div>
        </section>
    </div>

    {{-- ── Module Permissions ── --}}
    <div class="col-12">
        <section class="card mb-3">
            <header class="card-header d-flex justify-content-between align-items-center">
                <h2 class="card-title mb-0">Module Permissions</h2>
                <div class="checkbox-custom checkbox-primary">
                    <input type="checkbox" id="masterCheckAll">
                    <label for="masterCheckAll" class="text-muted" style="font-size:12px;">Select All</label>
                </div>
            </header>
            <div class="card-body p-0" style="max-height:480px;overflow-y:auto;">
                <table class="table table-bordered mb-0" style="font-size:13px;">
                    <thead style="position:sticky;top:0;z-index:2;">
                        <tr class="bg-primary text-white">
                            <th style="min-width:180px;">Module</th>
                            @foreach($actions as $ak => $al)
                            <th class="text-center" width="100">
                                <div class="checkbox-custom checkbox-primary">
                                    <input type="checkbox" class="col-check" data-action="{{ $ak }}" id="col_{{ $ak }}">
                                    <label for="col_{{ $ak }}" class="text-white">{{ $al }}</label>
                                </div>
                            </th>
                            @endforeach
                            <th class="text-center" width="80">
                                <div class="checkbox-custom checkbox-primary">
                                    <input type="checkbox" id="checkAllModules">
                                    <label for="checkAllModules" class="text-white">All</label>
                                </div>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                    @foreach($modulePerms as $module => $perms)
                    <tr>
                        <td class="align-middle">
                            <strong>{{ ucwords(str_replace('_',' ',$module)) }}</strong>
                        </td>
                        @foreach($actions as $ak => $al)
                        <td class="text-center align-middle">
                            @if(isset($perms[$ak]))
                            <div class="checkbox-custom checkbox-primary">
                                <input type="checkbox"
                                       name="permissions[]"
                                       value="{{ $perms[$ak] }}"
                                       data-action="{{ $ak }}"
                                       data-module="{{ $module }}"
                                       class="perm-cb action-{{ $ak }}"
                                       id="perm_{{ $module }}_{{ $ak }}"
                                       {{ in_array($perms[$ak], $assigned) ? 'checked' : '' }}>
                                <label for="perm_{{ $module }}_{{ $ak }}"></label>
                            </div>
                            @else
                            <span class="text-muted">—</span>
                            @endif
                        </td>
                        @endforeach
                        <td class="text-center align-middle">
                            <div class="checkbox-custom checkbox-primary">
                                <input type="checkbox" class="row-check" data-module="{{ $module }}" id="row_{{ $module }}">
                                <label for="row_{{ $module }}"></label>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </section>
    </div>

    {{-- ── Report Permissions ── --}}
    @if($reportPerms->count())
    <div class="col-12">
        <section class="card mb-3">
            <header class="card-header d-flex justify-content-between align-items-center">
                <h2 class="card-title mb-0">Report Access</h2>
                <div class="checkbox-custom checkbox-primary">
                    <input type="checkbox" id="checkAllReports">
                    <label for="checkAllReports" class="text-muted" style="font-size:12px;">Select All Reports</label>
                </div>
            </header>
            <div class="card-body">
                <div class="row">
                @foreach($reportPerms as $p)
                <div class="col-md-3 mb-2">
                    <div class="checkbox-custom checkbox-primary">
                        <input type="checkbox"
                               name="permissions[]"
                               value="{{ $p->name }}"
                               class="report-cb"
                               id="rpt_{{ $p->id }}"
                               {{ in_array($p->name, $assigned) ? 'checked' : '' }}>
                        <label for="rpt_{{ $p->id }}">
                            {{ ucwords(str_replace(['reports.','.','_'], ['',': ',' '], $p->name)) }}
                        </label>
                    </div>
                </div>
                @endforeach
                </div>
            </div>
        </section>
    </div>
    @endif

    {{-- ── Save Button (bottom) ── --}}
    <div class="col-12">
        <div class="d-flex justify-content-end gap-2 mb-4">
            <a href="{{ route('roles.index') }}" class="btn btn-default">Cancel</a>
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save me-1"></i>
                {{ isset($role) ? 'Update Role' : 'Create Role' }}
            </button>
        </div>
    </div>

</div>
</form>

@push('scripts')
<script>
// Column toggle (View / Create / Edit / Delete / Print)
document.querySelectorAll('.col-check').forEach(cb => {
    cb.addEventListener('change', function () {
        const action = this.dataset.action;
        document.querySelectorAll('.action-' + action).forEach(c => c.checked = this.checked);
        syncRowChecks();
    });
});

// Row toggle (All permissions for one module)
document.querySelectorAll('.row-check').forEach(cb => {
    cb.addEventListener('change', function () {
        const module = this.dataset.module;
        document.querySelectorAll('[data-module="' + module + '"].perm-cb').forEach(c => c.checked = this.checked);
        syncColChecks();
    });
});

// Select All Modules
document.getElementById('checkAllModules')?.addEventListener('change', function () {
    document.querySelectorAll('.row-check').forEach(cb => {
        cb.checked = this.checked;
        cb.dispatchEvent(new Event('change'));
    });
});

// Select All Reports
document.getElementById('checkAllReports')?.addEventListener('change', function () {
    document.querySelectorAll('.report-cb').forEach(cb => cb.checked = this.checked);
    syncMasterCheck();
});

// Master toggle
document.getElementById('masterCheckAll')?.addEventListener('change', function () {
    const state = this.checked;
    document.querySelectorAll('.perm-cb,.report-cb,.row-check,.col-check').forEach(cb => cb.checked = state);
    document.getElementById('checkAllModules').checked = state;
    document.getElementById('checkAllReports') && (document.getElementById('checkAllReports').checked = state);
});

// Sync helpers
function syncRowChecks() {
    document.querySelectorAll('.row-check').forEach(rb => {
        const module = rb.dataset.module;
        const cbs    = document.querySelectorAll('[data-module="' + module + '"].perm-cb');
        const all    = [...cbs].every(c => !c.closest('td').querySelector('span.text-muted') ? c.checked : true);
        rb.checked   = all;
    });
    syncMasterCheck();
}

function syncColChecks() {
    document.querySelectorAll('.col-check').forEach(cc => {
        const action = cc.dataset.action;
        const cbs    = document.querySelectorAll('.action-' + action);
        cc.checked   = cbs.length > 0 && [...cbs].every(c => c.checked);
    });
    syncMasterCheck();
}

function syncMasterCheck() {
    const all = [...document.querySelectorAll('.perm-cb,.report-cb')].every(c => c.checked);
    const mc  = document.getElementById('masterCheckAll');
    if (mc) mc.checked = all;
}

// Init row-check states on page load
document.querySelectorAll('.row-check').forEach(rb => {
    const module = rb.dataset.module;
    const cbs    = [...document.querySelectorAll('[data-module="' + module + '"].perm-cb')];
    if (cbs.length > 0) rb.checked = cbs.every(c => c.checked);
});
syncColChecks();
</script>
@endpush

@endsection