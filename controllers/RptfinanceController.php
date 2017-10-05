<?php
class RptfinanceController extends FController
{
	private $success_message = '';

	private function getMonthList()
	{
		$month_list[0] = '-- Pilih Bulan --';
		for($i=1;$i<13;$i++) $month_list[str_pad($i, 2, '0', STR_PAD_LEFT)] = str_pad($i, 2, '0', STR_PAD_LEFT);

		return $month_list;
	}

	private function getYearList()
	{
		$year_list[0] = '-- Pilih Tahun --';
		for($i=2013;$i<date("Y") + 4;$i++) $year_list[$i] = $i;

		return $year_list;
	}

	private function calculate_laba_rugi(&$report, $strMonth, $strYear) {
		$pu1 = FHelper::sum('410', 1, $strMonth, $strYear);
		$pu2 = FHelper::sum('411', 1, $strMonth, $strYear);
		$pu3 = FHelper::sum('412', 1, $strMonth, $strYear);

		$pendapatan_usaha = $pu1 + $pu2 + $pu3;
		$report['pendapatan_usaha'] = FHelper::ntb($pendapatan_usaha);

		$report['410'] = FHelper::ntb($pu1);
		$report['411'] = FHelper::ntb($pu2);
		$report['412'] = FHelper::ntb($pu3);

		$beban_bp = FHelper::sum('5', 0, $strMonth, $strYear);
		$report['beban_bp'] = FHelper::ntb($beban_bp);

		$report['5110'] = FHelper::ntb(FHelper::sum('5110', 0, $strMonth, $strYear));
		$report['5120'] = FHelper::ntb(FHelper::sum('5120', 0, $strMonth, $strYear));
		$report['5130'] = FHelper::ntb(FHelper::sum('5130', 0, $strMonth, $strYear));
		$report['5140'] = FHelper::ntb(FHelper::sum('5140', 0, $strMonth, $strYear));
		$report['5150'] = FHelper::ntb(FHelper::sum('5150', 0, $strMonth, $strYear));
		$report['5160'] = FHelper::ntb(FHelper::sum('5160', 0, $strMonth, $strYear));

		$laba_kotor = $beban_bp - $pendapatan_usaha;
		$report['laba_kotor'] = FHelper::ntb($laba_kotor);

		$bo1 = FHelper::sum('6100', 0, $strMonth, $strYear);
		$bo2 = FHelper::sum('6101', 0, $strMonth, $strYear);
		$bo3 = FHelper::sum('6102', 0, $strMonth, $strYear);
		$bo4 = FHelper::sum('6103', 0, $strMonth, $strYear);
		$bo5 = FHelper::sum('6104', 0, $strMonth, $strYear);
		$bo6 = FHelper::sum('6105', 0, $strMonth, $strYear);
		$bo7 = FHelper::sum('6106', 0, $strMonth, $strYear);
		$bo8 = FHelper::sum('6107', 0, $strMonth, $strYear);
		$bo9 = FHelper::sum('6108', 0, $strMonth, $strYear);

		$beban_operasional = $bo1 + $bo2 + $bo3 + $bo4 + $bo5 + $bo6 + $bo7 + $bo8 + $bo9;
		$report['bo'] = FHelper::ntb($beban_operasional);

		$report['6100'] = FHelper::ntb($bo1);
		$report['6101'] = FHelper::ntb($bo2);
		$report['6102'] = FHelper::ntb($bo3);
		$report['6103'] = FHelper::ntb($bo4);
		$report['6104'] = FHelper::ntb($bo5);
		$report['6105'] = FHelper::ntb($bo6);
		$report['6106'] = FHelper::ntb($bo7);
		$report['6107'] = FHelper::ntb($bo8);
		$report['6108'] = FHelper::ntb($bo9);

		$bua = FHelper::sum('62', 0, $strMonth, $strYear);
		$report['bua'] = FHelper::ntb($bua);
		
		$jum_bu = $beban_operasional + $bua;
		$report['jum_bu'] = FHelper::ntb($jum_bu);

		$labarugi_usaha = $laba_kotor - $jum_bu;
		$report['labarugi_usaha'] = FHelper::ntb($labarugi_usaha);

		$pendapatan_ll = FHelper::sum('71', 1, $strMonth, $strYear);
		$report['pendapatan_ll'] = FHelper::ntb($pendapatan_ll);

		$beban_ll = FHelper::sum('72', 0, $strMonth, $strYear);
		$report['beban_ll'] = FHelper::ntb($beban_ll);

		$labarugi_before_tax = $labarugi_usaha + $pendapatan_ll - $beban_ll;
		$report['labarugi_before_tax'] = FHelper::ntb($labarugi_before_tax);

		$taksiran_pp = FHelper::sum('750', 0, $strMonth, $strYear);
		$report['taksiran_pp'] = FHelper::ntb($taksiran_pp);

		$labarugi_bersih = $labarugi_before_tax - $taksiran_pp;
		$report['labarugi_bersih'] = FHelper::ntb($labarugi_bersih);

		return $labarugi_bersih;
	}

