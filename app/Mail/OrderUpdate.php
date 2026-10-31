<?php
namespace App\Mail;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
class OrderUpdate extends Mailable {
 public function __construct(public array $details){}
 public function envelope(): Envelope { $d=$this->details;return new Envelope(subject:$d['business']['brand_name'].' | '.($d['event']==='placed'?'Order received':($d['event']==='status'?$d['order']['status']:'Order details updated')).' | '.$d['order']['reference']); }
 public function content(): Content { return new Content(view:'emails.order-update',text:'emails.order-update-text'); }
}
