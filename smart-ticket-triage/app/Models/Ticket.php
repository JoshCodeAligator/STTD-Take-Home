<?php
declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Ticket extends Model
{
    use HasUlids, HasFactory;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'subject','body','requester_email','status',
        'ai_category','ai_explanation','ai_confidence',
        'override_category','classification_status','classified_at',
    ];

    // Append API-facing fields so SPA can read: category, confidence, explanation
    protected $appends = ['category','confidence','explanation'];

    public function notes() { return $this->hasMany(Note::class); }

    // API-facing virtual fields
    public function getCategoryAttribute(): ?string
    {
        return $this->override_category ?? $this->ai_category;
    }
    public function getConfidenceAttribute(): ?float
    {
        return $this->ai_confidence;
    }
    public function getExplanationAttribute(): ?string
    {
        return $this->ai_explanation;
    }

    // Filters for GET /tickets
    public function scopeSearch($q, ?string $term) {
        if (!$term) return $q;
        return $q->where(function($qq) use ($term) {
            $qq->where('subject','like',"%$term%")
               ->orWhere('body','like',"%$term%")
               ->orWhere('requester_email','like',"%$term%");
        });
    }
    public function scopeCategory($q, ?string $cat) {
        return $cat ? $q->whereRaw("COALESCE(override_category, ai_category) = ?", [$cat]) : $q;
    }
    public function scopeStatus($q, ?string $st) {
        return $st ? $q->where('status',$st) : $q;
    }
}