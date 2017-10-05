<?php
class PiutangController extends FController
{
	private $success_message = '';

	public function actionIndex()
	{
		$menuid = 29;
		$parentmenuid = 8;

		$userid_actor = Yii::app()->request->getParam('userid_actor');
		$this->idlokasi = Yii::app()->request->cookies['idlokasi']->value;

		$allow_read = FHelper::AllowMenu($menuid, FHelper::GetGroupId($userid_actor), 'read');
		if ($allow_read) {
			$Criteria = new CDbCriteria();
			$Criteria->condition = "t.is_del = 'N'";
			$Criteria->order = "tgl DESC";

			$piutangs = Piutang::model()->findAll($Criteria);

			$TheMenu = FHelper::RenderMenu(0, $userid_actor, $parentmenuid);

			$this->userid_actor = $userid_actor;
			$this->parentmenuid = $parentmenuid;

			$this->bread_crumb_list = '
				<li>Keuangan</li>
				<li>></li>
				<li>Piutang</li>';

	   		$this->layout = 'layout-baru';

			$TheContent = $this->renderPartial(
				'list',
				array(
					'userid_actor' => $userid_actor,
					'piutangs' => $piutangs,
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

	public function actionListPiutang()
	{
		$menuid = 29;
		$userid_actor = Yii::app()->request->getParam('userid_actor');

		$allow_read = FHelper::AllowMenu($menuid, FHelper::GetGroupId($userid_actor), 'read');
		if ($allow_read) {
			$Criteria = new CDbCriteria();
			$Criteria->condition = "t.is_del = 'N'";
			$Criteria->order = "tgl DESC";

			$piutangs = Piutang::model()->findAll($Criteria);

			$this->layout = 'layout-baru';

			$TheContent = $this->renderPartial(
				'list',
				array(
					'userid_actor' => $userid_actor,
					'piutangs' => $piutangs,
					'menuid' => $menuid
				),
				true
			);

			$bread_crumb_list =
				'<li>Keuangan</li>'.
				'<li>></li>'.
				'<li>Piutang</li>';

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

	public function actionViewPiutang()
	{
	  	$menuid = 29;
		$userid_actor = Yii::app()->request->getParam('userid_actor');

	  	$allow_read = FHelper::AllowMenu($menuid, FHelper::GetGroupId($userid_actor), 'read');
	  	if ($allow_read) {
			$idpiutang = Yii::app()->request->getParam('idpiutang');
			$active_option = array('N' => 'Tidak', 'Y' => 'Ya');

			$Criteria = new CDbCriteria();
			$Criteria->condition = 'piutang_id = :idpiutang';
			$Criteria->params = array(':idpiutang' => $idpiutang);

			$piutang = Piutang::model()->find($Criteria);

			$form = new PiutangForm();
			$form['tgl'] = $piutang['tgl'];
			$form['faktur_no'] = $piutang['faktur_no'];
			$form['so_no'] = $piutang['so_no'];
			$form['nama_piutang'] = $piutang['nama_piutang'];
			$form['no_telpon'] = $piutang['no_telpon'];
			$form['alamat'] = $piutang['alamat'];
			$form['total'] = $piutang['total'];
			$form['note'] = $piutang['note'];
			$form['is_paid'] = $piutang['is_paid'];
			$form['payment_date'] = $piutang['payment_date'];
			$form['status_payment'] = $piutang['status_payment'];
			$form['date_created'] = $piutang['date_created'];
			$form['created_by'] = $piutang['created_by'];
			$form['date_update'] = $piutang['date_update'];
			$form['update_by'] = $piutang['update_by'];

			$bread_crumb_list =
				'<li>Keuangan</li>'.
				'<li>></li>'.
				'<li><a href="#" onclick="ShowList('.$userid_actor.');">Piutang</a></li>'.
				'<li>></li>'.
				'<li>View Piutang</li>';

			$TheContent = $this->renderPartial(
				'view',
				array(
					'form' => $form,
					'userid_actor' => $userid_actor,
					'idpiutang' => $idpiutang,
					'active_option' => $active_option,
					'menuid' => $menuid
				),
				true
			);

			//AuditLog
			$data = "$piutang[piutang_id], $piutang[tgl], $piutang[faktur_no], $piutang[so_no], $piutang[nama_piutang], ".
				    "$piutang[no_telpon], $piutang[alamat], $piutang[total], $piutang[note], $piutang[is_paid], ".
					"$piutang[payment_date], $piutang[status_input], $piutang[status_payment], $piutang[date_created], ".
					"$piutang[created_by], $piutang[date_update], $piutang[update_by], $piutang[version]";

			FAudit::add('KEUANGANPIUTANG', 'View', FHelper::GetUserName($userid_actor), $data);
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

	public function actionAddPiutang()
	{
		$menuid = 29;

		$userid_actor = Yii::app()->request->getParam('userid_actor');
		$do_add = Yii::app()->request->getParam('do_add');

		Yii::log('do_add = ' . $do_add, 'info');

		$active_option = array('N' => 'Tidak', 'Y' => 'Ya');

		if(isset($do_add))
		{
			if($do_add == 1)
			{
				//process form
				$form = new PiutangForm();
				$form->attributes = Yii::app()->request->getParam('PiutangForm');
				//echo "<pre>".print_r($form->attributes)."</pre>";
				//exit();

				//Yii::log('add form[name] = ' . $form['name'], 'info');

				if($form->validate())
				{
					//form validated
					Yii::log('validated', 'info');

					$piutang = new Piutang();

					$piutang['tgl'] = $form['tgl_to_db'];
					$piutang['faktur_no'] = $form['faktur_no'];
					$piutang['so_no'] = $form['so_no'];
					$piutang['nama_piutang'] = $form['nama_piutang'];
					$piutang['no_telpon'] = $form['no_telpon'];
					$piutang['alamat'] = $form['alamat'];
					$piutang['due_date'] = $form['due_date'];
					$piutang['total'] = $form['total'];
					$piutang['note'] = $form['note'];

					if(!empty($form['is_paid'])) {
						$piutang['is_paid'] = 'Y';
						$piutang['status_payment'] = 'SDHBYR';
						$piutang['payment_date'] = $form['payment_date_to_db'];
					} else {
						$piutang['is_paid'] = 'N';
						$piutang['status_payment'] = 'BLMBYR';
						$piutang['payment_date'] = '0000-00-00';
					}

					$piutang['payment_date'] = $form['payment_date'];
					$piutang['status_input'] = $form['status_input'];
					$piutang['status_payment'] = $form['status_payment'];
					$piutang['date_created'] = new CDbExpression('NOW()');
					$piutang['created_by'] = FHelper::GetUserName($userid_actor);

					//echo "<pre>".print_r($piutang)."</pre>";
					//exit();

					$piutang->save();

					$idpiutang = $piutang->getPrimaryKey();

					$Criteria = new CDbCriteria();
					$Criteria->condition = 'piutang_id = :idpiutang';
					$Criteria->params = array(':idpiutang' => $idpiutang);

					$piutang = Piutang::model()->find($Criteria);

					$form = new PiutangForm();
					$form['tgl'] = $piutang['tgl'];
					$form['faktur_no'] = $piutang['faktur_no'];
					$form['so_no'] = $piutang['so_no'];
					$form['nama_piutang'] = $piutang['nama_piutang'];
					$form['no_telpon'] = $piutang['no_telpon'];
					$form['alamat'] = $piutang['alamat'];
					$form['total'] = $piutang['total'];
					$form['note'] = $piutang['note'];
					$form['is_paid'] = $piutang['is_paid'];
					$form['payment_date'] = $piutang['payment_date'];
					$form['status_input'] = $piutang['status_input'];
					$form['status_payment'] = $piutang['status_payment'];
					$form['date_created'] = $piutang['date_created'];
					$form['created_by'] = $piutang['created_by'];
					$form['date_update'] = $piutang['date_update'];
					$form['update_by'] = $piutang['update_by'];

					//AuditLog
					$data = "$piutang[piutang_id], $piutang[tgl], $piutang[faktur_no], $piutang[so_no], $piutang[nama_piutang], ".
							"$piutang[no_telpon], $piutang[alamat], $piutang[total], $piutang[note], $piutang[is_paid], ".
							"$piutang[payment_date], $piutang[status_input], $piutang[status_payment], $piutang[date_created], ".
							"$piutang[created_by], $piutang[date_update], $piutang[update_by], $piutang[version]";

					FAudit::add('KEUANGANPIUTANG', 'Add', FHelper::GetUserName($userid_actor), $data);

					$bread_crumb_list =
					'<li>Keuangan</li>'.
					'<li>></li>'.
					'<li><a href="#" onclick="ShowList('.$userid_actor.');">Piutang</a></li>'.
					'<li>></li>'.
					'<li>View Piutang</li>';

					$this->success_message =
					'<div class="notification note-success">'.
					'<a href="#" class="close" title="Close notification">close</a>'.
					'<p><strong>Success notification:</strong> Data faktur '.$piutang['faktur_no'].' tgl '.date("d-m-Y", strtotime($piutang['tgl'])).' berhasil ditambah</p>'.
					'</div>';

					$TheContent = $this->renderPartial(
							'view',
							array(
									'form' => $form,
									'userid_actor' => $userid_actor,
									'idpiutang' => $idpiutang,
									'active_option' => $active_option,
									'menuid' => $menuid
							),
							true
					);
				}
				else
				{
					//form not validated
					Yii::log('not validated', 'info');

					$bread_crumb_list =
					'<li>Keuangan</li>'.
					'<li>></li>'.
					'<li><a href="#" onclick="ShowList('.$userid_actor.');">Piutang</a></li>'.
					'<li>></li>'.
					'<li>Tambah Piutang</li>';

					$TheContent = $this->renderPartial(
							'add',
							array(
									'form' => $form,
									'userid_actor' => $userid_actor,
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
				$Criteria = new CDbCriteria();
				$Criteria->condition = "t.is_del = 'N'";
				$Criteria->limit = 20;

				$piutangs = Piutang::model()->findAll($Criteria);

				$this->layout = 'layout-baru';

				$TheContent = $this->renderPartial(
						'list',
						array(
								'userid_actor' => $userid_actor,
								'piutangs' => $piutangs,
								'menuid' => $menuid
						),
						true
				);
			}

		}
		else
		{
			//show form
			if(FHelper::AllowMenu($menuid, FHelper::GetGroupId($userid_actor), 'write'))
			{
			    Yii::log('do_add not set', 'info');

				$form = new PiutangForm();

				$bread_crumb_list =
				'<li>Keuangan</li>'.
				'<li>></li>'.
				'<li><a href="#" onclick="ShowList('.$userid_actor.');">Piutang</a></li>'.
				'<li>></li>'.
				'<li>Tambah Piutang</li>';

				$TheContent = $this->renderPartial(
						'add',
						array(
								'form' => $form,
								'userid_actor' => $userid_actor,
								'active_option' => $active_option,
								'menuid' => $menuid
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

	public function actionEditPiutang()
	{
	  	$menuid = 29;

		$userid_actor = Yii::app()->request->getParam('userid_actor');
		$do_edit = Yii::app()->request->getParam('do_edit');
		$idpiutang = Yii::app()->request->getParam('idpiutang');

		Yii::log('do_edit = ' . $do_edit, 'info');
		Yii::log('idpiutang = ' . $idpiutang, 'info');

		$active_option = array('N' => 'Tidak', 'Y' => 'Ya');

		if(isset($do_edit))
		{
			if($do_edit == 1)
			{
				//process form
				$form = new PiutangForm();
				$form->attributes = Yii::app()->request->getParam('PiutangForm');
				// "<pre>".print_r($form)."</pre>";
				//exit();

				//Yii::log('edit form[name] = ' . $form['name'], 'info');

				if($form->validate())
				{
					//form validated
					Yii::log('validated', 'info');

					$Criteria = new CDbCriteria();
					$Criteria->condition = 'piutang_id = :idpiutang';
					$Criteria->params = array(':idpiutang' => $idpiutang);

					$piutang = Piutang::model()->find($Criteria);

					$piutang['tgl'] = $form['tgl_to_db'];
					$piutang['faktur_no'] = $form['faktur_no'];
					$piutang['so_no'] = $form['so_no'];
					$piutang['nama_piutang'] = $form['nama_piutang'];
					$piutang['no_telpon'] = $form['no_telpon'];
					$piutang['alamat'] = $form['alamat'];
					$piutang['due_date'] = $form['due_date'];
					$piutang['total'] = $form['total'];
					$piutang['note'] = $form['note'];

					if(!empty($form['is_paid'])) {
						$piutang['is_paid'] = 'Y';
						$piutang['status_payment'] = 'SDHBYR';
						$piutang['payment_date'] = $form['payment_date_to_db'];
					} else {
						$piutang['is_paid'] = 'N';
						$piutang['status_payment'] = 'BLMBYR';
						$piutang['payment_date'] = '0000-00-00';
					}

					$piutang['status_input'] = $form['status_input'];
					$piutang['date_update'] = new CDbExpression('NOW()');
					$piutang['update_by'] = FHelper::GetUserName($userid_actor);
					$piutang['version'] = $piutang['version'] + 1;

					//echo "<pre>".print_r($piutang)."</pre>";
					//exit();

					$piutang->update();

					$piutang = Piutang::model()->find($Criteria);

					$form = new PiutangForm();
					$form['tgl'] = $piutang['tgl'];
					$form['faktur_no'] = $piutang['faktur_no'];
					$form['so_no'] = $piutang['so_no'];
					$form['nama_piutang'] = $piutang['nama_piutang'];
					$form['no_telpon'] = $piutang['no_telpon'];
					$form['alamat'] = $piutang['alamat'];
					$form['total'] = $piutang['total'];
					$form['note'] = $piutang['note'];
					$form['is_paid'] = $piutang['is_paid'];
					$form['payment_date'] = $piutang['payment_date'];
					$form['status_input'] = $piutang['status_input'];
					$form['status_payment'] = $piutang['status_payment'];
					$form['date_created'] = $piutang['date_created'];
					$form['created_by'] = $piutang['created_by'];
					$form['date_update'] = $piutang['date_update'];
					$form['update_by'] = $piutang['update_by'];

					//AuditLog
					$data = "$piutang[piutang_id], $piutang[tgl], $piutang[faktur_no], $piutang[so_no], $piutang[nama_piutang], ".
						"$piutang[no_telpon], $piutang[alamat], $piutang[total], $piutang[note], $piutang[is_paid], ".
						"$piutang[payment_date], $piutang[status_input], $piutang[status_payment], $piutang[date_created], ".
						"$piutang[created_by], $piutang[date_update], $piutang[update_by], $piutang[version]";

					FAudit::add('KEUANGANPIUTANG', 'Edit', FHelper::GetUserName($userid_actor), $data);

					$bread_crumb_list =
					'<li>Keuangan</li>'.
					'<li>></li>'.
					'<li><a href="#" onclick="ShowList('.$userid_actor.');">Piutang</a></li>'.
					'<li>></li>'.
					'<li>View Piutang</li>';

					$this->success_message =
					'<div class="notification note-success">'.
					'<a href="#" class="close" title="Close notification">close</a>'.
					'<p><strong>Success notification:</strong> Data faktur '.$piutang['faktur_no'].' tgl '.date("d-m-Y", strtotime($piutang['tgl'])).' berhasil diupdate</p>'.
					'</div>';

					$TheContent = $this->renderPartial(
							'view',
							array(
									'form' => $form,
									'userid_actor' => $userid_actor,
									'idpiutang' => $idpiutang,
									'active_option' => $active_option,
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
					'<li><a href="#" onclick="ShowList('.$userid_actor.');">Piutang</a></li>'.
					'<li>></li>'.
					'<li>Edit Piutang</li>';

					$TheContent = $this->renderPartial(
							'edit',
							array(
									'form' => $form,
									'userid_actor' => $userid_actor,
									'idpiutang' => $idpiutang,
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
				$Criteria = new CDbCriteria();
				$Criteria->condition = "t.is_del = 'N'";
				//$Criteria->limit = 20;

				$piutangs = Piutang::model()->findAll($Criteria);

				$this->layout = 'layout-baru';

				$TheContent = $this->renderPartial(
						'list',
						array(
								'userid_actor' => $userid_actor,
								'piutangs' => $piutangs,
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

				$Criteria = new CDbCriteria();
				$Criteria->condition = 'piutang_id = :idpiutang';
				$Criteria->params = array(':idpiutang' => $idpiutang);

				Yii::log('idpiutang = ' . $idpiutang, 'info');

				$piutang = Piutang::model()->find($Criteria);

				$form = new PiutangForm();
				$form['tgl'] = $piutang['tgl'];
				$form['faktur_no'] = $piutang['faktur_no'];
				$form['so_no'] = $piutang['so_no'];
				$form['nama_piutang'] = $piutang['nama_piutang'];
				$form['no_telpon'] = $piutang['no_telpon'];
				$form['alamat'] = $piutang['alamat'];
				$form['total'] = $piutang['total'];
				$form['note'] = $piutang['note'];
				$form['is_paid'] = $piutang['is_paid'];
				$form['payment_date'] = $piutang['payment_date'];
				$form['status_input'] = $piutang['status_input'];
				$form['status_payment'] = $piutang['status_payment'];
				$form['date_created'] = $piutang['date_created'];
				$form['created_by'] = $piutang['created_by'];
				$form['date_update'] = $piutang['date_update'];
				$form['update_by'] = $piutang['update_by'];

				$bread_crumb_list =
				'<li>Keuangan</li>'.
				'<li>></li>'.
				'<li><a href="#" onclick="ShowList('.$userid_actor.');">Piutang</a></li>'.
				'<li>></li>'.
				'<li>Edit Piutang</li>';

				$TheContent = $this->renderPartial(
						'edit',
						array(
								'form' => $form,
								'userid_actor' => $userid_actor,
								'idpiutang' => $idpiutang,
								'active_option' => $active_option,
								'menuid' => $menuid
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
	public function actionDeletePiutang()
	{
		$menuid = 29;
		$userid_actor = Yii::app()->request->getParam('userid_actor');

		$allow_delete = FHelper::AllowMenu($menuid, FHelper::GetGroupId($userid_actor), 'delete');
		if ($allow_delete) {

			$idpiutang = Yii::app()->request->getParam('idpiutang');

			$Criteria = new CDbCriteria();
			$Criteria->condition = 'piutang_id = :idpiutang';
			$Criteria->params = array(':idpiutang' => $idpiutang);

			//update record di tabel
			$piutang = Piutang::model()->find($Criteria);
			$piutang['is_del'] = 'Y';
			$piutang->update();

			$this->success_message =
			'<div class="notification note-success">'.
			'<a href="#" class="close" title="Close notification">close</a>'.
			'<p><strong>Success notification:</strong> Data faktur '.$piutang['faktur_no'].' tgl '.$piutang['tgl'].' berhasil dihapus</p>'.
			'</div>';

			//AuditLog
			$data = "$piutang[piutang_id], $piutang[tgl], $piutang[faktur_no], $piutang[so_no], $piutang[nama_piutang], $piutang[total], ".
				    "$piutang[note], $piutang[is_paid], $piutang[payment_date], $piutang[status_input], ".
					"$piutang[status_payment], $piutang[date_created], $piutang[created_by], $piutang[date_update], $piutang[update_by], ". "$piutang[version]";

			FAudit::add('KEUANGANPIUTANG', 'Del', FHelper::GetUserName($userid_actor), $data);

			$this->actionListPiutang();
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
	public function actionSelectionPiutang()
	{
		$menuid = 29;
		$userid_actor = Yii::app()->request->getParam('userid_actor');

		$allow_edit = FHelper::AllowMenu($menuid, FHelper::GetGroupId($userid_actor), 'edit');
		if ($allow_edit) {
			$action_type = Yii::app()->request->getParam('cboActionType');
			$item_list = Yii::app()->request->getParam('chkSelectedItem');
			//echo "action_type = $action_type";
			//echo "<pre>".print_r($item_list)."</pre>";
			//exit();

			$Criteria = new CDbCriteria();
			$Criteria->condition = 'piutang_id = :idpiutang';

			if(!empty($action_type))
			{
				foreach($item_list as $key => $value)
				{
					$Criteria->params = array(':idpiutang' => $value);
					$piutang = Piutang::model()->find($Criteria);

					$piutang['is_del'] = $action_type;
					//echo "<pre>".print_r($piutang)."</pre>";
					//exit();

					$piutang->update();

					//AuditLog
			$data = "$piutang[piutang_id], $piutang[tgl], $piutang[faktur_no], $piutang[so_no], $piutang[nama_piutang], $piutang[total], ".
				    "$piutang[note], $piutang[is_paid], $piutang[payment_date], $piutang[status_input], ".
					"$piutang[status_payment], $piutang[date_created], $piutang[created_by], $piutang[date_update], $piutang[update_by], ". "$piutang[version]";

					FAudit::add('KEUANGANPIUTANG', 'Del', FHelper::GetUserName($userid_actor), $data);
				}

				$this->success_message =
				'Data yang dipilih berhasil diupdate';

				$this->actionListPiutang();
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