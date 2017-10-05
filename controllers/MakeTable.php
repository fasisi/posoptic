<?php

class MakeTable
{
  public   
    $array_rows_per_page,
    $array_sort_by,
    $array_goto_page,
    $rows_per_page,
    $sort_by,
    $sort_direction, //0 = asc; 1 = desc
    $search,
    $offset;
  public   
    $list_type;
  public
    $action_name = 'Validasi_RefreshTable',
    $action_name2 = 'Validasi_RefreshTable2';
  public   $table_content;
  public   $pageno = 1, $pages = 1;
  
  
	public static function Render($data)
	{
		return Yii::app()->controller->renderPartial(
		  '//maketable/table',
		  array(
		    'table_data' => $data
      ),
		  true
    );
	}
	
	/**
	  Menghitung berapa banyak pages, berdasarkan jumlah rows dan rows_per_page
	*/
	public function SetupPages($rows_count)
	{
	  $this->rows_per_page = Yii::app()->request->getParam('rowsperpage');
	  $this->rows_per_page = ($this->rows_per_page == 0 ? 50 : $this->rows_per_page );
	  
	  $this->pageno = Yii::app()->request->getParam('pageno');
	  $this->pageno = ($this->pageno == 0 ? 1 : $this->pageno );
	  $this->offset = ($this->pageno - 1) * $this->rows_per_page;
	  $this->array_sort_by = array();
	  $this->array_rows_per_page = array();
	  $this->array_rows_per_page[10] = 10;
	  $this->array_rows_per_page[20] = 20;
	  $this->array_rows_per_page[50] = 50;
	  $this->array_rows_per_page[100] = 100;
	  $this->array_rows_per_page[200] = 200;
	  
	  $this->array_goto_page = array();
	  
	  $this->pages = intval($rows_count / $this->rows_per_page);
	  if( ($rows_count % $this->rows_per_page) > 0)
	  {
	    $this->pages++;
	  }
	  
	  for($pageke = 1; $pageke <= $this->pages; $pageke++)
	  {
	    $this->array_goto_page[$pageke] = $pageke;
	  }
	}
	
	/**
	  Menghasilkan record data yang akan ditampilkan pada page saat ini
	*/
	private function GenerateData()
	{
	  $command = Yii::app()->db->createCommand();
	  $command->text = $this->query;
	  $command->offset = ($this->pageno - 1) * $this->rows_per_page;
	  $command->limit = $this->rows_per_page;
	  
	  return $command->queryAll();
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