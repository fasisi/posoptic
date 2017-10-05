<?php
class VoucherController extends FController
{
	private $success_message = '';

	private function getCOAList()
	{
		$Criteria = new CDbCriteria();
		$Criteria->condition = "t.is_del = 'N' AND is_deact = 'N'";
		$Criteria->order = "code ASC";
		$coas = Coa::model()->findAll($Criteria);

		$coa_list[0] = '-- Pilih COA --';
		foreach($coas as $coa)
		{
			$value = $coa['coa_id'];
			$name = $coa['code'] . ' - ' . $coa['title'] . ' )';

			$coa_list[$value] = $name;
		}

		return $coa_list;
	}


	public function actionIndex()
	{
		$menuid = 24;
		$parentmenuid = 8;

		$userid_actor = Yii::app()->request->getParam('userid_actor');
		$this->idlokasi = Yii::app()->request->cookies['idlokasi']->value;

		$allow_read = FHelper::AllowMenu($menuid, FHelper::GetGroupId($userid_actor), 'read');
		if ($allow_read) {
			$Criteria = new CDbCriteria();
			$Criteria->condition = "t.is_del = 'N'";
			//$Criteria->limit = 20;
			$Criteria->order = "tgl DESC";

			$vouchers = Voucher::model()->findAll($Criteria);

			$TheMenu = FHelper::RenderMenu(0, $userid_actor, $parentmenuid);

			$this->userid_actor = $userid_actor;
			$this->parentmenuid = $parentmenuid;

			$this->bread_crumb_list = '
				<li>Keuangan</li>
				<li>></li>
				<li>Jurnal</li>';

	   		$this->layout = 'layout-baru';

			$TheContent = $this->renderPartial(
				'list',
				array(
					'userid_actor' => $userid_actor,
					'vouchers' => $vouchers,
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

	public function actionListVoucher()
	{
		$menuid = 24;
		$userid_actor = Yii::app()->request->getParam('userid_actor');

		$allow_read = FHelper::AllowMenu($menuid, FHelper::GetGroupId($userid_actor), 'read');

		if ($allow_read) {
			$Criteria = new CDbCriteria();
			$Criteria->condition = "t.is_del = 'N'";
			//$Criteria->limit = 20;
			$Criteria->order = "tgl DESC";

			$vouchers = Voucher::model()->findAll($Criteria);

			$this->layout = 'layout-baru';

			$TheContent = $this->renderPartial(
				'list',
				array(
					'userid_actor' => $userid_actor,
					'vouchers' => $vouchers,
					'menuid' => $menuid
				),
				true
			);

			$bread_crumb_list =
				'<li>Keuangan</li>'.
				'<li>></li>'.
				'<li>Jurnal</li>';

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

	public function actionViewVoucher()
	{
	  	$menuid = 24;
	  	$userid_actor = Yii::app()->request->getParam('userid_actor');

	  	$allow_read = FHelper::AllowMenu($menuid, FHelper::GetGroupId($userid_actor), 'read');
	  	if ($allow_read) {
			$idvoucher = Yii::app()->request->getParam('idvoucher');
			$active_option = array('N' => 'Tidak', 'Y' => 'Ya');

			$Criteria = new CDbCriteria();
			$Criteria->condition = 'voucher_id = :idvoucher';
			$Criteria->params = array(':idvoucher' => $idvoucher);

			$voucher = Voucher::model()->find($Criteria);

			$form = new VoucherForm();
			$form['tgl'] = date('d-m-Y', strtotime($voucher['tgl']));
			$form['no_voucher'] = $voucher['no_voucher'];
			$form['status_input'] = $voucher['status_input'];
			$form['keterangan'] = $voucher['keterangan'];
			$form['date_created'] = $voucher['date_created'];
			$form['created_by'] = $voucher['created_by'];
			$form['date_update'] = $voucher['date_update'];
			$form['update_by'] = $voucher['update_by'];

			//ambil daftar coa
			$command = Yii::app()->db->createCommand()
			->select('detail.*, coa.code, coa.title')
			->from('fin_voucher_detail detail')
			->join('mtr_coa coa', 'detail.coa_id = coa.coa_id')
			->where('detail.voucher_id = :idvoucher', array(':idvoucher' => $idvoucher));
			$daftar_coa = $command->queryAll();

			$bread_crumb_list =
				'<li>Keuangan</li>'.
				'<li>></li>'.
				'<li><a href="#" onclick="ShowList('.$userid_actor.');">Jurnal</a></li>'.
				'<li>></li>'.
				'<li>View Voucher</li>';

			$TheContent = $this->renderPartial(
				'view',
				array(
					'form' => $form,
					'daftar_coa' => $daftar_coa,
					'userid_actor' => $userid_actor,
					'idvoucher' => $idvoucher,
					'active_option' => $active_option,
					'menuid' => $menuid
				),
				true
			);

			//AuditLog
			$data = "$voucher[voucher_id], $voucher[tgl], $voucher[status_input], $voucher[keterangan], ".
					"$voucher[date_created], $voucher[created_by], $voucher[date_update], $voucher[update_by], ". "$voucher[version]";

			FAudit::add('KEUANGANVOUCHER', 'View', FHelper::GetUserName($userid_actor), $data);
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

	public function actionAddVoucher()
	{
		$menuid = 24;

		$userid_actor = Yii::app()->request->getParam('userid_actor');
		$do_add = Yii::app()->request->getParam('do_add');

		Yii::log('do_add = ' . $do_add, 'info');

		$active_option = array('N' => 'Tidak', 'Y' => 'Ya');

		if(isset($do_add))
		{
			if($do_add == 1)
			{
				//process form
				$form = new VoucherForm();
				$form->attributes = Yii::app()->request->getParam('VoucherForm');
				//echo "<pre>".print_r($form->attributes)."</pre>";
				//exit();

				//Yii::log('add form[name] = ' . $form['name'], 'info');

				if($form->validate())
				{
					//form validated
					Yii::log('validated', 'info');

					$voucher = new Voucher();

					$voucher['tgl'] = $form['tgl_to_db'];
					$voucher['no_voucher'] = $form['no_voucher'];
					$voucher['keterangan'] = $form['keterangan'];
					$voucher['status_input'] = 'OPEN';
					$voucher['date_created'] = new CDbExpression('NOW()');
					$voucher['created_by'] = FHelper::GetUserName($userid_actor);

					//echo "<pre>".print_r($voucher)."</pre>";
					//exit();

					$voucher->save();

					$idvoucher = $voucher->getPrimaryKey();

					$Criteria = new CDbCriteria();
					$Criteria->condition = 'voucher_id = :idvoucher';
					$Criteria->params = array(':idvoucher' => $idvoucher);

					$voucher = Voucher::model()->find($Criteria);

					$form = new VoucherForm();
					$form['tgl'] = $voucher['tgl'];
					$form['no_voucher'] = $voucher['no_voucher'];
					$form['keterangan'] = $voucher['keterangan'];
					$form['status_input'] = $voucher['status_input'];
					$form['date_created'] = $voucher['date_created'];
					$form['created_by'] = $voucher['created_by'];
					$form['date_update'] = $voucher['date_update'];
					$form['update_by'] = $voucher['update_by'];

					//AuditLog
					$data = "$voucher[voucher_id], $voucher[tgl], $voucher[no_voucher], ".
					"$voucher[keterangan], $voucher[status_input], $voucher[date_created], ".
					"$voucher[created_by], $voucher[date_update], $voucher[update_by], 1";

					FAudit::add('KEUANGANVOUCHER', 'Add', FHelper::GetUserName($userid_actor), $data);

					$bread_crumb_list =
					'<li>Keuangan</li>'.
					'<li>></li>'.
					'<li><a href="#" onclick="ShowList('.$userid_actor.');">Jurnal</a></li>'.
					'<li>></li>'.
					'<li>View Voucher</li>';

					$this->success_message =
					'<div class="notification note-success">'.
					'<a href="#" class="close" title="Close notification">close</a>'.
					'<p><strong>Success notification:</strong> Voucher No. '.$voucher['no_voucher'].' berhasil ditambah</p>'.
					'</div>';

					$TheContent = $this->renderPartial(
						'view',
						array(
							'form' => $form,
							'userid_actor' => $userid_actor,
							'idvoucher' => $idvoucher,
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
					'<li><a href="#" onclick="ShowList('.$userid_actor.');">Jurnal</a></li>'.
					'<li>></li>'.
					'<li>Tambah Voucher</li>';

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
				//$Criteria->limit = 20;

				$vouchers = Voucher::model()->findAll($Criteria);

				$this->layout = 'layout-baru';

				$TheContent = $this->renderPartial(
					'list',
					array(
						'userid_actor' => $userid_actor,
						'vouchers' => $vouchers,
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

				$coa_list = $this->getCOAList();

				$form = new VoucherForm();
				$form['tgl'] = date('d-m-Y');

				$bread_crumb_list =
				'<li>Keuangan</li>'.
				'<li>></li>'.
				'<li><a href="#" onclick="ShowList('.$userid_actor.');">Jurnal</a></li>'.
				'<li>></li>'.
				'<li>Tambah Voucher</li>';

				$TheContent = $this->renderPartial(
					'add',
					array(
						'form' => $form,
						'userid_actor' => $userid_actor,
						'active_option' => $active_option,
						'menuid' => $menuid,
						'coa_list' => $coa_list
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
				'notification_message' => $success_message
			)
		);
	}

	public function actionEditVoucher()
	{

		$menuid = 24;

		$userid_actor = Yii::app()->request->getParam('userid_actor');
		$do_edit = Yii::app()->request->getParam('do_edit');
		$idvoucher = Yii::app()->request->getParam('idvoucher');

		Yii::log('do_edit = ' . $do_edit, 'info');
		Yii::log('idvoucher = ' . $idvoucher, 'info');

		$active_option = array('N' => 'Tidak', 'Y' => 'Ya');

		if(isset($do_edit))
		{
			if($do_edit == 1)
			{
				//process form
				$form = new VoucherForm();
				$form->attributes = Yii::app()->request->getParam('VoucherForm');
				// "<pre>".print_r($form)."</pre>";
				//exit();

				//Yii::log('edit form[name] = ' . $form['name'], 'info');

				if($form->validate())
				{
					//form validated
					Yii::log('validated', 'info');

					$Criteria = new CDbCriteria();
					$Criteria->condition = 'voucher_id = :idvoucher';
					$Criteria->params = array(':idvoucher' => $idvoucher);

					$voucher = Voucher::model()->find($Criteria);

					$voucher['tgl'] = date('Y-m-d', strtotime($form['tgl']));
					$voucher['no_voucher'] = $form['no_voucher'];
					$voucher['keterangan'] = $form['keterangan'];
					$voucher['date_update'] = new CDbExpression('NOW()');
					$voucher['update_by'] = FHelper::GetUserName($userid_actor);
					$voucher['version'] = $voucher['version'] + 1;

					//echo "<pre>".print_r($voucher)."</pre>";
					//exit();

					$voucher->update();

					$voucher = Voucher::model()->find($Criteria);

					$form = new VoucherForm();
					$form['tgl'] = $voucher['tgl'];
					$form['no_voucher'] = $voucher['no_voucher'];
					$form['keterangan'] = $voucher['keterangan'];
					$form['status_input'] = $voucher['status_input'];
					$form['date_created'] = $voucher['date_created'];
					$form['created_by'] = $voucher['created_by'];
					$form['date_update'] = $voucher['date_update'];
					$form['update_by'] = $voucher['update_by'];

					//AuditLog
					$data = "$voucher[voucher_id], $voucher[tgl], $voucher[no_voucher], ".
					"$voucher[keterangan], $voucher[status_input], $voucher[date_created], ".
					"$voucher[created_by], $voucher[date_update], $voucher[update_by], $voucher[version]";

					FAudit::add('KEUANGANVOUCHER', 'Edit', FHelper::GetUserName($userid_actor), $data);

					//ambil daftar coa
					$command = Yii::app()->db->createCommand()
					->select('detail.*, coa.code, coa.title')
					->from('fin_voucher_detail detail')
					->join('mtr_coa coa', 'detail.coa_id = coa.coa_id')
					->where('detail.voucher_id = :idvoucher', array(':idvoucher' => $idvoucher));
					$daftar_coa = $command->queryAll();

					$bread_crumb_list =
					'<li>Keuangan</li>'.
					'<li>></li>'.
					'<li><a href="#" onclick="ShowList('.$userid_actor.');">Jurnal</a></li>'.
					'<li>></li>'.
					'<li>View Voucher</li>';

					$this->success_message =
					'<div class="notification note-success">'.
					'<a href="#" class="close" title="Close notification">close</a>'.
					'<p><strong>Success notification:</strong> Voucher No. '.$voucher['no_voucher'].' berhasil diupdate</p>'.
					'</div>';

					$TheContent = $this->renderPartial(
						'view',
						array(
							'form' => $form,
							'daftar_coa' => $daftar_coa,
							'userid_actor' => $userid_actor,
							'idvoucher' => $idvoucher,
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
					'<li><a href="#" onclick="ShowList('.$userid_actor.');">Jurnal</a></li>'.
					'<li>></li>'.
					'<li>Edit Voucher</li>';

					$TheContent = $this->renderPartial(
						'edit',
						array(
							'form' => $form,
							'userid_actor' => $userid_actor,
							'idvoucher' => $idvoucher,
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

				$vouchers = Voucher::model()->findAll($Criteria);

				$this->layout = 'layout-baru';

				$TheContent = $this->renderPartial(
						'list',
						array(
							'userid_actor' => $userid_actor,
							'vouchers' => $vouchers,
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
				$Criteria->condition = 'voucher_id = :idvoucher';
				$Criteria->params = array(':idvoucher' => $idvoucher);

				$voucher = Voucher::model()->find($Criteria);

				Yii::log('idvoucher = ' . $idvoucher, 'info');
				Yii::log('tgl = ' . $voucher['tgl'], 'info');

				$form = new VoucherForm();
				$form['tgl'] = date('d-m-Y', strtotime($voucher['tgl']));
				$form['no_voucher'] = $voucher['no_voucher'];
				$form['status_input'] = $voucher['status_input'];
				$form['keterangan'] = $voucher['keterangan'];
				$form['date_created'] = $voucher['date_created'];
				$form['created_by'] = $voucher['created_by'];
				$form['date_update'] = $voucher['date_update'];
				$form['update_by'] = $voucher['update_by'];

				//mengambil daftar coa pada voucher ini.
				$command = Yii::app()->db->createCommand()
				->select('*, coa.code, coa.title')
				->from('fin_voucher_detail')
				->join('mtr_coa coa', 'fin_voucher_detail.coa_id = coa.coa_id')
				->where(
				'voucher_id = :idvoucher',
				array(
				':idvoucher' => $idvoucher
				));

				$daftar_coa_voucher = $command->queryAll();

				$tabel_coa = $this->renderPartial(
					'v_tabel_coa_voucher',
					array(
						'status_input' => $form['status_input'],
						'daftar_coa_voucher' => $daftar_coa_voucher
					),
					true
				);

				$bread_crumb_list =
				'<li>Keuangan</li>'.
				'<li>></li>'.
				'<li><a href="#" onclick="ShowList('.$userid_actor.');">Jurnal</a></li>'.
				'<li>></li>'.
				'<li>Edit Voucher</li>';

				$TheContent = $this->renderPartial(
					'edit',
					array(
						'form' => $form,
						'userid_actor' => $userid_actor,
						'idvoucher' => $idvoucher,
						'active_option' => $active_option,
						'menuid' => $menuid,
						'tabel_coa' => $tabel_coa
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
	public function actionDeleteVoucher()
	{
		$menuid = 24;
		$userid_actor = Yii::app()->request->getParam('userid_actor');

		$allow_delete = FHelper::AllowMenu($menuid, FHelper::GetGroupId($userid_actor), 'delete');
		if ($allow_delete) {
			$idvoucher = Yii::app()->request->getParam('idvoucher');

			$Criteria = new CDbCriteria();
			$Criteria->condition = 'voucher_id = :idvoucher';
			$Criteria->params = array(':idvoucher' => $idvoucher);

			//update record di tabel
			$voucher = Voucher::model()->find($Criteria);
			$voucher['is_del'] = 'Y';
			$voucher->update();

			$this->success_message =
			'<div class="notification note-success">'.
			'<a href="#" class="close" title="Close notification">close</a>'.
			'<p><strong>Success notification:</strong> Data no. voucher '.$voucher['no_voucher'].' tgl. '.date("d-m-Y", strtotime($voucher['tgl'])).' berhasil dihapus</p>'.
			'</div>';

			//AuditLog
			$data = "$voucher[voucher_id], $voucher[tgl], $voucher[no_voucher], ".
			"$voucher[keterangan], $voucher[status_input], $voucher[date_created], ".
			"$voucher[created_by], $voucher[date_update], $voucher[update_by], $voucher[version]";

			FAudit::add('KEUANGANVOUCHER', 'Del', FHelper::GetUserName($userid_actor), $data);

			$this->actionListVoucher();
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
	public function actionSelectionVoucher()
	{
		$menuid = 24;
		$userid_actor = Yii::app()->request->getParam('userid_actor');

		$allow_edit = FHelper::AllowMenu($menuid, FHelper::GetGroupId($userid_actor), 'edit');
		if ($allow_edit) {
			$action_type = Yii::app()->request->getParam('cboActionType');
			$item_list = Yii::app()->request->getParam('chkSelectedItem');
			//echo "action_type = $action_type";
			//echo "<pre>".print_r($item_list)."</pre>";
			//exit();

			$Criteria = new CDbCriteria();
			$Criteria->condition = 'voucher_id = :idvoucher';

			if(!empty($action_type))
			{
				foreach($item_list as $key => $value)
				{
					$Criteria->params = array(':idvoucher' => $value);
					$voucher = Voucher::model()->find($Criteria);

					$voucher['is_del'] = $action_type;
					//echo "<pre>".print_r($voucher)."</pre>";
					//exit();

					$voucher->update();

					//AuditLog
					$data = "$voucher[voucher_id], $voucher[tgl], $voucher[no_voucher], ".
					"$voucher[keterangan], $voucher[status_input], $voucher[date_created], ".
					"$voucher[created_by], $voucher[date_update], $voucher[update_by], $voucher[version]";

					FAudit::add('KEUANGANVOUCHER', 'Del', FHelper::GetUserName($userid_actor), $data);
				}

				$this->success_message =
				'Data yang dipilih berhasil diupdate';

				$this->actionListVoucher();
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

	/*
	  actionDeleteCoa

	  Deskripsi
	  Fungsi untuk menghapus entry coa dari sebuah voucher.

	  Parameter
	  idvoucher
	    Integer
	  idcoa
	    Integer
	*/
	public function actionDeleteCoa()
	{
	  $idvoucher_detail = Yii::app()->request->getParam('idvoucher_detail');

	  $command = Yii::app()->db->createCommand()
	    ->delete(
	      'fin_voucher_detail',
	      'id = :idvoucher_detail',
	      array(
	        ':idvoucher_detail' => $idvoucher_detail
        )
      );

    echo CJSON::encode(array('status' => 'ok'));
	}

	/*
	  actionAmbilDaftarCoa

	  Deskripsi
	  Fungsi untuk mengambil daftar coa berdasarkan nama.

	  Parameter
	  namacoa
	    String

	  Return
	  Daftar <option> untuk ditambahkan ke <select>. Dibungkus dalam JSON.
	*/
	public function actionAmbilDaftarCoa()
	{
	  $namacoa = Yii::app()->request->getParam('namacoa');

	  $command = Yii::app()->db->createCommand()
	    ->select('*')
	    ->from('mtr_coa')
	    ->where(
	      'code like :namacoa',
	      array(':namacoa' => $namacoa . "%" ));
	  $hasil = $command->queryAll();

	  echo CJSON::encode(array('daftar_coa' => $hasil));
	}

	/*
	  actionTambahCoa

	  Deskripsi
	  Fungsi untuk menambahkan coa ke suatu voucher.

	  Parameter
	  idvoucher
	    Integer
	  idcoa
	    Integer

	  Result
	  Mengembalikan status yang dibungkus dalam json.
	*/
	public function actionTambahCoa()
	{
	  $idvoucher = Yii::app()->request->getParam('idvoucher');
	  $idcoa = Yii::app()->request->getParam('idcoa');
	  $debit = Yii::app()->request->getParam('debit');
	  $credit = Yii::app()->request->getParam('credit');

	  if( strlen($debit) == 0)
	  {
	    $debit = 0;
	  }

	  if( strlen($credit) == 0)
	  {
	    $credit = 0;
	  }

	  $command = Yii::app()->db->createCommand()
	    ->select('*')
	    ->from('fin_voucher_detail')
	    ->where(
	      'voucher_id = :idvoucher AND
	      coa_id = :idcoa',
	      array(
	        ':idvoucher' => $idvoucher,
	        ':idcoa' => $idcoa,
		));
    $hasil = $command->queryAll();

    if( count($hasil) > 0 )
    {
      $status = 'not ok';
      $pesan = 'COA sudah ada dalam voucher';
    }
    else
    {
      Yii::app()->db->createCommand()
        ->insert(
          'fin_voucher_detail',
          array(
            'voucher_id' => $idvoucher,
            'coa_id' => $idcoa,
            'debit' => $debit,
            'credit' => $credit
          )
        );

      $status = 'ok';
      $pesan = 'Berhasil menambahkan COA ke dalam voucher';
    }

    echo CJSON::encode(array('status' => $status, 'pesan' => $pesan));
	}

	/*
	  actionRefreshCoaList

	  Deskripsi
	  Fungsi untuk mengambil daftar entry coa pada suatu voucher

	  Parameter
	  idvoucher
	    Integer

	  Return
	  JSON berisi tabel html daftar coa suatu voucher
	*/
	public function actionRefreshCoaList()
	{
	  $idvoucher = Yii::app()->request->getParam('idvoucher');

	  $command = Yii::app()->db->createCommand()
	    ->select('*')
	    ->from('fin_voucher')
	    ->where(
	      'voucher_id = :idvoucher',
	      array(':idvoucher' => $idvoucher));
	  $voucher = $command->queryRow();

	  //mengambil daftar coa pada voucher ini.
    $command = Yii::app()->db->createCommand()
      ->select('*, coa.code, coa.title')
      ->from('fin_voucher_detail')
      ->join('mtr_coa coa', 'fin_voucher_detail.coa_id = coa.coa_id')
      ->where(
        'voucher_id = :idvoucher',
        array(
          ':idvoucher' => $idvoucher
        ));
    $daftar_coa_voucher = $command->queryAll();

    $html = $this->renderPartial(
      'v_tabel_coa_voucher',
      array(
        'status_input' => $voucher['status_input'],
        'daftar_coa_voucher' => $daftar_coa_voucher
      ),
      true
    );

    echo CJSON::encode(array('html' => $html));
	}

	public function actionPost()
	{
	  $idvoucher = Yii::app()->request->getParam('idvoucher');

	  $command = Yii::app()->db->createCommand()
	    ->update(
	      'fin_voucher',
	      array(
	        'status_input' => 'POST',
	        'date_update' => date('Y-m-d H:i:s')
        ),
        'voucher_id = :idvoucher',
        array(':idvoucher' => $idvoucher)
      );

    if($command == 1)
    {
      $pesan = 'Berhasil POST voucher';

      //salin fin_voucher_detil ke fin_jurnal
      $command = Yii::app()->db->createCommand()
        ->select('*')
        ->from('fin_voucher_detail a')
		->join('fin_voucher b', 'a.voucher_id=b.voucher_id')
        ->where('a.voucher_id = :idvoucher', array(':idvoucher' => $idvoucher));
      $daftar_detil = $command->queryAll();

      foreach($daftar_detil as $detil)
      {
        Yii::app()->db->createCommand()
          ->insert(
            'fin_journal',
            array(
              'journal_date' => $detil['tgl'],
              'debit' => $detil['debit'],
              'credit' => $detil['credit'],
              'coa_id' => $detil['coa_id'],
              'coa_code' => '',
              'ref_id' => $detil['voucher_id'],
              'ref_type' => 90,
              'ref_desc' => ''
            )
          );
      }
    }
    else
    {
      $pesan = 'Gagal POST voucher';
    }

    echo CJSON::encode(array('status' => 'ok', 'pesan' => $pesan));
	}


	public function actionUnpost()
	{
	  $idvoucher = Yii::app()->request->getParam('idvoucher');

	  $command = Yii::app()->db->createCommand()
	    ->update(
	      'fin_voucher',
	      array(
	        'status_input' => 'OPEN',
	        'date_update' => date('Y-m-d H:i:s')
        ),
        'voucher_id = :idvoucher',
        array(':idvoucher' => $idvoucher)
      );

    if($command == 1)
    {
      $pesan = 'Berhasil UNPOST voucher';

      //menghapus entry coa dari fin_journal
      Yii::app()->db->createCommand()
        ->delete(
          'fin_journal',
          'ref_id = :idvoucher AND
          ref_type = 90',
          array(
            ':idvoucher' => $idvoucher
          )
        );
    }
    else
    {
      $pesan = 'Gagal UNPOST voucher';
    }

    echo CJSON::encode(array('status' => 'ok', 'pesan' => $pesan));
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