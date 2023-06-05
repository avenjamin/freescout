<?php

namespace App\Console\Commands;

use App\User;
use Illuminate\Console\Command;

class DeleteUser extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'freescout:delete-user {--id= : User ID} {--confirmDelete: Bypass deletion confirmation.}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete existing user';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        /**
        * Get user from database with id $this->option('id')
        * Ask to confirm deletion unless $this->option('confirmDelete') is set
        * Cancel or delete user
        **/
        
        
        $class = config(
            'auth.providers.'.config(
                'auth.guards.'.config(
                    'auth.defaults.guard'
                ).'.provider'
            ).'.model'
        ); # User class
        
        $user = $class::find($this->option('id'));


        if (!$this->option('confirmDelete') && !$this->confirm('Do you want to delete the user?', false)) {
			
			# <- Delete User Here ->
			
            $this->info('User with ID '.$user->id.' was NOT deleted');
            return false;
        }

        $this->info('User with ID '.$user->id.' was deleted!');
        return true;
    }
}
