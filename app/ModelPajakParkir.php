<?php

namespace App;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;

class ModelPajakParkir extends Model
{
	public $table = 'pendataan_parkir';
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
			$query = $query->select(DB::raw('p.*, kc.nama as kec, kl.nama as kel'));
		}
		$query = $query
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
	    foreach ($req['columns'] as $k => $v) {
	    	$data = $v['data'];
	    	$search = $v['search']['value'];
	    	if($data=='no_penetapan'){
	    		$s_no_penetapan = $search;
	    	}else if($data=='no_reg'){
	    		$s_no_reg = $search;
	    	}else if($data=='nama_usaha'){
	    		$s_nama_usaha = $search;
	    	}else if($data=='npwpd'){
	    		$s_npwp = $search;
	    	}else if($data=='alamat_usaha'){
	    		$s_alamat = $search;
	    	}else if($data=='kel'){
	    		$s_kel = $search;
	    	}else if($data=='kec'){
	    		$s_kec = $search;
	    	}else if($data=='tgl_penetapan'){
	    		$s_tgl_penetapan = $search;
	    	}else if($data=='tgl_pendataan'){
	    		$s_tgl_pendataan = $search;
	    	}else if($data=='pajak_terhutang'){
	    		$s_hutang = $search;
	    	}else if($data=='tgl_jatuh_tempo'){
	    		$s_tgl_jatuh_tempo = $search;
	    	}else if($data=='omzet'){
	    		$s_omzet = $search;
	    	}else if($data=='persen_tarif'){
	    		$s_persen_tarif = $search;
	    	}else if($data=='jml_r2'){
	    		$s_jml_r2 = $search;
	    	}else if($data=='jml_r4'){
	    		$s_jml_r4 = $search;
	    	}
	    }
	    if(!empty($s_tgl_jatuh_tempo)){
		    $s_tgl_jatuh_tempo = $this->timeDB($s_tgl_jatuh_tempo);
	    	$query = $query->where('tgl_jatuh_tempo', '=', str_replace(' 00:00:00', '', $s_tgl_jatuh_tempo));
	    }
	    if(!empty($s_omzet)){
	    	$query = $query->where('omzet', 'like', '%'.$s_omzet.'%');
	    }
	    if(!empty($s_persen_tarif)){
	    	$query = $query->where('persen_tarif', '=', $s_persen_tarif);
	    }
	    if(!empty($s_jml_r2)){
	    	$query = $query->where('jml_r2', '=', $s_jml_r2);
	    }
	    if(!empty($s_jml_r4)){
	    	$query = $query->where('jml_r4', '=', $s_jml_r4);
	    }
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
	    if(!empty($s_no_penetapan)){
	    	$query = $query->where('no_penetapan', 'like', '%'.$s_no_penetapan.'%');
	    }
	    if(!empty($s_tgl_penetapan)){
	    	$s_tgl_penetapan = $this->timeDB($s_tgl_penetapan);
	    	$query = $query->where('tgl_penetapan', 'like', '%'.str_replace(' 00:00:00', '', $s_tgl_penetapan).'%');
	    }
	    if(!empty($s_no_reg)){
	    	$query = $query->where('no_reg', 'like', '%'.$s_no_reg.'%');
	    }
	    if(!empty($s_tgl_pendataan)){
	    	$s_tgl_pendataan = $this->timeDB($s_tgl_pendataan);
	    	$query = $query->where('tgl_pendataan', 'like', '%'.str_replace(' 00:00:00', '', $s_tgl_pendataan).'%');
	    }
	    if(!empty($s_hutang)){
	    	$query = $query->where('pajak_terhutang', 'like', '%'.$s_hutang.'%');
	    }

		if($req['type_action'] == 'piutang'){
	    	$query = $query->where('f_lunas', 'like', '0');
		}
		
	    $c_order = $req['order'][0]['column'];
	    $order = $req['order'][0]['dir'];
	    if($req['draw']!=1 && !empty($c_order) && $c_order > 0){
	    	if(!empty($c_order)){
	    		$f_order = $req['columns'][$c_order]['data'];
	    		if($f_order == 'kel'){
	    			$f_order = 'kl.nama';
	    		}else if($f_order == 'kec'){
	    			$f_order = 'kc.nama';
	    		}
	    		$query = $query->orderBy($f_order, $order);
	    	}
	    }
	    $time_start = $this->timeDB($req['time_start']);
	    $time_stop = $this->timeDB($req['time_stop']);
	    $starts = explode(' ', $time_start);
	    $stops = explode(' ', $time_stop);
    	if($req['type_action'] == 'penetapan' || $req['type_action'] == 'piutang'){
		    if(str_replace('-', '', $starts[0]) >= str_replace('-', '', $stops[0])){
		    	$query = $query->where('tgl_penetapan', '=', $starts[0]);
		    }else{
	    		$query = $query->whereBetween('tgl_penetapan', array($starts[0], $stops[0]));
		    }
		}else if($req['type_action'] == 'pendataan'){
		    if(str_replace('-', '', $starts[0]) >= str_replace('-', '', $stops[0])){
		    	$query = $query->where('tgl_pendataan', 'like', $time_start.'%');
		    }else{
	    		$query = $query->whereBetween('tgl_pendataan', array($time_start, $time_stop));
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
