<?php

/*
  KartustockController
  
  Deskripsi
  Class kusus untuk menghitung stok awal, jumlah transaksi in, jumlah transaksi out terhadap
  item-item yang dilakukan dalam suatu hari.
  
  Stok awal menghitung selisih in-out yang terjadi sejak awal jaman hingga saat
  penghitungan.
  
  Jumlah transaksi in, menghitung total transaksi in sejak tanggal stock terakhir.
  Jumlah transaksi out, menghitung total transaksi out sejak tanggal stock terakhir.
  
  Data dihitung dari tabel inv_status_history. Dirangkum menjadi informasi
  kartu stock.
*/

class KartustockController extends FController
{
  public function actionIndex()
	{
	  /*
	    1. ambil list idlokasi
	    2. ambil distinct idinventory dari tabel inv_status_history berdasarkan idlokasi dan rentang waktu
	    3. periksa apakah sudah ada record kartu_stock berdasarkan idinventory, idlokasi dan tanggal
	    4. jika belum ada, hitung stock awal untuk idinventory tersebut.
	       jika sudah ada, lanjut ke langkah 5 
	    
	    5. ambil stock akhir / stock opname terakhir berdasarkan idinventory, 
	       idlokasi dan tanggal. gunakan angka tersebut sebagai stock awal pada
	       record kartu stock saat ini.
	    6. hitung total in dari tabel inv_status_history berdasarkan idlokasi dan rentang waktu
	    7. hitung total out dari tabel inv_status_history berdasarkan idlokasi dan rentang waktu
	    8. hitung in - out = stock. simpan sebagai stock_akhir di tabel kartu_stock
	  */
	  
	  $command = Yii::app()->db->createCommand()
	    ->select("*")
	    ->from('mtr_branch');
	  $command->order = "name asc";
	  
	  $daftar_lokasi = $command->queryAll();
	  $tanggal = date('Y-m-j', strtotime("-1 days"));
	  
	  foreach($daftar_lokasi as $lokasi)
	  {
	    $idlokasi = $lokasi['branch_id'];
	    
	    //Mengambil daftar idinventory yang tercatat dalam sehari
	    $command = Yii::app()->db->createCommand()
	      ->select("item.idinventory")
	      ->from('inv_status_history history')
	      ->join('inv_item item', 'history.iditem = item.id')
	      ->where(
	        "history.idlokasi = :idlokasi AND
	        history.waktu >= :awal AND
	        history.waktu <= :akhir",
	        array(
	          ':idlokasi' => $idlokasi,
	          ':awal' => "$tanggal 00:00:00",
	          ':akhir' => "$tanggal 23:59:59"
          )
        );
      $command->distinct = true;
      $command->group = "item.idinventory";
      $daftar_inventory = $command->queryAll();
      
      foreach($daftar_inventory as $inventory)
      {
        $idinventory = $inventory['idinventory'];
        
        //periksa apakah sudah ada record inventory pada tabel kartu_stock
        $command = Yii::app()->db->createCommand()
          ->select("count(*) as jumlah")
          ->from("kartu_stock")
          ->where(
            "idlokasi = :idlokasi AND
            idinventory = :idinventory AND
            tanggal = :tanggal",
            array(
              ':idlokasi' => $idlokasi,
              ':idinventory' => $idinventory,
              ':tanggal' => "$tanggal 00:00:00"
            )
          );
        $hasil = $command->queryRow();
        
        if($hasil['jumlah'] == 0)
        {
          $insert = true;
          
          //hitung stock awal untuk inventory
          
          //cek apakah ada kartu_stock sebelum hari ini?
          $command = Yii::app()->db->createCommand()
            ->select("max(tanggal) as tanggal")
            ->from('kartu_stock')
            ->where(
              'idlokasi = :idlokasi AND
              idinventory = :idinventory AND
              tanggal < :tanggal',
              array(
                ':idlokasi' => $idlokasi,
                ':idinventory' => $idinventory,
                ':tanggal' => "$tanggal 00:00:00"
              )
            );
          $hasil = $command->queryRow();
          
          if( is_null($hasil['tanggal']) )
          {
            //tidak ada record pada kartu stock. hitung stock dari awal jaman
            
            //hitung total IN
            $total_masuk = $this->HitungStock1(
              $idlokasi, 
              $idinventory, 
              2, 
              "$tanggal 00:00:00"
            );
            
            //hitung total OUT
            $total_keluar = $this->HitungStock1(
              $idlokasi, 
              $idinventory, 
              3, 
              "$tanggal 00:00:00"
            );
            
            $stock_awal = 0;
            $stock_akhir = $total_masuk - $total_keluar;
          }
          else
          {
            //ada record pada kartu stock.
            
            //hitung stock antara tanggal tersebut dan hari ini
            
            $tanggal_terakhir = date('Y-m-j', strtotime($hasil['tanggal']));
            
            //ambil stock akhirnya sebagai stock awal kali ini
            
            $command = Yii::app()->db->createCommand()
              ->select('*')
              ->from('kartu_stock')
              ->where(
                'idlokasi = :idlokasi AND
                idinventory = :idinventory AND
                tanggal = :tanggal',
                array(
                  ':idlokasi' => $idlokasi,
                  ':idinventory' => $idinventory,
                  ':tanggal' => "$tanggal_terakhir 00:00:00"
                )
              );
            $hasil = $command->queryRow();
            $stock_awal = ($hasil['stock_opname'] > -1 ? $hasil['stock_opname'] : $hasil['stock_akhir']);
            
            //hitung total IN
            $total_masuk = $this->HitungStock2(
              $idlokasi,
              $idinventory,
              2,
              "$tanggal_terakhir 00:00:00",
              "$tanggal 23:59:59"
            );
            
            //hitung total OUT
            $total_keluar = $this->HitungStock2(
              $idlokasi,
              $idinventory,
              3,
              "$tanggal_terakhir 00:00:00",
              "$tanggal 23:59:59"
            );
            
            $stock_akhir = $stock_awal + $total_masuk - $total_keluar;
            
          }
        }
        else
        {
          //ada record pada kartu stock
          
          $insert = false;
          
          //ambil stock akhir dari record kartu_stock sebelumnya
          
          $command = Yii::app()->db->createCommand()
            ->select('max(tanggal) as tanggal')
            ->from('kartu_stock')
            ->where(
              "idlokasi = :idlokasi AND
              idinventory = :idinventory AND
              tanggal < :tanggal",
              array(
                ':idlokasi' => $idlokasi,
                ':idinventory' => $idinventory,
                ':tanggal' => "$tanggal 00:00:00"
              )
            );
          $hasil = $command->queryRow();
          $tanggal_terakhir = date('Y-m-j', strtotime($hasil['tanggal']));
          
          $command = Yii::app()->db->createCommand()
            ->select("*")
            ->from('kartu_stock')
            ->where(
              "idlokasi = :idlokasi AND
              idinventory = :idinventory AND
              tanggal = :tanggal",
              array(
                ':idlokasi' => $idlokasi,
                ':idinventory' => $idinventory,
                ':tanggal' => "$tanggal_terakhir 00:00:00"
              )
            );
          $hasil = $command->queryRow();
          
          $stock_awal = ($hasil['stock_opname'] > -1 ? $hasil['stock_opname'] : $hasil['stock_akhir']);
          
          //hitung total IN
          $total_masuk = $this->HitungStock2(
            $idlokasi,
            $idinventory,
            2,
            "$tanggal_terakhir 00:00:00",
            "$tanggal 23:59:59"
          );
          
          //hitung total OUT
          $total_keluar = $this->HitungStock2(
            $idlokasi,
            $idinventory,
            3,
            "$tanggal_terakhir 00:00:00",
            "$tanggal 23:59:59"
          );
          
          $stock_akhir = $stock_awal + $total_masuk - $total_keluar;
          
        } //hitung stock awal atau ambil stock akhir???
        
        $stock_awal = ( is_null($stock_awal) ? 0 : $stock_awal);
        
        //insert atau update record kartu stock???
        if($insert == true)
        {
          Yii::app()->db->createCommand()
            ->insert(
              "kartu_stock",
              array(
                'idlokasi' => $idlokasi,
                'idinventory' => $idinventory,
                'tanggal' => "$tanggal 00:00:00",
                'total_in' => ( is_null($total_masuk) ? 0 : $total_masuk),
                'total_out' => ( is_null($total_keluar) ? 0 : $total_keluar),
                'stock_awal' => $stock_awal,
                'stock_akhir' => $stock_akhir,
                'stock_kartu_time' => date('Y-m-j H:i:s')
              )
            );
        }
        else
        {
          Yii::app()->db->createCommand()
            ->update(
              'kartu_stock',
              array(
                'total_in' => ( is_null($total_masuk) ? 0 : $total_masuk),
                'total_out' => ( is_null($total_keluar) ? 0 : $total_keluar),
                'stock_awal' => $stock_awal,
                'stock_akhir' => $stock_akhir,
                'stock_kartu_time' => date('Y-m-j H:i:s')
              ),
              "idlokasi = :idlokasi AND
              idinventory = :idinventory AND
              tanggal = :tanggal",
              array(
                ':idlokasi' => $idlokasi,
                ':idinventory' => $idinventory,
                ':tanggal' => "$tanggal 00:00:00",
              )
            );
        } //insert atau update kartu_stock
        
      } //loop inventory
	    
	  } //loop lokasi
	}
	
