<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use App\Traits\Uuid;
use Database\Factories\ClientFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Scout\Searchable;

class Client extends Model
{
    /** @use HasFactory<ClientFactory> */
    use BelongsToTenant, HasFactory, Searchable, Uuid;

    protected $fillable = [
        'name',
        'contact_email',
        'contact_phone',
        'contact',
    ];

    /**
     * @return array<string, mixed>
     */
    public function toSearchableArray(): array
    {
        return [
            'id' => $this->id,
            'tenant_id' => $this->tenant_id,
            'name' => $this->name,
            'contact' => $this->contact,
            'contact_email' => $this->contact_email,
        ];
    }
}
