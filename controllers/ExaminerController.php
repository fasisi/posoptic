<?php
class ExaminerController extends FController
{
	private $success_message = '';

	public function actionIndex()
	{
	    $menuid = 51;
	    $parentmenuid = 6;

	    $userid_actor = Yii::app()->request->getParam('userid_actor');
	    $this->idlokasi = Yii::app()->request->cookies['idlokasi']->value;

	    $allow_read = FHelper::AllowMenu($menuid, FHelper::GetGroupId($userid_actor), 'read');
	    if ($allow_read) {
			$Criteria = new CDbCriteria();
			$Criteria->condition = "t.is_del = 'N'";

			$examiners = Examiner::model()->with('gender')->findAll($Criteria);

			$TheMenu = FHelper::RenderMenu(0, $userid_actor, $parentmenuid);

			$this->userid_actor = $userid_actor;
			$this->parentmenuid = $parentmenuid;

			$this->bread_crumb_list = '
				<li>Data Master</li>
				<li>></li>
				<li>Examiner</li>';

			$this->layout = 'layout-baru';

			$TheContent = $this->renderPartial(
				'v_list_examiner',
				array(
					'userid_actor' => $userid_actor,
					'examiners' => $examiners,
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

	public function actionListExaminer()
	{
	  	$menuid = 51;
	  	$userid_actor = Yii::app()->request->getParam('userid_actor');

	  	$allow_read = FHelper::AllowMenu($menuid, FHelper::GetGroupId($userid_actor), 'read');
	  	if ($allow_read) {
	  		$Criteria = new CDbCriteria();
	  		$Criteria->condition = "t.is_del = 'N'";

	  		$examiners = Examiner::model()->with('gender')->findAll($Criteria);

	  		$this->layout = 'layout-baru';

	  		$TheContent = $this->renderPartial(
				'v_list_examiner',
				array(
					'userid_actor' => $userid_actor,
					'examiners' => $examiners,
					'menuid' => $menuid
				),
				true
	  		);

	  		$bread_crumb_list =
	  			'<li>Data Master</li>'.
	  			'<li>></li>'.
	  			'<li>Examiner</li>';
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

	public function actionViewExaminer()
	{
		$menuid = 51;
		$userid_actor = Yii::app()->request->getParam('userid_actor');

		$allow_read = FHelper::AllowMenu($menuid, FHelper::GetGroupId($userid_actor), 'read');
		if ($allow_read) {
			$idexaminer = Yii::app()->request->getParam('idexaminer');
			$active_option = array('N' => 'Tidak', 'Y' => 'Ya');

			$Criteria = new CDbCriteria();
			$Criteria->condition = 'examiner_id = :idexaminer';
			$Criteria->params = array(':idexaminer' => $idexaminer);

			$examiner = Examiner::model()->with('gender')->find($Criteria);

			$form = new frmEditExaminer();
			$form['name'] = $examiner['name'];
			$form['address'] = $examiner['address'];
			$form['city'] = $examiner['city'];
			$form['zip'] = $examiner['zip'];
			$form['gender_id'] = $examiner->gender['dsc'];
			$form['mobile'] = $examiner['mobile'];
			$form['email'] = $examiner['email'];
			$form['phone'] = $examiner['phone'];
			$form['fax'] = $examiner['fax'];
			$form['reg_date'] = $examiner['reg_date'];
			$form['is_deact'] = $examiner['is_deact'];
			$form['date_created'] = $examiner['date_created'];
			$form['created_by'] = $examiner['created_by'];
			$form['date_update'] = $examiner['date_update'];
			$form['update_by'] = $examiner['update_by'];

			$bread_crumb_list =
				'<li>Data Master</li>'.
				'<li>></li>'.
				'<li><a href="#" onclick="ShowList('.$userid_actor.');">Examiner</a></li>'.
				'<li>></li>'.
				'<li>View Examiner</li>';

			$TheContent = $this->renderPartial(
				'v_view_examiner',
				array(
					'form' => $form,
					'userid_actor' => $userid_actor,
					'idexaminer' => $idexaminer,
					'active_option' => $active_option,
					'menuid' => $menuid
				),
				true
			);

			//AuditLog
			$data = "$examiner[examiner_id], $examiner[user_id], $examiner[name], $examiner[address], $examiner[city], $examiner[zip], ".$examiner->gender[dsc].", ".
					"$examiner[mobile], $examiner[email], $examiner[phone], $examiner[fax], $examiner[reg_date], $examiner[is_deact], ".
					"$examiner[date_created], $examiner[created_by], $examiner[date_update], $examiner[update_by], $examiner[version]";

			FAudit::add('MASTEREXAMINER', 'View', FHelper::GetUserName($userid_actor), $data);
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

	public function actionAddExaminer()
	{
	  	$menuid = 51;

		$userid_actor = Yii::app()->request->getParam('userid_actor');
		$do_add = Yii::app()->request->getParam('do_add');

		Yii::log('do_add = ' . $do_add, 'info');

		//ambil listData untuk gender
		$Criteria = new CDbCriteria();
		$Criteria->condition = 'is_del = "N" AND is_deact = "N" AND group_id = 2';
		$Criteria->order = 'order_no ASC';
		$mtr_std = mtr_std::model()->findAll($Criteria);
		$gender_list = CHtml::listData($mtr_std, 'mtr_id', 'dsc');

		$active_option = array('N' => 'Tidak', 'Y' => 'Ya');

		if(isset($do_add))
		{
			if($do_add == 1)
			{
				//process form
				$form = new frmEditExaminer();
				$form->attributes = Yii::app()->request->getParam('frmEditExaminer');
				//echo "<pre>".print_r($form->attributes)."</pre>";
				//exit();

				Yii::log('add form[name] = ' . $form['name'], 'info');

				if($form->validate())
				{
					//form validated
					Yii::log('validated', 'info');

					$examiner = new Examiner();

					$examiner['name'] = $form['name'];
					$examiner['address'] = $form['address'];
					$examiner['city'] = $form['city'];
					$examiner['zip'] = $form['zip'];
					$examiner['gender_id'] = $form['gender_id'];
					$examiner['mobile'] = $form['mobile'];
					$examiner['email'] = $form['email'];
					$examiner['phone'] = $form['phone'];
					$examiner['fax'] = $form['fax'];
					$examiner['reg_date'] = $form['reg_date_to_db'];
					$examiner['is_deact'] = $form['is_deact'];
					$examiner['date_created'] = new CDbExpression('NOW()');
					$examiner['created_by'] = FHelper::GetUserName($userid_actor);
					$examiner['is_deact'] = $form['is_deact'];

					//echo "<pre>".print_r($examiner)."</pre>";
					//exit();

					$examiner->save();

					$idexaminer = $examiner->getPrimaryKey();

					$Criteria = new CDbCriteria();
					$Criteria->condition = 'examiner_id = :idexaminer';
					$Criteria->params = array(':idexaminer' => $idexaminer);

					$examiner = Examiner::model()->with('gender')->find($Criteria);

					$form = new frmEditExaminer();
					$form['name'] = $examiner['name'];
					$form['address'] = $examiner['address'];
					$form['city'] = $examiner['city'];
					$form['zip'] = $examiner['zip'];
					$form['gender_id'] = $examiner->gender['code'];
					$form['mobile'] = $examiner['mobile'];
					$form['email'] = $examiner['email'];
					$form['phone'] = $examiner['phone'];
					$form['fax'] = $examiner['fax'];
					$form['reg_date'] = $examiner['reg_date'];
					$form['is_deact'] = $examiner['is_deact'];
					$form['date_created'] = $examiner['date_created'];
					$form['created_by'] = $examiner['created_by'];
					$form['date_update'] = $examiner['date_update'];
					$form['update_by'] = $examiner['update_by'];

					//AuditLog
					$data = "$examiner[examiner_id], $examiner[user_id], $examiner[name], $examiner[address], $examiner[city], $examiner[zip], ".$examiner->gender[dsc].", ".
							"$examiner[mobile], $examiner[email], $examiner[phone], $examiner[fax], $examiner[reg_date], $examiner[is_deact], ".
							"$examiner[date_created], $examiner[created_by], $examiner[date_update], $examiner[update_by], $examiner[version]";

					FAudit::add('MASTEREXAMINER', 'Add', FHelper::GetUserName($userid_actor), $data);

					$bread_crumb_list =
					'<li>Data Master</li>'.
					'<li>></li>'.
					'<li><a href="#" onclick="ShowList('.$userid_actor.');">Examiner</a></li>'.
					'<li>></li>'.
					'<li>View Examiner</li>';

					$this->success_message =
					'<div class="notification note-success">'.
					'<a href="#" class="close" title="Close notification">close</a>'.
					'<p><strong>Success notification:</strong> Data '.$examiner['name'].' berhasil ditambah</p>'.
					'</div>';

					$TheContent = $this->renderPartial(
							'v_view_examiner',
							array(
									'form' => $form,
									'userid_actor' => $userid_actor,
									'idexaminer' => $idexaminer,
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
					'<li><a href="#" onclick="ShowList('.$userid_actor.');">Examiner</a></li>'.
					'<li>></li>'.
					'<li>Tambah Examiner</li>';

					$TheContent = $this->renderPartial(
							'vfrm_addexaminer',
							array(
									'form' => $form,
									'userid_actor' => $userid_actor,
									'active_option' => $active_option,
									'gender_list' => $gender_list,
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

				$examiners = Examiner::model()->with('gender')->findAll($Criteria);

				$this->layout = 'layout-baru';

				$TheContent = $this->renderPartial(
						'v_list_examiner',
						array(
								'userid_actor' => $userid_actor,
								'examiners' => $examiners,
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

				$form = new frmEditExaminer();
				$form['reg_date'] = date("d-m-Y");

				$bread_crumb_list =
				'<li>Data Master</li>'.
				'<li>></li>'.
				'<li><a href="#" onclick="ShowList('.$userid_actor.');">Examiner</a></li>'.
				'<li>></li>'.
				'<li>Tambah Examiner</li>';

				$TheContent = $this->renderPartial(
						'vfrm_addexaminer',
						array(
								'form' => $form,
								'userid_actor' => $userid_actor,
								'active_option' => $active_option,
								'gender_list' => $gender_list,
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

	public function actionEditExaminer()
	{
	  	$menuid = 51;

		$userid_actor = Yii::app()->request->getParam('userid_actor');
		$do_edit = Yii::app()->request->getParam('do_edit');
		$idexaminer = Yii::app()->request->getParam('idexaminer');

		Yii::log('do_edit = ' . $do_edit, 'info');
		Yii::log('idexaminer = ' . $idexaminer, 'info');

		//ambil listData untuk gender
		$Criteria = new CDbCriteria();
		$Criteria->condition = 'is_del = "N" AND is_deact = "N" AND group_id = 2';
		$Criteria->order = 'order_no ASC';
		$mtr_std = mtr_std::model()->findAll($Criteria);
		$gender_list = CHtml::listData($mtr_std, 'mtr_id', 'dsc');

		$active_option = array('N' => 'Tidak', 'Y' => 'Ya');

		if(isset($do_edit))
		{
			if($do_edit == 1)
			{
				//process form
				$form = new frmEditExaminer();
				$form->attributes = Yii::app()->request->getParam('frmEditExaminer');
				// "<pre>".print_r($form)."</pre>";
				//exit();

				Yii::log('edit form[name] = ' . $form['name'], 'info');

				if($form->validate())
				{
					//form validated
					Yii::log('validated', 'info');

					$Criteria = new CDbCriteria();
					$Criteria->condition = 'examiner_id = :idexaminer';
					$Criteria->params = array(':idexaminer' => $idexaminer);

					$examiner = Examiner::model()->find($Criteria);

					$examiner['name'] = $form['name'];
					$examiner['address'] = $form['address'];
					$examiner['city'] = $form['city'];
					$examiner['zip'] = $form['zip'];
					$examiner['gender_id'] = $form['gender_id'];
					$examiner['mobile'] = $form['mobile'];
					$examiner['email'] = $form['email'];
					$examiner['phone'] = $form['phone'];
					$examiner['fax'] = $form['fax'];
					$examiner['reg_date'] = $form['reg_date_to_db'];
					$examiner['is_deact'] = $form['is_deact'];
					$examiner['date_update'] = new CDbExpression('NOW()');
					$examiner['update_by'] = FHelper::GetUserName($userid_actor);
					$examiner['is_deact'] = $form['is_deact'];
					$examiner['version'] = $examiner['version'] + 1;

					//echo "<pre>".print_r($examiner)."</pre>";
					//exit();

					$examiner->update();

					$examiner = Examiner::model()->with('gender')->find($Criteria);

					$form = new frmEditExaminer();
					$form['name'] = $examiner['name'];
					$form['address'] = $examiner['address'];
					$form['city'] = $examiner['city'];
					$form['zip'] = $examiner['zip'];
					$form['gender_id'] = $examiner->gender['code'];
					$form['mobile'] = $examiner['mobile'];
					$form['email'] = $examiner['email'];
					$form['phone'] = $examiner['phone'];
					$form['fax'] = $examiner['fax'];
					$form['reg_date'] = $examiner['reg_date'];
					$form['is_deact'] = $examiner['is_deact'];
					$form['date_created'] = $examiner['date_created'];
					$form['created_by'] = $examiner['created_by'];
					$form['date_update'] = $examiner['date_update'];
					$form['update_by'] = $examiner['update_by'];

					//AuditLog
					$data = "$examiner[examiner_id], $examiner[user_id], $examiner[name], $examiner[address], $examiner[city], $examiner[zip], ".$examiner->gender[dsc].", ".
							"$examiner[mobile], $examiner[email], $examiner[phone], $examiner[fax], $examiner[reg_date], $examiner[is_deact], ".
							"$examiner[date_created], $examiner[created_by], $examiner[date_update], $examiner[update_by], $examiner[version]";

					FAudit::add('MASTEREXAMINER', 'Edit', FHelper::GetUserName($userid_actor), $data);

					$bread_crumb_list =
					'<li>Data Master</li>'.
					'<li>></li>'.
					'<li><a href="#" onclick="ShowList('.$userid_actor.');">Examiner</a></li>'.
					'<li>></li>'.
					'<li>View Examiner</li>';

					$this->success_message =
					'<div class="notification note-success">'.
					'<a href="#" class="close" title="Close notification">close</a>'.
					'<p><strong>Success notification:</strong> Data '.$examiner['name'].' berhasil diupdate</p>'.
					'</div>';

					$TheContent = $this->renderPartial(
							'v_view_examiner',
							array(
									'form' => $form,
									'userid_actor' => $userid_actor,
									'idexaminer' => $idexaminer,
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
					'<li><a href="#" onclick="ShowList('.$userid_actor.');">Examiner</a></li>'.
					'<li>></li>'.
					'<li>Edit Examiner</li>';

					$TheContent = $this->renderPartial(
							'vfrm_editexaminer',
							array(
									'form' => $form,
									'userid_actor' => $userid_actor,
									'idexaminer' => $idexaminer,
									'active_option' => $active_option,
									'gender_list' => $gender_list,
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

				$examiners = Examiner::model()->with('gender')->findAll($Criteria);

				$this->layout = 'layout-baru';

				$TheContent = $this->renderPartial(
						'v_list_examiner',
						array(
								'userid_actor' => $userid_actor,
								'examiners' => $examiners,
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
				$Criteria->condition = 'examiner_id = :idexaminer';
				$Criteria->params = array(':idexaminer' => $idexaminer);

				Yii::log('idexaminer = ' . $idexaminer, 'info');

				$examiner = Examiner::model()->find($Criteria);

				$form = new frmEditExaminer();
				$form['name'] = $examiner['name'];
				$form['address'] = $examiner['address'];
				$form['city'] = $examiner['city'];
				$form['zip'] = $examiner['zip'];
				$form['gender_id'] = $examiner['gender_id'];
				$form['mobile'] = $examiner['mobile'];
				$form['email'] = $examiner['email'];
				$form['phone'] = $examiner['phone'];
				$form['fax'] = $examiner['fax'];
				$form['reg_date'] = date("d-m-Y", strtotime($examiner['reg_date']));
				$form['is_deact'] = $examiner['is_deact'];
				$form['date_created'] = $examiner['date_created'];
				$form['created_by'] = $examiner['created_by'];
				$form['date_update'] = $examiner['date_update'];
				$form['update_by'] = $examiner['update_by'];

				$bread_crumb_list =
				'<li>Data Master</li>'.
				'<li>></li>'.
				'<li><a href="#" onclick="ShowList('.$userid_actor.');">Examiner</a></li>'.
				'<li>></li>'.
				'<li>Edit Examiner</li>';

				$TheContent = $this->renderPartial(
						'vfrm_editexaminer',
						array(
								'form' => $form,
								'userid_actor' => $userid_actor,
								'idexaminer' => $idexaminer,
								'active_option' => $active_option,
								'gender_list' => $gender_list,
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
	public function actionDeleteExaminer()
	{
	  	$menuid = 51;
		$userid_actor = Yii::app()->request->getParam('userid_actor');

	  	$allow_delete = FHelper::AllowMenu($menuid, FHelper::GetGroupId($userid_actor), 'delete');
	  	if ($allow_delete) {
			$idexaminer = Yii::app()->request->getParam('idexaminer');

			$Criteria = new CDbCriteria();
			$Criteria->condition = 'examiner_id = :idexaminer';
			$Criteria->params = array(':idexaminer' => $idexaminer);

			//update record di tabel
			$examiner = Examiner::model()->find($Criteria);
			$examiner['is_del'] = 'Y';
			$examiner->update();

			$this->success_message =
			'<div class="notification note-success">'.
			'<a href="#" class="close" title="Close notification">close</a>'.
			'<p><strong>Success notification:</strong> Data '.$examiner['name'].' berhasil dihapus</p>'.
			'</div>';

			//AuditLog
			$data = "$examiner[examiner_id], $examiner[user_id], $examiner[name], $examiner[address], $examiner[city], $examiner[zip], ".$examiner->gender[dsc].", ".
					"$examiner[mobile], $examiner[email], $examiner[phone], $examiner[fax], $examiner[reg_date], $examiner[is_deact], ".
					"$examiner[date_created], $examiner[created_by], $examiner[date_update], $examiner[update_by], $examiner[version]";

			FAudit::add('MASTEREXAMINER', 'Del', FHelper::GetUserName($userid_actor), $data);

			$this->actionListExaminer();
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
	public function actionSelectionExaminer()
	{
		$menuid = 51;
		$userid_actor = Yii::app()->request->getParam('userid_actor');

		$allow_edit = FHelper::AllowMenu($menuid, FHelper::GetGroupId($userid_actor), 'edit');
		if ($allow_edit) {
			$action_type = Yii::app()->request->getParam('cboActionType');
			$item_list = Yii::app()->request->getParam('chkSelectedItem');
			//echo "action_type = $action_type";
			//echo "<pre>".print_r($item_list)."</pre>";
			//exit();

			$Criteria = new CDbCriteria();
			$Criteria->condition = 'examiner_id = :idexaminer';

			if(!empty($action_type))
			{
				foreach($item_list as $key => $value)
				{
					$Criteria->params = array(':idexaminer' => $value);
					$examiner = Examiner::model()->find($Criteria);

					$examiner['is_deact'] = $action_type;
					//echo "<pre>".print_r($examiner)."</pre>";
					//exit();

					$examiner->update();

					//AuditLog
					$data = "$examiner[examiner_id], $examiner[user_id], $examiner[name], $examiner[address], $examiner[city], $examiner[zip], ".$examiner->gender[dsc].", ".
							"$examiner[mobile], $examiner[email], $examiner[phone], $examiner[fax], $examiner[reg_date], $examiner[is_deact], ".
							"$examiner[date_created], $examiner[created_by], $examiner[date_update], $examiner[update_by], $examiner[version]";

					FAudit::add('MASTEREXAMINER', 'Edit', FHelper::GetUserName($userid_actor), $data);
				}

				$this->success_message =
				'Data yang dipilih berhasil diupdate';

				$this->actionListExaminer();
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