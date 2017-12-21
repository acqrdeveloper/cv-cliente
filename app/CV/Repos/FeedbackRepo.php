<?php
/**
 * Created by PhpStorm.
 * User: QuispeRoque
 * Date: 18/04/17
 * Time: 13:26
 */

namespace CVClient\CV\Repos;


use CVClient\Common\Repos\QueryRepo;
use CVClient\CV\Models\Feedback;
use CVClient\Http\Controllers\Controller;
use CVClient\User;
use Illuminate\Support\Facades\DB;
use PDOException;

class FeedbackRepo extends Controller
{

    function getFeedback($getparams)
    {
        return (new QueryRepo)->Q_feedback($getparams);
    }


}