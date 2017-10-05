<?php

class InfobarcodeController extends FController
{
  public function filters()
  {
    $this->userid_actor = Yii::app()->request->cookies['userid_actor']->value;
    $this->idlokasi = Yii::app()->request->cookies['idlokasi']->value;
    
    return array(
      array('CheckSessionFilter')
    );
  }
  
	public function actionIndex()
	{
	  $groupid = FHelper::GetGroupId($this->userid_actor);
	  
	  if(FHelper::AllowMenu(64, $groupid, 'read'))
	  {
	    $this->bread_crumb_list = "Inventory > Informasi Barcode";
	    $this->layout = "layout-baru";
	    $this->render('index');
	  }
	  else
	  {
	    $this->redirect('?r=index/showinvalidaccess');
	  }
	  
	  
	}
	
	public function actionAmbilInfo()
	{
	  $groupid = FHelper::GetGroupId($this->userid_actor);
	  
	  if(FHelper::AllowMenu(64, $groupid, 'read'))
	  {
	    //ambil barcodenya
	    $barcode = Yii::app()->request->getParam('barcode');
	    
	    //ambil informasi barcode
	    $command = Yii::app()->db->createCommand()
	      ->select('produk.id, produk.idkategori, produk.nama, lokasi.name as lokasi')
	      ->from('inv_item item')
	      ->join('inv_inventory produk', 'item.idinventory = produk.id')
	      ->leftJoin('mtr_branch lokasi', 'item.idlokasi = lokasi.branch_id')
	      ->where('item.barcode = :barcode', array(':barcode' => $barcode));
	      
      $produk = $command->queryRow();
      
      $ukuran = FHelper::GetProdukUkuran($produk['id']);
      
      //ambil sejarah barcode dari tabel inv_status_history
      $command = Yii::app()->db->createCommand()
        ->select('sejarah.*, item.barcode, lokasi.name')
        ->from('inv_item item')
        ->join('inv_status_history sejarah', 'item.id = sejarah.iditem')
        ->join('mtr_branch lokasi', 'sejarah.idlokasi = lokasi.branch_id')
        ->where(
          'item.barcode = :barcode',
          array(':barcode' => $barcode)
        );
      $command->order = 'sejarah.waktu asc';
      $sejarah = $command->queryAll();
      
      foreach($sejarah as $record)
      {
        $temp = array();
        $temp['waktu'] = date('Y-m-d H:i:s', strtotime($record['waktu']));
        $temp['lokasi'] = $record['name'];
        
        switch($record['idstatus'])
        {
          case 1: //gudang
            $status = 'Gudang / Kantor Pusat';
            break;
            
          case 2: //keluar
            $status = 'Keluar';
            break;
            
          case 3: //masuk
            $status = 'Masuk';
            break;
            
          case 4: //customer
            $status = 'Customer';
            break;
        }
        
        $temp['status'] = $status;
        
        //jika di customer, cari record invoice
        $invoice = '';
        if($record['idstatus'] == 4)
        {
          $command = Yii::app()->db->createCommand()
            ->select('*')
            ->from('pos_sales')
            ->where(
              'sales_id = :idsales', 
              array(':idsales' => $record['idsales'])
            );
          $sales = $command->queryRow();
          
          if($sales != false)
          {
            $invoice = 'Nomor order : ' . $sales['order_no'];
          }
          else
          {
            $invoice = "Nomor order tidak ditemukan";
          }
          
          $temp['invoice'] = $invoice;
          
        }// jika status = customer
        
        $data[] = $temp;
        
      } //loop sejarah
      
      $html = $this->renderPartial(
        'v_informasi_barcode',
        array(
          'produk' => $produk,
          'ukuran' => $ukuran,
          'sejarah' => $data
        ),
        true
      );
      
      $status = 'ok';
	  }
	  else
	  {
	    $status = 'not ok';
	  }
	  
	  echo CJSON::encode(array('html' => $html, 'status' => $status));
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