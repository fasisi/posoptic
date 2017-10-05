<?php

class SmssendController extends FController
{
	private $success_message = '';
	private $tbl_id = 'sms_id';
	
	public function actions()
	{
	  
	}
	
	public function actionGosms()
	{
	  echo $this->getSMS();
	}
	
	public static function getSMS()
	{
	  $Criteria = new CDbCriteria();
	  $Criteria->condition = 'is_proc = "N"';
	  $sms_send_list = Smssend::model()->findAll($Criteria);
	  
	  $Criteria2 = new CDbCriteria();
	  foreach($sms_send_list as $sms_send_row)
	  {
	    $Criteria2->condition = 'sms_id = :sms_id';
	    $Criteria2->params = array(':sms_id' => $sms_send_row['sms_id']);
	    $sms_send = Smssend::model()->find($Criteria2);
	    $sms_send['is_proc'] = 'Y';
	    $sms_send['date_proc'] = new CDbExpression('NOW()');
	    $sms_send->update();
	    
	    Yii::log($sms_send_row->dest_name . ', ' . $sms_send_row->dest_mobile . ', ' . $sms_send_row->content, 'info');
	  }
	  
	  return CJSON::encode(array('hasil' => $sms_send_list));
	}
	
	public function actionIndex()
	{
		$menuid = 40;
		$parentmenuid = 7;

		$userid_actor = Yii::app()->request->getParam('userid_actor');
		$this->idlokasi = Yii::app()->request->cookies['idlokasi']->value;
		
		$allow_read = FHelper::AllowMenu($menuid, FHelper::GetGroupId($userid_actor), 'read');
		if ($allow_read) {
			$recs = Smssend::model()->findAll();

			$TheMenu = FHelper::RenderMenu(0, $userid_actor, $parentmenuid);
	
			$this->userid_actor = $userid_actor;
			$this->parentmenuid = $parentmenuid;
	
			$this->bread_crumb_list = '
				<li>Sales</li>
				<li>></li>
				<li>Kirim SMS</li>';
	
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
		$menuid = 40;
		$userid_actor = Yii::app()->request->getParam('userid_actor');
	
		$allow_read = FHelper::AllowMenu($menuid, FHelper::GetGroupId($userid_actor), 'read');
		if ($allow_read) {
			$recs = Smssend::model()->findAll();
	
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
			'<li>Kirim SMS</li>';
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
		$menuid = 40;
		$userid_actor = Yii::app()->request->getParam('userid_actor');
	
		$allow_read = FHelper::AllowMenu($menuid, FHelper::GetGroupId($userid_actor), 'read');
		if ($allow_read) {
			$id = Yii::app()->request->getParam('id');
	
			$Criteria = new CDbCriteria();
			$Criteria->condition = $this->tbl_id." = :id";
			$Criteria->params = array(':id' => $id);
	
			$rec = Smssend::model()->find($Criteria);
	
			$form = new SmssendForm();
			$form['dest_name'] = $rec['dest_name'];
			$form['dest_mobile'] = $rec['dest_mobile'];
			$form['content'] = $rec['content'];
			$form['date_created'] = $rec['date_created'];
			$form['created_by'] = $rec['created_by'];
			$form['is_proc'] = $rec['is_proc'];
			$form['date_proc'] = $rec['date_proc'];
	
			$bread_crumb_list =
			'<li>Sales</li>'.
			'<li>></li>'.
			'<li><a href="#" onclick="ShowList('.$userid_actor.');">Kirim SMS</a></li>'.
			'<li>></li>'.
			'<li>View Kirim SMS</li>';
	
			$TheContent = $this->renderPartial(
					'view',
					array(
							'form' => $form,
							'userid_actor' => $userid_actor,
							'id' => $id,
							'menuid' => $menuid
					),
					true
			);
	
			//AuditLog
			$data = "$rec[sms_id], $rec[dest_name], $rec[dest_mobile], $rec[content], ".
					"$rec[date_created], $rec[created_by], $rec[is_proc], $rec[date_proc]";
	
			FAudit::add('SALESKIRIMSMS', 'View', FHelper::GetUserName($userid_actor), $data);
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

	public function actionAdd()
	{
		$menuid = 40;
	
		$userid_actor = Yii::app()->request->getParam('userid_actor');
		$do_add = Yii::app()->request->getParam('do_add');
	
		Yii::log('do_add = ' . $do_add, 'info');
	
		if(isset($do_add))
		{
			if($do_add == 1)
			{
				//process form
				$form = new Smssend();
				$form->attributes = Yii::app()->request->getParam('SmssendForm');
				//echo "<pre>".print_r($form->attributes)."</pre>";
				//exit();

				Yii::log('add form[dest_name] = ' . $form['dest_name'], 'info');

				if($form->validate())
				{
					//form validated
					Yii::log('validated', 'info');

					$rec = new Smssend();

					$rec['dest_name'] = $form['dest_name'];
					$rec['dest_mobile'] = $form['dest_mobile'];
					$rec['content'] = $form['content'];
					$rec['date_created'] = new CDbExpression('NOW()');
					$rec['created_by'] = FHelper::GetUserName($userid_actor);
					$rec['is_proc'] = $form['is_proc'];
					$rec['date_proc'] = $form['date_proc'];
					
					//echo "<pre>".print_r($rec)."</pre>";
					//exit();

					$rec->save();
          $idsms = $rec->getPrimaryKey();
          
          //integrasi gampp sms api
          $hasil = FHelper::KirimSms($form['dest_mobile'], $form['content']);
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
          
					$idoptic = $rec->getPrimaryKey();
					
					$Criteria = new CDbCriteria();
					$Criteria->condition = 'sms_id = :idoptic';
					$Criteria->params = array(':idoptic' => $idoptic);
					
					$rec = Smssend::model()->find($Criteria);

					$form = new Smssend();
					$form['dest_name'] = $rec['dest_name'];
					$form['dest_mobile'] = $rec['dest_mobile'];
					$form['content'] = $rec['content'];
					$form['date_created'] = $rec['date_created'];
					$form['created_by'] = $rec['created_by'];
					$form['is_proc'] = $rec['is_proc'];
					$form['date_proc'] = $rec['date_proc'];

					//AuditLog
					$data = "$rec[sms_id], $rec[dest_name], $rec[dest_mobile], $rec[content], $rec[date_created], $rec[created_by], ".
							"$rec[is_proc], $rec[date_proc]";
	
					FAudit::add('SALESKIRIMSMS', 'Add', FHelper::GetUserName($userid_actor), $data);

					$bread_crumb_list =
					'<li>Sales</li>'.
					'<li>></li>'.
					'<li><a href="#" onclick="ShowList('.$userid_actor.');">Kirim SMS</a></li>'.
					'<li>></li>'.
					'<li>View Kirim SMS</li>';

					$this->success_message =
					'<div class="notification note-success">'.
					'<a href="#" class="close" title="Close notification">close</a>'.
					'<p><strong>Success notification:</strong> Data '.$rec['dest_name'].' berhasil ditambah</p>'.
					'</div>';

					$TheContent = $this->renderPartial(
							'view',
							array(
									'form' => $form,
									'userid_actor' => $userid_actor,
									'id' => $id,
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
					'<li>Sales</li>'.
					'<li>></li>'.
					'<li><a href="#" onclick="ShowList('.$userid_actor.');">Kirim SMS</a></li>'.
					'<li>></li>'.
					'<li>Tambah Kirim SMS</li>';
	
					$TheContent = $this->renderPartial(
							'add',
							array(
									'form' => $form,
									'userid_actor' => $userid_actor,
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
	
				$recs = Smssend::model()->findAll($Criteria);
	
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
			if(FHelper::AllowMenu($menuid, FHelper::GetGroupId($userid_actor), 'write'))
			{
				Yii::log('do_add not set', 'info');
	
				$form = new SmssendForm();
	
				$bread_crumb_list =
				'<li>Sales</li>'.
				'<li>></li>'.
				'<li><a href="#" onclick="ShowList('.$userid_actor.');">Kirim SMS</a></li>'.
				'<li>></li>'.
				'<li>Tambah Kirim SMS</li>';
	
				$TheContent = $this->renderPartial(
						'add',
						array(
								'form' => $form,
								'userid_actor' => $userid_actor,
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
								'userid_actor' => $userid_actor,
								'recs' => $recs
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