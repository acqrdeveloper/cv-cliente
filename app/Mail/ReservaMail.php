<?php
namespace CVClient\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

// Models
use DB;
use CVClient\CV\Models\Cochera;
use CVClient\CV\Models\Empresa;
use CVClient\CV\Models\Local;
use CVClient\CV\Models\Oficina;
use CVClient\CV\Models\Reserva;
use CVClient\CV\Models\Representante;
use CVClient\CV\Models\OficinaPromocion;

class ReservaMail extends Mailable
{
    use Queueable, SerializesModels;

    public $reserva;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($id)
    {
        $this->reserva = Reserva::find($id);
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {

        $tipos = ['PRIVADA' => 'Sala Privada','REUNION' => 'Sala de Reunión','AUDITORIO'=>'Auditorio','TERRAZA'=>'Terraza','CAPACITACION'=>'Sala de capacitación','COWORKING'=>'Coworking'];
        
        $representante = Representante::where('empresa_id', $this->reserva->empresa_id)->where('estado','A')->first();
        $oficina = Oficina::find($this->reserva->oficina_id);
        $local = Local::find($oficina->local_id);
        $empresa = Empresa::find($this->reserva->empresa_id);

        $cochera = null;

        if($this->reserva->cochera_id > 1){
            $cochera = Cochera::find($this->reserva->cochera_id);
        }

        if(env('APP_ENV') != 'local'){
            $to_addr = $representante->correo; 
            $to_name = $representante->nombre . ' ' . $representante->apellido;
        } else {
            $to_addr = 'kbaylon@sitatel.com';
            $to_name = 'Kevin';
        }

        $data = [
            'fullname' => $representante->nombre . ' ' . $representante->apellido,
            'estado' => $this->reserva->estado,
            'fecha_reserva' => $this->reserva->fecha_reserva,
            'local_nombre' => $local->direccion . ' - ' . $local->distrito,
            'espacio' => $tipos[$oficina->tipo]. ' ' . $oficina->nombre,
            'hora_inicio' => $this->reserva->hora_inicio,
            'hora_fin' => $this->reserva->hora_fin,
            'proyector' => $this->reserva->proyector,
            'modelo_id' => $oficina->modelo_id,
            'cochera' => (is_null($cochera)?'NO':$cochera->nombre . ' Placa ' . $this->reserva->placa ),
            'horas' => 5
        ];

        if($oficina->modelo_id != 1){

            $cupon = DB::table('cupon')->where('reserva_id', $this->reserva->id)->first();
            $coffeebreak = DB::table('reserva_detalle')->where('reserva_id', $this->reserva->id)->first();
            $promocion = OficinaPromocion::where( 'local_id', $local->id)->where( 'modelo_id', $oficina->modelo_id)->where( 'plan_id', $empresa->plan_id)->where( 'tipo', "H")->where( "desde", "<=", 0 )->first();

            $detalle = []; $total = 0;

            if(!is_null($promocion)){
                $tot = ((int)substr($this->reserva->hora_fin,0,2) - (int)substr($this->reserva->hora_inicio,0,2))*$promocion->precio;
                array_push($detalle, [
                    'concepto' => 'Alquiler de Espacio',
                    'cantidad' => 1,
                    'preciou' => $promocion->precio,
                    'preciot' => $tot,
                ]);

                $total += $tot;
            }

            if(!is_null($coffeebreak)){
                $tot = $coffeebreak->cantidad*$coffeebreak->precio;
                array_push($detalle, [
                    'concepto' => 'Coffeebreak combo ' . $coffeebreak->concepto_id,
                    'cantidad' => (int)$coffeebreak->cantidad,
                    'preciou' => $coffeebreak->precio,
                    'preciot' => $tot,
                ]);

                $total += $tot;
            }

            if(!is_null($cupon)){
                $tot = $cupon->monto;
                array_push($detalle, [
                    'concepto' => 'Cupon dcto. (' . $cupon->codigo . ') ',
                    'cantidad' => 1,
                    'preciou' => $cupon->monto,
                    'preciot' => $tot,
                ]);

                $total -= $tot;
            }

            $data['detalle'] = $detalle;
            $data['total'] = $total;

        }

        switch($this->reserva->estado) {
            case 'A':
                $subject = 'Reserva Confirmada';
                break;
            case 'C':
                $subject = 'Reserva en revisión';
                break;
            case 'P':
                $subject = 'Nueva Reserva';
                break;
            case 'E':
                $subject = 'Reserva cancelada';
        }

        return $this->from('noreply@centrosvirtuales.com' ,'Centros Virtuales del Perú E.I.R.L.')
                    ->to($to_addr, $to_name)
                    ->subject($subject)
                    ->view('mail.reserva', $data);
    }

}
