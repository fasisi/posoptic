<?php
class BayarController extends FController
{
	private $success_message = '';

	private function getSupplierList()
	{
		//ambil listData untuk supplier
		$Criteria = new CDbCriteria();
		$Criteria->condition = 'is_del = "N" AND is_deact = "N"';
		$Criteria->order = 'name ASC';
		$mtr_supplier = mtr_supplier::model()->findAll($Criteria);

		return CHtml::listData($mtr_supplier, 'supplier_id', 'name');
	}

	public function actionIndex()
	{
		$menuid = 31;
		$parentmenuid = 8;

		$userid_actor = Yii::app()->request->getParam('userid_actor');
		$this->idlokasi = Yii::app()->request->cookies['idlokasi']->value;

		$allow_read = FHelper::AllowMenu($menuid, FHelper::GetGroupId($userid_actor), 'read');
		if ($allow_read) {
			$Criteria = new CDbCriteria();
			$Criteria->condition = "t.is_del = 'N'";
			$Criteria->order = "code ASC";

			$bayars = Hutang::model()->with('Supplier')->findAll($Criteria);

			$TheMenu = FHelper::RenderMenu(0, $userid_actor, $parentmenuid);

			$this->userid_actor = $userid_actor;
			$this->parentmenuid = $parentmenuid;

			$this->bread_crumb_list = '
				<li>Keuangan</li>
				<li>></li>
				<li>Pembayaran Hutang</li>';

	   		$this->layout = 'layout-baru';

			$TheContent = $this->renderPartial(
				'list',
				array(
					'userid_actor' => $userid_actor,
					'bayars' => $bayars,
					'menuid' => $menuid
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

	public function actionListBayar()
	{
		$menuid = 31;
		$userid_actor = Yii::app()->request->getParam('userid_actor');

		$allow_read = FHelper::AllowMenu($menuid, FHelper::GetGroupId($userid_actor), 'read');
		if ($allow_read) {
			$Criteria = new CDbCriteria();
			$Criteria->condition = "t.is_del = 'N'";
			$Criteria->order = "code ASC";

			$bayars = Hutang::model()->with('Supplier')->findAll($Criteria);

			$this->layout = 'layout-baru';

			$TheContent = $this->renderPartial(
				'list',
				array(
					'userid_actor' => $userid_actor,
					'bayars' => $bayars,
					'menuid' => $menuid
				),
				true
			);

			$bread_crumb_list =
				'<li>Keuangan</li>'.
				'<li>></li>'.
				'<li>Pembayaran Hutang</li>';

		}
		else
		{
			$bread_crumb_list = '
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

		echo CJSON::encode(
			array(
				'html' => $TheContent,
				'bread_crumb_list' => $bread_crumb_list
			)
		);
	}

	public function actionViewBayar()
	{
	  	$menuid = 31;
		$userid_actor = Yii::app()->request->getParam('userid_actor');

	  	$allow_read = FHelper::AllowMenu($menuid, FHelper::GetGroupId($userid_actor), 'read');
	  	if ($allow_read) {
			$idhutang = Yii::app()->request->getParam('idhutang');
			$paid_option = array('N' => 'Tidak', 'Y' => 'Ya');

			$Criteria = new CDbCriteria();
			$Criteria->condition = 'hutang_id = :idhutang';
			$Criteria->params = array(':idhutang' => $idhutang);

			$hutang = Hutang::model()->with('Supplier')->find($Criteria);

			$form = new HutangForm();
			$form['tgl'] = $hutang['tgl'];
			$form['faktur_no'] = $hutang['faktur_no'];
			$form['po_no'] = $hutang['po_no'];
			$form['supplier_id'] = $hutang->Supplier['name'];
			$form['total'] = $hutang['total'];
			$form['note'] = $hutang['note'];
			$form['is_paid'] = $hutang['is_paid'];
			$form['payment_date'] = $hutang['payment_date'];
			$form['payment_code'] = $hutang['payment_code'];
			$form['status_input'] = $hutang['status_input'];
			$form['status_payment'] = $hutang['status_payment'];
			$form['date_created'] = $hutang['date_created'];
			$form['created_by'] = $hutang['created_by'];
			$form['date_update'] = $hutang['date_update'];
			$form['update_by'] = $hutang['update_by'];

			$bread_crumb_list =
				'<li>Keuangan</li>'.
				'<li>></li>'.
				'<li><a href="#" onclick="ShowList('.$userid_actor.');">Pembayaran Hutang</a></li>'.
				'<li>></li>'.
				'<li>View Pembayaran Hutang</li>';

			$TheContent = $this->renderPartial(
				'view',
				array(
					'form' => $form,
					'userid_actor' => $userid_actor,
					'idhutang' => $idhutang,
					'paid_option' => $paid_option,
					'menuid' => $menuid
				),
				true
			);

			//AuditLog
			$data = "$hutang[hutang_id], $hutang[tgl], $hutang[faktur_no], $hutang[po_no], $hutang[supplier_id], $hutang[total], ".
				    "$hutang[note], $hutang[is_paid], $hutang[payment_date], $hutang[payment_code], $hutang[status_input], ".
					"$hutang[status_payment], $hutang[date_created], $hutang[created_by], $hutang[date_update], $hutang[update_by], ". "$hutang[version]";

			FAudit::add('KEUANGANHUTANG', 'View', FHelper::GetUserName($userid_actor), $data);
		}
		else
		{
			$bread_crumb_list = '
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

		echo CJSON::encode(
			array(
				'html' => $TheContent,
				'bread_crumb_list' => $bread_crumb_list
			)
		);
	}

	public function actionEditBayar()
	{
	  	$menuid = 31;

		$userid_actor = Yii::app()->request->getParam('userid_actor');
		$do_edit = Yii::app()->request->getParam('do_edit');
		$idhutang = Yii::app()->request->getParam('idhutang');

		Yii::log('do_edit = ' . $do_edit, 'info');
		Yii::log('idhutang = ' . $idhutang, 'info');

		$paid_option = array('N' => 'Tidak', 'Y' => 'Ya');

		if(isset($do_edit))
		{
			if($do_edit == 1)
			{
				//process form
				$form = new HutangForm();
				$form->attributes = Yii::app()->request->getParam('HutangForm');
				// "<pre>".print_r($form)."</pre>";
				//exit();

				//Yii::log('edit form[name] = ' . $form['name'], 'info');

				if($form->validate())
				{
					//form validated
					Yii::log('validated', 'info');

					$Criteria = new CDbCriteria();
					$Criteria->condition = 'hutang_id = :idhutang';
					$Criteria->params = array(':idhutang' => $idhutang);

					$hutang = Hutang::model()->find($Criteria);
					/*
					$hutang['tgl'] = $form['tgl_to_db'];
					$hutang['faktur_no'] = $form['faktur_no'];
					$hutang['po_no'] = $form['po_no'];
					$hutang['supplier_id'] = $form['supplier_id'];
					$hutang['due_date'] = $form['due_date'];
					$hutang['total'] = $form['total'];
					*/
					$hutang['note'] = $form['note'];
					if(!empty($form['is_paid'])) {
						$hutang['is_paid'] = 'Y';
						$hutang['status_payment'] = 'SDHBYR';
						$hutang['payment_date'] = $form['payment_date_to_db'];
					} else {
						$hutang['is_paid'] = 'N';
						$hutang['status_payment'] = 'BLMBYR';
						$hutang['payment_date'] = '0000-00-00';
					}

					//$hutang['payment_code'] = $form['payment_code'];
					$hutang['status_input'] = $form['status_input'];
					$hutang['date_update'] = new CDbExpression('NOW()');
					$hutang['update_by'] = FHelper::GetUserName($userid_actor);
					$hutang['version'] = $hutang['version'] + 1;

					//echo "<pre>".print_r($hutang)."</pre>";
					//exit();

					$hutang->update();

					$hutang = Hutang::model()->find($Criteria);

					$form = new HutangForm();
					$form['tgl'] = $hutang['tgl'];
					$form['faktur_no'] = $hutang['faktur_no'];
					$form['po_no'] = $hutang['po_no'];
					$form['supplier_id'] = $hutang->Supplier['name'];
					$form['total'] = $hutang['total'];
					$form['note'] = $hutang['note'];
					$form['is_paid'] = $hutang['is_paid'];
					$form['payment_date'] = $hutang['payment_date'];
					$form['payment_code'] = $hutang['payment_code'];
					$form['status_input'] = $hutang['status_input'];
					$form['status_payment'] = $hutang['status_payment'];
					$form['date_created'] = $hutang['date_created'];
					$form['created_by'] = $hutang['created_by'];
					$form['date_update'] = $hutang['date_update'];
					$form['update_by'] = $hutang['update_by'];

					//AuditLog
					$data = "$hutang[hutang_id], $hutang[tgl], $hutang[faktur_no], $hutang[po_no], $hutang[supplier_id], $hutang[total], ".
							"$hutang[note], $hutang[is_paid], $hutang[payment_date], $hutang[payment_code], $hutang[status_input], ".
							"$hutang[status_payment], $hutang[date_created], $hutang[created_by], $hutang[date_update], $hutang[update_by], ". "$hutang[version]";

					FAudit::add('KEUANGANHUTANG', 'Edit', FHelper::GetUserName($userid_actor), $data);

					$bread_crumb_list =
					'<li>Keuangan</li>'.
					'<li>></li>'.
					'<li><a href="#" onclick="ShowList('.$userid_actor.');">Pembayaran Hutang</a></li>'.
					'<li>></li>'.
					'<li>View Pembayaran Hutang</li>';

					$this->success_message =
					'<div class="notification note-success">'.
					'<a href="#" class="close" title="Close notification">close</a>'.
					'<p><strong>Success notification:</strong> Data faktur '.$hutang['faktur_no'].' tgl '.date("d-m-Y", strtotime($hutang['tgl'])).' berhasil diupdate</p>'.
					'</div>';

					$TheContent = $this->renderPartial(
							'view',
							array(
									'form' => $form,
									'userid_actor' => $userid_actor,
									'idhutang' => $idhutang,
									'paid_option' => $paid_option,
									'menuid' => $menuid
							),
							true
					);
				}
				else
				{
					//form not validated
					$bread_crumb_list =
					'<li>Keuangan</li>'.
					'<li>></li>'.
					'<li><a href="#" onclick="ShowList('.$userid_actor.');">Pembayaran Hutang</a></li>'.
					'<li>></li>'.
					'<li>Edit Pembayaran Hutang</li>';

					$TheContent = $this->renderPartial(
							'edit',
							array(
									'form' => $form,
									'userid_actor' => $userid_actor,
									'idhutang' => $idhutang,
									'paid_option' => $paid_option,
									'menuid' => $menuid
							),
							true
					);
				}
			}
			else
			{
				//back to list
				$Criteria = new CDbCriteria();
				$Criteria->condition = "t.is_del = 'N'";
				//$Criteria->limit = 20;

				$bayars = Hutang::model()->findAll($Criteria);

				$this->layout = 'layout-baru';

				$TheContent = $this->renderPartial(
						'list',
						array(
								'userid_actor' => $userid_actor,
								'bayars' => $bayars,
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

				$supplier_list = $this->getSupplierList();

				$Criteria = new CDbCriteria();
				$Criteria->condition = 'hutang_id = :idhutang';
				$Criteria->params = array(':idhutang' => $idhutang);

				Yii::log('idhutang = ' . $idhutang, 'info');

				$hutang = Hutang::model()->find($Criteria);

				$form = new HutangForm();
				$form['tgl'] = $hutang['tgl'];
				$form['faktur_no'] = $hutang['faktur_no'];
				$form['po_no'] = $hutang['po_no'];
				$form['supplier_id'] = $hutang['supplier_id'];
				$form['total'] = $hutang['total'];
				$form['note'] = $hutang['note'];
				$form['is_paid'] = $hutang['is_paid'];
				$form['payment_date'] = $hutang['payment_date'];
				$form['payment_code'] = $hutang['payment_code'];
				$form['status_input'] = $hutang['status_input'];
				$form['status_payment'] = $hutang['status_payment'];
				$form['date_created'] = $hutang['date_created'];
				$form['created_by'] = $hutang['created_by'];
				$form['date_update'] = $hutang['date_update'];
				$form['update_by'] = $hutang['update_by'];

				$bread_crumb_list =
				'<li>Keuangan</li>'.
				'<li>></li>'.
				'<li><a href="#" onclick="ShowList('.$userid_actor.');">Pembayaran Hutang</a></li>'.
				'<li>></li>'.
				'<li>Edit Pembayaran Hutang</li>';

				$TheContent = $this->renderPartial(
						'edit',
						array(
								'form' => $form,
								'userid_actor' => $userid_actor,
								'idhutang' => $idhutang,
								'paid_option' => $paid_option,
								'menuid' => $menuid,
								'supplier_list' => $supplier_list
						),
						true
				);
			}
			else
			{
				$bread_crumb_list = '
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

		echo CJSON::encode(
				array(
						'html' => $TheContent,
						'bread_crumb_list' => $bread_crumb_list,
						'notification_message' => $this->success_message
				)
		);
	}

	/*
	 actionDeleteItem

	Deskripsi
	Action untuk menghapus data, yaitu mengubah flag is_del menjadi Y
	*/
	public function actionDeleteBayar()
	{
		$menuid = 31;
		$userid_actor = Yii::app()->request->getParam('userid_actor');

		$allow_delete = FHelper::AllowMenu($menuid, FHelper::GetGroupId($userid_actor), 'delete');
		if ($allow_delete) {
			$idhutang = Yii::app()->request->getParam('idhutang');

			$Criteria = new CDbCriteria();
			$Criteria->condition = 'hutang_id = :idhutang';
			$Criteria->params = array(':idhutang' => $idhutang);

			//update record di tabel
			$hutang = Hutang::model()->find($Criteria);
			$hutang['is_del'] = 'Y';
			$hutang->update();

			$this->success_message =
			'<div class="notification note-success">'.
			'<a href="#" class="close" title="Close notification">close</a>'.
			'<p><strong>Success notification:</strong> Data faktur '.$hutang['faktur_no'].' tgl '.date("d-m-Y", strtotime($hutang['tgl'])).' berhasil dihapus</p>'.
			'</div>';

			//AuditLog
			$data = "$hutang[hutang_id], $hutang[tgl], $hutang[faktur_no], $hutang[po_no], $hutang[supplier_id], $hutang[total], ".
				    "$hutang[note], $hutang[is_paid], $hutang[payment_date], $hutang[payment_code], $hutang[status_input], ".
					"$hutang[status_payment], $hutang[date_created], $hutang[created_by], $hutang[date_update], $hutang[update_by], ". "$hutang[version]";

			FAudit::add('KEUANGANHUTANG', 'Del', FHelper::GetUserName($userid_actor), $data);

			$this->actionListBayar();
		}
		else
		{
			$bread_crumb_list = '
				<li>Not Authorize</li>';

			$this->layout = 'layout-baru';

			$TheContent = $this->renderPartial(
				'not_auth',
				array(
					'userid_actor' => $userid_actor
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

	/*
	 actionSelection

	Deskripsi
	Action bersama untuk data yang dipilih
	*/
	public function actionSelectionBayar()
	{
		$menuid = 31;
		$userid_actor = Yii::app()->request->getParam('userid_actor');

		$allow_edit = FHelper::AllowMenu($menuid, FHelper::GetGroupId($userid_actor), 'edit');
		if ($allow_edit) {
			$action_type = Yii::app()->request->getParam('cboActionType');
			$item_list = Yii::app()->request->getParam('chkSelectedItem');
			//echo "action_type = $action_type";
			//echo "<pre>".print_r($item_list)."</pre>";
			//exit();

			$Criteria = new CDbCriteria();
			$Criteria->condition = 'hutang_id = :idhutang';

			if(!empty($action_type))
			{
				foreach($item_list as $key => $value)
				{
					$Criteria->params = array(':idhutang' => $value);
					$hutang = Hutang::model()->find($Criteria);

					$hutang['is_del'] = $action_type;
					//echo "<pre>".print_r($hutang)."</pre>";
					//exit();

					$hutang->update();

					//AuditLog
					$data = "$hutang[hutang_id], $hutang[tgl], $hutang[faktur_no], $hutang[po_no], $hutang[supplier_id], $hutang[total], ".
							"$hutang[note], $hutang[is_paid], $hutang[payment_date], $hutang[payment_code], $hutang[status_input], ".
							"$hutang[status_payment], $hutang[date_created], $hutang[created_by], $hutang[date_update], $hutang[update_by], ". "$hutang[version]";

					FAudit::add('KEUANGANHUTANG', 'Del', FHelper::GetUserName($userid_actor), $data);
				}

				$this->success_message =
				'Data yang dipilih berhasil diupdate';

				$this->actionListBayar();
			}
			else
			{
				$bread_crumb_list = '
					<li>Not Authorize</li>';

				$this->layout = 'layout-baru';

				$TheContent = $this->renderPartial(
					'not_auth',
					array(
						'userid_actor' => $userid_actor
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