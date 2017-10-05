<?php
class OpticController extends FController
{
	private $success_message = '';

	public function actionIndex()
	{
		$menuid = 16;
		$parentmenuid = 6;

		$userid_actor = Yii::app()->request->getParam('userid_actor');
		$this->idlokasi = Yii::app()->request->cookies['idlokasi']->value;

		$allow_read = FHelper::AllowMenu($menuid, FHelper::GetGroupId($userid_actor), 'read');
		if ($allow_read) {
			$Criteria = new CDbCriteria();
			$Criteria->condition = "t.is_del = 'N'";
			
			$optics = Optic::model()->findAll($Criteria);

			$TheMenu = FHelper::RenderMenu(0, $userid_actor, $parentmenuid);

			$this->userid_actor = $userid_actor;
			$this->parentmenuid = $parentmenuid;

			$this->bread_crumb_list = '
				<li>Data Master</li>
				<li>></li>
				<li>Kustomer Optik</li>';

	   		$this->layout = 'layout-baru';

			$TheContent = $this->renderPartial(
				'v_list_optic',
				array(
					'userid_actor' => $userid_actor,
					'optics' => $optics,
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
				'v_not_auth',
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

	public function actionListOptic()
	{
		$menuid = 16;
		$userid_actor = Yii::app()->request->getParam('userid_actor');

		$allow_read = FHelper::AllowMenu($menuid, FHelper::GetGroupId($userid_actor), 'read');
		if ($allow_read) {
			$Criteria = new CDbCriteria();
			$Criteria->condition = "t.is_del = 'N'";

			$optics = Optic::model()->findAll($Criteria);

			$this->layout = 'layout-baru';

			$TheContent = $this->renderPartial(
				'v_list_optic',
				array(
					'userid_actor' => $userid_actor,
					'optics' => $optics,
					'menuid' => $menuid
				),
				true
			);

			$bread_crumb_list =
				'<li>Data Master</li>'.
				'<li>></li>'.
				'<li>Kustomer Optik</li>';
		}
		else
		{
			$bread_crumb_list = '
				<li>Not Authorize</li>';

			$this->layout = 'layout-baru';

			$TheContent = $this->renderPartial(
				'v_not_auth',
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

	public function actionViewOptic()
	{
	  	$menuid = 16;
		$userid_actor = Yii::app()->request->getParam('userid_actor');

	  	$allow_read = FHelper::AllowMenu($menuid, FHelper::GetGroupId($userid_actor), 'read');
	  	if ($allow_read) {
			$idoptic = Yii::app()->request->getParam('idoptic');
			$active_option = array('N' => 'Tidak', 'Y' => 'Ya');

			$Criteria = new CDbCriteria();
			$Criteria->condition = 'optic_id = :idoptic';
			$Criteria->params = array(':idoptic' => $idoptic);

			$optic = Optic::model()->find($Criteria);

			$form = new OpticForm();
			$form['name'] = $optic['name'];
			$form['address'] = $optic['address'];
			$form['city'] = $optic['city'];
			$form['zip'] = $optic['zip'];
			$form['contact_person'] = $optic['contact_person'];
			$form['mobile'] = $optic['mobile'];
			$form['email'] = $optic['email'];
			$form['phone'] = $optic['phone'];
			$form['fax'] = $optic['fax'];
			$form['reg_date'] = $optic['reg_date'];
			$form['is_deact'] = $optic['is_deact'];
			$form['date_created'] = $optic['date_created'];
			$form['created_by'] = $optic['created_by'];
			$form['date_update'] = $optic['date_update'];
			$form['update_by'] = $optic['update_by'];

			$bread_crumb_list =
				'<li>Data Master</li>'.
				'<li>></li>'.
				'<li><a href="#" onclick="ShowList('.$userid_actor.');">Kustomer Optik</a></li>'.
				'<li>></li>'.
				'<li>View Kustomer Optik</li>';

			$TheContent = $this->renderPartial(
				'v_view_optic',
				array(
					'form' => $form,
					'userid_actor' => $userid_actor,
					'idoptic' => $idoptic,
					'active_option' => $active_option,
					'menuid' => $menuid
				),
				true
			);

			//AuditLog
			$data = "$optic[optic_id], $optic[name], $optic[address], $optic[city], $optic[zip], $optic[contact_person], ".
					"$optic[mobile], $optic[email], $optic[phone], $optic[fax], $optic[reg_date], $optic[is_deact], ".
					"$optic[date_created], $optic[created_by], $optic[date_update], $optic[update_by], $optic[version]";

			FAudit::add('MASTERCUSTOMEROPTIC', 'View', FHelper::GetUserName($userid_actor), $data);
		}
		else
		{
			$bread_crumb_list = '
				<li>Not Authorize</li>';

			$this->layout = 'layout-baru';

			$TheContent = $this->renderPartial(
				'v_not_auth',
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

	public function actionAddOptic()
	{
		$menuid = 16;

		$userid_actor = Yii::app()->request->getParam('userid_actor');
		$do_add = Yii::app()->request->getParam('do_add');

		Yii::log('do_add = ' . $do_add, 'info');

		$active_option = array('N' => 'Tidak', 'Y' => 'Ya');

		if(isset($do_add))
		{
			if($do_add == 1)
			{
				//process form
				$form = new OpticForm();
				$form->attributes = Yii::app()->request->getParam('OpticForm');
				//echo "<pre>".print_r($form->attributes)."</pre>";
				//exit();

				Yii::log('add form[name] = ' . $form['name'], 'info');

				if($form->validate())
				{
					//form validated
					Yii::log('validated', 'info');

					$optic = new Optic();

					$optic['name'] = $form['name'];
					$optic['address'] = $form['address'];
					$optic['city'] = $form['city'];
					$optic['zip'] = $form['zip'];
					$optic['contact_person'] = $form['contact_person'];
					$optic['mobile'] = $form['mobile'];
					$optic['email'] = $form['email'];
					$optic['phone'] = $form['phone'];
					$optic['fax'] = $form['fax'];
					$optic['reg_date'] = $form['reg_date_to_db'];
					$optic['is_deact'] = $form['is_deact'];
					$optic['date_created'] = new CDbExpression('NOW()');
					$optic['created_by'] = FHelper::GetUserName($userid_actor);
					$optic['is_deact'] = $form['is_deact'];

					//echo "<pre>".print_r($optic)."</pre>";
					//exit();

					$optic->save();

					$idoptic = $optic->getPrimaryKey();

					$Criteria = new CDbCriteria();
					$Criteria->condition = 'optic_id = :idoptic';
					$Criteria->params = array(':idoptic' => $idoptic);

					$optic = Optic::model()->find($Criteria);

					$form = new OpticForm();
					$form['name'] = $optic['name'];
					$form['address'] = $optic['address'];
					$form['city'] = $optic['city'];
					$form['zip'] = $optic['zip'];
					$form['contact_person'] = $optic['contact_person'];
					$form['mobile'] = $optic['mobile'];
					$form['email'] = $optic['email'];
					$form['phone'] = $optic['phone'];
					$form['fax'] = $optic['fax'];
					$form['reg_date'] = $optic['reg_date'];
					$form['is_deact'] = $optic['is_deact'];
					$form['date_created'] = $optic['date_created'];
					$form['created_by'] = $optic['created_by'];
					$form['date_update'] = $optic['date_update'];
					$form['update_by'] = $optic['update_by'];

					//AuditLog
					$data = "$optic[optic_id], $optic[name], $optic[address], $optic[city], $optic[zip], $optic[contact_person], ".
							"$optic[mobile], $optic[email], $optic[phone], $optic[fax], $optic[reg_date], $optic[is_deact], ".
							"$optic[date_created], $optic[created_by], $optic[date_update], $optic[update_by], $optic[version]";

					FAudit::add('MASTERCUSTOMEROPTIC', 'Add', FHelper::GetUserName($userid_actor), $data);

					$bread_crumb_list =
					'<li>Data Master</li>'.
					'<li>></li>'.
					'<li><a href="#" onclick="ShowList('.$userid_actor.');">Kustomer Optik</a></li>'.
					'<li>></li>'.
					'<li>View Kustomer Optik</li>';

					$this->success_message =
					'<div class="notification note-success">'.
					'<a href="#" class="close" title="Close notification">close</a>'.
					'<p><strong>Success notification:</strong> Data '.$optic['name'].' berhasil ditambah</p>'.
					'</div>';

					$TheContent = $this->renderPartial(
							'v_view_optic',
							array(
									'form' => $form,
									'userid_actor' => $userid_actor,
									'idoptic' => $idoptic,
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
					'<li>Data Master</li>'.
					'<li>></li>'.
					'<li><a href="#" onclick="ShowList('.$userid_actor.');">Kustomer Optik</a></li>'.
					'<li>></li>'.
					'<li>Tambah Kustomer Optik</li>';

					$TheContent = $this->renderPartial(
							'vfrm_addoptic',
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

				$optics = Optic::model()->findAll($Criteria);

				$this->layout = 'layout-baru';

				$TheContent = $this->renderPartial(
						'v_list_optic',
						array(
								'userid_actor' => $userid_actor,
								'optics' => $optics,
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

				$form = new OpticForm();
				$form['reg_date'] = date("d-m-Y");

				$bread_crumb_list =
				'<li>Data Master</li>'.
				'<li>></li>'.
				'<li><a href="#" onclick="ShowList('.$userid_actor.');">Kustomer Optik</a></li>'.
				'<li>></li>'.
				'<li>Tambah Kustomer Optik</li>';

				$TheContent = $this->renderPartial(
						'vfrm_addoptic',
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
					'v_not_auth',
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

	public function actionEditOptic()
	{
	  	$menuid = 16;

		$userid_actor = Yii::app()->request->getParam('userid_actor');
		$do_edit = Yii::app()->request->getParam('do_edit');
		$idoptic = Yii::app()->request->getParam('idoptic');

		Yii::log('do_edit = ' . $do_edit, 'info');
		Yii::log('idoptic = ' . $idoptic, 'info');

		$active_option = array('N' => 'Tidak', 'Y' => 'Ya');

		if(isset($do_edit))
		{
			if($do_edit == 1)
			{
				//process form
				$form = new OpticForm();
				$form->attributes = Yii::app()->request->getParam('OpticForm');
				// "<pre>".print_r($form)."</pre>";
				//exit();

				Yii::log('edit form[name] = ' . $form['name'], 'info');

				if($form->validate())
				{
					//form validated
					Yii::log('validated', 'info');

					$Criteria = new CDbCriteria();
					$Criteria->condition = 'optic_id = :idoptic';
					$Criteria->params = array(':idoptic' => $idoptic);

					$optic = Optic::model()->find($Criteria);

					$optic['name'] = $form['name'];
					$optic['address'] = $form['address'];
					$optic['city'] = $form['city'];
					$optic['zip'] = $form['zip'];
					$optic['contact_person'] = $form['contact_person'];
					$optic['mobile'] = $form['mobile'];
					$optic['email'] = $form['email'];
					$optic['phone'] = $form['phone'];
					$optic['fax'] = $form['fax'];
					$optic['reg_date'] = $form['reg_date_to_db'];
					$optic['is_deact'] = $form['is_deact'];
					$optic['date_update'] = new CDbExpression('NOW()');
					$optic['update_by'] = FHelper::GetUserName($userid_actor);
					$optic['is_deact'] = $form['is_deact'];
					$optic['version'] = $optic['version'] + 1;

					//echo "<pre>".print_r($optic)."</pre>";
					//exit();

					$optic->update();

					$optic = Optic::model()->find($Criteria);

					$form = new OpticForm();
					$form['name'] = $optic['name'];
					$form['address'] = $optic['address'];
					$form['city'] = $optic['city'];
					$form['zip'] = $optic['zip'];
					$form['contact_person'] = $optic['contact_person'];
					$form['mobile'] = $optic['mobile'];
					$form['email'] = $optic['email'];
					$form['phone'] = $optic['phone'];
					$form['fax'] = $optic['fax'];
					$form['reg_date'] = $optic['reg_date'];
					$form['is_deact'] = $optic['is_deact'];
					$form['date_created'] = $optic['date_created'];
					$form['created_by'] = $optic['created_by'];
					$form['date_update'] = $optic['date_update'];
					$form['update_by'] = $optic['update_by'];

					//AuditLog
					$data = "$optic[optic_id], $optic[name], $optic[address], $optic[city], $optic[zip], $optic[contact_person], ".
							"$optic[mobile], $optic[email], $optic[phone], $optic[fax], $optic[reg_date], $optic[is_deact], ".
							"$optic[date_created], $optic[created_by], $optic[date_update], $optic[update_by], $optic[version]";

					FAudit::add('MASTERCUSTOMEROPTIC', 'Edit', FHelper::GetUserName($userid_actor), $data);

					$bread_crumb_list =
					'<li>Data Master</li>'.
					'<li>></li>'.
					'<li><a href="#" onclick="ShowList('.$userid_actor.');">Kustomer Optik</a></li>'.
					'<li>></li>'.
					'<li>View Kustomer Optik</li>';

					$this->success_message =
					'<div class="notification note-success">'.
					'<a href="#" class="close" title="Close notification">close</a>'.
					'<p><strong>Success notification:</strong> Data '.$optic['name'].' berhasil diupdate</p>'.
					'</div>';

					$TheContent = $this->renderPartial(
							'v_view_optic',
							array(
									'form' => $form,
									'userid_actor' => $userid_actor,
									'idoptic' => $idoptic,
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
					'<li>Data Master</li>'.
					'<li>></li>'.
					'<li><a href="#" onclick="ShowList('.$userid_actor.');">Kustomer Optik</a></li>'.
					'<li>></li>'.
					'<li>Edit Kustomer Optik</li>';

					$TheContent = $this->renderPartial(
							'vfrm_editoptic',
							array(
									'form' => $form,
									'userid_actor' => $userid_actor,
									'idoptic' => $idoptic,
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

				$optics = Optic::model()->findAll($Criteria);

				$this->layout = 'layout-baru';

				$TheContent = $this->renderPartial(
						'v_list_optic',
						array(
								'userid_actor' => $userid_actor,
								'optics' => $optics,
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
				$Criteria->condition = 'optic_id = :idoptic';
				$Criteria->params = array(':idoptic' => $idoptic);

				Yii::log('idoptic = ' . $idoptic, 'info');

				$optic = Optic::model()->find($Criteria);

				$form = new OpticForm();
				$form['name'] = $optic['name'];
				$form['address'] = $optic['address'];
				$form['city'] = $optic['city'];
				$form['zip'] = $optic['zip'];
				$form['contact_person'] = $optic['contact_person'];
				$form['mobile'] = $optic['mobile'];
				$form['email'] = $optic['email'];
				$form['phone'] = $optic['phone'];
				$form['fax'] = $optic['fax'];
				$form['reg_date'] = date("d-m-Y", strtotime($optic['reg_date']));
				$form['is_deact'] = $optic['is_deact'];
				$form['date_created'] = $optic['date_created'];
				$form['created_by'] = $optic['created_by'];
				$form['date_update'] = $optic['date_update'];
				$form['update_by'] = $optic['update_by'];

				$bread_crumb_list =
				'<li>Data Master</li>'.
				'<li>></li>'.
				'<li><a href="#" onclick="ShowList('.$userid_actor.');">Kustomer Optik</a></li>'.
				'<li>></li>'.
				'<li>Edit Kustomer Optik</li>';

				$TheContent = $this->renderPartial(
						'vfrm_editoptic',
						array(
								'form' => $form,
								'userid_actor' => $userid_actor,
								'idoptic' => $idoptic,
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
					'v_not_auth',
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
	public function actionDeleteOptic()
	{
		$menuid = 16;
		$userid_actor = Yii::app()->request->getParam('userid_actor');

		$allow_delete = FHelper::AllowMenu($menuid, FHelper::GetGroupId($userid_actor), 'delete');
		if ($allow_delete) {
			$idoptic = Yii::app()->request->getParam('idoptic');

			$Criteria = new CDbCriteria();
			$Criteria->condition = 'optic_id = :idoptic';
			$Criteria->params = array(':idoptic' => $idoptic);

			//update record di tabel
			$optic = Optic::model()->find($Criteria);
			$optic['is_del'] = 'Y';
			$optic->update();

			$this->success_message =
			'<div class="notification note-success">'.
			'<a href="#" class="close" title="Close notification">close</a>'.
			'<p><strong>Success notification:</strong> Data '.$optic['name'].' berhasil dihapus</p>'.
			'</div>';

			//AuditLog
			$data = "$optic[optic_id], $optic[name], $optic[address], $optic[city], $optic[zip], $optic[contact_person], ".
					"$optic[mobile], $optic[email], $optic[phone], $optic[fax], $optic[reg_date], $optic[is_deact], ".
					"$optic[date_created], $optic[created_by], $optic[date_update], $optic[update_by], $optic[version]";

			FAudit::add('MASTERCUSTOMEROPTIC', 'Del', FHelper::GetUserName($userid_actor), $data);

			$this->actionListOptic();
		}
		else
		{
			$bread_crumb_list = '
				<li>Not Authorize</li>';

			$this->layout = 'layout-baru';

			$TheContent = $this->renderPartial(
				'v_not_auth',
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
	public function actionSelectionOptic()
	{
		$menuid = 16;
		$userid_actor = Yii::app()->request->getParam('userid_actor');

		$allow_edit = FHelper::AllowMenu($menuid, FHelper::GetGroupId($userid_actor), 'edit');
		if ($allow_edit) {
			$action_type = Yii::app()->request->getParam('cboActionType');
			$item_list = Yii::app()->request->getParam('chkSelectedItem');
			//echo "action_type = $action_type";
			//echo "<pre>".print_r($item_list)."</pre>";
			//exit();

			$Criteria = new CDbCriteria();
			$Criteria->condition = 'optic_id = :idoptic';

			if(!empty($action_type))
			{
				foreach($item_list as $key => $value)
				{
					$Criteria->params = array(':idoptic' => $value);
					$optic = Optic::model()->find($Criteria);

					$optic['is_deact'] = $action_type;
					//echo "<pre>".print_r($optic)."</pre>";
					//exit();

					$optic->update();

					//AuditLog
					$data = "$optic[optic_id], $optic[name], $optic[address], $optic[city], $optic[zip], $optic[contact_person], ".
							"$optic[mobile], $optic[email], $optic[phone], $optic[fax], $optic[reg_date], $optic[is_deact], ".
							"$optic[date_created], $optic[created_by], $optic[date_update], $optic[update_by], $optic[version]";

					FAudit::add('MASTERCUSTOMEROPTIC', 'Edit', FHelper::GetUserName($userid_actor), $data);
				}

				$this->success_message =
				'Data yang dipilih berhasil diupdate';

				$this->actionListOptic();
			}
			else
			{
				$bread_crumb_list = '
					<li>Not Authorize</li>';

				$this->layout = 'layout-baru';

				$TheContent = $this->renderPartial(
					'v_not_auth',
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