<?php

namespace App\Api\V1\Controllers;

use JWTAuth;
use Validator;
use Config;
use App\ModelPajakHiburan;
use App\ModelPajakHotel;
use App\ModelPajakPat;
use App\ModelPajakReklame;
use App\ModelPajakRestoran;
use App\ModelPajakParkir;
use App\ModelPajakSSPD;
use Illuminate\Http\Request;
use Illuminate\Mail\Message;
use Dingo\Api\Routing\Helpers;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Password;
use Tymon\JWTAuth\Exceptions\JWTException;
use Dingo\Api\Exception\ValidationHttpException;

class DataPajak extends Controller
{
    use Helpers;

    public function getData(Request $request)
    {
        $req = $_GET;
        $ret = array();

        $validator = Validator::make($req, [
            'length' => 'required',
            'start' => 'required'
        ]);

        if($validator->fails()) {
            throw new ValidationHttpException($validator->errors()->all());
        }
        if($req['table']=='pat'){
            $pajak = new ModelPajakPat;
        }else if($req['table']=='hotel'){
            $pajak = new ModelPajakHotel;
        }else if($req['table']=='reklame'){
            $pajak = new ModelPajakReklame;
        }else if($req['table']=='restoran'){
            $pajak = new ModelPajakRestoran;
        }else if($req['table']=='hiburan'){
            $pajak = new ModelPajakHiburan;
        }else if($req['table']=='parkir'){
            $pajak = new ModelPajakParkir;
        }else if($req['table']=='sspd' || $req['table']=='sspd_group'){
            $pajak = new ModelPajakSSPD;
        }
        $ret['data'] = $pajak->getData($req);
        $ret['total'] = $pajak->getTotal($req);
        $ret['totalFilter'] = $pajak->getTotalFilter($req);
        $ret['totalSum'] = $pajak->getTotalSumFilter($req);
        $ret['pageTotalSum'] = $pajak->getpageTotalSumFilter($ret['data']);
        // echo "<pre>".print_r($ret, 1)."</pre>";die();
        return $ret;
    }
}