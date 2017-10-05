<?php
class SmstplController extends FController
{
	private $success_message = '';
	private $tbl_id = 'tpl_id';

	public function actionIndex()
	{
		$menuid = 21;
		$parentmenuid = 1;

		$userid_actor = Yii::app()->request->getParam('userid_actor');
		$this->idlokasi = Yii::app()->request->cookies['idlokasi']->value;

		$allow_read = FHelper::AllowMenu($menuid, FHelper::GetGroupId($userid_actor), 'read');
		if ($allow_read) {
			$recs = Smstemplate::model()->findAll();

			$TheMenu = FHelper::RenderMenu(0, $userid_actor, $parentmenuid);

			$this->userid_actor = $userid_actor;
			$this->parentmenuid = $parentmenuid;

			$this->bread_crumb_list = '
				<li>Setting</li>
				<li>></li>
				<li>SMS Template</li>';

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

	public function actionList()
	{
		$menuid = 21;
		$userid_actor = Yii::app()->request->getParam('userid_actor');

		$allow_read = FHelper::AllowMenu($menuid, FHelper::GetGroupId($userid_actor), 'read');
		if ($allow_read) {
			$recs = Smstemplate::model()->findAll();

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
				'<li>Setting</li>'.
				'<li>></li>'.
				'<li>SMS Template</li>';


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

	public function actionView()
	{
	  	$menuid = 21;
	  	$userid_actor = Yii::app()->request->getParam('userid_actor');

	  	$allow_read = FHelper::AllowMenu($menuid, FHelper::GetGroupId($userid_actor), 'read');
	  	if ($allow_read) {
			$id = Yii::app()->request->getParam('id');
			$active_option = array('N' => 'Tidak', 'Y' => 'Ya');

			$Criteria = new CDbCriteria();
			$Criteria->condition = $this->tbl_id." = :id";
			$Criteria->params = array(':id' => $id);

			$rec = Smstemplate::model()->find($Criteria);

			$form = new SmstemplateForm();
			$form['title'] = $rec['title'];
			$form['dsc'] = $rec['dsc'];
			$form['content'] = $rec['content'];
			$form['is_deact'] = $rec['is_deact'];
			$form['date_created'] = $rec['date_created'];
			$form['created_by'] = $rec['created_by'];
			$form['date_update'] = $rec['date_update'];
			$form['update_by'] = $rec['update_by'];

			$bread_crumb_list =
				'<li>Data Master</li>'.
				'<li>></li>'.
				'<li><a href="#" onclick="ShowList('.$userid_actor.');">SMS Template</a></li>'.
				'<li>></li>'.
				'<li>View SMS Template</li>';

			$TheContent = $this->renderPartial(
				'view',
				array(
					'form' => $form,
					'userid_actor' => $userid_actor,
					'id' => $id,
					'active_option' => $active_option,
					'menuid' => $menuid
				),
				true
			);

			//AuditLog
			$data = "$rec[tpl_id], $rec[title], $rec[dsc], $rec[content], $rec[is_deact], ".
					"$rec[date_created], $rec[created_by], $rec[date_update], $rec[update_by], $rec[version]";

			FAudit::add('SETTINGSMSTEMPLATE', 'View', FHelper::GetUserName($userid_actor), $data);
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

	public function actionEdit()
	{
	  	$menuid = 21;

		$userid_actor = Yii::app()->request->getParam('userid_actor');
		$id = Yii::app()->request->getParam('id');
		$do_edit = Yii::app()->request->getParam('do_edit');

		Yii::log('do_edit = ' . $do_edit, 'info');
		Yii::log('id = ' . $id, 'info');

		$active_option = array('N' => 'Tidak', 'Y' => 'Ya');

		if(isset($do_edit))
		{
			if($do_edit == 1)
			{
				//process form
				$form = new SmstemplateForm();
				$form->attributes = Yii::app()->request->getParam('SmstemplateForm');
				// "<pre>".print_r($form)."</pre>";
				//exit();

				Yii::log('edit form[title] = ' . $form['title'], 'info');

				if($form->validate())
				{
					//form validated
					Yii::log('validated', 'info');

					$Criteria = new CDbCriteria();
					$Criteria->condition = $this->tbl_id." = :id";
					$Criteria->params = array(':id' => $id);

					$rec = Smstemplate::model()->find($Criteria);

					$rec['title'] = $form['title'];
					$rec['dsc'] = $form['dsc'];
					$rec['content'] = $form['content'];
					$rec['is_deact'] = $form['is_deact'];
					$rec['date_update'] = new CDbExpression('NOW()');
					$rec['update_by'] = FHelper::GetUserName($userid_actor);
					$rec['is_deact'] = $form['is_deact'];
					$rec['version'] = $rec['version'] + 1;

					//echo "<pre>".print_r($rec)."</pre>";
					//exit();

					$rec->update();

					$rec = Smstemplate::model()->find($Criteria);

					$form = new SmstemplateForm();
					$form['title'] = $rec['title'];
					$form['dsc'] = $rec['dsc'];
					$form['content'] = $rec['content'];
					$form['is_deact'] = $rec['is_deact'];
					$form['date_created'] = $rec['date_created'];
					$form['created_by'] = $rec['created_by'];
					$form['date_update'] = $rec['date_update'];
					$form['update_by'] = $rec['update_by'];

					//AuditLog
					$data = "$rec[tpl_id], $rec[title], $rec[dsc], $rec[content], $rec[is_deact], ".
							"$rec[date_created], $rec[created_by], $rec[date_update], $rec[update_by], $rec[version]";

					FAudit::add('SETTINGSMSTEMPLATE', 'Edit', FHelper::GetUserName($userid_actor), $data);

					$bread_crumb_list =
					'<li>Setting</li>'.
					'<li>></li>'.
					'<li><a href="#" onclick="ShowList('.$userid_actor.');">SMS Template</a></li>'.
					'<li>></li>'.
					'<li>View SMS Template</li>';

					$this->success_message =
					'<div class="notification note-success">'.
					'<a href="#" class="close" title="Close notification">close</a>'.
					'<p><strong>Success notification:</strong> Data '.$rec['title'].' berhasil diupdate</p>'.
					'</div>';

					$TheContent = $this->renderPartial(
							'view',
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
				else
				{
					//form not validated
					$bread_crumb_list =
					'<li>Setting</li>'.
					'<li>></li>'.
					'<li><a href="#" onclick="ShowList('.$userid_actor.');">SMS Template</a></li>'.
					'<li>></li>'.
					'<li>Edit SMS Template</li>';

					$TheContent = $this->renderPartial(
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
				$recs = Smstemplate::model()->findAll();

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
			}

		}
		else
		{
			//show form
			if(FHelper::AllowMenu($menuid, FHelper::GetGroupId($userid_actor), 'edit'))
			{
			    Yii::log('do_edit not set', 'info');

				$Criteria = new CDbCriteria();
				$Criteria->condition = $this->tbl_id." = :id";
				$Criteria->params = array(':id' => $id);

				Yii::log('id = ' . $id, 'info');

				$rec = Smstemplate::model()->find($Criteria);

				$form = new SmstemplateForm();
				$form['title'] = $rec['title'];
				$form['dsc'] = $rec['dsc'];
				$form['content'] = $rec['content'];
				$form['is_deact'] = $rec['is_deact'];
				$form['date_created'] = $rec['date_created'];
				$form['created_by'] = $rec['created_by'];
				$form['date_update'] = $rec['date_update'];
				$form['update_by'] = $rec['update_by'];

				$bread_crumb_list =
				'<li>Setting</li>'.
				'<li>></li>'.
				'<li><a href="#" onclick="ShowList('.$userid_actor.');">SMS Template</a></li>'.
				'<li>></li>'.
				'<li>Edit SMS Template</li>';

				$TheContent = $this->renderPartial(
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

	public function actionSelection()
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
			$Criteria->condition = $this->tbl_id." = :id";

			if(!empty($action_type))
			{
				foreach($item_list as $key => $value)
				{
					$Criteria->params = array(':id' => $value);
					$rec = Smstemplate::model()->find($Criteria);

					$rec['is_deact'] = $action_type;
					//echo "<pre>".print_r($rec)."</pre>";
					//exit();

					$rec->update();

					//AuditLog
					$data = "$rec[tpl_id], $rec[title], $rec[dsc], $rec[content], $rec[is_deact], ".
							"$rec[date_created], $rec[created_by], $rec[date_update], $rec[update_by], $rec[version]";

					FAudit::add('SETTINGSMSTEMPLATE', 'Edit', FHelper::GetUserName($userid_actor), $data);
				}

				$this->success_message =
				'Data yang dipilih berhasil diupdate';

				$this->actionList();
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