<?php
namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;

trait BranchScoped
{
    protected function branchScope(Builder $query): Builder
    {
        $user = auth()->user();
        if ($user && $user->branch_id) {
            $query->where('branch_id', $user->branch_id);
        }
        return $query;
    }

    protected function userBranchId(): ?int
    {
        return auth()->user()?->branch_id;
    }

    protected function defaultBranchId(): int
    {
        return auth()->user()?->branch_id ?? \App\Models\Branch::first()?->id ?? 1;
    }
}
