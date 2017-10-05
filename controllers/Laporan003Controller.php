<?php

class Laporan003Controller extends FController
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
      '<li>Penjualan Outstanding Per Sales</li>';
      
    $this->layout = 'layout-baru';
    
    $daftar_sales = $this->GetListCreatedBy();
    $daftar_sales = CHtml::listData($daftar_sales, 'id', 'nama');
    
    $html = $this->renderPartial(
      'vfrm_laporan',
      array(
        'daftar_sales' => $daftar_sales
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
	  GetListCreatedBy()
	
    Deskripsi
    Fungsi untuk mengembalikan daftar sales yang diambil dari tabel pos_sales.create_by
    untuk digunakan menghitung laporan outstanding.
	*/
	private function GetListCreatedBy()
	{
	  $command = Yii::app()->db->createCommand()
	    ->select('created_by as id, created_by as nama')
	    ->from('pos_sales');
    $command->distinct = true;
    $command->group = "created_by";
    $command->order = "created_by";
    
    $hasil = $command->queryAll();
    
    return $hasil;
	}
	
	/*
	  actionHitung()
	  
	  Deskripsi
	  Mengolah data pada tabel pos_sales, untuk menghitung penjualan belum lunas
	  (outstanding) berdasarkan idsales, tanggal awal dan tanggal akhir.
	*/
	public function actionHitung()
	{
	  $created_by = Yii::app()->request->getParam('idsales');
	  
	  ini_set('max_execution_time', 0);
    
    //ambil daftar item dari view umur_item_inventory
    $command = Yii::app()->db->createCommand()
      ->select('*')
      ->from('pos_sales sales')
      ->where(
        'sales.status = "BAYAR" AND
        sales.created_by = :created_by',
        array(
          ':created_by' => $created_by
        )
      );
    $daftar_sales = $command->queryAll();
    
    foreach($daftar_sales as $sales)
    {
      //informasi yang mau ditampilkan:
      //1. idlokasi
      //2. tanggal transaksi
      //3. nomor invoice & order
      //4. nilai transaksi
      //5. nilai dibayarkan
      //6. sisa
      //7. tindakan (detail)
      
      $temp_data['branch_id'] = $sales['branch_id'];
      $temp_data['tanggal_transaksi'] = $sales['open_date'];
      $temp_data['invoice'] = $sales['invoice_no'];
      $temp_data['order'] = $sales['order_no'];
      $temp_data['nilai_transaksi'] = $sales['total'];
      $temp_data['nilai_dibayarkan'] = $sales['balance'];
      $temp_data['sisa'] = $sales['total'] - $sales['balance'];
      
      $data[] = $temp_data;
    }
    
    $html = $this->renderPartial(
      'v_daftar_outstanding',
      array(
        'data_outstanding' => $data
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