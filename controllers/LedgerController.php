<?php
class LedgerController extends FController
{
	private $error_message = '';
	private $mon_start = '2014-06';
	private $day_protect = 7;

	private function getMonthList()
	{
		$month_list[0] = '-- Pilih Bulan --';
		for($i=1;$i<13;$i++) $month_list[$i] = str_pad($i, 2, '0', STR_PAD_LEFT);

		return $month_list;
	}

	private function getYearList()
	{
		$year_list[0] = '-- Pilih Tahun --';
		for($i=2013;$i<date("Y") + 4;$i++) $year_list[$i] = $i;

		return $year_list;
	}

	private function sum($arrCOA, $intMode, $month, $year) {
		$array_size = sizeof($arrCOA);
		$sum = 0;
		for ($i=0;$i<$array_size;$i++) {
			if ($intMode) {
				//1 = Credit - Debit (Other)
				/*
				$strSQL = "SELECT SUM(credit) - SUM(debit) AS sum FROM journal
				WHERE coa_code LIKE '".$arrCOA[$i]."%' AND MONTH(journal_date) = $month AND YEAR(journal_date) = $year";
				*/
				$strSQL = "SELECT SUM(credit - debit) AS sum FROM fin_ledger_detail a
				INNER JOIN fin_ledger b ON a.ledger_id = b.id
				INNER JOIN mtr_coa c ON a.coa_id = c.id
				WHERE c.code LIKE '".$arrCOA[$i]."%' AND MONTH(b.ledger_date) = ".(int) $month." AND YEAR(b.ledger_date) = $year";
			} else {
				//0 = Debit - Credit (Pendapatan)
				/*
				$strSQL = "SELECT SUM(debit) - SUM(credit) AS sum FROM journal
				WHERE coa_code LIKE '".$arrCOA[$i]."%' AND MONTH(journal_date) = $month AND YEAR(journal_date) = $year";
				*/
				$strSQL = "SELECT SUM(debit - credit) AS sum FROM fin_ledger_detail a
				INNER JOIN fin_ledger b ON a.ledger_id = b.id
				INNER JOIN mtr_coa c ON a.coa_id = c.id
				WHERE c.code LIKE '".$arrCOA[$i]."%' AND MONTH(b.ledger_date) = ".(int) $month." AND YEAR(b.ledger_date) = $year";
			}
			//echo("strSQLsum = $strSQL<p>");
			$commSQL = Yii::app()->db->createCommand($strSQL);
			$rowCount = $commSQL->execute();
			
			if ($rowCount > 0) {
				$row = $commSQL->queryAll();
				$sum += $row['sum'];
			}
		}

		return $sum;
	}

	private function calculate_laba_rugi(&$report, $strMonth, $strYear) {
		$pu1 = $this->sum('410', 1, $strMonth, $strYear);
		$pu2 = $this->sum('411', 1, $strMonth, $strYear);
		$pu3 = $this->sum('412', 1, $strMonth, $strYear);
		$report['pendapatan_usaha'] = $pu1 + $pu2 + $pu3;

		$report['beban_bp'] = $this->sum('5', 0, $strMonth, $strYear);

		$report['laba_kotor'] = $report['beban_bp'] - $report['pendapatan_usaha'];

		$bo1 = $this->sum('6100', 0, $strMonth, $strYear);
		$bo2 = $this->sum('6101', 0, $strMonth, $strYear);
		$bo3 = $this->sum('6102', 0, $strMonth, $strYear);
		$bo4 = $this->sum('6103', 0, $strMonth, $strYear);
		$bo5 = $this->sum('6104', 0, $strMonth, $strYear);
		$bo6 = $this->sum('6105', 0, $strMonth, $strYear);
		$bo7 = $this->sum('6106', 0, $strMonth, $strYear);
		$bo8 = $this->sum('6107', 0, $strMonth, $strYear);
		$bo9 = $this->sum('6108', 0, $strMonth, $strYear);
		$report['bo'] = $bo1 + $bo2 + $bo3 + $bo4 + $bo5 + $bo6 + $bo7 + $bo8 + $bo9;

		$report['bua'] = $this->sum('62', 0, $strMonth, $strYear);

		$report['jum_bu'] = $report['bo'] + $report['bua'];

		$report['labarugi_usaha'] = $report['laba_kotor'] - $report['jum_bu'];

		$report['pendapatan_ll'] = $this->sum('71', 1, $strMonth, $strYear);
		$report['beban_ll'] = $this->sum('72', 0, $strMonth, $strYear);

		$report['labarugi_before_tax'] = $report['labarugi_usaha'] + $report['pendapatan_ll'] - $report['beban_ll'];

		$report['taksiran_pp'] = $this->sum('750', 0, $strMonth, $strYear);
		$report['labarugi_bersih'] = $report['labarugi_before_tax'] - $report['taksiran_pp'];

		return $report['labarugi_bersih'];
	}

