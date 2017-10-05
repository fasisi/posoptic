<?php

class LaporanController extends FController
{
  private $xlsStock, $wsStock;
  
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
      '<li>Inventory</li>'.
      '<li>></li>'.
      '<li>Posisi Stock</li>';
             
	  $daftar_lokasi = FHelper::GetLocationListData(false);
	  
	  $daftar_tipe = FHelper::GetTipeProdukList();
	  
	  $this->layout = 'layout-baru';
		$content = $this->renderPartial(
		  'vfrm_posisistock',
		  array(
		    'daftar_lokasi' => $daftar_lokasi,
		    'daftar_tipe_produk' => $daftar_tipe
      ),
      true
    );
    
    $this->render(
      'index',
      array(
        'TheContent' => $content
      )
    );
	}
	
	//----------- hitung posisi stock ------------
	
	  /*
      actionHitungPosisiStock
      
      Deskripsi
      Fungsi untuk menghitung posisi stock berdasarkan idlokasi. Apakah menghitung
      posisi stock untuk seluruh toko atau sebuah toko.
      
      Parameter
      idlokasi
        Integer
        
      Result
        No dirent result. See detail in the function.
    */
    public function actionHitungPosisiStock()
    {
      $idlokasi = Yii::app()->request->getParam('idlokasi');
      $idtipeproduk = Yii::app()->request->getParam('idtipeproduk');
      
      if($idlokasi == 0)
      {
        //hitung posisi stock pada semua toko
        $nama_file = $this->PosisiStock();
        
        $nama_file = CHtml::link(
          'posisi stock',
          $nama_file
        );
      }
      else
      {
        $nama_file = $this->PosisiStock6($idlokasi, $idtipeproduk);
        
        $nama_file = CHtml::link(
          'posisi stock toko',
          $nama_file
        );
      }
      
      echo CJSON::encode(
        array(
          'nama_file' => $nama_file
        )
      );
    }
    
    /*
      PosisiStock menggunakan PHPExcel
      
      Deskripsi
      Fungsi untuk menghitung posisi stock setiap produk pada setiap toko
      
      Parameter
      Tidak ada parameter
      
      Result
      Mengembalikan view yang menampilkan posisi stock setiap produk pada setiap toko
    
    */
    public function PosisiStock($idtoko=-1)
    {
      //loop produk (dari tabel inv_inventory
      $Criteria = new CDbCriteria();
      $Criteria->condition = 'is_del = 0 AND is_deact = 0';
      $Criteria->order = 'nama asc';
      $daftar_produk = inv_inventory::model()->findAll($Criteria);
      
      $data_stock = array();
      $data_produk = array();
      
      //loop toko (dari tabel mtr_branch)
      $Criteria2 = new CDbCriteria();
      if($idtoko > -1)
      {
        $Criteria2->condition = 'is_del = "N" AND is_deact = "N" AND branch_id = :idtoko';
        $Criteria2->params = array(':idtoko' => $idtoko);
      }
      else
      {
        $Criteria2->condition = 'is_del = "N" AND is_deact = "N"';
      }
      
      $Criteria2->order = 'name asc';
      $daftar_toko = mtr_branch::model()->findAll($Criteria2);
      
      $Criteria0 = new CDbCriteria();
      
      set_time_limit(0);
      //ini_set('memory_limit', '256M'); 
      
      $sheet_count = 1;
      $record_count = 0;
      
      //menyiapkan PHPExcel
        $this->xlsStock = new PHPExcel();
        
        $cacheMethod = PHPExcel_CachedObjectStorageFactory:: cache_to_discISAM;
        $cacheSettings = array( 
          'memoryCacheSize' => '2MB',
          'dir' => '/home/sloki/user/h17655/sites/jhmoriska.com/www/apps'
        );
        PHPExcel_Settings::setCacheStorageMethod($cacheMethod, $cacheSettings);
        
        $this->wsStock = $this->xlsStock->getActiveSheet();
        $this->wsStock->setTitle('Posisi Stock');
        
        $baris = 1;
        $kolom = 0;
        
        if($idtoko == -1)
        {
          $this->wsStock->GetCellByColumnAndRow($kolom, $baris)
            ->setValue('Posisi Stock'); $baris++; $baris++;
        }
        else
        {
          $this->wsStock->GetCellByColumnAndRow($kolom, $baris)
            ->setValue('Posisi Stock Toko'); $baris++; $baris++;
            
          $this->wsStock->GetCellByColumnAndRow($kolom, $baris)
            ->setValue('Toko : ' . FHelper::GetLocationName($idtoko, true)); $baris++;
        }
        
          
        $this->wsStock->GetCellByColumnAndRow($kolom, $baris)
          ->setValue('Waktu : ' . date('j M Y, H:i:s')); $baris++; $baris++;
        
        $this->wsStock->GetCellByColumnAndRow($kolom, $baris)
          ->setValue('Produk'); $kolom++;
        
        foreach($daftar_toko as $toko)
        {
          $this->wsStock->GetCellByColumnAndRow($kolom, $baris)
            ->setValue($toko['name']);
            
          if($stock_opname)
          {
            $this->wsStock->GetCellByColumnAndRow($kolom, $baris)
              ->setValue('Posisi stock baru');
          }
            
          $kolom++;
        }
        
        $baris++;
        $kolom = 0;
      //menyiapkan PHPExcel
      
      foreach($daftar_produk as $produk)
      {
        //hitung total populasi produk. untuk menghitung persentase populasi per toko
        $idproduk = $produk['id'];
        $data_produk['id'] = $produk['id'];
        $data_produk['nama'] = $produk['nama'];
        
        $hasil = Yii::app()->db->createCommand()
          ->select('count(*) as jumlah')
          ->from('inv_item')
          ->where(
            'idinventory = :idproduk AND
            idstatus in (1, 2, 3)',
            array(':idproduk' => $idproduk))
          ->queryScalar();
        $populasi = $hasil;
        
        foreach($daftar_toko as $toko)
        {
          //hitung populasi produk per toko
          $idtoko = $toko['branch_id'];
          
          $hasil = Yii::app()->db->createCommand()
            ->select('count(*) as jumlah')
            ->from('inv_item')
            ->where(
              'idlokasi = :idtoko AND idinventory = :idproduk', 
              array(':idtoko' => $idtoko, ':idproduk' => $idproduk))
            ->queryScalar();
          
          $count = $hasil;
          
          if($populasi == 0)
          {
            $persen = 0;
          }
          else
          {
            $persen = (float) ((float)$count / (float)$populasi) * 100;
          }
          
          $data_stock[$idtoko]['jumlah'] = $count;
          $data_stock[$idtoko]['persen'] = $persen;
        }
        
        $this->BikinExcelPosisiStock(
          $baris,
          $daftar_toko, 
          $data_produk, 
          $data_stock, 
          ($idtoko > -1));
        
        unset($data_stock);
        $baris++;
      }
      
      //penutupan PHPExcel
        if($idtoko == -1)
        {
          $namafile = 'posisi_stock.xlsx';
        }
        else
        {
          $namafile = 'posisi_stock_toko.xlsx';
        }
        
        $writer = new PHPExcel_Writer_Excel2007($this->xlsStock);
        $writer->save($namafile);
      //penutupan PHPExcel
      
      return $namafile;
    }
    
    /*
      PosisiStock menggunakan PHPExcel
      
      Deskripsi
      Fungsi untuk menghitung posisi stock setiap produk pada setiap toko
      
      Parameter
      Tidak ada parameter
      
      Result
      Mengembalikan view yang menampilkan posisi stock setiap produk pada setiap toko
    
    */
    public function PosisiStock5($idtoko=-1)
    {
      //loop produk (dari tabel inv_inventory
      $Criteria = new CDbCriteria();
      $Criteria->condition = 'is_del = 0 AND is_deact = 0';
      $Criteria->order = 'nama asc';
      $daftar_produk = inv_inventory::model()->findAll($Criteria);
      
      $data_stock = array();
      $data_produk = array();
      
      //loop toko (dari tabel mtr_branch)
      $Criteria2 = new CDbCriteria();
      if($idtoko > -1)
      {
        $Criteria2->condition = 'is_del = "N" AND is_deact = "N" AND branch_id = :idtoko';
        $Criteria2->params = array(':idtoko' => $idtoko);
      }
      else
      {
        $Criteria2->condition = 'is_del = "N" AND is_deact = "N"';
      }
      
      $Criteria2->order = 'name asc';
      $daftar_toko = mtr_branch::model()->findAll($Criteria2);
      
      $Criteria0 = new CDbCriteria();
      
      set_time_limit(0);
      //ini_set('memory_limit', '256M'); 
      
      $sheet_count = 1;
      $record_count = 0;
      
      $sheet_count = 1;
      $record_count = 0;
      
      //menyiapkan PHPExcel
        $this->xlsStock = new PHPExcel();
        
        $cacheMethod = PHPExcel_CachedObjectStorageFactory:: cache_to_discISAM;
        $cacheSettings = array( 
          'memoryCacheSize' => '2MB',
          'dir' => '/home/sloki/user/h17655/sites/jhmoriska.com/www/apps'
        );
        PHPExcel_Settings::setCacheStorageMethod($cacheMethod, $cacheSettings);
        
        $writer = new PHPExcel_Writer_Excel2007($this->xlsStock);
        
        if($idtoko == -1)
        {
          $namafile = 'posisi_stock.xlsx';
        }
        else
        {
          $namafile = 'posisi_stock_toko.xlsx';
        }
      //menyiapkan PHPExcel
      
      foreach($daftar_produk as $produk)
      {
        
        //periksa apakah saat ini perlu sheet baru?
        if($record_count == 0)
        {
          $this->wsStock = new PHPExcel_Worksheet($this->xlsStock, 'Posisi Stock-' . $sheet_count++);
          $this->xlsStock->addSheet($this->wsStock);
          //$this->wsStock->setTitle('Posisi Stock-' . $sheet_count++);
          
          $baris = 1;
          $kolom = 0;
          
          if($idtoko == -1)
          {
            $this->wsStock->GetCellByColumnAndRow($kolom, $baris)
              ->setValue('Posisi Stock'); $baris++; $baris++;
          }
          else
          {
            $this->wsStock->GetCellByColumnAndRow($kolom, $baris)
              ->setValue('Posisi Stock Toko'); $baris++; $baris++;
              
            $this->wsStock->GetCellByColumnAndRow($kolom, $baris)
              ->setValue('Toko : ' . FHelper::GetLocationName($idtoko, true)); $baris++;
          }
            
          $this->wsStock->GetCellByColumnAndRow($kolom, $baris)
            ->setValue('Waktu : ' . date('j M Y, H:i:s')); $baris++; $baris++;
          
          $this->wsStock->GetCellByColumnAndRow($kolom, $baris)
            ->setValue('Produk'); $kolom++;
          
          foreach($daftar_toko as $toko)
          {
            $this->wsStock->GetCellByColumnAndRow($kolom, $baris)
              ->setValue($toko['name']);
              
            if($stock_opname)
            {
              $this->wsStock->GetCellByColumnAndRow($kolom, $baris)
                ->setValue('Posisi stock baru');
            }
              
            $kolom++;
          }
          
          $baris++;
          $kolom = 0;
        } //periksa apakah perlu bikin sheet baru?
        
        //hitung total populasi produk. untuk menghitung persentase populasi per toko
        $idproduk = $produk['id'];
        $data_produk['id'] = $produk['id'];
        $data_produk['nama'] = $produk['nama'];
        
        $hasil = Yii::app()->db->createCommand()
          ->select('count(*) as jumlah')
          ->from('inv_item')
          ->where('idinventory = :idproduk',
                  array(':idproduk' => $idproduk))
          ->queryScalar();
        $populasi = $hasil;
        
        foreach($daftar_toko as $toko)
        {
          //hitung populasi produk per toko
          $idtoko = $toko['branch_id'];
          
          $hasil = Yii::app()->db->createCommand()
            ->select('count(*) as jumlah')
            ->from('inv_item')
            ->where(array('and', 'idlokasi = :idtoko', 'idinventory = :idproduk'),
                    array(':idtoko' => $idtoko, ':idproduk' => $idproduk))
            ->queryScalar();
          
          $count = $hasil;
          
          if($populasi == 0)
          {
            $persen = 0;
          }
          else
          {
            $persen = (float) ((float)$count / (float)$populasi) * 100;
          }
          
          $data_stock[$idtoko]['jumlah'] = $count;
          $data_stock[$idtoko]['persen'] = $persen;
        }
        
        $this->BikinExcelPosisiStock(
          $baris,
          $daftar_toko, 
          $data_produk, 
          $data_stock, 
          ($idtoko > -1));
        
        unset($data_stock);
        
        $baris++;
        
        $record_count = ($record_count + 1) % 500;
        
        //jika terjadi pergantian sheet, tulis file dan lepaskan memory
        if($record_count == 0)
        {
          $writer->save($namafile);
          
          //$this->wsStock = null;
          unset($this->wsStock);
        }
      } //loop daftar_produk
      
      //penutupan PHPExcel
        $writer->save($namafile);
      //penutupan PHPExcel
      
      return $namafile;
    }
    
    /*
      PosisiStock menggunakan PHPExcel
      
      Deskripsi
      Fungsi untuk menghitung posisi stock setiap produk pada setiap toko
      
      Parameter
      Tidak ada parameter
      
      Result
      Mengembalikan view yang menampilkan posisi stock setiap produk pada setiap toko
    
    */
    public function PosisiStock6($idtoko=-1, $idtipeproduk)
    {
      //loop produk (dari tabel inv_inventory
      $Criteria = new CDbCriteria();
      $Criteria->condition = 'is_del = 0 AND is_deact = 0 AND idkategori = :idtipeproduk';
      $Criteria->params = array(':idtipeproduk' => $idtipeproduk);
      $Criteria->order = 'nama asc';
      $daftar_produk = inv_inventory::model()->findAll($Criteria);
      
      $data_stock = array();
      $data_produk = array();
      
      //loop toko (dari tabel mtr_branch)
      $Criteria2 = new CDbCriteria();
      if($idtoko > -1)
      {
        $Criteria2->condition = 'is_del = "N" AND is_deact = "N" AND branch_id = :idtoko';
        $Criteria2->params = array(':idtoko' => $idtoko);
      }
      else
      {
        $Criteria2->condition = 'is_del = "N" AND is_deact = "N"';
      }
      
      $Criteria2->order = 'name asc';
      $daftar_toko = mtr_branch::model()->findAll($Criteria2);
      
      $Criteria0 = new CDbCriteria();
      
      set_time_limit(0);
      //ini_set('memory_limit', '256M'); 
      
      $sheet_count = 1;
      $record_count = 0;
      
      //menyiapkan PHPExcel
        if($idtoko == -1)
        {
          $namafile = 'posisi_stock.xls';
        }
        else
        {
          $namafile = 'posisi_stock_toko.xls';
        }
        
        $this->xlsStock = new ExcelWriterXML();
        
        $this->wsStock = $this->xlsStock->addSheet('Posisi Stock');
        
        $baris = 1;
        $kolom = 1;
        
        if($idtoko == -1)
        {
          $this->wsStock->writeString($baris, $kolom, 'Posisi Stock'); $baris++; $baris++;
          $this->wsStock->writeString($baris, $kolom, 'Tipe produk : ' . FHelper::GetTipeProdukText($idtipeproduk)); $baris++; $baris++;
        }
        else
        {
          $this->wsStock->writeString($baris, $kolom, 'Posisi Stock Toko'); $baris++; $baris++;
          $this->wsStock->writeString($baris, $kolom, 'Toko : ' . FHelper::GetLocationName($idtoko, true)); $baris++; $baris++;
          $this->wsStock->writeString($baris, $kolom, 'Tipe produk : ' . FHelper::GetTipeProdukText($idtipeproduk)); $baris++; $baris++;
        }
        
          
        $this->wsStock->writeString($baris, $kolom, 'Waktu : ' . date('j M Y, H:i:s')); $baris++; $baris++;
        
        $this->wsStock->writeString($baris, $kolom, 'Produk'); $kolom++;
        
        foreach($daftar_toko as $toko)
        {
          $this->wsStock->writeString($baris, $kolom, $toko['name']);
            
          if($stock_opname)
          {
            $this->wsStock->writeString($baris, $kolom, 'Posisi stock baru');
          }
            
          $kolom++;
        }
        
        $baris++;
        $kolom = 1;
      //menyiapkan PHPExcel
      
      foreach($daftar_produk as $produk)
      {
        //hitung total populasi produk. untuk menghitung persentase populasi per toko
        $idproduk = $produk['id'];
        $data_produk['id'] = $produk['id'];
        $data_produk['nama'] = $produk['nama'];
        
        $hasil = Yii::app()->db->createCommand()
          ->select('count(*) as jumlah')
          ->from('inv_item')
          ->where('idstatus in (1, 2, 3) AND idinventory = :idproduk',
                  array(':idproduk' => $idproduk))
          ->queryScalar();
        $populasi = $hasil;
        
        foreach($daftar_toko as $toko)
        {
          //hitung populasi produk per toko
          $idtoko = $toko['branch_id'];
          
          $hasil = Yii::app()->db->createCommand()
            ->select('count(*) as jumlah')
            ->from('inv_item')
            ->where('idlokasi = :idtoko AND idinventory = :idproduk AND idstatus = 3',
                    array(':idtoko' => $idtoko, ':idproduk' => $idproduk))
            ->queryRow();
          
          $count = $hasil['jumlah'];
          
          if($populasi == 0)
          {
            $persen = 0;
          }
          else
          {
            $persen = (float) ((float)$count / (float)$populasi) * 100;
          }
          
          $data_stock[$idtoko]['jumlah'] = $count;
          $data_stock[$idtoko]['persen'] = $persen;
        }
        
        $hasil = $this->BikinExcelPosisiStock6(
          $baris,
          $daftar_toko, 
          $data_produk, 
          $data_stock, 
          ($idtoko > -1));
        
        unset($data_stock);
        
        if($hasil == true) //jika menuliskan data maka...
          $baris++;
      }
      
      //penutupan PHPExcel
        
        
        $this->xlsStock->overwriteFile(true);
        $this->xlsStock->writeData($namafile);
      //penutupan PHPExcel
      
      return $namafile;
    }
    
    /*
      PosisiStock2 menggunakan ExportXLS
      
      Deskripsi
      Fungsi untuk menghitung posisi stock setiap produk pada setiap toko
      
      Parameter
      Tidak ada parameter
      
      Result
      Mengembalikan view yang menampilkan posisi stock setiap produk pada setiap toko
    
    */
    public function PosisiStock2($idtoko=-1)
    {
      //loop produk (dari tabel inv_inventory
      $Criteria = new CDbCriteria();
      $Criteria->condition = 'is_del = 0 AND is_deact = 0';
      $Criteria->order = 'nama asc';
      $daftar_produk = inv_inventory::model()->findAll($Criteria);
      
      $data_stock = array();
      $data_produk = array();
      
      //loop toko (dari tabel mtr_branch)
      $Criteria2 = new CDbCriteria();
      if($idtoko > -1)
      {
        $Criteria2->condition = 'is_del = "N" AND is_deact = "N" AND branch_id = :idtoko';
        $Criteria2->params = array(':idtoko' => $idtoko);
      }
      else
      {
        $Criteria2->condition = 'is_del = "N" AND is_deact = "N"';
      }
      
      $Criteria2->order = 'name asc';
      $daftar_toko = mtr_branch::model()->findAll($Criteria2);
      
      $Criteria0 = new CDbCriteria();
      
      set_time_limit(0);
      ini_set('memory_limit', '140M'); 
      
      //menyiapkan ExportXLS
        if($idtoko == -1)
        {
          $namafile = 'posisi_stock.xls';
        }
        else
        {
          $namafile = 'posisi_stock_toko.xls';
        }
        
        $xlsStock = new ExportXLS($namafile);
      
        if($idtoko == -1)
        {
          $xlsStock->addRow(array('Posisi Stock')); $xlsStock->addRow('');
        }
        else
        {
          $xlsStock->addRow(array('Posisi Stock')); $xlsStock->addRow('');
          $xlsStock->addRow(array('Toko : ' . FHelper::GetLocationName($idtoko, true))); $xlsStock->addRow('');
        }
          
        $xlsStock->addRow(array('Waktu : ' . date('j M Y, H:i:s')));
        
        $judul_kolom[] = 'Produk';
        
        foreach($daftar_toko as $toko)
        {
          $judul_kolom[] = $toko['name'];
            
          if($stock_opname)
          {
            $judul_kolom[] = 'Posisi stock baru';
          }
            
          $kolom++;
        }
        $xlsStock->addRow($judul_kolom);
      //menyiapkan ExportXLS
      
      foreach($daftar_produk as $produk)
      {
        //hitung total populasi produk. untuk menghitung persentase populasi per toko
        $idproduk = $produk['id'];
        $data_stock[] = $produk['nama'];
        
        $hasil = Yii::app()->db->createCommand()
          ->select('count(*) as jumlah')
          ->from('inv_item')
          ->where('idinventory = :idproduk',
                  array(':idproduk' => $idproduk))
          ->queryScalar();
        $populasi = $hasil;
        
        foreach($daftar_toko as $toko)
        {
          //hitung populasi produk per toko
          $idtoko = $toko['branch_id'];
          
          $hasil = Yii::app()->db->createCommand()
            ->select('count(*) as jumlah')
            ->from('inv_item')
            ->where(array('and', 'idlokasi = :idtoko', 'idinventory = :idproduk'),
                    array(':idtoko' => $idtoko, ':idproduk' => $idproduk))
            ->queryScalar();
          
          $count = $hasil;
          
          if($populasi == 0)
          {
            $persen = 0;
          }
          else
          {
            $persen = (float) ((float)$count / (float)$populasi) * 100;
          }
        
          if($persen > 0)
          {
            
            $data = $count . '/' . $persen;
          }
          else
          {
            $data = '-';
          }
          
          $data_stock[] = $data;
          
        }
        
        $xlsStock->addRow($data_stock);
        
        unset($data_stock);
      }
      
      //penutupan PHPExcel
        $xlsStock->sendFile();
      //penutupan PHPExcel
    }
    
    /*
      PosisiStock3 menggunakan ExportDataExcel
      
      Deskripsi
      Fungsi untuk menghitung posisi stock setiap produk pada setiap toko
      
      Parameter
      Tidak ada parameter
      
      Result
      Mengembalikan view yang menampilkan posisi stock setiap produk pada setiap toko
    
    */
    public function PosisiStock3($idtoko=-1)
    {
      //loop produk (dari tabel inv_inventory
      $Criteria = new CDbCriteria();
      $Criteria->condition = 'is_del = 0 AND is_deact = 0';
      $Criteria->order = 'nama asc';
      $daftar_produk = inv_inventory::model()->findAll($Criteria);
      
      $data_stock = array();
      $data_produk = array();
      
      //loop toko (dari tabel mtr_branch)
      $Criteria2 = new CDbCriteria();
      if($idtoko > -1)
      {
        $Criteria2->condition = 'is_del = "N" AND is_deact = "N" AND branch_id = :idtoko';
        $Criteria2->params = array(':idtoko' => $idtoko);
      }
      else
      {
        $Criteria2->condition = 'is_del = "N" AND is_deact = "N"';
      }
      
      $Criteria2->order = 'name asc';
      $daftar_toko = mtr_branch::model()->findAll($Criteria2);
      
      $Criteria0 = new CDbCriteria();
      
      set_time_limit(0);
      ini_set('memory_limit', '512M'); 
      
      //menyiapkan ExportXLS
        if($idtoko == -1)
        {
          $namafile = 'posisi_stock.xml';
        }
        else
        {
          $namafile = 'posisi_stock_toko.xml';
        }
        
        $xlsStock = new ExportDataExcel('file', $namafile);
        $xlsStock->initialize();
      
        if($idtoko == -1)
        {
          $xlsStock->addRow(array('Posisi Stock')); $xlsStock->addRow(array(''));
        }
        else
        {
          $xlsStock->addRow(array('Posisi Stock')); $xlsStock->addRow(array(''));
          $xlsStock->addRow(array('Toko : ' . FHelper::GetLocationName($idtoko, true))); $xlsStock->addRow(array(''));
        }
          
        $xlsStock->addRow(array('Waktu : ' . date('j M Y, H:i:s')));
        
        $judul_kolom[] = 'Produk';
        
        foreach($daftar_toko as $toko)
        {
          $judul_kolom[] = $toko['name'];
            
          if($stock_opname)
          {
            $judul_kolom[] = 'Posisi stock baru';
          }
            
          $kolom++;
        }
        $xlsStock->addRow($judul_kolom);
      //menyiapkan ExportXLS
      
      foreach($daftar_produk as $produk)
      {
        //hitung total populasi produk. untuk menghitung persentase populasi per toko
        $idproduk = $produk['id'];
        $data_stock[] = $produk['nama'];
        
        $hasil = Yii::app()->db->createCommand()
          ->select('count(*) as jumlah')
          ->from('inv_item')
          ->where('idinventory = :idproduk',
                  array(':idproduk' => $idproduk))
          ->queryScalar();
        $populasi = $hasil;
        
        foreach($daftar_toko as $toko)
        {
          //hitung populasi produk per toko
          $idtoko = $toko['branch_id'];
          
          $hasil = Yii::app()->db->createCommand()
            ->select('count(*) as jumlah')
            ->from('inv_item')
            ->where(array('and', 'idlokasi = :idtoko', 'idinventory = :idproduk'),
                    array(':idtoko' => $idtoko, ':idproduk' => $idproduk))
            ->queryScalar();
          
          $count = $hasil;
          
          if($populasi == 0)
          {
            $persen = 0;
          }
          else
          {
            $persen = (float) ((float)$count / (float)$populasi) * 100;
          }
        
          if($persen > 0)
          {
            
            $data = $count . '/' . $persen;
          }
          else
          {
            $data = '';
          }
          
          $data_stock[] = $data;
          
        }
        
        $xlsStock->addRow($data_stock);
        
        unset($data_stock);
      }
      
      //penutupan PHPExcel
        $xlsStock->finalize();
      //penutupan PHPExcel
      
      return $namafile;
    }
    
    /*
      PosisiStock3 menggunakan teknik dari MIR
      
      Deskripsi
      Fungsi untuk menghitung posisi stock setiap produk pada setiap toko
      
      Parameter
      Tidak ada parameter
      
      Result
      Mengembalikan view yang menampilkan posisi stock setiap produk pada setiap toko
    
    */
    public function PosisiStock4($idtoko=-1)
    {
      //loop produk (dari tabel inv_inventory
      $Criteria = new CDbCriteria();
      $Criteria->condition = 'is_del = 0 AND is_deact = 0';
      $Criteria->order = 'nama asc';
      $daftar_produk = inv_inventory::model()->findAll($Criteria);
      
      $data_stock = array();
      $data_produk = array();
      
      //loop toko (dari tabel mtr_branch)
      $Criteria2 = new CDbCriteria();
      if($idtoko > -1)
      {
        $Criteria2->condition = 'is_del = "N" AND is_deact = "N" AND branch_id = :idtoko';
        $Criteria2->params = array(':idtoko' => $idtoko);
      }
      else
      {
        $Criteria2->condition = 'is_del = "N" AND is_deact = "N"';
      }
      
      $Criteria2->order = 'name asc';
      $daftar_toko = mtr_branch::model()->findAll($Criteria2);
      
      $Criteria0 = new CDbCriteria();
      
      set_time_limit(0);
      //ini_set('memory_limit', '512M'); 
      
      //menyiapkan export ke excel
        if($idtoko == -1)
        {
          $namafile = 'posisi_stock.xls';
        }
        else
        {
          $namafile = 'posisi_stock_toko.xls';
        }
        
        if($idtoko == -1)
        {
          $judul = 'Posisi Stock';
        }
        else
        {
          $judul = 'Posisi Stock';
          $nama_toko = 'Toko : ' . FHelper::GetLocationName($idtoko, true);
        }
          
        $waktu = 'Waktu : ' . date('j M Y, H:i:s');
        
        $judul_kolom[] = 'No';
        $judul_kolom[] = 'Produk';
        
        foreach($daftar_toko as $toko)
        {
          $judul_kolom[] = $toko['name'];
            
          if($stock_opname)
          {
            $judul_kolom[] = 'Posisi stock baru';
          }
            
          $kolom++;
        }
        $jumlah_kolom = count($judul_kolom);
        
        
        
      //menyiapkan export ke excel
      
      //tulis tahap awal eksport excel. header dan judul laporan
      header('Content-Type: application/vnd.ms-excel');
      header('Content-Disposition: attachment; filename=' . $namafile);
      header('Content-Transfer-Encoding: binary');
      header('Accept-Ranges: bytes');
      
      echo '<html>';
      
      echo 
        "<head>
          <title>{$judul}</title>
        </head>";
        
      echo "<body>";
      echo "<table border='0' width='100%'>";
      
      //cetak judul laporan
      echo
        "<tr>
          <td colspan='{$jumlah_kolom}'>{$judul}</td>
        </tr>";
        
      //cetak nama toko
      if($idtoko > -1)
      {
        echo
          "<tr>
            <td colspan='{$jumlah_kolom}'>{$nama_toko}</td>
          </tr>";
      }
      
      //cetak waktu pembuatan laporan
      echo
        "<tr>
          <td colspan='{$jumlah_kolom}'>{$waktu}</td>
        </tr>";
        
      //cetak judul kolom
          echo "<tr>";
          
          for($kolom_ke = 0; $kolom_ke < $jumlah_kolom; $kolom_ke++)
          {
            echo
              "<th align='left'><b>{$judul_kolom[$kolom_ke]}</b></th>";
          }
          
          echo "</tr>";
      //cetak judul kolom
      
      //cetak data
      
          $baris_ke = 1;
          foreach($daftar_produk as $produk)
          {
            //hitung total populasi produk. untuk menghitung persentase populasi per toko
            $idproduk = $produk['id'];
            $data_stock[] = $produk['nama'];
            
            $hasil = Yii::app()->db->createCommand()
              ->select('count(*) as jumlah')
              ->from('inv_item')
              ->where('idinventory = :idproduk',
                      array(':idproduk' => $idproduk))
              ->queryScalar();
            $populasi = $hasil;
            
            echo "<tr>"; //pembuka row
            
            echo "<td align='left'>{$baris_ke}</td>"; $baris_ke++;
            echo "<td align='left'>{$produk['nama']}</td>";
            
            foreach($daftar_toko as $toko)
            {
              //hitung populasi produk per toko
              $idtoko = $toko['branch_id'];
              
              $hasil = Yii::app()->db->createCommand()
                ->select('count(*) as jumlah')
                ->from('inv_item')
                ->where(array('and', 'idlokasi = :idtoko', 'idinventory = :idproduk'),
                        array(':idtoko' => $idtoko, ':idproduk' => $idproduk))
                ->queryScalar();
              
              $count = $hasil;
              
              if($populasi == 0)
              {
                $persen = 0;
              }
              else
              {
                $persen = (float) ((float)$count / (float)$populasi) * 100;
              }
            
              if($persen > 0)
              {
                
                $data = $count . '/' . $persen;
              }
              else
              {
                $data = '';
              }
              
              echo "<td align='left'>{$data}</td>";
              
            } //loop toko
            
            echo "</tr>"; //penutup row
          } //loop produk
      
      //cetak data
      
      echo "</table>";
      echo "</body>";
      echo "</html>";
    }
    
    /*
      PosisiStockToko
      
      Deskripsi
      Fungsi untuk menghitung posisi stock setiap produk pada setiap toko
      
      Parameter
      idlokasi
        Integer
      
      Result
      Mengembalikan view yang menampilkan posisi stock setiap produk pada setiap toko
    
    */
    public function PosisiStockToko($idtoko)
    {
      //loop produk (dari tabel inv_inventory
      $Criteria = new CDbCriteria();
      $Criteria->condition = 'is_del = 0 AND is_deact = 0';
      $Criteria->order = 'nama asc';
      $daftar_produk = inv_inventory::model()->findAll($Criteria);
      
      $data_stock = array();
      $data_produk = array();
      
      $Criteria0 = new CDbCriteria();
      
      set_time_limit(0);
      ini_set('memory_limit', '512M'); 
      
      foreach($daftar_produk as $produk)
      {
        //hitung total populasi produk. untuk menghitung persentase populasi per toko
        $idproduk = $produk['id'];
        $data_produk[$idproduk]['nama'] = $produk['nama'];
        
        $hasil = Yii::app()->db->createCommand()
          ->select('count(*) as jumlah')
          ->from('inv_item')
          ->where('idinventory = :idproduk',
                  array(':idproduk' => $idproduk))
          ->queryScalar();
        $populasi = $hasil;
        
        //hitung populasi produk toko
        $hasil = Yii::app()->db->createCommand()
          ->select('count(*) as jumlah')
          ->from('inv_item')
          ->where(array('and', 'idlokasi = :idtoko', 'idinventory = :idproduk'),
                  array(':idtoko' => $idtoko, ':idproduk' => $idproduk))
          ->queryScalar();
        
        $count = $hasil;
        
        if($populasi == 0)
        {
          $persen = 0;
        }
        else
        {
          $persen = (float) ((float)$count / (float)$populasi) * 100;
        }
        
        $data_stock[$idproduk][$idtoko]['jumlah'] = $count;
        $data_stock[$idproduk][$idtoko]['persen'] = $persen;
      }
      
      $nama_file = $this->BikinExcelPosisiStockToko($idtoko, $daftar_produk, $data_stock);
      
      return $nama_file;
      
    }
    
    private function BikinExcelPosisiStock(
      $baris,
      $daftar_toko, 
      $data_produk, 
      $data_stock, 
      $stock_opname=false)
    {
      $kolom = 0;
      
      $this->wsStock->GetCellByColumnAndRow($kolom, $baris)
        ->setValue("{$data_produk['id']} : {$data_produk['nama']}"); $kolom++;
        
      $this->wsStock->GetCellByColumnAndRow($kolom, $baris)
        ->setValue(FHelper::GetProdukUkuran($data_produk['id'])); $kolom++;
      
      foreach($daftar_toko as $toko)
      {
        $idtoko = $toko['branch_id'];
        $jumlah = $data_stock[$idtoko]['jumlah'];
        $persen = $data_stock[$idtoko]['persen'];
        
        if($jumlah > 0)
        {
          $this->wsStock->GetCellByColumnAndRow($kolom, $baris)
            ->setValue($jumlah . '/' . 
                       number_format($persen, 2, ',', '.') . '%');
        }
         
        $kolom++;
      }
    }
    
    private function BikinExcelPosisiStock6(
      $baris,
      $daftar_toko, 
      $data_produk, 
      $data_stock, 
      $stock_opname=false)
    {
      $kolom = 1;
      
      foreach($daftar_toko as $toko)
      {
        $idtoko = $toko['branch_id'];
        $jumlah = $data_stock[$idtoko]['jumlah'];
        $persen = $data_stock[$idtoko]['persen'];
        
        if($jumlah > 0)
        {
          $this->wsStock->writeString($baris, $kolom, "{$data_produk['nama']}"); $kolom++;
        
          $this->wsStock->writeString($baris, $kolom, FHelper::GetProdukUkuran($data_produk['id'])); $kolom++;
      
          $this->wsStock->writeString($baris, $kolom,
            $jumlah);
          
          return true;
        }
        else
        {
          return false;
        }
      }
    }
    
    private function BikinExcelPosisiStock_asli($daftar_toko, $daftar_produk, $data_stock, $stock_opname=false)
    {
      $xlsStock = new PHPExcel();
      $wsStock = $xlsStock->getActiveSheet();
      $wsStock->setTitle('Posisi Stock');
      
      $baris = 1;
      $kolom = 0;
      
      $wsStock->GetCellByColumnAndRow($kolom, $baris)
        ->setValue('Posisi Stock'); $baris++; $baris++;
        
      $wsStock->GetCellByColumnAndRow($kolom, $baris)
        ->setValue('Waktu : ' . date('j M Y, H:i:s')); $baris++; $baris++;
      
      $wsStock->GetCellByColumnAndRow($kolom, $baris)
        ->setValue('Produk'); $kolom++;
      
      foreach($daftar_toko as $toko)
      {
        $wsStock->GetCellByColumnAndRow($kolom, $baris)
          ->setValue($toko['name']);
          
        if($stock_opname)
        {
          $wsStock->GetCellByColumnAndRow($kolom, $baris)
            ->setValue('Posisi stock baru');
        }
          
        $kolom++;
      }
      
      $baris++;
      $kolom = 0;
      
      foreach($daftar_produk as $produk)
      {
        $idproduk = $produk['id'];
        $wsStock->GetCellByColumnAndRow($kolom, $baris)
          ->setValue($produk['nama']); $kolom++;
        
        foreach($daftar_toko as $toko)
        {
          $idtoko = $toko['branch_id'];
          $jumlah = $data_stock[$idproduk][$idtoko]['jumlah'];
          $persen = $data_stock[$idproduk][$idtoko]['persen'];
          
          if($jumlah > 0)
          {
            $wsStock->GetCellByColumnAndRow($kolom, $baris)
              ->setValue($jumlah);
          }
           
          $kolom++;
        }
        $baris++;
        $kolom = 0;
      }
      
      $namafile = 'posisi_stock.xlsx';
      $writer = new PHPExcel_Writer_Excel2007($xlsStock);
      $writer->save($namafile);
      
      return $namafile;
    }
    
    private function BikinExcelPosisiStockToko($idtoko, $daftar_produk, $data_stock)
    {
      $xlsStock = new PHPExcel();
      $wsStock = $xlsStock->getActiveSheet();
      $wsStock->setTitle('Posisi Stock');
      
      $baris = 1;
      $kolom = 0;
      
      $wsStock->GetCellByColumnAndRow($kolom, $baris)
        ->setValue('Posisi Stock Toko'); $baris++; $baris++;
        
      $wsStock->GetCellByColumnAndRow($kolom, $baris)
        ->setValue('Toko : ' . FHelper::GetLocationName($idtoko, true)); $baris++;
        
      $wsStock->GetCellByColumnAndRow($kolom, $baris)
        ->setValue('Waktu : ' . date('j M Y, H:i:s')); $baris++; $baris++;
      
      $wsStock->GetCellByColumnAndRow($kolom, $baris)->setValue('Produk'); $kolom++;
      
      $wsStock->GetCellByColumnAndRow($kolom, $baris)
        ->setValue(FHelper::GetLocationName($idtoko, true)); $kolom++;
        
      $wsStock->GetCellByColumnAndRow($kolom, $baris)
        ->setValue('Posisi stock baru');
      
      $baris++;
      $kolom = 0;
      
      foreach($daftar_produk as $produk)
      {
        $idproduk = $produk['id'];
        $wsStock->GetCellByColumnAndRow($kolom, $baris)
          ->setValue($produk['nama']); $kolom++;
        
        $wsStock->GetCellByColumnAndRow($kolom, $baris)
          ->setValue("ukuran produk"); $kolom++;
          
        /*
        $wsStock->GetCellByColumnAndRow($kolom, $baris)
          ->setValue(FHelper::GetProdukUkuran($idproduk)); $kolom++;
        */
        
        $jumlah = $data_stock[$idproduk][$idtoko]['jumlah'];
        $persen = $data_stock[$idproduk][$idtoko]['persen'];
        
        if($jumlah > 0)
        {
          $wsStock->GetCellByColumnAndRow($kolom, $baris)
            ->setValue($jumlah . '/' . 
                       number_format($persen, 2, ',', '.') . '%');
        }
          
        $baris++;
        $kolom = 0;
      }
      
      $namafile = 'posisi_stock_toko.xlsx';
      $writer = new PHPExcel_Writer_Excel2007($xlsStock);
      $writer->save($namafile);
      
      return $namafile;
    }
	
	//----------- hitung posisi stock ------------
	
    
  
  //---- absensi01 - absensi cabang dalam sebulan - begin
  
    /*
      actionAbsensi01
      
      Deskripsi
      Menghitung data absensi suatu cabang dalam sebulan.
      
      Parameter
      idlokasi
        Integer
      bulan
        Integer
      tahun
        Integer
        
      Result
        Mengembalikan link file excel untuk di-download
    */
    public function actionAbsensi01()
    {
      $this->menuid = 58;
      $this->parentmenuid = 57; 
      $this->userid_actor = Yii::app()->request->cookies['userid_actor']->value;
      $this->idlokasi = Yii::app()->request->cookies['idlokasi']->value;
      $idgroup = FHelper::GetGroupId($this->userid_actor);
      $this->bread_crumb_list = 
        '<li>Laporan</li>'.
        '<li>></li>'.
        '<li>Absensi</li>'.
        '<li>></li>'.
        '<li>Absensi Cabang Dalam Sebulan</li>';
               
      $daftar_lokasi = FHelper::GetLocationListData(false);
      
      $daftar_bulan[1] = 'Januari';
      $daftar_bulan[2] = 'Februari';
      $daftar_bulan[3] = 'Maret';
      $daftar_bulan[4] = 'April';
      $daftar_bulan[5] = 'Mei';
      $daftar_bulan[6] = 'Juni';
      $daftar_bulan[7] = 'Juli';
      $daftar_bulan[8] = 'Agustus';
      $daftar_bulan[9] = 'September';
      $daftar_bulan[10] = 'Oktober';
      $daftar_bulan[11] = 'November';
      $daftar_bulan[12] = 'Desember';
      
      $this->layout = 'layout-baru';
      $content = $this->renderPartial(
        'vfrm_laporan_absensi01',
        array(
          'daftar_lokasi' => $daftar_lokasi,
          'daftar_bulan' => $daftar_bulan
        ),
        true
      );
      
      $this->render(
        'index',
        array(
          'TheContent' => $content
        )
      );
    }
    
    /*
      actionHitungAbsensi01
      
      Deskripsi
      Mengolah idlokasi, tahun, bulan untuk mengembalikan data absensi melalui
      file excel.
      
      Parameter
      idlokasi
        Integer
      tahun
        Integer
      bulan
        Integer
        
      Result
      Mengembalikan link untuk download file excel.
    */
    public function actionHitungAbsensi01()
    {
      $idlokasi = Yii::app()->request->getParam('idlokasi');
      $tahun = Yii::app()->request->getParam('tahun');
      $bulan = Yii::app()->request->getParam('bulan');
      
      $nama_lokasi = FHelper::GetLocationName($idlokasi, true);
      
      //mengambil data absensi berdasarkan idlokasi, tahun, bulan
      $daftar_absensi = Yii::app()->db->createCommand()
        ->select('absensi.*, sys_user.nama')
        ->from('absensi')
        ->join('sys_user', 'absensi.iduser = sys_user.id')
        ->where(
          'tahun = :tahun AND
          bulan = :bulan AND
          idlokasi = :idlokasi', 
          array(
            ':tahun' => $tahun,
            ':bulan' => $bulan,
            ':idlokasi' => $idlokasi))
        ->order('absensi.tanggal asc, sys_user.nama asc, absensi.keluarmasuk asc')
        ->queryAll();
      
      //simpan ke file excel
      $namafile = "Absensi Sebulan-$nama_lokasi-$tahun-$bulan.xlsx";
      $namafile = str_replace("/", "_", $namafile);
      
      $xlsAbsensi = new PHPExcel();
      $wsAbsensi = $xlsAbsensi->getActiveSheet();
      $wsAbsensi->setTitle("Absensi-$tahun-$bulan");
      
      $baris = 1;
      $kolom = 0;
      
      $wsAbsensi->GetCellByColumnAndRow($kolom, $baris)
        ->setValue('Absensi Cabang Sebulan'); $baris++;
      
      $wsAbsensi->GetCellByColumnAndRow($kolom, $baris)
        ->setValue("Cabang : $nama_lokasi"); $baris++;
        
      $wsAbsensi->GetCellByColumnAndRow($kolom, $baris)
        ->setValue("Tahun : $tahun"); $baris++;
        
      $wsAbsensi->GetCellByColumnAndRow($kolom, $baris)
        ->setValue("Bulan : $bulan"); $baris++;
        
      $baris++; $baris++;
      
      foreach($daftar_absensi as $absensi)
      {
        $wsAbsensi->GetCellByColumnAndRow($kolom, $baris)
          ->setValue(date('d-m-Y H:i:s', strtotime($absensi['waktu']))); $kolom++;
          
        $wsAbsensi->GetCellByColumnAndRow($kolom, $baris)
          ->setValue($absensi['nama']); $kolom++;
          
        switch($absensi['keluarmasuk'])
        {
          case 1: //masuk
            $tipe_absen = 'Masuk';
            break;
          case 2: //keluar
            $tipe_absen = 'Keluar';
            break;
          case 3: //ijin
            $tipe_absen = 'Ijin';
            break;
          case 4: //cuti
            $tipe_absen = 'Cuti';
            break;
          case 5: //Is-In
            $tipe_absen = 'Is-In';
            break;
          case 6: //Is-Out
            $tipe_absen = 'Is-Out';
            break;
        }
          
        $wsAbsensi->GetCellByColumnAndRow($kolom, $baris)
          ->setValue( $tipe_absen ); $kolom++;
          
        $kolom = 0;
        $baris++;
      }
      
      $writer = new PHPExcel_Writer_Excel2007($xlsAbsensi);
      $writer->save($namafile);
      
      $nama_file = CHtml::link(
        'absensi cabang sebulan',
        $namafile
      );
      
      echo CJSON::encode(array('nama_file' => $nama_file));
    }
  
  //---- absensi01 - absensi cabang dalam sebulan - end
  



  //---- absensi02 - absensi individu dalam sebulan - begin
  
    /*
      actionAbsensi02
      
      Deskripsi
      Menghitung data absensi suatu user dalam sebulan.
      
      Parameter
      idlokasi
        Integer
      bulan
        Integer
      tahun
        Integer
        
      Result
        Mengembalikan link file excel untuk di-download
    */
    public function actionAbsensi02()
    {
      $this->menuid = 58;
      $this->parentmenuid = 57; 
      $this->userid_actor = Yii::app()->request->cookies['userid_actor']->value;
      $this->idlokasi = Yii::app()->request->cookies['idlokasi']->value;
      $idgroup = FHelper::GetGroupId($this->userid_actor);
      $this->bread_crumb_list = 
        '<li>Laporan</li>'.
        '<li>></li>'.
        '<li>Absensi</li>'.
        '<li>></li>'.
        '<li>Absensi Individu Dalam Sebulan</li>';
               
      $daftar_user = FHelper::GetUserListData();
      
      $daftar_bulan[1] = 'Januari';
      $daftar_bulan[2] = 'Februari';
      $daftar_bulan[3] = 'Maret';
      $daftar_bulan[4] = 'April';
      $daftar_bulan[5] = 'Mei';
      $daftar_bulan[6] = 'Juni';
      $daftar_bulan[7] = 'Juli';
      $daftar_bulan[8] = 'Agustus';
      $daftar_bulan[9] = 'September';
      $daftar_bulan[10] = 'Oktober';
      $daftar_bulan[11] = 'November';
      $daftar_bulan[12] = 'Desember';
      
      $this->layout = 'layout-baru';
      $content = $this->renderPartial(
        'vfrm_laporan_absensi02',
        array(
          'daftar_lokasi' => $daftar_lokasi,
          'daftar_user' => $daftar_user,
          'daftar_bulan' => $daftar_bulan
        ),
        true
      );
      
      $this->render(
        'index',
        array(
          'TheContent' => $content
        )
      );
    }
    
    /*
      actionHitungAbsensi02
      
      Deskripsi
      Mengolah iduser, tahun, bulan untuk mengembalikan data absensi melalui
      file excel.
      
      Parameter
      iduser
        Integer
      tahun
        Integer
      bulan
        Integer
        
      Result
      Mengembalikan link untuk download file excel.
    */
    public function actionHitungAbsensi02()
    {
      $iduser = Yii::app()->request->getParam('iduser');
      $tahun = Yii::app()->request->getParam('tahun');
      $bulan = Yii::app()->request->getParam('bulan');
      
      $nama_user = FHelper::GetUserName($iduser);
      
      //mengambil data absensi berdasarkan idlokasi, tahun, bulan
      $daftar_absensi = Yii::app()->db->createCommand()
        ->select('absensi.*, sys_user.nama')
        ->from('absensi')
        ->join('sys_user', 'absensi.iduser = sys_user.id')
        ->where(
          'tahun = :tahun AND
          bulan = :bulan AND
          iduser = :iduser', 
          array(
            ':tahun' => $tahun,
            ':bulan' => $bulan,
            ':iduser' => $iduser))
        ->order('absensi.tanggal asc, absensi.waktu asc, sys_user.nama asc, absensi.keluarmasuk asc')
        ->queryAll();
      
      //simpan ke file excel
      $namafile = "Absensi Individu Sebulan-$nama_user-$tahun-$bulan.xlsx";
      $namafile = str_replace("/", "_", $namafile);
      
      $xlsAbsensi = new PHPExcel();
      $wsAbsensi = $xlsAbsensi->getActiveSheet();
      $wsAbsensi->setTitle("Absensi-$tahun-$bulan");
      
      $baris = 1;
      $kolom = 0;
      
      $wsAbsensi->GetCellByColumnAndRow($kolom, $baris)
        ->setValue('Absensi Individu Sebulan'); $baris++;
      
      $wsAbsensi->GetCellByColumnAndRow($kolom, $baris)
        ->setValue("Individu : $nama_user"); $baris++;
        
      $wsAbsensi->GetCellByColumnAndRow($kolom, $baris)
        ->setValue("Tahun : $tahun"); $baris++;
        
      $wsAbsensi->GetCellByColumnAndRow($kolom, $baris)
        ->setValue("Bulan : $bulan"); $baris++;
        
      $baris++; $baris++;
      
      foreach($daftar_absensi as $absensi)
      {
        $wsAbsensi->GetCellByColumnAndRow($kolom, $baris)
          ->setValue(date('d-m-Y H:i:s', strtotime($absensi['waktu']))); $kolom++;
          
        $wsAbsensi->GetCellByColumnAndRow($kolom, $baris)
          ->setValue(FHelper::GetLocationName($absensi['idlokasi'], true)); $kolom++;
          
        switch($absensi['keluarmasuk'])
        {
          case 1: //masuk
            $tipe_absen = 'Masuk';
            break;
          case 2: //keluar
            $tipe_absen = 'Keluar';
            break;
          case 3: //ijin
            $tipe_absen = 'Ijin';
            break;
          case 4: //cuti
            $tipe_absen = 'Cuti';
            break;
          case 5: //Is-In
            $tipe_absen = 'Is-In';
            break;
          case 6: //Is-Out
            $tipe_absen = 'Is-Out';
            break;
        }
          
        $wsAbsensi->GetCellByColumnAndRow($kolom, $baris)
          ->setValue( $tipe_absen ); $kolom++;
          
        $kolom = 0;
        $baris++;
      }
      
      $writer = new PHPExcel_Writer_Excel2007($xlsAbsensi);
      $writer->save($namafile);
      
      $nama_file = CHtml::link(
        'absensi individu sebulan',
        $namafile
      );
      
      echo CJSON::encode(array('nama_file' => $nama_file));
    }
  
  //---- absensi02 - absensi individu dalam sebulan - end
  
  
  
  
  //---- absensi03 - absensi seluruh cabang dalam sehari - begin
  
    /*
      actionAbsensi03
      
      Deskripsi
      Menghitung data absensi seluruh cabang dalam sehari.
      
      Parameter
      idlokasi
        Integer
      tanggal
        String
        
      Result
        Mengembalikan view absensi dalam sehari.
    */
    public function actionAbsensi03()
    {
      $this->menuid = 58;
      $this->parentmenuid = 57; 
      $this->userid_actor = Yii::app()->request->cookies['userid_actor']->value;
      $this->idlokasi = Yii::app()->request->cookies['idlokasi']->value;
      $idgroup = FHelper::GetGroupId($this->userid_actor);
      $this->bread_crumb_list = 
        '<li>Laporan</li>'.
        '<li>></li>'.
        '<li>Absensi</li>'.
        '<li>></li>'.
        '<li>Absensi Cabang Dalam Sehari</li>';
               
      $daftar_user = FHelper::GetUserListData();
      $daftar_lokasi = FHelper::GetLocationListData(false);
      
      $this->layout = 'layout-baru';
      $content = $this->renderPartial(
        'vfrm_laporan_absensi03',
        array(
          'daftar_lokasi' => $daftar_lokasi,
          
        ),
        true
      );
      
      $this->render(
        'index',
        array(
          'TheContent' => $content
        )
      );
    }
    
    /*
      actionHitungAbsensi03
      
      Deskripsi
      Mengolah iduser, tahun, bulan untuk mengembalikan data absensi sehari melalui
      view yang dibungkus JSON.
      
      Parameter
      iduser
        Integer
      tahun
        Integer
      bulan
        Integer
        
      Result
      Mengembalikan link untuk download file excel.
    */
    public function actionHitungAbsensi03()
    {
      $idlokasi = Yii::app()->request->getParam('idlokasi');
      $tanggal = Yii::app()->request->getParam('tanggal');
      
      $tanggal = strtotime($tanggal);
      $tahun = (int)date('Y', $tanggal);
      $bulan = (int)date('n', $tanggal);
      $hari = (int)date('j', $tanggal);
      
      Yii::log("tahun = $tahun; bulan = $bulan; tanggal = $hari", 'info');
      
      $nama_lokasi = FHelper::GetLocationName($idlokasi, true);
      
      //mengambil data absensi berdasarkan idlokasi, tanggal
      $daftar_absensi = Yii::app()->db->createCommand()
        ->select('absensi.*, sys_user.nama')
        ->from('absensi')
        ->join('sys_user', 'absensi.iduser = sys_user.id')
        ->where(
          'tahun = :tahun AND
          bulan = :bulan AND
          tanggal = :tanggal AND
          idlokasi = :idlokasi', 
          array(
            ':tahun' => $tahun,
            ':bulan' => $bulan,
            ':tanggal' => $hari,
            ':idlokasi' => $idlokasi))
        ->order('absensi.tanggal asc, sys_user.nama asc, absensi.keluarmasuk asc')
        ->queryAll();
      
      //tampilkan melalui view
      $html = $this->renderPartial(
        'v_absen_cabang_sehari',
        array(
          'daftar_absensi' => $daftar_absensi
        ),
        true
      );
      
      echo CJSON::encode(array('html' => $html));
    }
  
  //---- absensi03 - absensi seluruh cabang dalam sehari - end
  
  
  
  //---- stock opname - begin
  
    /*
      actionShowStockOpname
      
      Deskripsi
      Fungsi untuk menampilkan form pencetak stock opname.
      
      Return
      View form pencetak stock opname
    */
    public function actionShowStockOpname()
    {
      $this->menuid = 36;
      $this->parentmenuid = 42; 
      $this->userid_actor = Yii::app()->request->cookies['userid_actor']->value;
      $this->idlokasi = Yii::app()->request->cookies['idlokasi']->value;
      $idgroup = FHelper::GetGroupId($this->userid_actor);
      $this->bread_crumb_list = 
        '<li>Laporan</li>'.
        '<li>></li>'.
        '<li>Inventory</li>'.
        '<li>></li>'.
        '<li>Stock Opname</li>';
      
      $daftar_lokasi = FHelper::GetLocationListData(false);
      $daftar_tipe_produk = FHelper::GetTipeProdukList();
      
      $this->layout = 'layout-baru';
      $content = $this->renderPartial(
        'vfrm_stock_opname',
        array(
          'daftar_lokasi' => $daftar_lokasi,
          'daftar_tipe_produk' => $daftar_tipe_produk
        ),
        true
      );
      
      $this->render(
        'index',
        array(
          'TheContent' => $content
        )
      );
    }
  
    /*
      actionStockOpname
      
      Deskripsi
      Fungsi untuk mencetak daftar barcode pada suatu cabang.
      
      Parameter
      idlokasi
        Integer
        
      Return
      File excel yang berisi daftar barcode pada suatu cabang
    */
    public function actionCetakStockOpname()
    {
      $idlokasi = Yii::app()->request->getParam('idlokasi');
      $idtipeproduk = Yii::app()->request->getParam('idtipeproduk');
      
      $command = Yii::app()->db->createCommand()
        ->select('item.barcode, inventory.*')
        ->from('inv_item item')
        ->join('inv_inventory inventory', 'item.idinventory = inventory.id')
        ->where(
          'item.idstatus = 3 AND
          item.idlokasi = :idlokasi AND
          inventory.idkategori = :idtipeproduk',
          array(
            ':idlokasi' => $idlokasi,
            ':idtipeproduk' => $idtipeproduk,
          )
        )
        ->order('inventory.idkategori, item.barcode asc');
      $daftar_item = $command->queryAll();
      
      //tulis ke file excel
      $nama_file = $this->BikinExcelStockOpname($daftar_item, $idlokasi, $idtipeproduk);
      
      $html = CHtml::link(
        'file stock opname',
        $nama_file
      );
      
      echo CJSON::encode(array('nama_file' => $html));
    }
    
    private function BikinExcelStockOpname($daftar_item, $idlokasi, $idtipeproduk)
    {
      $xls = new PHPExcel();

      $wsStock = $xls->getActiveSheet();
      $wsStock->setTitle('Stock Opname');

      $baris = 1;
      $kolom = 0;

      $wsStock->GetCellByColumnAndRow($kolom, $baris)
        ->setValue('Stock Opname'); $baris++; $baris++;
        
      $wsStock->GetCellByColumnAndRow($kolom, $baris)
        ->setValue('Toko : ' . FHelper::GetLocationName($idlokasi, true)); $baris++;
        
      $wsStock->GetCellByColumnAndRow($kolom, $baris)
        ->setValue('Waktu : ' . date('j M Y, H:i:s')); $baris++; $baris++;
        
      $idkategori = -1;
      $idinventory = -1;
      
      foreach($daftar_item as $item)
      {
        set_time_limit(20);
        
        if($idkategori != $item['idkategori'])
        {
          $baris++; $baris++;
          $idkategori = $item['idkategori'];
          
          //cantumkan kategori
          $wsStock->GetCellByColumnAndRow($kolom, $baris)
            ->setValue('Kategori : ' . FHelper::GetTipeProdukText($idkategori)); $baris++; $baris++;
        }
        
        if($idinventory != $item['id'])
        {
          $baris++;
          $idinventory = $item['id'];
          
          //cantumkan info produk
          $wsStock->GetCellByColumnAndRow($kolom, $baris)
            ->setValue('Produk : ' . FHelper::GetProdukName($idinventory)); $baris++;
          $wsStock->GetCellByColumnAndRow($kolom, $baris)
            ->setValue('Brand : ' . FHelper::GetProdukBrand($idinventory)); $baris++;
          $wsStock->GetCellByColumnAndRow($kolom, $baris)
            ->setValue('Ukuran : ' . FHelper::GetProdukUkuran($idinventory)); $baris++;
            
          $baris++;
        }
        
        //tulis kode barcode
        $wsStock->GetCellByColumnAndRow($kolom, $baris)
          ->setDataType($TYPE_STRING);
          
        $wsStock->GetCellByColumnAndRow($kolom, $baris)
          ->setValueExplicit($item['barcode']); $baris++;
      }
      
      $nama_kategori_produk = FHelper::GetTipeProdukText($idtipeproduk);
      
      $nama_file = "Stock Opname-$nama_kategori_produk-" . str_replace('/', '-', FHelper::GetLocationName($idlokasi, true)) . '-' . date('Y-M-j') . '.xlsx';
      
      $writer = new PHPExcel_Writer_Excel2007($xls);
      $writer->save($nama_file);
      
      return $nama_file;
    }
  
  //---- stock opname - end
  
  
  //----------- inventory by penjualan --------------
  
    public function actionShowInventoryPenjualan()
    {
      $this->menuid = 69;
      $this->parentmenuid = 43; 
      $this->userid_actor = Yii::app()->request->cookies['userid_actor']->value;
      $this->idlokasi = Yii::app()->request->cookies['idlokasi']->value;
      $this->bread_crumb_list = 
        '<li>Laporan</li>'.
        '<li>></li>'.
        '<li>Inventory</li>'.
        '<li>></li>'.
        '<li>Inventory Menurut Penjualan</li>';
      
      $daftar_lokasi = FHelper::GetBranchListData();
      $daftar_sales = FHelper::GetSalesListData();
      $tipe_produk = FHelper::GetTipeProdukList();
      
      $this->layout = "layout-baru";
      $html = $this->renderPartial(
        'vfrm_laporan_inventory_by_penjualan',
        array(
          'daftar_lokasi' => $daftar_lokasi,
          'daftar_sales' => $daftar_sales,
          'tipe_produk' => $tipe_produk
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
      actionCetakInventoryPenjualanCabang()
      
      Deskripsi
      Hitung inventory penjualan
    */
    public function actionCetakInventoryPenjualanCabang()
    {
      /*
        select inventory.nama, count(inventory.id) as jumlah
        from
        pos_sales sales inner join pos_sales_det sales_det on sales.sales_id = sales_det.sales_id
        inner join inv_item on sales_det.item_id = inv_item.id
        inner join inv_inventory inventory on inv_item.idinventory = inventory.id
        where
        sales.date_created >= '2015-7-1 00:00:00' and
        sales.date_created <= '2015-7-30 00:00:00' and
        sales.branch_id = 1 and
        inventory.idkategori = :kategori
        group by 
        inventory.nama
        order by 
        inventory.nama asc
      */
      
      $idlokasi = Yii::app()->request->getParam('idlokasi');
      $awal = Yii::app()->request->getParam('tanggalawal');
      $akhir = Yii::app()->request->getParam('tanggalakhir');
      $idkategori = Yii::app()->request->getParam('tipeproduk');
      
      /*
      $command = Yii::app()->db->createCommand()
        ->select('inventory.id, inventory.nama, count(inventory.id) as jumlah')
        ->from('pos_sales sales')
        ->join('pos_sales_det sales_det', 'sales.sales_id = sales_det.sales_id')
        ->join('inv_item', 'sales_det.item_id = inv_item.id')
        ->join('inv_inventory inventory', 'inv_item.idinventory = inventory.id')
        ->where(
          'sales.open_date >= :awal AND
          sales.open_date <= :akhir AND
          sales.branch_id = :idlokasi AND
          inventory.idkategori = :idkategori AND
          sales.status = "LUNAS"', 
          array(
            ':awal' => date('Y-m-j 00:00:00', strtotime($awal)),
            ':akhir' => date('Y-m-j 23:59:59', strtotime($akhir)),
            ':idkategori' => $idkategori,
            ':idlokasi' => $idlokasi
          )
        );
      $command->distinct = true;
      $command->group = "inventory.id, inventory.nama";
      */
      
      if($idkategori == 1 || $idkategori == 2)
      {
        //kusus frame dan lensa
        
        $command = Yii::app()->db->createCommand()
          ->select("
              inventory.id, inventory.nama, inventory.idkategori, 
              inv_item.barcode, 
              sales.invoice_no, sales.order_no
            ")
          ->from('pos_sales sales')
          ->join('pos_sales_det sales_det', 'sales.sales_id = sales_det.sales_id')
          ->join('inv_item', 'sales_det.item_id = inv_item.id')
          ->join('inv_inventory inventory', 'inv_item.idinventory = inventory.id')
          ->where(
            'sales.open_date >= :awal AND
            sales.open_date <= :akhir AND
            sales.branch_id = :idlokasi AND
            inventory.idkategori = :idkategori AND
            sales.status = "LUNAS"', 
            array(
              ':awal' => date('Y-m-j 00:00:00', strtotime($awal)),
              ':akhir' => date('Y-m-j 23:59:59', strtotime($akhir)),
              ':idkategori' => $idkategori,
              ':idlokasi' => $idlokasi
            )
          );
        
        $command->order = "sales.invoice_no asc, inventory.nama asc";
      }
      else
      {
        //cukup hitung jumlahnya saja
        
        $command = Yii::app()->db->createCommand()
          ->select("
              inventory.id, inventory.nama, inventory.idkategori, 
              count(inv_item.barcode) as jumlah
            ")
          ->from('pos_sales sales')
          ->join('pos_sales_det sales_det', 'sales.sales_id = sales_det.sales_id')
          ->join('inv_item', 'sales_det.item_id = inv_item.id')
          ->join('inv_inventory inventory', 'inv_item.idinventory = inventory.id')
          ->where(
            'sales.open_date >= :awal AND
            sales.open_date <= :akhir AND
            sales.branch_id = :idlokasi AND
            inventory.idkategori = :idkategori AND
            sales.status = "LUNAS"', 
            array(
              ':awal' => date('Y-m-j 00:00:00', strtotime($awal)),
              ':akhir' => date('Y-m-j 23:59:59', strtotime($akhir)),
              ':idkategori' => $idkategori,
              ':idlokasi' => $idlokasi
            )
          );
          
        $command->distinct = true;
        $command->group = "inventory.id, inventory.nama, inventory.idkategori";
        $command->order = "inventory.nama asc";
      }
        
      $data = $command->queryAll();
      
      $xls = new PHPExcel();
      
      $ws = $xls->getActiveSheet();
      $ws->setTitle('Inventory Penjualan');
      
      $baris = 1;
      $kolom = 0;
      
      $nama_cabang = FHelper::GetLocationName($idlokasi, true);
      $rentang_tanggal = date('Y-m-j', strtotime($awal)) . " s/d " . date('Y-m-j', strtotime($akhir));
      $tipe_produk = FHelper::GetTipeProdukText($idkategori);
      
      $ws->GetCellByColumnAndRow($kolom, $baris)
        ->setValue("Laporan Inventory Berdasarkan Penjualan Per Cabang"); $baris++;
      
      $ws->GetCellByColumnAndRow($kolom, $baris)
        ->setValue("Cabang : $nama_cabang"); $baris++;
      
      $ws->GetCellByColumnAndRow($kolom, $baris)
        ->setValue("Rentang tanggal : $rentang_tanggal"); $baris++;
        
      $ws->GetCellByColumnAndRow($kolom, $baris)
        ->setValue("Kategori produk : $tipe_produk"); $baris++;
        
        
      if($idkategori == 1 || $idkategori == 2)
      {
        $baris++;
        $ws->GetCellByColumnAndRow($kolom, $baris)
          ->setValue("Nama Produk"); $kolom++;
        $ws->GetCellByColumnAndRow($kolom, $baris)
          ->setValue("Tipe Produk"); $kolom++;
        $ws->GetCellByColumnAndRow($kolom, $baris)
          ->setValue("Barcode"); $kolom++;
        $ws->GetCellByColumnAndRow($kolom, $baris)
          ->setValue("Invoice"); $kolom++;
        $ws->GetCellByColumnAndRow($kolom, $baris)
          ->setValue("Order"); $kolom++;
          
          
        $kolom = 0;
        $baris++;
        
        foreach($data as $record)
        {
          set_time_limit(20);
          
          $ws->GetCellByColumnAndRow($kolom, $baris)
            ->setValue($record['nama']); $kolom++;
            
          $ukuran_produk = FHelper::GetProdukUkuran($record['id']);
          $ws->GetCellByColumnAndRow($kolom, $baris)
            ->setValue($ukuran_produk); $kolom++;
            
          $ws->GetCellByColumnAndRow($kolom, $baris)
            ->setValue($record['barcode']); $kolom++;
            
          //idkategori 1 = frame; 2 = lensa 
          if($record['idkategori'] == 1 || $record['idkategori'] == 2)
          {
            $ws->GetCellByColumnAndRow($kolom, $baris)
              ->setValue($record['invoice_no']); $kolom++;
              
            $ws->GetCellByColumnAndRow($kolom, $baris)
              ->setValue($record['order_no']); $kolom++;
          }
            
          $baris++;
          $kolom = 0;
        }
      }
      else
      {
        //hanya menampilkan jumlah.
        
        $baris++;
        $ws->GetCellByColumnAndRow($kolom, $baris)
          ->setValue("Nama Produk"); $kolom++;
        $ws->GetCellByColumnAndRow($kolom, $baris)
          ->setValue("Tipe Produk"); $kolom++;
        $ws->GetCellByColumnAndRow($kolom, $baris)
          ->setValue("Warna"); $kolom++;
        $ws->GetCellByColumnAndRow($kolom, $baris)
          ->setValue("Jumlah"); $kolom++;
          
        $kolom = 0;
        $baris++;
        
        foreach($data as $record)
        {
          set_time_limit(20);
          
          if($idkategori == 3) //softlens
          {
            //ambil informasi warna
            $id_inventory = $record['id'];
            $command = Yii::app()->db->createCommand()
              ->select('*')
              ->from('inv_type_softlens')
              ->where(
                'id_item = :idinventory',
                array(
                  ':idinventory' => $id_inventory
                )
              );
            $info_frame = $command->queryRow();
            $warna = $info_frame['color'];
          }
          else
          {
            $warna = "--";
          }
          
          $ws->GetCellByColumnAndRow($kolom, $baris)
            ->setValue($record['nama']); $kolom++;
            
          $ukuran_produk = FHelper::GetProdukUkuran($record['id']);
          $ws->GetCellByColumnAndRow($kolom, $baris)
            ->setValue($ukuran_produk); $kolom++;
            
          $ws->GetCellByColumnAndRow($kolom, $baris)
            ->setValue($warna); $kolom++;
            
          $ws->GetCellByColumnAndRow($kolom, $baris)
            ->setValue($record['jumlah']); $kolom++;
            
          $baris++;
          $kolom = 0;
        }
      }
      
      $nama_file = "Inventory Penjualan Per Cabang-{$nama_cabang}-{$tipe_produk}.xlsx";
      
      $writer = new PHPExcel_Writer_Excel2007($xls);
      $writer->save($nama_file);
      
      $html = CHtml::link(
        'file inventory penjualan',
        $nama_file
      );
      
      echo CJSON::encode(array('nama_file' => $html));
    }
    
    
    /*
      actionCetakInventoryPenjualanSales()
      
      Deskripsi
      Fungsi untuk menghitung jumlah inventory berdasarkan penjualan. Dihitung
      berdasarkan sales yang dipilih.
    */
    public function actionCetakInventoryPenjualanSales()
    {
      /*
        select inventory.nama, count(inventory.id) as jumlah
        from
        pos_sales sales inner join pos_sales_det sales_det on sales.sales_id = sales_det.sales_id
        inner join inv_item on sales_det.item_id = inv_item.id
        inner join inv_inventory inventory on inv_item.idinventory = inventory.id
        where
        sales.date_created >= '2015-7-1 00:00:00' and
        sales.date_created <= '2015-7-30 00:00:00' and
        sales.created_by = :sales and
        inventory.idkategori = :kategori
        group by 
        inventory.nama
        order by 
        inventory.nama asc
      */
      
      $idsales = Yii::app()->request->getParam('idsales');
      $awal = Yii::app()->request->getParam('tanggalawal');
      $akhir = Yii::app()->request->getParam('tanggalakhir');
      $idkategori = Yii::app()->request->getParam('tipeproduk');
      
      $sales = FHelper::GetUserName($idsales);
      
      $command = Yii::app()->db->createCommand()
        ->select('sales.branch_id as idlokasi, inventory.id, inventory.nama, count(inventory.id) as jumlah')
        ->from('pos_sales sales')
        ->join('pos_sales_det sales_det', 'sales.sales_id = sales_det.sales_id')
        ->join('inv_item', 'sales_det.item_id = inv_item.id')
        ->join('inv_inventory inventory', 'inv_item.idinventory = inventory.id')
        ->where(
          'sales.date_created >= :awal AND
          sales.date_created <= :akhir AND
          sales.created_by = :sales AND
          inventory.idkategori = :idkategori', 
          array(
            ':awal' => date('Y-m-j 00:00:00', strtotime($awal)),
            ':akhir' => date('Y-m-j 23:59:59', strtotime($akhir)),
            ':idkategori' => $idkategori,
            ':sales' => $sales
          )
        );
      $command->distinct = true;
      $command->group = "inventory.id, inventory.nama";
        
      $data = $command->queryAll();
      
      $xls = new PHPExcel();
      
      $ws = $xls->getActiveSheet();
      $ws->setTitle('Inventory Penjualan');
      
      $baris = 1;
      $kolom = 0;
      
      $nama_cabang = "";
      $rentang_tanggal = date('Y-m-j', strtotime($awal)) . " s/d " . date('Y-m-j', strtotime($akhir));
      $tipe_produk = FHelper::GetTipeProdukText($idkategori);
      
      $ws->GetCellByColumnAndRow($kolom, $baris)
        ->setValue("Laporan Inventory Berdasarkan Penjualan Per Sales"); $baris++;
      
      $ws->GetCellByColumnAndRow($kolom, $baris)
        ->setValue("Sales : $sales"); $baris++;
      
      $ws->GetCellByColumnAndRow($kolom, $baris)
        ->setValue("Rentang tanggal : $rentang_tanggal"); $baris++;
        
      $ws->GetCellByColumnAndRow($kolom, $baris)
        ->setValue("Kategori produk : $tipe_produk"); $baris++;
        
      $baris++;
          
      
      foreach($data as $record)
      {
        set_time_limit(20);
        
        $temp_nama_cabang = FHelper::GetLocationName($record['idlokasi'], true);
        
        if($nama_cabang != $temp_nama_cabang)
        {
          $nama_cabang = $temp_nama_cabang;
          
          $baris++; $baris++;
          
          //tulis nama cabang
          $ws->GetCellByColumnAndRow($kolom, $baris)
            ->setValue("Cabang : $nama_cabang ({$record['idlokasi']})");
          
          $baris++;
          $kolom = 0;
          
          $ws->GetCellByColumnAndRow($kolom, $baris)
            ->setValue("Nama Produk"); $kolom++;
          $ws->GetCellByColumnAndRow($kolom, $baris)
            ->setValue("Tipe Produk"); $kolom++;
          $ws->GetCellByColumnAndRow($kolom, $baris)
            ->setValue("Jumlah"); $kolom++;
            
          $kolom = 0;
          $baris++;
        }
        
        $ws->GetCellByColumnAndRow($kolom, $baris)
          ->setValue($record['nama']); $kolom++;
          
        $ukuran_produk = FHelper::GetProdukUkuranMini($record['id']);
        $ws->GetCellByColumnAndRow($kolom, $baris)
          ->setValue($ukuran_produk); $kolom++;
          
        $ws->GetCellByColumnAndRow($kolom, $baris)
          ->setValue($record['jumlah']); $kolom++;
          
        $baris++;
        $kolom = 0;
      }
      
      $nama_file = "Inventory Penjualan Per Sales-{$sales}-{$tipe_produk}.xlsx";
      
      $writer = new PHPExcel_Writer_Excel2007($xls);
      $writer->save($nama_file);
      
      $html = CHtml::link(
        'file inventory penjualan',
        $nama_file
      );
      
      echo CJSON::encode(array('nama_file' => $html));
    }
  
  //----------- inventory by penjualan --------------
  
  
  //----------- laporan stock minimum ----------------
  
    public function actionStockMinimum()
    {
      $this->menuid = 69;
      $this->parentmenuid = 43; 
      $this->userid_actor = Yii::app()->request->cookies['userid_actor']->value;
      $this->idlokasi = Yii::app()->request->cookies['idlokasi']->value;
      $this->bread_crumb_list = 
        '<li>Laporan</li>'.
        '<li>></li>'.
        '<li>Inventory</li>'.
        '<li>></li>'.
        '<li>Stock Minimum</li>';
      
      $daftar_lokasi = FHelper::GetBranchListData();
      
      $daftar_tipe = FHelper::GetTipeProdukList();
      
      $this->layout = "layout-baru";
      $html = $this->renderPartial(
        'vfrm_laporan_stock_minimum',
        array(
          'daftar_lokasi' => $daftar_lokasi,
          'daftar_tipe_produk' => $daftar_tipe
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
  
    public function actionStockMinimumHitung()
    {
      $idlokasi = Yii::app()->request->getParam('idlokasi');
      $idkategori = Yii::app()->request->getParam('idkategori');
      $bikin_file = (Yii::app()->request->getParam('bikin_file') == 1 ? true : false);
      
      $command = Yii::app()->db->createCommand()
        ->select('item.idinventory, inv.nama, count(item.id) as jumlah')
        ->from('inv_item item')
        ->join('inv_inventory inv', 'inv.id = item.idinventory')
        ->where(
          'item.idlokasi = :idlokasi AND
          item.idstatus = 3 AND
          inv.idkategori = :idkategori',
          array(
            ':idlokasi' => $idlokasi,
            ':idkategori' => $idkategori
          )
        );
      $command->distinct = true;
      $command->group = "item.idinventory";
      $hasil = $command->queryAll();
      
      $nama_file = '';
      if($bikin_file == true)
      {
        $nama_file = $this->StockMinimum_BikinFile($idlokasi, $hasil);
        
        $nama_file = CHtml::link(
          'stock minimum',
          $nama_file
        );
      }
      
      $html = $this->renderPartial(
        'v_laporan_stock_minimum',
        array(
          'data' => $hasil,
          'idlokasi' => $idlokasi,
          'nama_file' => $nama_file
        ),
        true
      );
      
      echo CJSON::encode(array('html' => $html, 'nama_file' => $nama_file) );
      
    }
    
    private function StockMinimum_BikinFile($idtoko, $data)
    {
      $xlsStock = new PHPExcel();
      $wsStock = $xlsStock->getActiveSheet();
      $wsStock->setTitle('Stock Minimum');
      
      $baris = 1;
      $kolom = 0;
      
      $wsStock->GetCellByColumnAndRow($kolom, $baris)
        ->setValue('Stock Minimum Toko'); $baris++; $baris++;
        
      $wsStock->GetCellByColumnAndRow($kolom, $baris)
        ->setValue('Toko : ' . FHelper::GetLocationName($idtoko, true)); $baris++;
        
      $wsStock->GetCellByColumnAndRow($kolom, $baris)
        ->setValue('Waktu : ' . date('j M Y, H:i:s')); $baris++; $baris++;
        
      $baris++;
      $kolom = 0;
      foreach($data as $produk)
      {
        $idproduk = $produk['idinventory'];
        $wsStock->GetCellByColumnAndRow($kolom, $baris)
          ->setValue($produk['nama']); $kolom++;
          
        $wsStock->GetCellByColumnAndRow($kolom, $baris)
          ->setValue(FHelper::GetProdukUkuran($idproduk)); $kolom++;
          
        $wsStock->GetCellByColumnAndRow($kolom, $baris)
          ->setValue($produk['jumlah']); $kolom++;
          
        $min_stock = FHelper::GetMinimumStock($inventory['idinventory'], $idlokasi);
        
        $wsStock->GetCellByColumnAndRow($kolom, $baris)
          ->setValue($min_stock);
          
        $baris++;
        $kolom = 0;
      }
      
      $nama_toko = trim(FHelper::GetLocationName($idtoko, true));
      $nama_toko = str_replace('/', '-', $nama_toko);
      $namafile = "stock_minimum_toko_{$nama_toko}.xlsx";
      $writer = new PHPExcel_Writer_Excel2007($xlsStock);
      $writer->save($namafile);
      
      return $namafile;
    }
  
  //----------- laporan stock minimum ----------------
  

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