 <?php

class customerController extends Controller
{
	public function actionIndex()
	{
		$this->render('index');
	}
  
	public function actionListCustomer()
	{
		$Criteria = new CDbCriteria();
		$Criteria->condition = "is_del = 'N' AND is_deact = 'N'";

		$userid_actor = Yii::app()->request->getParam('userid_actor');
		$customers = Customer::model()->findAll($Criteria);
		$TheMenu = FHelper::RenderMenu(0, $userid_actor);

		$this->layout = 'setting';
		$TheContent = $this->renderPartial(
			'v_list_customer',
			array(
				'userid_actor' => $userid_actor,
				'customers' => $customers
			),
			true
		);

		$this->render(
			'index_datamaster',
			array(
				'TheMenu' => $TheMenu,
				'TheContent' => $TheContent,
				'userid_actor' => $userid_actor
			)
		);

		echo CJSON::encode(array('html' => $TheContent));
	}

	/*
	actionAddCustomer

	Deskripsi
	Action untuk menampilkan form penambahan customer dan mengolah form 
	submission.
	*/	    
	public function actionAddCustomer()
	{
		$userid_actor = Yii::app()->request->getParam('userid_actor');
		$customers = Customer::model()->findAll();

		$form = new frmEditCustomer();

		$do_add = Yii::app()->request->getParam('do_add');

		if(isset($do_add))
		{
			if($do_add == 1)
			{
				//proses form submission

				$form->attributes = Yii::app()->request->getParam('frmEditCustomer');

				if($form->validate())
				{
					//simpan record ke tabel
					$Customer = new Customer();
					$Customer['nama'] = $form['nama'];
					$Customer['alamat'] = $form['alamat'];
					$Customer['telepon'] = $form['telepon'];
					$Customer['email'] = $form['email'];
					$Customer['tipe'] = 1;
					$Customer->save();
					
					//tampilkan informasi sukses
					$html = $this->renderPartial(
						'v_addcustomer_success',
						array(
							'userid_actor' => $userid_actor
						),
						true
					);
				}
				else
				{
					//tampilkan form 
					$html = $this->renderPartial(
						'vfrm_addcustomer',
						array(
							'form' => $form,
							'userid_actor' => $userid_actor,
						),
						true
					);
				}
			}
			else
			{
				$Criteria = new CDbCriteria();
				$Criteria->condition = 'is_del = 0';

				$userid_actor = Yii::app()->request->getParam('userid_actor');
				$customers = Customer::model()->findAll($Criteria);

				$html = $this->renderPartial(
					'v_list_customer',
					array(
						'userid_actor' => $userid_actor,
						'customers' => $customers
					),
					true
				);
			}
		}
		else
		{
			$html = $this->renderPartial(
				'vfrm_addcustomer',
				array(
					'form' => $form,
					'userid_actor' => $userid_actor,
				),
				true
			);
		}

		echo CJSON::encode(array('html' => $html));
	}

	/*
	actionEditCustomer

	Deskripsi
	Action untuk menampilkan form edit customer dan mengolah form submission.
	*/
	public function actionEditCustomer()
	{
		$userid_actor = Yii::app()->request->getParam('userid_actor');
		$idcustomer = Yii::app()->request->getParam('idcustomer');
		$do_edit = Yii::app()->request->getParam('do_edit');

		if(isset($do_edit))
		{
			if($do_edit == 1)
			{
				//proses edit form submission
				$form = new frmEditCustomer();
				$form->attributes = Yii::app()->request->getParam('frmEditCustomer');

				if($form->validate())
				{
					$Criteria = new CDbCriteria();
					$Criteria->condition = 'id = :idcustomer';
					$Criteria->params = array(':idcustomer' => $idcustomer);

					//simpan record ke tabel
					$Customer = Customer::model()->find($Criteria);
					$Customer['nama'] = $form['nama'];
					$Customer['alamat'] = $form['alamat'];
					$Customer['telepon'] = $form['telepon'];
					$Customer['email'] = $form['email'];
					$Customer->update();

					//tampilkan informasi sukses
					$html = $this->renderPartial(
						'v_editcustomer_success',
						array(
							'userid_actor' => $userid_actor
						),
						true
					);
				}
				else
				{
					//tampilkan form 
					$html = $this->renderPartial(
						'vfrm_editcustomer',
						array(
							'form' => $form,
							'userid_actor' => $userid_actor,
							'idcustomer' => $idcustomer
						),
						true
					);
				}
			}
			else
			{
				//batal edit, kembali ke daftar customer
				$Criteria = new CDbCriteria();
				$Criteria->condition = 'is_del = 0';

				$userid_actor = Yii::app()->request->getParam('userid_actor');
				$customers = Customer::model()->findAll($Criteria);

				$html = $this->renderPartial(
					'v_list_customer',
					array(
						'userid_actor' => $userid_actor,
						'customers' => $customers
					),
					true
				);
			}
		}
		else
		{
			//tampilkan form edit customer
			$Criteria = new CDbCriteria();
			$Criteria->condition = 'id = :idcustomer';
			$Criteria->params = array(':idcustomer' => $idcustomer);

			$customers = Customer::model()->find($Criteria);

			$form = new frmEditCustomer();
			$form['nama'] = $customers['nama'];
			$form['alamat'] = $customers['alamat'];
			$form['telepon'] = $customers['telepon'];
			$form['email'] = $customers['email'];

			//show form edit customer
			$html = $this->renderPartial(
				'vfrm_editcustomer',
				array(
					'form' => $form,
					'userid_actor' => $userid_actor,
					'idcustomer' => $idcustomer
				),
				true
			);
		}

		echo CJSON::encode(array('html' => $html));
	}

	/*
	actionDeleteCustomer

	Deskripsi
	Action untuk mengubah flag is_del pada record Customer
	*/
	public function actionDeleteCustomer()
	{
		$userid_actor = Yii::app()->request->getParam('userid_actor');
		$idcustomer = Yii::app()->request->getParam('idcustomer');

		$Criteria = new CDbCriteria();
		$Criteria->condition = 'id = :idcustomer';
		$Criteria->params = array(':idcustomer' => $idcustomer);
		
		//update flag delete customer
		$Customer = Customer::model()->find($Criteria);
		$Customer['is_del'] = 1;
		$Customer->update();

		//tampilkan informasi sukses
		$html = $this->renderPartial(
			'v_deletecustomer_success',
			array(
				'userid_actor' => $userid_actor
			),
			true
		);

		echo CJSON::encode(array('html' => $html));
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