	private function send_to_journal($coa_code, $tgl, $debit, $credit, $ref_id, $ref_type, $ref_desc) {
		global $db;

		$strSQL = "INSERT INTO journal (coa_id, journal_date, debit, credit, coa_code, ref_id, ref_type, ref_desc)
		VALUES (
		(SELECT id FROM master_coa WHERE code = '$coa_code'), '$tgl',
		$debit, $credit, '$coa_code', $ref_id, $ref_type, '$ref_desc')";
		//echo("strSQLsend_to_journal = $strSQL<p>"); exit();
		$commSQL = Yii::app()->db->createCommand($strSQL);
		$row = $commSQL->execute();
	}

	private function remove_from_journal($coa_code, $tgl, $debit, $credit, $ref_id, $ref_type, $ref_desc) {
		global $db;

		$strSQL = "DELETE FROM journal WHERE coa_id = (SELECT id FROM master_coa WHERE code = '$coa_code')
		AND journal_date = '$tgl'
		AND debit = $debit
		AND credit = $credit
		AND coa_code = '$coa_code'
		AND ref_id = $ref_id
		AND ref_type = $ref_type";
		//echo("strSQLremove_from_journal = $strSQL<p>");
		$commSQL = Yii::app()->db->createCommand($strSQL);
		$row = $commSQL->execute();
	}

