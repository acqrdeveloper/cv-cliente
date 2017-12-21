<?php namespace CVClient\CV\Repos;
use CVClient\Http\Controllers\Controller;
use CVClient\Common\Repos\QueryRepo;
use CVClient\CV\Models\Abogado;
class AbogadoRepo extends Controller
{
    function search($getparams)
    {
        $getparams["empresa_id"] = \Auth::user()->id;
        return ( new QueryRepo )->Q_abogados_casos($getparams);
    }
    function create($params)
    {
        $abogado = Abogado::create(
        	array(
				'created_at'	=> date("Y-m-d H:i:s"),
                'updated_at'    => date("Y-m-d H:i:s"),
				'caso'			=> $params["caso"],
				'demandado'		=> $params["demandado"],
				'demandante'	=> $params["demandante"],
				'estado' 		=> "A",
				'empresa_id' 	=> \Auth::user()->id
        	)
        );
        return $abogado;
    }
    function update($id, $estado)
    {
        $abogado = Abogado::where( 'empresa_id', \Auth::user()->id )->where( 'id', $id )->update( array( 'estado' => $estado ) );
        return $abogado;
    }
}