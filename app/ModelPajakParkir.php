<?php

namespace App;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;

class ModelPajakParkir extends Model
{
	public $table = 'pendataan_parkir';
	public $pr = 'm_prop';
	public $kb = 'm_kab';
	public $kc = 'm_kec';
	public $kl = 'm_kel';

	public function getData($req, $count=false, $options=array()){
		$that = $this;
	    $query = DB::table($this->table.' as p');
	    if(!empty($count)){
			$query = $query->select(DB::raw('count(*) as total'));
		}else if(!empty($options['total'])){
			$query = $query->select(DB::raw('SUM(p.pajak_terhutang) as total'));
		}else{
			$query = $query->select(DB::raw('p.*, pr.nama as prop, kb.nama as kab, kc.nama as kec, kl.nama as kel'));
		}
		$query = $query
	    	->leftjoin(
	    		$this->pr.' as pr', 
	    		function($q){
	    			$q->on('p.kd_prop', '=', 'pr.kd_prop');
	    	})
	    	->leftjoin(
	    		$this->kb.' as kb', 
	    		function($q){
	    			$q->on('p.kd_kab', '=', 'kb.kd_kab')
	    				->on('p.kd_prop', '=', 'kb.kd_prop');
	    	})
	    	->leftjoin(
	    		$this->kc.' as kc', 
	    		function($q){
	    			$q->on('p.kd_kec', '=', 'kc.kd_kec')
	    				->on('p.kd_kab', '=', 'kc.kd_kab')
	    				->on('p.kd_prop', '=', 'kc.kd_prop');
	    	})
	    	->leftjoin(
	    		$this->kl.' as kl', 
	    		function($q){
	    			$q->on('p.kd_kel', '=', 'kl.kd_kel')
	    				->on('p.kd_kec', '=', 'kl.kd_kec')
	    				->on('p.kd_kab', '=', 'kl.kd_kab')
	    				->on('p.kd_prop', '=', 'kl.kd_prop');
	    	});
	    $s_pat = $req['columns'][1]['search']['value'];
	    $s_nama_usaha = $req['columns'][2]['search']['value'];
	    $s_npwp = $req['columns'][3]['search']['value'];
	    $s_alamat = $req['columns'][4]['search']['value'];
	    $s_kel = $req['columns'][5]['search']['value'];
	    $s_kec = $req['columns'][6]['search']['value'];
	    $s_kab = $req['columns'][7]['search']['value'];
	    $s_prop = $req['columns'][8]['search']['value'];
	    $s_tgl_pendataan = $req['columns'][9]['search']['value'];
	    // $s_periode = $req['columns'][10]['search']['value'];
	    $s_hutang = $req['columns'][11]['search']['value'];
	    // $search = $req['search']['value'];
	    if(!empty($s_nama_usaha)){
	    	$query = $query->where('nama_usaha', 'like', '%'.$s_nama_usaha.'%');
	    }
	    if(!empty($s_npwp)){
	    	$query = $query->where('npwpd', 'like', '%'.$s_npwp.'%');
	    }
	    if(!empty($s_alamat)){
	    	$query = $query->where('alamat_usaha', 'like', '%'.$s_alamat.'%');
	    }
	    if(!empty($s_kel)){
	    	$query = $query->where('kl.nama', 'like', '%'.$s_kel.'%');
	    }
	    if(!empty($s_kec)){
	    	$query = $query->where('kc.nama', 'like', '%'.$s_kec.'%');
	    }
	    if(!empty($s_kab)){
	    	$query = $query->where('kb.nama', 'like', '%'.$s_kab.'%');
	    }
	    if(!empty($s_prop)){
	    	$query = $query->where('pr.nama', 'like', '%'.$s_prop.'%');
	    }
	    if($req['type_action'] == 'penetapan' || $req['type_action'] == 'piutang'){
		    if(!empty($s_pat)){
		    	$query = $query->where('no_penetapan', 'like', '%'.$s_pat.'%');
		    }
		    if(!empty($s_tgl_pendataan)){
		    	$s_tgl_pendataan = $this->timeDB($s_tgl_pendataan);
		    	$query = $query->where('tgl_penetapan', 'like', '%'.str_replace(' 00:00:00', '', $s_tgl_pendataan).'%');
		    }
		}else if($req['type_action'] == 'pendataan'){
		    if(!empty($s_pat)){
		    	$query = $query->where('no_reg', 'like', '%'.$s_pat.'%');
		    }
		    if(!empty($s_tgl_pendataan)){
		    	$s_tgl_pendataan = $this->timeDB($s_tgl_pendataan);
		    	$query = $query->where('tgl_pendataan', 'like', '%'.str_replace(' 00:00:00', '', $s_tgl_pendataan).'%');
		    }
		}
	    if(!empty($s_hutang)){
	    	$query = $query->where('pajak_terhutang', 'like', '%'.$s_hutang.'%');
	    }

		if($req['type_action'] == 'piutang'){
	    	$query = $query->where('f_lunas', 'like', '0');
		}
		
	    $c_order = $req['order'][0]['column'];
	    $order = $req['order'][0]['dir'];
	    if($req['draw']!=1 && $c_order != 0){
	    	if($c_order==1){
		    	if($req['type_action'] == 'penetapan' || $req['type_action'] == 'piutang'){
		    		$_c_order = 'no_penetapan';
				}else if($req['type_action'] == 'pendataan'){
		    		$_c_order = 'no_reg';
				}
	    	}else if($c_order==2){
	    		$_c_order = 'nama_usaha';
	    	}else if($c_order==3){
	    		$_c_order = 'npwpd';
	    	}else if($c_order==4){
	    		$_c_order = 'alamat_usaha';
	    	}else if($c_order==5){
	    		$_c_order = 'kl.nama';
	    	}else if($c_order==6){
	    		$_c_order = 'kc.nama';
	    	}else if($c_order==7){
	    		$_c_order = 'kb.nama';
	    	}else if($c_order==8){
	    		$_c_order = 'pr.nama';
	    	}else if($c_order==9){
		    	if($req['type_action'] == 'penetapan' || $req['type_action'] == 'piutang'){
	    			$_c_order = 'tgl_penetapan';
				}else if($req['type_action'] == 'pendataan'){
	    			$_c_order = 'tgl_pendataan';
				}
	    	}else if($c_order==11){
	    		$_c_order = 'pajak_terhutang';
	    	}
	    	if(!empty($_c_order)){
	    		$query = $query->orderBy($_c_order, $order);
	    	}
	    }
	    $time_start = $this->timeDB($req['time_start']);
	    $time_stop = $this->timeDB($req['time_stop']);
    	if($req['type_action'] == 'penetapan' || $req['type_action'] == 'piutang'){
	    	$query = $query->whereBetween('tgl_penetapan', array($time_start, $time_stop));
		}else if($req['type_action'] == 'pendataan'){
	    	$query = $query->whereBetween('tgl_pendataan', array($time_start, $time_stop));
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
	    if(false){
  			$query = $query->toSql();
			dd($query);
		}
        // echo "<pre>".print_r($req['columns'][0]['search'], 1)."</pre>";
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
	public function timeDB($time){
		$times = explode('-', $time);
		$newTime = $times[2].'-'.$times[1].'-'.$times[0].' 00:00:00';
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
			$pajak = $pajak + $v->pajak_terhutang;
		}
	    return $pajak;
	}
}
