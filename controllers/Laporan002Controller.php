<?php

/*
  Menghitung umur item dalam satuan hari.
  Umur dihitung sejak barang diberikan barcode.
  Data didapat dari tabel inv_status_history
*/

class Laporan002Controller extends FController
{
  public function filters()
  {
    return array(
      array('application.filters.CheckSessionFilter + index, hitung'),
      array('application.filters.CheckLokasiUserFilter + index, hitung')
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
      
    $daftar_lokasi = FHelper::GetLocationListData(false);
    $daftar_supplier = FHelper::GetSupplierListData();
      
    $this->layout = 'layout-baru';
    
    $html = $this->renderPartial(
      'vfrm_laporan',
      array(
        'daftar_lokasi' => $daftar_lokasi,
        'daftar_supplier' => $daftar_supplier,
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
	  Mengolah data pada tabel umur_item_inventory_olahan, untuk disajikan dalam
	  bentuk tabel. Gunakan cache untuk mempersingkat penampilan hasil.
	*/
	public function actionHitung()
	{
	  $tipeproduk = Yii::app()->request->getParam('tipeproduk');
	  $idsupplier = Yii::app()->request->getParam('idsupplier');
	  $idlokasi = Yii::app()->request->getParam('idlokasi');
	  $cache_key = "Laporan002_Hitung_{$tipeproduk}_{$idsupplier}_{$idlokasi}";
	  
	  $cache = Yii::app()->cache;
	  $html = $cache->get($cache_key);
	  //$html = false;
	  
	  if($html == false)
	  {
	    ini_set('max_execution_time', 0);
      
      //ambil daftar item dari view umur_item_inventory
      $command = Yii::app()->db->createCommand()
        ->select('inventory.id as idinventory, inventory.nama, inventory.brand')
        ->from('umur_item_inventory_olahan umur')
        ->join('inv_inventory inventory', 'inventory.id = umur.idinventory')
        ->where(
          'inventory.idkategori = :idkategori AND
          inventory.idsupplier = :idsupplier AND
          umur.idlokasi = :idlokasi',
          array(
            ':idkategori' => $tipeproduk,
            ':idsupplier' => $idsupplier,
            ':idlokasi' => $idlokasi
          )
        );
      $command->distinct = true;
      //$command->limit = 10;
      $daftar_item = $command->queryAll();
      
      foreach($daftar_item as $item)
      {
        $ukuran = FHelper::GetProdukUkuranMini($item['idinventory']);
        $idinventory = $item['idinventory'];
        
        //hitung umur 7 hari >= X
        $command = Yii::app()->db->createCommand()
          ->select('count(idinventory) as jumlah')
          ->from('umur_item_inventory_olahan umur')
          ->join('inv_inventory inventory', 'inventory.id = umur.idinventory')
          ->where(
            'kategori_umur = 1 AND 
            umur.idinventory = :idinventory',
            array(
              ':idinventory' => $idinventory
            )
          );
        $hasil = $command->queryRow();
        $jumlah_1 = $hasil['jumlah'];
          
        //hitung umur 7 hari < X <= 30 hari
        $command = Yii::app()->db->createCommand()
          ->select('count(idinventory) as jumlah')
          ->from('umur_item_inventory_olahan umur')
          ->join('inv_inventory inventory', 'inventory.id = umur.idinventory')
          ->where(
            'kategori_umur = 2 AND 
            umur.idinventory = :idinventory',
            array(
              ':idinventory' => $idinventory
            )
          );
        $hasil = $command->queryRow();
        $jumlah_2 = $hasil['jumlah'];
        
        //hitung umur 30 hari < X <= 90 hari
        $command = Yii::app()->db->createCommand()
          ->select('count(idinventory) as jumlah')
          ->from('umur_item_inventory_olahan umur')
          ->join('inv_inventory inventory', 'inventory.id = umur.idinventory')
          ->where(
            'kategori_umur = 3 AND 
            umur.idinventory = :idinventory',
            array(
              ':idinventory' => $idinventory
            )
          );
        $hasil = $command->queryRow();
        $jumlah_3 = $hasil['jumlah'];
        
        $temp_data['idinventory'] = $idinventory;
        $temp_data['nama'] = $item['nama'];
        $temp_data['brand'] = $item['brand'];
        $temp_data['ukuran'] = $ukuran;
        $temp_data['umur1'] = $jumlah_1;
        $temp_data['umur2'] = $jumlah_2;
        $temp_data['umur3'] = $jumlah_3;
        
        $data[] = $temp_data;
      }
      
      $html = $this->renderPartial(
        'v_daftar_umur',
        array(
          'data_umur' => $data
        ),
        true
      );
      
      $cache->add($cache_key, $html, (24 * 60 * 60));
	  }
    
    echo CJSON::encode( array('html' => $html) );
	}
	
	/*
	  actionGenerateData()
	  
	  Deskripsi
	  Fungsi untuk mengolah data dan menyimpannya dalam tabel kusus. Tabel ini
	  kemudian akan dipakai oleh actionHitung. Gunanya untuk mempercepat proses
	  menampilkan data. Karena proses query melibatkan banyak resource. Maka
	  perlu dilakukan caching.
	*/
	public function actionGenerateData()
	{
	  /*
	    1. query idinventory, tanggal_awal, tanggal_akhir, selisih
	       ambil data dari view umur_item_inventory
	    2. ambil record dari langkah #1 yang selisihnya <= 90
	    3. olah hasil langkah #2 menjadi jumlah produk berdasarkan kategori umur 1 .. 4
	    4. simpan hasil langkah #3 ke dalam tabel umur_item_inventory_olahan
	  */
	  
	  ini_set('max_execution_time', 0);
	  
	  //0. kosongkan tabel umur_item_inventory_olahan
	  Yii::app()->db->createCommand()
	    ->delete('umur_item_inventory_olahan');
	  
	  //1. query idinventory, tanggal_awal, tanggal_akhir, selisih
	  //2. ambil record dari langkah #1 yang selisihnya <= 90
	  $command = Yii::app()->db->createCommand()
	    ->select('umur.*, item.idinventory')
	    ->from('umur_item_inventory umur')
	    ->join('inv_item item', 'item.id = umur.iditem')
	    ->where('selisih <= 90');
	  $langkah2 = $command->queryAll();
	  
	  //3. olah hasil langkah #2 menjadi jumlah produk berdasarkan kategori umur 1 .. 4
	  //4. simpan hasil langkah #3 ke dalam tabel umur_item_inventory_olahan
	  foreach($langkah2 as $data)
	  {
	    $umur = $data['selisih'];
	    switch($umur)
	    {
        case ($umur >= 0 && $umur <= 7) :
          $kategori_umur = 1;
          break;
          
        case ($umur > 7 && $umur <= 30) :
          $kategori_umur = 2;
          break;
          
        case ($umur >= 31 && $umur <= 90) :
          $kategori_umur = 3;
          break;
	    }
	    
	    Yii::app()->db->createCommand()
        ->insert(
          'umur_item_inventory_olahan',
          array(
            'idinventory' => $data['idinventory'],
            'idlokasi' => $data['idlokasi'],
            'kategori_umur' => $kategori_umur
          )
        );
	  }//loop record langkah2
	  
	  //selesai
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