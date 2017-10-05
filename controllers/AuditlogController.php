<?php
class AuditlogController extends FController
{
	private $success_message = '';
	
	public function actionIndex()
	{
		$menuid = 20;
		$parentmenuid = 1;
		$userid_actor = Yii::app()->request->getParam('userid_actor');

		$allow_read = FHelper::AllowMenu($menuid, FHelper::GetGroupId($userid_actor), 'read');
		if ($allow_read) {	
			$Criteria = new CDbCriteria();
			
			$form = new AuditLogForm();
			$userid_actor = Yii::app()->request->getParam('userid_actor');

			$modules = AuditLog::model()->findAll(array(
				'select'=>'t.module_name',
				'distinct'=>true
			));

			$ModuleList = CHtml::listData($modules, 'module_name', 'module_name');

			foreach ($ModuleList as $moduleName) {
				break;
			}
			//echo $moduleName;
			//exit();
			$Criteria->condition = 'module_name = :modulename';
			$Criteria->params = array(':modulename' => $moduleName);
			$auditlogs = AuditLog::model()->findAll($Criteria);
			/*
			echo '<pre>';
			print_r($auditlogs);
			echo '</pre>';
			exit();
			*/
			
			$TheMenu = FHelper::RenderMenu(0, $userid_actor, $parentmenuid);

			$this->userid_actor = $userid_actor;
			$this->parentmenuid = $parentmenuid;
	    
			$this->bread_crumb_list = '
				<li>Setting</li>
				<li>></li>
				<li>Audit Log</li>';
	    
	   		$this->layout = 'layout-baru';
	
			$TheContent = $this->renderPartial(
				'v_list_auditlog',
				array(
					'form' => $form,
					'ModuleList' => $ModuleList,
					'userid_actor' => $userid_actor,
					'auditlogs' => $auditlogs,
					'menuid' => $menuid
				),
				true
			);
		
			$this->render(
				'index',
				array(
					'TheMenu' => $TheMenu,
					'TheContent' => $TheContent,
					'userid_actor' => $userid_actor
				)
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
	}

	public function actionListAuditLog()
	{
		$menuid = 20;
		$userid_actor = Yii::app()->request->getParam('userid_actor');

		$allow_read = FHelper::AllowMenu($menuid, FHelper::GetGroupId($userid_actor), 'read');
		if ($allow_read) {
			$Criteria = new CDbCriteria();
			
			$form = new AuditLogForm();
			$form->attributes = Yii::app()->request->getParam('AuditLogForm');
			
			$userid_actor = Yii::app()->request->getParam('userid_actor');

			$modules = AuditLog::model()->findAll(array(
				'select'=>'t.module_name',
				'distinct'=>true
			));

			$ModuleList = CHtml::listData($modules, 'module_name', 'module_name');
			
			$moduleName = $form['module_name'];
			if (empty($moduleName)) {
				foreach ($ModuleList as $moduleName) {
					break;
				}
			}

			$Criteria->condition = 'module_name = :modulename';
			$Criteria->params = array(':modulename' => $moduleName);
			$auditlogs = AuditLog::model()->findAll($Criteria);
			/*
			echo '<pre>';
			print_r($auditlogs);
			echo '</pre>';
			exit();
			*/
			
			$TheMenu = FHelper::RenderMenu(0, $userid_actor, $parentmenuid);

			$this->userid_actor = $userid_actor;
			$this->parentmenuid = $parentmenuid;
				
			$this->layout = 'layout-baru';
	
			$TheContent = $this->renderPartial(
				'v_list_auditlog',
				array(
					'form' => $form,
					'ModuleList' => $ModuleList,
					'userid_actor' => $userid_actor,
					'auditlogs' => $auditlogs,
					'menuid' => $menuid
				),
				true
			);
		
			$bread_crumb_list =
				'<li>Setting</li>'.
				'<li>></li>'.
				'<li><a href="#" onclick="ShowList('.$userid_actor.');">Audit Log</a></li>';
		
			echo CJSON::encode(
				array(
					'html' => $TheContent,
					'bread_crumb_list' => $bread_crumb_list,
					'notification_message' => $this->success_message
				)
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
					'userid_actor' => $userid_actor,
					'auditlogs' => $auditlogs
				),
				true
			);
		}			
	}
	
	public function actionViewAuditLog()
	{
	     $userid_actor = Yii::app()->request->getParam('userid_actor');
	  	$menuid = 20;

	  	$allow_read = FHelper::AllowMenu($menuid, FHelper::GetGroupId($userid_actor), 'read');
	  	if ($allow_read) {
			$userid_actor = Yii::app()->request->getParam('userid_actor');
			$idauditlog = Yii::app()->request->getParam('idauditlog');
			$active_option = array('N' => 'Tidak', 'Y' => 'Ya');
		
			$Criteria = new CDbCriteria();
			$Criteria->condition = 'id = :idauditlog';
			$Criteria->params = array(':idauditlog' => $idauditlog);
		
			$auditlog = AuditLog::model()->find($Criteria);
		
			$form = new AuditLogForm();
			$form['module_name'] = $auditlog['module_name'];
			$form['act_code'] = $auditlog['act_code'];
			$form['act_by'] = $auditlog['act_by'];
			$form['date_act'] = $auditlog['date_act'];
			$form['data'] = $auditlog['data'];
		
			$bread_crumb_list =
				'<li>Setting</li>'.
				'<li>></li>'.
				'<li><a href="#" onclick="ShowList('.$userid_actor.');">Kustomer Optik</a></li>'.
				'<li>></li>'.
				'<li>View Audit Log</li>';
		
			$html = $this->renderPartial(
				'v_view_auditlog',
				array(
					'form' => $form,
					'userid_actor' => $userid_actor,
					'idauditlog' => $idauditlog,
					'active_option' => $active_option,
					'menuid' => $menuid
				),
				true
			);
	
			echo CJSON::encode(
				array(
					'html' => $html,
					'bread_crumb_list' => $bread_crumb_list
				)
			);
			
			//AuditLog
			//$data = "$auditlog[auditlog_id], $auditlog[module_name], $auditlog[act_by]";
	
			//FAudit::add('SYSAUDITLOG', 'View', FHelper::GetUserName($userid_actor), $data);
		}
		else
		{
			$this->bread_crumb_list = '
				<li>Not Authorize</li>';
		
			$this->layout = 'layout-baru';
		
			$TheContent = $this->renderPartial(
				'v_not_auth',
				array(
					'userid_actor' => $userid_actor,
					'auditlogs' => $auditlogs
				),
				true
			);
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