	//Get data for Laporan Neraca
	private function getReportH01Data($strMonth, $strYear, $tplName)
	{
		$report = array();

		$report['month'] = $strMonth;
		$report['year'] = $strYear;

		$al1 = FHelper::sum('111', 0, $strMonth, $strYear);
		$al2 = FHelper::sum('112', 0, $strMonth, $strYear);
		$al3 = FHelper::sum('113', 0, $strMonth, $strYear);
		$al4 = FHelper::sum('114', 0, $strMonth, $strYear);
		$al5 = FHelper::sum('115', 0, $strMonth, $strYear);
		$al6 = FHelper::sum('116', 0, $strMonth, $strYear);
		$al7 = FHelper::sum('117', 0, $strMonth, $strYear);
		$al8 = FHelper::sum('118', 0, $strMonth, $strYear);

		$aktiva_lancar = $al1 + $al2 + $al3 + $al4 + $al5 + $al7 + $al8;
		$report['aktiva_lancar'] = FHelper::ntb($aktiva_lancar);

		$report['111'] = FHelper::ntb($al1);
		$report['112'] = FHelper::ntb($al2);
		$report['113'] = FHelper::ntb($al3);
		$report['114'] = FHelper::ntb($al4);
		$report['115'] = FHelper::ntb($al5);
		$report['116'] = FHelper::ntb($al6);
		$report['117'] = FHelper::ntb($al7);
		$report['118'] = FHelper::ntb($al8);

		$at21 = FHelper::sum('121', 0, $strMonth, $strYear);
		$at22 = FHelper::sum('124', 0, $strMonth, $strYear);
		$at23 = FHelper::sum('125', 0, $strMonth, $strYear);
		$at24 = FHelper::sum('127', 0, $strMonth, $strYear);

		$aktiva_tdk_lancar = $at21 + $at22 + $at23 + $at24;
		$report['aktiva_tdk_lancar'] = FHelper::ntb($aktiva_tdk_lancar);

		$report['121'] = FHelper::ntb($atl1);
		$report['124'] = FHelper::ntb($atl2);
		$report['125'] = FHelper::ntb($atl3);
		$report['127'] = FHelper::ntb($atl4);

		$kl1 = FHelper::sum('211', 1, $strMonth, $strYear);
		$kl2 = FHelper::sum('212', 1, $strMonth, $strYear);
		$kl3 = FHelper::sum('213', 1, $strMonth, $strYear);
		$kl4 = FHelper::sum('214', 1, $strMonth, $strYear);
		$kl5 = FHelper::sum('215', 1, $strMonth, $strYear);

		$kewajiban_lancar = $kl1 + $kl2 + $kl3 + $kl4 + $kl5;
		$report['kewajiban_lancar'] = FHelper::ntb($kewajiban_lancar);

		$report['211'] = FHelper::ntb($kl1);
		$report['212'] = FHelper::ntb($kl2);
		$report['213'] = FHelper::ntb($kl3);
		$report['214'] = FHelper::ntb($kl4);
		$report['215'] = FHelper::ntb($kl5);

		$kt21 = FHelper::sum('221', 1, $strMonth, $strYear);
		$kt22 = FHelper::sum('222', 1, $strMonth, $strYear);
		$kt23 = FHelper::sum('223', 1, $strMonth, $strYear);

		$kewajiban_tdk_lancar = $kt21 + $kt22 + $kt23;
		$report['kewajiban_tdk_lancar'] = FHelper::ntb($kewajiban_tdk_lancar);

		$report['221'] = FHelper::ntb($ktl1);
		$report['222'] = FHelper::ntb($ktl2);
		$report['223'] = FHelper::ntb($ktl4);

		$modal1 = FHelper::sum('310', 1, $strMonth, $strYear);
		$modal2 = FHelper::sum('320', 1, $strMonth, $strYear);

		$modal = $modal1 + $modal2;
		$report['modal'] = FHelper::ntb($modal);

		$report['310'] = FHelper::ntb($modal1);
		$report['320'] = FHelper::ntb($modal2);

		$laba_rugi_ditahan = FHelper::sum('34', 0, $strMonth, $strYear);
		$report['laba_rugi_ditahan'] = FHelper::ntb($laba_rugi_ditahan);

		$laba_rugi = $this->calculate_laba_rugi($rpt, $strMonth, $strYear);
		$report['laba_rugi'] = FHelper::ntb($laba_rugi);

		$jumlah_aktiva = $aktiva_lancar + $aktiva_tdk_lancar;
		$report['jumlah_aktiva'] =  FHelper::ntb($jumlah_aktiva);

		$jumlah_passiva = $kewajiban_lancar + $kewajiban_tdk_lancar + $modal + $laba_rugi_ditahan + $laba_rugi;
		$report['jumlah_passiva'] = FHelper::ntb($jumlah_passiva);

		//print_r($report);
		//exit();

		$v_report = $this->renderPartial(
			$tplName,
			array(
				 'report' => $report
			 ),
			 true
		);

		return $v_report;
	}