	private function HitungStock1($idlokasi, $idinventory, $status, $waktu)
	{
	  $command = Yii::app()->db->createCommand()
      ->select("count(history.iditem) as jumlah")
      ->from('inv_status_history history')
      ->join('inv_item item', 'history.iditem = item.id')
      ->where(
        "history.idlokasi = :idlokasi AND
        item.idinventory = :idinventory AND
        history.idstatus = :status AND
        history.waktu < :tanggal", 
        array(
          ':idlokasi' => $idlokasi,
          ':idinventory' => $idinventory,
          ':status' => $status,
          ':tanggal' => $waktu
        )
      );
    $hasil = $command->queryRow();
    
    return ($hasil['jumlah'] == null ? 0 : $hasil['jumlah']);
	}
	
	private function HitungStock2($idlokasi, $idinventory, $status, $waktu1, $waktu2)
	{
	  $command = Yii::app()->db->createCommand()
      ->select("count(history.iditem) as jumlah")
      ->from('inv_status_history history')
      ->join('inv_item item', 'history.iditem = item.id')
      ->where(
        "history.idlokasi = :idlokasi AND
        item.idinventory = :idinventory AND
        history.idstatus = :status AND
        history.waktu >= :awal AND
        history.waktu <= :akhir", 
        array(
          ':idlokasi' => $idlokasi,
          ':idinventory' => $idinventory,
          ':status' => $status,
          ':awal' => $waktu1,
          ':akhir' => $waktu2
        )
      );
    $hasil = $command->queryRow();
    
    return ($hasil['jumlah'] == null ? 0 : $hasil['jumlah']);
	}
	
	
	/*
	  actionShowForm()
	  
	  Deskripsi
	  Menampilkan form untuk melihat record kartu stock.
	  Pada form ada:
	  1. dropdown lokasi
	  2. dropdown kategori barang, 
	  3. text field pencarian nama barang
	  4. drop down untuk memilih barang.
	  5. text field untuk memilih tanggal awal dan akhir
	  6. tombol pencarian
	*/
	public function actionShowForm()
	{
	  $this->bread_crumb_list = '
      <li>Inventory</li>
      <li>></li>
      <li>Kartu Stock</li>';

    $this->layout = 'layout-baru';
			
    $html = $this->renderPartial(
      'vfrm_kartustock',
      array(
        'daftar_lokasi' =>  FHelper::GetLocationListData(false),
	      'daftar_kategori' => FHelper::GetTipeProdukList()
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
	  actionCariBarang()
	  
	  Deskripsi
	  Fungsi untuk mencari daftar barang berdasarkan kategori dan teks yang
	  diketik. Teks mengandung nama dan angka spek barang. Tiru modif algoritma
	  yang dipakai pada modul sales.
	*/
	public function actionCariBarang()
	{
	  $namabarang = Yii::app()->request->getParam('namabarang');
		$dropdownlist = array();
		//$dropdownlist[-1] = 'Pilih produk...';
		
		if( trim($namabarang) != '')
		{
		  //explode string menjadi 2 array. Array pertama berisi string saja. Array kedua berisi numeric
		  //lakukan loop pencarian berdasarkan array nama.
		  //pada setiap loop nama, lakukan pencarian berdasarkan array ukuran.
		  
		  $array_temp = explode(" ", trim($namabarang));
		  
		  $array_numeric = array();
		  $array_string = array();
		  foreach($array_temp as $test)
		  {
		    if( is_numeric($test))
		    {
		      $array_numeric[] = $test;
		    }
		    else
		    {
		      $array_string[] = $test;
		    }
		  }
		  
		  foreach($array_string as $test_string)
		  {
		    $command = Yii::app()->db->createCommand()
        ->select('produk.*')
        ->from('inv_inventory produk')
        ->where(
          "produk.nama like :nama AND
          produk.is_del = 0", 
          array(
          ':nama' => "%$test_string%"
          ))
        ->order('produk.nama');
        //->limit('100');
        $daftar_produk = $command->queryAll();
        
        foreach($daftar_produk as $produk)
        {
          $idproduk = $produk['id'];
          
          $real_nama_produk = FHelper::GetProdukName($idproduk);
          $real_brand_produk = FHelper::GetProdukBrand($idproduk);
          $real_ukuran_produk = FHelper::GetProdukUkuran($idproduk);
          $barang = $real_nama_produk . ' | ' .
                $real_brand_produk . ' | ' .
                $real_ukuran_produk;
                
          if( count($array_numeric) > 0 )
          {
            //lakukan pencarian string menggunakan array_numeric
            foreach($array_numeric as $test)
            {
              if(strpos($barang, $test))
              {
                $temp['id'] = $idproduk;
                $temp['value'] = $barang;
                $dropdownlist[] = $temp;
              }
            } //loop array_numeric
          }
          else
          {
            $temp['id'] = $idproduk;
            $temp['value'] = $barang;
            $dropdownlist[] = $temp;
          }
              
        }//loop produk
		  } //loop array_string
  
      if (empty($daftar_produk)) 
      {
        $temp['id'] = 0;
        $temp['value'] = 'Barang tidak ditemukan';
        $dropdownlist[] = $temp;
		  }
		}// if namabarang not empty
		
		echo CJSON::encode(
		  array(
			'dropdownlist' => $dropdownlist
		  )
		);
	}
	
	
	/*
	  actionCariKartuStock()
	  
	  Deskripsi
	  Fungsi untuk mengembalikan view tabel, berisi daftar kartu stock berdasarkan
	  idinventory, tanggal awal, tanggal akhir dan lokasi.
	*/
	public function actionCariKartuStock()
	{
	  $idlokasi = Yii::app()->request->getParam('idlokasi');
	  $idinventory = Yii::app()->request->getParam('idinventory');
	  $awal = Yii::app()->request->getParam('awal');
	  $akhir = Yii::app()->request->getParam('akhir');
	  
	  $command = Yii::app()->db->createCommand()
	    ->select('*')
	    ->from('kartu_stock')
	    ->where(
	      "idlokasi = :idlokasi AND
	      idinventory = :idinventory AND
	      tanggal >= :awal AND
	      tanggal <= :akhir",
	      array(
	        ':idlokasi' => $idlokasi,
	        ':idinventory' => $idinventory,
	        ':awal' => "$awal 00:00:00",
	        ':akhir' => "$akhir 23:59:59",
        )
      );
    $command->order = "tanggal asc";
    
    $hasil = $command->queryAll();
    
    $html = $this->renderPartial(
      'v_daftar_kartu_stock',
      array(
        'daftar_kartu_stock' => $hasil
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