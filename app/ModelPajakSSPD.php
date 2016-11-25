<?php

namespace App;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;

class ModelPajakSSPD extends Model
{
	public $table = 'sspd';

	public function getData($req, $count=false, $options=array()){
		$that = $this;
	    $query = DB::table($this->table);
	    if(!empty($count)){
			$query = $query->select(DB::raw('count(*) as total'));
		}else if(!empty($options['total'])){
			$query = $query->select(DB::raw('SUM(jumlah) as total'));
	    }else if($req['table'] == 'sspd_group'){
			$query = $query->select(DB::raw('jenis_pajak, SUM(jumlah) as jumlah'));
		}else{
			$query = $query->select(DB::raw('*'));
		}

	    $tgl_bayar = $this->timeDB($req['tgl_bayar']);
	    $query = $query->where('tgl_bayar', 'like', $tgl_bayar.'%');

	    if($req['table'] == 'sspd'){
		    $pk = $req['columns'][0]['search']['value'];
		    $no_skp = $req['columns'][1]['search']['value'];
		    $jenis_pajak = $req['columns'][2]['search']['value'];
		    $nama = $req['columns'][3]['search']['value'];
		    $jumlah = $req['columns'][4]['search']['value'];
		    if(!empty($pk)){
		    	$query = $query->where('pk', 'like', '%'.$pk.'%');
		    }
		    if(!empty($no_skp)){
		    	$query = $query->where('no_skp', 'like', '%'.$no_skp.'%');
		    }
		    if(!empty($jenis_pajak)){
		    	$query = $query->where('jenis_pajak', 'like', '%'.$jenis_pajak.'%');
		    }
		    if(!empty($nama)){
		    	$query = $query->where('nama', 'like', '%'.$nama.'%');
		    }
		    if(!empty($jumlah)){
		    	$query = $query->where('jumlah', 'like', '%'.$jumlah.'%');
		    }

		    $c_order = $req['order'][0]['column'];
		    $order = $req['order'][0]['dir'];
		    if($req['draw']!=1 && $c_order != 0){
		    	if($c_order==0){
			    	$_c_order = 'pk';
		    	}else if($c_order==1){
		    		$_c_order = 'no_skp';
		    	}else if($c_order==2){
		    		$_c_order = 'jenis_pajak';
		    	}else if($c_order==3){
		    		$_c_order = 'nama';
		    	}else if($c_order==4){
		    		$_c_order = 'jumlah';
		    	}
		    	if(!empty($_c_order)){
		    		$query = $query->orderBy($_c_order, $order);
		    	}
		    }

			if(!empty($req['columns'][0]['search']['value'])){
		    	$query = $query->limit('1');
		    	$query = $query->offset($req['columns'][0]['search']['value']-1);
			}else if(
				$req['length']!="-1" 
				&& empty($count)
				&& empty($options['total'])
			){
		    	$query = $query->limit($req['length']);
		    	$query = $query->offset($req['start']);
		    }
		}else{
			if(empty($options['total'])){
				$query = $query->groupBy('jenis_pajak');
			}
		}

	    if(false){
  			$query = $query->toSql();
			dd($query);
		}
	    return $query->get();
	}
	public function getTotal($req){
		$value = DB::table($this->table)->select(DB::raw('count(*) as total'))->get();
		if(!empty($value)){
	    	return $value[0]->total;
	    }else{
	    	return 0;
	    }
	}
	public function getTotalFilter($req){
		if(!empty($req['columns'][0]['search']['value'])){
			return 1;
		}
		$value = $this->getData($req, TRUE);
		if(!empty($value)){
	    	return $value[0]->total;
	    }else{
	    	return 0;
	    }
	}
	public function timeDB($time, $end=false){
		$times = explode('-', $time);
		$newTime = $times[2].'-'.$times[1].'-'.$times[0];
		return $newTime;
	}
	public function getTotalSumFilter($req){
		$value = $this->getData($req, FALSE, array('total' => TRUE));
		if(!empty($value)){
	    	return $value[0]->total;
	    }else{
	    	return 0;
	    }
	}
	public function getpageTotalSumFilter($data){
		$pajak = 0;
		foreach ($data as $k => $v) {
			$pajak = $pajak + $v->jumlah;
		}
	    return $pajak;
	}
}
