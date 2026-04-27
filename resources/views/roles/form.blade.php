@extends('layouts.app')
@section('title', isset($role) ? 'Edit Role: '.$role->name : 'New Role')
@section('content')

@php
use App\Http\Controllers\RoleController;

$actions = ['index'=>'View','create'=>'Create','edit'=>'Edit','delete'=>'Delete','print'=>'Print'];
$moduleLabels = RoleController::moduleLabels();

// Group dot-notation permissions by module (exclude reports.*)
$modulePerms = [];
foreach($permissions as $p) {
    $parts = explode('.', $p->name);
    if(count($parts) === 2 && $parts[0] !== 'reports') {
        $modulePerms[$parts[0]][$parts[1]] = $p->name;
    }
}

// Only show modules that have at least one permission AND are in our label list
$modulePerms = array_filter($modulePerms, fn($m) => isset($moduleLabels[$m]), ARRAY_FILTER_USE_KEY);

// Sort by label
uksort($modulePerms, fn($a,$b) => strcmp($moduleLabels[$a] ?? $a, $moduleLabels[$b] ?? $b));

// Report permissions
$reportPerms = $permissions->filter(fn($p) => str_starts_with($p->name, 'reports.'));

// Currently assigned
$assigned = isset($role) ? $role->permissions->pluck('name')->toArray() : [];
@endphp

<form action="{{ isset($role) ? route('roles.update', $role) : route('roles.store') }}" method="POST">
@csrf
@if(isset($role)) @method('PUT') @endif

{{-- ── Header ── --}}
<section class="card card-featured card-featured-primary mb-3">
    <header class="card-header d-flex justify-content-between align-items-center">
        <h2 class="card-title mb-0">
            {{ isset($role) ? 'Edit Role: '.$role->name : 'New Role' }}
        </h2>
        <div class="d-flex gap-2">
            <a href="{{ route('roles.index') }}" class="btn btn-default btn-sm">
                <i class="fas fa-arrow-left me-1"></i> Back
            </a>
            <button type="submit" class="btn btn-primary btn-sm">
                <i class="fas fa-save me-1"></i>
                {{ isset($role) ? 'Update Role' : 'Create Role' }}
            </button>
        </div>
    </header>
    <div class="card-body">
        @if($errors->any())
        <div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
        @endif

        <div class="col-md-4">
            <div class="form-group mb-0">
                <label class="control-label">Role Name <span class="required">*</span></label>
                <input type="text" name="name" class="form-control" required
                       value="{{ old('name', $role->name ?? '') }}"
                       placeholder="e.g. Sales Manager">
            </div>
        </div>
    </div>
</section>

