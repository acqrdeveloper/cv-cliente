<?php namespace CVClient\Http\Controllers\CV;
use CVClient\Http\Controllers\Controller;
use CVClient\CV\Repos\EmpresaRepo;
use Illuminate\Http\Request;
use CVClient\Common\Repos\QueryRepo;

class dashboardController extends Controller {
    public function initial( Request $request ){
        try{
            $getparams = $request->all();
            $getparams["anio"] = isset( $getparams["anio"] ) ? $getparams["anio"] : date("Y");
            $getparams["mes"]  = isset( $getparams["mes"]  ) ? $getparams["mes"]  : date("m");
            $recurso   = ( new EmpresaRepo )->getEmpresaRecursoPeriodoHoras( \Auth::user()->id, \Auth::user()->preferencia_facturacion, $getparams["anio"], $getparams["mes"] );
            $plan      = ( new EmpresaRepo )->getEmpresaPlan( \Auth::user()->plan_id );
            $bandeja   = \DB::table('bandeja')->where( "leido", 0 )->where( "a_tipo", "C" )->where( "a", \Auth::user()->id )->count();
            $empresa   = \Auth::user();
            $quer      = "SELECT IF((disposition='ANSWERED'),('CONTESTADA'),('NO CONTESTADA')) as 'name', COUNT(*) AS 'y' FROM cdr WHERE userfield = ? AND YEAR(calldate)= ? AND MONTH(calldate) = ? AND dst <> 's' AND LENGTH(dst) <= 3 GROUP BY disposition";
            $cdr       = (\DB::select(\DB::raw($quer), [\Auth::user()->preferencia_cdr, $getparams["anio"], $getparams["mes"] ]));
            return response()->json(["load" => true, "data" => [ "recurso" => $recurso, "plan" => $plan, "cdr" => $cdr, "bandeja" => $bandeja ] ]);
        } catch(\Exception $ex) {
            return response()->json(["load" => true, "error" => $ex->getMessage(), "detail" => $ex->getLine() ], 412);
        }
    }
    public function horario(){
        try{
            return response()->json((new QueryRepo)->Q_horario_local());
        } catch(\Exception $ex) {
            return response()->json(["load" => true, "error" => $ex->getMessage(), "detail" => $ex->getLine() ], 412);
        }

    }
}