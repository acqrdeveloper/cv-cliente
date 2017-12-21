<?php
namespace CVClient\Http\Controllers\CV;
header('Access-Control-Allow-Headers: Content-Type');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Origin: *');
use DB;
use CVClient\CV\Repos\BandejaRepo;
use Illuminate\Http\Request;

class bandejaController{

    use \CVClient\Traits\NotificationTrait;

    function getMyMessages( $tipo_usuario, $id )
    {
        try{
            $params = request()->all();
            return response()->json( ( new BandejaRepo )->getMyMessages( $tipo_usuario, $id, $params ) );
        } catch(\Exception $ex) {
            return response()->json( [ "load" => true, "error" => $ex->getMessage(), "detail" => $ex->getLine() ], 412 );
        }
    }

    function getMyReceivedMessages( $tipo_usuario, $id )
    {
        try{
            $params = request()->all();
            return response()->json( ( new BandejaRepo )->getMyReceivedMessages( $tipo_usuario, $id, $params ) );
        } catch(\Exception $ex) {
            return response()->json( [ "load" => true, "error" => $ex->getMessage(), "detail" => $ex->getLine() ], 412 );
        }
    }

    function getMySendMessages( $tipo_usuario, $id )
    {
        try{
            $params = request()->all();
            return response()->json( ( new BandejaRepo )->getMySendMessages( $tipo_usuario, $id, $params ) );
        } catch(\Exception $ex) {
            return response()->json( [ "load" => true, "error" => $ex->getMessage(), "detail" => $ex->getLine() ], 412 );
        }
    }

    function getMessageDetail( $message_id )
    {
        try{
            return response()->json( ( new BandejaRepo )->getMessageDetail( $message_id ) );
        } catch(\Exception $ex) {
            return response()->json( [ "load" => true, "error" => $ex->getMessage(), "detail" => $ex->getLine()." ".$ex->getFile() ], 412 );
        }
    }

    function postNewMessages()
    {   
        $data = [];
        DB::beginTransaction();
        $params = request()->all();

        try{

            $user = \Auth::user();

            if( ($user->preferencia_estado != 'A' && $user->preferencia_estado != 'X') && $params["asunto"] == 'H')
                throw new \Exception("Su cuenta se encuentra suspendida, regularice su situación llamando al (01) 707-3500 Anexo 305.");

            $data = ( new BandejaRepo )->postNewMessages( $params );
            DB::commit();
            return response()->json( ['message' => 'Mensaje enviado', 'data' => $data ]);
        } catch(\Exception $ex) {
            DB::rollBack();
            return response()->json( ["message" => $ex->getMessage(), "detail" => $ex->getLine() ], 412 );
        }
    }

    function putReadMessages( $message_id )
    {
        try{
            return response()->json( ( new BandejaRepo )->putReadMessages( $message_id ) );
        } catch(\Exception $ex) {
            return response()->json( [ "load" => true, "error" => $ex->getMessage(), "detail" => $ex->getLine() ], 412 );
        }
    }
}