	public function actionIndex()
	{
		$menuid = 25;
		$parentmenuid = 8;

		$userid_actor = Yii::app()->request->getParam('userid_actor');
		$this->idlokasi = Yii::app()->request->cookies['idlokasi']->value;

		$allow_read = FHelper::AllowMenu($menuid, FHelper::GetGroupId($userid_actor), 'read');
		if ($allow_read) {
			$month_list = $this->getMonthList();
			$year_list = $this->getYearList();

			$TheMenu = FHelper::RenderMenu(0, $userid_actor, $parentmenuid);

			$this->userid_actor = $userid_actor;
	    	$this->parentmenuid = $parentmenuid;

			$this->bread_crumb_list = '
				<li>Keuangan</li>
				<li>></li>
				<li>Ledger</li>';

	   		$this->layout = 'layout-baru';

			$TheContent = $this->renderPartial(
				'edit',
				array(
					'menuid' => $menuid,
					'userid_actor' => $userid_actor,
					'month_list' => $month_list,
					'year_list' => $year_list
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

	public function actionListLedger()
	{
		$menuid = 25;
		$userid_actor = Yii::app()->request->getParam('userid_actor');

		$strYear = Yii::app()->request->getParam('y');
		$intMonth = Yii::app()->request->getParam('m');
		$strCOAFrom = '';//Yii::app()->request->getParam('coaf');
		$strCOATo = '';//Yii::app()->request->getParam('coat');
		//echo "$strYear - $intMonth - $strCOAFrom - $strCOATo";
		//exit();

		if(!empty($strYear) && !empty($intMonth))
		{
			$strMonth = str_pad($intMonth, 2, '0', STR_PAD_LEFT);
			$strSQL = "SELECT ledger_id FROM fin_ledger
					  WHERE ledger_date = '$strYear-$strMonth-01'";
			//echo("strSQL = $strSQL<p>"); exit();

			$commSQL = Yii::app()->db->createCommand($strSQL);
			$rowCount = $commSQL->execute();
			
			$blnIsLedgerFound = false;
			$ledger_status = "OPEN";
			if ($rowCount > 0) {
				$blnIsLedgerFound = true;
				$ledger_status = "CLOSE";
			}

			if ($blnIsLedgerFound) {
				//exit ("Ledger Found");

				$ledger = $commSQL->queryRow();
				//print_r($ledger); exit();

				$strSQL = "SELECT a.coa_id, CONCAT(b.code,' - ',b.title) AS coa, a.debit, a.credit 
				FROM fin_ledger_detail a 
				INNER JOIN mtr_coa b ON a.coa_id = b.coa_id 
				WHERE ledger_id = ".$ledger['ledger_id'];

				if (!(empty($strCOAFrom) && empty($strCOATo))) $strSQL .= " AND b.code BETWEEN $strCOAFrom AND $strCOATo";

				$strSQL .= " ORDER BY b.code";
			} else {
				//exit ("Ledger Not Found");

				$strSQL = "SELECT coa_id, CONCAT(a.code,' - ',a.title) AS coa, 0 AS debit, 0 AS credit FROM mtr_coa a";

				if (!(empty($strCOAFrom) && empty($strCOATo))) $strSQL .= " WHERE a.code BETWEEN '$strCOAFrom' AND '$strCOATo'";

				$strSQL .= " ORDER BY a.code";
			}
			//echo("strSQL_view1-1 = $strSQL<p>"); exit();

			$commSQL1 = Yii::app()->db->createCommand($strSQL);
			$rowCount1 = $commSQL1->execute();

			if ($rowCount1 > 0) {
				$ledgers = $commSQL1->queryAll();

				//echo("strSQL_view2-1 = $strSQL<p>"); exit();

				$no = 1;
				$dblJumDebit = 0;
				$dblJumCredit = 0;

				foreach ($ledgers as $key=>$row) {
					
					//echo("strSQL_view2-2 = $strSQL<p>"); exit();

					$dblSADebit = 0;
					$dblSACredit = 0;

					$idcoa = $row['coa_id'];

					if (!$blnLedgerFound) {

						//exit("Ledger Not Found");

						$debit = 0;
						$credit = 0;

						//debit
						$strSQL = "SELECT SUM(b.debit) AS debit FROM fin_journal b 
						WHERE MONTH(b.journal_date) = $intMonth AND YEAR(b.journal_date) = $strYear AND b.coa_id = $idcoa 
						GROUP BY b.coa_id";

						//echo("strSQL_view3 = $strSQL<p>"); exit();
						$commSQL2 = Yii::app()->db->createCommand($strSQL);
						$rowCount2 = $commSQL2->execute();

						if ($rowCount2 > 0) {
							$row2 = $commSQL2->queryRow();
							$debit = $row2['debit'];
							$ledgers[$key]['debit'] = $debit;
						}

						//credit
						$strSQL = "SELECT SUM(b.credit) AS credit FROM fin_journal b 
						WHERE MONTH(b.journal_date) = $intMonth AND YEAR(b.journal_date) = $strYear AND b.coa_id = $idcoa 
						GROUP BY b.coa_id";

						//echo("strSQL_view3 = $strSQL<p>"); exit();
						$commSQL2 = Yii::app()->db->createCommand($strSQL);
						$rowCount2 = $commSQL2->execute();

						if ($rowCount2 > 0) {
							$row2 = $commSQL2->queryRow();
							$credit = $row2['credit'];
							$ledgers[$key]['credit'] = $credit;
						}

						//Check if last month is app start month
						$tsLastMonth = mktime(0, 0, 0, $intMonth-1, 1, $strYear);
						$mon_lastmonth = date("Y-m", $tsLastMonth);

						if ($mon_lastmonth == $this->mon_start) {
							//Get data from coa starting balance
							$strSQL = "SELECT starting_debit AS sa_debit, starting_credit AS sa_credit FROM mtr_coa
							WHERE coa_id = $idcoa";
						} else {
							//Get data from last month ledger
							//Every january saldo laba/rugi is reset to 0, so
							//check if month = 1 then get saldo from last month only for COA prefix 1, 2, 3 (neraca)

							if ($intMonth == 1) {
								$strSQL = "SELECT b.debit AS sa_debit, b.credit AS sa_credit FROM fin_ledger a 
								INNER JOIN fin_ledger_detail b ON a.ledger_id = b.ledger_id 
								INNER JOIN mtr_coa c ON b.coa_id = c.coa_id 
								WHERE b.coa_id = $idcoa AND MONTH(a.ledger_date) = ".date("n", $tsLastMonth)." AND YEAR(a.ledger_date) = ".date("Y", $tsLastMonth). " AND 
								(SUBSTR(c.code, 1, 1) = 1 OR SUBSTR(c.code, 1, 1) = 2 OR SUBSTR(c.code, 1, 1) = 3)";
							} else {
								$strSQL = "SELECT b.debit AS sa_debit, b.credit AS sa_credit FROM fin_ledger a 
								INNER JOIN fin_ledger_detail b ON a.ledger_id = b.ledger_id 
								WHERE b.coa_id = $idcoa AND MONTH(a.ledger_date) = ".date("n", $tsLastMonth)." AND YEAR(a.ledger_date) = ".date("Y", $tsLastMonth);
							}
						}
						
						//echo("strSQL_view4 = $strSQL - $mon_lastmonth - $this->mon_start<p>"); exit();
						$commSQL2 = Yii::app()->db->createCommand($strSQL);
						$rowCount2 = $commSQL2->execute();

						if ($rowCount2 > 0) {
							$row2 = $commSQL2->queryRow();
							$dblSADebit = $row2['sa_debit'];
							$dblSACredit = $row2['sa_credit'];
						}
						
						$dblDebit = $debit + $dblSADebit;
						$dblCredit = $credit + $dblSACredit;
						/*
						if ($idcoa == 1) {
							echo "idcoa=$idcoa, JumDebit=$dblDebit, JumCredit=$dblCredit";
							exit();
						}
						*/
					} else {
						//exit("Ledger Found");

						$dblDebit = $row['debit'] + $dblSADebit;
						$dblCredit = $row['credit'] + $dblSACredit;
					}

					$dblJumDebit += $dblDebit;
					$dblJumCredit += $dblCredit;
				}
			}

			//echo "rowCount=$rowCount, JumDebit=$dblJumDebit, JumCredit=$dblJumDebit ";
			//exit();

			$ledger = $this->renderPartial(
				'v_ledger_list',
				array(
					'menuid' => $menuid,
					'userid_actor' => $userid_actor,
					'ledger_status' => $ledger_status,
					'ledgers' => $ledgers,
					'year' => $strYear,
					'month' => $intMonth,
					'jum_debit' => $dblJumDebit,
					'jum_credit' => $dblJumDebit
				 ),
				 true
			);

			echo CJSON::encode(
				 array(
					  'ledger' => $ledger,
					  'status' => 'ok'
				 )
			);

			//AuditLog
			$data = "List Ledger, $strYear, $strMonth, $strCOAFrom, $strCOATo";

			FAudit::add('KEUANGANLEDGER', 'List', FHelper::GetUserName($userid_actor), $data);
		 }
		 else
		 {
			  //strBranchId, strDateFrom, strDateTo empty
		 }
	}

	public function actionViewLedger()
	{
		$menuid = 25;
		$userid_actor = Yii::app()->request->getParam('userid_actor');

	  	$allow_read = FHelper::AllowMenu($menuid, FHelper::GetGroupId($userid_actor), 'read');
	  	if ($allow_read) {
			$strYear = Yii::app()->request->getParam('y');
			$intMonth = Yii::app()->request->getParam('m');
			$idcoa = Yii::app()->request->getParam('idcoa');
			//echo "$strYear - $intMonth - $idcoa";
			//exit();

			if(!empty($strYear) && !empty($intMonth) && !empty($idcoa))
			{
				$strMonth = str_pad($intMonth, 2, '0', STR_PAD_LEFT);

				$dblJumDebit = 0;
				$dblJumCredit = 0;

				//Check if last month is app start month
				$tsLastMonth = mktime(0, 0, 0, $intMonth-1, 1, $strYear);
				$mon_lastmonth = date("Y-m", $tsLastMonth);

				if ($mon_lastmonth == $this->mon_start) {
					//Get data from coa starting balance
					$strSQL = "SELECT '' as no_voucher,'' as tgl, CONCAT(code,' - ',title) AS coa, starting_debit AS sa_debit, starting_credit  AS sa_credit, 'SALDO BULAN LALU' as ref_desc FROM mtr_coa 
					WHERE coa_id = $idcoa";
				} else {
					//Get data from last month ledger
					//Every january saldo laba/rugi is reset to 0, so
					//check if month = 1 then get saldo from last month only for COA prefix 1, 2, 3 (neraca)

					if ($intMonth == 1) {
						$strSQL = "SELECT '' as no_voucher,'' as tgl, CONCAT(c.code,' - ',c.title) AS coa, b.debit AS sa_debit, b.credit AS sa_credit, 'SALDO BULAN LALU' as ref_desc FROM fin_ledger a 
						INNER JOIN fin_ledger_detail b ON a.ledger_id = b.ledger_id 
						INNER JOIN mtr_coa c ON b.coa_id = c.coa_id 
						WHERE b.coa_id = $idcoa AND MONTH(a.ledger_date) = ".date("n", $tsLastMonth)." AND YEAR(a.ledger_date) = ".date("Y", $tsLastMonth). " AND 
						(SUBSTR(c.code, 1, 1) = 1 OR SUBSTR(c.code, 1, 1) = 2 OR SUBSTR(c.code, 1, 1) = 3)";
					} else {
						$strSQL = "SELECT '' as no_voucher,'' as tgl, CONCAT(c.code,' - ',c.title) AS coa, b.debit AS sa_debit, b.credit AS sa_credit, 'SALDO BULAN LALU' as ref_desc FROM fin_ledger a 
						INNER JOIN fin_ledger_detail b ON a.ledger_id = b.ledger_id 
						INNER JOIN mtr_coa c ON b.coa_id = c.coa_id 
						WHERE b.coa_id = $idcoa AND MONTH(a.ledger_date) = ".date("n", $tsLastMonth)." AND YEAR(a.ledger_date) = ".date("Y", $tsLastMonth);
					}
				}
				//echo("strSQL_view = $strSQL - $mon_lastmonth - $this->mon_start<p>"); exit();

				$commSQL = Yii::app()->db->createCommand($strSQL);
				$rowCount = $commSQL->execute();

				$no = 1;
				
				$ledgers1 = array();
				if ($rowCount > 0) {
					$ledgers1 = $commSQL->queryRow();

					$dblJumDebit += $ledgers1['sa_debit'];
					$dblJumCredit += $ledgers1['sa_credit'];
					/*
					$ledgers1['no_voucher'] = $row['no_voucher'];
					$ledgers1['tgl'] = $row['tgl'];
					$ledgers1['coa'] = $row['coa'];
					$ledgers1['debit'] = $row['sa_debit'];
					$ledgers1['credit'] = $row['sa_credit'];
					$ledgers1['ref_desc'] = 'SALDO BULAN LALU';
					*/

					$no++;
				}
				//echo print_r ($ledgers1);
				//echo sizeof($ledgers1);

				$ledgers2 = array();
				if (!$blnLedgerFound) {

					//exit("Ledger Not Found");

					$strSQL = "SELECT c.no_voucher, c.tgl, a.coa_id, CONCAT(b.code,' - ',b.title) AS coa, a.debit, a.credit, a.ref_desc, a.journal_date 
					FROM fin_journal a 
					INNER JOIN mtr_coa b ON a.coa_id = b.coa_id 
					LEFT OUTER JOIN fin_voucher c ON (a.ref_id = c.voucher_id AND a.ref_type = 90) 
					WHERE MONTH(journal_date) = $intMonth AND YEAR(journal_date) = $strYear 
					AND a.coa_id = $idcoa 
					ORDER BY a.journal_date";
					//echo("strSQL_view2 = $strSQL<p>"); exit();

					$commSQL = Yii::app()->db->createCommand($strSQL);
					$rowCount = $commSQL->execute();

					if ($rowCount > 0) {
						$ledgers2 = $commSQL->queryAll();

						foreach ($ledgers2 as $row) {

							$dblJumDebit += $row['debit'];
							$dblJumCredit += $row['credit'];

							$no++;
						}
					}
				} else {
					//exit("Ledger Found");

					$strSQL = "SELECT c.no_voucher, c.tgl, a.coa_id, CONCAT(b.code,' - ',b.title) AS coa, a.debit, a.credit, a.ref_desc, a.journal_date 
					FROM fin_journal a 
					INNER JOIN mtr_coa b ON a.coa_id = b.coa_id 
					LEFT OUTER JOIN fin_voucher c ON (a.ref_id = c.voucher_id AND a.ref_type = 90) 
					WHERE MONTH(journal_date) = $intMonth AND YEAR(journal_date) = $strYear 
					AND a.coa_id = $idcoa 
					ORDER BY a.journal_date";
					//echo("strSQL_view2 = $strSQL<p>"); exit();
					$commSQL = Yii::app()->db->createCommand($strSQL);
					$rowCount = $commSQL->execute();

					if ($rowCount > 0) {
						$ledgers2 = $commSQL->queryAll();
						
						foreach ($ledgers2 as $row) {

							$dblJumDebit += $row['debit'];
							$dblJumCredit += $row['credit'];

							$no++;
						}
					}
				}
				//echo print_r ($ledgers2);

				$ledgers = array_merge($ledgers1, $ledgers2);
				//echo "JumDebit = $dblJumDebit, JumCredit = $dblJumCredit";
				//$ledgers = $ledgers1 + $ledgers2;
				//echo print_r ($ledgers);
				//exit();
			}

			$bread_crumb_list =
				'<li>Keuangan</li>'.
				'<li>></li>'.
				'<li><a href="#" onclick="ShowList('.$userid_actor.');">Ledger</a></li>'.
				'<li>></li>'.
				'<li>View Ledger</li>';

			$TheContent = $this->renderPartial(
				'v_ledger_detail',
				array(
					'menuid' => $menuid,
					'userid_actor' => $userid_actor,
					'ledgers' => $ledgers,
					'year' => $strYear,
					'month' => $intMonth,
					'jum_debit' => $dblJumDebit,
					'jum_credit' => $dblJumCredit
				),
				true
			);

			echo $TheContent;

			//AuditLog
			$data = "View Ledger, $strYear, $strMonth, $idcoa";

			FAudit::add('KEUANGANLEDGER', 'View', FHelper::GetUserName($userid_actor), $data);
		}
		else
		{
			$bread_crumb_list = '
				<li>Not Authorize</li>';

			$this->layout = 'layout-baru';

			$TheContent = $this->renderPartial(
				'not_auth',
				array(
					'userid_actor' => $userid_actor,
					'ledgers' => $ledgers
				),
				true
			);

			echo CJSON::encode(
				array(
					'html' => $TheContent,
					'bread_crumb_list' => $bread_crumb_list
				)
			);
		}
	}

	public function actionCloseLedger()
	{
		$menuid = 25;
		$userid_actor = Yii::app()->request->getParam('userid_actor');

	  	$allow_edit = FHelper::AllowMenu($menuid, FHelper::GetGroupId($userid_actor), 'edit');
	  	if ($allow_edit) {
			$strYear = Yii::app()->request->getParam('y');
			$intMonth = Yii::app()->request->getParam('m');

			$strMonth = str_pad($intMonth, 2, '0', STR_PAD_LEFT);

			//Check bulan ini
			$tsActiveMonth = mktime(0, 0, 0, $intMonth, 1, $strYear);
			$tsCurrMonth = strtotime(date("Y-m-01"));
			//echo "$tsActiveMonth - $tsCurrMonth"; exit();

			if ($tsActiveMonth >= $tsCurrMonth) {

				$this->error_message =
				'<div class="notification note-error">'.
				'<a href="#" class="close" title="Close notification">close</a>'.
				'<p><strong>Error notification:</strong> Ledger bulan berjalan tidak bisa diclose</p>'.
				'</div>';
				/*
				echo("<script language=\"javascript\">alert('Error!\\n\\nLedger bulan berjalan tidak bisa diclose');window.history.back();</script>");
				exit;
				*/
			} else {

				//Check ledger sudah lewat X hari ($day_protect) baru bisa diclose
				//Get last date of month, +1 month, -1 day
				$strLastDateofActiveMonth = date("Y-m-d", mktime(0, 0, 0, $intMonth + 1, 1, $strYear) - 86400);
				//echo("strLastDateofActiveMonth = $strLastDateofActiveMonth");
				if (strtotime(date("Y-m-d")) - strtotime($strLastDateofActiveMonth) < (3600 * $this->day_protect)) {
					$this->error_message =
					'<div class="notification note-error">'.
					'<a href="#" class="close" title="Close notification">close</a>'.
					'<p><strong>Error notification:</strong> Ledger bulan lalu blm lewat 1 minggu</p>'.
					'</div>';
					/*
					echo("<script language=\"javascript\">alert('Error!\\n\\nLedger bulan lalu blm lewat 1 minggu');window.history.back();</script>");
					exit;
					*/
				} else {

					//Check ledger sudah diclose
					$strSQL = "SELECT * FROM fin_ledger
					WHERE ledger_date = '$strYear-$strMonth-01'";
					//echo("strSQL_close = $strSQL<p>"); exit();
					
					$commSQL = Yii::app()->db->createCommand($strSQL);
					$rowCount = $commSQL->execute();

					if ($rowCount > 0) {
						$this->error_message =
						'<div class="notification note-error">'.
						'<a href="#" class="close" title="Close notification">close</a>'.
						'<p><strong>Error notification:</strong> Ledger bulan $strMonth-$strYear sudah diclose</p>'.
						'</div>';
						/*
						echo("<script language=\"javascript\">alert('Error!\\n\\nLedger bulan $strMonth-$strYear sudah diclose');window.history.back();</script>");
						exit;
						*/
					}
				}
			}

			if (empty($this->error_message)) {
				//Create ledger
				$strSQL = "INSERT INTO fin_ledger (ledger_date, date_created, created_by, date_update, update_by)
				VALUES ('$strYear-$strMonth-01', NOW(), '".FHelper::GetUserName($userid_actor)."', NOW(), '".FHelper::GetUserName($userid_actor)."')";
				//echo("strSQL_close1 = $strSQL<p>"); exit();
				$commSQL = Yii::app()->db->createCommand($strSQL);
				$row = $commSQL->execute();

				$intId = Yii::app()->db->getLastInsertId();

				//Get data from journal

				//harusnya ambil semua coa di mtr_coa join dengan jurnal untuk dibuat ledger
				//jika mon start ambil saldo awal dari db, jika tidak ambil saldo bulan lalu dari ledger
				/*
				$strSQL = "SELECT a.coa_id, SUM(a.debit) AS sum_debit, SUM(a.credit) AS sum_credit FROM journal a
				INNER JOIN mtr_coa b ON a.coa_id = b.id
				WHERE MONTH(journal_date) = $intMonth AND YEAR(journal_date) = $strYear
				GROUP BY coa_id";
				*/

				$strSQL = "SELECT a.coa_id, 0 AS sum_debit, 0 AS sum_credit FROM mtr_coa a ORDER BY a.code";

				//echo("strSQL_close2 = $strSQL<p>"); exit();
				$commSQL = Yii::app()->db->createCommand($strSQL);
				$rowCount = $commSQL->execute();

				if ($rowCount > 0) {
					$coas = $commSQL->queryAll();

					foreach ($coas as $row) {
						$coa_id = $row['coa_id'];

						$debit = 0;
						$credit = 0;

						//debit
						$strSQL = "SELECT SUM(b.debit) AS debit FROM fin_journal b 
						WHERE MONTH(b.journal_date) = $intMonth AND YEAR(b.journal_date) = $strYear AND b.coa_id = $coa_id 
						GROUP BY b.coa_id";

						//echo("strSQL_view3 = $strSQL<p>"); exit();
						$commSQL1 = Yii::app()->db->createCommand($strSQL);
						$rowCount1 = $commSQL1->execute();

						if ($rowCount1 > 0) {
							$row2 = $commSQL1->queryRow();
							$debit = $row2['debit'];
						}

						//credit
						$strSQL = "SELECT SUM(b.credit) AS credit FROM fin_journal b 
						WHERE MONTH(b.journal_date) = $intMonth AND YEAR(b.journal_date) = $strYear AND b.coa_id = $coa_id 
						GROUP BY b.coa_id";

						//echo("strSQL_view3 = $strSQL<p>"); exit();
						$commSQL1 = Yii::app()->db->createCommand($strSQL);
						$rowCount1 = $commSQL1->execute();

						if ($rowCount1 > 0) {
							$row2 = $commSQL1->queryRow();
							$credit = $row2['credit'];
						}

						//Check if last month is app start month
						$tsLastMonth = mktime(0, 0, 0, $intMonth-1, 1, $strYear);
						$mon_lastmonth = date("Y-m", $tsLastMonth);

						if ($mon_lastmonth == $this->mon_start) {
							//Get data from coa starting balance
							$strSQL = "SELECT starting_debit AS sa_debit, starting_credit AS sa_credit FROM mtr_coa 
							WHERE coa_id = $coa_id";
						} else {
							//Get data from last month ledger
							//Every january saldo laba/rugi is reset to 0, so
							//check if month = 1 then get saldo from last month only for COA prefix 1, 2, 3 (neraca)

							if ($intMonth == 1) {
								$strSQL = "SELECT b.debit AS sa_debit, b.credit AS sa_credit FROM fin_ledger a 
								INNER JOIN fin_ledger_detail b ON a.ledger_id = b.ledger_id 
								INNER JOIN mtr_coa c ON b.coa_id = c.coa_id 
								WHERE b.coa_id = $coa_id AND MONTH(a.ledger_date) = ".date("n", $tsLastMonth)." AND YEAR(a.ledger_date) = ".date("Y", $tsLastMonth). " AND 
								(SUBSTR(c.code, 1, 1) = 1 OR SUBSTR(c.code, 1, 1) = 2 OR SUBSTR(c.code, 1, 1) = 3)";
							} else {
								$strSQL = "SELECT b.debit AS sa_debit, b.credit AS sa_credit FROM fin_ledger a 
								INNER JOIN fin_ledger_detail b ON a.ledger_id = b.ledger_id 
								WHERE b.coa_id = $coa_id AND MONTH(a.ledger_date) = ".date("n", $tsLastMonth)." AND YEAR(a.ledger_date) = ".date("Y", $tsLastMonth);
							}
						}

						//echo("strSQL_close3 = $strSQL<p>"); exit();
						$commSQL1 = Yii::app()->db->createCommand($strSQL);
						$rowCount1 = $commSQL1->execute();

						$dblSADebit = 0;
						$dblSACredit = 0;

						if ($rowCount1 > 0) {
							$row2 = $commSQL1->queryRow();

							$dblSADebit = $row2['sa_debit'];
							$dblSACredit = $row2['sa_credit'];
						}

						$dblDebit = $debit + $dblSADebit;
						$dblCredit = $credit + $dblSACredit;

						$strSQL = "INSERT INTO fin_ledger_detail (ledger_id, coa_id, debit, credit) 
						VALUES ($intId, $coa_id, $dblDebit, $dblCredit)";
						//echo("strSQL_close4 = $strSQL<p>");
						$commSQL1 = Yii::app()->db->createCommand($strSQL);
						$row = $commSQL1->execute();
					}

					//Insert Laba Rugi Ditahan ke bulan depan, coa_id = 214
					if ($intMonth == 12) {
						//set global variable: $month & $year
						$month = $intMonth;
						$year = $strYear;

						$tsNextMonth = mktime(0, 0, 0, $intMonth+1, 1, $strYear);
						$strTglNextMonth = date("Y-m-01", $tsNextMonth);
						$laba_rugi_bersih = calculate_laba_rugi(&$report, $strMonth, $strYear);
						$laba_rugi_ditahan = abs($laba_rugi_bersih); //calculate_laba_rugi() use global variable: $month & $year

						if (!empty($laba_rugi_ditahan)) send_to_journal('340000001', "$strTglNextMonth", "$laba_rugi_ditahan", 0, "$year{$month}", 99, '');

						//exit("======: '340000001', $strTglNextMonth, $laba_rugi_ditahan, 0, $year{$month}, 99, ''");
					}
				}

				//Close all journal source with status = POST
				//1. Voucher ref_type = 90
				$strSQL = "UPDATE voucher SET status = 'CLOSE' 
				WHERE status = 'POST' AND MONTH(voucher_date) = $intMonth AND YEAR(voucher_date) = $strYear";
				//echo("strSQL_close5 = $strSQL<p>");
				/*
				$commSQL = Yii::app()->db->createCommand($strSQL);
				$rowCount = $commSQL->execute();
				*/

				//2. Input Hutang ref_type = 10
				$strSQL = "UPDATE hutang SET status_input = 'CLOSE' 
				WHERE status_input = 'POST' AND MONTH(tgl) = $intMonth AND YEAR(tgl) = $strYear";
				//echo("strSQL_close6 = $strSQL<p>");
				/*
				$commSQL = Yii::app()->db->createCommand($strSQL);
				$rowCount = $commSQL->execute();
				*/

				//3. Bayar Hutang ref_type = 11
				$strSQL = "UPDATE hutang SET status_payment = 'CLOSE' 
				WHERE status_payment = 'PAID' AND MONTH(tgl) = $intMonth AND YEAR(tgl) = $strYear";
				//echo("strSQL_close6 = $strSQL<p>");
				/*
				$commSQL = Yii::app()->db->createCommand($strSQL);
				$rowCount = $commSQL->execute();
				*/
			}

			$ledger_status = "CLOSE";

			$ledger = $this->renderPartial(
				'v_ledger_close',
				array(
					'menuid' => $menuid,
					'userid_actor' => $userid_actor,
					'ledger_status' => $ledger_status,
					'year' => $strYear,
					'month' => $intMonth,
				    'error' => $this->error_message
				 ),
				 true
			);

			echo CJSON::encode(
				 array(
					  'ledger' => $ledger,
					  'notification_message' => $this->error_message,
					  'status' => 'ok'
				 )
			);

			//AuditLog
			$data = "Close Ledger, $strYear, $strMonth, $strCOAFrom, $strCOATo";

			FAudit::add('KEUANGANLEDGER', 'Edit', FHelper::GetUserName($userid_actor), $data);
		 }
		 else
		 {
			  //strBranchId, strDateFrom, strDateTo empty
		 }
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
?>