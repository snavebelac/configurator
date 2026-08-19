<?php

namespace App\Console\Commands;

use App\Helpers\TenantProvisioner;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

use function Laravel\Prompts\password as promptPassword;
use function Laravel\Prompts\text;

class TenantCreate extends Command
{
    protected $signature = 'tenant:create
                            {--company= : The workspace / company name}
                            {--name= : The first user\'s first name}
                            {--last-name= : The first user\'s last name}
                            {--email= : The first user\'s email address}
                            {--password= : The first user\'s password}';

    protected $description = 'Create a new tenant workspace and its first admin user';

    public function handle(TenantProvisioner $provisioner): int
    {
        $attributes = [
            'company' => $this->option('company') ?: text('Company name', required: true),
            'name' => $this->option('name') ?: text('First name', required: true),
            'last_name' => $this->option('last-name') ?: text('Last name', required: true),
            'email' => $this->option('email') ?: text('Email address', required: true),
            'password' => $this->option('password') ?: promptPassword('Password', required: true),
        ];

        $validator = Validator::make($attributes, [
            'company' => ['required', 'string', 'max:255'],
            'name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', Password::default()],
        ]);

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->components->error($error);
            }

            return self::FAILURE;
        }

        $user = $provisioner->provision($attributes);

        $this->components->info("Workspace \"{$attributes['company']}\" created.");
        $this->newLine();
        $this->table(
            ['Tenant', 'Subdomain', 'User', 'Email'],
            [[
                $user->tenant->name,
                $user->tenant->subdomain,
                $user->full_name,
                $user->email,
            ]],
        );

        return self::SUCCESS;
    }
}
