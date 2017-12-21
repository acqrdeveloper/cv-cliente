<?php

namespace CVClient\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
use CVClient\Models\Empresa;

class Credentials extends Mailable
{
    use Queueable, SerializesModels;

    public $empresa = null;
    public $copia = null;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($empresa, $cc = null)
    {
        $this->empresa = $empresa;
        $this->copia = $cc;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $rep = $this->empresa->representantes()->first();

        if(is_null($rep)){
            throw new \Exception("La empresa no tiene representante");
        }

        $fullname = $rep->nombre . " " . $rep->apellido;
        $to_email = $rep->correo;
        $to_name = $fullname;

        if(env('APP_ENV') == 'local'){
            $to_name = '';
            $to_email = 'kbaylon@sitatel.com';
        }

        $mail = $this->from('noreply@centrosvirtuales.com')
                ->to($to_email, $to_name)
                ->subject('Bienvenido a Centros Virtuales')
                ->view('mail.html.welcome')
                ->with([
                    'fullname' => $fullname,
                    'username' => $this->empresa->preferencia_login,
                    'password' => $this->empresa->preferencia_contrasenia
                ]);

        if(!is_null($this->copia) && !empty($this->copia)){
            $mail->cc($this->copia, null);
        }

        return $mail;
    }
}
