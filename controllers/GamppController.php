<?php

class GamppController extends Controller
{
	public function actionIndex()
	{
		$this->render('index');
		
		
		
	}
	
	public function actionKirim($nomor, $pesan)
	{
	  $gamppapi = new Gamppapi();
	  
	  $hasil = $gamppapi->sendsms($nomor, $pesan);
	  
	  return $hasil;
	}
	
	public function actionCekKredit()
	{
	  $gamppapi = new Gamppapi();
	  
	  $hasil = $gamppapi->cekkredit();
	  
	  echo print_r($hasil, true);
	}
	
	public function actionReadOutbox()
	{
	  $gamppapi = new Gamppapi();
	  
	  $hasil = $gamppapi->readoutbox('', '');
	  
	  echo print_r($hasil, true);
	}
	
	public function actionReadInbox()
	{
	  $gamppapi = new Gamppapi();
	  
	  $hasil = $gamppapi->readinbox('', '');
	  
	  echo print_r($hasil, true);
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