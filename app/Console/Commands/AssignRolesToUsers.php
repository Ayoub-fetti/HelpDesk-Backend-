<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;

class AssignRolesToUsers extends Command
{

    protected $signature = 'users:assign-roles';
    protected $description = 'Assign roles to existing users based on their user_type';


    public function handle()
    {
        $this->info('Assigning roles to users...');

        $users = User::all();
        $count = 0;

        foreach($users as $user) {
            if ($user->user_type) {
                $user->assignRole($user->user_type);
                $count++;
            }
        }
        $this->info("Roles assigned to {$count} users successfully.");
    }
}
