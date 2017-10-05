<?php

class SalesController extends FController
{
	private $success_message = '';
	private $tbl_id = 't.sales_id';

	private function getFrameTypeList()
	{
		//ambil listData untuk frametype
		$Criteria = new CDbCriteria();
		$Criteria->condition = 'is_del = "N" AND is_deact = "N" AND group_id = 4';
		$Criteria->order = 'order_no ASC';
		$mtr_std = mtr_std::model()->findAll($Criteria);
		return CHtml::listData($mtr_std, 'dsc', 'dsc');
		//print_r($frametype_list);
		//exit();
	}

	private function getBCList()
	{
		//ambil listData untuk BC
		$bc_list = array('Normal' => 'Normal','4' => '4', '6' => '6' ,'8' => '8');
		return $bc_list;
		//print_r($bc_list);
		//exit();
	}

	public function actionIndex()
	{
		$menuid = 22;
		$parentmenuid = 7;

		$userid_actor = Yii::app()->request->getParam('userid_actor');
		$idlokasi = Yii::app()->request->cookies['idlokasi']->value;

		$allow_read = FHelper::AllowMenu($menuid, FHelper::GetGroupId($userid_actor), 'read');
		
		if ($allow_read) 
		{
			$Criteria = new CDbCriteria();
			$Criteria->condition = "t.branch_id = :idlokasi";
			$Criteria->params = array(':idlokasi' => $idlokasi);
			$Criteria->order = 'date_created DESC';
			$Criteria->limit = 1500;

			$recs = Sales::model()->with('SalesCustomer')->findAll($Criteria);

			$TheMenu = FHelper::RenderMenu(0, $userid_actor, $parentmenuid);

			$this->userid_actor = $userid_actor;
			$this->parentmenuid = $parentmenuid;

			$this->bread_crumb_list = '
				<li>Sales</li>
				<li>></li>
				<li>Penjualan</li>';

			$this->layout = 'layout-baru';
			
			$TheContent = $this->GetListView();

			/*
			$TheContent = $this->renderPartial(
				'list',
				array(
					'userid_actor' => $userid_actor,
					'recs' => $recs,
					'menuid' => $menuid
				),
				true
			);
			*/

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
				'not_auth',
				array(
					'userid_actor' => $userid_actor
				),
				true
			);
		}
	}

	public function actionList()
	{
		$menuid = 22;
		$userid_actor = Yii::app()->request->getParam('userid_actor');
		$idlokasi = Yii::app()->request->cookies['idlokasi']->value;

		$allow_read = FHelper::AllowMenu($menuid, FHelper::GetGroupId($userid_actor), 'read');
		if ($allow_read) 
		{
			$Criteria = new CDbCriteria();
			$Criteria->condition = "t.branch_id = :idlokasi";
			$Criteria->params = array(':idlokasi' => $idlokasi);
			$Criteria->order = 'date_created DESC';
			$Criteria->limit = 1500;

			$recs = Sales::model()->with('SalesCustomer')->findAll($Criteria);

			$this->layout = 'layout-baru';

			$TheContent = $this->GetListView();
			
			/*
			$TheContent = $this->renderPartial(
				'list',
				array(
					'userid_actor' => $userid_actor,
					'recs' => $recs,
					'menuid' => $menuid
				),
				true
			);
			*/
			
			$bread_crumb_list =
			'<li>Sales</li>'.
			'<li>></li>'.
			'<li><a href="#" onclick="ShowList('.$userid_actor.');">Penjualan</a></li>';

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
				array(
					'userid_actor' => $userid_actor
				),
				true
			);
		}
	}
	
	/**
	  Fungsi untuk mengembalikan view (memanfaatkan MakeTable)
	*/
	private function GetListView()
	{
	  $menuid = 22;
	  $userid_actor = Yii::app()->request->cookies['userid_actor']->value;
		$idlokasi = Yii::app()->request->cookies['idlokasi']->value;
	  $search_term = Yii::app()->request->getParam("search");
    $search = "";
    
    if($search_term != "")
    {
      $search_terms = explode(" ", $search_term);
      
      foreach($search_terms as $term)
      {
        $temp_search = "
          customer.name like '%$term%' OR
          customer.mobile like '%$term%' OR
          sales.date_created like '%$term%' OR
          sales.order_no like '%$term%' OR
          sales.invoice_no like '%$term%' OR
          sales.created_by like '%$term%' OR
          sales.total like '%$term%' OR
          sales.balance like '%$term%' OR
          sales.status like '%$term%'
        ";
        
        if($search != "")
        {
          $search .= " AND ";
        }
        
        $search .= $temp_search;
      }
      
      if($search != "")
      {
        $search = " AND ( $search )";
      }
    }
    
    /**/
    $command = Yii::app()->db->createCommand()
      ->select("sales.*, customer.*")
      ->from("pos_sales sales")
      ->join('pos_sales_customer customer', 'customer.sales_id = sales.sales_id')
      ->where(
        "sales.branch_id = :idlokasi
        $search"
      );
    $command->params = array(
      ':idlokasi' => $idlokasi
    );
    $command->order = "date_created DESC";
    $temp_recs = $command->queryAll();
    
    $maketable = new MakeTable();
    $maketable->SetupPages( count($temp_recs) );
    $maketable->list_type = "sales";
    $maketable->action_name = "Sales_RefreshTable";
    $maketable->action_name2 = "Sales_RefreshTable2";
    
    $command->text = "";
    $command->offset = ($maketable->pageno - 1) * $maketable->rows_per_page;
    $command->limit = $maketable->rows_per_page;
    $recs = $command->queryAll();
    
    $TheContent = $this->renderPartial(
      'list',
      array(
        'userid_actor' => $userid_actor,
        'recs' => $recs,
        'menuid' => $menuid
      ),
      true
    );
    
    $maketable->table_content = $TheContent;
    $TheContent = $maketable->Render($maketable);
    
    return $TheContent;
	}
	
	/**
	  Fungsi untuk mengembalikan list view saja.
	*/
	public function actionGetListView()
	{
	  ini_set('display_errors', '1');
	  ini_set('max_execution_time', 6000);
	  ini_set('memory_limit', "1000M");
	  error_reporting(E_ALL);
	  
	  $TheContent = $this->GetListView();
	  
	  echo CJSON::encode( array('html' => $TheContent) );
	}

	public function actionView()
	{
		$menuid = 22;
		$userid_actor = Yii::app()->request->getParam('userid_actor');
		$from = Yii::app()->request->getParam('from');

		$allow_read = FHelper::AllowMenu($menuid, FHelper::GetGroupId($userid_actor), 'read');
		if ($allow_read) {
			$id = Yii::app()->request->getParam('id');

			$Criteria = new CDbCriteria();
			$Criteria->condition = $this->tbl_id." = :id";
			$Criteria->params = array(':id' => $id);

			$rec = Sales::model()->with('SalesDetail','SalesCustomer','SalesPresc','SalesPrecal')->find($Criteria);

			$bread_crumb_list =
			'<li>Sales</li>'.
			'<li>></li>'.
			'<li><a href="#" onclick="ShowList('.$userid_actor.');">Penjualan</a></li>'.
			'<li>></li>'.
			'<li>View Penjualan</li>';

			$html = $this->renderPartial(
				'view',
				array(
					'form' => $form,
					'userid_actor' => $userid_actor,
					'id' => $id,
					'rec' => $rec,
					'menuid' => $menuid,
					'from' => $from
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
			$data = "$rec[sales_id], $rec[order_no], $rec[invoice_no], $rec[customer_id], $rec[open_date], $rec[close_date], ".
					"$rec[branch_id], $rec[table_no], $rec[pax], $rec[note], $rec[subtotal1], $rec[disc_percent], $rec[disc_amount], ".
					"$rec[subtotal2], $rec[tax_percent], $rec[tax_amount], $rec[total], $rec[num_of_item], $rec[balance], $rec[status], ".
					"$rec[date_created], $rec[created_by], $rec[date_update], $rec[update_by], $rec[version]";

			FAudit::add('PENJUALAN', 'View', FHelper::GetUserName($userid_actor), $data);
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
	}

	/*
	  actionAdd()
	  
	  Deskripsi
	  Fungsi untuk menyimpan record sales baru
	*/
	public function actionAdd()
	{
	  ini_set('display_errors', '1');
	  error_reporting(E_ALL & ~E_NOTICE);
	  
		$menuid = 22;

		$userid_actor = Yii::app()->request->getParam('userid_actor');
		$idlokasi = Yii::app()->request->cookies['idlokasi']->value;
		$do_add = Yii::app()->request->getParam('do_add');

		Yii::log('do_add = ' . $do_add, 'info');

		$frametype_list = $this->getFrameTypeList();
		$bc_list = $this->getBCList();

		if(isset($do_add))
		{
			if($do_add == 1)
			{
				//ambil variabel dari form
				/*
					variabel-variabel dari form dan querystring
					r:sales/add
						userid_actor:1
						aip:18,18,18
						SalesForm[customer_id]:24
						SalesForm[presc_id]:92
						SalesForm[precal_pvr]:1
						SalesForm[precal_pvl]:4
						SalesForm[precal_a]:2
						SalesForm[precal_b]:5
						SalesForm[precal_dbl]:3
						SalesForm[precal_frametype]:Nilor
						row0:0
						iditem0:18
						item_qty0:1
						row1:1
						iditem1:18
						item_qty1:1
						row2:2
						iditem2:18
						item_qty2:1
						SalesForm[note]:cattan
						SalesForm[subtotal1]:35.183.250
						SalesForm[disc_percent]:5
						SalesForm[disc_amount]:1.759.163
						SalesForm[subtotal2]:33.424.088
						SalesForm[tax_percent]:10
						SalesForm[tax_amount]:3.342.409
						SalesForm[total]:36.766.496
						do_add:1
				*/

				$form = new SalesForm();
				$form->attributes = Yii::app()->request->getParam('SalesForm');
				$aidproduk = Yii::app()->request->getParam('aip');

				//$arrip = explode (",", $aidproduk);
				$arrip = $aidproduk;
				$noofitem = sizeof($arrip);
				//echo "<pre>";print_r($form);echo "</pre>";
				//exit();

				if($form->validate())
				{
					//form validated
					$idcustomer = $form['customer_id'];
					$idresep = $form['presc_id'];

					if (empty($idcustomer)) 
					{
						//new customer
						//echo "idcustomer = $idcustomer<br /><pre>";print_r($form);echo "</pre>";
						//exit();

						//add new customer
						$mtr_customer = new mtr_customer();
						$mtr_customer['name'] = $form['cust_name'];
						$mtr_customer['gender_id'] = $form['cust_gender'];
						$mtr_customer['mobile'] = $form['cust_mobile'];
						$mtr_customer['phone'] = $form['cust_phone'];
						$mtr_customer['birth_place'] = $form['cust_pob'];
						$mtr_customer['birth_date'] = $form['cust_dob_to_db'];
						$mtr_customer['email'] = $form['cust_email'];
						$mtr_customer['address'] = $form['cust_addr'];
						$mtr_customer['city'] = $form['cust_city'];
						$mtr_customer['reg_date'] = date('Y-m-d H:i:s');
						$mtr_customer['cust_type_id'] = 5; //Customer Type = 5: Reguler, 6: VIP, 7: VVIP
						$mtr_customer['is_del'] = 'N';
						$mtr_customer['is_deact'] = 'N';
						$mtr_customer['date_created'] = date('Y-m-d H:i:s');
						$mtr_customer['created_by'] = $userid_actor;
						$mtr_customer['date_update'] = date('Y-m-d H:i:s');
						$mtr_customer['update_by'] = $userid_actor;
						$mtr_customer->insert();
						$idcustomer = $mtr_customer->getPrimaryKey();

						//add new resep
						$pos_customer_presc = new pos_customer_presc();
						$pos_customer_presc['customer_id'] = $idcustomer;
						$pos_customer_presc['frame_rec'] = '';
						$pos_customer_presc['lens_rec'] = '';
						$pos_customer_presc['wearing_sch']  = '';
						$pos_customer_presc['presc_date'] = $form['presc_date_to_db'];
						$pos_customer_presc['note'] = '';
						$pos_customer_presc['examiner_id'] = $form['examiner_id'];
						$pos_customer_presc->insert();
						$idresep = $pos_customer_presc->getPrimaryKey();

						//add new resep detail for left eye
						$pos_customer_presc_detail = new pos_customer_presc_detail();
						$pos_customer_presc_detail['side'] = 'L';
						$pos_customer_presc_detail['presc_id'] = $idresep;
						$pos_customer_presc_detail['sph'] = $form['l_sph'];
						$pos_customer_presc_detail['cyl'] = $form['l_cyl'];
						$pos_customer_presc_detail['axis'] = $form['l_axis'];
						$pos_customer_presc_detail['prism'] = $form['l_prism'];
						$pos_customer_presc_detail['base'] = $form['l_base'];
						$pos_customer_presc_detail['add'] = $form['l_add'];
						$pos_customer_presc_detail['dist_pd'] = $form['l_dist_pd'];
						$pos_customer_presc_detail['near_pd'] = $form['l_near_pd'];
						$pos_customer_presc_detail->insert();

						//add new resep detail for right eye
						$pos_customer_presc_detail = new pos_customer_presc_detail();
						$pos_customer_presc_detail['side'] = 'R';
						$pos_customer_presc_detail['presc_id'] = $idresep;
						$pos_customer_presc_detail['sph'] = $form['r_sph'];
						$pos_customer_presc_detail['cyl'] = $form['r_cyl'];
						$pos_customer_presc_detail['axis'] = $form['r_axis'];
						$pos_customer_presc_detail['prism'] = $form['r_prism'];
						$pos_customer_presc_detail['base'] = $form['r_base'];
						$pos_customer_presc_detail['add'] = $form['r_add'];
						$pos_customer_presc_detail['dist_pd'] = $form['r_dist_pd'];
						$pos_customer_presc_detail['near_pd'] = $form['r_near_pd'];
						$pos_customer_presc_detail->insert();
					} //pembuatan customer baru

					
					$subtotal1 = str_replace(".", "", $form['subtotal1']);
					//$disc_percent = str_replace(".", "", $form['disc_percent']);
					$disc_percent = $form['disc_percent'];
					$disc_amount = str_replace(".", "", $form['disc_amount']);
					$subtotal2 = str_replace(".", "", $form['subtotal2']);
					$tax_percent = str_replace(".", "", $form['tax_percent']);
					$tax_amount = str_replace(".", "", $form['tax_amount']);
					$total = str_replace(".", "", $form['total']);
					$daftar_paket = Yii::app()->request->getParam('daftar_paket'); 
					
					//simpan pos_sales
					$sales = new Sales();
					$sales['order_no'] = $form['order_no'] = FHelper::GenerateOrderNo($idlokasi);
					$sales['customer_id'] = $idcustomer;
					$sales['presc_id'] = $idresep;
					$sales['open_date'] = new CDbExpression('NOW()');
					$sales['branch_id'] = $idlokasi;
					$sales['note'] = $form['note'];
					$sales['subtotal1'] = $subtotal1;
					$sales['disc_percent'] = $disc_percent;
					$sales['disc_amount'] = $disc_amount;
					$sales['subtotal2'] = $subtotal2;
					$sales['tax_percent'] = $tax_percent;
					$sales['tax_amount'] = $tax_amount;
					$sales['total'] = $total;
					$sales['num_of_item'] = $noofitem;
					$sales['balance'] = $total;
					$sales['status'] = 'OPEN';
					$sales['tanparesep'] = 0;
					
					$tanparesep = Yii::app()->request->getParam('chkTanpaResep');
					if( isset($tanparesep) )
					{
						$sales['tanparesep'] = 1;
					}
					
					$sales['date_created'] = new CDbExpression('NOW()');
					$sales['created_by'] = FHelper::GetUserName($userid_actor);
					$sales['created_by_id'] = $userid_actor;
					$sales->save();
					
					/*
					try
					{
						$save_ret_val = 'test';
						$save_ret_val = $sales->save();
						$save_ret_val = ($save_ret_val == 'test' ? 'test' :  ($save_ret_val == true ? 'true' : $save_Ret_val));
						
						Yii::log("errors: " . print_r($sales->getErrors(), true), 'info');
						Yii::log("errors: " . print_r($sales, true), 'info');
					}
					catch(Exception $e)
					{
						Yii::log("errors: " . print_r($sales->getErrors(), true), 'info');
					}
					*/

					$idsales = $sales->getPrimaryKey();

					//simpan pos_sales_customer

					//ambil record customer
					$Criteria = new CDbCriteria();
					$Criteria->condition = 'customer_id = :idcustomer';
					$Criteria->params = array(':idcustomer' => $idcustomer);
					$customer = mtr_customer::model()->with('customer_type','gender')->find($Criteria);

					$sales_customer = new PosSalesCustomer();
					$sales_customer['sales_id'] = $idsales;
					$sales_customer['customer_id'] = $idcustomer;
					$sales_customer['name'] = $form['cust_name'] = $customer['name'];
					$sales_customer['type'] = $form['cust_type'] = $customer->customer_type['dsc'];
					$sales_customer['gender'] = $form['cust_gender'] = $customer->gender['dsc'];
					$sales_customer['mobile'] = $form['cust_mobile'] = $customer['mobile'];
					$sales_customer['phone'] = $form['cust_phone'] = $customer['phone'];
					$sales_customer['birth_place'] = $form['cust_pob'] = $customer['birth_place'];
					$sales_customer['birth_date'] = $form['cust_dob'] = $customer['birth_date'];
					$sales_customer['email'] = $form['cust_email'] = $customer['email'];
					$sales_customer['address'] = $form['cust_addr'] = $customer['address'];
					$sales_customer['city'] = $form['cust_city'] = $customer['city'];
					$sales_customer->save();

					//simpan pos_sales_presc
					//ambil record resep terakhir
					$Criteria = new CDbCriteria();
					$Criteria->condition = 'presc_id = :idresep';
					$Criteria->params = array(':idresep' => $idresep);
					$resep = pos_customer_presc::model()->find($Criteria);

					$Criteria = new CDbCriteria();
					$Criteria->condition = 'presc_id = :idresep';
					$Criteria->params = array(':idresep' => $idresep);
					$Criteria->order = 'side';
					$resep_detail = pos_customer_presc_detail::model()->findAll($Criteria);

					$sales_presc = new PosSalesPresc();
					$sales_presc['sales_id'] = $idsales;
					$sales_presc['presc_id'] = $idresep;
					$sales_presc['presc_date'] = $form['presc_date_to_db'] = $resep['presc_date'];
					$sales_presc['examiner_name'] = $form['examiner_name'] = $resep->examiner['name'];
					$sales_presc['l_sph'] = $form['l_sph'] = $resep_detail[0]['sph'];
					$sales_presc['l_cyl'] = $form['l_cyl'] = $resep_detail[0]['cyl'];
					$sales_presc['l_axis'] = $form['l_axis'] = $resep_detail[0]['axis'];
					$sales_presc['l_prism'] = $form['l_prism'] = $resep_detail[0]['prism'];
					$sales_presc['l_base'] = $form['l_base'] = $resep_detail[0]['base'];
					$sales_presc['l_add'] = $form['l_add'] = $resep_detail[0]['add'];
					$sales_presc['l_dist_pd'] = $form['l_dist_pd'] = $resep_detail[0]['dist_pd'];
					$sales_presc['l_near_pd'] = $form['l_near_pd'] = $resep_detail[0]['near_pd'];
					$sales_presc['r_sph'] = $form['r_sph'] = $resep_detail[1]['sph'];
					$sales_presc['r_cyl'] = $form['r_cyl'] = $resep_detail[1]['cyl'];
					$sales_presc['r_axis'] = $form['r_axis'] = $resep_detail[1]['axis'];
					$sales_presc['r_prism'] = $form['r_prism'] = $resep_detail[1]['prism'];
					$sales_presc['r_base'] = $form['r_base'] = $resep_detail[1]['base'];
					$sales_presc['r_add'] = $form['r_add'] = $resep_detail[1]['add'];
					$sales_presc['r_dist_pd'] = $form['r_dist_pd'] = $resep_detail[1]['dist_pd'];
					$sales_presc['r_near_pd'] = $form['r_near_pd'] = $resep_detail[1]['near_pd'];
					$sales_presc->save();

					//simpan pos_sales_precal
					$sales_precal = new PosSalesPrecal();
					$sales_precal['sales_id'] = $idsales;
					$sales_precal['pvl'] = $form['precal_pvl'];
					$sales_precal['pvr'] = $form['precal_pvr'];
					$sales_precal['a'] = $form['precal_a'];
					$sales_precal['b'] = $form['precal_b'];
					$sales_precal['dbl'] = $form['precal_dbl'];
					$sales_precal['frame_type'] = $form['precal_frametype'];
					$sales_precal['bc'] = $form['precal_bc'];
					$sales_precal['et'] = $form['precal_et'];
					$sales_precal->save();

					//simpan pos_sales_det
					$disc_amount = (float)$hargajual['diskon']/(float)100 * (float)$hargajual['harga_jual'];
					$hargajualnet = 1 * ((float)($hargajual['harga_jual'] - ((float)$hargajual['diskon']/(float)100 * (float)$hargajual['harga_jual'])));
  
					$item_count = 0;
					foreach($arrip as $idproduk) 
					{
						$item_qty = Yii::app()->request->getParam('item_qty'.$idproduk);
						$item_qty = $idproduk['item_qty'];

						if($idproduk['byname'] == 1) //entry produk berdasarkan nama
						{
						  //ambil info produk
							$CriteriaIP = new CDbCriteria();
							$CriteriaIP->condition = 'id = :idproduk';
							$CriteriaIP->params = array(':idproduk' => (int)$idproduk['idinventory']); //idproduk
							$produk = inv_inventory::model()->find($CriteriaIP);

							//ambil harga jual
							$CriteriaHJ = new CDbCriteria();
							$CriteriaHJ->condition = 'id_item = :idproduk AND id_toko = :idlokasi';
							$CriteriaHJ->params = array(':idlokasi' => $idlokasi, ':idproduk' => $idproduk['idinventory']);
							$hargajual = inv_harga_jual::model()->find($CriteriaHJ);
						}
						else //entry produk berdasarkan barcode
						{
							//ambil info produk berdasarkan id individu
							$CriteriaIP = new CDbCriteria();
							$CriteriaIP->condition = 'id = :idproduk';
							$CriteriaIP->params = array(':idproduk' => (int)$idproduk['iditem']); //id individu
							$item = inv_item::model()->find($CriteriaIP);
							$produk = $item->produk;

							//ambil harga jual
							$CriteriaHJ = new CDbCriteria();
							$CriteriaHJ->condition = 'id_item = :idproduk AND id_toko = :idlokasi';
							$CriteriaHJ->params = array(':idlokasi' => $idlokasi, ':idproduk' => $produk['id']);
							$hargajual = inv_harga_jual::model()->find($CriteriaHJ);
						}

						$sales_det = new PosSalesDet();
						$sales_det['sales_id'] = $idsales;
						
						if($idproduk['byname'] == 1)
						{
						  if($idproduk['is_paket'] == 0)
						  {
						    $sales_det['item_id'] = $idproduk['idinventory'];
						  }
						  else
						  {
						    $sales_det['item_id'] = $idproduk['iditem'];
						  }
						  
						}
						else
						{
						  if($idproduk['iditem'] != -1)
						  {
						    $sales_det['item_id'] = $idproduk['iditem'];
						  }
						  else
						  {
						    $sales_det['item_id'] = $idproduk['idinventory'];
						  }
						  
						}
						
						$sales_det['nobarcode'] = $idproduk['byname'];
						$sales_det['item_cid'] = $produk['idkategori'];
						$sales_det['barcode'] = ($idproduk['byname'] == 1 ? '---' : $item['barcode']);
						$sales_det['name'] = $produk['nama'];
						$sales_det['quantity'] = 1; // FRA >> nilai "1" menggantikan : $idproduk['qty'];
						$sales_det['price'] = $hargajual['harga_jual'];
						$sales_det['disc_percent'] = $hargajual['diskon'];

						$diskonamt = $hargajual['diskon']/100 * $hargajual['harga_jual'];
						$harganet = $item_qty * ($hargajual['harga_jual'] - $diskonamt);

						$sales_det['disc_amount'] = $diskonamt;
						$sales_det['total_price'] = $harganet;
						$sales_det['act_by'] = '0';
						$sales_det['is_free'] = 'N';
						$sales_det['is_canceled'] = 'N';
						$sales_det['is_printed'] = 'N';
						$sales_det->save();
						
						$idsalesdet = $sales_det->getPrimaryKey();
						
						Yii::log(
              "idproduk = " . print_r($idproduk, true), 
              'info'
            );

						//Set status item to 'customer' (=4)
						if($idproduk['byname'] == 1 && $idproduk['is_paket'] == 0)
						{
						  $this->MakeJobOrder(
                $idsales,
                $idsalesdet,
                $idproduk['idinventory'], 
                $idproduk['idlokasipengolah'],
                $idproduk['kirikanan'],
                $idproduk['catatan'],
                $idproduk['prioritas']
              );
            }
						else
						{
						  /*
                TODO: 
                tambahkan logic untuk memeriksa barang tipe paket.
                jika barang tipe paket, maka lakukan insert iditem-iditem ke tabel 
                pos_sales_det_paket.
                
                Lalu update status iditem-iditem tersebut.
                
                Gunakan $daftar_paket
              */
						
              if($idproduk['is_paket'] == 0)
              {
                FHelper::ItemStatusUpdate(
                  $idproduk['iditem'], 
                  4, 
                  $idlokasi, 
                  Yii::app()->request->cookies['userid_actor']->value,
                  'Pencatatan status item akibat save sales',
                  $idsales
                );
              }
              else
              {
                //set flag per item pada daftar detil paket
                $the_paket = null;
                
                Yii::log(
                  "daftar_paket = " . print_r($daftar_paket, true), 
                  'info'
                );
                
                foreach($daftar_paket as $data_paket)
                {
                  if($data_paket['iditem'] == $idproduk['iditem'])
                  {
                    $the_paket = $data_paket;
                    break;
                  }
                }
                
                if($the_paket != null)
                {
                  $daftar_barang_paket = $the_paket['daftarproduk'];
                  
                  foreach($daftar_barang_paket as $data_barang)
                  {
                    
                    $daftar_item = $data_barang['daftarbarcode'];
                    foreach($daftar_item as $data_item)
                    {
                      $item_barcode = $data_item['barcode'];
                      
                      //ambil iditem berdasarkan barcode
                      //$item_iditem = FHelper::GetIdItemByBarcode($item_barcode);
                      $item_iditem = $data_item['iditem'];
                      
                      //tandai status item
                      FHelper::ItemStatusUpdate(
                        $item_iditem, 
                        4, 
                        $idlokasi, 
                        Yii::app()->request->cookies['userid_actor']->value,
                        'Pencatatan status item akibat save sales',
                        $idsales
                      );
                      
                      //catat detil paket ke tabel pos_sales_det_paket
                      try
                      {
                        $detil_sales_paket = new pos_sales_det_paket();
                        $detil_sales_paket['sales_det_id'] = $idsalesdet;
                        $detil_sales_paket['iditem'] = $item_iditem;
                        $detil_sales_paket['idinventory'] = $item_iditem;
                        $detil_sales_paket->save();
                        
                        $errors = $detil_sales_paket->getErrors();
                        
                        Yii::log(
                          "detil_sales_paket errors: " . print_r($errors, true),
                          "info"
                        );
                      }
                      catch(Exception $e)
                      {
                        Yii::log(
                          "detil_sales_paket exception: " . $e->getMessage(),
                          "info"
                        );
                      }
                        
                    }//loop for items of any package's member.
                    
                  }//loop barang paket
                }
                  
              }//is_produk or not
                
						}//is_by_name ?

						$item_count++;
					} //loop array produk
					//simpan pos_sales_det
							
					//AuditLog
					$data = "$sales[order_no], $sales[customer_id], $sales[presc_id], $sales[open_date], $sales[branch_id], $sales[note], $sales[subtotal1], $sales[disc_percent], $sales[disc_amount], $sales[subtotal2],
					$sales[tax_percent], $sales[tax_amount], $sales[total], $sales[num_of_item], $sales[balance], $sales[status], $sales[date_created], $sales[created_by]";

					FAudit::add('PENJUALAN', 'Add', FHelper::GetUserName($userid_actor), $data);

					$Criteria = new CDbCriteria();
					$Criteria->condition = $this->tbl_id." = :id";
					$Criteria->params = array(':id' => $idsales);

					$rec = Sales::model()->with('SalesDetail','SalesCustomer','SalesPresc','SalesPrecal')->find($Criteria);

					$bread_crumb_list =
					'<li>Sales</li>'.
					'<li>></li>'.
					'<li><a href="#" onclick="ShowList('.$userid_actor.');">Penjualan</a></li>'.
					'<li>></li>'.
					'<li>View Penjualan</li>';

					$success_message =
					'<div class="notification note-success">'.
					'<a href="#" class="close" title="Close notification">close</a>'.
					'<p><strong>Success notification:</strong> Data '.$sales['order_no'].' berhasil ditambah</p>'.
					'</div>';

					$html = $this->renderPartial(
						'view',
						array(
							'form' => $form,
							'userid_actor' => $userid_actor,
							'id' => $idsales,
							'rec' => $rec,
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
					'<li><a href="#" onclick="ShowList('.$userid_actor.');">Penjualan</a></li>'.
					'<li>></li>'.
					'<li>Tambah Penjualan</li>';

					$daftar_lokasi = FHelper::GetLocationListData(false);
					
					$dialog_job_order = $this->renderPartial(
            'v_dialog_job_order',
            array(
              'daftar_lokasi' => $daftar_lokasi
            ),
            true
          );
          
          $dialog_info_job_order = $this->renderPartial(
            'v_dialog_info_job_order',
            array(),
            true
          );
          
          $dialog_entry_paket = $this->renderPartial(
            'v_dialog_entry_paket',
            array(),
            true
          );
					
					$html = $this->renderPartial(
						'add',
						array(
							'form' => $form,
							'daftar_paket' => $daftar_paket,  //TODO: bikin logic untuk menerima informasi daftar paket
							'userid_actor' => $userid_actor,
							'menuid' => $menuid,
							'daftar_lokasi' => $daftar_lokasi,
							'dialog_job_order' => $dialog_job_order,
							'dialog_info_job_order' => $dialog_info_job_order,
							'dialog_entry_paket' => $dialog_entry_paket
						),
						true
					);
				}
			}
			else
			{
			  //do_add == 0
			  
				//back to list
				$Criteria = new CDbCriteria();

				$userid_actor = Yii::app()->request->getParam('userid_actor');
				$recs = Sales::model()->findAll($Criteria);

				$this->layout = 'layout-baru';

				$html = $this->renderPartial(
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

				$form = new SalesForm();

				$bread_crumb_list =
				'<li>Sales</li>'.
				'<li>></li>'.
				'<li><a href="#" onclick="ShowList('.$userid_actor.');">Penjualan</a></li>'.
				'<li>></li>'.
				'<li>Tambah Penjualan</li>';

				$rec['subtotal1'] = 0;
				$rec['disc_percent'] = '0.00';
				$rec['disc_amount'] = 0;
				$rec['subtotal2'] = 0;
				$rec['tax_percent'] = '0.00';
				$rec['tax_amount'] = 0;
				$rec['total'] = 0;

				$daftar_lokasi = FHelper::GetLocationListData(false);
				
				$dialog_job_order = $this->renderPartial(
				  'v_dialog_job_order',
				  array(
				    'daftar_lokasi' => $daftar_lokasi
          ),
				  true
        );
        
        $dialog_info_job_order = $this->renderPartial(
				  'v_dialog_info_job_order',
				  array(
				    'daftar_lokasi' => $daftar_lokasi
          ),
				  true
        );
        
        $dialog_entry_paket = $this->renderPartial(
          'v_dialog_entry_paket',
          array(),
          true
        );
				
				$html = $this->renderPartial(
					'add',
					array(
						'form' => $form,
						'userid_actor' => $userid_actor,
						'menuid' => $menuid,
						'rec' => $rec,
						'frametype_list' => $frametype_list,
						'bc_list' => $bc_list,
						'daftar_lokasi' => $daftar_lokasi,
						'dialog_job_order' => $dialog_job_order,
						'dialog_info_job_order' => $dialog_info_job_order,
						'dialog_entry_paket' => $dialog_entry_paket
					),
					true
				);
			}
			else
			{
			  //user is not allowed
			  
				$this->bread_crumb_list = '
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
				'html' => $html,
				'bread_crumb_list' => $bread_crumb_list,
				'notification_message' => $success_message
			)
		);
	}

	public function actionEdit()
	{
	  Yii::getLogger()->autoFlush = 1;
    
    $menuid = 22;
    
    ini_set('display_errors', '1');
    error_reporting(E_ALL & ~E_NOTICE);

    $userid_actor = Yii::app()->request->getParam('userid_actor');
    $idlokasi = Yii::app()->request->cookies['idlokasi']->value;
    $id = Yii::app()->request->getParam('id');
    $do_edit = Yii::app()->request->getParam('do_edit');

    Yii::log('do_edit = ' . $do_edit, 'info');
    Yii::log('id = ' . $id, 'info');

    $frametype_list = $this->getFrameTypeList();
    $bc_list = $this->getBCList();
    
    $form = new SalesForm();

    if(isset($do_edit))
    {
      if($do_edit == 1)
      {
        //ambil variabel dari form
        /*
          variabel-variabel dari form dan querystring
          r:sales/edit
          userid_actor:1
          aip:18,236
          id:40
          SalesForm[customer_id]:24
          SalesForm[presc_id]:92
          SalesForm[precal_pvl]:7
          SalesForm[precal_pvr]:4
          SalesForm[precal_a]:7
          SalesForm[precal_b]:7
          SalesForm[precal_dbl]:7
          SalesForm[precal_frametype]:Full Rim
          row0:0
          iditem0:18
          item_qty0:1
          row1:1
          iditem1:236
          item_qty1:1
          SalesForm[note]:ok sdh 1111
          SalesForm[subtotal1]:11.812.750
          SalesForm[disc_percent]:5
          SalesForm[disc_amount]:590.638
          SalesForm[subtotal2]:11.222.113
          SalesForm[tax_percent]:10
          SalesForm[tax_amount]:1.122.211
          SalesForm[total]:12.344.324
          do_edit:1
        */

        $form->attributes = Yii::app()->request->getParam('SalesForm');
        $aidproduk = Yii::app()->request->getParam('aip');
        $idsales = Yii::app()->request->getParam('id');
        $daftar_paket = Yii::app()->request->getParam('daftar_paket');

        //$arrip = explode (",", $aidproduk);
        $arrip = $aidproduk;
        $noofitem = sizeof($arrip);
        //echo "idsales = $idsales<br /><pre>";print_r($form);echo "</pre>";
        //exit();

        if($form->validate())
        {
          //form validated
          $Criteria = new CDbCriteria();
          $Criteria->condition = $this->tbl_id." = :id";
          $Criteria->params = array(':id' => $idsales);

          $sales = Sales::model()->with('SalesDetail','SalesCustomer','SalesPresc','SalesPrecal')->find($Criteria);

          $idcustomer = $form['customer_id'];
          $idresep = $form['presc_id'];
          $subtotal1 = str_replace(".", "", $form['subtotal1']);
          //$disc_percent = str_replace(".", "", $form['disc_percent']);
          $disc_percent = $form['disc_percent'];
          $disc_amount = str_replace(".", "", $form['disc_amount']);
          $subtotal2 = str_replace(".", "", $form['subtotal2']);
          $tax_percent = str_replace(".", "", $form['tax_percent']);
          $tax_amount = str_replace(".", "", $form['tax_amount']);
          $total = str_replace(".", "", $form['total']);

          //update pos_sales
          //$sales = new Sales();
          //$sales['order_no'] = $form['order_no'] = FHelper::GenerateOrderNo($idlokasi);
          $sales['customer_id'] = $idcustomer;
          $sales['presc_id'] = $idresep;
          //$sales['open_date'] = new CDbExpression('NOW()');
          //$sales['branch_id'] = $idlokasi;
          $sales['note'] = $form['note'];
          $sales['subtotal1'] = $subtotal1;
          $sales['disc_percent'] = $disc_percent;
          $sales['disc_amount'] = $disc_amount;
          $sales['subtotal2'] = $subtotal2;
          $sales['tax_percent'] = $tax_percent;
          $sales['tax_amount'] = $tax_amount;
          $sales['total'] = $total;
          $sales['num_of_item'] = $noofitem;
          $sales['balance'] = $total;
          //$sales['status'] = 'OPEN';
          $sales['date_update'] = new CDbExpression('NOW()');
          $sales['update_by'] = FHelper::GetUserName($userid_actor);
          $sales['version'] = $sales['version'] + 1;
          $sales['tanparesep'] = 0;
          
          $tanparesep = Yii::app()->request->getParam('chkTanpaResep');
          if( isset($tanparesep) )
          {
            $sales['tanparesep'] = 1;
          }
          $sales->update();

          //update pos_sales_customer

            //ambil record customer
            $Criteria = new CDbCriteria();
            $Criteria->condition = 'customer_id = :idcustomer';
            $Criteria->params = array(':idcustomer' => $idcustomer);
            $customer = mtr_customer::model()->with('customer_type','gender')->find($Criteria);

          //$sales_customer = new PosSalesCustomer();
          //$sales_customer['sales_id'] = $idsales;
          $sales->SalesCustomer['customer_id'] = $idcustomer;
          $sales->SalesCustomer['name'] = $form['cust_name'] = $customer['name'];
          $sales->SalesCustomer['type'] = $form['cust_type'] = $customer->customer_type['dsc'];
          $sales->SalesCustomer['gender'] = $form['cust_gender'] = $customer->gender['dsc'];
          $sales->SalesCustomer['mobile'] = $form['cust_mobile'] = $customer['mobile'];
          $sales->SalesCustomer['phone'] = $form['cust_phone'] = $customer['phone'];
          $sales->SalesCustomer['birth_place'] = $form['cust_pob'] = $customer['birth_place'];
          $sales->SalesCustomer['birth_date'] = $form['cust_dob'] = $customer['birth_date'];
          $sales->SalesCustomer['email'] = $form['cust_email'] = $customer['email'];
          $sales->SalesCustomer['address'] = $form['cust_addr'] = $customer['address'];
          $sales->SalesCustomer['city'] = $form['cust_city'] = $customer['city'];
          $sales->SalesCustomer->update();

          //update pos_sales_presc
          //ambil record resep terakhir
          $Criteria = new CDbCriteria();
          $Criteria->condition = 'presc_id = :idresep';
          $Criteria->params = array(':idresep' => $idresep);
          $resep = pos_customer_presc::model()->find($Criteria);
          
          $Criteria = new CDbCriteria();
          $Criteria->condition = 'presc_id = :idresep';
          $Criteria->params = array(':idresep' => $idresep);
          $Criteria->order = 'side';
          $resep_detail = pos_customer_presc_detail::model()->findAll($Criteria);

          //$sales_presc = new PosSalesPresc();
          //$sales_presc['sales_id'] = $idsales;
          $sales->SalesPresc['presc_id'] = $idresep;
          $sales->SalesPresc['presc_date'] = $form['presc_date'] = $resep['presc_date'];
          $sales->SalesPresc['examiner_name'] = $form['examiner_name'] = $resep->examiner['name'];
          $sales->SalesPresc['l_sph'] = $form['l_sph'] = $resep_detail[0]['sph'];
          $sales->SalesPresc['l_cyl'] = $form['l_cyl'] = $resep_detail[0]['cyl'];
          $sales->SalesPresc['l_axis'] = $form['l_axis'] = $resep_detail[0]['axis'];
          $sales->SalesPresc['l_prism'] = $form['l_prism'] = $resep_detail[0]['prism'];
          $sales->SalesPresc['l_base'] = $form['l_base'] = $resep_detail[0]['base'];
          $sales->SalesPresc['l_add'] = $form['l_add'] = $resep_detail[0]['add'];
          $sales->SalesPresc['l_dist_pd'] = $form['l_dist_pd'] = $resep_detail[0]['dist_pd'];
          $sales->SalesPresc['l_near_pd'] = $form['l_near_pd'] = $resep_detail[0]['near_pd'];
          $sales->SalesPresc['r_sph'] = $form['r_sph'] = $resep_detail[1]['sph'];
          $sales->SalesPresc['r_cyl'] = $form['r_cyl'] = $resep_detail[1]['cyl'];
          $sales->SalesPresc['r_axis'] = $form['r_axis'] = $resep_detail[1]['axis'];
          $sales->SalesPresc['r_prism'] = $form['r_prism'] = $resep_detail[1]['prism'];
          $sales->SalesPresc['r_base'] = $form['r_base'] = $resep_detail[1]['base'];
          $sales->SalesPresc['r_add'] = $form['r_add'] = $resep_detail[1]['add'];
          $sales->SalesPresc['r_dist_pd'] = $form['r_dist_pd'] = $resep_detail[1]['dist_pd'];
          $sales->SalesPresc['r_near_pd'] = $form['r_near_pd'] = $resep_detail[1]['near_pd'];
          $sales->SalesPresc->update();

          //update pos_sales_precal
          //$sales_precal = new PosSalesPrecal();
          //$sales->SalesPrecal['sales_id'] = $idsales;
          $sales->SalesPrecal['pvl'] = $form['precal_pvl'];
          $sales->SalesPrecal['pvr'] = $form['precal_pvr'];
          $sales->SalesPrecal['a'] = $form['precal_a'];
          $sales->SalesPrecal['b'] = $form['precal_b'];
          $sales->SalesPrecal['dbl'] = $form['precal_dbl'];
          $sales->SalesPrecal['frame_type'] = $form['precal_frametype'];
          $sales->SalesPrecal['bc'] = $form['precal_bc'];
          $sales->SalesPrecal['et'] = $form['precal_et'];
          $sales->SalesPrecal->update();

          //update item status to 'customer' (=4) based on pos_sales_det
          /*
            1. reset status item menjadi 3. berdasarkan pos_sales_det.sales_id
            2. kosongkan record pos_sales_det berdasarkan sales_id
            3. populasikan ulang pos_sales_det berdasarkan arrip
          */
          
          //1. reset status item menjadi 3. berdasarkan pos_sales_det.sales_id
            $Criteria = new CDbCriteria();
            $Criteria->condition = 'sales_id = :idsales';
            $Criteria->params = array(':idsales' => $idsales);
            $sales_dets = PosSalesDet::model()->findAll($Criteria);
            if(count($sales_dets) > 0) 
            {
              foreach($sales_dets as $sales_det)
              {
                if($sales_det['nobarcode'] == 0 && is_numeric($sales_det['barcode']))
                {
                  FHelper::ItemStatusUpdate(
                    $sales_det['item_id'], 
                    3, 
                    $idlokasi, 
                    Yii::app()->request->cookies['userid_actor']->value,
                    'Reset status item akibat update sales'
                  );
                }
                
                //menghapus record joborder
                $Criteria = new CDbCriteria();
                $Criteria->condition = 'id_sales = :idsalesdet';
                $Criteria->params = array(':idsalesdet' => $sales_det['sales_id']);
                inv_joborder::model()->deleteAll($Criteria);
              }
            }
          //1. reset status item menjadi 3. berdasarkan pos_sales_det.sales_id
          
          
          //2. kosongkan record pos_sales_det berdasarkan sales_id
            $Criteria = new CDbCriteria();
            $Criteria->condition = 'sales_id = :idsales';
            $Criteria->params = array(':idsales' => $idsales);
            PosSalesDet::model()->deleteAll($Criteria);
          //2. kosongkan record pos_sales_det berdasarkan sales_id
          
          
          //3. populasi ulang pos_sales_det berdasarkan $arrip - begin
          
            //save new pos_sales_det - begin
              //$disc_amount = (float)$hargajual['diskon']/(float)100 * (float)$hargajual['harga_jual'];
              //$hargajualnet = 1 * ((float)($hargajual['harga_jual'] - ((float)$hargajual['diskon']/(float)100 * (float)$hargajual['harga_jual'])));
          
              $item_count = 0;
              if( count($arrip) > 0)
              {
                
                foreach($arrip as $idproduk) 
                {
                  if( $idproduk['idinventory'] != '' )
                  {
                    
                    //$item_qty = $idproduk['qty'];
                    $item_qty = 1;
                    $item_jo = Yii::app()->request->getParam('item_jo'.$idproduk['idinventory']);
              
                    //Simpan informasi Sales Detail - begin
                    
                        //ambil info produk
                        
                        if($idproduk['byname'] == 1 && is_numeric($idproduk['barcode']) == false) //entry produk berdasarkan nama
                        {
                          //ambil info produk
                            $CriteriaIP = new CDbCriteria();
                            $CriteriaIP->condition = 'id = :idproduk';
                            $CriteriaIP->params = array(':idproduk' => (int)$idproduk['idinventory']); //idproduk
                            $produk = inv_inventory::model()->find($CriteriaIP);
                          
                          //ambil harga jual
                            $CriteriaHJ = new CDbCriteria();
                            $CriteriaHJ->condition = 'id_item = :idproduk AND id_toko = :idlokasi';
                            $CriteriaHJ->params = array(':idlokasi' => $idlokasi, ':idproduk' => $produk['id']);
                            $hargajual = inv_harga_jual::model()->find($CriteriaHJ);
                        }
                        else //entry produk berdasarkan barcode
                        {
                          //ambil info produk berdasarkan id individu
                            $CriteriaIP = new CDbCriteria();
                            $CriteriaIP->condition = 'id = :idproduk';
                            $CriteriaIP->params = array(':idproduk' => (int)$idproduk['iditem']); //id individu
                            $item = inv_item::model()->find($CriteriaIP);
                            $produk = $item->produk;
                          
                          //ambil harga jual
                            $CriteriaHJ = new CDbCriteria();
                            $CriteriaHJ->condition = 'id_item = :idproduk AND id_toko = :idlokasi';
                            $CriteriaHJ->params = array(':idlokasi' => $idlokasi, ':idproduk' => $produk['id']);
                            $hargajual = inv_harga_jual::model()->find($CriteriaHJ);
                        }
                        
                        $sales_det = new PosSalesDet();
                        $sales_det['sales_id'] = $idsales;
                        
                        if($idproduk['byname'] == 1)
                        {
                          $sales_det['item_id'] = $idproduk['idinventory'];
                        }
                        else
                        {
                          $sales_det['item_id'] = $idproduk['iditem'];
                        }
                        
                        
                        $sales_det['nobarcode'] = $idproduk['byname'];
                        $sales_det['item_cid'] = $produk['idkategori'];
                        $sales_det['barcode'] = ($idproduk['byname'] == 1 ? '---' : $item['barcode']);
                        $sales_det['name'] = $produk['nama'];
                        $sales_det['quantity'] = $item_qty;
                        $sales_det['price'] = $hargajual['harga_jual'];
                        $sales_det['disc_percent'] = $hargajual['diskon'];
                  
                        $diskonamt = $hargajual['diskon']/100 * $hargajual['harga_jual'];
                        $harganet = $item_qty * ($hargajual['harga_jual'] - $diskonamt);
                  
                        $sales_det['disc_amount'] = $diskonamt;
                        $sales_det['total_price'] = $harganet;
                        $sales_det['act_by'] = '0';
                        $sales_det['jo_id'] = $item_jo;
                        $sales_det['is_free'] = 'N';
                        $sales_det['is_canceled'] = 'N';
                        $sales_det['is_printed'] = 'N';
                        $sales_det->save();
                        
                        $idsalesdet = $sales_det->getPrimaryKey();
                        
                        //gunakan logic tersendiri untuk menyimpan item-item paket
                        
                        $the_paket = null;
                        if($idproduk['is_paket'] == 1)
                        {
                          //ambil info transaksi paket
                          foreach($daftar_paket as $data_paket)
                          {
                            if($data_paket['iditem'] == $idproduk['iditem'])
                            {
                              $the_paket = $data_paket;
                              break;
                            }
                          }
                          
                          $data_paket = $daftar_paket[$idproduk['idinventory']];
                          
                          //hapus detil transaksi paket yang ada dalam tabel pos_sales_det_paket
                          pos_sales_det_paket::model()->deleteAll(
                            "sales_det_id = :idsalesdet",
                            array(
                              ":idsalesdet" => $idsalesdet
                            )
                          ); 
                          
                          //insert record-record transaksi paket - begin
                          
                            foreach($the_paket['daftarproduk'] as $data_item_paket)
                            {
                              $daftar_barcode = $data_item_paket['daftarbarcode'];
                              
                              foreach($daftar_barcode as $data_barcode)
                              {
                                $info_iditem = FHelper::GetIdItemByBarcode($data_barcode['barcode']);
                                
                                //tandai status item
                                FHelper::ItemStatusUpdate(
                                  $info_iditem, 
                                  4, 
                                  $idlokasi, 
                                  Yii::app()->request->cookies['userid_actor']->value,
                                  'Pencatatan status item akibat save sales',
                                  $idsales
                                );
                                
                                $pos_sales_det_paket = new pos_sales_det_paket();
                                $pos_sales_det_paket['sales_det_id'] = $idsalesdet;
                                $pos_sales_det_paket['iditem'] = $info_iditem;
                                $pos_sales_det_paket['idinventory'] = $data_barcode['idproduk'];
                                $pos_sales_det_paket->save();
                              }
                              
                                
                            }//loop item-item suatu paket
                          
                          //insert record-record transaksi paket - end
                        }
                    
                    //Simpan informasi Sales Detail - end
                    
                    
                    
                    
                    //Update status item - begin
                    
                      if($idproduk['byname'] == 0 && $idproduk['is_paket'] == 0)
                      {
                        Yii::log("Set status = 4; iditem = {$idproduk['iditem']}", 'info');
                        
                        FHelper::ItemStatusUpdate(
                          $idproduk['iditem'], 
                          4, 
                          $idlokasi, 
                          Yii::app()->request->cookies['userid_actor']->value,
                          'Pencatatan item akibat update sales',
                          $idsales
                        );
                      }
                    
                    //Update status item - end
                        
                    
                    
                    // Simpan informasi job order - begin
                    
                      if($idproduk['byname'] == 1 && $idproduk['is_paket'] == 0)
                      {
                        $this->MakeJobOrder(
                          $idsales,
                          $idsalesdet,
                          $idproduk['idinventory'], 
                          $idproduk['idlokasipengolah'],
                          $idproduk['kirikanan'],
                          $idproduk['catatan'],
                          $idproduk['prioritas']
                        );
                      }
                    
                    // Simpan informasi job order - end
                    
                    
              
                    $item_count++;
                    
                  } //hanya jika record sales_det valid
                    
                } //loop array of produk
                
              } // if( count($arrip) > 0)
              
            //save new pos_sales_det - end
              
          //3. populasi ulang pos_sales_det berdasarkan $arrip - end

          
          //AuditLog
          $data = "$sales[order_no], $sales[customer_id], $sales[presc_id], $sales[open_date], $sales[branch_id], $sales[note], $sales[subtotal1], $sales[disc_percent], $sales[disc_amount], $sales[subtotal2],
          $sales[tax_percent], $sales[tax_amount], $sales[total], $sales[num_of_item], $sales[balance], $sales[status], $sales[date_created], $sales[created_by]";

          FAudit::add('PENJUALAN', 'Edit', FHelper::GetUserName($userid_actor), $data);

          $Criteria = new CDbCriteria();
          $Criteria->condition = $this->tbl_id." = :id";
          $Criteria->params = array(':id' => $idsales);

          $rec = Sales::model()->with('SalesDetail','SalesCustomer','SalesPresc','SalesPrecal')->find($Criteria);

          $bread_crumb_list =
          '<li>Sales</li>'.
          '<li>></li>'.
          '<li><a href="#" onclick="ShowList('.$userid_actor.');">Penjualan</a></li>'.
          '<li>></li>'.
          '<li>View Penjualan</li>';

          $success_message =
          '<div class="notification note-success">'.
          '<a href="#" class="close" title="Close notification">close</a>'.
          '<p><strong>Success notification:</strong> Data '.$sales['order_no'].' berhasil diupdate</p>'.
          '</div>';

          $html = $this->renderPartial(
              'view',
              array(
                  'form' => $form,
                  'userid_actor' => $userid_actor,
                  'id' => $idsales,
                  'rec' => $rec,
                  'menuid' => $menuid
              ),
              true
          );
        }
        else
        {
          $daftar_lokasi = FHelper::GetLocationListData(false);
          
          $dialog_job_order = $this->renderPartial(
            'v_dialog_job_order',
            array(
              'daftar_lokasi' => $daftar_lokasi
            ),
            true
          );
          
          $dialog_info_job_order = $this->renderPartial(
            'v_dialog_info_job_order',
            array(
              'daftar_lokasi' => $daftar_lokasi
            ),
            true
          );
          
          //form not validated
          $bread_crumb_list =
          '<li>Sales</li>'.
          '<li>></li>'.
          '<li><a href="#" onclick="ShowList('.$userid_actor.');">Penjualan</a></li>'.
          '<li>></li>'.
          '<li>Edit Penjualan</li>';

          $html = $this->renderPartial(
            'edit',
            array(
              'form' => $form,
              'userid_actor' => $userid_actor,
              'id' => $id,
              'active_option' => $active_option,
              'menuid' => $menuid,
              'dialog_job_order' => $dialog_job_order,
              'dialog_info_job_order' => $dialog_info_job_order,
              'daftar_lokasi' => $daftar_lokasi
              ),
            true
          );
        }
      }
      else
      {
        //back to list
        $userid_actor = Yii::app()->request->getParam('userid_actor');
        $recs = Sales::model()->findAll();

        $this->layout = 'layout-baru';

        $html = $this->renderPartial(
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
      //show edit sales form
      
      //cek hak akses untuk edit...
      if(FHelper::AllowMenu($menuid, FHelper::GetGroupId($userid_actor), 'edit'))
      {
        Yii::log('do_edit not set', 'info');

        $idsales = Yii::app()->request->getParam('id');

        $Criteria = new CDbCriteria();
        $Criteria->condition = $this->tbl_id." = :id";
        $Criteria->params = array(':id' => $idsales);

        Yii::log('idsales = ' . $idsales, 'info');

        $rec = Sales::model()
          ->with('SalesDetail','SalesCustomer','SalesPresc','SalesPrecal')
          ->find($Criteria);

        $bread_crumb_list =
          '<li>Sales</li>'.
          '<li>></li>'.
          '<li><a href="#" onclick="ShowList('.$userid_actor.');">Penjualan</a></li>'.
          '<li>></li>'.
          '<li>Edit Penjualan</li>';
        
        $daftar_lokasi = FHelper::GetLocationListData(false);
        
        $dialog_job_order = $this->renderPartial(
          'v_dialog_job_order',
          array(
            'daftar_lokasi' => $daftar_lokasi
          ),
          true
        );
        
        $dialog_info_job_order = $this->renderPartial(
          'v_dialog_info_job_order',
          array(
            'daftar_lokasi' => $daftar_lokasi
          ),
          true
        );
        
        $dialog_entry_paket = $this->renderPartial(
          'v_dialog_entry_paket',
          array(),
          true
        );
        
        $html = $this->renderPartial(
          'edit',
          array(
            'form' => $form,
            'userid_actor' => $userid_actor,
            'menuid' => $menuid,
            'rec' => $rec,
            'frametype_list' => $frametype_list,
            'bc_list' => $bc_list,
            'dialog_job_order' => $dialog_job_order,
            'dialog_info_job_order' => $dialog_info_job_order,
            'daftar_lokasi' => $daftar_lokasi,
            'dialog_entry_paket' => $dialog_entry_paket
          ),
          true
        );
      }
      else
      {
        //tidak ada hak akses untuk melakukan edit...
        
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
        /*
        $url = $this->createUrl(
              'index/showinvalidaccess',
              array('userid_actor' => $userid_actor)
            );

        $this->redirect($url);
        */
      }
    }

    echo CJSON::encode(
      array(
        'html' => $html,
        'bread_crumb_list' => $bread_crumb_list,
        'notification_message' => $success_message
      )
    );
	}
	
	/*
	  actionPasangBarcode
	  
	  Deskripsi
	  Fungsi untuk memasakan barcode ke entry penjualan yang belum ada barcode.
	  
	  Parameter
	  id
	    Integer
	    id pos_sales_det
	  barcode
	    String
	    Barcode yang dibacakan user.
	   
	  Return
	  Object JSON dengan elemen: 
	  status : ok - idproduk berdasarkan barcode cocok dengan idproduk yang di-entry sebelumnya
	           not ok - idproduk berdasarkan barcode TIDAK cocok dengan idproduk yang di-entry sebelumnya
	  
	  
	*/
	public function actionPasangBarcode()
	{
		$idjoborder = Yii::app()->request->getParam('idjoborder');
		$pos_sales_det_id = Yii::app()->request->getParam('idsalesdet');
		$barcode = Yii::app()->request->getParam('barcode');
		$produk = FHelper::GetProdukByBarcode($barcode);
		$iditem = FHelper::GetIdItemByBarcode($barcode);

		$idlokasi = Yii::app()->request->cookies['idlokasi']->value;
		$iduser = Yii::app()->request->cookies['userid_actor']->value;

		//ambil info pos_sales_det
		$command = Yii::app()->db->createCommand()
		->select('*')
		->from('pos_sales_det')
		->where(
		'id = :pos_sales_det_id', 
		array(':pos_sales_det_id' => $pos_sales_det_id));
		$data = $command->queryRow();
		$idsales = $data['sales_id'];
		$idprodukawal = $data['item_id'];

		Yii::log("idprodukawal = {$data['item_id']} vs produk by barcode = {$produk['id']}", 'info');

		if($produk['id'] == $idprodukawal)
		{
			//pastikan barcode valid
			if(FHelper::IsBarcodeValid($barcode, $idlokasi))
			{
				//ok...
				$status = 'ok';

				//update pos_sales_det
				Yii::app()->db->createCommand()
				->update(
					'pos_sales_det',
					array(
						'item_id' => $iditem,
						'nobarcode' => '0'
					),
					'id = :pos_sales_det_id',
					array(
						':pos_sales_det_id' => $pos_sales_det_id
					)
				);

				//update status inv_item &  status_history
				FHelper::ItemStatusUpdate(
				  $iditem, 
				  4, 
				  $idlokasi, 
				  $iduser,
				  'Pencatatan sales item akibat job order',
				  $idsales
        );
			}
			else
			{
				//barcode tidak valid
				$status = 'not ok';
				$pesan = 'Barcode tidak valid.';
			}
		}
		else
		{
			//not ok
			//kembalikan peringatan

			$status = 'not ok';
		}
	  
		echo CJSON::encode(array(
			'status' => $status, 
			'iditem' => $iditem, 
			'idsalesdet' => $pos_sales_det_id)
		);
	}

	public function actionCancel()
	{
	  	$menuid = 22;

		$userid_actor = Yii::app()->request->getParam('userid_actor');
		$idlokasi = Yii::app()->request->cookies['idlokasi']->value;

		$idsales = Yii::app()->request->getParam('id');

		$Criteria = new CDbCriteria();
		$Criteria->condition = $this->tbl_id." = :id";
		$Criteria->params = array(':id' => $idsales);

		$sales = Sales::model()->with('SalesDetail')->find($Criteria);
		$recs = $sales->SalesDetail;

		//update pos_sales
		$sales['status'] = 'CANCEL';
		$sales['date_update'] = new CDbExpression('NOW()');
		$sales['update_by'] = FHelper::GetUserName($userid_actor);
		$sales['version'] = $sales['version'] + 1;
		$sales->update();

		//update item status to 'in' (=3) based on pos_sales_det
		$Criteria = new CDbCriteria();
		$Criteria->condition = 'sales_id = :idsales';
		$Criteria->params = array(':idsales' => $idsales);

		if(count($recs) > 0) {
			foreach($recs as $rec)
			{
				$idproduk = $rec['item_id'];

				$Criteria = new CDbCriteria();
				$Criteria->condition = 'id = :idproduk';
				$Criteria->params = array(':idproduk' => $idproduk);
				$item = inv_item::model()->find($Criteria);

				//Set status item to 'in' (=3)
				$item['idstatus'] = 3;
				$item->update();
			}

			//AuditLog
			$data = "$sales[order_no], $sales[customer_id], $sales[presc_id], $sales[open_date], $sales[branch_id], $sales[note], $sales[subtotal1], $sales[disc_percent], $sales[disc_amount], $sales[subtotal2],
			$sales[tax_percent], $sales[tax_amount], $sales[total], $sales[num_of_item], $sales[balance], $sales[status], $sales[date_created], $sales[created_by]";

			FAudit::add('PENJUALAN', 'Del', FHelper::GetUserName($userid_actor), $data);

			$Criteria = new CDbCriteria();
			$Criteria->condition = $this->tbl_id." = :id";
			$Criteria->params = array(':id' => $idsales);

			$rec = Sales::model()->with('SalesDetail','SalesCustomer','SalesPresc','SalesPrecal')->find($Criteria);

			$bread_crumb_list =
			'<li>Sales</li>'.
			'<li>></li>'.
			'<li><a href="#" onclick="ShowList('.$userid_actor.');">Penjualan</a></li>'.
			'<li>></li>'.
			'<li>View Penjualan</li>';

			$success_message =
			'<div class="notification note-success">'.
			'<a href="#" class="close" title="Close notification">close</a>'.
			'<p><strong>Success notification:</strong> Data '.$sales['order_no'].' berhasil dicancel</p>'.
			'</div>';

			$html = $this->renderPartial(
				'view',
				array(
					'form' => $form,
					'userid_actor' => $userid_actor,
					'id' => $idsales,
					'rec' => $rec,
					'menuid' => $menuid
				),
				true
			);
		}

		echo CJSON::encode(
			array(
				'html' => $html,
				'bread_crumb_list' => $bread_crumb_list,
				'notification_message' => $success_message
			)
		);
	}
	
	public function actionPayment()
	{
	  $menuid = 22;

		$userid_actor = Yii::app()->request->getParam('userid_actor');
		$idlokasi = Yii::app()->request->cookies['idlokasi']->value;

		$idsales = Yii::app()->request->getParam('id');
		
		$Criteria = new CDbCriteria();
		$Criteria->condition = $this->tbl_id." = :id";
		$Criteria->params = array(':id' => $idsales);

	  $sales = Sales::model()->with('SalesDetail')->find($Criteria);

		//update pos_sales
		$sales['invoice_no'] = $form['invoice_no'] = FHelper::GenerateInvoiceNo($idlokasi);
		$sales['status'] = 'BAYAR';
		$sales['date_update'] = new CDbExpression('NOW()');
		$sales['update_by'] = FHelper::GetUserName($userid_actor);
		$sales['version'] = $sales['version'] + 1;
		$sales->update();

		//AuditLog
		$data = "$sales[order_no], $sales[customer_id], $sales[presc_id], $sales[open_date], $sales[branch_id], $sales[note], $sales[subtotal1], $sales[disc_percent], $sales[disc_amount], $sales[subtotal2],
		$sales[tax_percent], $sales[tax_amount], $sales[total], $sales[num_of_item], $sales[balance], $sales[status], $sales[date_created], $sales[created_by]";

		FAudit::add('PENJUALAN', 'Edit', FHelper::GetUserName($userid_actor), $data);

		$Criteria = new CDbCriteria();
		$Criteria->condition = $this->tbl_id." = :id";
		$Criteria->params = array(':id' => $idsales);

		$rec = Sales::model()->with('SalesDetail','SalesCustomer','SalesPresc','SalesPrecal')->find($Criteria);

		$bread_crumb_list =
		'<li>Sales</li>'.
		'<li>></li>'.
		'<li><a href="#" onclick="ShowList('.$userid_actor.');">Penjualan</a></li>'.
		'<li>></li>'.
		'<li>View Penjualan</li>';

		$success_message =
		'<div class="notification note-success">'.
		'<a href="#" class="close" title="Close notification">close</a>'.
		'<p><strong>Success notification:</strong> Data '.$sales['order_no'].' berhasil dikirim untuk pembayaran</p>'.
		'</div>';

		$html = $this->renderPartial(
			'view',
			array(
				'form' => $form,
				'userid_actor' => $userid_actor,
				'id' => $idsales,
				'rec' => $rec,
				'menuid' => $menuid
			),
			true
		);

		echo CJSON::encode(
			array(
				'html' => $html,
				'id' => $idsales,
				'bread_crumb_list' => $bread_crumb_list,
				'notification_message' => $success_message
			)
		);
	}

	function actionTambahJoborder()
	{
		$userid_actor = Yii::app()->request->cookies['userid_actor']->value;
		$idlokasi = Yii::app()->request->cookies['idlokasi']->value;

		$idproduk = Yii::app()->request->getParam('iditem');
		$idpresc = Yii::app()->request->getParam('idpresc');

		$Criteria = new CDbCriteria();
		$Criteria->condition = 'id = :idproduk';
		$Criteria->params = array(':idproduk' => $idproduk);
		$item = inv_item::model()->find($Criteria);
		$produk = $item->produk;

		$Criteria = new CDbCriteria();
		$Criteria->condition = 'id_item = :idproduk AND id_toko = :idlokasi';
		$Criteria->params = array(':idlokasi' => $idlokasi, ':idproduk' => $idproduk);
		$hargajual = inv_harga_jual::model()->find($Criteria);

		Yii::log('idproduk = ' . $idproduk, 'info');
		Yii::log('idlokasi = ' . $idlokasi, 'info');

		$diskonamt = $hargajual['diskon']/100 * $hargajual['harga_jual'];
		$harganet = 1 * ($hargajual['harga_jual'] - $diskonamt);

		//create jo
		$joborder = new InvJoborder();
		$joborder['waktu'] = new CDbExpression('NOW()');
		$joborder['sales_det_id'] = $idproduk;
		$joborder['sales_presc_id'] = $idpresc;
		$joborder->save();

		$idjo = $joborder->getPrimaryKey();

		$status = 'ok';
		echo CJSON::encode(
			array(
				 'status' => $status,
				 'iditem' => $item['id'],
				 'idkategori' => $produk['idkategori'],
				 'barcode' => $item['barcode'],
				 'nama' => $produk['nama'],
				 'jumlah' => 1,
				 'harga' => number_format($hargajual['harga_jual'], 0, '.', '.'),
				 'diskon' => number_format($hargajual['diskon'], 0, '.', '.'),
				 'diskonamt' => number_format($diskonamt, 0, '.', '.'),
				 'harganet' => number_format($harganet, 0, '.', '.'),
				 'idjo' => $idjo
			)
		);
	}

	function actionTambahJoborder2_obsolete()
	{
		$menuid = 22;
		$userid_actor = Yii::app()->request->getParam('userid_actor');

		$allow_read = FHelper::AllowMenu($menuid, FHelper::GetGroupId($userid_actor), 'read');
		if ($allow_read) {
			$idsales = Yii::app()->request->getParam('idsales');
			$idsalesdet = Yii::app()->request->getParam('idsalesdet');
			$idpresc = Yii::app()->request->getParam('idpresc');

			//create jo
			$joborder = new InvJoborder();
			$joborder['waktu'] = new CDbExpression('NOW()');
			$joborder['sales_det_id'] = $idsalesdet;
			$joborder['sales_presc_id'] = $idpresc;
			$joborder->save();

			$idjo = $joborder->getPrimaryKey();

			//AuditLog
			$data = "$idjo, $joborder[waktu], $rec[sales_det_id], $rec[sales_presc_id]";

			FAudit::add('JOBORDER', 'Add', FHelper::GetUserName($userid_actor), $data);

			//update idjo di sales detail
			$Criteria = new CDbCriteria();
			$Criteria->condition = "id = :id";
			$Criteria->params = array(':id' => $idsalesdet);

			$sales_det = PosSalesDet::model()->find($Criteria);

			$sales_det['jo_id'] = $idjo;
			$sales_det->update();

			//AuditLog
			$data = "$idsalesdet, $sales_det[jo_id]";

			FAudit::add('SALESDETAIL', 'Edit', FHelper::GetUserName($userid_actor), $data);

			//view sales
			$Criteria = new CDbCriteria();
			$Criteria->condition = $this->tbl_id." = :id";
			$Criteria->params = array(':id' => $idsales);

			$rec = Sales::model()->with('SalesDetail','SalesCustomer','SalesPresc','SalesPrecal')->find($Criteria);

			$bread_crumb_list =
			'<li>Sales</li>'.
			'<li>></li>'.
			'<li><a href="#" onclick="ShowList('.$userid_actor.');">Penjualan</a></li>'.
			'<li>></li>'.
			'<li>View Penjualan</li>';

			$html = $this->renderPartial(
					'view',
					array(
							'form' => $form,
							'userid_actor' => $userid_actor,
							'id' => $idsales,
							'rec' => $rec,
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
			$data = "$rec[sales_id], $rec[order_no], $rec[invoice_no], $rec[customer_id], $rec[open_date], $rec[close_date], ".
					"$rec[branch_id], $rec[table_no], $rec[pax], $rec[note], $rec[subtotal1], $rec[disc_percent], $rec[disc_amount], ".
					"$rec[subtotal2], $rec[tax_percent], $rec[tax_amount], $rec[total], $rec[num_of_item], $rec[balance], $rec[status], ".
					"$rec[date_created], $rec[created_by], $rec[date_update], $rec[update_by], $rec[version]";

			FAudit::add('PENJUALAN', 'View', FHelper::GetUserName($userid_actor), $data);
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
	}
	
	/*
	  actionTambahJoborder2()
	  
	  Deskripsi
	  Fungsi untuk membuat record job order baru. Dan mengembalikan id_job_order
	*/
	function actionTambahJoborder2()
	{
		//create jo
    $joborder = new inv_joborder();
    $joborder['waktu_dibikin'] = new CDbExpression('NOW()');
    $joborder->save();

    $idjo = $joborder->getPrimaryKey();
    
    echo CJSON::encode( array('idjoborder' => $idjo) );
	}

	function actionHapusJoborder()
	{
		$userid_actor = Yii::app()->request->getParam('userid_actor');
		$idlokasi = Yii::app()->request->cookies['idlokasi']->value;

		$idproduk = Yii::app()->request->getParam('iditem');
		$idjo = Yii::app()->request->getParam('idjo');

		$Criteria = new CDbCriteria();
		$Criteria->condition = 'id = :idproduk';
		$Criteria->params = array(':idproduk' => $idproduk);
		$item = inv_item::model()->find($Criteria);
		$produk = $item->produk;

		$Criteria = new CDbCriteria();
		$Criteria->condition = 'id_item = :idproduk AND id_toko = :idlokasi';
		$Criteria->params = array(':idlokasi' => $idlokasi, ':idproduk' => $idproduk);
		$hargajual = inv_harga_jual::model()->find($Criteria);

		Yii::log('idproduk = ' . $idproduk, 'info');
		Yii::log('idlokasi = ' . $idlokasi, 'info');

		$diskonamt = $hargajual['diskon']/100 * $hargajual['harga_jual'];
		$harganet = 1 * ($hargajual['harga_jual'] - $diskonamt);

		//update jo
		$Criteria = new CDbCriteria();
		$Criteria->condition = 'id = :idjo';
		$Criteria->params = array(':idjo' => $idjo);
		$joborder = InvJoborder::model()->find($Criteria);

		//Set is_del = 0
		$joborder['is_del'] = 1;
		$joborder->update();

		$status = 'ok';
		echo CJSON::encode(
			array(
				 'status' => $status,
				 'iditem' => $item['id'],
				 'idkategori' => $produk['idkategori'],
				 'barcode' => $item['barcode'],
				 'nama' => $produk['nama'],
				 'jumlah' => 1,
				 'harga' => number_format($hargajual['harga_jual'], 0, '.', '.'),
				 'diskon' => number_format($hargajual['diskon'], 0, '.', '.'),
				 'diskonamt' => number_format($diskonamt, 0, '.', '.'),
				 'harganet' => number_format($harganet, 0, '.', '.'),
				 'idjo' => $idjo
			)
		);
	}

	/*
	  actionHapusJoborder2()
	  
	  Deskripsi
	  Fungsi untuk menghapus job order berdasarkan idjoborder.
	  
	  Parameter
	  idjoborder - integer. Identitas job order yang menjadi fokus.
	*/
	function actionHapusJoborder2()
	{
		$idjoborder = Yii::app()->request->getParam('idjoborder');
		
		try
		{
		  $hasil = Yii::app()->db->createCommand()
		    ->update(
		      'inv_joborder',
		      array(
		        'is_del' => '1'
          ),
		      'id = :idjoborder',
		      array(
		        ':idjoborder' => $idjoborder
          )
        );
        
      if($hasil == 1)
      {
        $status = 'ok';
      }
      else
      {
        $status = 'not ok';
        $pesan = 'Gagal menghapus record job order';
      }
		}
		catch(Exception $e)
		{
		  $status = 'not ok';
		  $pesan = 'Kesalahan dalam menghapus record job order';
		}
		
		echo CJSON::encode(
		  array(
		    'status' => $status,
		    'pesan' => $pesan,
		    'exception' => $e
      )
    );
	}
	
	/*
	  actionHapusJoborder2()
	  
	  Deskripsi
	  Fungsi untuk menghapus job order berdasarkan idjoborder.
	  
	  Parameter
	  idjoborder - integer. Identitas job order yang menjadi fokus.
	*/
	function actionHapusJoborder2_obsolete()
	{
		$menuid = 22;
		$userid_actor = Yii::app()->request->getParam('userid_actor');

		$allow_read = FHelper::AllowMenu($menuid, FHelper::GetGroupId($userid_actor), 'read');
		if ($allow_read) {
			$idsales = Yii::app()->request->getParam('idsales');
			$idsalesdet = Yii::app()->request->getParam('idsalesdet');
			$idjo = Yii::app()->request->getParam('idjo');

			//delete jo
			$Criteria = new CDbCriteria();
			$Criteria->condition = 'id = :idjo';
			$Criteria->params = array(':idjo' => $idjo);
			$joborder = InvJoborder::model()->find($Criteria);

			//Set is_del = 0
			$joborder['is_del'] = 1;
			$joborder->update();

			//AuditLog
			$data = "$idjo, $joborder[waktu], $rec[sales_det_id], $rec[sales_presc_id]";

			FAudit::add('JOBORDER', 'Del', FHelper::GetUserName($userid_actor), $data);

			//update idjo di sales detail
			$Criteria = new CDbCriteria();
			$Criteria->condition = "id = :id";
			$Criteria->params = array(':id' => $idsalesdet);

			$sales_det = PosSalesDet::model()->find($Criteria);

			$sales_det['jo_id'] = 0;
			$sales_det->update();

			//AuditLog
			$data = "$idsalesdet, $sales_det[jo_id]";

			FAudit::add('SALESDETAIL', 'Edit', FHelper::GetUserName($userid_actor), $data);

			//view sales
			$Criteria = new CDbCriteria();
			$Criteria->condition = $this->tbl_id." = :id";
			$Criteria->params = array(':id' => $idsales);

			$rec = Sales::model()->with('SalesDetail','SalesCustomer','SalesPresc','SalesPrecal')->find($Criteria);

			$bread_crumb_list =
			'<li>Sales</li>'.
			'<li>></li>'.
			'<li><a href="#" onclick="ShowList('.$userid_actor.');">Penjualan</a></li>'.
			'<li>></li>'.
			'<li>View Penjualan</li>';

			$html = $this->renderPartial(
					'view',
					array(
							'form' => $form,
							'userid_actor' => $userid_actor,
							'id' => $idsales,
							'rec' => $rec,
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
			$data = "$rec[sales_id], $rec[order_no], $rec[invoice_no], $rec[customer_id], $rec[open_date], $rec[close_date], ".
					"$rec[branch_id], $rec[table_no], $rec[pax], $rec[note], $rec[subtotal1], $rec[disc_percent], $rec[disc_amount], ".
					"$rec[subtotal2], $rec[tax_percent], $rec[tax_amount], $rec[total], $rec[num_of_item], $rec[balance], $rec[status], ".
					"$rec[date_created], $rec[created_by], $rec[date_update], $rec[update_by], $rec[version]";

			FAudit::add('PENJUALAN', 'View', FHelper::GetUserName($userid_actor), $data);
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
	}
	
	/*
	  actionSimpanJoborder2()
	  
	  Deskripsi
	  Fungsi untuk mengupdate informasi job order berdasarkan idjoborder.
	  
	  Parameter
	  idjoborder - integer. id_joborder yang menjadi fokus fungsi ini
	  idlokasipengolah - integer. id lokasi yang dipilih sebagai tempat pengolahan
	  kirikanan - integer. menunjukkan apakah proses kiri/kanan
	  catatan - string. diisi dengan catatan untuk pihak lab.
	  prioritas - integer. menunjukkan prioritas pekerjaan
	*/
	public function actionSimpanJoborder2()
	{
	  try
	  {
	    $hasil = Yii::app()->db->createCommand()
        ->update(
          'inv_joborder',
          array(
            'idlokasi_pengolah' => Yii::app()->request->getParam('idlokasipengolahan'),
            'leftright' => Yii::app()->request->getParam('kirikanan'),
            'catatan' => Yii::app()->request->getParam('catatan'),
            'prioritas' => Yii::app()->request->getParam('prioritas'),
            'idstatus' => 2
          ),
          'id = :idjoborder',
          array(
            ':idjoborder' => Yii::app()->request->getParam('idjoborder')
          )
        );
        
      if($hasil == 1)
      {
        $status = 'ok';
      }
      else
      {
        $status = 'not ok';
        $pesan = 'Job order tidak ditemukan';
      }
	  }
	  catch(Exception $e)
	  {
	    $status = 'not ok';
      $pesan = 'Kesalahan dalam meng-update informasi job order.';
      $exception = $e;
	  }
      
	  echo CJSON::encode(
	    array(
	      'status' => $status,
	      'pesan' => $pesan,
	      'exception' => $exception
      )
    );
	}
	
	/*
	  MakeJobOrder($idsales, $idsalesdet, $idinventory, $idlokasipengolah, $kirikanan, $catatan, $prioritas)
	  
	  Deskripsi
	  Fungsi untuk membuat informasi job order berdasarkan idjoborder.
	  
	  Parameter
	  idsales - integer. id_sales yang menjadi fokus fungsi ini
	  idsalesdet - integer. id_sales_det yang menjadi fokus fungsi ini
	  idlokasipengolah - integer. id lokasi yang dipilih sebagai tempat pengolahan
	  kirikanan - integer. menunjukkan apakah proses kiri/kanan
	  catatan - string. diisi dengan catatan untuk pihak lab.
	  prioritas - integer. menunjukkan prioritas pekerjaan
	*/
	public function MakeJobOrder($idsales, $idsalesdet, $idinventory, $idlokasipengolah, 
	  $kirikanan, $catatan, $prioritas)
	{
	  try
	  {
	    $iduser = Yii::app()->request->cookies['userid_actor']->value;
	    $criteria = new CDbCriteria();
	    $criteria->condition = "id = :iduser";
	    $criteria->params = array(':iduser' => $iduser);
	    $user = sys_user::model()->find($criteria);
	    
	    try
	    {
	      $joborder = new inv_joborder();
        $joborder['id_sales'] = $idsalesdet;
        $joborder['idinventory_pesan'] = $idinventory;
        $joborder['idlokasi_pengolah'] = $idlokasipengolah;
        $joborder['idlokasi_penerbit'] = $user['id_location'];
        $joborder['leftright'] = $kirikanan;
        $joborder['catatan'] = $catatan;
        $joborder['prioritas'] = $prioritas;
        $joborder['waktu_dibikin'] = date('Y-m-j H:i:s');
        $joborder['idstatus'] = 2;
        $joborder->save();
        $idjoborder = $joborder->getPrimaryKey();
        
        //update inv_sales_det dengan idjoborder
        
        Yii::app()->db->createCommand()
          ->update(
            'pos_sales_det',
            array(
              'jo_id' => $idjoborder
            ),
            "id = :idsalesdet",
            array(
              ':idsalesdet' => $idsalesdet
            )
          );
        
        $status = 'ok';
	    }
	    catch(Exception $e)
	    {
	      $status = 'not ok';
	      $pesan = 'Job order gagal dibuat';
	    }
	  }
	  catch(Exception $e)
	  {
	    $status = 'not ok';
      $pesan = 'Kesalahan dalam membuat informasi job order.';
      $exception = $e;
	  }
      
	  return array(
      'status' => $status,
      'pesan' => $pesan,
      'exception' => $exception
    );
	}

	/*
	     actionGetCustomerList

	     Deskripsi
	     Fungsi untuk mengambil record untuk ditampilkan sebagai combo box di
	     interface sales.

	     Parameter
	     ednamacustomer
	          String. Nama customer.

          Return
          Drop down list dalam format html, dibungkus json.
	*/

	public function actionGetCustomerList()
	{
		$namacustomer = Yii::app()->request->getParam('ednamacustomer');

		if($namacustomer != '')
		{
			//pastikan nama customer ada.
			$Criteria = new CDbCriteria();
			$Criteria->condition = 'name like :nama AND is_del = "N" AND is_deact = "N" ';
			$Criteria->params = array(':nama' => '%' . $namacustomer . '%');
			$count = mtr_customer::model()->count($Criteria);

			if($count > 0)
			{
				//ada customer... tampilkan daftarnya dalam bentuk dropdown
				$Criteria->condition = 'name like :nama AND is_del = "N" AND is_deact = "N" ';
				$Criteria->params = array(':nama' => '%' . $namacustomer . '%');
				$customers = mtr_customer::model()->findAll($Criteria);

				$customer_list[0] = '-- Pilih Nama Customer --';
				foreach($customers as $customer)
				{
					$value = $customer['customer_id'];
					$nama = $customer['name'] . ' (No. HP: ' . $customer['mobile'] . ' )';

					$customer_list[$value] = $nama;
				}
			}
			else
			{
				$customer_list[0] = '-- Nama Tidak Ditemukan --';
			}
		}
		else
		{
			$customer_list[0] = '--';
		}

		$dropdown = CHtml::dropDownList(
			'SalesForm[customer_id]',
			'0',
			$customer_list,
			array(
				 'id' => 'cboCustomerId',
				 'style' => 'width: 100%'
			)
		);

		echo CJSON::encode(
			array(
				 'dropdown' => $dropdown
			)
		);
	}

	public function actionGetCustomerInfo()
	{
	    $idcustomer = Yii::app()->request->getParam('idcustomer');

		if (!empty($idcustomer))
		{
			//ambil listData untuk frametype
			/*
			$Criteria = new CDbCriteria();
			$Criteria->condition = 'is_del = "N" AND is_deact = "N" AND group_id = 4';
			$Criteria->order = 'order_no ASC';
			$mtr_std = mtr_std::model()->findAll($Criteria);
			$frametype_list = CHtml::listData($mtr_std, 'dsc', 'dsc');
			*/
			//print_r($frametype_list);
			//exit();

			$frametype_list = $this->getFrameTypeList();
			$bc_list = $this->getBCList();

			//pastikan idcustomer valid (ada di tabel)
			$Criteria = new CDbCriteria();
			$Criteria->condition = 'customer_id = :idcustomer';
			$Criteria->params = array(':idcustomer' => $idcustomer);
			$count = mtr_customer::model()->count($Criteria);

			if($count == 1)
			{
				//idcustomer valid

				//ambil record customer
				$customer = mtr_customer::model()->with('customer_type','gender')->find($Criteria);
				$infoCustomer = $this->renderPartial(
					'v_sales_info_customer',
					array(
						'customer' => $customer
					),
					true
				);

				//ambil record resep terakhir
				//cari id terakhir
				$Criteria->select = 'max(presc_id) as presc_id';
				$Criteria->condition = 'customer_id = :idcustomer';
				$Criteria->params = array(':idcustomer' => $idcustomer);

				$resep = pos_customer_presc::model()->find($Criteria);
				$idresep = $resep['presc_id'];

				$Criteria = new CDbCriteria();
				$Criteria->condition = 'presc_id = :idresep';
				$Criteria->params = array(':idresep' => $idresep);
				$resep = pos_customer_presc::model()->find($Criteria);

				//ambil record resep detail terakhir
				$Criteria = new CDbCriteria();
				$Criteria->condition = 'presc_id = :idresep';
				$Criteria->params = array(':idresep' => $idresep);
				$Criteria->order = 'side';
				$resep_detail = pos_customer_presc_detail::model()->findAll($Criteria);

				$infoResep = $this->renderPartial(
					'v_sales_info_resep',
					array(
						'resep' => $resep,
						'resep_detail' => $resep_detail
					),
					true
				);

				//render form precal
				$form_precal = $this->renderPartial(
					'v_sales_form_precal',
					array(
						'frametype_list' => $frametype_list,
						'bc_list' => $bc_list
					),
					true
				);

				echo CJSON::encode(
					array(
						'infoCustomer' => $infoCustomer,
						'infoResep' => $infoResep,
						'formPrecal' => $form_precal,
						'status' => 'ok'
					)
				);
			}
			else
			{
				//idcustomer tidak valid
				echo CJSON::encode(
					array(
						'status' => 'not_ok',
						'pesan' => 'Error! Data Customer belum dipilih'
					)
				);
			}
		}
		else
		{
			//idcustomer tidak ada
			echo CJSON::encode(
				array(
					'status' => 'not_ok',
					'pesan' => 'Error! Data Customer belum dipilih'
				)
			);
		}
	}

	public function actionNewCustomerEntry()
	{
		//ambil listData untuk gender
		$Criteria = new CDbCriteria();
		$Criteria->condition = 'is_del = "N" AND is_deact = "N" AND group_id = 2';
		$Criteria->order = 'order_no ASC';
		$genders = mtr_std::model()->findAll($Criteria);
		$gender_list = CHtml::listData($genders, 'mtr_id', 'dsc');

		//ambil listData untuk examiner
		$Criteria = new CDbCriteria();
		$Criteria->condition = 'is_del = "N" AND is_deact = "N"';
		$examiners = Examiner::model()->findAll($Criteria);
		$examiner_list = CHtml::listData($examiners, 'examiner_id', 'name');

		//ambil listData untuk frametype
		/*
		$Criteria = new CDbCriteria();
		$Criteria->condition = 'is_del = "N" AND is_deact = "N" AND group_id = 4';
		$Criteria->order = 'order_no ASC';
		$frametypes = mtr_std::model()->findAll($Criteria);
		$frametype_list = CHtml::listData($frametypes, 'dsc', 'dsc');
		*/

		$frametype_list = $this->getFrameTypeList();
		$bc_list = $this->getBCList();

		$formCustomer = $this->renderPartial(
			'v_sales_form_customer',
			array(
				'gender_list' => $gender_list
			),
			true
		);

	   $formResep = $this->renderPartial(
			'v_sales_form_resep',
			array(
				'examiner_list' => $examiner_list
			),
			true
		);

	   $formPrecal = $this->renderPartial(
			'v_sales_form_precal',
			array(
				'frametype_list' => $frametype_list,
				'bc_list' => $bc_list
			),
			true
		);

		echo CJSON::encode(
			array(
				'formCustomer' => $formCustomer,
				'formResep' => $formResep,
				'formPrecal' => $formPrecal,
				'status' => 'ok'
			)
		);
	}

	/*
	     actionGetProdukList

	     Deskripsi
	     Fungsi untuk mengambil record untuk ditampilkan sebagai combo box di
	     interface sales.

	     Parameter
	     edbarcodeproduk
	          String. barcode produk

          Return
          Drop down list dalam format html, dibungkus json.
	*/
	public function actionGetProdukList()
	{
	     $barcodeproduk = Yii::app()->request->getParam('edbarcodeproduk');
	     $idlokasi = Yii::app()->request->cookies['idlokasi']->value;

	     if($barcodeproduk != '')
	     {
	          //cari barang berdasarkan barcode

               //pastikan barcode valid.

               $Criteria = new CDbCriteria();
               $Criteria->condition = 'barcode = :barcode AND idlokasi = :idlokasi AND idstatus in (1, 3)';
               $Criteria->params = array(':barcode' => $barcodeproduk, ':idlokasi' => $idlokasi);

               $count = inv_item::model()->count($Criteria);

               if($count > 0)
               {
                    $Criteria->condition = 'barcode = :barcode AND idlokasi = :idlokasi AND idstatus in (1, 3)';
                    $Criteria->params = array(':barcode' => $barcodeproduk, ':idlokasi' => $idlokasi);
                    $items = inv_item::model()->findAll($Criteria);

                    foreach($items as $item)
                    {
                         $value = $item['id'];
                         $produk = $item->produk;

                         switch($produk['idkategori'])
                         {
                              case 1: //lensa
                                   $spesifik = $produk->lensa;
                                   break;
                              case 2: //frame
                                   $spesifik = $produk->frame;
                                   break;
                              case 3: //softlens
                                   $spesifik = $produk->softlens;
                                   break;
                              case 4: //solution
                                   $spesifik = $produk->solution;
                                   break;
                              case 5: //accessories
                                   $spesifik = $produk->accessories;
                                   break;
                              case 6: //services
                                   $spesifik = $produk->services;
                                   break;
                              case 7: //other
                                   $spesifik = $produk->other;
                                   break;
                              case 8: //supplies
                                   $spesifik = $produk->supplies;
                                   break;
                         }

                         //ambil harga jual
                         $Criteria = new CDbCriteria();
                         $Criteria->condition = 'id_item = :iditem AND id_toko = :idlokasi';
                         $Criteria->params = array(':iditem' => $item['idinventory'], ':idlokasi' => $idlokasi);
                         $harga_jual = inv_harga_jual::model()->find($Criteria);

                         Yii::log('iditem = ' . $item['idinventory'], 'info');
                         Yii::log('idlokasi = ' . $idlokasi, 'info');
                         Yii::log('cek_harga_jual = ' . $harga_jual['harga_jual'], 'info');
                         
                         $idbarang = $item['id'];
                         $idproduk = $item['idinventory'];
                         $namabarang = $produk['nama'];
                         $hargabarang = number_format($harga_jual['harga_jual'], 0, '.', '.');
                    }

                    echo CJSON::encode(
                         array(
                          'idbarang' => $idbarang,
                          'idproduk' => $idproduk,
                          'namabarang' => $namabarang,
                          'hargabarang' => $hargabarang,
                         )
                    );
               }
               else
               {
                    $namabarang = 'Barang Tidak Ditemukan';

                    echo CJSON::encode(
                         array(
							  'idbarang' => 0,
                              'namabarang' => $namabarang,
							  'hargabarang' => '0'
                         )
                    );
               }
	     }
	}

	/*
	     actionTambahPenjualan

	     Deskripsi
	     Fungsi untuk mengambil informasi barang berdasarkan idproduk

	     Parameter
	     idproduk
	          Integer

          Return
          Data produk dibungkus dalam json.
	*/
	public function actionTambahPenjualan()
	{
     $idproduk = Yii::app()->request->getParam('idproduk');
     $idlokasi = Yii::app()->request->cookies['idlokasi']->value;
     $byname = Yii::app()->request->getParam('byname');

     if($byname == 1) //apakah menambah penjualan berdasarkan barcode ?
     {
       //idproduk adalah idinventory jika byname = 1
       //idproduk adalah iditem jika byname = 0
       
       //menambahkan penjualan tanpa barcode
       if ($idproduk != 0) 
       {
       
          //mengambil info produk
          $produk = FHelper::GetRecordProduk($idproduk);
          $iditem = -1;
          $barcode = '<img class="icon12 fl-space2" src="images/ico_attention.png" width="24px" title="Tanpa barcode">';
          $is_paket = 0;
  
          $ukuran = FHelper::GetProdukUkuranMini($idproduk);
          $ukuran = (strlen($ukuran) > 0 ? ' - ' : '').$ukuran;
  
          //mengambil harga jual
          $hargajual = FHelper::GetHargaJual($idlokasi, $idproduk);
          //mengambil harga jual
  
          //menghitung harga setelah diskon
          $diskonamt = $hargajual['diskon']/100 * $hargajual['harga_jual'];
          $harganet = 1 * ($hargajual['harga_jual'] - $diskonamt);
          
          //barang tipe paket (idkategori == 9) - begin
          
            if($produk['idkategori'] == 9)
            {
              $byname = 0;
              $is_paket = 1;
              
              $info_barcode = FHelper::GenerateBarcode($produk['id']);
              $iditem = $info_barcode['iditem'];
              $barcode = $info_barcode['barcode'];
              
              //ambil informasi paket
              $infopaket = array();
              $infopaket['iditem'] = $iditem;
              $infopaket['daftarproduk'] = array();
              
              //hitung total jumlah barang untuk melengkapi paket ini
              
              
              //hitung total jumlah barang yang sudah dibacakan untuk sales paket ini
              
              //ambil daftar item penyusun paket dari tabel inv_paket_detail
              //ambil daftar item - begin
                $command = Yii::app()->db->createCommand()
                  ->select('detail.*, inventory.nama, inventory.id')
                  ->from('inv_paket_detail detail')
                  ->join('inv_type_paket paket', 'paket.id_item = detail.idpaket')
                  ->join('inv_inventory inventory', 'inventory.id = detail.iditem')
                  ->where(
                    "detail.idpaket = :idpaket",
                    array(
                      ':idpaket' => $produk['id']
                    )
                  );
                $daftar_produk = $command->queryAll();
                
                foreach($daftar_produk as $data_produk)
                {
                  $temp['name'] = $data_produk['nama'];
                  $temp['count'] = 0;
                  $temp['total'] = $data_produk['jumlah'];
                  $temp['daftarbarcode'] = array();
                  $temp['idproduk'] = $data_produk['id'];
                  
                  $infopaket['daftarproduk'][] = $temp;
                }
              //ambil daftar item - end
            }//apakah barang bertipe PAKET
          
          //barang tipe paket (idkategori == 9) - end
  
          //mengembalikan hasil
          $status = 'ok';
          echo CJSON::encode(
            array(
              'status' => $status,
              'idinventory' => $produk['id'], //idproduk
              'iditem' => $iditem,
              'byname' => $byname,
              'idkategori' => $produk['idkategori'],
              'is_paket' => $is_paket,
              'info_paket' => $infopaket,
              'barcode' => $barcode,
              'namabarang' => $produk['nama'],
              'nama' => $produk['nama'],
              'ukuran' => $ukuran,
              'jumlah' => 1,
              'harga' => number_format($hargajual['harga_jual'], 0, '.', '.'),
              'diskon' => number_format($hargajual['diskon'], 0, '.', '.'),
              'diskonamt' => number_format($diskonamt, 0, '.', '.'),
              'harganet' => number_format($harganet, 0, '.', '.')
            )
          );
       } 
       else 
       {
          $status = 'not_ok';
          echo CJSON::encode(
            array(
              'status' => $status,
              'pesan' => '-- Barang Tidak Ditemukan --'
            )
          );
       }
     }
     else
     {
       //menambahkan penjualan dengan barcode
       
       //memastikan barcode valid (terdaftar, berstatus tepat, dilokasi yang benar)
       $Criteria = new CDbCriteria();
       $Criteria->condition = "
        id = :iditem AND
        idlokasi = :idlokasi AND
        idstatus in (1, 3)";
       $Criteria->params = array(
         ':iditem' => $idproduk,
         ':idlokasi' => $idlokasi
       );
       $count = inv_item::model()->count($Criteria);

       if($count == 1)
       {
          //mengambil harga jual
          $item = inv_item::model()->find($Criteria);
          $produk = $item->produk;
      
          $ukuran = FHelper::GetProdukUkuranMini($produk['id']);
          $ukuran = (strlen($ukuran) > 0 ? ' - ' : '').$ukuran;
      
          $hargajual = FHelper::GetHargaJual($idlokasi, $produk['id']);
          //mengambil harga jual
          
          //menghitung harga setelah diskon
          $diskonamt = $hargajual['diskon']/100 * $hargajual['harga_jual'];
          $harganet = 1 * ($hargajual['harga_jual'] - $diskonamt);
          
          //Update status item pada saat ditambahkan ke suatu sales
          /*
          FHelper::ItemStatusUpdate(
            $item['id'], 
            4, 
            $idlokasi, 
            Yii::app()->request->cookies['iduser_actor']->value
          );
          */
    
          //mengembalikan hasil
          $status = 'ok';
          echo CJSON::encode(
            array(
             'status' => $status,
             'iditem' => $item['id'], //iditem
             'idinventory' => $idproduk, //idinventory
             'byname' => 0,
             'is_paket' => 0,
             'idkategori' => $produk['idkategori'],
             'barcode' => $item['barcode'],
             'namabarang' => $produk['nama'],
             'nama' => $produk['nama'].$ukuran,
             'jumlah' => 1,
             'harga' => number_format($hargajual['harga_jual'], 0, '.', '.'),
             'diskon' => number_format($hargajual['diskon'], 0, '.', '.'),
             'diskonamt' => number_format($diskonamt, 0, '.', '.'),
             'harganet' => number_format($harganet, 0, '.', '.')
            )
          );
       }
       else
       {
          $status = 'not ok';

          echo CJSON::encode(
            array(
               'status' => $status
            )
           );
       }
     } //menambah penjualan menggunakan barcode ?
	     
	     
	}
	
	
	/*
     actionHapusPenjualan

     Deskripsi
     Fungsi untuk membatalkan status penjualan terhadap suatu iditem

     Parameter
     iditem - Integer

     Return
     Data produk dibungkus dalam json.
	*/
	public function actionHapusPenjualan()
	{
    $iditem = Yii::app()->request->getParam('iditem');
    $idlokasi = Yii::app()->request->cookies['idlokasi']->value;
    $iduser = Yii::app()->request->cookies['userid_actor']->value;
    
    FHelper::ItemStatusUpdate(
      $iditem, 
      3, 
      $idlokasi, 
      $iduser,
      'Batal penjualan item'
    );
    

	}
	
	
	/*
	  actionCariBarang
	  
	  Deskripsi
	  Fungsi untuk mengembalikan daftar barang berdasarkan nama barang.
	  
	  Parameter
	  namabarang
	    String
	    
	  Return
	  Mengembalikan array untuk mengupdate daftar option select pada view.
	*/
	public function actionCariBarang()
	{
	  ini_set("display_errors", "1");
	  ini_set("memory_limit", "2000M");
	  ini_set("max_execution_time", "0");
	  
		$userid_actor = Yii::app()->request->getParam('userid_actor');
		$this->idlokasi = Yii::app()->request->cookies['idlokasi']->value;
		
		$this->userid_actor = $userid_actor;
		$this->menuid = 37;
		$this->parentmenuid = 9;
		
		$namabarang = Yii::app()->request->getParam('namabarang');
		$dropdownlist = array();
		//$dropdownlist[-1] = 'Pilih produk...';
		
		if( trim($namabarang) != '')
		{
		  //explode string menjadi 2 array. Array pertama berisi string saja. Array kedua berisi numeric
		  //lakukan loop pencarian berdasarkan array nama.
		  //pada setiap loop nama, lakukan pencarian berdasarkan array ukuran.
		  
		  $array_temp = explode(" ", trim($namabarang));
		  
		  $array_numeric = array();
		  $array_string = array();
		  foreach($array_temp as $test)
		  {
		    if( is_numeric($test))
		    {
		      $array_numeric[] = $test;
		    }
		    else
		    {
		      $array_string[] = $test;
		    }
		  }
		  
		  $test_ke = 1;
		  $where = "";
		  $where_array = array();
		  foreach($array_string as $test_string)
		  {
		    if($where != "")
		    {
		      $where .= " AND ";
		    }
		    
		    $where .= "produk.nama like :test_$test_ke";
		    
		    $where_array[":test_$test_ke"] = "%$test_string%";
		    
		    $test_ke++;
		  }
		  
		  $daftar_produk = array();
		  $command = Yii::app()->db->createCommand()
        ->select('produk.*')
        ->from('inv_inventory produk')
        ->where(
          "$where 
          AND
          produk.is_del = 0", 
          $where_array
        )
        ->order('produk.nama');
      //->limit('100');
      $daftar_produk = $command->queryAll();
		  
      foreach($daftar_produk as $produk)
      {
        $idproduk = $produk['id'];
        
        $real_nama_produk = FHelper::GetProdukName($idproduk);
        $real_brand_produk = FHelper::GetProdukBrand($idproduk);
        $real_ukuran_produk = FHelper::GetProdukUkuran($idproduk);
        $barang = 
          $real_nama_produk . ' | ' .
          $real_brand_produk . ' | ' .
          $real_ukuran_produk;
              
        if( count($array_numeric) > 0 )
        {
          //lakukan pencarian string menggunakan array_numeric
          foreach($array_numeric as $test)
          {
            if(strpos($barang, $test))
            {
              $temp['id'] = $idproduk;
              $temp['value'] = $barang;
              $dropdownlist[] = $temp;
            }
          } //loop array_numeric
        }
        else
        {
          $temp['id'] = $idproduk;
          $temp['value'] = $barang;
          $dropdownlist[] = $temp;
        }
      }//loop produk
		  
      
      /*
		  foreach($array_string as $test_string)
		  {
		    $command = Yii::app()->db->createCommand()
          ->select('produk.*')
          ->from('inv_inventory produk')
          ->where(
            "produk.nama like :nama AND
            produk.is_del = 0", 
            array(
            ':nama' => "%$test_string%"
            ))
          ->order('produk.nama');
        //->limit('100');
        $daftar_produk = $command->queryAll();
        
        foreach($daftar_produk as $produk)
        {
          $idproduk = $produk['id'];
          
          $real_nama_produk = FHelper::GetProdukName($idproduk);
          $real_brand_produk = FHelper::GetProdukBrand($idproduk);
          $real_ukuran_produk = FHelper::GetProdukUkuran($idproduk);
          $barang = 
            $real_nama_produk . ' | ' .
            $real_brand_produk . ' | ' .
            $real_ukuran_produk;
                
          if( count($array_numeric) > 0 )
          {
            //lakukan pencarian string menggunakan array_numeric
            foreach($array_numeric as $test)
            {
              if(strpos($barang, $test))
              {
                $temp['id'] = $idproduk;
                $temp['value'] = $barang;
                $dropdownlist[] = $temp;
              }
            } //loop array_numeric
          }
          else
          {
            $temp['id'] = $idproduk;
            $temp['value'] = $barang;
            $dropdownlist[] = $temp;
          }
        }//loop produk
		  } //loop array_string
		  */
		  
		  
      if (count($daftar_produk) == 0) 
      {
        $temp['id'] = 0;
        $temp['value'] = 'Barang tidak ditemukan';
        $dropdownlist[] = $temp;
		  }
		}
		
		echo CJSON::encode(
		  array(
			'dropdownlist' => $dropdownlist
		  )
		);
	}

	public function actionKalkulatorPenjualan()
	{
		$idlokasi = Yii::app()->request->cookies['idlokasi']->value;
		$aidproduk = Yii::app()->request->getParam('aip');
		$disc_percent = Yii::app()->request->getParam('disc');
		$tax_percent = Yii::app()->request->getParam('tax');
		$diskon_rupiah = Yii::app()->request->getParam('discrupiah');
    
		//menghilangkan tanda thousand separator pada angka diskon
		$diskon_rupiah = preg_replace('/\./', '', $diskon_rupiah);
    
		$jenis_diskon = Yii::app()->request->getParam('jenisdiskon');
		//echo ("$disc_percent, $tax_percent, $diskon_rupiah, $jenis_diskon");
		//exit();

		$subtotal1 = 0;
		//$arrip = explode (",", $aidproduk);
		$arrip = $aidproduk;

		if(count($arrip) > 0)
		{
			foreach($arrip as $data_produk) 
			{
				//periksa apakah entry produk berdasarkan barcode atau nama
				if($data_produk['byname'] == 1)
				{
					//cari harga berdasarkan nama produk

					$Criteria = new CDbCriteria();
					$Criteria->condition = 'id_toko = :idlokasi AND id_item = :idproduk';
					$Criteria->params = array(':idlokasi' => $idlokasi, ':idproduk' => $data_produk['idinventory']);
					$hargajual = inv_harga_jual::model()->find($Criteria);
				}
				else
				{
					//cari harga berdasarkan idindividu

					if($data_produk['is_paket'] == 0)
					{
					  $Criteria = new CDbCriteria();
            $Criteria->condition = 'id = :idproduk';
            $Criteria->params = array(':idproduk' => $data_produk['idinventory']);
            $produk = inv_inventory::model()->find($Criteria);
            //$produk = $item->produk;
					}
					else
					{
					  $produk = FHelper::GetProdukByIdItem($data_produk['iditem']);
					}
            

					$Criteria = new CDbCriteria();
					$Criteria->condition = 'id_toko = :idlokasi AND id_item = :idproduk';
					$Criteria->params = array(':idlokasi' => $idlokasi, ':idproduk' => $produk['id']);
					$hargajual = inv_harga_jual::model()->find($Criteria);
				}

				$hargajualnet = 1 * ((float)($hargajual['harga_jual'] - ((float)$hargajual['diskon']/(float)100 * (float)$hargajual['harga_jual'])));

				Yii::log("hargajual = {$hargajual['harga_jual']}, diskon = {$hargajual['diskon']}, idlokasi = $idlokasi, idproduk = {$produk['id']}", 'info');

				$subtotal1 += $hargajualnet;

				Yii::log("subtotal1 = {$subtotal1}", 'info');

				if($jenis_diskon == "rupiah")
				{
					//hitung besaran diskon dalam persen
					$disc_percent = ((float)$diskon_rupiah / (float)$subtotal1) * 100.00;
					$disc_amount = $diskon_rupiah;
				}
				else
				{
					$disc_amount = $disc_percent/100 * $subtotal1;
				}

				$subtotal2 = $subtotal1 - $disc_amount;
				$tax_amount = $tax_percent/100 * $subtotal2;
				$total = $subtotal2 + $tax_amount;
			}
		  
			$status = 'ok';
			echo CJSON::encode(
				array(
					'status' => $status,
					'subtotal1' => number_format($subtotal1, 0, '.', '.'),
					'disc_amount' => number_format($disc_amount, 0, '.', '.'),
					'disc_percent' => number_format($disc_percent, 2, '.', '.'),
					'subtotal2' => number_format($subtotal2, 0, '.', '.'),
					'tax_amount' => number_format($tax_amount, 2, '.', '.'),
					'total' => number_format($total, 0, '.', '.')
				)
			);
		}
		else
		{
			$status = 'ok';
			echo CJSON::encode(
				array(
					'status' => $status,
					'subtotal1' => 0,
					'disc_amount' => number_format($disc_amount, 0, '.', '.'),
					'disc_percent' => number_format($disc_percent, 2, '.', '.'),
					'subtotal2' => 0,
					'tax_amount' => 0,
					'total' => 0
				)
			);
		}
	}

	public function actionOut()
	{
		$menuid = 32;
		$parentmenuid = 7;

		$userid_actor = Yii::app()->request->getParam('userid_actor');
		$idlokasi = Yii::app()->request->cookies['idlokasi']->value;

		$allow_read = FHelper::AllowMenu($menuid, FHelper::GetGroupId($userid_actor), 'read');
		if ($allow_read) {
			$Criteria = new CDbCriteria();
			$Criteria->condition = "t.status = 'BAYAR' AND t.branch_id = :idlokasi";
			$Criteria->params = array(':idlokasi' => $idlokasi);
			$recs = Sales::model()->with('SalesCustomer')->findAll($Criteria);
			$TheMenu = FHelper::RenderMenu(0, $userid_actor, $parentmenuid);

			$this->userid_actor = $userid_actor;
			$this->parentmenuid = $parentmenuid;

			$this->bread_crumb_list = '
				<li>Sales</li>
				<li>></li>
				<li>Outstanding</li>';

			$this->layout = 'layout-baru';

			$TheContent = $this->renderPartial(
					'out',
					array(
							'userid_actor' => $userid_actor,
							'recs' => $recs,
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
					'not_auth',
					array(
							'userid_actor' => $userid_actor
					),
					true
			);
		}
	}
	
	/*
	  actionCetakResep()
	  
	  Deskripsi
	  Fungsi untuk menampilkan informasi resep berdasarkan idsales. Informasi 
	  resep ditampilkan menggunakan plugin fancybox.
	*/
	public function actionCetakResep()
	{
	  $idsales = Yii::app()->request->getParam('idsales');
	  
	  //ambil informasi customer dari tabel pos_sales_customer
	  $info_customer = FHelper::GetCustomerByIdSales($idsales);
	  
	  //ambil informasi resep dari tabel pos_sales_presc
	  $info_presc = FHelper::GetResepByIdSales2($idsales);
    
    $resep = $this->renderPartial(
      'v_print_resep',
      array(
        'info_customer' => $info_customer,
        'info_presc' => $info_presc
      ),
      true
    );
    
    echo $resep;
	}
	
	
	/**
	  Fungsi untuk melakukan validasi, bahwa suatu barcode merupakan item penyusun
	  suatu paket.
	  
	  @param {string} barcode - Barcode yang akan diperiksa validitasnya.
	  @param {integer} idpaket - ID produk tipe paket yang dijadikan rujukan
	  @return {json} 
	*/
	public function actionValidasiBarcodePaket()
	{
	  $barcode = Yii::app()->request->getParam('barcode');
	  $iditem_paket = Yii::app()->request->getParam('idpaket');
	  
	  //ambil idpaket (as id_inventory
	  $item = inv_item::model()->find(
	    "id = :iditem_paket",
	    array(
	      ":iditem_paket" => $iditem_paket
      )
    );
    $idinventory = $item['idinventory'];
	  
	  //ambil record produk berdasarkan barcode 
	  $produk = FHelper::GetProdukByBarcode($barcode);
	  
	  //ambil record item berdasarkan barcode
	  $iditem = FHelper::GetIdItemByBarcode($barcode);
	  
	  //test iditem vs idpaket
	  $command = Yii::app()->db->createCommand()
	    ->select('*')
	    ->from('inv_paket_detail')
	    ->where(
	      "iditem = :iditem AND
	      idpaket = :idpaket",
	      array(
	        ':iditem' => $produk['id'],
	        ':idpaket' => $idinventory
        )
      );
      
    $hasil = $command->queryRow();
    
    if($hasil != false)
    {
      $status = 'ok';
    }
    else
    {
      $status = 'not ok';
    }
    
    echo CJSON::encode(
      array(
        'status' => $status,
        'produk' => array(
          'idproduk' => $produk['id'],
          'iditem' => $iditem,
          'barcode' => $barcode,
        )
      )
    );
	}
	
	/**
	  Fungsi untuk mengembalikan view daftar detail paket. Jika idsalesdetail 
	  disediakan maka fungsi mengembalikan view daftar detail paket dari pos_sales_det.
	  
	  @param {integer} idpaket
	  @param {integer} idsalesdetail (optional)
	  
	  @return {JSON} object berisi informasi detail paket.
	*/
	public function actionShowDetailPaket()
	{
	  $idpaket = Yii::app()->request->getParam('idpaket');
	  $idsalesdetail = Yii::app()->request->getParam('idsalesdetail');
	  
	  $command = Yii::app()->db->createCommand()
	    ->select("*")
	    ->from('inv_inventory')
	    ->where(
	      "id = :idpaket AND
	      id_kategori = 9",
	      array(
	        ':idpaket' => $idpaket
        )
      );
    $paket = $command->queryRow();
    $info_paket['daftar_produk'] = array();
    $info_paket['idpaket'] = $idpaket;
    $info_paket['nama'] = $paket['nama'];
      
    $command = Yii::app()->db->createCommand()
      ->select("*")
      ->from("inv_detil_paket")
      ->where(
        "idpaket = :idpaket",
        array(
          ':idpaket' => $idpaket
        )
      );
    $detail_paket = $command->queryAll();
    
    foreach($detail_paket as $item_paket)
    {
      $temp['count'] = $item_paket['jumlah'];
      $temp['daftar_barcode'] = array();
      $temp['idproduk'] = $item_paket['idinventory'];
      
      $info_paket['daftar_produk'][] = $temp;
    }
	  
	  if( $idsalesdetail > -1 )
	  {
	    //ambil informasi detail paket yang sudah di-scan dari pos_sales_det_paket
	    
	    foreach($info_paket['daftar_produk'] as $info_produk)
	    {
	      
	      //ambil iditem dari pos_sales_det_paket, berdasarkan idproduk.
        $command = Yii::app()->db->createCommand()
          ->select("*")
          ->from('pos_sales_det_paket')
          ->where(
            "idinventory = :idproduk AND
            sales_det_id = :idsalesdet",
            array(
              ':idproduk' => $info_produk['idproduk'],
              ':idsalesdet' => $idsalesdetail
            )
          );
        $daftar_barcode = $command->queryAll();
        
        foreach($daftar_barcode as $data_barcode)
        {
          $temp_barcode['iditem'] = $data_barcode['iditem'];
          $temp_barcode['barcode'] = $data_barcode['barcode'];
          
          $info_produk['daftar_barcode'][] = $temp_barcode;
        }
	      
	    }
	  }
	  
	  echo CJSON::encode( array('info_paket' => $info_paket) );
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