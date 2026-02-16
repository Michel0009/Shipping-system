<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $email;
    public $emailBody;
    public $subject;

    public function __construct($email, $emailBody, $subject)
    {
        $this->email = $email;
        $this->emailBody = $emailBody;
        $this->subject = $subject;
    }

    public function handle(): void
    {
        Mail::html($this->emailBody, function ($message) {
            $message->to($this->email)
                ->subject($this->subject);
        });
        
    }
}
