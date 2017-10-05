<?php
class BranchController extends FController
{
	private $success_message = '';

	public function actionIndex()
	{
		$menuid = 14;
		$parentmenuid = 6;

		$userid_actor = Yii::app()->request->getParam('userid_actor');
		$this->idlokasi = Yii::app()->request->cookies['idlokasi']->value;

		$allow_read = FHelper::AllowMenu($menuid, FHelper::GetGroupId($userid_actor), 'read');
		if ($allow_read) {
			$Criteria = new CDbCriteria();
			$Criteria->condition = "t.is_del = 'N'";
			$Criteria->order = "branch_type ASC";
			//$Criteria->limit = 20;

			$branches = Branch::model()->findAll($Criteria);

			$TheMenu = FHelper::RenderMenu(0, $userid_actor, $parentmenuid);

			$this->userid_actor = $userid_actor;
			$this->parentmenuid = $parentmenuid;

			$this->bread_crumb_list = '
				<li>Data Master</li>
				<li>></li>
				<li>Cabang</li>';

	   		$this->layout = 'layout-baru';

			$TheContent = $this->renderPartial(
				'list',
				array(
					'userid_actor' => $userid_actor,
					'branches' => $branches,
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

	public function actionListBranch()
	{
		$menuid = 14;
		$userid_actor = Yii::app()->request->getParam('userid_actor');

		$allow_read = FHelper::AllowMenu($menuid, FHelper::GetGroupId($userid_actor), 'read');
		if ($allow_read) {
			$Criteria = new CDbCriteria();
			$Criteria->condition = "t.is_del = 'N'";
			$Criteria->order = "branch_type ASC";
			//$Criteria->limit = 20;

			$branches = Branch::model()->findAll($Criteria);

			$this->layout = 'layout-baru';

			$TheContent = $this->renderPartial(
				'list',
				array(
					'userid_actor' => $userid_actor,
					'branches' => $branches,
					'menuid' => $menuid
				),
				true
			);

			$bread_crumb_list =
				'<li>Data Master</li>'.
				'<li>></li>'.
				'<li>Cabang</li>';

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

	public function actionViewBranch()
	{
	  	$menuid = 14;
		$userid_actor = Yii::app()->request->getParam('userid_actor');

	  	$allow_read = FHelper::AllowMenu($menuid, FHelper::GetGroupId($userid_actor), 'read');
	  	if ($allow_read) {
			$idbranch = Yii::app()->request->getParam('idbranch');

			$Criteria = new CDbCriteria();
			$Criteria->condition = 'branch_id = :idbranch';
			$Criteria->params = array(':idbranch' => $idbranch);

			$branch = Branch::model()->find($Criteria);

			$form = new BranchForm();
			$form['branch_parent_id'] = $branch['branch_parent_id'];
			$form['branch_parent_name'] = FHelper::GetLocationName($branch['branch_parent_id'], true);
			$form['branch_type'] = $branch['branch_type'];
			$form['code'] = $branch['code'];
			$form['initial'] = $branch['initial'];
			$form['name'] = $branch['name'];
			$form['address'] = $branch['address'];
			$form['city'] = $branch['city'];
			$form['zip'] = $branch['zip'];
			$form['country'] = $branch['country'];
			$form['phone'] = $branch['phone'];
			$form['fax'] = $branch['fax'];
			$form['is_deact'] = $branch['is_deact'];
			$form['date_created'] = $branch['date_created'];
			$form['created_by'] = $branch['created_by'];
			$form['date_updated'] = $branch['date_updated'];
			$form['updated_by'] = $branch['updated_by'];

			$bread_crumb_list =
				'<li>Data Master</li>'.
				'<li>></li>'.
				'<li><a href="#" onclick="ShowList('.$userid_actor.');">Cabang</a></li>'.
				'<li>></li>'.
				'<li>View Cabang</li>';

			$TheContent = $this->renderPartial(
				'view',
				array(
					'form' => $form,
					'userid_actor' => $userid_actor,
					'idbranch' => $idbranch,
					'menuid' => $menuid
				),
				true
			);

			//AuditLog
			$data = "$branch[branch_id], $branch[branch_type], $branch[code], $branch[initial], $branch[name], ".
					"$branch[address], $branch[city], $branch[zip], $branch[country], $branch[phone], $branch[fax], $branch[is_deact], ".
					"$branch[date_created], $branch[created_by], $branch[date_updated], $branch[updated_by], $branch[version]";

			FAudit::add('MASTERCABANG', 'View', FHelper::GetUserName($userid_actor), $data);
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

	public function actionAddBranch()
	{
		$menuid = 14;

		$userid_actor = Yii::app()->request->getParam('userid_actor');
		$do_add = Yii::app()->request->getParam('do_add');

		Yii::log('do_add = ' . $do_add, 'info');

		$branchtype_list = array('1' => 'Kantor Pusat', '2' => 'Wilayah', '3' => 'Cabang/Toko');
		//print_r($branchtype_list); exit();

		$Criteria = new CDbCriteria();
		$Criteria->condition = 'branch_type < :branchtype AND is_deact = "N" AND is_del = "N"';
		$Criteria->params = array(':branchtype' => '3');
		$Criteria->order = "branch_type ASC";

		$branch = Branch::model()->findAll($Criteria);

		$branchparent_list = CHtml::listData($branch, 'branch_id', 'name');
		$temp = array ('0' => '-- Pilih Nama Cabang Induk/Kosongkan --');

		$branchparent_list = $temp + $branchparent_list;
		//print_r($branchparent_list); exit();

		$status_option = array('N' => 'Aktif', 'Y' => 'Tdk Aktif');

		if(isset($do_add))
		{
			if($do_add == 1)
			{
				//process form
				$form = new BranchForm();
				$form->attributes = Yii::app()->request->getParam('BranchForm');
				//echo "<pre>".print_r($form->attributes)."</pre>";
				//exit();

				//Yii::log('add form[name] = ' . $form['name'], 'info');

				if($form->validate())
				{
					//form validated
					Yii::log('validated', 'info');

					$branch = new Branch();
					$branch['branch_type'] = $form['branch_type'];
					$branch['branch_parent_id'] = $form['branch_parent_id'];
					$branch['code'] = $form['code'];
					$branch['initial'] = $form['initial'];
					$branch['name'] = $form['name'];
					$branch['address'] = $form['address'];
					$branch['city'] = $form['city'];
					$branch['zip'] = $form['zip'];
					$branch['country'] = $form['country'];
					$branch['phone'] = $form['phone'];
					$branch['fax'] = $form['fax'];
					$branch['is_deact'] = $form['is_deact'];
					$branch['date_created'] = new CDbExpression('NOW()');
					$branch['created_by'] = FHelper::GetUserName($userid_actor);

					//echo "<pre>".print_r($branch)."</pre>";
					//exit();

					$branch->save();

					$idbranch = $branch->getPrimaryKey();

					$Criteria = new CDbCriteria();
					$Criteria->condition = 'branch_id = :idbranch';
					$Criteria->params = array(':idbranch' => $idbranch);

					$branch = Branch::model()->find($Criteria);

					$form = new BranchForm();
					$form['branch_type'] = $branch['branch_type'];
					$form['branch_parent_name'] = FHelper::GetLocationName($branch['branch_parent_id'], true);
					$form['code'] = $branch['code'];
					$form['initial'] = $branch['initial'];
					$form['name'] = $branch['name'];
					$form['address'] = $branch['address'];
					$form['city'] = $branch['city'];
					$form['zip'] = $branch['zip'];
					$form['country'] = $branch['country'];
					$form['phone'] = $branch['phone'];
					$form['fax'] = $branch['fax'];
					$form['is_deact'] = $branch['is_deact'];
					$form['date_created'] = $branch['date_created'];
					$form['created_by'] = $branch['created_by'];
					$form['date_updated'] = $branch['date_updated'];
					$form['updated_by'] = $branch['updated_by'];

					//AuditLog
					$data = "$branch[branch_id], $branch[branch_parent_id], $branch[branch_type], $branch[code], $branch[initial], ".					"$branch[name], $branch[address], $branch[city], $branch[zip], $branch[country], $branch[phone], $branch[fax], ".			"$branch[is_deact], $branch[date_created], $branch[created_by], $branch[date_updated], $branch[updated_by], 1";

					FAudit::add('MASTERCABANG', 'Add', FHelper::GetUserName($userid_actor), $data);

					$bread_crumb_list =
					'<li>Data Master</li>'.
					'<li>></li>'.
					'<li><a href="#" onclick="ShowList('.$userid_actor.');">Cabang</a></li>'.
					'<li>></li>'.
					'<li>View Cabang</li>';

					$this->success_message =
					'<div class="notification note-success">'.
					'<a href="#" class="close" title="Close notification">close</a>'.
					'<p><strong>Success notification:</strong> Data cabang '.$branch['name'].' berhasil ditambah</p>'.
					'</div>';

					$TheContent = $this->renderPartial(
						'view',
						array(
							'form' => $form,
							'userid_actor' => $userid_actor,
							'idbranch' => $idbranch,
							'active_option' => $active_option,
							'menuid' => $menuid
						),
						true
					);
				}
				else
				{
					//form not validated
					Yii::log('form not validated', 'info');

					$bread_crumb_list =
					'<li>Data Master</li>'.
					'<li>></li>'.
					'<li><a href="#" onclick="ShowList('.$userid_actor.');">Cabang</a></li>'.
					'<li>></li>'.
					'<li>Tambah Cabang</li>';

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
				Yii::log('back to list', 'info');
				$this->actionListBranch();
			}

		}
		else
		{
			//show form
			if(FHelper::AllowMenu($menuid, FHelper::GetGroupId($userid_actor), 'write'))
			{
			    Yii::log('do_add not set', 'info');
				
				$form = new BranchForm();

				$bread_crumb_list =
				'<li>Data Master</li>'.
				'<li>></li>'.
				'<li><a href="#" onclick="ShowList('.$userid_actor.');">Cabang</a></li>'.
				'<li>></li>'.
				'<li>Tambah Cabang</li>';

				$TheContent = $this->renderPartial(
					'add',
					array(
						'form' => $form,
						'userid_actor' => $userid_actor,
						'menuid' => $menuid,
						'branchtype_list' => $branchtype_list,
						'branchparent_list' => $branchparent_list,
						'status_option' => $status_option
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

	public function actionEditBranch()
	{
	  	$menuid = 14;

		$userid_actor = Yii::app()->request->getParam('userid_actor');
		$do_edit = Yii::app()->request->getParam('do_edit');
		$idbranch = Yii::app()->request->getParam('idbranch');

		Yii::log('do_edit = ' . $do_edit, 'info');
		Yii::log('idbranch = ' . $idbranch, 'info');

		$branchtype_list = array('1' => 'Kantor Pusat', '2' => 'Wilayah', '3' => 'Cabang/Toko');
		//print_r($branchtype_list); exit();

		$Criteria = new CDbCriteria();
		$Criteria->condition = 'branch_type < :branchtype AND is_deact = "N" AND is_del = "N"';
		$Criteria->params = array(':branchtype' => '3');
		$Criteria->order = "branch_type ASC";

		$branch = Branch::model()->findAll($Criteria);

		$branchparent_list = CHtml::listData($branch, 'branch_id', 'name');
		$temp = array ('0' => '-- Pilih Nama Cabang Induk/Kosongkan --');

		$branchparent_list = $temp + $branchparent_list;
		//print_r($branchparent_list); exit();

		$status_option = array('N' => 'Aktif', 'Y' => 'Tdk Aktif');

		if(isset($do_edit))
		{
			if($do_edit == 1)
			{
				//process form
				$form = new BranchForm();
				$form->attributes = Yii::app()->request->getParam('BranchForm');
				// "<pre>".print_r($form)."</pre>";
				//exit();

				//Yii::log('edit form[name] = ' . $form['name'], 'info');

				if($form->validate())
				{
					//form validated
					Yii::log('validated', 'info');

					$Criteria = new CDbCriteria();
					$Criteria->condition = 'branch_id = :idbranch';
					$Criteria->params = array(':idbranch' => $idbranch);

					$branch = Branch::model()->find($Criteria);

					$branch['branch_type'] = $form['branch_type'];
					$branch['branch_parent_id'] = $form['branch_parent_id'];
					$branch['code'] = $form['code'];
					$branch['initial'] = $form['initial'];
					$branch['name'] = $form['name'];
					$branch['address'] = $form['address'];
					$branch['city'] = $form['city'];
					$branch['zip'] = $form['zip'];
					$branch['country'] = $form['country'];
					$branch['phone'] = $form['phone'];
					$branch['fax'] = $form['fax'];
					$branch['is_deact'] = $form['is_deact'];
					$branch['date_updated'] = new CDbExpression('NOW()');
					$branch['updated_by'] = FHelper::GetUserName($userid_actor);
					$branch['version'] = $branch['version'] + 1;

					//echo "<pre>".print_r($branch)."</pre>";
					//exit();

					$branch->update();

					$branch = Branch::model()->find($Criteria);

					$form = new BranchForm();
					$form['branch_type'] = $branch['branch_type'];
					$form['branch_parent_name'] = FHelper::GetLocationName($branch['branch_parent_id'], true);
					$form['code'] = $branch['code'];
					$form['initial'] = $branch['initial'];
					$form['name'] = $branch['name'];
					$form['address'] = $branch['address'];
					$form['city'] = $branch['city'];
					$form['zip'] = $branch['zip'];
					$form['country'] = $branch['country'];
					$form['phone'] = $branch['phone'];
					$form['fax'] = $branch['fax'];
					$form['is_deact'] = $branch['is_deact'];
					$form['date_created'] = $branch['date_created'];
					$form['created_by'] = $branch['created_by'];
					$form['date_updated'] = $branch['date_updated'];
					$form['updated_by'] = $branch['updated_by'];

					//AuditLog
					$data = "$branch[branch_id], $branch[branch_parent_id], $branch[branch_type], $branch[code], $branch[initial], ".					"$branch[name], $branch[address], $branch[city], $branch[zip], $branch[country], $branch[phone], $branch[fax], ".			"$branch[is_deact], $branch[date_created], $branch[created_by], $branch[date_updated], $branch[updated_by], ". 				"$branch[version]";

					FAudit::add('MASTERCABANG', 'Edit', FHelper::GetUserName($userid_actor), $data);

					$bread_crumb_list =
					'<li>Data Master</li>'.
					'<li>></li>'.
					'<li><a href="#" onclick="ShowList('.$userid_actor.');">Cabang</a></li>'.
					'<li>></li>'.
					'<li>View Cabang</li>';

					$this->success_message =
					'<div class="notification note-success">'.
					'<a href="#" class="close" title="Close notification">close</a>'.
					'<p><strong>Success notification:</strong> Data cabang '.$branch['name'].' berhasil diupdate</p>'.
					'</div>';

					$TheContent = $this->renderPartial(
							'view',
							array(
									'form' => $form,
									'userid_actor' => $userid_actor,
									'idbranch' => $idbranch,
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
					'<li><a href="#" onclick="ShowList('.$userid_actor.');">Cabang</a></li>'.
					'<li>></li>'.
					'<li>Edit Cabang</li>';

					$TheContent = $this->renderPartial(
							'edit',
							array(
									'form' => $form,
									'userid_actor' => $userid_actor,
									'idbranch' => $idbranch,
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

				$branches = Branch::model()->findAll($Criteria);

				$this->layout = 'layout-baru';

				$TheContent = $this->renderPartial(
						'list',
						array(
								'userid_actor' => $userid_actor,
								'branches' => $branches,
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
				$Criteria->condition = 'branch_id = :idbranch';
				$Criteria->params = array(':idbranch' => $idbranch);

				Yii::log('idbranch = ' . $idbranch, 'info');

				$branch = Branch::model()->find($Criteria);

				$form = new BranchForm();
				$form['branch_type'] = $branch['branch_type'];
				$form['branch_parent_id'] = $branch['branch_parent_id'];
				$form['code'] = $branch['code'];
				$form['initial'] = $branch['initial'];
				$form['name'] = $branch['name'];
				$form['address'] = $branch['address'];
				$form['city'] = $branch['city'];
				$form['zip'] = $branch['zip'];
				$form['country'] = $branch['country'];
				$form['phone'] = $branch['phone'];
				$form['fax'] = $branch['fax'];
				$form['is_deact'] = $branch['is_deact'];
				$form['date_created'] = $branch['date_created'];
				$form['created_by'] = $branch['created_by'];
				$form['date_updated'] = $branch['date_updated'];
				$form['updated_by'] = $branch['updated_by'];

				//print_r($form);exit();

				$bread_crumb_list =
				'<li>Data Master</li>'.
				'<li>></li>'.
				'<li><a href="#" onclick="ShowList('.$userid_actor.');">Cabang</a></li>'.
				'<li>></li>'.
				'<li>Edit Cabang</li>';

				$TheContent = $this->renderPartial(
						'edit',
						array(
								'form' => $form,
								'userid_actor' => $userid_actor,
								'idbranch' => $idbranch,
								'menuid' => $menuid,
								'branchtype_list' => $branchtype_list,
								'branchparent_list' => $branchparent_list,
								'status_option' => $status_option
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
	public function actionDeleteBranch()
	{
		$menuid = 14;
		$userid_actor = Yii::app()->request->getParam('userid_actor');

		$allow_delete = FHelper::AllowMenu($menuid, FHelper::GetGroupId($userid_actor), 'delete');
		if ($allow_delete) {
			$idbranch = Yii::app()->request->getParam('idbranch');

			$Criteria = new CDbCriteria();
			$Criteria->condition = 'branch_id = :idbranch';
			$Criteria->params = array(':idbranch' => $idbranch);

			//update record di tabel
			$branch = Branch::model()->find($Criteria);
			$branch['is_del'] = 'Y';
			$branch->update();

			$this->success_message =
			'<div class="notification note-success">'.
			'<a href="#" class="close" title="Close notification">close</a>'.
			'<p><strong>Success notification:</strong> Data cabang '.$branch['name'].' berhasil dihapus</p>'.
			'</div>';

			//AuditLog
			$data = "$branch[branch_id], $branch[branch_type], $branch[code], $branch[initial], $branch[name], ".
					"$branch[address], $branch[phone], $branch[fax], $branch[is_deact], ".
					"$branch[date_created], $branch[created_by], $branch[date_updated], $branch[updated_by], $branch[version]";

			FAudit::add('MASTERCABANG', 'Del', FHelper::GetUserName($userid_actor), $data);

			$this->actionListBranch();
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
	public function actionSelectionBranch()
	{
		$menuid = 14;
		$userid_actor = Yii::app()->request->getParam('userid_actor');

		$allow_edit = FHelper::AllowMenu($menuid, FHelper::GetGroupId($userid_actor), 'edit');
		if ($allow_edit) {
			$action_type = Yii::app()->request->getParam('cboActionType');
			$item_list = Yii::app()->request->getParam('chkSelectedItem');
			//echo "action_type = $action_type";
			//echo "<pre>".print_r($item_list)."</pre>";
			//exit();

			$Criteria = new CDbCriteria();
			$Criteria->condition = 'branch_id = :idbranch';

			if(!empty($action_type))
			{
				foreach($item_list as $key => $value)
				{
					$Criteria->params = array(':idbranch' => $value);
					$branch = Branch::model()->find($Criteria);

					$branch['is_del'] = $action_type;
					//echo "<pre>".print_r($branch)."</pre>";
					//exit();

					$branch->update();

				//AuditLog
				$data = "$branch[branch_id], $branch[branch_type], $branch[code], $branch[initial], $branch[name], ".
						"$branch[address], $branch[phone], $branch[fax], $branch[is_deact], ".
						"$branch[date_created], $branch[created_by], $branch[date_updated], $branch[updated_by], $branch[version]";

					FAudit::add('MASTERCABANG', 'Del', FHelper::GetUserName($userid_actor), $data);
				}

				$this->success_message =
				'Data yang dipilih berhasil diupdate';

				$this->actionListBranch();
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