	//Get data for Laporan Laba/Rugi
	private function getReportH02Data($strMonth, $strYear, $tplName)
	{
		$report = array();

		$report['month'] = $strMonth;
		$report['year'] = $strYear;

		$this->calculate_laba_rugi(&$report, $strMonth, $strYear);

		//print_r($report);
		//exit();

		$v_report = $this->renderPartial(
			$tplName,
			array(
				 'report' => $report
			 ),
			 true
		);

		return $v_report;
	}

	//Index Laporan Neraca
	public function actionIndexH01()
	{
		$menuid = 28;
		$parentmenuid = 44;

		$userid_actor = Yii::app()->request->getParam('userid_actor');
		$idlokasi = Yii::app()->request->cookies['idlokasi']->value;

		$allow_read = FHelper::AllowMenu($menuid, FHelper::GetGroupId($userid_actor), 'read');
		if ($allow_read) {
			$month_list = $this->getMonthList();
			$year_list = $this->getYearList();
			$strMonth = date("n")-1;
			$strMonth = str_pad($strMonth, 2, '0', STR_PAD_LEFT);
			$strYear = date("Y");

			$TheMenu = FHelper::RenderMenu(0, $userid_actor, $parentmenuid);

			$this->userid_actor = $userid_actor;
			$this->parentmenuid = $parentmenuid;

			$this->bread_crumb_list = '
				<li>Laporan</li>
				<li>></li>
				<li>Keuangan</li>
				<li>></li>
				<li>Neraca</li>';

			$this->layout = 'layout-baru';

			$TheContent = $this->renderPartial(
				'indexh01',
				array(
					'menuid' => $menuid,
					'userid_actor' => $userid_actor,
					'month_list' => $month_list,
					'month' => $strMonth,
					'year_list' => $year_list,
					'year' => $strYear
				),
				true
			);
		}
		else
		{
			$this->bread_crumb_list = '
				<li>Not Authorize</li>';

			$this->layout = 'layout-baru';

			$TheContent = $this->renderPartial(
				'not_auth',
				array(
					'userid_actor' => $userid_actor
				),
				true
			);
		}

		$this->render(
			'index',
			array(
				'TheMenu' => $TheMenu,
				'TheContent' => $TheContent,
				'userid_actor' => $userid_actor
			)
		);
	}

	//View Laporan Neraca
	public function actionViewReportH01()
	{
		$userid_actor = Yii::app()->request->getParam('userid_actor');
		$idlokasi = Yii::app()->request->cookies['idlokasi']->value;

		$strMonth = Yii::app()->request->getParam('m');
		$strYear = Yii::app()->request->getParam('y');
		//echo "$strMonth - $strYear";
		//exit();

		if(!empty($strMonth) && !empty($strYear))
		{
			//$strMonth = str_pad($strMonth, 2, '0', STR_PAD_LEFT);

			$v_reporth01 = $this->getReportH01Data($strMonth, $strYear, 'v_reporth01');

			echo CJSON::encode(
				 array(
					  'report' => $v_reporth01,
					  'status' => 'ok'
				 )
			);

			//AuditLog
			$data = "View Laporan Neraca, $strMonth, $strYear";

			FAudit::add('LAPORANJEUANGAN', 'View', FHelper::GetUserName($userid_actor), $data);
		 }
		 else
		 {
			  //strMonth, strYear empty
		 }
	}

