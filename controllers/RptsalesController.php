<?php
class RptsalesController extends FController
{
	private $success_message = '';

	private function getReportH01Data($strBranchId, $strDateFrom, $strDateTo, $tplName)
	{
		$report = array();

		$report['kode_cabang'] = FHelper::GetBranchInitial($strBranchId);
		$report['nama_cabang'] = FHelper::GetLocationName($strBranchId, true);
		$report['tgl_dari'] = $strDateFrom;
		$report['tgl_sampai'] = $strDateTo;

		$strDateFrom2DB = date("Y-m-d", strtotime($strDateFrom));
		$strDateTo2DB = date("Y-m-d", strtotime($strDateTo));

		//get data for view report (sql style)
		//menghitung jumlah nilai penjualan
		
		// subtotal1 = jumlah harga sebelum dipotong diskon
		// subtotal2 = jumlah harga setelah dipotong diskon
		// nilai penjualan = subtotal2 + tas_amount
		$strSQL = "
		  SELECT count(order_no) AS total_so, IFNULL(sum(num_of_item), 0) AS total_item, 
      IFNULL(sum(subtotal1), 0) AS gross_sales, 
      IFNULL(sum(subtotal2), 0) AS net_sales_bt, 
      IFNULL(sum(disc_amount), 0) AS disc, 
      IFNULL(sum(tax_amount), 0) AS tax 
      FROM pos_sales 
      WHERE 
      (status='LUNAS' OR status='BAYAR') AND 
      branch_id='$strBranchId' AND 
      open_date BETWEEN '$strDateFrom2DB 00:00:00' AND '$strDateTo2DB 23:59:59'
    ";
		//echo("SQL = $strSQL");
		//exit();

		$commandRpt = Yii::app()->db->createCommand($strSQL);
		$row = $commandRpt->queryRow();
		$report['total_so'] = $row['total_so'];
		$report['total_item'] = $row['total_item'];
		$report['gross_sales'] = $row['gross_sales'];
		$report['net_sales_bt'] = $row['net_sales_bt'];
		$report['disc'] = $row['disc'];
		$report['tax'] = $row['tax'];

		//menghitung jumlah nilai penjualan per kategori pembayaran
		$strSQL = "
		  SELECT IFNULL(sum(b.total_amount), 0) AS net_sales_at, 
      IFNULL(sum(b.cash_amount), 0) AS cash, 
      IFNULL(sum(b.change), 0) AS chng, 
      IFNULL(sum(b.noncash_amount), 0) AS noncash 
      FROM pos_sales a INNER JOIN pos_payment b ON a.sales_id = b.sales_id 
      WHERE 
      (a.status='LUNAS' OR a.status='BAYAR') AND 
      a.branch_id='$strBranchId' AND 
      a.open_date BETWEEN '$strDateFrom2DB 00:00:00' AND '$strDateTo2DB 23:59:59'
    ";
    
		//echo("SQL = $strSQL");
		//exit();

		$commandRpt = Yii::app()->db->createCommand($strSQL);
		$row = $commandRpt->queryRow();
		$report['cash'] = $row['cash'];
		$report['change'] = $row['chng'];
		$report['noncash'] = $row['noncash'];
		$report['net_sales_at'] = $row['net_sales_at'];

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

	private function getReportH02Data($strBranchId, $strDateFrom, $strDateTo, $tplName)
	{
		$report = array();

		$report['kode_cabang'] = FHelper::GetBranchInitial($strBranchId);
		$report['nama_cabang'] = FHelper::GetLocationName($strBranchId, true);
		$report['tgl_dari'] = $strDateFrom;
		$report['tgl_sampai'] = $strDateTo;

		$strDateFrom2DB = date("Y-m-d", strtotime($strDateFrom));
		$strDateTo2DB = date("Y-m-d", strtotime($strDateTo));

		//get data for view report (sql style)
		$strSQL = "SELECT b.item_cid AS category, count(b.id) AS num_item, ".
					"IFNULL(sum(b.total_price), 0) AS total_price ".
					"FROM pos_sales a INNER JOIN pos_sales_det b ON a.sales_id = b.sales_id ".
					"WHERE a.status='LUNAS' AND ".
					"a.branch_id='$strBranchId' AND ".
					"a.open_date BETWEEN '$strDateFrom2DB 00:00:00' AND '$strDateTo2DB 23:59:59'  ".
					"GROUP BY b.item_cid  ".
					"ORDER BY b.item_cid";
		//echo("SQL = $strSQL");
		//exit();

		$commandRpt = Yii::app()->db->createCommand($strSQL);
		$rows = $commandRpt->queryAll();

		foreach($rows as $row)
		{
			$report[$row['category']] = array($row['num_item'], $row['total_price']);
			$report['gross_sales'] += $row['total_price'];
		}

		$strSQL = "SELECT count(b.id) AS num_item_disc, ".
					"IFNULL(sum(b.disc_amount), 0) AS total_item_disc_amt ".
					"FROM pos_sales a INNER JOIN pos_sales_det b ON a.sales_id = b.sales_id ".
					"WHERE a.status='LUNAS' AND ".
					"a.branch_id='$strBranchId' AND ".
					"a.open_date BETWEEN '$strDateFrom2DB 00:00:00' AND '$strDateTo2DB 23:59:59' AND ".
					"b.disc_amount <> 0";
		//echo("SQL = $strSQL");
		//exit();

		$commandRpt = Yii::app()->db->createCommand($strSQL);
		$row = $commandRpt->queryRow();
		$report['num_item_disc'] = $row['num_item_disc'];
		$report['total_item_disc_amt'] = $row['total_item_disc_amt'];

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

	private function getReportH03Data($strBranchId, $strDateFrom, $strDateTo, $tplName)
	{
		$report = array();

		$report['kode_cabang'] = FHelper::GetBranchInitial($strBranchId);
		$report['nama_cabang'] = FHelper::GetLocationName($strBranchId, true);
		$report['tgl_dari'] = $strDateFrom;
		$report['tgl_sampai'] = $strDateTo;

		$strDateFrom2DB = date("Y-m-d", strtotime($strDateFrom));
		$strDateTo2DB = date("Y-m-d", strtotime($strDateTo));

		//get data for view report (sql style)
		$strSQL = "SELECT created_by AS nama_sales, ".
					"IFNULL(sum(subtotal1), 0) AS gross_sales, ".
					"IFNULL(sum(disc_amount), 0) AS disc, ".
					"IFNULL(sum(subtotal2), 0) AS net_sales_bt ".
					"FROM pos_sales ".
					"WHERE status='LUNAS' AND ".
					"branch_id='$strBranchId' AND ".
					"open_date BETWEEN '$strDateFrom2DB 00:00:00' AND '$strDateTo2DB 23:59:59'  ".
					"GROUP BY created_by  ".
					"ORDER BY created_by";
		//echo("SQL = $strSQL");
		//exit();

		$commandRpt = Yii::app()->db->createCommand($strSQL);
		$rows = $commandRpt->queryAll();

		//print_r($report);
		//exit();

		$v_report = $this->renderPartial(
			$tplName,
			array(
				 'report' => $report,
				 'rows' => $rows
			 ),
			 true
		);

		return $v_report;
	}

	private function getReportH04Data($intUserId, $idJenis, $strBranchId, $strDateFrom, $strDateTo, $tplName)
	{
		$report = array();
		
		$report['id_user'] = $intUserId;
		if ($idJenis == 1) {
			$report['jenis'] = 'Frame';
		} else {
			$report['jenis'] = 'Softlens';
		}
		$report['id_jenis'] = $idJenis;
		$report['id_cabang'] = $strBranchId;
		$report['kode_cabang'] = FHelper::GetBranchInitial($strBranchId);
		$report['nama_cabang'] = FHelper::GetLocationName($strBranchId, true);
		$report['tgl_dari'] = $strDateFrom;
		$report['tgl_sampai'] = $strDateTo;

		$strDateFrom2DB = date("Y-m-d", strtotime($strDateFrom));
		$strDateTo2DB = date("Y-m-d", strtotime($strDateTo));

		//get data for view report (sql style)
		$strSQL = "
					SELECT d.bank_id, IFNULL(e.nama, 'CASH') as nama_bank, SUM(d.total_price_per_category) as total_price_per_bank FROM 
					( 
					SELECT b.sales_id as sales_id, a.item_cid as category_id, SUM(total_price) as total_price_per_category, card_bank_name as bank_id FROM pos_sales_det a 
					INNER JOIN pos_sales b ON a.sales_id = b.sales_id 
					INNER JOIN pos_payment c ON b.sales_id = c.sales_id 
					WHERE b.status = 'LUNAS' AND 
					b.branch_id = '$strBranchId' AND 
					c.payment_date BETWEEN '$strDateFrom2DB 00:00:00' AND '$strDateTo2DB 23:59:59' 
					GROUP BY b.sales_id, a.item_cid, c.card_bank_name 
					) AS d 
					LEFT OUTER JOIN sys_bank e ON d.bank_id = e.id 
					WHERE ";
		if ($idJenis == 1) {
			$strSQL .= " (d.category_id < 3) ";
		} else {
			$strSQL .= " (d.category_id > 2) ";
		}					
					
		$strSQL .= "GROUP BY d.bank_id 
					ORDER BY d.bank_id";

		//echo $strSQL;
		//exit();

		$commandRpt = Yii::app()->db->createCommand($strSQL);
		$rows = $commandRpt->queryAll();

		//print_r($report);
		//exit();

		$v_report = $this->renderPartial(
			$tplName,
			array(
				 'rows' => $rows,
				 'report' => $report
			 ),
			 true
		);

		return $v_report;
	}

	//Index Laporan Rekap Penjualan
	public function actionIndexH01()
	{
		$menuid = 54;
		$parentmenuid = 42;

		$userid_actor = Yii::app()->request->getParam('userid_actor');
		$idlokasi = Yii::app()->request->cookies['idlokasi']->value;

		$allow_read = FHelper::AllowMenu($menuid, FHelper::GetGroupId($userid_actor), 'read');
		if ($allow_read) {
			$default_date = date("Y-m-d");
			$branch_list = FHelper::GetLocationListData(false);

			$TheMenu = FHelper::RenderMenu(0, $userid_actor, $parentmenuid);

			$this->userid_actor = $userid_actor;
			$this->parentmenuid = $parentmenuid;

			$this->bread_crumb_list = '
				<li>Laporan</li>
				<li>></li>
				<li>Penjualan</li>
				<li>></li>
				<li>Rekapitulasi Penjualan</li>';

			$this->layout = 'layout-baru';

			$TheContent = $this->renderPartial(
				'indexh01',
				array(
					'menuid' => $menuid,
					'userid_actor' => $userid_actor,
					'branch_list' => $branch_list,
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

	//View Laporan Rekap Penjualan
	public function actionViewReportH01()
	{
		$userid_actor = Yii::app()->request->getParam('userid_actor');
		$idlokasi = Yii::app()->request->cookies['idlokasi']->value;

		$strBranchId = Yii::app()->request->getParam('bid');
		$strDateFrom = Yii::app()->request->getParam('df');
		$strDateTo = Yii::app()->request->getParam('dt');

		if(!empty($strBranchId) && !empty($strDateFrom) && !empty($strDateTo))
		{
			$v_reporth01 = $this->getReportH01Data($strBranchId, $strDateFrom, $strDateTo, 'v_reporth01');

			echo CJSON::encode(
				array(
					'report' => $v_reporth01,
					'status' => 'ok'
				)
			);

			//AuditLog
			$data = "View Laporan Rekap Penjualan, $strBranchId, $strDateFrom, $strDateTo";

			FAudit::add('LAPORANPENJUALAN', 'View', FHelper::GetUserName($userid_actor), $data);
		 }
		 else
		 {
			  //strBranchId, strDateFrom, strDateTo empty
		 }
	}

	//Export Laporan Rekap Penjualan
	public function actionExportReportH01()
	{
		$userid_actor = Yii::app()->request->getParam('userid_actor');
		$idlokasi = Yii::app()->request->cookies['idlokasi']->value;

		$strBranchId = Yii::app()->request->getParam('bid');
		$strDateFrom = Yii::app()->request->getParam('df');
		$strDateTo = Yii::app()->request->getParam('dt');

		if(!empty($strBranchId) && !empty($strDateFrom) && !empty($strDateTo))
		{
			$v_reporth01 = $this->getReportH01Data($strBranchId, $strDateFrom, $strDateTo, 'e_reporth01');

			echo $v_reporth01;

			//AuditLog
			$data = "Export Laporan Rekap Penjualan, $strBranchId, $strDateFrom, $strDateTo";

			FAudit::add('LAPORANPENJUALAN', 'Export', FHelper::GetUserName($userid_actor), $data);
		 }
		 else
		 {
			  //strBranchId, strDateFrom, strDateTo empty
		 }
	}

	//Laporan Penjualan Per Kategori Produk
	public function actionIndexH02()
	{
		$menuid = 54;
		$parentmenuid = 42;

		$userid_actor = Yii::app()->request->getParam('userid_actor');
		$idlokasi = Yii::app()->request->cookies['idlokasi']->value;

		$allow_read = FHelper::AllowMenu($menuid, FHelper::GetGroupId($userid_actor), 'read');
		if ($allow_read) {
			$default_date = date("Y-m-d");
			$branch_list = FHelper::GetLocationListData(false);

			$TheMenu = FHelper::RenderMenu(0, $userid_actor, $parentmenuid);

			$this->userid_actor = $userid_actor;
			$this->parentmenuid = $parentmenuid;

			$this->bread_crumb_list = '
				<li>Laporan</li>
				<li>></li>
				<li>Penjualan</li>
				<li>></li>
				<li>Penjualan Per Kategori Produk</li>';

			$this->layout = 'layout-baru';

			$TheContent = $this->renderPartial(
				'indexh02',
				array(
					'menuid' => $menuid,
					'userid_actor' => $userid_actor,
					'branch_list' => $branch_list,
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

	//View Laporan Rekap Penjualan
	public function actionViewReportH02()
	{
		$userid_actor = Yii::app()->request->getParam('userid_actor');
		$idlokasi = Yii::app()->request->cookies['idlokasi']->value;

		$strBranchId = Yii::app()->request->getParam('bid');
		$strDateFrom = Yii::app()->request->getParam('df');
		$strDateTo = Yii::app()->request->getParam('dt');

		if(!empty($strBranchId) && !empty($strDateFrom) && !empty($strDateTo))
		{
			$v_reporth02 = $this->getReportH02Data($strBranchId, $strDateFrom, $strDateTo, 'v_reporth02');

			echo CJSON::encode(
				array(
					'report' => $v_reporth02,
					'status' => 'ok'
				)
			);

			//AuditLog
			$data = "View Laporan Penjualan Per Kategori Produk, $strBranchId, $strDateFrom, $strDateTo";

			FAudit::add('LAPORANPENJUALAN', 'View', FHelper::GetUserName($userid_actor), $data);
		 }
		 else
		 {
			  //strBranchId, strDateFrom, strDateTo empty
		 }
	}

	//Export Laporan Rekap Penjualan
	public function actionExportReportH02()
	{
		$userid_actor = Yii::app()->request->getParam('userid_actor');
		$idlokasi = Yii::app()->request->cookies['idlokasi']->value;

		$strBranchId = Yii::app()->request->getParam('bid');
		$strDateFrom = Yii::app()->request->getParam('df');
		$strDateTo = Yii::app()->request->getParam('dt');

		if(!empty($strBranchId) && !empty($strDateFrom) && !empty($strDateTo))
		{
			$v_reporth02 = $this->getReportH02Data($strBranchId, $strDateFrom, $strDateTo, 'e_reporth02');

			echo $v_reporth02;

			//AuditLog
			$data = "Export Laporan Penjualan Per Kategori Produk, $strBranchId, $strDateFrom, $strDateTo";

			FAudit::add('LAPORANPENJUALAN', 'Export', FHelper::GetUserName($userid_actor), $data);
		 }
		 else
		 {
			  //strBranchId, strDateFrom, strDateTo empty
		 }
	}

	//Laporan Penjualan Per Sales
	public function actionIndexH03()
	{
		$menuid = 54;
		$parentmenuid = 42;

		$userid_actor = Yii::app()->request->getParam('userid_actor');
		$idlokasi = Yii::app()->request->cookies['idlokasi']->value;

		$allow_read = FHelper::AllowMenu($menuid, FHelper::GetGroupId($userid_actor), 'read');
		if ($allow_read) {
			$default_date = date("Y-m-d");
			$branch_list = FHelper::GetLocationListData(false);
			if (empty($idlokasi)) $idlokasi = $branch_list[0];
			$sales_list = FHelper::GetSalesListDataByBranch($idlokasi);

			$TheMenu = FHelper::RenderMenu(0, $userid_actor, $parentmenuid);

			$this->userid_actor = $userid_actor;
			$this->parentmenuid = $parentmenuid;

			$this->bread_crumb_list = '
				<li>Laporan</li>
				<li>></li>
				<li>Penjualan</li>
				<li>></li>
				<li>Penjualan Per Karyawan</li>';

			$this->layout = 'layout-baru';

			$TheContent = $this->renderPartial(
				'indexh03',
				array(
					'menuid' => $menuid,
					'userid_actor' => $userid_actor,
					'branch_list' => $branch_list,
					'sales_list' => $sales_list
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

	//View Laporan Rekap Penjualan
	public function actionViewReportH03()
	{
		$userid_actor = Yii::app()->request->getParam('userid_actor');
		$idlokasi = Yii::app()->request->cookies['idlokasi']->value;

		$strBranchId = Yii::app()->request->getParam('bid');
		$strDateFrom = Yii::app()->request->getParam('df');
		$strDateTo = Yii::app()->request->getParam('dt');

		if(!empty($strBranchId) && !empty($strDateFrom) && !empty($strDateTo))
		{
			$v_reporth03 = $this->getReportH03Data($strBranchId, $strDateFrom, $strDateTo, 'v_reporth03');

			echo CJSON::encode(
				 array(
					  'report' => $v_reporth03,
					  'status' => 'ok'
				 )
			);

			//AuditLog
			$data = "View Laporan Penjualan Per Karyawan, $strBranchId, $strDateFrom, $strDateTo";

			FAudit::add('LAPORANPENJUALAN', 'View', FHelper::GetUserName($userid_actor), $data);
		 }
		 else
		 {
			  //strBranchId, strDateFrom, strDateTo empty
		 }
	}

	//Export Laporan Rekap Penjualan
	public function actionExportReportH03()
	{
		$userid_actor = Yii::app()->request->getParam('userid_actor');
		$idlokasi = Yii::app()->request->cookies['idlokasi']->value;

		$strBranchId = Yii::app()->request->getParam('bid');
		$strDateFrom = Yii::app()->request->getParam('df');
		$strDateTo = Yii::app()->request->getParam('dt');

		if(!empty($strBranchId) && !empty($strDateFrom) && !empty($strDateTo))
		{
			$v_reporth03 = $this->getReportH03Data($strBranchId, $strDateFrom, $strDateTo, 'e_reporth03');

			echo $v_reporth03;

			//AuditLog
			$data = "Export Laporan Penjualan Per Karyawan, $strBranchId, $strDateFrom, $strDateTo";

			FAudit::add('LAPORANPENJUALAN', 'Export', FHelper::GetUserName($userid_actor), $data);
		 }
		 else
		 {
			  //strBranchId, strDateFrom, strDateTo empty
		 }
	}

	public function actionGetSalesList()
	{
		$idlokasi = Yii::app()->request->getParam('bid');
		//exit($idlokasi);

		if(!empty($idlokasi))
		{
			//pastikan sales ada.
			$Criteria = new CDbCriteria();
			$Criteria->condition = 'id_location = :idlokasi AND idgroup = 4 AND is_del = 0';
			$Criteria->params = array(':idlokasi' => $idlokasi);
			$count = sys_user::model()->count($Criteria);

			if($count > 0)
			{
				//ada sales... tampilkan daftarnya dalam bentuk dropdown
				$Criteria = new CDbCriteria();
				$Criteria->condition = 'id_location = :idlokasi AND idgroup = 4 AND is_del = 0';
				$Criteria->params = array(':idlokasi' => $idlokasi);
				$Criteria->order = 'nama ASC';
				$saless = sys_user::model()->findAll($Criteria);

				$sales_list[0] = '-- Pilih Nama Staff Sales --';
				foreach($saless as $sales)
				{
					$value = $sales['id'];
					$name = $sales['nama'];

					$sales_list[$value] = $name;
				}
			}
			else
			{
				$sales_list[0] = '-- Staff Sales Tidak Ditemukan --';
			}
		}
		else
		{
			$sales_list[0] = '--';
		}

		$dropdown = CHtml::dropDownList(
			'cboSalesId',
			'0',
			$sales_list,
			array(
				 'id' => 'cboSalesId',
				 'style' => 'width: 100%'
			)
		);

		echo CJSON::encode(
			array(
				 'dropdown' => $dropdown
			)
		);
	}

	//Index Laporan Penjualan Per Bank
	public function actionIndexH04()
	{
		$menuid = 65;
		$parentmenuid = 42;

		$userid_actor = Yii::app()->request->getParam('userid_actor');
		$idlokasi = Yii::app()->request->cookies['idlokasi']->value;
		$groupid = FHelper::GetGroupId($userid_actor);

		$allow_read = FHelper::AllowMenu($menuid, FHelper::GetGroupId($userid_actor), 'read');
		if ($allow_read) {
			$jenis_list = array('1' => 'Frame', 'Softlens');

			if ($groupid == 6) {
				//If Supervisor

				$branch_list = FHelper::GetLocationListDataSupervisor($idlokasi);
			} elseif ($groupid == 11) {
				//If Sales
				$Criteria = new CDbCriteria();
				$Criteria->condition = 'branch_id = :idlokasi';
				$Criteria->params = array(':idlokasi' => $idlokasi);
				$Criteria->order = 'name ASC';
				$branches = Branch::model()->findAll($Criteria);

				$branch_list = CHtml::listData($branches, 'branch_id', 'name');
			} else {
				$branch_list = FHelper::GetLocationListData(false);
			}

			$default_date = date("Y-m-d");

			$TheMenu = FHelper::RenderMenu(0, $userid_actor, $parentmenuid);

			$this->userid_actor = $userid_actor;
			$this->parentmenuid = $parentmenuid;

			$this->bread_crumb_list = '
				<li>Laporan</li>
				<li>></li>
				<li>Penjualan</li>
				<li>></li>
				<li>Penjualan Per Bank</li>';

			$this->layout = 'layout-baru';

			$TheContent = $this->renderPartial(
				'indexh04',
				array(
					'menuid' => $menuid,
					'userid_actor' => $userid_actor,
					'branch_list' => $branch_list,
					'jenis_list' => $jenis_list,
					'groupid' => $groupid,
					'idlokasi' => $idlokasi
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

	//View Laporan Penjualan Frame & Lensa
	public function actionViewReportH04()
	{
		$userid_actor = Yii::app()->request->getParam('userid_actor');
		$idlokasi = Yii::app()->request->cookies['idlokasi']->value;

		$strJenis = Yii::app()->request->getParam('cid');
		$strBranchId = Yii::app()->request->getParam('bid');
		$strDateFrom = Yii::app()->request->getParam('df');
		$strDateTo = Yii::app()->request->getParam('dt');

		if(!empty($strBranchId) && !empty($strDateFrom) && !empty($strDateTo))
		{
			$v_reporth04 = $this->getReportH04Data($userid_actor, $strJenis, $strBranchId, $strDateFrom, $strDateTo, 'v_reporth04');

			echo CJSON::encode(
				array(
					'report' => $v_reporth04,
					'status' => 'ok'
				)
			);

			//AuditLog
			$data = "View Laporan Penjualan Frame & Lensa, $strBranchId, $strDateFrom, $strDateTo";

			FAudit::add('LAPORANPENJUALAN', 'View', FHelper::GetUserName($userid_actor), $data);
		 }
		 else
		 {
			  //strBranchId, strDateFrom, strDateTo empty
		 }
	}

	//Export Laporan Rekap Penjualan
	public function actionExportReportH04()
	{
		$userid_actor = Yii::app()->request->getParam('userid_actor');
		$idlokasi = Yii::app()->request->cookies['idlokasi']->value;

		$strJenis = Yii::app()->request->getParam('cid');
		$strBranchId = Yii::app()->request->getParam('bid');
		$strDateFrom = Yii::app()->request->getParam('df');
		$strDateTo = Yii::app()->request->getParam('dt');

		if(!empty($strBranchId) && !empty($strDateFrom) && !empty($strDateTo))
		{
			$e_reporth04 = $this->getReportH04Data($userid_actor, $strJenis, $strBranchId, $strDateFrom, $strDateTo, 'e_reporth04');

			echo $e_reporth04;

			//AuditLog
			$data = "Export Laporan Penjualan Frame & Lensa, $strBranchId, $strDateFrom, $strDateTo";

			FAudit::add('LAPORANPENJUALAN', 'Export', FHelper::GetUserName($userid_actor), $data);
		}
		else
		{
		  //strBranchId, strDateFrom, strDateTo empty
			  
		  echo "Tidak ada data untuk dilaporkan";
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