{{-- ── Module Permissions ── --}}
<section class="card mb-3">
    <header class="card-header d-flex justify-content-between align-items-center">
        <h2 class="card-title mb-0">Module Permissions</h2>
        <div class="checkbox-custom checkbox-primary">
            <input type="checkbox" id="masterCheckAll">
            <label for="masterCheckAll" style="font-size:12px;font-weight:600;cursor:pointer;">
                Select All
            </label>
        </div>
    </header>
    <div class="card-body p-0" style="max-height:480px;overflow-y:auto;">
        <table class="table table-bordered mb-0" style="font-size:13px;">
            <thead style="position:sticky;top:0;z-index:2;">
                <tr style="background:#1a1a2e;color:#fff;">
                    <th style="min-width:200px;padding:10px 14px;">Module</th>
                    @foreach($actions as $ak => $al)
                    <th class="text-center" style="width:100px;padding:8px;">
                        <div class="checkbox-custom checkbox-primary">
                            <input type="checkbox" class="col-check" data-action="{{ $ak }}" id="col_{{ $ak }}">
                            <label for="col_{{ $ak }}" style="color:#fff;font-weight:600;cursor:pointer;">{{ $al }}</label>
                        </div>
                    </th>
                    @endforeach
                    <th class="text-center" style="width:80px;padding:8px;">
                        <div class="checkbox-custom checkbox-primary">
                            <input type="checkbox" id="checkAllModules">
                            <label for="checkAllModules" style="color:#fff;font-weight:600;cursor:pointer;">All</label>
                        </div>
                    </th>
                </tr>
            </thead>
            <tbody>
            @forelse($modulePerms as $module => $perms)
            <tr>
                <td class="align-middle" style="padding:8px 14px;">
                    <strong>{{ $moduleLabels[$module] ?? ucwords(str_replace(['-','_'],' ',$module)) }}</strong>
                </td>
                @foreach($actions as $ak => $al)
                <td class="text-center align-middle" style="padding:6px;">
                    @if(isset($perms[$ak]))
                    <div class="checkbox-custom checkbox-primary">
                        <input type="checkbox"
                               name="permissions[]"
                               value="{{ $perms[$ak] }}"
                               data-action="{{ $ak }}"
                               data-module="{{ $module }}"
                               class="perm-cb action-{{ $ak }}"
                               id="p_{{ $module }}_{{ $ak }}"
                               {{ in_array($perms[$ak], $assigned) ? 'checked' : '' }}>
                        <label for="p_{{ $module }}_{{ $ak }}"></label>
                    </div>
                    @else
                    <span class="text-muted" style="font-size:16px;">—</span>
                    @endif
                </td>
                @endforeach
                <td class="text-center align-middle" style="padding:6px;">
                    <div class="checkbox-custom checkbox-primary">
                        <input type="checkbox" class="row-check" data-module="{{ $module }}" id="row_{{ $module }}">
                        <label for="row_{{ $module }}"></label>
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="{{ 2 + count($actions) }}" class="text-center text-muted py-4">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    No module permissions found. Run <code>php artisan db:seed</code> to create them.
                </td>
            </tr>
            @endforelse
            </tbody>
        </table>
    </div>
</section>

{{-- ── Report Permissions ── --}}
@if($reportPerms->count())
<section class="card mb-3">
    <header class="card-header d-flex justify-content-between align-items-center">
        <h2 class="card-title mb-0">Report Access</h2>
        <div class="checkbox-custom checkbox-primary">
            <input type="checkbox" id="checkAllReports">
            <label for="checkAllReports" style="font-size:12px;font-weight:600;cursor:pointer;">
                Select All Reports
            </label>
        </div>
    </header>
    <div class="card-body">
        <div class="row">
        @foreach($reportPerms as $p)
        @php
            $label = ucwords(str_replace(['reports.','.','_'], ['',': ',' '], $p->name));
        @endphp
        <div class="col-md-3 mb-2">
            <div class="checkbox-custom checkbox-primary">
                <input type="checkbox"
                       name="permissions[]"
                       value="{{ $p->name }}"
                       class="report-cb"
                       id="rpt_{{ $p->id }}"
                       {{ in_array($p->name, $assigned) ? 'checked' : '' }}>
                <label for="rpt_{{ $p->id }}">{{ $label }}</label>
            </div>
        </div>
        @endforeach
        </div>
    </div>
</section>
@endif

{{-- ── General (Readable) Permissions ── --}}
@php
$generalPerms = $permissions->filter(fn($p) =>
    !str_contains($p->name, '.') &&
    !str_starts_with($p->name, 'reports.')
);
@endphp
@if($generalPerms->count())
<section class="card mb-3">
    <header class="card-header d-flex justify-content-between align-items-center">
        <h2 class="card-title mb-0">General Permissions</h2>
        <div class="checkbox-custom checkbox-primary">
            <input type="checkbox" id="checkAllGeneral">
            <label for="checkAllGeneral" style="font-size:12px;font-weight:600;cursor:pointer;">
                Select All
            </label>
        </div>
    </header>
    <div class="card-body">
        <div class="row">
        @foreach($generalPerms as $p)
        <div class="col-md-3 mb-2">
            <div class="checkbox-custom checkbox-primary">
                <input type="checkbox"
                       name="permissions[]"
                       value="{{ $p->name }}"
                       class="general-cb"
                       id="gen_{{ $p->id }}"
                       {{ in_array($p->name, $assigned) ? 'checked' : '' }}>
                <label for="gen_{{ $p->id }}">{{ ucfirst($p->name) }}</label>
            </div>
        </div>
        @endforeach
        </div>
    </div>
