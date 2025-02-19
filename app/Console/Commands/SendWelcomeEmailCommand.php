<?php
namespace App\Console\Commands;

use App\Jobs\SendWelcomeEmail;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SendWelcomeEmailCommand extends Command
{
    protected $signature   = 'email:welcome {email} {name}';
    protected $description = 'Send a welcome email to a user';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        try {
            $email = $this->argument('email');
            $name  = $this->argument('name');

            SendWelcomeEmail::dispatch($email, $name);

            $this->info("Welcome email queued for: $email");
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            $this->error("Failed to queue welcome email for: $email");
        }
    }
}