	//Export Laporan Neraca
	public function actionExportReportH01()
	{
		$userid_actor = Yii::app()->request->getParam('userid_actor');
		$idlokasi = Yii::app()->request->cookies['idlokasi']->value;

		$strMonth = Yii::app()->request->getParam('m');
		$strYear = Yii::app()->request->getParam('y');

		if(!empty($strMonth) && !empty($strYear))
		{
			$strMonth = str_pad($i, 2, '0', STR_PAD_LEFT);

			$v_reporth01 = $this->getReportH01Data($strMonth, $strYear, 'e_reporth01');

			echo $v_reporth01;

			//AuditLog
			$data = "Export Laporan Neraca $strMonth, $strYear";

			FAudit::add('LAPORANKEUANGAN', 'Export', FHelper::GetUserName($userid_actor), $data);
		 }
		 else
		 {
			  //strMonth, strYear empty
		 }
	}

	//Laporan Laba/Rugi
	public function actionIndexH02()
	{
		$menuid = 27;
		$parentmenuid = 44;

		$userid_actor = Yii::app()->request->getParam('userid_actor');
		$idlokasi = Yii::app()->request->cookies['idlokasi']->value;

		$allow_read = FHelper::AllowMenu($menuid, FHelper::GetGroupId($userid_actor), 'read');
		if ($allow_read) {
			$month_list = $this->getMonthList();
			$year_list = $this->getYearList();
			$strMonth = date("n")-1;
			$strMonth = str_pad($strMonth, 2, '0', STR_PAD_LEFT);
			$strYear = date("Y");

			$TheMenu = FHelper::RenderMenu(0, $userid_actor, $parentmenuid);

			$this->userid_actor = $userid_actor;
			$this->parentmenuid = $parentmenuid;

			$this->bread_crumb_list = '
				<li>Laporan</li>
				<li>></li>
				<li>Keuangan</li>
				<li>></li>
				<li>Laba / Rugi</li>';

			$this->layout = 'layout-baru';

			$TheContent = $this->renderPartial(
				'indexh02',
				array(
					'menuid' => $menuid,
					'userid_actor' => $userid_actor,
					'month_list' => $month_list,
					'month' => $strMonth,
					'year_list' => $year_list,
					'year' => $strYear
				),
				true
			);
		}
		else
		{
			$this->bread_crumb_list = '
				<li>Not Authorize</li>';

			$this->layout = 'layout-baru';

			$TheContent = $this->renderPartial(
				'not_auth',
				array(
					'userid_actor' => $userid_actor
				),
				true
			);
		}

		$this->render(
			'index',
			array(
					'TheMenu' => $TheMenu,
					'TheContent' => $TheContent,
					'userid_actor' => $userid_actor
			)
		);
	}

	//View Laporan Laba/Rugi
	public function actionViewReportH02()
	{
		$userid_actor = Yii::app()->request->getParam('userid_actor');
		$idlokasi = Yii::app()->request->cookies['idlokasi']->value;

		$strMonth = Yii::app()->request->getParam('m');
		$strYear = Yii::app()->request->getParam('y');

		if(!empty($strMonth) && !empty($strYear))
		{
			//$strMonth = str_pad($i, 2, '0', STR_PAD_LEFT);

			$v_reporth02 = $this->getReportH02Data($strMonth, $strYear, 'v_reporth02');

			echo CJSON::encode(
				 array(
					  'report' => $v_reporth02,
					  'status' => 'ok'
				 )
			);

			//AuditLog
			$data = "View Laporan Laba / Rugi, $strMonth, $strYear";

			FAudit::add('LAPORANKEUANGAN', 'View', FHelper::GetUserName($userid_actor), $data);
		 }
		 else
		 {
			  //strMonth, strYear empty
		 }
	}

	//Export Laporan Laba/Rugi
	public function actionExportReportH02()
	{
		$userid_actor = Yii::app()->request->getParam('userid_actor');
		$idlokasi = Yii::app()->request->cookies['idlokasi']->value;

		$strMonth = Yii::app()->request->getParam('m');
		$strYear = Yii::app()->request->getParam('y');

		if(!empty($strMonth) && !empty($strYear))
		{
			$strMonth = str_pad($i, 2, '0', STR_PAD_LEFT);

			$v_reporth02 = $this->getReportH02Data($strMonth, $strYear, 'e_reporth02');

			echo $v_reporth02;

			//AuditLog
			$data = "Export Laporan Laba / Rugi, $strMonth, $strYear";

			FAudit::add('LAPORANKEUANGAN', 'Export', FHelper::GetUserName($userid_actor), $data);
		 }
		 else
		 {
			  //strMonth, strYear empty
		 }
	}

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