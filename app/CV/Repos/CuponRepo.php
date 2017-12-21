<?php
namespace CVClient\CV\Repos;
use CVClient\CV\Models\Cupon;
class CuponRepo{
    public function valid( $codigo ){
        $cupon = Cupon::where('codigo', $codigo)->where( "finicio", "<=", date("Y-m-d") )->where( "ffin", ">=", date("Y-m-d") )->where("usado", 0)->first();
        if(is_null($cupon)){
            throw new \Exception("El cupón ingresado no existe o ya expiró");
        }
        return $cupon;
    }
}
?>