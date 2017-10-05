<?php
class CoaController extends FController
{
	private $success_message = '';

	public function actionIndex()
	{
		$menuid = 38;
		$parentmenuid = 6;

		$userid_actor = Yii::app()->request->getParam('userid_actor');
		$this->idlokasi = Yii::app()->request->cookies['idlokasi']->value;

		$allow_read = FHelper::AllowMenu($menuid, FHelper::GetGroupId($userid_actor), 'read');
		if ($allow_read) {
			$Criteria = new CDbCriteria();
			$Criteria->condition = "t.is_del = 'N'";
			$Criteria->order = "code ASC";
			//$Criteria->limit = 20;

			$coas = Coa::model()->findAll($Criteria);

			$TheMenu = FHelper::RenderMenu(0, $userid_actor, $parentmenuid);

			$this->userid_actor = $userid_actor;
			$this->parentmenuid = $parentmenuid;

			$this->bread_crumb_list = '
				<li>Data Master</li>
				<li>></li>
				<li>COA</li>';

	   		$this->layout = 'layout-baru';

			$TheContent = $this->renderPartial(
				'list',
				array(
					'userid_actor' => $userid_actor,
					'coas' => $coas,
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

	public function actionListCOA()
	{
		$menuid = 38;
		$userid_actor = Yii::app()->request->getParam('userid_actor');

		$allow_read = FHelper::AllowMenu($menuid, FHelper::GetGroupId($userid_actor), 'read');
		if ($allow_read) {
			$Criteria = new CDbCriteria();
			$Criteria->condition = "t.is_del = 'N'";
			$Criteria->order = "code ASC";
			//$Criteria->limit = 20;

			$coas = Coa::model()->findAll($Criteria);

			$this->layout = 'layout-baru';

			$TheContent = $this->renderPartial(
				'list',
				array(
					'userid_actor' => $userid_actor,
					'coas' => $coas,
					'menuid' => $menuid
				),
				true
			);

			$bread_crumb_list =
				'<li>Data Master</li>'.
				'<li>></li>'.
				'<li>COA</li>';

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

	public function actionViewCOA()
	{
	  	$menuid = 38;
		$userid_actor = Yii::app()->request->getParam('userid_actor');

	  	$allow_read = FHelper::AllowMenu($menuid, FHelper::GetGroupId($userid_actor), 'read');
	  	if ($allow_read) {
			$idcoa = Yii::app()->request->getParam('idcoa');

			$Criteria = new CDbCriteria();
			$Criteria->condition = 'coa_id = :idcoa';
			$Criteria->params = array(':idcoa' => $idcoa);

			$coa = Coa::model()->find($Criteria);

			$form = new CoaForm();
			$form['code'] = $coa['code'];
			$form['title'] = $coa['title'];
			$form['starting_debit'] = number_format($coa['starting_debit'], 0, '.', '.');
			$form['starting_credit'] = number_format($coa['starting_credit'], 0, '.', '.');
			$form['is_deact'] = $coa['is_deact'];
			$form['date_created'] = $coa['date_created'];
			$form['created_by'] = $coa['created_by'];
			$form['date_update'] = $coa['date_update'];
			$form['update_by'] = $coa['update_by'];

			$bread_crumb_list =
				'<li>Data Master</li>'.
				'<li>></li>'.
				'<li><a href="#" onclick="ShowList('.$userid_actor.');">COA</a></li>'.
				'<li>></li>'.
				'<li>View COA</li>';

			$TheContent = $this->renderPartial(
				'view',
				array(
					'form' => $form,
					'userid_actor' => $userid_actor,
					'idcoa' => $idcoa,
					'active_option' => $active_option,
					'menuid' => $menuid
				),
				true
			);

			//AuditLog
			$data = "$coa[coa_id], $coa[code], $coa[title], $coa[starting_debit], $coa[starting_credit], $coa[is_deact], ".
					"$coa[date_created], $coa[created_by], $coa[date_update], $coa[update_by], $coa[version]";

			FAudit::add('MASTERCOA', 'View', FHelper::GetUserName($userid_actor), $data);
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

	public function actionAddCOA()
	{
		$menuid = 38;

		$userid_actor = Yii::app()->request->getParam('userid_actor');
		$do_add = Yii::app()->request->getParam('do_add');

		Yii::log('do_add = ' . $do_add, 'info');

		$status_option = array('N' => 'Aktif', 'Y' => 'Tdk Aktif');

		if(isset($do_add))
		{
			if($do_add == 1)
			{
				//process form
				$form = new CoaForm();
				$form->attributes = Yii::app()->request->getParam('CoaForm');
				//echo "<pre>".print_r($form->attributes)."</pre>";
				//exit();

				//Yii::log('add form[name] = ' . $form['name'], 'info');

				if($form->validate())
				{
					//form validated
					Yii::log('validated', 'info');

					$coa = new Coa();
					$coa['code'] = $form['code'];
					$coa['title'] = $form['title'];
					$coa['starting_debit'] = number_format($form['starting_debit'], 0, '', '');
					$coa['starting_credit'] = number_format($form['starting_credit'], 0, '', '');
					$coa['is_deact'] = $form['is_deact'];
					$coa['date_created'] = new CDbExpression('NOW()');
					$coa['created_by'] = FHelper::GetUserName($userid_actor);

					//echo "<pre>".print_r($coa)."</pre>";
					//exit();

					$coa->save();

					$idcoa = $coa->getPrimaryKey();

					$Criteria = new CDbCriteria();
					$Criteria->condition = 'coa_id = :idcoa';
					$Criteria->params = array(':idcoa' => $idcoa);

					$coa = Coa::model()->find($Criteria);

					$form = new CoaForm();
					$form['code'] = $coa['code'];
					$form['title'] = $coa['title'];
					$form['starting_debit'] = number_format($coa['starting_debit'], 0, '.', '.');
					$form['starting_credit'] = number_format($coa['starting_credit'], 0, '.', '.');
					$form['is_deact'] = $coa['is_deact'];
					$form['date_created'] = $coa['date_created'];
					$form['created_by'] = $coa['created_by'];
					$form['date_update'] = $coa['date_update'];
					$form['update_by'] = $coa['update_by'];

					//AuditLog
					$data = "$coa[coa_id], $coa[code], $coa[title], $coa[starting_debit], $coa[starting_credit], $coa[is_deact], ".
							"$coa[date_created], $coa[created_by], $coa[date_update], $coa[update_by], $coa[version]";

					FAudit::add('MASTERCOA', 'Add', FHelper::GetUserName($userid_actor), $data);

					$bread_crumb_list =
					'<li>Data Master</li>'.
					'<li>></li>'.
					'<li><a href="#" onclick="ShowList('.$userid_actor.');">COA</a></li>'.
					'<li>></li>'.
					'<li>View COA</li>';

					$this->success_message =
					'<div class="notification note-success">'.
					'<a href="#" class="close" title="Close notification">close</a>'.
					'<p><strong>Success notification:</strong> Data '.$coa['code'].' berhasil ditambah</p>'.
					'</div>';

					$TheContent = $this->renderPartial(
						'view',
						array(
							'form' => $form,
							'userid_actor' => $userid_actor,
							'idcoa' => $idcoa,
							'status_option' => $status_option,
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
					'<li><a href="#" onclick="ShowList('.$userid_actor.');">COA</a></li>'.
					'<li>></li>'.
					'<li>Tambah COA</li>';

					$TheContent = $this->renderPartial(
						'add',
						array(
							'form' => $form,
							'userid_actor' => $userid_actor,
							'status_option' => $status_option,
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

				$coas = Coa::model()->findAll($Criteria);

				$this->layout = 'layout-baru';

				$TheContent = $this->renderPartial(
					'list',
					array(
						'userid_actor' => $userid_actor,
						'coas' => $coas,
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

				$form = new CoaForm();

				$bread_crumb_list =
				'<li>Data Master</li>'.
				'<li>></li>'.
				'<li><a href="#" onclick="ShowList('.$userid_actor.');">COA</a></li>'.
				'<li>></li>'.
				'<li>Tambah COA</li>';

				$TheContent = $this->renderPartial(
					'add',
					array(
						'form' => $form,
						'userid_actor' => $userid_actor,
						'status_option' => $status_option,
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

	public function actionEditCOA()
	{
	  	$menuid = 38;

		$userid_actor = Yii::app()->request->getParam('userid_actor');
		$do_edit = Yii::app()->request->getParam('do_edit');
		$idcoa = Yii::app()->request->getParam('idcoa');

		Yii::log('do_edit = ' . $do_edit, 'info');
		Yii::log('idcoa = ' . $idcoa, 'info');

		$status_option = array('N' => 'Aktif', 'Y' => 'Tdk Aktif');

		if(isset($do_edit))
		{
			if($do_edit == 1)
			{
				//process form
				$form = new CoaForm();
				$form->attributes = Yii::app()->request->getParam('CoaForm');
				//"<pre>".print_r($form)."</pre>";
				//exit();

				//Yii::log('edit form[name] = ' . $form['name'], 'info');

				if($form->validate())
				{
					//form validated
					Yii::log('validated', 'info');

					$Criteria = new CDbCriteria();
					$Criteria->condition = 'coa_id = :idcoa';
					$Criteria->params = array(':idcoa' => $idcoa);

					$coa = Coa::model()->find($Criteria);
					$coa['code'] = $form['code'];
					$coa['title'] = $form['title'];
					$coa['starting_debit'] = number_format($form['starting_debit'], 0, '', '');
					$coa['starting_credit'] = number_format($form['starting_credit'], 0, '', '');
					$coa['is_deact'] = $form['is_deact'];
					$coa['date_update'] = new CDbExpression('NOW()');
					$coa['update_by'] = FHelper::GetUserName($userid_actor);
					$coa['version'] = $coa['version'] + 1;

					//echo "<pre>".print_r($coa)."</pre>";
					//exit();

					$coa->update();

					$coa = Coa::model()->find($Criteria);

					$form = new CoaForm();
					$form['code'] = $coa['code'];
					$form['title'] = $coa['title'];
					$form['starting_debit'] = number_format($coa['starting_debit'], 0, '.', '.');
					$form['starting_credit'] = number_format($coa['starting_credit'], 0, '.', '.');
					$form['is_deact'] = $coa['is_deact'];
					$form['date_created'] = $coa['date_created'];
					$form['created_by'] = $coa['created_by'];
					$form['date_update'] = $coa['date_update'];
					$form['update_by'] = $coa['update_by'];

					//AuditLog
					$data = "$coa[coa_id], $coa[code], $coa[title], $coa[starting_debit], $coa[starting_credit], $coa[is_deact], ".
							"$coa[date_created], $coa[created_by], $coa[date_update], $coa[update_by], $coa[version]";

					FAudit::add('MASTERCOA', 'Edit', FHelper::GetUserName($userid_actor), $data);

					$bread_crumb_list =
					'<li>Data Master</li>'.
					'<li>></li>'.
					'<li><a href="#" onclick="ShowList('.$userid_actor.');">COA</a></li>'.
					'<li>></li>'.
					'<li>View COA</li>';

					$this->success_message =
					'<div class="notification note-success">'.
					'<a href="#" class="close" title="Close notification">close</a>'.
					'<p><strong>Success notification:</strong> Data '.$coa['code'].' berhasil diupdate</p>'.
					'</div>';

					$TheContent = $this->renderPartial(
						'view',
						array(
							'form' => $form,
							'userid_actor' => $userid_actor,
							'idcoa' => $idcoa,
							'status_option' => $status_option,
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
					'<li><a href="#" onclick="ShowList('.$userid_actor.');">COA</a></li>'.
					'<li>></li>'.
					'<li>Edit COA</li>';

					$TheContent = $this->renderPartial(
						'edit',
						array(
							'form' => $form,
							'userid_actor' => $userid_actor,
							'idcoa' => $idcoa,
							'status_option' => $status_option,
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

				$coas = Coa::model()->findAll($Criteria);

				$this->layout = 'layout-baru';

				$TheContent = $this->renderPartial(
					'list',
					array(
						'userid_actor' => $userid_actor,
						'coas' => $coas,
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
				$Criteria->condition = 'coa_id = :idcoa';
				$Criteria->params = array(':idcoa' => $idcoa);

				Yii::log('idcoa = ' . $idcoa, 'info');

				$coa = Coa::model()->find($Criteria);

				$form = new CoaForm();
				$form['code'] = $coa['code'];
				$form['title'] = $coa['title'];
				$form['starting_debit'] = number_format($coa['starting_debit'], 0, '', '');
				$form['starting_credit'] = number_format($coa['starting_credit'], 0, '', '');
				$form['is_deact'] = $coa['is_deact'];
				$form['date_created'] = $coa['date_created'];
				$form['created_by'] = $coa['created_by'];
				$form['date_update'] = $coa['date_update'];
				$form['update_by'] = $coa['update_by'];

				$bread_crumb_list =
				'<li>Data Master</li>'.
				'<li>></li>'.
				'<li><a href="#" onclick="ShowList('.$userid_actor.');">COA</a></li>'.
				'<li>></li>'.
				'<li>Edit COA</li>';

				$TheContent = $this->renderPartial(
					'edit',
					array(
						'form' => $form,
						'userid_actor' => $userid_actor,
						'idcoa' => $idcoa,
						'status_option' => $status_option,
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
	public function actionDeleteCOA()
	{
		$menuid = 38;
		$userid_actor = Yii::app()->request->getParam('userid_actor');

		$allow_delete = FHelper::AllowMenu($menuid, FHelper::GetGroupId($userid_actor), 'delete');
		if ($allow_delete) {
			$idcoa = Yii::app()->request->getParam('idcoa');

			$Criteria = new CDbCriteria();
			$Criteria->condition = 'coa_id = :idcoa';
			$Criteria->params = array(':idcoa' => $idcoa);

			//update record di tabel
			$coa = Coa::model()->find($Criteria);
			$coa['is_del'] = 'Y';
			$coa->update();

			$this->success_message =
			'<div class="notification note-success">'.
			'<a href="#" class="close" title="Close notification">close</a>'.
			'<p><strong>Success notification:</strong> Data '.$coa['code'].' berhasil dihapus</p>'.
			'</div>';

			//AuditLog
			$data = "$coa[coa_id], $coa[code], $coa[title], $coa[starting_debit], $coa[starting_credit], $coa[is_deact], ".
					"$coa[date_created], $coa[created_by], $coa[date_update], $coa[update_by], $coa[version]";

			FAudit::add('MASTERCOA', 'Del', FHelper::GetUserName($userid_actor), $data);

			$this->actionListCOA();
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
	public function actionSelectionCOA()
	{
		$menuid = 38;
		$userid_actor = Yii::app()->request->getParam('userid_actor');

		$allow_edit = FHelper::AllowMenu($menuid, FHelper::GetGroupId($userid_actor), 'edit');
		if ($allow_edit) {
			$action_type = Yii::app()->request->getParam('cboActionType');
			$item_list = Yii::app()->request->getParam('chkSelectedItem');
			//echo "action_type = $action_type";
			//echo "<pre>".print_r($item_list)."</pre>";
			//exit();

			$Criteria = new CDbCriteria();
			$Criteria->condition = 'coa_id = :idcoa';

			if(!empty($action_type))
			{
				foreach($item_list as $key => $value)
				{
					$Criteria->params = array(':idcoa' => $value);
					$coa = Coa::model()->find($Criteria);

					$coa['is_deact'] = $action_type;
					//echo "<pre>".print_r($coa)."</pre>";
					//exit();

					$coa->update();

					//AuditLog
					$data = "$coa[coa_id], $coa[code], $coa[title], $coa[starting_debit], $coa[starting_credit], $coa[is_deact], ".
							"$coa[date_created], $coa[created_by], $coa[date_update], $coa[update_by], $coa[version]";

					FAudit::add('MASTERCOA', 'Edit', FHelper::GetUserName($userid_actor), $data);
				}

				$this->success_message =
				'Data yang dipilih berhasil diupdate';

				$this->actionListCOA();
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