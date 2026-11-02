<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\In;

abstract class Controller
{
    protected const DEFAULT_PER_PAGE = 15;

    protected const MAX_PER_PAGE = 100;

    /**
     * Validation rules shared by every paginated list endpoint.
     *
     * @param  list<string>  $sortableFields
     * @return array<string, list<mixed>>
     */
    protected function listRules(array $sortableFields): array
    {
        return [
            'search' => ['sometimes', 'nullable', 'string', 'max:255'],
            'sort' => ['sometimes', $this->sortRule($sortableFields)],
            'page' => ['sometimes', 'integer', 'min:1'],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:'.self::MAX_PER_PAGE],
        ];
    }

    /**
     * Accepts a field name for ascending order, or the name prefixed with "-" for descending.
     *
     * @param  list<string>  $sortableFields
     */
    protected function sortRule(array $sortableFields): In
    {
        return Rule::in([...$sortableFields, ...array_map(fn (string $field): string => "-{$field}", $sortableFields)]);
    }

    /**
     * Order by the requested field, using the id as a tie-breaker so pages never overlap.
     */
    protected function applySort(Builder $query, ?string $sort, string $defaultSort): Builder
    {
        $sort ??= $defaultSort;
        $direction = str_starts_with($sort, '-') ? 'desc' : 'asc';

        return $query->orderBy(ltrim($sort, '-'), $direction)->orderBy('id', $direction);
    }

    /**
     * Wrap a search term for a case-insensitive LIKE, escaping the LIKE wildcards in it.
     */
    protected function likePattern(string $term): string
    {
        return '%'.addcslashes(mb_strtolower($term), '%_\\').'%';
    }
}
