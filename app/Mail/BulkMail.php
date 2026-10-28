<?php


namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class BulkMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $subjectLine;
    public string $bodyHtml;
    public ?string $fromEmail;
    public ?string $fromName;

    public function __construct(string $subjectLine, string $bodyHtml, ?string $fromEmail = null, ?string $fromName = null)
    {
        $this->subjectLine = $subjectLine;
        $this->bodyHtml = $bodyHtml;
        $this->fromEmail = $fromEmail;
        $this->fromName = $fromName;
    }

    public function build()
    {
        $mail = $this->subject($this->subjectLine)
                     ->html($this->bodyHtml);

        if ($this->fromEmail) {
            $mail->from($this->fromEmail, $this->fromName ?? $this->fromEmail);
        }

        return $mail;
    }
}