</section>
@endif

{{-- ── Bottom Save ── --}}
<div class="d-flex justify-content-end gap-2 mb-4">
    <a href="{{ route('roles.index') }}" class="btn btn-default">Cancel</a>
    <button type="submit" class="btn btn-primary">
        <i class="fas fa-save me-1"></i>
        {{ isset($role) ? 'Update Role' : 'Create Role' }}
    </button>
</div>

</form>

@push('scripts')
<script>
// Column toggle (check action across all modules)
document.querySelectorAll('.col-check').forEach(cb => {
    cb.addEventListener('change', function () {
        document.querySelectorAll('.action-' + this.dataset.action).forEach(c => c.checked = this.checked);
        syncRowChecks(); syncMasterCheck();
    });
});

// Row toggle (all actions for one module)
document.querySelectorAll('.row-check').forEach(cb => {
    cb.addEventListener('change', function () {
        document.querySelectorAll('[data-module="' + this.dataset.module + '"].perm-cb').forEach(c => c.checked = this.checked);
        syncColChecks(); syncMasterCheck();
    });
});

// Select All Modules
document.getElementById('checkAllModules')?.addEventListener('change', function () {
    document.querySelectorAll('.perm-cb').forEach(c => c.checked = this.checked);
    document.querySelectorAll('.row-check').forEach(c => c.checked = this.checked);
    syncColChecks(); syncMasterCheck();
});

// Select All Reports
document.getElementById('checkAllReports')?.addEventListener('change', function () {
    document.querySelectorAll('.report-cb').forEach(c => c.checked = this.checked);
    syncMasterCheck();
});

// Select All General
document.getElementById('checkAllGeneral')?.addEventListener('change', function () {
    document.querySelectorAll('.general-cb').forEach(c => c.checked = this.checked);
    syncMasterCheck();
});

// Master Select All
document.getElementById('masterCheckAll')?.addEventListener('change', function () {
    const s = this.checked;
    document.querySelectorAll('.perm-cb,.report-cb,.general-cb,.row-check,.col-check').forEach(c => c.checked = s);
    const cm = document.getElementById('checkAllModules');
    const cr = document.getElementById('checkAllReports');
    const cg = document.getElementById('checkAllGeneral');
    if (cm) cm.checked = s;
    if (cr) cr.checked = s;
    if (cg) cg.checked = s;
});

// Individual perm checkbox
document.querySelectorAll('.perm-cb').forEach(cb => {
    cb.addEventListener('change', () => { syncRowChecks(); syncColChecks(); syncMasterCheck(); });
});

function syncRowChecks() {
    document.querySelectorAll('.row-check').forEach(rb => {
        const module = rb.dataset.module;
        const cbs = [...document.querySelectorAll('[data-module="' + module + '"].perm-cb')];
        rb.checked = cbs.length > 0 && cbs.every(c => c.checked);
    });
}
function syncColChecks() {
    document.querySelectorAll('.col-check').forEach(cc => {
        const cbs = [...document.querySelectorAll('.action-' + cc.dataset.action)];
        cc.checked = cbs.length > 0 && cbs.every(c => c.checked);
    });
}
function syncMasterCheck() {
    const all = [...document.querySelectorAll('.perm-cb,.report-cb,.general-cb')];
    const mc  = document.getElementById('masterCheckAll');
    if (mc) mc.checked = all.length > 0 && all.every(c => c.checked);
}

// Init on load
syncRowChecks(); syncColChecks(); syncMasterCheck();
</script>
@endpush
@endsection