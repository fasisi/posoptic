<?php

class Laporan001Controller extends FController
{
  public function filters()
  {
    return array(
      array('application.filters.CheckSessionFilter'),
      array('application.filters.CheckLokasiUserFilter')
    );
  }
  
	public function actionIndex()
	{
	  $this->menuid = 36;
    $this->parentmenuid = 42; 
    $this->userid_actor = Yii::app()->request->cookies['userid_actor']->value;
    $this->idlokasi = Yii::app()->request->cookies['idlokasi']->value;
    $idgroup = FHelper::GetGroupId($this->userid_actor);
	  $this->bread_crumb_list = 
      '<li>Laporan</li>'.
      '<li>></li>'.
      '<li>Penjualan</li>'.
      '<li>></li>'.
      '<li>Penjualan Belum Lunas</li>';
      
    $this->layout = 'layout-baru';
    
    $daftar_lokasi = FHelper::GetLocationListData(false);
    
    $html = $this->renderPartial(
      'vfrm_laporan',
      array(
        'daftar_lokasi' => $daftar_lokasi
      ),
      true
    );
    
		$this->render(
		  'index',
		  array(
		    'TheContent' => $html
      )
    );
	}
	
	/*
	  actionHitung()
	  
	  Deskripsi
	  Menampilkan daftar record penjualan yang statusnya belum LUNAS dan nilai
	  transaksinya TIDAK nol.
	*/
	public function actionHitung()
	{
	  $awal = Yii::app()->request->getParam('awal');
	  $akhir = Yii::app()->request->getParam('akhir');
	  $idlokasi = Yii::app()->request->getParam('idlokasi');
	  
	  $command = Yii::app()->db->createCommand()
	    ->select('sales.*, cabang.name')
	    ->from('pos_sales sales')
	    ->join('mtr_branch cabang', 'cabang.branch_id = sales.branch_id')
	    ->where(
	      "sales.status <> 'LUNAS' AND
	      sales.total > 0 AND
	      sales.open_date >= :awal AND
	      sales.open_date <= :akhir AND
	      sales.branch_id = :idlokasi",
	      array(
	        ':awal' => date('Y-m-j 00:00:00', strtotime($awal)),
	        ':akhir' => date('Y-m-j 23:59:59', strtotime($akhir)),
	        ':idlokasi' => $idlokasi
        )
      );
    $command->order = "sales.open_date desc, cabang.name asc";
    
    $daftar_penjualan = $command->queryAll();
    
    $html = $this->renderPartial(
      'v_daftar_penjualan',
      array(
        'daftar_penjualan' => $daftar_penjualan
      ),
      true
    );
    
    echo CJSON::encode( array('html' => $html) );
	}

	// -----------------------------------------------------------
	// Uncomment the following methods and override them if needed
	/*
	public function filters()
	{
		// return the filter configuration for this controller, e.g.:
		return array(
			'inlineFilterName',
			array(
				'class'=>'path.to.FilterClass',
				'propertyName'=>'propertyValue',
			),
		);
	}

	public function actions()
	{
		// return external action classes, e.g.:
		return array(
			'action1'=>'path.to.ActionClass',
			'action2'=>array(
				'class'=>'path.to.AnotherActionClass',
				'propertyName'=>'propertyValue',
			),
		);
	}
	*/
}