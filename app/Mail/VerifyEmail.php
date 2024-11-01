<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class VerifyEmail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     *
     */
    public $data;
    public function __construct($data)
    {
        $this->data = $data;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        if ($this->data['verify2']){
            return $this->view('emails.verify_email2')
                    ->subject('Verifica o seu novo email')
                    ->with('data', $this->data);
        }else {
            return $this->view('emails.verify_email')
                    ->subject('Verifica o seu email')
                    ->with('data', $this->data);
        }
    }
}
