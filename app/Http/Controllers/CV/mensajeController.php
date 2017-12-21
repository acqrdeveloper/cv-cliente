<?php
/**
 * Created by PhpStorm.
 * User: QuispeRoque
 * Date: 21/04/17
 * Time: 12:50
 */

namespace CVClient\Http\Controllers\CV;


use CVClient\CV\Repos\MensajeRepo;
use Illuminate\Http\Request;

class mensajeController
{
    protected $request;
    protected $repo;

    public function __construct(Request $request, MensajeRepo $mensajeRepo)
    {
        $this->request = $request;
        $this->repo = $mensajeRepo;
    }

    function getConversationsList()
    {
        $rpta = $this->repo->getConversationsList($this->request->all());
        if ($rpta['load']) {
            return response()->json($rpta, 200);
        } else {
            return response()->json($rpta, 412);
        }
    }

    function conversations()
    {
        $rpta = $this->repo->conversations($this->request);
        if ($rpta['load']) {
            return response()->json($rpta, 200);
        } else {
            return response()->json($rpta, 412);
        }
    }

    function conversation()
    {
        $rpta = $this->repo->nuevaRespuesta($this->request);
        if ($rpta['load']) {
            return response()->json($rpta, 200);
        } else {
            return response()->json($rpta, 412);
        }
    }

}