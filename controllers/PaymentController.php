<?php

class PaymentController extends FController
{
	private $success_message = '';
	private $tbl_id = 't.payment_id';

	private function getPaymentMethodList()
	{
		//ambil listData untuk paymentmethod
		$Criteria = new CDbCriteria();
		$Criteria->condition = 'is_del = "N" AND is_deact = "N" AND group_id = 5';
		$Criteria->order = 'order_no ASC';
		$mtr_std = mtr_std::model()->findAll($Criteria);
		return CHtml::listData($mtr_std, 'mtr_id', 'dsc');
		//print_r($paymentmethod);
		//exit();
	}

	public function actionIndex()
	{
	  ini_set('max_execution_time', 6000);
	  
		$menuid = 33;
		$parentmenuid = 7;

		$userid_actor = Yii::app()->request->getParam('userid_actor');
		$idlokasi = Yii::app()->request->cookies['idlokasi']->value;

		$allow_read = FHelper::AllowMenu($menuid, FHelper::GetGroupId($userid_actor), 'read');
		if ($allow_read) {
			$Criteria = new CDbCriteria();
			//$Criteria->condition = "t.status = 'NEW' AND Sales.branch_id = :idlokasi";
			//$Criteria->condition = "Sales.status = 'PAYMENT' AND Sales.branch_id = :idlokasi";
			$Criteria->condition = "(status = 'BAYAR' OR status = 'LUNAS') AND branch_id = :idlokasi";
			$Criteria->params = array(':idlokasi' => $idlokasi);
			$Criteria->order = 'date_created DESC';
			$Criteria->limit = 6000;
			//$recs = Payment::model()->with('Sales')->findAll($Criteria);
			$recs = Sales::model()->findAll($Criteria);

			$TheMenu = FHelper::RenderMenu(0, $userid_actor, $parentmenuid);

			$this->userid_actor = $userid_actor;
			$this->parentmenuid = $parentmenuid;

			$this->bread_crumb_list = '
				<li>Sales</li>
				<li>></li>
				<li>Pembayaran</li>';

			$this->layout = 'layout-baru';

			$TheContent = $this->renderPartial(
					'list',
					array(
							'userid_actor' => $userid_actor,
							'recs' => $recs,
							'menuid' => $menuid
					),
					true
			);

			$this->render(
					'index',
					array(
							'TheMenu' => $TheMenu,
							'TheContent' => $TheContent,
							'userid_actor' => $userid_actor
					)
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
	}

	public function actionList()
	{
		$menuid = 33;
		$userid_actor = Yii::app()->request->getParam('userid_actor');
		$idlokasi = Yii::app()->request->cookies['idlokasi']->value;

		$allow_read = FHelper::AllowMenu($menuid, FHelper::GetGroupId($userid_actor), 'read');
		if ($allow_read) {
			$Criteria = new CDbCriteria();
			//$Criteria->condition = "t.status = 'NEW' AND Sales.branch_id = :idlokasi";
			//$Criteria->condition = "Sales.status = 'PAYMENT' AND Sales.branch_id = :idlokasi";
			$Criteria->condition = "(status = 'BAYAR' OR status = 'LUNAS') AND branch_id = :idlokasi";
			$Criteria->params = array(':idlokasi' => $idlokasi);
			$Criteria->order = 'date_created DESC';
			//$recs = Payment::model()->with('Sales')->findAll($Criteria);
			$recs = Sales::model()->findAll($Criteria);

			$this->layout = 'layout-baru';

			$TheContent = $this->renderPartial(
					'list',
					array(
							'userid_actor' => $userid_actor,
							'recs' => $recs,
							'menuid' => $menuid
					),
					true
			);

			$bread_crumb_list =
			'<li>Sales</li>'.
			'<li>></li>'.
			'<li><a href="#" onclick="Payment_ShowList('.$userid_actor.');">Pembayaran</a></li>';

			echo CJSON::encode(
					array(
							'html' => $TheContent,
							'bread_crumb_list' => $bread_crumb_list,
							'notification_message' => $this->success_message
					)
			);
		}
		else
		{
			$this->bread_crumb_list = '
				<li>Not Authorize</li>';

			$this->layout = 'layout-baru';

			$TheContent = $this->renderPartial(
					array(
							'userid_actor' => $userid_actor
					),
					true
			);
		}
	}

	public function actionEdit()
	{
	  	$menuid = 33;

		$userid_actor = Yii::app()->request->getParam('userid_actor');
		$idlokasi = Yii::app()->request->cookies['idlokasi']->value;
		$do_edit = Yii::app()->request->getParam('do_edit');

		Yii::log('do_edit = ' . $do_edit, 'info');
		Yii::log('id = ' . $id, 'info');

		$paymentmethod_list = $this->getPaymentMethodList();
		$banks = FHelper::GetBankListData();
		$temp[] = '--Pilih Nama Bank--';
		$bank_list = $temp + $banks;

		if(isset($do_edit))
		{
			if($do_edit == 1)
			{
				//ambil variabel dari form
				/*
					variabel-variabel dari form dan querystring
					r:sales/edit
					userid_actor:1
					id:1
					do_edit:1
				*/

				$form = new PaymentForm();
				$form->attributes = Yii::app()->request->getParam('PaymentForm');
				$idpayment = Yii::app()->request->getParam('id');
				//echo "idpayment = $idpayment<br /><pre>";print_r($form);echo "</pre>";
				//exit();

				if($form->validate())
				{
					//form validated
					$Criteria = new CDbCriteria();
					$Criteria->condition = $this->tbl_id." = :id";
					$Criteria->params = array(':id' => $idpayment);

					$payment = Payment::model()->with('Sales')->find($Criteria);

					$intPaymentAmount = str_replace(".", "", $form['total_amount']);
					$intBalance = str_replace(".", "", $form['balance']);
					$intChange = str_replace(".", "", $form['change']);

					//update pos_payment
					$payment['payment_date'] = $form['payment_date_to_db'];
					$payment['payment_method_id'] = $form['payment_method_id'];
					$payment['card_bank_name'] = $form['card_bank_name'];
					$payment['total_amount'] = $intPaymentAmount;
					$payment['balance'] = $intBalance;
					$payment['change'] = $intChange;
					$payment['status'] = 'DONE';
					$payment['date_update'] = new CDbExpression('NOW()');
					$payment['update_by'] = FHelper::GetUserName($userid_actor);
					$payment['version'] = $payment['version'] + 1;
					$payment->update();

					$payment->Sales['status'] = 'LUNAS';
					$payment->Sales->update();

					//AuditLog
					$data = "$payment[payment_date], $payment[payment_method_id], $payment[card_bank_name], $payment[balance], $payment[change], $payment[date_update], $payment[update_by], $payment[version]";

					FAudit::add('PEMBAYARAN', 'Edit', FHelper::GetUserName($userid_actor), $data);

					$Criteria = new CDbCriteria();
					//$Criteria->condition = "t.status = 'NEW' AND Sales.branch_id = :idlokasi";
					$Criteria->condition = "Sales.status = 'PAYMENT' AND Sales.branch_id = :idlokasi";
					$Criteria->params = array(':idlokasi' => $idlokasi);
					$recs = Payment::model()->with('Sales')->findAll($Criteria);

					$bread_crumb_list =
					'<li>Sales</li>'.
					'<li>></li>'.
					'<li><a href="#" onclick="Payment_ShowList('.$userid_actor.');">Pembayaran</a></li>';

					$success_message =
					'<div class="notification note-success">'.
					'<a href="#" class="close" title="Close notification">close</a>'.
					'<p><strong>Success notification:</strong> Data berhasil diupdate</p>'.
					'</div>';

					$html = $this->renderPartial(
						'list',
						array(
							'userid_actor' => $userid_actor,
							'recs' => $recs,
							'menuid' => $menuid,
							'bank_list' => $bank_list
						),
						true
					);
				}
				else
				{
					//form not validated
					$bread_crumb_list =
					'<li>Sales</li>'.
					'<li>></li>'.
					'<li><a href="#" onclick="Payment_ShowList('.$userid_actor.');">Penjualan</a></li>'.
					'<li>></li>'.
					'<li>Edit Penjualan</li>';

					$html = $this->renderPartial(
						'edit',
						array(
							'form' => $form,
							'userid_actor' => $userid_actor,
							'id' => $id,
							'active_option' => $active_option,
							'menuid' => $menuid
						),
						true
					);
				}
			}
			else
			{
				//back to list
				$userid_actor = Yii::app()->request->getParam('userid_actor');
				$recs = Sales::model()->findAll();

				$this->layout = 'layout-baru';

				$html = $this->renderPartial(
					'list',
					array(
						'userid_actor' => $userid_actor,
						'recs' => $recs,
						'menuid' => $menuid
					),
					true
				);
			}
		}
		else
		{
			//show form
			if(FHelper::AllowMenu($menuid, FHelper::GetGroupId($userid_actor), 'edit'))
			{
			    Yii::log('do_edit not set', 'info');

				$form = new SalesForm();

				$idsales = Yii::app()->request->getParam('id');

				Yii::log('idsales = ' . $idsales, 'info');

				//get payment amount for outstanding payment
				$Criteria = new CDbCriteria();
				$Criteria->condition = 'sales_id = :id';
				$Criteria->params = array(':id' => $idsales);
				$Criteria->order = "payment_id DESC";
				$Criteria->limit = 1;

				$count = Payment::model()->count($Criteria);
				
				$payment = array();
                if($count > 0)
                {
					$rec = Payment::model()->find($Criteria);
					$payment['cash_amount'] = 0;
					$payment['noncash_amount'] = 0;
					$payment['balance'] = $rec['balance'];
					$payment['new_tn'] = $rec['term_no'] + 1;
				} else {
					$Criteria = new CDbCriteria();
					$Criteria->condition = 'sales_id = :id';
					$Criteria->params = array(':id' => $idsales);

					$rec = Sales::model()->find($Criteria);
					$payment['cash_amount'] = 0;
					$payment['noncash_amount'] = 0;
					$payment['balance'] = $rec['balance'];
					$payment['new_tn'] = 1;
				}
				
				//sales
				$Criteria = new CDbCriteria();
				$Criteria->condition = 'sales_id = :id';
				$Criteria->params = array(':id' => $idsales);

				$sales = Sales::model()->find($Criteria);
				
				//payment history
				$Criteria = new CDbCriteria();
				$Criteria->condition = 'sales_id = :id';
				$Criteria->params = array(':id' => $idsales);
				//$Criteria->order = 'term_no';

				$payment_history_recs = Payment::model()->with('PaymentMethod','BankName')->findAll($Criteria);
				//print_r($payment_history_recs);
				/*
				foreach($payment_history_recs as $rec) {
					echo "$rec[payment_method_id] <br/>";
					print_r($rec->PaymentMethod);
					echo $rec->PaymentMethod['dsc'];
				}*/
				//exit();

				$bread_crumb_list =
				'<li>Sales</li>'.
				'<li>></li>'.
				'<li><a href="#" onclick="Payment_ShowList('.$userid_actor.');">Pembayaran</a></li>'.
				'<li>></li>'.
				'<li>Edit Pembayaran</li>';

				$html = $this->renderPartial(
					'edit',
					array(
						'form' => $form,
						'userid_actor' => $userid_actor,
						'menuid' => $menuid,
						'payment' => $payment,
						'sales' => $sales,
						'payment_history_recs' => $payment_history_recs,
						'paymentmethod_list' => $paymentmethod_list,
						'bank_list' => $bank_list
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

				/*
				$url = $this->createUrl(
							'index/showinvalidaccess',
							array('userid_actor' => $userid_actor)
						);

				$this->redirect($url);
				*/
			}
		}

		echo CJSON::encode(
			array(
				'html' => $html,
				'bread_crumb_list' => $bread_crumb_list,
				'notification_message' => $success_message
			)
		);
	}

	public function actionKalkulatorPembayaran()
	{
		$idlokasi = Yii::app()->request->cookies['idlokasi']->value;
		$intSaleAmount = Yii::app()->request->getParam('ts');
		$intPaymentAmount = Yii::app()->request->getParam('tp');
		$intPaymentCardAmount = Yii::app()->request->getParam('tk');
		$intBalanceAmount = Yii::app()->request->getParam('tb');

		$status = 'ok';

		$intPaymentAmount = $intPaymentAmount + $intPaymentCardAmount;

		if ($intPaymentAmount > $intBalanceAmount) {
			$intChangeAmount = $intPaymentAmount - $intBalanceAmount;
			$intBalanceAmount = 0;
		} else {
			$intChangeAmount = 0;
			$intBalanceAmount = $intBalanceAmount - $intPaymentAmount;
		}

		echo CJSON::encode(
			array(
				'status' => $status,
				'change' => number_format($intChangeAmount, 0, '.', '.'),
				'balance' => number_format($intBalanceAmount, 0, '.', '.'),
				'pesan' => ''
			)
		);
	}

	public function actionTambahPembayaran()
	{
		$userid_actor = Yii::app()->request->getParam('userid_actor');
		$idlokasi = Yii::app()->request->cookies['idlokasi']->value;

		$form = new PaymentForm();
		$form->attributes = Yii::app()->request->getParam('PaymentForm');

		$intTotalAmount = str_replace(".", "", $form['total_amount']);
		$intBalance = str_replace(".", "", $form['balance']);
		$intCash = str_replace(".", "", $form['cash_amount']);
		$intNonCash = str_replace(".", "", $form['noncash_amount']);
		$intChange = str_replace(".", "", $form['change']);

		//create new payment
		$payment = new Payment();
		$payment['sales_id'] = $form['sales_id'];
		$payment['term_no'] = $form['term_no'];
		$payment['payment_date'] = $form['payment_date_to_db'];
		$payment['total_amount'] = $intTotalAmount;
		$payment['balance'] = $intBalance;
		$payment['cash_amount'] = $intCash;
		$payment['noncash_amount'] = $intNonCash;
		$payment['change'] = $intChange;
		$payment['payment_method_id'] = $form['payment_method_id'];
		$payment['card_bank_name'] = $form['card_bank_name'];
		$payment['status'] = 'DONE';
		$payment['date_created'] = new CDbExpression('NOW()');
		$payment['created_by'] = FHelper::GetUserName($userid_actor);
		$payment['version'] = 1;
		$payment->save();

		//update sales
		$strSalesStatus = 'BAYAR';
		if ($intBalance == 0) {
			$payment->Sales['close_date'] = new CDbExpression('NOW()');
			$strSalesStatus = 'LUNAS';
		}

		$payment->Sales['status'] = $strSalesStatus;
		$payment->Sales['invoice_no'] = FHelper::GenerateInvoiceNo($idlokasi);
		$payment->Sales['balance'] = $intBalance;
		$payment->Sales['status'] = $strSalesStatus;
		$payment->Sales['date_update'] = new CDbExpression('NOW()');
		$payment->Sales['update_by'] = FHelper::GetUserName($userid_actor);
		$payment->Sales['version'] = $sales['version'] + 1;
		$payment->Sales->update();

		//AuditLog
		$data = "$payment[payment_date], $payment[total_amount], $payment[balance], $payment[cash_amount], $payment[noncash_amount], $payment[change], $payment[payment_method_id], $payment[card_bank_name], $payment[status], $payment[date_created], $payment[created_by], $payment[version]";

		FAudit::add('PEMBAYARAN', 'Add', FHelper::GetUserName($userid_actor), $data);

		$status = 'ok';

		//ambil deskripsi paymentmethod
		$mtr_std = MtrStd::model()->findByPk($form['payment_method_id']);
		$bank_name = sys_bank::model()->findByPk($form['card_bank_name']);
		$strCaraBayar = $mtr_std['dsc'];

		if(!empty($bank_name)) $strCaraBayar .= " - ".$bank_name['nama'];
		
		echo CJSON::encode(
			array(
				'status' => $status,
				'term_no' => $form['term_no'].".",
				'tgl' => $form['payment_date'],
				'cara' => $strCaraBayar,
				'pembayaran' => number_format($intCash + $intNonCash, 0, '.', '.'),
				'kembali' => number_format($intChange, 0, '.', '.'),
				'sisa' => number_format($intBalance, 0, '.', '.'),
				'sisa_no_format' => $intBalance,
				'sales_status' => $strSalesStatus,
			)
		);
	}

	public function actionPrintInvoice()
	{
	  	$menuid = 33;

		$userid_actor = Yii::app()->request->getParam('userid_actor');
		$idlokasi = Yii::app()->request->cookies['idlokasi']->value;
		$idsales = Yii::app()->request->getParam('id');

		if(FHelper::AllowMenu($menuid, FHelper::GetGroupId($userid_actor), 'edit'))
		{
			$Criteria = new CDbCriteria();
			$Criteria->condition = "t.sales_id = :id";
			$Criteria->params = array(':id' => $idsales);

			$sales = Sales::model()->with('SalesDetail','SalesCustomer')->find($Criteria);

			$Criteria = new CDbCriteria();
			$Criteria->condition = 't.sales_id = :id';
			$Criteria->params = array(':id' => $idsales);
			$Criteria->order = "payment_id DESC";
			$Criteria->limit = 1;

			$payment_rec = Payment::model()->with('Sales')->find($Criteria);

			$strCustName = $sales->SalesCustomer['name'];

			$intTotalPayment = ($sales['total'] - $sales['balance']);

			//echo "$idsales - $strCustName - $intTotalPayment";
			//exit();

			$printInvoice = $this->renderPartial(
				'v_print_invoice',
				array(
					'cust_name' => $strCustName,
					'sales' => $sales,
					'payment' => $payment_rec,
					'payment_total' => $intTotalPayment,
				),
				true
			);

			

			//send sms
			/*
			
			Oleh: Frans Indroyono
			Pesan: untuk sementara tidak melakukan pengiriman otomatis ini. Karena
			dianggap terlalu banyak dibandingkan pesan lain yang lebih 'berbobot'.
			
			//get template sms untuk update kustomer
			$smstplCC = new CDbCriteria();
			$smstplCC->condition = 'tpl_id = 5 AND is_deact = "N"';
			$smstpl = Smstemplate::model()->find($smstplCC);
			
			$smssend = new Smssend();
			$smssend['dest_name'] = $sales->SalesCustomer['name'];
			$smssend['dest_mobile'] = $sales->SalesCustomer['mobile'];
			$smssend['content'] = $smstpl['content'];
			$smssend['date_created'] = new CDbExpression('NOW()');
			$smssend['created_by'] = FHelper::GetUserName($userid_actor);
			$smssend['is_proc'] = 'N';
			*/
			
			//echo "<pre>".print_r($smssend)."</pre>";
			//exit();

			if ($sales['status'] == 'LUNAS') 
			{
			  /*
        
        Oleh: Frans Indroyono
        Pesan: untuk sementara tidak melakukan pengiriman otomatis ini. Karena
			  dianggap terlalu banyak dibandingkan pesan lain yang lebih 'berbobot'.
			  
        $smssend->save();
        $idsms = $smssend->getPrimaryKey();
        
        //integrasi gampp sms api
        $hasil = FHelper::KirimSms($sales->SalesCustomer['mobile'], $smstpl['content']);
        
        Yii::log('gampp sms api : ' . print_r($hasil, true), 'info');
        
        
        if($hasil == "OK")
        {
          $criteria = new CDbCriteria();
          $criteria->condition = 'sms_id = :id';
          $criteria->params = array(':id' => $idsms);
          
          $smssend = Smssend::model()->find($criteria);
          $smssend['is_proc'] = 'Y';
          $smssend['date_proc'] = date('Y-m-j H:i:s');
          $smssend->save();
        }
        */
      }

			echo $printInvoice;
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

			/*
			$url = $this->createUrl(
						'index/showinvalidaccess',
						array('userid_actor' => $userid_actor)
					);

			$this->redirect($url);
			*/
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