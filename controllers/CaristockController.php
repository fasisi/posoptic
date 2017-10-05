<?php

class CaristockController extends FController
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
	  
	  if(FHelper::AllowMenu(70, $groupid, 'read'))
	  {
	    $this->bread_crumb_list = "Inventory > Cari Stock";
	    $this->layout = "layout-baru";
	    $this->render('index');
	  }
	  else
	  {
	    $this->redirect('?r=index/showinvalidaccess');
	  }
	  
	  
	}
	
	public function actionAmbilDaftarProduk()
	{
	  $groupid = FHelper::GetGroupId($this->userid_actor);
	  
	  if(FHelper::AllowMenu(70, $groupid, 'read'))
	  {
	    //ambil idproduknya
	    $nama = Yii::app()->request->getParam('nama');
	    
	    //ambil informasi barcode
	    $command = Yii::app()->db->createCommand()
	      ->select('produk.nama, produk.id as idproduk')
	      ->from('inv_inventory produk')
	      ->where(
	        'produk.nama like :nama AND
	        produk.is_del = 0', 
	        array(':nama' => "%$nama%"))
	      ->order('nama asc');
	      
      $daftar_produk = $command->queryAll();
      
      foreach($daftar_produk as $key => $produk)
      {
        $ukuran = FHelper::GetProdukUkuran($produk['idproduk']);
        $daftar_produk[$key]['ukuran'] = $ukuran;
      }
      
      $status = 'ok';
	  }
	  else
	  {
	    $status = 'not ok';
	  }
	  
	  echo CJSON::encode(array('daftar_produk' => $daftar_produk, 'status' => $status));
	}
	
	public function actionCariStock()
	{
	  $groupid = FHelper::GetGroupId($this->userid_actor);
	  
	  if(FHelper::AllowMenu(70, $groupid, 'read'))
	  {
	    //ambil idproduknya
	    $idproduk = Yii::app()->request->getParam('idproduk');
	    
	    //ambil informasi barcode
	    $command = Yii::app()->db->createCommand()
	      ->select('lokasi.name as nama, count(item.id) as jumlah')
	      ->from('inv_item item')
	      ->join('mtr_branch lokasi', 'lokasi.branch_id = item.idlokasi')
	      ->where(
	        'item.idinventory = :idproduk AND
	        item.idstatus = 3', 
	        array(':idproduk' => $idproduk))
	      ->order('lokasi.name asc');
	    $command->group = 'item.idlokasi';
	      
      $daftar_stock = $command->queryAll();
      
      $ukuran = FHelper::GetProdukUkuran($idproduk);
      
      $html = $this->renderPartial(
        'v_cari_stock',
        array(
          'daftar_stock' => $daftar_stock,
          'ukuran' => $ukuran
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