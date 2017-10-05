<?php

/*
  Laporan006Controller
  
  Deskripsi
  Class untuk membuat laporan average stock.
  Average stock dihitung berdasarkan angka yang dicatat oleh modul KartuStock
  dan StockOpname.
  
*/
class Laporan006Controller extends FController
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
	  $this->menuid = 72;
    $this->parentmenuid = 9; 
    $this->userid_actor = Yii::app()->request->cookies['userid_actor']->value;
    $this->idlokasi = Yii::app()->request->cookies['idlokasi']->value;
    $idgroup = FHelper::GetGroupId($this->userid_actor);
	  $this->bread_crumb_list = 
      '<li>Laporan</li>'.
      '<li>></li>'.
      '<li>Inventory</li>'.
      '<li>></li>'.
      '<li>Average Stock</li>';
      
    $this->layout = 'layout-baru';
    
    $daftar_lokasi = FHelper::GetLocationListData(false);
    
    $html = $this->renderPartial(
      'vfrm_laporan',
      array(
        'daftar_lokasi' => $daftar_lokasi,
        'daftar_kategori' => FHelper::GetTipeProdukList(),
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
	  Menghitung stock rata-rata dari setiap barang yang tercatat di suatu cabang.
	  Stock rata-rata dihitung dalam rentang waktu tertentu dan menurut kategori
	  produk tertentu.
	*/
	public function actionHitung()
	{
	  set_time_limit(0);
	  
	  $awal = Yii::app()->request->getParam('tanggalawal');
	  $akhir = Yii::app()->request->getParam('tanggalakhir');
	  $idlokasi = Yii::app()->request->getParam('idlokasi');
	  $idkategori = Yii::app()->request->getParam('idkategori');
	  
	  $dt_awal = new DateTimeCalc("$awal 00:00:00", "Y-m-d H:i:s");
	  $dt_akhir = new DateTimeCalc("$akhir 23:59:59", "Y-m-d H:i:s");
	  
	  //ambil daftar barang yang tercatat kartu stock dalam rentang waktu yang dipilih
	  $command = Yii::app()->db->createCommand()
	    ->select('kartu.idinventory')
	    ->from('kartu_stock kartu')
	    ->join('inv_inventory inventory', 'inventory.id = kartu.idinventory')
	    ->where(
	      "kartu.tanggal >= :awal AND
	      kartu.tanggal <= :akhir AND
	      inventory.idkategori = :idkategori AND
	      kartu.idlokasi = :idlokasi",
	      array(
	        ':awal' => $dt_awal->date_time,
	        ':akhir' => $dt_akhir->date_time,
	        ':idkategori' => $idkategori,
	        ':idlokasi' => $idlokasi
        )
      );
    $command->distinct = true;
    $daftar_produk = $command->queryAll();
    
    
    
    //Hitung stock rata-rata dari setiap barang
    $hasil = array();
    foreach($daftar_produk as $barang)
    {
      //hitung rata-rata stock - begin
      
        $current = new DateTimeCalc("$awal 00:00:00", "Y-m-d H:i:s");
        Yii::log("Reset current : " . print_r($current, true), 'info');
        
        $total_stock = 0;
        $last_stock = 0;
        do
        {
          //ambil record stock
          Yii::log("Hitung stock : " . print_r($barang, true) . " " . print_r($current, true), 'info');
          Yii::log("current : " . print_r($current, true), 'info');
          Yii::log("awal : " . print_r($dt_awal, true), 'info');
          Yii::log("akhir : " . print_r($dt_akhir, true), 'info');
          
          $command = Yii::app()->db->createCommand()
            ->select('*')
            ->from('kartu_stock')
            ->where(
              "idinventory = :idinventory AND
              idlokasi = :idlokasi AND
              tanggal >= :awal AND
              tanggal <= :akhir",
              array(
                ':idinventory' => $barang['idinventory'],
                ':awal' => date("Y-m-d 00:00:00", $current->date_time_stamp),
                ':akhir' => date("Y-m-d 23:59:59", $current->date_time_stamp),
                ':idlokasi' => $idlokasi
              )
            );
          $data_stock = $command->queryRow();
          
          if($data_stock != false)
          {
             
            if($data_stock['stock_opname'] > 0)
            {
              $total_stock = $data_stock['stock_opname'];
              $last_stock = $data_stock['stock_opname'];
            }
            else
            {
              $total_stock += $data_stock['stock_akhir'];
              $last_stock = $data_stock['stock_akhir'];
            }
          }
          else
          {
            $total_stock += $last_stock;
          }
          
          $current->add('day', 1);
          
        }while($current->date_time_stamp < $dt_akhir->date_time_stamp);
      
        $jumlah_hari = ($dt_akhir->date_time_stamp - $dt_awal->date_time_stamp) / 86400;  //86400 = seconds in a day
        
        Yii::log("total_stock = $total_stock; jumlah_hari = $jumlah_hari", 'info');
        if($total_stock != 0 && $jumlah_hari > 0)
        {
          $rata_stock = $total_stock / $jumlah_hari;
        }
        else
        {
          $rata_stock = 0;
        }
      
      //hitung rata-rata stock - end
      
      //simpan hasil query ke array sementara
      $temp['idinventory'] = $barang['idinventory'];
      $temp['rata_stock'] = $rata_stock;
      $temp['days'] = $jumlah_hari;
      $temp['total'] = $total_stock;
      $hasil[] = $temp;
      
    }//loop barang yang tercatat kartu stock
	  
	  //tulis array hasil ke file xls
    $this->TulisKeXls($hasil);
    
    echo CJSON::encode( array(
      'status' => 'ok'
    ) );
	}
	
	/*
	  TulisKeXls($data)
	  
	  Deskripsi
	  Fungsi untuk menuliskan hasil perhitungan average stock ke xls
	*/
	
	private function TulisKeXls($data)
	{
	  $awal = Yii::app()->request->getParam('tanggalawal');
	  $akhir = Yii::app()->request->getParam('tanggalakhir');
	  $idlokasi = Yii::app()->request->getParam('idlokasi');
	  $idkategori = Yii::app()->request->getParam('idkategori');
	  
	  $nama_lokasi = FHelper::GetLocationName($idlokasi, true);
	  $tipe_produk = FHelper::GetTipeProdukText($idkategori);
	  
	  $xls = new PHPExcel();
    $ws = $xls->getActiveSheet();
    $ws->setTitle('Average Stock');
    $kolom = 0;
    $baris = 1;
    
    //tulis informasi laporan
    $ws->setCellValueByColumnAndRow($kolom, $baris, "Lokasi : $nama_lokasi"); $baris++;
    $ws->setCellValueByColumnAndRow($kolom, $baris, "Tanggal : $awal - $akhir"); $baris++;
    $ws->setCellValueByColumnAndRow($kolom, $baris, "Tipe produk : $tipe_produk"); $baris++;
    
    $baris++;
    
    //tulis informasi stock
    foreach($data as $record)
    {
      switch($idkategori)
      {
        case 1: //lensa
          //ambil informasi nama produk
          $nama_barang = FHelper::GetProdukName($record['idinventory']);
          $ukuran = FHelper::GetProdukUkuran($record['idinventory']);
          $rata_stock = $record['rata_stock'];
          $total_stock = $record['total'];
          $jumlah_hari = $record['days'];
          
          $ws->setCellValueByColumnAndRow(
            $kolom, 
            $baris, 
            "$nama_barang - idinventory = {$record['idinventory']}"
          ); $kolom++;
          
          $ws->setCellValueByColumnAndRow(
            $kolom, 
            $baris, 
            $ukuran
          ); $kolom++;
          
          $ws->setCellValueByColumnAndRow(
            $kolom, 
            $baris, 
            $rata_stock
          ); $kolom++;
          
          $ws->setCellValueByColumnAndRow(
            $kolom, 
            $baris, 
            $total_stock
          ); $kolom++;
          
          $ws->setCellValueByColumnAndRow(
            $kolom, 
            $baris, 
            $jumlah_hari
          ); $kolom++;
          
          $baris++;
          $kolom = 0;
          
          break;
          
        case 2: //softlens
          //ambil informasi nama produk
          $nama_barang = FHelper::GetProdukName($record['idinventory']);
          $ukuran = FHelper::GetProdukUkuran($record['idinventory']);
          $rata_stock = $record['rata_stock'];
          $total_stock = $record['total'];
          $jumlah_hari = $record['days'];
          
          $ws->setCellValueByColumnAndRow(
            $kolom, 
            $baris, 
            "$nama_barang - idinventory = {$record['idinventory']}"
          ); $kolom++;
          
          $ws->setCellValueByColumnAndRow(
            $kolom, 
            $baris, 
            $ukuran
          ); $kolom++;
          
          $ws->setCellValueByColumnAndRow(
            $kolom, 
            $baris, 
            $rata_stock
          ); $kolom++;
          
          $ws->setCellValueByColumnAndRow(
            $kolom, 
            $baris, 
            $total_stock
          ); $kolom++;
          
          $ws->setCellValueByColumnAndRow(
            $kolom, 
            $baris, 
            $jumlah_hari
          ); $kolom++;
          
          $baris++;
          $kolom = 0;
          break;
          
        case 3: //frame
          //ambil informasi nama produk
          $nama_barang = FHelper::GetProdukName($record['idinventory']);
          $ukuran = FHelper::GetProdukUkuran($record['idinventory']);
          $rata_stock = $record['rata_stock'];
          $total_stock = $record['total'];
          $jumlah_hari = $record['days'];
          
          $ws->setCellValueByColumnAndRow(
            $kolom, 
            $baris, 
            "$nama_barang - idinventory = {$record['idinventory']}"
          ); $kolom++;
          
          $ws->setCellValueByColumnAndRow(
            $kolom, 
            $baris, 
            $ukuran
          ); $kolom++;
          
          $ws->setCellValueByColumnAndRow(
            $kolom, 
            $baris, 
            $rata_stock
          ); $kolom++;
          
          $ws->setCellValueByColumnAndRow(
            $kolom, 
            $baris, 
            $total_stock
          ); $kolom++;
          
          $ws->setCellValueByColumnAndRow(
            $kolom, 
            $baris, 
            $jumlah_hari
          ); $kolom++;
          
          $baris++;
          $kolom = 0;
          break;
          
        default: //default
          //ambil informasi nama produk
          $nama_barang = FHelper::GetProdukName($record['idinventory']);
          $ukuran = FHelper::GetProdukUkuran($record['idinventory']);
          $rata_stock = $record['rata_stock'];
          $total_stock = $record['total'];
          $jumlah_hari = $record['days'];
          
          $ws->setCellValueByColumnAndRow(
            $kolom, 
            $baris, 
            "$nama_barang - idinventory = {$record['idinventory']}"
          ); $kolom++;
          
          $ws->setCellValueByColumnAndRow(
            $kolom, 
            $baris, 
            $ukuran
          ); $kolom++;
          
          $ws->setCellValueByColumnAndRow(
            $kolom, 
            $baris, 
            $rata_stock
          ); $kolom++;
          
          $ws->setCellValueByColumnAndRow(
            $kolom, 
            $baris, 
            $total_stock
          ); $kolom++;
          
          $ws->setCellValueByColumnAndRow(
            $kolom, 
            $baris, 
            $jumlah_hari
          ); $kolom++;
          
          $baris++;
          $kolom = 0;
          break;
      } //switch idkategori
    } //loop record
    
    //kembalikan nama file
    $file_name = "average_stock_{$tipe_produk}_{$nama_lokasi}_{$awal}_{$akhir}.xlsx";
    
    $writer = new PHPExcel_Writer_Excel2007($xls);
    
    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment;filename="'.$file_name.'"');
    header('Cache-Control: max-age=0');
      
    $writer->save('php://output');
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