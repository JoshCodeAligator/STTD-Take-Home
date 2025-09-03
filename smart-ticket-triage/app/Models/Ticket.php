<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    protected $fillable = [
        'subject','description','requester_email','status',
        'ai_category','ai_confidence','override_category','classification_status','classified_at'
    ];

    protected $appends = ['effective_category'];

    public function notes() { return $this->hasMany(Note::class); }

    public function getEffectiveCategoryAttribute(): string {
        return $this->override_category ?? $this->ai_category ?? 'Uncategorized';
    }

    public function scopeSearch($q, $term) {
        if (!$term) return $q;
        return $q->where(function($qq) use ($term) {
            $qq->where('subject','like',"%$term%")
               ->orWhere('description','like',"%$term%")
               ->orWhere('requester_email','like',"%$term%");
        });
    }
    public function scopeCategory($q, $cat) {
        return $cat ? $q->whereRaw("COALESCE(override_category, ai_category) = ?", [$cat]) : $q;
    }
    public function scopeStatus($q, $st) {
        return $st ? $q->where('status',$st) : $q;
    }
}
