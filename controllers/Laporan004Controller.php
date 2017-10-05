<?php

class Laporan004Controller extends FController
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
      '<li>Sejarah Pembelian Customer</li>';
      
    $this->layout = 'layout-baru';
    
    $daftar_customer = $this->GetListCustomer();
    
    $html = $this->renderPartial(
      'vfrm_laporan',
      array(
        
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
	  GetListCustomer()
	  
	  Deskripsi
	  Fungsi untuk mengambil daftar customer
	*/
	private function GetListCustomer()
	{
	  $command = Yii::app()->db->createCommand()
	    ->select('*')
	    ->from('mtr_customer')
	    ->where(
	      "is_del = 'N'"
      );
    $command->order = "name asc";
    
    $hasil = $command->queryAll();
    
    return $hasil;
	}
	
	/*
	  actionResep()
	  
	  Deskripsi
	  Menampilkan informasi resep suatu customer. Informasi resep diambil dari tabel
	  pos_sales_presc
	*/
	public function actionResep()
	{
	  $idcustomer = Yii::app()->request->getParam('idcustomer');
	}
	
	/*
	  actionSejarahPembelian()
	  
	  Deskripsi
	  Menampilkan informasi sejarah pembelian suatu customer. Informasi resep diambil
	  dari tabel pos_sales
	  
	*/
	public function actionSejarahPembelian()
	{
	  $idcustomer = Yii::app()->request->getParam('idcustomer');
	  
	  $command = Yii::app()->db->createCommand()
	    ->select('*')
	    ->from('pos_sales')
	    ->where(
	      'customer_id = :idcustomer',
	      array(
	        ':idcustomer' => $idcustomer
        )
      );
    $command->order = 'open_date desc';
    $sejarah_transaksi = $command->queryAll();
    
    $html = $this->renderPartial(
      'v_list_transaksi',
      array(
        'sejarah_transaksi' => $sejarah_transaksi
      ),
      true
    );
    
    echo CJSON::encode( array('html' => $html) );
	}
	
	public function actionRefreshList2()
	{
	  /*
      1. Ambil daftar gambar dari directory /images/user_images/*.jpg
      2. Pass array hasil langkah 1 ke view untuk ditampilkan menggunakan plugin
         imagepicker.
      3. kembalikan hasil ke client dalam bentuk JSON
    */
    
    //populate records
      //$this->actionHimpunDataGambar();
    
    //get records
      $rowsperpage = Yii::app()->request->getParam('rowsperpage');
      $rowsperpage = ( isset($rowserpage) == false ? 20 : $rowsperpage );
      
      $sortby = Yii::app()->request->getParam('sortby');
      
      
      $pageno = Yii::app()->request->getParam('pageno');
      $pageno = ( isset($pageno) == false ? 1 : $pageno );
      
      $search = Yii::app()->request->getParam('search');
      $search = ( $search == "" ? "" : $search );
      
      $list_type = "Laporan004";
      
      $array_rows_per_page[10] = 10;
      $array_rows_per_page[20] = 20;
      $array_rows_per_page[40] = 40;
      $array_rows_per_page[50] = 50;
      $array_rows_per_page[100] = 100;
      $array_rows_per_page[200] = 200;
      
      $array_sort_by[1] = 'nama file';
      
      /*
        mengambil record dari tabel media_gallery
      */
      $command = Yii::app()->db->createCommand();
      $command->select = "*";
      $command->from = "mtr_customer";
      
      if($search != '')
      {
        $command->where = " name like :search ";
        $command->params = array(':search' => "%$search%" );
      }
      
      $command->order = "name asc";
      $command->offset = ($pageno - 1) * $rowsperpage;
      $command->limit = $rowsperpage;
      $hasil = $command->queryAll();
      
      
      $command2 = Yii::app()->db->createCommand();
      $command2->select = "*";
      $command2->from = "mtr_customer";
      
      if($search != '')
      {
        $command2->where = " name like :search ";
        $command2->params = array(':search' => "%$search%" );
      }
      
      $hasil2 = $command2->queryAll();
      $rows = count($hasil2);
      
      $table_content = $this->renderPartial(
        'v_daftar_customer',
        array(
          'daftar_customer' => $hasil
        ),
        true
      );
      
    //get records
    
    
    
    //MakeTable init sequences
      $maketable = new Maketable();
      $maketable->pages = intval($rows / $rowsperpage);
      
      if( ($rows % $rowsperpage) > 0)
      {
        $maketable->pages++;
      }
      
      if($maketable->pages == 0)
      {
        $maketable->pages = 1;
      }
      
      for($pageke = 1; $pageke <= $maketable->pages; $pageke++)
      {
        $array_goto_page[$pageke] = $pageke;
      }
      
      $maketable->list_type = "Laporan004";
      $maketable->search = $search;
      $maketable->pageno = $pageno;
      $maketable->table_content = $table_content;
      $maketable->array_rows_per_page = $array_rows_per_page;
      $maketable->rows_per_page = $rowsperpage;
      $maketable->array_sort_by = $array_sort_by;
      $maketable->sort_by = $sortby;
      $maketable->array_goto_page = $array_goto_page;
      $maketable->action_name = "Laporan004_RefreshTable";
      $maketable->action_name2 = "Laporan004_RefreshTable2";
      
      $html = $maketable->Render($maketable);
    //MakeTable init sequences
    
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