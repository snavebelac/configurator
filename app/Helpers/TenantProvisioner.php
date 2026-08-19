<?php

namespace App\Helpers;

use App\Models\Setting;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

class TenantProvisioner
{
    public const OWNER_ROLE = 'superadmin';

    /**
     * Create a tenancy and its first user.
     *
     * Shared by the public signup screen and the tenant:create command so
     * there is one definition of what a new workspace starts life with.
     *
     * @param  array{company: string, name: string, last_name: string, email: string, password: string}  $attributes
     */
    public function provision(array $attributes): User
    {
        return DB::transaction(function () use ($attributes) {
            $tenant = Tenant::create([
                'name' => $attributes['company'],
                'subdomain' => $this->uniqueSubdomain($attributes['company']),
            ]);

            // tenant_id is assigned directly rather than mass-assigned: it is
            // not in either model's $fillable, so passing it to create() would
            // be silently dropped. BelongsToTenant's creating hook can't help
            // here either — it only stamps tenant_id when a session tenant
            // exists, and during signup there isn't one yet.
            $user = new User([
                'name' => $attributes['name'],
                'last_name' => $attributes['last_name'],
                'email' => $attributes['email'],
                'password' => $attributes['password'],
                'active' => true,
            ]);
            $user->tenant_id = $tenant->id;
            $user->save();

            // Model defaults supply currency, tax name and rate, so a new
            // workspace is never left without settings for SettingsHelper.
            $setting = new Setting;
            $setting->tenant_id = $tenant->id;
            $setting->save();

            $user->assignRole($this->ownerRole());

            return $user;
        });
    }

    /**
     * Roles are global rather than per-tenant in this app, so the owner role
     * is created once and reused by every subsequent workspace.
     */
    private function ownerRole(): Role
    {
        return Role::firstOrCreate([
            'name' => self::OWNER_ROLE,
            'guard_name' => config('auth.defaults.guard', 'web'),
        ]);
    }

    /**
     * Derive a URL-safe subdomain from the company name.
     *
     * The column is NOT NULL with no default and nothing else populates it.
     * Subdomains aren't routed on yet, but they should still be unique so the
     * value stays usable if tenancy ever moves to subdomain resolution.
     */
    private function uniqueSubdomain(string $company): string
    {
        $base = Str::slug($company) ?: 'workspace';
        $candidate = $base;
        $suffix = 2;

        while (Tenant::where('subdomain', $candidate)->exists()) {
            $candidate = $base.'-'.$suffix;
            $suffix++;
        }

        return $candidate;
    }
}
