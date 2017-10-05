<?php
class BeritaController extends FController
{
	private $success_message = '';

	public function actionIndex()
	{
		$menuid = 68;
		$parentmenuid = 1;

		$userid_actor = Yii::app()->request->getParam('userid_actor');
		$this->idlokasi = Yii::app()->request->cookies['idlokasi']->value;

		$allow_read = FHelper::AllowMenu($menuid, FHelper::GetGroupId($userid_actor), 'read');
		if ($allow_read) {
			$Criteria = new CDbCriteria();
			$Criteria->condition = "t.is_del = 'N'";
			$Criteria->order = "date_updated DESC";
			//$Criteria->limit = 20;

			$beritas = Berita::model()->findAll($Criteria);

			$TheMenu = FHelper::RenderMenu(0, $userid_actor, $parentmenuid);

			$this->userid_actor = $userid_actor;
			$this->parentmenuid = $parentmenuid;

			$this->bread_crumb_list = '
				<li>Setting</li>
				<li>></li>
				<li>Berita</li>';

	   		$this->layout = 'layout-baru';

			$TheContent = $this->renderPartial(
				'list',
				array(
					'userid_actor' => $userid_actor,
					'beritas' => $beritas,
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

	public function actionListBerita()
	{
		$menuid = 68;
		$userid_actor = Yii::app()->request->getParam('userid_actor');

		$allow_read = FHelper::AllowMenu($menuid, FHelper::GetGroupId($userid_actor), 'read');
		if ($allow_read) {
			$Criteria = new CDbCriteria();
			$Criteria->condition = "t.is_del = 'N'";
			$Criteria->order = "date_updated DESC";
			//$Criteria->limit = 20;

			$beritas = Berita::model()->findAll($Criteria);

			$this->layout = 'layout-baru';

			$TheContent = $this->renderPartial(
				'list',
				array(
					'userid_actor' => $userid_actor,
					'beritas' => $beritas,
					'menuid' => $menuid
				),
				true
			);

			$bread_crumb_list =
				'<li>Setting</li>'.
				'<li>></li>'.
				'<li>Berita</li>';

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

	public function actionViewBerita()
	{
	  	$menuid = 68;
		$userid_actor = Yii::app()->request->getParam('userid_actor');

	  	$allow_read = FHelper::AllowMenu($menuid, FHelper::GetGroupId($userid_actor), 'read');
	  	if ($allow_read) {
			$idberita = Yii::app()->request->getParam('idberita');

			$Criteria = new CDbCriteria();
			$Criteria->condition = 'berita_id = :idberita';
			$Criteria->params = array(':idberita' => $idberita);

			$berita = Berita::model()->find($Criteria);

			$form = new BeritaForm();
			$form['judul'] = $berita['judul'];
			$form['isi'] = $berita['isi'];
			$form['tgl'] = $berita['tgl'];
			$form['prioritas'] = $berita['prioritas'];
			$form['is_pub'] = $berita['is_pub'];
			$form['date_created'] = $berita['date_created'];
			$form['created_by'] = $berita['created_by'];
			$form['date_updated'] = $berita['date_updated'];
			$form['updated_by'] = $berita['updated_by'];

			$bread_crumb_list =
				'<li>Setting</li>'.
				'<li>></li>'.
				'<li><a href="#" onclick="ShowList('.$userid_actor.');">Berita</a></li>'.
				'<li>></li>'.
				'<li>View Berita</li>';

			$TheContent = $this->renderPartial(
				'view',
				array(
					'form' => $form,
					'userid_actor' => $userid_actor,
					'idberita' => $idberita,
					'active_option' => $active_option,
					'menuid' => $menuid
				),
				true
			);

			//AuditLog
			$data = "$berita[berita_id], $berita[judul], $berita[isi], $berita[tgl], $berita[prioritas], $berita[is_pub], ".
					"$berita[date_created], $berita[created_by], $berita[date_updated], $berita[updated_by], $berita[version]";

			FAudit::add('SETTINGBERITA', 'View', FHelper::GetUserName($userid_actor), $data);
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

	public function actionAddBerita()
	{
		$menuid = 68;

		$userid_actor = Yii::app()->request->getParam('userid_actor');
		$do_add = Yii::app()->request->getParam('do_add');

		Yii::log('do_add = ' . $do_add, 'info');

		$active_option = array('N' => 'Tidak', 'Y' => 'Ya');
		$prioritas_option = array('T' => 'Tinggi', 'N' => 'Normal', 'R' => 'Rendah');

		if(isset($do_add))
		{
			if($do_add == 1)
			{
				//process form
				$form = new BeritaForm();
				$form->attributes = Yii::app()->request->getParam('BeritaForm');
				//echo "<pre>".print_r($form->attributes)."</pre>";
				//exit();

				//Yii::log('add form[name] = ' . $form['name'], 'info');

				if($form->validate())
				{
					//form validated
					Yii::log('validated', 'info');

					$berita = new Berita();

					$berita['judul'] = $form['judul'];
					$berita['isi'] = $form['isi'];
					$berita['tgl'] = $form['tgl_to_db'];
					$berita['prioritas'] = $form['prioritas'];
					$berita['is_pub'] = $form['is_pub'];
					$berita['date_created'] = new CDbExpression('NOW()');
					$berita['created_by'] = FHelper::GetUserName($userid_actor);
					$berita['date_updated'] = new CDbExpression('NOW()');
					$berita['updated_by'] = FHelper::GetUserName($userid_actor);

					//echo "<pre>".print_r($berita)."</pre>";
					//exit();

					$berita->save();

					$idberita = $berita->getPrimaryKey();

					$Criteria = new CDbCriteria();
					$Criteria->condition = 'berita_id = :idberita';
					$Criteria->params = array(':idberita' => $idberita);

					$berita = Berita::model()->find($Criteria);

					$form = new BeritaForm();
					$form['judul'] = $berita['judul'];
					$form['isi'] = $berita['isi'];
					$form['tgl'] = $berita['tgl'];
					$form['prioritas'] = $berita['prioritas'];
					$form['is_pub'] = $berita['is_pub'];
					$form['date_created'] = $berita['date_created'];
					$form['created_by'] = $berita['created_by'];
					$form['date_updated'] = $berita['date_updated'];
					$form['updated_by'] = $berita['updated_by'];

					//AuditLog
					$data = "$berita[berita_id], $berita[judul], $berita[isi], $berita[tgl], $berita[prioritas], $berita[is_pub], ".
							"$berita[date_created], $berita[created_by], $berita[date_updated], $berita[updated_by], 1";

					FAudit::add('SETTINGBERITA', 'Add', FHelper::GetUserName($userid_actor), $data);

					$bread_crumb_list =
					'<li>Setting</li>'.
					'<li>></li>'.
					'<li><a href="#" onclick="ShowList('.$userid_actor.');">Berita</a></li>'.
					'<li>></li>'.
					'<li>View Berita</li>';

					$this->success_message =
					'<div class="notification note-success">'.
					'<a href="#" class="close" title="Close notification">close</a>'.
					'<p><strong>Success notification:</strong> Data berita '.$berita['judul'].' berhasil ditambah</p>'.
					'</div>';

					$TheContent = $this->renderPartial(
						'view',
						array(
							'form' => $form,
							'userid_actor' => $userid_actor,
							'idberita' => $idberita,
							'active_option' => $active_option,
							'prioritas_option' => $prioritas_option,
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
					'<li>Setting</li>'.
					'<li>></li>'.
					'<li><a href="#" onclick="ShowList('.$userid_actor.');">Berita</a></li>'.
					'<li>></li>'.
					'<li>Tambah Berita</li>';

					$TheContent = $this->renderPartial(
						'add',
						array(
							'form' => $form,
							'userid_actor' => $userid_actor,
							'active_option' => $active_option,
							'prioritas_option' => $prioritas_option,
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

				$beritas = Berita::model()->findAll($Criteria);

				$this->layout = 'layout-baru';

				$TheContent = $this->renderPartial(
					'list',
					array(
						'userid_actor' => $userid_actor,
						'beritas' => $beritas,
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
				
				$form = new BeritaForm();

				$bread_crumb_list =
				'<li>Setting</li>'.
				'<li>></li>'.
				'<li><a href="#" onclick="ShowList('.$userid_actor.');">Berita</a></li>'.
				'<li>></li>'.
				'<li>Tambah Berita</li>';

				$TheContent = $this->renderPartial(
					'add',
					array(
						'form' => $form,
						'userid_actor' => $userid_actor,
						'active_option' => $active_option,
						'prioritas_option' => $prioritas_option,
						'menuid' => $menuid,
						
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

	public function actionEditBerita()
	{
	  	$menuid = 68;

		$userid_actor = Yii::app()->request->getParam('userid_actor');
		$do_edit = Yii::app()->request->getParam('do_edit');
		$idberita = Yii::app()->request->getParam('idberita');

		Yii::log('do_edit = ' . $do_edit, 'info');
		Yii::log('idberita = ' . $idberita, 'info');

		$active_option = array('N' => 'Tidak', 'Y' => 'Ya');
		$prioritas_option = array('T' => 'Tinggi', 'N' => 'Normal', 'R' => 'Rendah');

		if(isset($do_edit))
		{
			if($do_edit == 1)
			{
				//process form
				$form = new BeritaForm();
				$form->attributes = Yii::app()->request->getParam('BeritaForm');
				// "<pre>".print_r($form)."</pre>";
				//exit();

				//Yii::log('edit form[name] = ' . $form['name'], 'info');

				if($form->validate())
				{
					//form validated
					Yii::log('validated', 'info');

					$Criteria = new CDbCriteria();
					$Criteria->condition = 'berita_id = :idberita';
					$Criteria->params = array(':idberita' => $idberita);

					$berita = Berita::model()->find($Criteria);

					$berita['judul'] = $form['judul'];
					$berita['isi'] = $form['isi'];
					$berita['tgl'] = $form['tgl_to_db'];
					$berita['prioritas'] = $form['prioritas'];
					$berita['is_pub'] = $form['is_pub'];
					$berita['date_updated'] = new CDbExpression('NOW()');
					$berita['updated_by'] = FHelper::GetUserName($userid_actor);
					$berita['version'] = $berita['version'] + 1;

					//echo "<pre>".print_r($berita)."</pre>";
					//exit();

					$berita->update();

					$berita = Berita::model()->find($Criteria);

					$form = new BeritaForm();
					$form['judul'] = $berita['judul'];
					$form['isi'] = $berita['isi'];
					$form['tgl'] = $berita['tgl'];
					$form['prioritas'] = $berita['prioritas'];
					$form['is_pub'] = $berita['is_pub'];
					$form['date_created'] = $berita['date_created'];
					$form['created_by'] = $berita['created_by'];
					$form['date_updated'] = $berita['date_updated'];
					$form['updated_by'] = $berita['updated_by'];

					//AuditLog
					$data = "$berita[berita_id], $berita[judul], $berita[isi], $berita[tgl], $berita[prioritas], $berita[is_pub], ".
							"$berita[date_created], $berita[created_by], $berita[date_updated], $berita[updated_by], $berita[version]";

					FAudit::add('SETTINGBERITA', 'Edit', FHelper::GetUserName($userid_actor), $data);

					$bread_crumb_list =
					'<li>Setting</li>'.
					'<li>></li>'.
					'<li><a href="#" onclick="ShowList('.$userid_actor.');">Berita</a></li>'.
					'<li>></li>'.
					'<li>View Berita</li>';

					$this->success_message =
					'<div class="notification note-success">'.
					'<a href="#" class="close" title="Close notification">close</a>'.
					'<p><strong>Success notification:</strong> Data berita '.$berita['judul'].' berhasil diupdate</p>'.
					'</div>';

					$TheContent = $this->renderPartial(
						'view',
						array(
							'form' => $form,
							'userid_actor' => $userid_actor,
							'idberita' => $idberita,
							'active_option' => $active_option,
							'prioritas_option' => $prioritas_option,
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
					'<li><a href="#" onclick="ShowList('.$userid_actor.');">Berita</a></li>'.
					'<li>></li>'.
					'<li>Edit Berita</li>';

					$TheContent = $this->renderPartial(
						'edit',
						array(
							'form' => $form,
							'userid_actor' => $userid_actor,
							'idberita' => $idberita,
							'active_option' => $active_option,
							'prioritas_option' => $prioritas_option,
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

				$beritas = Berita::model()->findAll($Criteria);

				$this->layout = 'layout-baru';

				$TheContent = $this->renderPartial(
					'list',
					array(
						'userid_actor' => $userid_actor,
						'beritas' => $beritas,
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
				$Criteria->condition = 'berita_id = :idberita';
				$Criteria->params = array(':idberita' => $idberita);

				Yii::log('idberita = ' . $idberita, 'info');

				$berita = Berita::model()->find($Criteria);

				$form = new BeritaForm();
				$form['judul'] = $berita['judul'];
				$form['isi'] = $berita['isi'];
				$form['tgl'] = $berita['tgl'];
				$form['prioritas'] = $berita['prioritas'];
				$form['is_pub'] = $berita['is_pub'];
				$form['date_created'] = $berita['date_created'];
				$form['created_by'] = $berita['created_by'];
				$form['date_updated'] = $berita['date_updated'];
				$form['updated_by'] = $berita['updated_by'];

				$bread_crumb_list =
				'<li>Setting</li>'.
				'<li>></li>'.
				'<li><a href="#" onclick="ShowList('.$userid_actor.');">Berita</a></li>'.
				'<li>></li>'.
				'<li>Edit Berita</li>';

				$TheContent = $this->renderPartial(
					'edit',
					array(
						'form' => $form,
						'userid_actor' => $userid_actor,
						'idberita' => $idberita,
						'active_option' => $active_option,
						'prioritas_option' => $prioritas_option,
						'menuid' => $menuid,
							
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
	public function actionDeleteBerita()
	{
		$menuid = 68;
		$userid_actor = Yii::app()->request->getParam('userid_actor');

		$allow_delete = FHelper::AllowMenu($menuid, FHelper::GetGroupId($userid_actor), 'delete');
		if ($allow_delete) {
			$idberita = Yii::app()->request->getParam('idberita');

			$Criteria = new CDbCriteria();
			$Criteria->condition = 'berita_id = :idberita';
			$Criteria->params = array(':idberita' => $idberita);

			//update record di tabel
			$berita = Berita::model()->find($Criteria);
			$berita['is_del'] = 'Y';
			$berita->update();

			$this->success_message =
			'<div class="notification note-success">'.
			'<a href="#" class="close" title="Close notification">close</a>'.
			'<p><strong>Success notification:</strong> Data berita '.$berita['judul'].' berhasil dihapus</p>'.
			'</div>';

			//AuditLog
			$data = "$berita[berita_id], $berita[judul], $berita[isi], $berita[tgl], $berita[prioritas], $berita[is_pub], ".
					"$berita[date_created], $berita[created_by], $berita[date_updated], $berita[updated_by], $berita[version]";

			FAudit::add('SETTINGBERITA', 'Del', FHelper::GetUserName($userid_actor), $data);

			$this->actionListBerita();
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
	public function actionSelectionBerita()
	{
		$menuid = 68;
		$userid_actor = Yii::app()->request->getParam('userid_actor');

		$allow_edit = FHelper::AllowMenu($menuid, FHelper::GetGroupId($userid_actor), 'edit');
		if ($allow_edit) {
			$action_type = Yii::app()->request->getParam('cboActionType');
			$item_list = Yii::app()->request->getParam('chkSelectedItem');
			//echo "action_type = $action_type";
			//echo "<pre>".print_r($item_list)."</pre>";
			//exit();

			$Criteria = new CDbCriteria();
			$Criteria->condition = 'berita_id = :idberita';

			if(!empty($action_type))
			{
				foreach($item_list as $key => $value)
				{
					$Criteria->params = array(':idberita' => $value);
					$berita = Berita::model()->find($Criteria);

					$berita['is_del'] = $action_type;
					//echo "<pre>".print_r($berita)."</pre>";
					//exit();

					$berita->update();

				//AuditLog
				$data = "$berita[berita_id], $berita[judul], $berita[isi], $berita[tgl], $berita[prioritas], $berita[is_pub], ".
						"$berita[date_created], $berita[created_by], $berita[date_updated], $berita[updated_by], $berita[version]";

					FAudit::add('SETTINGBERITA', 'Del', FHelper::GetUserName($userid_actor), $data);
				}

				$this->success_message =
				'Data yang dipilih berhasil diupdate';

				$this->actionListBerita();
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