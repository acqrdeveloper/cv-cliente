<?php
/**
 * Created by PhpStorm.
 * User: QuispeRoque
 * Date: 18/04/17
 * Time: 13:16
 */

namespace CVClient\Http\Controllers\CV;


use CVClient\CV\Repos\FeedbackRepo;
use CVClient\Http\Controllers\Controller;
use Illuminate\Http\Request;

class feedbackController extends Controller
{
    protected $request;
    protected $repo;

    public function __construct(Request $request, FeedbackRepo $feedbackRepo)
    {
        $this->request = $request;
        $this->repo = $feedbackRepo;
    }

    function getFeedback()
    {
        $rpta = (new FeedbackRepo)->getFeedback($this->request->all());
        if ($rpta['load']) {
            return $rpta;
        } else {
            return response()->json($rpta, 412);
        }
    }



}