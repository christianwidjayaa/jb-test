<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BaseModel extends Model
{
    protected $searchables = [];

    /**
     * Scope a query to only include records that match the search query.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $searchQuery
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSearch($query, $searchQuery)
    {
        if (!$searchQuery) {
            return $query;
        }

        $query->where(function ($subQuery) use ($searchQuery) {
            // escape the search query for % characters
            $searchQuery = str_replace('%', '\\%', $searchQuery);
            foreach ($this->searchables as $searchable) {
                $subQuery->orWhere($searchable, 'LIKE', "%{$searchQuery}%");
            }
        });

        return $query;
    }

    /**
     * Scope a query to order the results by a given field.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $sort
     * @param  string  $order
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOfOrder($query, $sort, $order)
    {
        if (!$sort) {
            return $query;
        }

        if (!$order) {
            $order = 'asc';
        }

        return $query->orderBy($sort, $order);
    }
    // End of local scope functions

    // global static functions
    /**
     * Searchable column.
     *
     * @return array
     */
    public static function getSearchables(): array
    {
        return [];
    }
}
