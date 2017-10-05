<?php

class MasterController extends FController
{
  
  
	public function actionIndex()
	{
		$this->render('index');
	}
	
	public function filters()
  {
    return array(
      array('application.filters.CheckSessionFilter'),
      array('application.filters.CheckLokasiUserFilter')
    );
  }

	//Setting - Data Masters - begin

	  public function actionShowInvalidAccess($userid_actor, $is_ajax=true)
	  {
	    if($is_ajax)
	    {
	      $html = $this->renderPartial(
          'v_not_auth',
          array(
            'userid_actor' => $userid_actor
          ),
          true
        );
        
        echo CJSON::encode(array('html' => $html));
	    }
	    else
	    {
	      $this->layout = 'layout-baru';
	      
	      $this->render(
          'v_not_auth',
          array(
            'userid_actor' => $userid_actor
          )
        );
	    }
	    
	  }

	  //lokasi - begin

	    /*
        actionLokasi

        Deskripsi
        Action untuk menampilkan daftar lokasi
      */
      public function actionLokasi()
      {
        $menuid = 14;
        $parentmenuid = 6;
        $userid_actor = Yii::app()->request->getParam('userid_actor');
        $this->idlokasi = Yii::app()->request->cookies['idlokasi']->value;
        $idgroup = FHelper::GetGroupId($userid_actor);
        
        if(FHelper::AllowMenu($menuid, $idgroup, 'read'))
        {
          $Criteria = new CDbCriteria();
          $Criteria->condition = 'is_del = \'N\'';
          
          $branch_list = mtr_branch::model()->findAll($Criteria);
          $TheMenu = FHelper::RenderMenu(0, $userid_actor, $parentmenuid);
          
          $this->userid_actor = $userid_actor;
          $this->parentmenuid = $parentmenuid;
          
          $this->bread_crumb_list = '
            <li>
              Data Master
            </li>
            <li>
              <span>></span>
            </li>
            <li>
              Cabang
            </li>';
  
          $this->layout = 'layout-baru';                    
          $TheContent = $this->renderPartial(
            'v_list_lokasi',
            array(
              'userid_actor' => $userid_actor,
              'idlokasi' => $idlokasi,
              'branch_list' => $branch_list,
              'menuid' => $menuid
            ),
            true
          );
  
          $this->render(
            'index_general',
            array(
              'TheMenu' => $TheMenu,
              'TheContent' => $TheContent,
              'userid_actor' => $userid_actor,
              'idlokasi' => $idlokasi,
            )
          );
        }
        else
        {
          $this->actionShowInvalidAccess($userid_actor, false);
        }
      }

      /*
        actionListLokasi

        Deskripsi
        Action untuk menampilkan daftar lokasi
      */
      public function actionListLokasi()
      {
        $menuid = 14;
        $parentmenuid = 6;
        $userid_actor = Yii::app()->request->getParam('userid_actor');
        $idgroup = FHelper::GetGroupId($userid_actor);
        
        if(FHelper::AllowMenu($menuid, $idgroup, 'read'))
        {
          $Criteria = new CDbCriteria();
          $Criteria->condition = 'is_del = \'N\'';
  
          $branch_list = mtr_branch::model()->findAll($Criteria);
          $TheMenu = FHelper::RenderMenu(0, $userid_actor, $parentmenuid);
  
          $this->layout = 'setting';
          $html = $this->renderPartial(
            'v_list_lokasi',
            array(
              'userid_actor' => $userid_actor,
              'idlokasi' => $idlokasi,
              'branch_list' => $branch_list,
              'menuid' => $menuid
            ),
            true
          );
  
          $bread_crumb_list = 
            '<li>Data Master</li>' .
            
            '<li>'.
              '<span> > </span>'.
              'Cabang/Toko/Outlet'.
            '</li>';
            
          echo CJSON::encode(
            array(
              'html' => $html, 
              'bread_crumb_list' => $bread_crumb_list
            )
          );
        }
        else
        {
          $this->actionShowInvalidAccess($userid_actor);
        }
      }

	    /*
	      actionAddLokasi

	      Deskripsi
	      Action untuk menampilkan form menambah lokasi
	    */
	    public function actionAddLokasi()
	    {
	      $menuid = 14;
        $parentmenuid = 6;
        $userid_actor = Yii::app()->request->getParam('userid_actor');
        $branches = mtr_branch::model()->findAll();
        $active_option = array('N' => 'Aktif', 'Y' => 'Tidak aktif');
        
        $idgroup = FHelper::GetGroupId($userid_actor);
        
        if(FHelper::AllowMenu($menuid, $idgroup, 'write'))
        {
          $form = new frmEditLokasi();

          $do_add = Yii::app()->request->getParam('do_add');
  
          if(isset($do_add))
          {
            if($do_add == 1)
            {
              //proses form submission
  
              $form->attributes = Yii::app()->request->getParam('frmEditLokasi');
  
              if($form->validate())
              {
                //simpan record ke tabel
                $mtr_branch = new mtr_branch();
                $mtr_branch['name'] = Defense::Sanitize($form['nama']);
                $mtr_branch['code'] = Defense::Sanitize($form['kode']);
                $mtr_branch['initial'] = Defense::Sanitize($form['inisial']);
                $mtr_branch['address'] = Defense::Sanitize($form['alamat']);
                $mtr_branch['phone'] = Defense::Sanitize($form['telepon']);
                $mtr_branch['city'] = Defense::Sanitize($form['kota']);
                $mtr_branch['zip'] = Defense::Sanitize($form['zip']);
                $mtr_branch['country'] = Defense::Sanitize($form['negara']);
                $mtr_branch['address'] = Defense::Sanitize($form['alamat']);
                $mtr_branch['fax'] = Defense::Sanitize($form['fax']);
                $mtr_branch['is_deact'] = Defense::Sanitize($form['is_deact']);   
                $mtr_branch->insert();
                $idlokasi = $mtr_branch->getPrimaryKey();
  
                //tampilkan informasi sukses menambahkan record lokasi
                $bread_crumb_list = 
                  '<li>Data Master</li>' .
                  
                  '<li>'.
                    '<span> > </span>'.
                    '<a href="#" onclick="ShowLocationList('.$userid_actor.');">Cabang/Toko/Outlet</a>'.
                  '</li>'.
                  
                  '<li>'.
                    '<span> > </span>'.
                    'Tambah Cabang/Toko/Outlet'.
                  '</li>';
                  
                $html = $this->renderPartial(
                  'v_addlokasi_success',
                  array(
                    'userid_actor' => $userid_actor,
                    'idlokasi' => $idlokasi,
                    'form' => $form,
                    'active_option' => $active_option,
                  ),
                  true
                );
              }
              else
              {
                //tampilkan form
                $bread_crumb_list = 
                  '<li>Data Master</li>' .
                  
                  '<li>'.
                    '<span> > </span>'.
                    '<a href="#" onclick="ShowLocationList('.$userid_actor.');">Cabang/Toko/Outlet</a>'.
                  '</li>'.
                  
                  '<li>'.
                    '<span> > </span>'.
                    'Tambah Cabang/Toko/Outlet'.
                  '</li>';
                  
                $html = $this->renderPartial(
                  'vfrm_addlokasi',
                  array(
                    'form' => $form,
                    'userid_actor' => $userid_actor,
                    'idlokasi' => $idlokasi,
                    'active_option' => $active_option,
                  ),
                  true
                );
              }
            }
            else
            {
              //batal menambah lokasi.
              //alihkan ke view list lokasi.
              $Criteria = new CDbCriteria();
              $Criteria->condition = 'is_del = \'N\'';
  
              $userid_actor = Yii::app()->request->getParam('userid_actor');
              $idlokasi = Yii::app()->request->getParam('idlokasi');
              $branch_list = mtr_branch::model()->findAll($Criteria);
  
              $bread_crumb_list = 
                '<li>Data Master</li>' .
                
                '<li>'.
                  '<span> > </span>'.
                  'Cabang/Toko/Outlet'.
                '</li>';
                
              $html = $this->renderPartial(
                'v_list_lokasi',
                array(
                  'userid_actor' => $userid_actor,
                  'idlokasi' => $idlokasi,
                  'branch_list' => $branch_list,
                  'menuid' => $menuid
                ),
                true
              );
            }
  
          }
          else
          {
            //show form add lokasi
            $bread_crumb_list = 
              '<li>Data Master</li>' .
              
              '<li>'.
                '<span> > </span>'.
                '<a href="#" onclick="ShowLocationList('.$userid_actor.');">Cabang/Toko/Outlet</a>'.
              '</li>'.
              
              '<li>'.
                '<span> > </span>'.
                'Tambah Cabang/Toko/Outlet'.
              '</li>';
              
            $form['negara'] = 'Indonesia';
            $form['telepon'] = '+62';
            $form['fax'] = '+62';
            $html = $this->renderPartial(
              'vfrm_addlokasi',
              array(
                'form' => $form,
                'userid_actor' => $userid_actor,
                'idlokasi' => $idlokasi,
                'active_option' => $active_option
              ),
              true
            );
          }
  
          echo CJSON::encode(
            array(
              'html' => $html, 
              'bread_crumb_list' => $bread_crumb_list
            )
          );
        }
        else
        {
          $this->actionShowInvalidAccess($userid_actor);
        }
        
        
	    }

	    public function actionEditLokasi()
	    {
	      $menuid = 14;
        $parentmenuid = 6;
	      $userid_actor = Yii::app()->request->getParam('userid_actor');
	      $idlokasi = Yii::app()->request->getParam('idlokasi');
	      $do_edit = Yii::app()->request->getParam('do_edit');
	      $active_option = array('N' => 'Aktif', 'Y' => 'Tidak aktif');
	      
	      $idgroup = FHelper::GetGroupId($userid_actor);
        
        if(FHelper::AllowMenu($menuid, $idgroup, 'edit'))
        {
          if(isset($do_edit))
          {
            if($do_edit == 1)
            {
              //proses edit form submission
  
              $form = new frmEditLokasi();
              $form->attributes = Yii::app()->request->getParam('frmEditLokasi');
  
              if($form->validate())
              {
                $Criteria = new CDbCriteria();
                $Criteria->condition = 'branch_id = :idlokasi';
                $Criteria->params = array(':idlokasi' => $idlokasi);
  
                //simpan record ke tabel
                $mtr_branch = mtr_branch::model()->find($Criteria);
                $mtr_branch['name'] = Defense::Sanitize($form['nama']);
                $mtr_branch['code'] = Defense::Sanitize($form['kode']);
                $mtr_branch['initial'] = Defense::Sanitize($form['inisial']);
                $mtr_branch['address'] = Defense::Sanitize($form['alamat']);
                $mtr_branch['phone'] = Defense::Sanitize($form['telepon']);
                $mtr_branch['city'] = Defense::Sanitize($form['kota']);
                $mtr_branch['zip'] = Defense::Sanitize($form['zip']);
                $mtr_branch['country'] = Defense::Sanitize($form['negara']);
                $mtr_branch['address'] = Defense::Sanitize($form['alamat']);
                $mtr_branch['fax'] = Defense::Sanitize($form['fax']);
                $mtr_branch['is_deact'] = Defense::Sanitize($form['is_deact']);
                $mtr_branch->update();
  
                //tampilkan informasi sukses menambahkan record lokasi
                $bread_crumb_list = 
                  '<li>Data Master</li>' .
                  
                  '<li>'.
                    '<span> > </span>'.
                    '<a href="#" onclick="ShowLocationList('.$userid_actor.');">Cabang/Toko/Outlet</a>'.
                  '</li>'.
                  
                  '<li>'.
                    '<span> > </span>'.
                    'Edit Cabang/Toko/Outlet'.
                  '</li>';
                  
                $html = $this->renderPartial(
                  'v_editlokasi_success',
                  array(
                    'userid_actor' => $userid_actor,
                    'idlokasi' => $idlokasi,
                    'form' => $form,
                    'active_option' => $active_option
                  ),
                  true
                );
              }
              else
              {
                //tampilkan form
                $bread_crumb_list = 
                  '<li>Data Master</li>' .
                  
                  '<li>'.
                    '<span> > </span>'.
                    '<a href="#" onclick="ShowLocationList('.$userid_actor.');">Cabang/Toko/Outlet</a>'.
                  '</li>'.
                  
                  '<li>'.
                    '<span> > </span>'.
                    'Edit Cabang/Toko/Outlet'.
                  '</li>';
                  
                $html = $this->renderPartial(
                  'vfrm_editlokasi',
                  array(
                    'form' => $form,
                    'userid_actor' => $userid_actor,
                    'idlokasi' => $idlokasi,
                    'active_option' => $active_option
                  ),
                  true
                );
              }
            }
            else
            {
              //batal edit
              //kembali ke daftar lokasi
              $Criteria = new CDbCriteria();
              $Criteria->condition = 'is_del = \'N\'';
  
              $userid_actor = Yii::app()->request->getParam('userid_actor');
              $branch_list = mtr_branch::model()->findAll($Criteria);
  
              $bread_crumb_list = 
                '<li>Data Master</li>' .
                
                '<li>'.
                  '<span> > </span>'.
                  'Cabang/Toko/Outlet'.
                '</li>';
                
              $html = $this->renderPartial(
                'v_list_lokasi',
                array(
                  'userid_actor' => $userid_actor,
                  'idlokasi' => $idlokasi,
                  'branch_list' => $branch_list,
                  'active_option' => $active_option,
                  'menuid' => $menuid
                ),
                true
              );
            }
  
          }
          else
          {
            //tampilkan form edit lokasi
  
            $Criteria = new CDbCriteria();
            $Criteria->condition = 'branch_id = :idlokasi';
            $Criteria->params = array(':idlokasi' => $idlokasi);
  
            $locations = mtr_branch::model()->find($Criteria);
  
            $form = new frmEditLokasi();
            $form['nama'] = $locations['name'];
            $form['kode'] = $locations['code'];
            $form['inisial'] = $locations['initial'];
            $form['alamat'] = $locations['address'];
            $form['kota'] = $locations['city'];
            $form['zip'] = $locations['zip'];
            $form['negara'] = $locations['country'];
            $form['zip'] = $locations['zip'];
            $form['telepon'] = $locations['phone'];
            $form['fax'] = $locations['fax'];
            $form['is_deact'] = $locations['is_deact'];
  
            //show form add lokasi
            $bread_crumb_list = 
              '<li>Data Master</li>' .
              
              '<li>'.
                '<span> > </span>'.
                '<a href="#" onclick="ShowLocationList('.$userid_actor.');">Cabang/Toko/Outlet</a>'.
              '</li>'.
              
              '<li>'.
                '<span> > </span>'.
                'Edit Cabang/Toko/Outlet'.
              '</li>';
              
            $html = $this->renderPartial(
              'vfrm_editlokasi',
              array(
                'form' => $form,
                'userid_actor' => $userid_actor,
                'idlokasi' => $idlokasi,
                'active_option' => $active_option
              ),
              true
            );
          }
  
          echo CJSON::encode(
            array(
              'html' => $html, 
              'bread_crumb_list' => $bread_crumb_list
            )
          );
        }
        else
        {
          $this->actionShowInvalidAccess($userid_actor);
        }
	    }

	    public function actionHapusLokasi()
	    {
	      $menuid = 14;
        $parentmenuid = 6;
	      $userid_actor = Yii::app()->request->getParam('userid_actor');
	      $idlokasi = Yii::app()->request->getParam('idlokasi');
	      
	      $idgroup = FHelper::GetGroupId($userid_actor);
        
        if(FHelper::AllowMenu($menuid, $idgroup, 'delete'))
        {
          $Criteria = new CDbCriteria();
          $Criteria->condition = 'branch_id = :idlokasi';
          $Criteria->params = array(':idlokasi' => $idlokasi);
  
          //update record di tabel
          $mtr_branch = mtr_branch::model()->find($Criteria);
          $mtr_branch['is_del'] = 1;
          $mtr_branch->update();
          
          $this->actionListLokasi();
        }
        else
        {
          $this->actionShowInvalidAccess($userid_actor);
        }
	    }
	    
	    /*
	      actionViewLokasi
	      
	      Deskripsi
	      Action untuk menampilkan informasi lokasi. Juga memberikan akses untuk 
	      mengedit atau menghapus lokasi.
	      
	      Parameter
	      userid_actor
	        Integer. Id user yang menggunakan system.
	      idlokasi
	        Integer. Menerangkan id lokasi yang ditampilkan.
	        
	      Return
	      Interface view data lokasi.
	    */
	    public function actionViewLokasi()
	    {
	      $menuid = 14;
        $parentmenuid = 6;
	      $userid_actor = Yii::app()->request->getParam('userid_actor');
	      $idlokasi = Yii::app()->request->getParam('idlokasi');
	      $do_edit = Yii::app()->request->getParam('do_edit');
	      $active_option = array('N' => 'Aktif', 'Y' => 'Tidak aktif');

	      $idgroup = FHelper::GetGroupId($userid_actor);
        
        if(FHelper::AllowMenu($menuid, $idgroup, 'read'))
        {
          $Criteria = new CDbCriteria();
          $Criteria->condition = 'branch_id = :idlokasi';
          $Criteria->params = array(':idlokasi' => $idlokasi);
  
          $branch_list = mtr_branch::model()->find($Criteria);
  
          $form = new frmEditLokasi();
          $form['nama'] = $branch_list['name'];
          $form['kode'] = $branch_list['code'];
          $form['inisial'] = $branch_list['initial'];
          $form['alamat'] = $branch_list['address'];
          $form['kota'] = $branch_list['city'];
          $form['zip'] = $branch_list['zip'];
          $form['negara'] = $branch_list['country'];
          $form['zip'] = $branch_list['zip'];
          $form['telepon'] = $branch_list['phone'];
          $form['fax'] = $branch_list['fax'];
          $form['is_deact'] = $branch_list['is_deact'];
  
          //show form add lokasi
          $bread_crumb_list = 
            '<li>Data Master</li>' .
            
            '<li>'.
              '<span> > </span>'.
              '<a href="#" onclick="ShowLocationList('.$userid_actor.')">Cabang/Toko/Outlet</a>'.
            '</li>'.
            
            '<li>'.
              '<span> > </span>'.
              'View Cabang/Toko/Outlet'.
            '</li>';
            
          $html = $this->renderPartial(
            'v_view_lokasi',
            array(
              'form' => $form,
              'userid_actor' => $userid_actor,
              'idlokasi' => $idlokasi,
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
        }
        else
        {
          $this->actionShowInvalidAccess($userid_actor);
        }
	      
	      
	    }
	    
	    /*
	      actionListActionLokasi
	      
	      Deskripsi
	      Action untuk menerima ajax call untuk menangani table wise action.
	      
	      Parameter
	      action
	        Integer. Menentukan action yang diambil terhadap list.
	        1 = delete
	        2 = set inactive
	        3 = set active
	        
	      items
	        Array. Berisi id lokasi.
	        
	      Return
	      List lokasi.
	    */
	    public function actionListActionLokasi()
	    {
	      $action_type = Yii::app()->request->getParam('data_master_action_type');
	      $item_list = Yii::app()->request->getParam('selected_item_list');
	      
	      $Criteria = new CDbCriteria();
	      $Criteria->condition = 'branch_id = :idlokasi';
            
	      if($action_type > 0)
	      {
	        foreach($item_list as $key => $value)
          {
            $Criteria->params = array(':idlokasi' => $value);
            $bank = mtr_branch::model()->find($Criteria);
            
            $bank['is_deact'] = ($action_type == 1 ? 'Y' : 'N');
            $bank->update();
          }
          
          $this->actionListLokasi();
	      }
	    }

	  //lokasi - end


	  //produsen - begin

	    /*
        actionProdusen

        Deskripsi
        Action untuk menampilkan daftar produsen
      */
      public function actionProdusen()
      {
        $Criteria = new CDbCriteria();
        $Criteria->condition = 'is_del = 0';

        $userid_actor = Yii::app()->request->getParam('userid_actor');
        $produsen_list = inv_produsen::model()->findAll($Criteria);
        $TheMenu = FHelper::RenderMenu(0, $userid_actor);

        $this->layout = 'setting';
        $TheContent = $this->renderPartial(
          'v_list_produsen',
          array(
            'userid_actor' => $userid_actor,
            'produsen_list' => $produsen_list
          ),
          true
        );

        $this->render(
          'index_datamaster_produsen',
          array(
            'TheMenu' => $TheMenu,
            'TheContent' => $TheContent,
            'userid_actor' => $userid_actor
          )
        );
      }

      /*
        actionListProdusen

        Deskripsi
        Action untuk menampilkan daftar produsen
      */
      public function actionListProdusen()
      {
        $Criteria = new CDbCriteria();
        $Criteria->condition = 'is_del = 0';

        $userid_actor = Yii::app()->request->getParam('userid_actor');
        $produsen_list = inv_produsen::model()->findAll($Criteria);
        $TheMenu = FHelper::RenderMenu(0, $userid_actor);

        $this->layout = 'setting';
        $TheContent = $this->renderPartial(
          'v_list_produsen',
          array(
            'userid_actor' => $userid_actor,
            'produsen_list' => $produsen_list
          ),
          true
        );

        echo CJSON::encode(array('html' => $TheContent));
      }

	    /*
	      actionAddProdusen

	      Deskripsi
	      Action untuk menampilkan form menambah produsen
	    */
	    public function actionAddProdusen()
	    {
	      $userid_actor = Yii::app()->request->getParam('userid_actor');
	      $producers = inv_produsen::model()->findAll();
	      
	      

	      $form = new frmEditProdusen();

	      $do_add = Yii::app()->request->getParam('do_add');

	      if(isset($do_add))
	      {
	        if($do_add == 1)
	        {
	          //proses form submission

            $form->attributes = Yii::app()->request->getParam('frmEditProdusen');

            if($form->validate())
            {
              //simpan record ke tabel
              $mtr_branch = new mtr_branch();
              $mtr_branch['name'] = $form['nama'];
              $mtr_branch['address'] = $form['alamat'];
              $mtr_branch['phone'] = $form['telepon'];
              $mtr_branch['fax'] = $form['fax'];
              $mtr_branch->save();

              //tampilkan informasi sukses menambahkan record produsen
              $html = $this->renderPartial(
                'v_addprodusen_success',
                array(),
                true
              );
            }
            else
            {
              //tampilkan form
              $html = $this->renderPartial(
                'vfrm_addprodusen',
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
	          //batal menambah produsen.
	          //alihkan ke view list produsen.
	          $Criteria = new CDbCriteria();
            $Criteria->condition = 'is_del = 0';

            $userid_actor = Yii::app()->request->getParam('userid_actor');
            $produsen_list = inv_produsen::model()->findAll($Criteria);

            $html = $this->renderPartial(
              'v_list_produsen',
              array(
                'userid_actor' => $userid_actor,
                'produsen_list' => $produsen_list
              ),
              true
            );
	        }

	      }
	      else
	      {
	        //show form add produsen
	        $html = $this->renderPartial(
            'vfrm_addprodusen',
            array(
              'form' => $form,
              'userid_actor' => $userid_actor,
            ),
            true
          );
	      }

        echo CJSON::encode(array('html' => $html));
	    }

	    public function actionEditProdusen()
	    {
	      $userid_actor = Yii::app()->request->getParam('userid_actor');
	      $idprodusen = Yii::app()->request->getParam('idprodusen');
	      $do_edit = Yii::app()->request->getParam('do_edit');

	      if(isset($do_edit))
	      {
	        if($do_edit == 1)
	        {
	          //proses edit form submission

            $form = new frmEditLokasi();
            $form->attributes = Yii::app()->request->getParam('frmEditProdusen');

            if($form->validate())
            {
              $Criteria = new CDbCriteria();
              $Criteria->condition = 'id = :idprodusen';
              $Criteria->params = array(':idprodusen' => $idprodusen);

              //simpan record ke tabel
              $inv_produsen = inv_produsen::model()->find($Criteria);
              $inv_produsen['nama'] = $form['nama'];
              $inv_produsen['alamat'] = $form['alamat'];
              $inv_produsen['telepon'] = $form['telepon'];
              $inv_produsen->update();

              //tampilkan informasi sukses menambahkan record produsen
              $html = $this->renderPartial(
                'v_editprodusen_success',
                array(),
                true
              );
            }
            else
            {
              //tampilkan form
              $html = $this->renderPartial(
                'vfrm_editprodusen',
                array(
                  'form' => $form,
                  'userid_actor' => $userid_actor,
                  'idprodusen' => $idprodusen
                ),
                true
              );
            }
	        }
	        else
	        {
	          //batal edit
	          //kembali ke daftar produsen
	          $Criteria = new CDbCriteria();
            $Criteria->condition = 'is_del = 0';

            $userid_actor = Yii::app()->request->getParam('userid_actor');
            $producers = inv_produsen::model()->findAll($Criteria);

            $html = $this->renderPartial(
              'v_list_produsen',
              array(
                'userid_actor' => $userid_actor,
                'producers' => $producers
              ),
              true
            );
	        }

	      }
	      else
	      {
	        //tampilkan form edit produsen

	        $Criteria = new CDbCriteria();
          $Criteria->condition = 'id = :idprodusen';
          $Criteria->params = array(':idprodusen' => $idprodusen);

          $producers = inv_produsen::model()->find($Criteria);

          $form = new frmEditProdusen();
          $form['nama'] = $producers['nama'];
          $form['alamat'] = $producers['alamat'];
          $form['telepon'] = $producers['telepon'];

	        //show form add lokasi
	        $html = $this->renderPartial(
            'vfrm_editprodusen',
            array(
              'form' => $form,
              'userid_actor' => $userid_actor,
              'idprodusen' => $idprodusen
            ),
            true
          );
	      }

        echo CJSON::encode(array('html' => $html));
	    }

	    public function actionHapusProdusen()
	    {
	      $userid_actor = Yii::app()->request->getParam('userid_actor');
	      $idprodusen = Yii::app()->request->getParam('idprodusen');

	      $Criteria = new CDbCriteria();
        $Criteria->condition = 'id = :idprodusen';
        $Criteria->params = array(':idprodusen' => $idprodusen);

        //update record di tabel
        $inv_produsen = inv_produsen::model()->find($Criteria);
        $inv_produsen['is_del'] = 1;
        $inv_produsen->update();

        //tampilkan informasi sukses menambahkan record lokasi
        $html = $this->renderPartial(
          'v_deleteprodusen_success',
          array(),
          true
        );

        echo CJSON::encode(array('html' => $html));
	    }
	    
	    

	  //produsen - end

	  //bank - begin



	    /*
	      actionBank

	      Deskripsi
	      Action untuk menampilkan daftar bank

	    */
	    public function actionBank()
	    {
	      $menuid = 13;
	      $parentmenuid = 6;
	      $Criteria = new CDbCriteria();
	      $Criteria->condition = 'is_del = 0';

	      $userid_actor = Yii::app()->request->getParam('userid_actor');
	      $this->idlokasi = Yii::app()->request->cookies['idlokasi']->value;
	      
	      $idgroup = FHelper::GetGroupId($userid_actor);
        
        if(FHelper::AllowMenu($menuid, $idgroup, 'read'))
        {
          $banks = sys_bank::model()->findAll($Criteria);
          $TheMenu = FHelper::RenderMenu(0, $userid_actor, $parentmenuid);
  
          $this->userid_actor = $userid_actor;
          $this->parentmenuid = $parentmenuid;
          
          $this->bread_crumb_list = '
            <li>
              Data Master
            </li>
            <li>
              <span>></span>
            </li>
            <li>
              Bank
            </li>';
          
          $this->layout = 'layout-baru';
          $TheContent = $this->renderPartial(
            'v_list_bank',
            array(
              'userid_actor' => $userid_actor,
              'banks' => $banks,
              'menuid' => $menuid
            ),
            true
          );
  
          $this->render(
            'index_general',
            array(
              'TheMenu' => $TheMenu,
              'TheContent' => $TheContent,
              'userid_actor' => $userid_actor
            )
          );
        }
        else
        {
          $this->actionShowInvalidAccess($userid_actor, false);
        }
	      
	      
	    }


	    /*
	      actionLIstBank

	      Deskripsi
	      Action untuk menampilkan daftar bank

	    */
	    public function actionListBank()
	    {
	      $menuid = 13;
	      $parentmenuid = 6;
	      $Criteria = new CDbCriteria();
	      $Criteria->condition = 'is_del = 0';

	      $userid_actor = Yii::app()->request->getParam('userid_actor');
	      
	      $idgroup = FHelper::GetGroupId($userid_actor);
        
        if(FHelper::AllowMenu($menuid, $idgroup, 'read'))
        {
          $banks = sys_bank::model()->findAll($Criteria);
          $TheMenu = FHelper::RenderMenu(0, $userid_actor, $parentmenuid);
  
          $this->layout = 'setting';
          $html = $this->renderPartial(
            'v_list_bank',
            array(
              'userid_actor' => $userid_actor,
              'banks' => $banks,
              'menuid' => $menuid
            ),
            true
          );
          
          $bread_crumb_list = 
            '<li>Data Master</li>' .
            
            '<li>'.
              '<span> > </span>'.
              'Bank'.
            '</li>';
  
          echo CJSON::encode(
            array(
              'html' => $html, 
              'bread_crumb_list' => $bread_crumb_list
            )
          );
        }
        else
        {
          $this->actionShowInvalidAccess($userid_actor);
        }
	      
	      
	    }

      /*
        actionMasterAddBank

        Deskripsi
        Action untuk menampilkan form penambahan bank dan mengolah form
        submission.
      */
	    public function actionAddBank()
	    {              
	      $menuid = 13;
	      $parentmenuid = 6;
	      $userid_actor = Yii::app()->request->getParam('userid_actor');
	      $idgroup = FHelper::GetGroupId($userid_actor);
        
        if(FHelper::AllowMenu($menuid, $idgroup, 'write'))
        {
          $banks = sys_bank::model()->findAll();
          $active_option = array('N' => 'Aktif', 'Y' => 'Tidak aktif');
  
          $form = new frmEditBank();
  
          $do_add = Yii::app()->request->getParam('do_add');
  
          if(isset($do_add))
          {
            if($do_add == 1)
            {
              //proses form submission
  
              $form->attributes = Yii::app()->request->getParam('frmEditBank');
  
              if($form->validate())
              {
                //simpan record ke tabel
                $sys_bank = new sys_bank();
                $sys_bank['nama'] = Defense::Sanitize($form['nama']);
                $sys_bank['alamat'] = Defense::Sanitize($form['alamat']);
                $sys_bank['kota'] = Defense::Sanitize($form['kota']);
                $sys_bank['zip'] = Defense::Sanitize($form['zip']);
                $sys_bank['negara'] = Defense::Sanitize($form['negara']);
                $sys_bank['telepon'] = Defense::Sanitize($form['telepon']);
                $sys_bank['rekening'] = Defense::Sanitize($form['rekening']);
                $sys_bank['is_deact'] = Defense::Sanitize($form['is_deact']);
                $sys_bank->save();
                $idbank = $sys_bank->getPrimaryKey();
  
                //tampilkan informasi sukses menambahkan record lokasi
                $bread_crumb_list = 
                  '<li>Data Master</li>' .
                  
                  '<li>'.
                    '<span> > </span>'.
                    '<a href="#" onclick="ShowBankList('.$userid_actor.');">Bank</a>'.
                  '</li>'.
                  
                  '<li>'.
                    '<span> > </span>'.
                    'Tambah Bank'.
                  '</li>';
                  
                $html = $this->renderPartial(
                  'v_addbank_success',
                  array(
                    'userid_actor' => $userid_actor,
                    'idbank' => $idbank,
                    'form' => $form,
                    'active_option' => $active_option,
                  ),
                  true
                );
              }
              else
              {
                //tampilkan form
                
                $bread_crumb_list = 
                  '<li>Data Master</li>' .
                  
                  '<li>'.
                    '<span> > </span>'.
                    '<a href="#" onclick="ShowBankList('.$userid_actor.');">Bank</a>'.
                  '</li>'.
                  
                  '<li>'.
                    '<span> > </span>'.
                    'Tambah Bank'.
                  '</li>';
                  
                $html = $this->renderPartial(
                  'vfrm_addbank',
                  array(
                    'form' => $form,
                    'userid_actor' => $userid_actor,
                    'active_option' => $active_option,
                  ),
                  true
                );
              }
            }
            else
            {
              //batal menambah lokasi.
              //alihkan ke view list lokasi.
              $Criteria = new CDbCriteria();
              $Criteria->condition = 'is_del = 0';
  
              $userid_actor = Yii::app()->request->getParam('userid_actor');
              $banks = sys_bank::model()->findAll($Criteria);
  
              $bread_crumb_list = 
                '<li>Data Master</li>' .
                
                '<li>'.
                  '<span> > </span>'.
                  'Bank'.
                '</li>';
                
              $html = $this->renderPartial(
                'v_list_bank',
                array(
                  'userid_actor' => $userid_actor,
                  'banks' => $banks,
                  'menuid' => $menuid
                ),
                true
              );
            }
  
          }
          else
          {
            //show form add bank
            
            $bread_crumb_list = 
              '<li>Data Master</li>' .
              
              '<li>'.
                '<span> > </span>'.
                '<a href="#" onclick="ShowBankList('.$userid_actor.');">Bank</a>'.
              '</li>'.
              
              '<li>'.
                '<span> > </span>'.
                'Tambah Bank'.
              '</li>';
              
            $form['negara'] = 'Indonesia';
            $html = $this->renderPartial(
              'vfrm_addbank',
              array(
                'form' => $form,
                'userid_actor' => $userid_actor,
                'active_option' => $active_option
              ),
              true
            );
          }
  
          echo CJSON::encode(
            array(
              'html' => $html, 
              'bread_crumb_list' => $bread_crumb_list
            )
          );
        }
        else
        {
          $this->actionShowInvalidAccess($userid_actor);
        }
        
	      
	      
	    }

	    /*
	      actionEditBank

	      Deskripsi
	      Action untuk menampilkan form edit bank dan mengolah form submission.
	    */
	    public function actionEditBank()
	    {
	      $menuid = 13;
	      $parentmenuid = 6;
	      $userid_actor = Yii::app()->request->getParam('userid_actor');
	      $idbank = Yii::app()->request->getParam('idbank');
	      $do_edit = Yii::app()->request->getParam('do_edit');
	      $active_option = array('N' => 'Aktif', 'Y' => 'Tidak aktif');
	      
	      
	      $idgroup = FHelper::GetGroupId($userid_actor);
        
        if(FHelper::AllowMenu($menuid, $idgroup, 'edit'))
        {
          if(isset($do_edit))
          {
            if($do_edit == 1)
            {
              //proses edit form submission
  
              $form = new frmEditBank();
              $form->attributes = Yii::app()->request->getParam('frmEditBank');
  
              if($form->validate())
              {
                $Criteria = new CDbCriteria();
                $Criteria->condition = 'id = :idbank';
                $Criteria->params = array(':idbank' => $idbank);
                
                Yii::log('form[is_deact] = ' . $form['is_deact'] , 'info');
  
                //simpan record ke tabel
                $sys_bank = sys_bank::model()->find($Criteria);
                $sys_bank['nama'] = Defense::Sanitize($form['nama']);
                $sys_bank['alamat'] = Defense::Sanitize($form['alamat']);
                $sys_bank['kota'] = Defense::Sanitize($form['kota']);
                $sys_bank['zip'] = Defense::Sanitize($form['zip']);
                $sys_bank['negara'] = Defense::Sanitize($form['negara']);
                $sys_bank['telepon'] = Defense::Sanitize($form['telepon']);
                $sys_bank['rekening'] = Defense::Sanitize($form['rekening']);
                $sys_bank['is_deact'] = Defense::Sanitize($form['is_deact']);
                $sys_bank->update();
  
                //tampilkan informasi sukses menambahkan record lokasi
                $bread_crumb_list = 
                  '<li>Data Master</li>' .
                  
                  '<li>'.
                    '<span> > </span>'.
                    '<a href="#" onclick="ShowBankList('.$userid_actor.');">Bank</a>'.
                  '</li>'.
                  
                  '<li>'.
                    '<span> > </span>'.
                    'Edit Bank'.
                  '</li>';
                  
                $html = $this->renderPartial(
                  'v_editbank_success',
                  array(
                    'userid_actor' => $userid_actor,
                    'idbank' => $idbank,
                    'form' => $form,
                    'active_option' => $active_option
                  ),
                  true
                );
              }
              else
              {
                //tampilkan form
                $bread_crumb_list = 
                  '<li>Data Master</li>' .
                  
                  '<li>'.
                    '<span> > </span>'.
                    '<a href="#" onclick="ShowBankList('.$userid_actor.');">Bank</a>'.
                  '</li>'.
                  
                  '<li>'.
                    '<span> > </span>'.
                    'Edit Bank'.
                  '</li>';
                  
                $html = $this->renderPartial(
                  'vfrm_editbank',
                  array(
                    'form' => $form,
                    'userid_actor' => $userid_actor,
                    'idbank' => $idbank,
                    'active_option' => $active_option
                  ),
                  true
                );
              }
            }
            else
            {
              //batal edit
              //kembali ke daftar lokasi
              $Criteria = new CDbCriteria();
              $Criteria->condition = 'is_del = 0';
  
              $userid_actor = Yii::app()->request->getParam('userid_actor');
              $banks = sys_bank::model()->findAll($Criteria);
  
              $bread_crumb_list = 
                '<li>Data Master</li>' .
                
                '<li>'.
                  '<span> > </span>'.
                  'Bank'.
                '</li>';
                
              $html = $this->renderPartial(
                'v_list_bank',
                array(
                  'userid_actor' => $userid_actor,
                  'banks' => $banks,
                  'active_option' => $active_option,
                  'menuid' => $menuid
                ),
                true
              );
            }
  
          }
          else
          {
            //tampilkan form edit lokasi
  
            $Criteria = new CDbCriteria();
            $Criteria->condition = 'id = :idbank';
            $Criteria->params = array(':idbank' => $idbank);
  
            $banks = sys_bank::model()->find($Criteria);
  
            $form = new frmEditBank();
            $form['nama'] = $banks['nama'];
            $form['alamat'] = $banks['alamat'];
            $form['kota'] = $banks['kota'];
            $form['zip'] = $banks['zip'];
            $form['negara'] = $banks['negara'];
            $form['telepon'] = $banks['telepon'];
            $form['rekening'] = $banks['rekening'];
            $form['is_deact'] = $banks['is_deact'];
  
            //show form add lokasi
            $bread_crumb_list = 
              '<li>Data Master</li>' .
              
              '<li>'.
                '<span> > </span>'.
                '<a href="#" onclick="ShowBankList('.$userid_actor.');">Bank</a>'.
              '</li>'.
              
              '<li>'.
                '<span> > </span>'.
                'Edit Bank'.
              '</li>';
              
            $html = $this->renderPartial(
              'vfrm_editbank',
              array(
                'form' => $form,
                'userid_actor' => $userid_actor,
                'idbank' => $idbank,
                'active_option' => $active_option
              ),
              true
            );
          }
  
          echo CJSON::encode(
            array(
              'html' => $html, 
              'bread_crumb_list' => $bread_crumb_list
            )
          );
        }
        else
        {
          $this->actionShowInvalidAccess($userid_actor);
        }

	      
	    }

	    /*
	      actionDeleteBank

	      Deskripsi
	      Action untuk mengubah flag is_del pada record sys_bank
	    */
	    public function actionDeleteBank()
	    {
	      $menuid = 13;
	      $parentmenuid = 6;
	      $userid_actor = Yii::app()->request->getParam('userid_actor');
	      $idbank = Yii::app()->request->getParam('idbank');
	      
	      $idgroup = FHelper::GetGroupId($userid_actor);
        
        if(FHelper::AllowMenu($menuid, $idgroup, 'delete'))
        {
          $Criteria = new CDbCriteria();
          $Criteria->condition = 'id = :idbank';
          $Criteria->params = array(':idbank' => $idbank);
  
          //update record di tabel
          $sys_bank = sys_bank::model()->find($Criteria);
          $sys_bank['is_del'] = 1;
          $sys_bank->update();
          
          $this->actionListBank();
        }
        else
        {
          $this->actionShowInvalidAccess($userid_actor);
        }
	    }
	    
	    /*
	      actionViewBank
	      
	      Deskripsi
	      Action untuk menampilkan informasi bank. Juga memberikan akses untuk 
	      mengedit atau menghapus bank.
	      
	      Parameter
	      userid_actor
	        Integer. Id user yang menggunakan system.
	      idbank
	        Integer. Menerangkan id bank yang ditampilkan.
	        
	      Return
	      Interface view data bank.
	    */
	    public function actionViewBank()
	    {
	      $menuid = 13;
	      $parentmenuid = 6;
	      $userid_actor = Yii::app()->request->getParam('userid_actor');
	      $idbank = Yii::app()->request->getParam('idbank');
	      $do_edit = Yii::app()->request->getParam('do_edit');
	      $active_option = array('N' => 'Aktif', 'Y' => 'Tidak aktif');
	      
	      
	      $idgroup = FHelper::GetGroupId($userid_actor);
        
        if(FHelper::AllowMenu($menuid, $idgroup, 'read'))
        {
          $Criteria = new CDbCriteria();
          $Criteria->condition = 'id = :idbank';
          $Criteria->params = array(':idbank' => $idbank);
  
          $banks = sys_bank::model()->find($Criteria);
  
          $form = new frmEditBank();
          $form['nama'] = $banks['nama'];
          $form['alamat'] = $banks['alamat'];
          $form['rekening'] = $banks['rekening'];
          $form['telepon'] = $banks['telepon'];
          $form['is_deact'] = $banks['is_deact'];
  
          //show form add lokasi
          $bread_crumb_list = 
            '<li>Data Master</li>' .
            
            '<li>'.
              '<span> > </span>'.
              '<a href="#" onclick="ShowBankList('.$userid_actor.')">Bank</a>'.
            '</li>'.
            
            '<li>'.
              '<span> > </span>'.
              'View Bank'.
            '</li>';
            
          $html = $this->renderPartial(
            'v_view_bank',
            array(
              'form' => $form,
              'userid_actor' => $userid_actor,
              'idbank' => $idbank,
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
        }
        else
        {
          $this->actionShowInvalidAccess($userid_actor);
        }
        

	      
	    }
	    
	    /*
	      actionListActionBank
	      
	      Deskripsi
	      Action untuk menerima ajax call untuk menangani table wise action.
	      
	      Parameter
	      action
	        Integer. Menentukan action yang diambil terhadap list.
	        1 = delete
	        2 = set inactive
	        3 = set active
	        
	      items
	        Array. Berisi id bank.
	        
	      Return
	      List bank.
	    */
	    public function actionListActionBank()
	    {
	      $action_type = Yii::app()->request->getParam('data_master_action_type');
	      $item_list = Yii::app()->request->getParam('selected_item_list');
	      
	      $Criteria = new CDbCriteria();
	      $Criteria->condition = 'id = :idbank';
            
	      if($action_type > 0)
	      {
	        foreach($item_list as $key => $value)
          {
            $Criteria->params = array(':idbank' => $value);
            $bank = sys_bank::model()->find($Criteria);
            
            $bank['is_deact'] = ($action_type == 1 ? 'Y' : 'N');
            $bank->update();
          }
          
          $this->actionListBank();
	      }
	    }

	  //bank - end


	  //metode bayar - begin



	    /*
	      actionMasterListMetode

	      Deskripsi
	      Action untuk menampilkan daftar metode

	    */
	    public function actionMetode()
	    {
	      $Criteria = new CDbCriteria();
	      $Criteria->condition = 'is_del = 0';

	      $userid_actor = Yii::app()->request->getParam('userid_actor');
	      $methods = sys_metode_bayar::model()->findAll($Criteria);
	      $TheMenu = FHelper::RenderMenu(0, $userid_actor);

	      $this->layout = 'setting';
	      $TheContent = $this->renderPartial(
          'v_list_metode',
          array(
            'userid_actor' => $userid_actor,
            'methods' => $methods
          ),
          true
        );

        $this->render(
          'index_datamaster_metode',
          array(
            'TheMenu' => $TheMenu,
            'TheContent' => $TheContent,
            'userid_actor' => $userid_actor
          )
        );
	    }

	    /*
	      actionListMetode

	      Deskripsi
	      Action untuk menampilkan daftar metode

	    */
	    public function actionListMetode()
	    {
	      $Criteria = new CDbCriteria();
	      $Criteria->condition = 'is_del = 0';

	      $userid_actor = Yii::app()->request->getParam('userid_actor');
	      $methods = sys_metode_bayar::model()->findAll($Criteria);
	      $TheMenu = FHelper::RenderMenu(0, $userid_actor);

	      $this->layout = 'setting';
	      $TheContent = $this->renderPartial(
          'v_list_metode',
          array(
            'userid_actor' => $userid_actor,
            'methods' => $methods
          ),
          true
        );

        echo CJSON::encode(array('html' => $TheContent));
	    }

      /*
        actionAddMetode

        Deskripsi
        Action untuk menampilkan form penambahan metode bayar dan mengolah form
        submission.
      */
	    public function actionAddMetode()
	    {
	      $userid_actor = Yii::app()->request->getParam('userid_actor');
	      $methods = sys_metode_bayar::model()->findAll();

	      $form = new frmEditMetode();

	      $do_add = Yii::app()->request->getParam('do_add');

	      if(isset($do_add))
	      {
	        if($do_add == 1)
	        {
	          //proses form submission

            $form->attributes = Yii::app()->request->getParam('frmEditMetode');

            if($form->validate())
            {
              //simpan record ke tabel
              $sys_metode_bayar = new sys_metode_bayar();
              $sys_metode_bayar['nama'] = $form['nama'];
              $sys_metode_bayar->save();

              //tampilkan informasi sukses menambahkan record lokasi
              $html = $this->renderPartial(
                'v_addmetode_success',
                array(),
                true
              );
            }
            else
            {
              //tampilkan form
              $html = $this->renderPartial(
                'vfrm_addmetode',
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
	          //batal menambah lokasi.
	          //alihkan ke view list lokasi.
	          $Criteria = new CDbCriteria();
            $Criteria->condition = 'is_del = 0';

            $userid_actor = Yii::app()->request->getParam('userid_actor');
            $methods = sys_metode_bayar::model()->findAll($Criteria);

            $html = $this->renderPartial(
              'v_list_metode',
              array(
                'userid_actor' => $userid_actor,
                'methods' => $methods
              ),
              true
            );
	        }

	      }
	      else
	      {
	        //show form add lokasi
	        $html = $this->renderPartial(
            'vfrm_addmetode',
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
	      actionEditMetode

	      Deskripsi
	      Action untuk menampilkan form edit metode bayar dan mengolah form submission.
	    */
	    public function actionEditMetode()
	    {
	      $userid_actor = Yii::app()->request->getParam('userid_actor');
	      $idmetode = Yii::app()->request->getParam('idmetode');
	      $do_edit = Yii::app()->request->getParam('do_edit');

	      if(isset($do_edit))
	      {
	        if($do_edit == 1)
	        {
	          //proses edit form submission

            $form = new frmEditMetode();
            $form->attributes = Yii::app()->request->getParam('frmEditMetode');

            if($form->validate())
            {
              $Criteria = new CDbCriteria();
              $Criteria->condition = 'id = :idmetode';
              $Criteria->params = array(':idmetode' => $idmetode);

              //simpan record ke tabel
              $sys_metode_bayar = sys_metode_bayar::model()->find($Criteria);
              $sys_metode_bayar['nama'] = $form['nama'];
              $sys_metode_bayar->update();

              //tampilkan informasi sukses menambahkan record lokasi
              $html = $this->renderPartial(
                'v_editmetode_success',
                array(),
                true
              );
            }
            else
            {
              //tampilkan form
              $html = $this->renderPartial(
                'vfrm_editmetode',
                array(
                  'form' => $form,
                  'userid_actor' => $userid_actor,
                  'idmetode' => $idmetode
                ),
                true
              );
            }
	        }
	        else
	        {
	          //batal edit
	          //kembali ke daftar lokasi
	          $Criteria = new CDbCriteria();
            $Criteria->condition = 'is_del = 0';

            $userid_actor = Yii::app()->request->getParam('userid_actor');
            $methods = sys_metode_bayar::model()->findAll($Criteria);

            $html = $this->renderPartial(
              'v_list_metode',
              array(
                'userid_actor' => $userid_actor,
                'methods' => $methods
              ),
              true
            );
	        }

	      }
	      else
	      {
	        //tampilkan form edit lokasi

	        $Criteria = new CDbCriteria();
          $Criteria->condition = 'id = :idmetode';
          $Criteria->params = array(':idmetode' => $idmetode);

          $methods = sys_metode_bayar::model()->find($Criteria);

          $form = new frmEditMetode();
          $form['nama'] = $methods['nama'];

	        //show form add lokasi
	        $html = $this->renderPartial(
            'vfrm_editmetode',
            array(
              'form' => $form,
              'userid_actor' => $userid_actor,
              'idmetode' => $idmetode
            ),
            true
          );
	      }

        echo CJSON::encode(array('html' => $html));
	    }

	    /*
	      actionDeleteMetode

	      Deskripsi
	      Action untuk mengubah flag is_del pada record sys_metode_bayar
	    */
	    public function actionDeleteMetode()
	    {
	      $userid_actor = Yii::app()->request->getParam('userid_actor');
	      $idmetode = Yii::app()->request->getParam('idmetode');

	      $Criteria = new CDbCriteria();
        $Criteria->condition = 'id = :idmetode';
        $Criteria->params = array(':idmetode' => $idmetode);

        //update record di tabel
        $sys_metode_bayar = sys_metode_bayar::model()->find($Criteria);
        $sys_metode_bayar['is_del'] = 1;
        $sys_metode_bayar->update();

        //tampilkan informasi sukses menambahkan record lokasi
        $html = $this->renderPartial(
          'v_deletemetode_success',
          array(),
          true
        );

        echo CJSON::encode(array('html' => $html));
	    }

	  //metode bayar - end


	  //supplier - begin



	    /*
	      actionSupplier

	      Deskripsi
	      Action untuk menampilkan daftar supplier

	    */
	    public function actionSupplier()
	    {
	      $this->menuid = 10;
	      $this->parentmenuid = 6;
	      $userid_actor = Yii::app()->request->getParam('userid_actor');
	      $this->userid_actor = $userid_actor;
	      $this->idlokasi = Yii::app()->request->cookies['idlokasi']->value;
	      $idgroup = FHelper::GetGroupId($userid_actor);
	      
	      $Criteria = new CDbCriteria();
	      $Criteria->condition = 'is_del = \'N\'';
        
        if(FHelper::AllowMenu($this->menuid, $idgroup, 'read'))
        {
          $this->userid_actor = $userid_actor;
          
          $suppliers = mtr_supplier::model()->findAll($Criteria);
          $TheMenu = FHelper::RenderMenu(0, $userid_actor, $parentmenuid);
          
          $this->bread_crumb_list = '
            <li>
              Data Master
            </li>
            <li>
              <span>></span>
            </li>
            <li>
              Supplier
            </li>';
  
          $this->layout = 'layout-baru';
          $TheContent = $this->renderPartial(
            'v_list_supplier',
            array(
              'userid_actor' => $userid_actor,
              'suppliers' => $suppliers,
              'menuid' => $this->menuid
            ),
            true
          );
  
          $this->render(
            //'index_datamaster_supplier',
            'index_general',
            array(
              'TheMenu' => $TheMenu,
              'TheContent' => $TheContent,
              'userid_actor' => $userid_actor,
            )
          );
        }
        else
        {
          $this->actionShowInvalidAccess($userid_actor, false);
        }
        
	      
	      
	    }

	    /*
	      actionListSupplier

	      Deskripsi
	      Action untuk menampilkan daftar supplier

	    */
	    public function actionListSupplier()
	    {
	      $menuid = 10;
	      $parentmenuid = 6;
	      $Criteria = new CDbCriteria();
	      $Criteria->condition = 'is_del = \'N\'';

	      $userid_actor = Yii::app()->request->getParam('userid_actor');
	      
	      
	      $idgroup = FHelper::GetGroupId($userid_actor);
        
        if(FHelper::AllowMenu($menuid, $idgroup, 'read'))
        {
          $suppliers = mtr_supplier::model()->findAll($Criteria);

          $TheContent = $this->renderPartial(
            'v_list_supplier',
            array(
              'userid_actor' => $userid_actor,
              'suppliers' => $suppliers,
              'menuid' => $menuid
            ),
            true
          );
          
          $bread_crumb_list = 
            '<li>Data Master</li>' .
            
            '<li>'.
              '<span> > </span>'.
              'Supplier'.
            '</li>';
  
          echo CJSON::encode(
            array(
              'html' => $TheContent, 
              'bread_crumb_list' => $bread_crumb_list
            )
          );
        }
        else
        {
          $this->actionShowInvalidAccess($userid_actor);
        }
        
	      
	      
	    }

      /*
        actionAddSupplier

        Deskripsi
        Action untuk menampilkan form penambahan supplier dan mengolah form
        submission.
      */
	    public function actionAddSupplier()
	    {
	      $menuid = 10;
	      $parentmenuid = 6;
	      $userid_actor = Yii::app()->request->getParam('userid_actor');
	      
	      $idgroup = FHelper::GetGroupId($userid_actor);
        
        if(FHelper::AllowMenu($menuid, $idgroup, 'write'))
        {
          $supliers = mtr_supplier::model()->findAll();
          $active_option = array('N' => 'Aktif', 'Y' => 'Tidak aktif');
  
          $form = new frmEditSupplier();
  
          $do_add = Yii::app()->request->getParam('do_add');
  
          if(isset($do_add))
          {
            if($do_add == 1)
            {
              //proses form submission
  
              $form->attributes = Yii::app()->request->getParam('frmEditSupplier');
              Yii::log('form[kode] = ' . $form['kode'], 'info');
  
              if($form->validate())
              {
                Yii::log('validated', 'info');
                
                //simpan record ke tabel
                $suppliers = new mtr_supplier();
                $suppliers['name'] = Defense::Sanitize($form['nama']);
                $suppliers['code'] = Defense::Sanitize($form['kode']);
                $suppliers['address'] = $form['alamat'];
                $suppliers['city'] = Defense::Sanitize($form['kota']);
                $suppliers['zip'] = Defense::Sanitize($form['zip']);
                $suppliers['country'] = Defense::Sanitize($form['negara']);
                $suppliers['phone'] = Defense::Sanitize($form['telepon']);
                $suppliers['mobile'] = Defense::Sanitize($form['mobile']);
                $suppliers['fax'] = Defense::Sanitize($form['fax']);
                $suppliers['email'] = Defense::Sanitize($form['email']);
                $suppliers['npwp'] = Defense::Sanitize($form['npwp']);
                $suppliers['contact'] = Defense::Sanitize($form['contact']);
                $suppliers['is_del'] = 'N';
                $suppliers['is_deact'] = Defense::Sanitize($form['is_deact']);
                $suppliers['note'] = '';
                $suppliers->insert();
                $idsupplier = $suppliers->getPrimaryKey();
  
                //tampilkan informasi sukses menambahkan record lokasi
                $bread_crumb_list = 
                  '<li>Data Master</li>' .
                  
                  '<li>'.
                    '<span> > </span>'.
                    '<a href="#" onclick="ShowSupplierList('.$userid_actor.');">Supplier</a>'.
                  '</li>'.
                  
                  '<li>'.
                    '<span> > </span>'.
                    'Tambah Supplier'.
                  '</li>';
                
                $html = $this->renderPartial(
                  'v_addsupplier_success',
                  array(
                    'userid_actor' => $userid_actor,
                    'idsupplier' => $idsupplier,
                    'form' => $form,
                    'active_option' => $active_option,
                  ),
                  true
                );
              }
              else
              {
                //tampilkan form
                
                Yii::log('NOT validated', 'info');
                
                $bread_crumb_list = 
                  '<li>Data Master</li>' .
                  
                  '<li>'.
                    '<span> > </span>'.
                    '<a href="#" onclick="ShowSupplierList('.$userid_actor.');">Supplier</a>'.
                  '</li>'.
                  
                  '<li>'.
                    '<span> > </span>'.
                    'Tambah Supplier'.
                  '</li>';
                  
                $html = $this->renderPartial(
                  'vfrm_addsupplier',
                  array(
                    'form' => $form,
                    'userid_actor' => $userid_actor,
                    'active_option' => $active_option,
                  ),
                  true
                );
              }
            }
            else
            {
              //batal menambah lokasi.
              //alihkan ke view list supplier.
              $Criteria = new CDbCriteria();
              $Criteria->condition = 'is_del = \'N\'';
  
              $userid_actor = Yii::app()->request->getParam('userid_actor');
              $suppliers = mtr_supplier::model()->findAll($Criteria);
              
              $bread_crumb_list = 
                '<li>Data Master</li>' .
                
                '<li>'.
                  '<span> > </span>'.
                  'Supplier'.
                '</li>';
                
              $html = $this->renderPartial(
                'v_list_supplier',
                array(
                  'userid_actor' => $userid_actor,
                  'suppliers' => $suppliers,
                  'menuid' => $menuid
                ),
                true
              );
            }
  
          }
          else
          {
            //show form add lokasi
            $bread_crumb_list = 
              '<li>Data Master</li>' .
              
              '<li>'.
                '<span> > </span>'.
                '<a href="#" onclick="ShowSupplierList('.$userid_actor.');">Supplier</a>'.
              '</li>'.
              
              '<li>'.
                '<span> > </span>'.
                'Tambah Supplier'.
              '</li>';
              
            $form['negara'] = 'Indonesia';
            $form['telepon'] = '+62';
            $html = $this->renderPartial(
              'vfrm_addsupplier',
              array(
                'form' => $form,
                'userid_actor' => $userid_actor,
                'active_option' => $active_option
              ),
              true
            );
          }
  
          echo CJSON::encode(
            array(
              'html' => $html, 
              'bread_crumb_list' => $bread_crumb_list
            )
          );
        }
        else
        {
          $this->actionShowInvalidAccess($userid_actor);
        }
	      
	      
	      
	    }

	    /*
	      actionEditSupplier

	      Deskripsi
	      Action untuk menampilkan form edit supplier dan mengolah form submission.
	    */
	    public function actionEditSupplier()
	    {
	      $menuid = 10;
	      $parentmenuid = 6;
	      $userid_actor = Yii::app()->request->getParam('userid_actor');
	      $idsupplier = Yii::app()->request->getParam('idsupplier');
	      $do_edit = Yii::app()->request->getParam('do_edit');
	      $active_option = array('N' => 'Aktif', 'Y' => 'Tidak aktif');
	      
	      
	      $idgroup = FHelper::GetGroupId($userid_actor);
        
        if(FHelper::AllowMenu($menuid, $idgroup, 'edit'))
        {
          if(isset($do_edit))
          {
            if($do_edit == 1)
            {
              //proses edit form submission
  
              $form = new frmEditSupplier();
              $form->attributes = Yii::app()->request->getParam('frmEditSupplier');
  
              if($form->validate(array('nama', 'npwp', 'mobile', 'contact', 
                                       'note', 'alamat', 'kota', 'zip', 'negara', 
                                       'telepon', 'fax', 'email')))
              {
                $Criteria = new CDbCriteria();
                $Criteria->condition = 'supplier_id = :idsupplier';
                $Criteria->params = array(':idsupplier' => $idsupplier);
  
                //simpan record ke tabel
                $supplier = mtr_supplier::model()->find($Criteria);
                $supplier['name'] = Defense::Sanitize($form['nama']);
                $supplier['address'] = Defense::Sanitize($form['alamat']);
                $supplier['city'] = Defense::Sanitize($form['kota']);
                $supplier['phone'] = Defense::Sanitize($form['telepon']);
                $supplier['fax'] = Defense::Sanitize($form['fax']);
                $supplier['mobile'] = Defense::Sanitize($form['mobile']);
                $supplier['country'] = Defense::Sanitize($form['negara']);
                $supplier['zip'] = Defense::Sanitize($form['zip']);
                $supplier['email'] = Defense::Sanitize($form['email']);
                $supplier['code'] = Defense::Sanitize($form['kode']);
                $supplier['npwp'] = Defense::Sanitize($form['npwp']);
                $supplier['contact'] = Defense::Sanitize($form['contact']);
                $supplier['is_deact'] = Defense::Sanitize($form['is_deact']);
                $supplier['version'] = $supplier['version'] + 1;
                $supplier['date_update'] = date('Y-m-j H:i:s');
                $supplier['update_by'] = $userid_actor;
            
                $supplier->update();
  
                //tampilkan informasi sukses menambahkan record supplier
                $bread_crumb_list = 
                  '<li>Data Master</li>' .
                  
                  '<li>'.
                    '<span> > </span>'.
                    '<a href="#" onclick="ShowSupplierList('.$userid_actor.');">Supplier</a>'.
                  '</li>'.
                  
                  '<li>'.
                    '<span> > </span>'.
                    'Edit Supplier'.
                  '</li>';
                
                $html = $this->renderPartial(
                  'v_editsupplier_success',
                  array(
                    'userid_actor' => $userid_actor,
                    'idsupplier' => $idsupplier,
                    'form' => $form,     
                    'active_option' => $active_option
                  ),
                  true
                );
              }
              else
              {
                //tampilkan form
                $bread_crumb_list = 
                  '<li>Data Master</li>' .
                  
                  '<li>'.
                    '<span> > </span>'.
                    '<a href="#" onclick="ShowSupplierList('.$userid_actor.');">Supplier</a>'.
                  '</li>'.
                  
                  '<li>'.
                    '<span> > </span>'.
                    'Edit Supplier'.
                  '</li>';
                  
                $html = $this->renderPartial(
                  'vfrm_editsupplier',
                  array(
                    'form' => $form,
                    'userid_actor' => $userid_actor,
                    'idsupplier' => $idsupplier,
                    'active_option' => $active_option
                  ),
                  true
                );
              }
            }
            else
            {
              //batal edit
              //kembali ke daftar supplier
              $Criteria = new CDbCriteria();
              $Criteria->condition = 'is_del = 0';
  
              $userid_actor = Yii::app()->request->getParam('userid_actor');
              $suppliers = inv_supplier::model()->findAll($Criteria);
              
              $bread_crumb_list = 
                '<li>Data Master</li>' .
                
                '<li>'.
                  '<span> > </span>'.
                  'Supplier'.
                '</li>';
  
              $html = $this->renderPartial(
                'v_list_supplier',
                array(
                  'userid_actor' => $userid_actor,
                  'suppliers' => $suppliers,
                  'active_option' => $active_option,
                  'menuid' => $menuid
                ),
                true
              );
            }
  
          }
          else
          {
            //tampilkan form edit supplier
  
            $Criteria = new CDbCriteria();
            $Criteria->condition = 'supplier_id = :idsupplier';
            $Criteria->params = array(':idsupplier' => $idsupplier);
  
            $suppliers = mtr_supplier::model()->find($Criteria);
  
            $form = new frmEditSupplier();
            $form['nama'] = $suppliers['name'];
            $form['alamat'] = $suppliers['address'];
            $form['kota'] = $suppliers['city'];
            $form['telepon'] = $suppliers['phone'];
            $form['fax'] = $suppliers['fax'];
            $form['mobile'] = $suppliers['mobile'];
            $form['negara'] = $suppliers['country'];
            $form['zip'] = $suppliers['zip'];
            $form['email'] = $suppliers['email'];
            $form['kode'] = $suppliers['code'];
            $form['npwp'] = $suppliers['npwp'];
            $form['contact'] = $suppliers['contact'];
            $form['is_deact'] = $suppliers['is_deact'];
  
            //show form add lokasi
            $bread_crumb_list = 
              '<li>Data Master</li>' .
              
              '<li>'.
                '<span> > </span>'.
                '<a href="#" onclick="ShowSupplierList('.$userid_actor.');">Supplier</a>'.
              '</li>'.
              
              '<li>'.
                '<span> > </span>'.
                'Edit Supplier'.
              '</li>';
              
            $html = $this->renderPartial(
              'vfrm_editsupplier',
              array(
                'form' => $form,
                'userid_actor' => $userid_actor,
                'idsupplier' => $idsupplier,
                'active_option' => $active_option
              ),
              true
            );
            
          }
  
          echo CJSON::encode(
            array(
              'html' => $html, 
              'bread_crumb_list' => $bread_crumb_list
            )
          );
        }
        else
        {
          $this->actionShowInvalidAccess($userid_actor);
        }
	    }

	    /*
	      actionDeleteSupplier

	      Deskripsi
	      Action untuk mengubah flag is_del pada record sys_metode_bayar
	    */
	    public function actionDeleteSupplier()
	    {
	      $menuid = 10;
	      $parentmenuid = 6;
	      $userid_actor = Yii::app()->request->getParam('userid_actor');
	      $idsupplier = Yii::app()->request->getParam('idsupplier');
	      
	      
	      $idgroup = FHelper::GetGroupId($userid_actor);
        
        if(FHelper::AllowMenu($menuid, $idgroup, 'delete'))
        {
          $Criteria = new CDbCriteria();
          $Criteria->condition = 'supplier_id = :idsupplier';
          $Criteria->params = array(':idsupplier' => $idsupplier);
  
          //update record di tabel
          $supplier = mtr_supplier::model()->find($Criteria);
          $supplier['is_del'] = 'Y';
          $supplier->update();
          
          $this->actionListSupplier();
        }
        else
        {
          $this->actionShowInvalidAccess($userid_actor);
        }
	    }
	    
	    /*
	      actionViewSupplier
	      
	      Deskripsi
	      Action untuk menampilkan informasi lokasi. Juga memberikan akses untuk 
	      mengedit atau menghapus supplier.
	      
	      Parameter
	      userid_actor
	        Integer. Id user yang menggunakan system.
	      idsupplier
	        Integer. Menerangkan id supplier yang ditampilkan.
	        
	      Return
	      Interface view data supplier.
	    */
	    public function actionViewSupplier()
	    {
	      $menuid = 10;
	      $parentmenuid = 6;
	      $userid_actor = Yii::app()->request->getParam('userid_actor');
	      $idsupplier = Yii::app()->request->getParam('idsupplier');
	      $do_edit = Yii::app()->request->getParam('do_edit');
	      $active_option = array('N' => 'Aktif', 'Y' => 'Tidak aktif');
	      
	      
	      $idgroup = FHelper::GetGroupId($userid_actor);
        
        if(FHelper::AllowMenu($menuid, $idgroup, 'read'))
        {
          $Criteria = new CDbCriteria();
          $Criteria->condition = 'supplier_id = :idsupplier';
          $Criteria->params = array(':idsupplier' => $idsupplier);
  
          $supplier = mtr_supplier::model()->find($Criteria);
  
          $form = new frmEditSupplier();
          $form['nama'] = $supplier['name'];
          $form['alamat'] = $supplier['address'];
          $form['kota'] = $supplier['city'];
          $form['telepon'] = $supplier['phone'];
          $form['fax'] = $supplier['fax'];
          $form['mobile'] = $supplier['mobile'];
          $form['negara'] = $supplier['country'];
          $form['zip'] = $supplier['zip'];
          $form['email'] = $supplier['email'];
          $form['kode'] = $supplier['code'];
          $form['npwp'] = $supplier['npwp'];
          $form['contact'] = $supplier['contact'];
  
          //show form add lokasi
          $bread_crumb_list = 
            '<li>Data Master</li>' .
            
            '<li>'.
              '<span> > </span>'.
              '<a href="#" onclick="ShowSupplierList('.$userid_actor.')">Supplier</a>'.
            '</li>'.
            
            '<li>'.
              '<span> > </span>'.
              'View Supplier'.
            '</li>';
            
          $html = $this->renderPartial(
            'v_view_supplier',
            array(
              'form' => $form,
              'userid_actor' => $userid_actor,
              'idsupplier' => $idsupplier,
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
        }
        else
        {
          $this->actionShowInvalidAccess($userid_actor);
        }
        

	      
	    }
	    
	    /*
	      actionListActionSupplier
	      
	      Deskripsi
	      Action untuk mengolah action terhadap list data master (general). Untuk
	      melakukan set_active = 0 atau set_active = 1.
	      
	      Parameter
	      data_master_action_type
	        Integer. 1: set_active = 1; 1: set_active = 0
	        
        Return
        List data master yang dipaketkan dalam JSON.
	    */
	    public function actionListActionSupplier()
	    {
	      $action_type = Yii::app()->request->getParam('data_master_action_type');
	      $item_list = Yii::app()->request->getParam('selected_item_list');
	      
	      $Criteria = new CDbCriteria();
	      $Criteria->condition = 'supplier_id = :supplier_id';
            
	      if($action_type > 0)
	      {
	        foreach($item_list as $key => $value)
          {
            $Criteria->params = array(':supplier_id' => $value);
            $supplier = mtr_supplier::model()->find($Criteria);
            
            $supplier['is_deact'] = ($action_type == 1 ? 'Y' : 'N');
            $supplier->update();
          }
          
          $this->actionListSupplier();
	      }
	    }

	  //supplier - end



	  //customer perorangan - begin



	    /*
	      actionMasterListCustomer

	      Deskripsi
	      Action untuk menampilkan daftar customer

	    */
	    public function actionCustomer()
	    {
	      $menuid = 15;
	      $parentmenuid = 6;
        $Criteria = new CDbCriteria();
        $Criteria->condition = 'is_del = \'N\'';
  
        $userid_actor = Yii::app()->request->getParam('userid_actor');
        $this->idlokasi = Yii::app()->request->cookies['idlokasi']->value;
        
        $idgroup = FHelper::GetGroupId($userid_actor);
        
        if(FHelper::AllowMenu($menuid, $idgroup, 'read'))
        {
          $customers = mtr_customer::model()->findAll($Criteria);
          $TheMenu = FHelper::RenderMenu(0, $userid_actor, $parentmenuid);
    
          $this->userid_actor = $userid_actor;
          $this->parentmenuid = $parentmenuid;
          
          $this->bread_crumb_list = '
            <li>
              Data Master
            </li>
            <li>
              <span>></span>
            </li>
            <li>
              Kustomer
            </li>';
            
          set_time_limit(3000);
          
          $this->layout = 'layout-baru';
          $TheContent = $this->renderPartial(
            'v_list_customer',
            array(
              'userid_actor' => $userid_actor,
              'customers' => $customers,
              'menuid' => $menuid
            ),
            true
          );
    
          $this->render(
            'index_general',
            array(
              'TheMenu' => $TheMenu,
              'TheContent' => $TheContent,
              'userid_actor' => $userid_actor
            )
          );
        }
        else
        {
          $this->actionShowInvalidAccess($userid_actor, false);
        }
        
        
        
      }

	    /*
	      actionListCustomer

	      Deskripsi
	      Action untuk menampilkan daftar customer

	    */
	    public function actionListCustomer()
	    {
	      $menuid = 15;
	      $parentmenuid = 6;
	      $Criteria = new CDbCriteria();
	      $Criteria->condition = 'is_del = \'N\'';

	      $userid_actor = Yii::app()->request->getParam('userid_actor');
	      
	      $idgroup = FHelper::GetGroupId($userid_actor);
        
        if(FHelper::AllowMenu($menuid, $idgroup, 'read'))
        {
          $customers = mtr_customer::model()->findAll($Criteria);
          $TheMenu = FHelper::RenderMenu(0, $userid_actor, $parentmenuid);
  
          $this->layout = 'setting';
          $html = $this->renderPartial(
              'v_list_customer',
              array(
                'userid_actor' => $userid_actor,
                'customers' => $customers,
                'menuid' => $menuid
              ),
              true
            );
          
          $bread_crumb_list = 
            '<li>Data Master</li>' .
            
            '<li>'.
              '<span> > </span>'.
              'Kustomer'.
            '</li>';
  
          echo CJSON::encode(
            array(
              'html' => $html, 
              'bread_crumb_list' => $bread_crumb_list
            )
          );
        }
        else
        {
          $this->actionShowInvalidAccess($userid_actor);
        }
	      
	      
	      
	    }

      /*
        actionAddCustomer

        Deskripsi
        Action untuk menampilkan form penambahan customer dan mengolah form
        submission.
      */
	    public function actionAddCustomer()
	    {            
	      $menuid = 15;
	      $parentmenuid = 6;
	      $form = new frmEditCustomer();
	      $frmResep = new frmResep();
	      $Criteria = new CDbCriteria();

	      $userid_actor = Yii::app()->request->getParam('userid_actor');
	      
	      $idgroup = FHelper::GetGroupId($userid_actor);
        
        if(FHelper::AllowMenu($menuid, $idgroup, 'write'))
        {
          $customers = mtr_customer::model()->findAll();
          $do_add = Yii::app()->request->getParam('do_add');
  
          //ambil listData untuk gender
          $gender_list = FHelper::GetGenderListData();
          $cust_type_list = FHelper::GetCustTypeListData();
          
          $Criteria = new CDbCriteria();
          $Criteria->condition = 'is_del = "N" AND is_deact = "N"';
          $examiners = Examiner::model()->findAll($Criteria);
          $examiner_list = CHtml::listData($examiners, 'examiner_id', 'name');
  
          if(isset($do_add))
          {
            if($do_add == 1)
            {
              //proses form submission
  
              //ambil informasi kustomer
              $form->attributes = Yii::app()->request->getParam('frmEditCustomer');
              $form['gender_id'] = $form['gender_id'] == 'M' ? 1 : 2;
  
              //ambil informasi isian form resep - begin
                $resep = Yii::app()->request->getParam('resep');
              //ambil informasi isian form resep - end
  
              //ambil informasi resep detail - begin
                $daftar_resep['frame_rec'] = Yii::app()->request->getParam('daftar_resep_frame_rec');
                $daftar_resep['lens_rec'] = Yii::app()->request->getParam('daftar_resep_lens_rec');
                $daftar_resep['wearing_sch'] = Yii::app()->request->getParam('daftar_resep_wearing_sch');
                $daftar_resep['note'] = Yii::app()->request->getParam('daftar_resep_note');
                $daftar_resep['examiner_id'] = Yii::app()->request->getParam('daftar_resep_examiner_id');
  
                $daftar_resep['left_sph'] = Yii::app()->request->getParam('daftar_resep_left_sph');
                $daftar_resep['left_cyl'] = Yii::app()->request->getParam('daftar_resep_left_cyl');
                $daftar_resep['left_axis'] = Yii::app()->request->getParam('daftar_resep_left_axis');
                $daftar_resep['left_prism'] = Yii::app()->request->getParam('daftar_resep_left_prism');
                $daftar_resep['left_base'] = Yii::app()->request->getParam('daftar_resep_left_base');
                $daftar_resep['left_add'] = Yii::app()->request->getParam('daftar_resep_left_add');
                $daftar_resep['left_dist_pd'] = Yii::app()->request->getParam('daftar_resep_left_dist_pd');
                $daftar_resep['left_near_pd'] = Yii::app()->request->getParam('daftar_resep_left_near_pd');
  
                $daftar_resep['right_sph'] = Yii::app()->request->getParam('daftar_resep_right_sph');
                $daftar_resep['right_cyl'] = Yii::app()->request->getParam('daftar_resep_right_cyl');
                $daftar_resep['right_axis'] = Yii::app()->request->getParam('daftar_resep_right_axis');
                $daftar_resep['right_prism'] = Yii::app()->request->getParam('daftar_resep_right_prism');
                $daftar_resep['right_base'] = Yii::app()->request->getParam('daftar_resep_right_base');
                $daftar_resep['right_add'] = Yii::app()->request->getParam('daftar_resep_right_add');
                $daftar_resep['right_dist_pd'] = Yii::app()->request->getParam('daftar_resep_right_dist_pd');
                $daftar_resep['right_near_pd'] = Yii::app()->request->getParam('daftar_resep_right_near_pd');
              //ambil informasi resep detail - end
  
              if($form->validate())
              {
                //simpan record kustomer ke tabel - begin
                  $mtr_customer = new mtr_customer();
                  $mtr_customer['name'] = ($form['name']);
                  $mtr_customer['address'] = ($form['address']);
                  $mtr_customer['phone'] = ($form['phone']);
                  $mtr_customer['email'] = ($form['email']);
                  $mtr_customer['zip'] = ($form['zip']);
                  $mtr_customer['ktp'] = ($form['ktp']);
                  $mtr_customer['mobile'] = ($form['mobile']);
                  $mtr_customer['fax'] = ($form['fax']);   
                  $mtr_customer['gender_id'] = ($form['gender_id']);
                  $mtr_customer['reg_date'] = date('Y-m-d H:i:s');
                  $mtr_customer['is_del'] = 'N';
                  $mtr_customer['is_deact'] = 'N';
                  $mtr_customer['date_created'] = date('Y-m-d H:i:s');
                  $mtr_customer['created_by'] = $userid_actor;
                  $mtr_customer['date_update'] = date('Y-m-d H:i:s');
                  $mtr_customer['update_by'] = $userid_actor;
                  $mtr_customer['cust_type_id'] = 0;
                  $mtr_customer['city'] = ($form['city']);
                  $mtr_customer['birth_place'] = ($form['birth_place']);
                  $mtr_customer['birth_date'] = date('Y-m-j', strtotime(($form['birth_date'])));
                  $mtr_customer->insert();

                  $pk_kustomer = $mtr_customer->getPrimaryKey();
                //simpan record kustomer ke tabel - end

				//simpan informasi resep detail - begin

                  if(count($daftar_resep['left_sph']) > 0)
                  {
                    foreach($daftar_resep['left_sph'] as $key => $value)
                    {
                      //simpan informasi resep - begin
                        $pos_customer_presc = new pos_customer_presc();
                        $pos_customer_presc['customer_id'] = $pk_kustomer;
                        $pos_customer_presc['frame_rec'] = ($resep['frame_rec'][$key]);
                        $pos_customer_presc['lens_rec'] = ($resep['lens_rec'][$key]);
                        $pos_customer_presc['wearing_sch'] = ($resep['wearing_sch'][$key]);
                        $pos_customer_presc['presc_sch'] = date('Y-m-j');
                        $pos_customer_presc['note'] = ($resep['note'][$key]);
                        $pos_customer_presc['examiner_id'] = ($resep['examiner_id'][$key]);   
                        $pos_customer_presc->insert();
                        $pk_resep = $pos_customer_presc->getPrimaryKey();
                      //simpan informasi resep - end
                      
                      
                      $resep_detail = new pos_customer_presc_detail();
                      $resep_detail['side'] = 'L';
                      $resep_detail['presc_id'] = $pk_resep;
                      $resep_detail['sph'] = ($resep['left_sph'][$key]);
                      $resep_detail['cyl'] = ($resep['left_cyl'][$key]);
                      $resep_detail['axis'] = ($resep['left_axis'][$key]);
                      $resep_detail['prism'] = ($resep['left_prism'][$key]);
                      $resep_detail['base'] = ($resep['left_base'][$key]);
                      $resep_detail['add'] = ($resep['left_add'][$key]);
                      $resep_detail['dist_pd'] = ($resep['left_dist_pd'][$key]);           
                      $resep_detail['near_pd'] = ($resep['left_near_pd'][$key]);  
                      try
                      {
                        $resep_detail->insert();
                      }
                      catch(Exception $e)
                      {
                      }
                      
    
                      $resep_detail = new pos_customer_presc_detail();
                      $resep_detail['side'] = 'R';
                      $resep_detail['presc_id'] = $pk_resep;
                      $resep_detail['sph'] = ($resep['right_sph'][$key]);
                      $resep_detail['cyl'] = ($resep['right_cyl'][$key]);
                      $resep_detail['axis'] = ($resep['right_axis'][$key]);
                      $resep_detail['prism'] = ($resep['right_prism'][$key]);
                      $resep_detail['base'] = ($resep['right_base'][$key]);
                      $resep_detail['add'] = ($resep['right_add'][$key]);
                      $resep_detail['dist_pd'] = ($resep['right_dist_pd'][$key]);
                      $resep_detail['near_pd'] = ($resep['right_near_pd'][$key]);   
                      try
                      {
                        $resep_detail->insert();
                      }
                      catch(Exception $e)
                      {
                      }
                    }
                  }
                  
                //simpan informasi resep detail - end
  
                FAudit::add(
                  'CUST', 
                  'ADD',
                  $userid_actor, 
                  'id=' . $pk_kustomer
                );
                
                
                //tampilkan informasi sukses menambahkan record kustomer
                $bread_crumb_list = 
                  '<li>Data Master</li>' .
                  
                  '<li>'.
                    '<span> > </span>'.
                    '<a href="#" onclick="ShowCustomerList('.$userid_actor.');">Kustomer</a>'.
                  '</li>'.
                  
                  '<li>'.
                    '<span> > </span>'.
                    'Tambah Kustomer'.
                  '</li>';
                  
                $html = $this->renderPartial(
                  'v_addcustomer_success',
                  array(
                    'form' => $form,                                                               
                    'userid_actor' => $userid_actor,
                    'idcustomer' => $pk_kustomer,
                    'daftar_resep' => $daftar_resep,
                    'gender_list' => $gender_list,
                    'cust_type_list' => $cust_type_list
                  ),
                  true
                );

                //get template sms untuk kustomer baru
                $smstplCC = new CDbCriteria();
                $smstplCC->condition = 'tpl_id = 1 AND is_deact = "N"';
                $smstpl = Smstemplate::model()->find($smstplCC);
        
                //send sms
                $smssend = new Smssend();
                $smssend['dest_name'] = $mtr_customer['name'];
                $smssend['dest_mobile'] = $mtr_customer['mobile'];
                $smssend['content'] = $smstpl['content'];
                $smssend['date_created'] = new CDbExpression('NOW()');
                $smssend['created_by'] = FHelper::GetUserName($userid_actor);
                $smssend['is_proc'] = 'N';
                
                //echo "<pre>".print_r($smssend)."</pre>";
                //exit();
        
                if (!empty($smssend['dest_mobile'])) 
                {  
                  $smssend->save();
                  $idsms = $smssend->getPrimaryKey();
                  
                  //integrasi gampp sms api
                  $hasil = FHelper::KirimSms($mtr_customer['mobile'], $smstpl['content']);
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
                  
                    
                }
              }
              else
              {
                //form validation failed
                $bread_crumb_list = 
                  '<li>Data Master</li>' .
                  
                  '<li>'.
                    '<span> > </span>'.
                    '<a href="#" onclick="ShowCustomerList('.$userid_actor.');">Kustomer</a>'.
                  '</li>'.
                  
                  '<li>'.
                    '<span> > </span>'.
                    'Tambah Kustomer'.
                  '</li>';
                  
                $form_resep = $this->renderPartial(
                  'vfrm_resep',
                  array(
                    'resep' => $resep,               // <---- array yang menampung isian form resep
                    'frmResep' => $frmResep,         // <---- form model yang menampung kesalahan isian form
                    'examiner_list' => $examiner_list
                  ),
                  true
                );
  
                //tampilkan form
                $html = $this->renderPartial(
                  'vfrm_addcustomer',
                  array(
                    'form' => $form,                  // <--- form model untuk informasi kustomer
                    'form_resep' => $form_resep,      // <--- hasil render vfrm_resep
                    'daftar_resep' => $daftar_resep,  // <---- array yang menampung daftar resep detail
                    'userid_actor' => $userid_actor,
                    'gender_list' => $gender_list,
                    'cust_type_list' => $cust_type_list
                  ),
                  true
                );
              }
            }
            else
            {
              //batal menambah kustomer.
              //alihkan ke view list kustomer.
  
              $Criteria = new CDbCriteria();
              $Criteria->condition = 'is_del = 0';
  
              $userid_actor = Yii::app()->request->getParam('userid_actor');
              $customers = mtr_customer::model()->findAll($Criteria);
              
              $bread_crumb_list = 
                '<li>Data Master</li>' .
                
                '<li>'.
                  '<span> > </span>'.
                  'Kustomer'.
                '</li>';
  
              $html = $this->renderPartial(
                'v_list_customer',
                array(
                  'userid_actor' => $userid_actor,
                  'customers' => $customers,
                  'menuid' => $menuid
                ),
                true
              );
            }
  
          }
          else
          {
            //bukan form submission, tampilkan form add kustomer
            
            $bread_crumb_list = 
              '<li>Data Master</li>' .
              
              '<li>'.
                '<span> > </span>'.
                '<a href="#" onclick="ShowCustomerList('.$userid_actor.');">Kustomer</a>'.
              '</li>'.
              
              '<li>'.
                '<span> > </span>'.
                'Tambah Kustomer'.
              '</li>';
  
            $form_resep = $this->renderPartial(
              'vfrm_resep',
              array(
                'resep' => $resep,        // <---- array yang menampung isian form resep
                'frmResep' => $frmResep,  // <---- form model yang menampung kesalahan isian form
                'examiner_list' => $examiner_list
              ),
              true
            );
  
            $form['mobile'] = '+62';
            $html = $this->renderPartial(
              'vfrm_addcustomer',
              array(
                'form' => $form,                  // <--- form model untuk informasi kustomer
                'form_resep' => $form_resep,      // <--- hasil render vfrm_resep
                'userid_actor' => $userid_actor,
                'gender_list' => $gender_list,
                'cust_type_list' => $cust_type_list
              ),
              true
            );
          }
  
          echo CJSON::encode(
            array(
              'html' => $html, 
              'bread_crumb_list' => $bread_crumb_list
            )
          );
        }
        else
        {
          $this->actionShowInvalidAccess($userid_actor);
        }

	    }

	    /*
	      actionSubmitResep

	      Deskripsi
	      Action untuk menangani penambahan resep untuk suatu kustomer. Fungsi
	      hanya memeriksa validitas data yang mau ditambahkan ke daftar resep
	      kustomer. Fungsi ini dipakai oleh AJAX call pada view vfrm_addcustomer.

	      Parameter
	      resep
	        Array yang berisi field-field dalam view vfrm_resep

	      Return
	      json
	        JSON yang berisi status (ok/not ok) dan html vfrm_resep.
	    */
	    public function actionSubmitResep()
	    {
	      //$resep = Yii::app()->request->getParam('resep');

	      $frmResep = new frmResep();
	      
	      $frmResep->attributes = Yii::app()->request->getParam('frmResep');

	      //bind parameters  
	      
	      /*
	      $frmResep['frame_rec'] = Defense::Sanitize($resep['frame_rec']);
	      $frmResep['lens_rec'] = Defense::Sanitize($resep['lens_rec']);
	      $frmResep['wearing_sch'] = Defense::Sanitize($resep['wearing_sch']);
	      $frmResep['note'] = Defense::Sanitize($resep['note']);      
	      $frmResep['examiner_id'] = Defense::Sanitize($resep['examiner_id']);

	      $frmResep['left_sph'] = Defense::Sanitize($resep['left_sph']);
	      $frmResep['left_cyl'] = Defense::Sanitize($resep['left_cyl']);
	      $frmResep['left_axis'] = Defense::Sanitize($resep['left_axis']);
	      $frmResep['left_prism'] = Defense::Sanitize($resep['left_prism']);                            
	      $frmResep['left_base'] = Defense::Sanitize($resep['left_base']);
	      $frmResep['left_add'] = Defense::Sanitize($resep['left_add']);
	      $frmResep['left_dist_pd'] = Defense::Sanitize($resep['left_dist_pd']);
	      $frmResep['left_near_pd'] = Defense::Sanitize($resep['left_near_pd']);

	      $frmResep['right_sph'] = Defense::Sanitize($resep['right_sph']);
	      $frmResep['right_cyl'] = Defense::Sanitize($resep['right_cyl']);
	      $frmResep['right_axis'] = Defense::Sanitize($resep['right_axis']);
	      $frmResep['right_prism'] = Defense::Sanitize($resep['right_prism']);
	      $frmResep['right_base'] = Defense::Sanitize($resep['right_base']);
	      $frmResep['right_add'] = Defense::Sanitize($resep['right_add']);
	      $frmResep['right_dist_pd'] = Defense::Sanitize($resep['right_dist_pd']);
	      $frmResep['right_near_pd'] = Defense::Sanitize($resep['right_near_pd']);  
	      */
	      
	      //validate form entry
	      if($frmResep->validate())
	      {
	        $status = 'ok';
	      }
	      else
	      {
	        $status = 'not ok';
	      }
	      
	      $Criteria = new CDbCriteria();
        $Criteria->condition = 'is_del = "N" AND is_deact = "N"';
        $examiners = Examiner::model()->findAll($Criteria);
        $examiner_list = CHtml::listData($examiners, 'examiner_id', 'name');

	      $html = $this->renderPartial(
          'vfrm_resep',
          array(
            'resep' => $resep,
            'frmResep' => $frmResep,
            'examiner_list' => $examiner_list
          ),
          true
        );

        echo CJSON::encode(array('html' => $html, 'status' => $status));
	    }

	    /*
	      actionEditCustomer

	      Deskripsi
	      Action untuk menampilkan form edit customer dan mengolah form submission.
	    */
	    public function actionEditCustomer()
	    {
	      $menuid = 15;
	      $parentmenuid = 6;
	      $userid_actor = Yii::app()->request->getParam('userid_actor');
	      $idcustomer = Yii::app()->request->getParam('idcustomer');
	      $do_edit = Yii::app()->request->getParam('do_edit');
	      
	      $idgroup = FHelper::GetGroupId($userid_actor);
        
        if(FHelper::AllowMenu($menuid, $idgroup, 'edit'))
        {
          $frmResep = new frmResep();
          $gender_list = FHelper::GetGenderListData();
          $cust_type_list = FHelper::GetCustTypeListData();
          
          $Criteria = new CDbCriteria();
          $Criteria->condition = 'is_del = "N" AND is_deact = "N"';
          $examiners = Examiner::model()->findAll($Criteria);
          $examiner_list = CHtml::listData($examiners, 'examiner_id', 'name');
          
          if(isset($do_edit))
          {
            if($do_edit == 1)
            {
              //proses edit form submission
  
              $form = new frmEditCustomer();
              $form->attributes = Yii::app()->request->getParam('frmEditCustomer');
              
              //ambil informasi resep dan detail resep dari form - begin
                
                //ambil informasi resep - begin
                  $daftar_resep['frame_rec'] = Yii::app()->request->getParam('daftar_resep_frame_rec');
                  $daftar_resep['lens_rec'] = Yii::app()->request->getParam('daftar_resep_lens_rec');
                  $daftar_resep['wearing_sch'] = Yii::app()->request->getParam('daftar_resep_wearing_sch');
                  $daftar_resep['note'] = Yii::app()->request->getParam('daftar_resep_note');
                  $daftar_resep['examiner_id'] = Yii::app()->request->getParam('daftar_resep_examiner_id');
                //ambil informasi resep - end
                
                //ambil detil resep kiri dari form - begin
                  $daftar_resep['left_sph'] = Yii::app()->request->getParam('daftar_resep_left_sph');
                  $daftar_resep['left_cyl'] = Yii::app()->request->getParam('daftar_resep_left_cyl');
                  $daftar_resep['left_axis'] = Yii::app()->request->getParam('daftar_resep_left_axis');
                  $daftar_resep['left_prism'] = Yii::app()->request->getParam('daftar_resep_left_prism');
                  $daftar_resep['left_base'] = Yii::app()->request->getParam('daftar_resep_left_base');
                  $daftar_resep['left_add'] = Yii::app()->request->getParam('daftar_resep_left_add');
                  $daftar_resep['left_dist_pd'] = Yii::app()->request->getParam('daftar_resep_left_dist_pd');           
                  $daftar_resep['left_near_pd'] = Yii::app()->request->getParam('daftar_resep_left_near_pd');
                //ambil detil resep kiri dari form - end
                
                
                //ambil detil resep kanan dari form - begin
                  $daftar_resep['right_sph'] = Yii::app()->request->getParam('daftar_resep_right_sph');
                  $daftar_resep['right_cyl'] = Yii::app()->request->getParam('daftar_resep_right_cyl');
                  $daftar_resep['right_axis'] = Yii::app()->request->getParam('daftar_resep_right_axis');
                  $daftar_resep['right_prism'] = Yii::app()->request->getParam('daftar_resep_right_prism');
                  $daftar_resep['right_base'] = Yii::app()->request->getParam('daftar_resep_right_base');
                  $daftar_resep['right_add'] = Yii::app()->request->getParam('daftar_resep_right_add');
                  $daftar_resep['right_dist_pd'] = Yii::app()->request->getParam('daftar_resep_right_dist_pd');           
                  $daftar_resep['right_near_pd'] = Yii::app()->request->getParam('daftar_resep_right_near_pd');
                //ambil detil resep kanan dari form - end
                  
              //ambil informasi resep dan detail resep dari form - end
              
              if($form->validate())
              {
                $Criteria = new CDbCriteria();
                $Criteria->condition = 'customer_id = :idcustomer';
                $Criteria->params = array(':idcustomer' => $idcustomer);
                
                Yii::log('form[birth_date] = ' . $form['birth_date'] , 'info');
  
                //update record kustomer ke tabel - begin
                  $mtr_customer = mtr_customer::model()->find($Criteria);
                  $mtr_customer['name'] = Defense::Sanitize($form['name']);
                  $mtr_customer['address'] = Defense::Sanitize($form['address']);
                  $mtr_customer['phone'] = Defense::Sanitize($form['phone']);
                  $mtr_customer['email'] = Defense::Sanitize($form['email']);
                  $mtr_customer['zip'] = Defense::Sanitize($form['zip']);
                  $mtr_customer['ktp'] = Defense::Sanitize($form['ktp']);
                  $mtr_customer['mobile'] = Defense::Sanitize($form['mobile']);
                  $mtr_customer['fax'] = Defense::Sanitize($form['fax']);          
                  $mtr_customer['gender_id'] = (Defense::Sanitize($form['gender_id']) == 'M' ? 1 : 2);
                  //$mtr_customer['reg_date'] = Defense::Sanitize($form['reg_date']);
                  //$mtr_customer['date_created'] = Defense::Sanitize($form['date_created']);
                  //$mtr_customer['created_by'] = Defense::Sanitize($form['created_by']);
                  $mtr_customer['date_update'] = date('Y-m-d H:i:s');
                  $mtr_customer['update_by'] = $userid_actor;
                  $mtr_customer['cust_type_id'] = $form['cust_type_id'];
                  $mtr_customer['city'] = Defense::Sanitize($form['city']);
                  $mtr_customer['birth_place'] = Defense::Sanitize($form['birth_place']);  
                  $mtr_customer['birth_date'] = date('Y-m-j', strtotime(Defense::Sanitize($form['birth_date'])));
                  $mtr_customer['version'] = $mtr_customer['version'] + 1;
                  $mtr_customer->update();
                //update record kustomer ke tabel - end
  
                
  
                //update informasi resep detail - begin
                  
                  //hapus resep berdasarkan idcustomer - begin
                    $Criteria_resep = new CDbCriteria();
                    $Criteria_resep->condition = 'customer_id = :idcustomer';
                    $Criteria_resep->params = array(':idcustomer' => $idcustomer);
                    pos_customer_presc::model()->deleteAll($Criteria_resep);
                  //hapus resep berdasarkan idcustomer - end
                
                  if(count($daftar_resep['left_sph']) > 0)
                  {       
                    foreach($daftar_resep['left_sph'] as $key => $value)
                    {
                      //update informasi resep - begin
                        $pos_customer_presc = new pos_customer_presc();
                        $pos_customer_presc['customer_id'] = $idcustomer;
                        $pos_customer_presc['frame_rec'] = Defense::Sanitize($daftar_resep['frame_rec'][$key]);
                        $pos_customer_presc['lens_rec'] = Defense::Sanitize($daftar_resep['lens_rec'][$key]);
                        $pos_customer_presc['wearing_sch'] = date('Y-m-j', strtotime(Defense::Sanitize($daftar_resep['wearing_sch'][$key])));
                        $pos_customer_presc['note'] = Defense::Sanitize($daftar_resep['note'][$key]);
                        $pos_customer_presc['examiner_id'] = Defense::Sanitize($daftar_resep['examiner_id'][$key]);                        
                        $pos_customer_presc->insert();                                                    
                        
                        $pk_resep = $pos_customer_presc->getPrimaryKey();
                      //update informasi resep - end   
                      
                      
                      //update detil resep kiri - begin
                        Yii::log('daftar_resep[left_sph][key] = ' . $daftar_resep['left_sph'][$key], 'info');
                      
                        $resep_detail = new pos_customer_presc_detail();
                        $resep_detail['presc_id'] = $pk_resep;     
                        $resep_detail['side'] = 'L';
                        $resep_detail['sph'] = Defense::Sanitize($daftar_resep['left_sph'][$key]);
                        $resep_detail['cyl'] = Defense::Sanitize($daftar_resep['left_cyl'][$key]);
                        $resep_detail['axis'] = Defense::Sanitize($daftar_resep['left_axis'][$key]);
                        $resep_detail['prism'] = Defense::Sanitize($daftar_resep['left_prism'][$key]);
                        $resep_detail['base'] = Defense::Sanitize($daftar_resep['left_base'][$key]);
                        $resep_detail['add'] = Defense::Sanitize($daftar_resep['left_add'][$key]);
                        $resep_detail['dist_pd'] = Defense::Sanitize($daftar_resep['left_dist_pd'][$key]);           
                        $resep_detail['near_pd'] = Defense::Sanitize($daftar_resep['left_near_pd'][$key]);   
                        $resep_detail->insert();
                      //update detil resep kiri - end
                      
                      //update detil resep kanan - begin
                        $resep_detail = new pos_customer_presc_detail();
                        $resep_detail['presc_id'] = $pk_resep;
                        $resep_detail['side'] = 'R';
                        $resep_detail['sph'] = Defense::Sanitize($daftar_resep['right_sph'][$key]);
                        $resep_detail['cyl'] = Defense::Sanitize($daftar_resep['right_cyl'][$key]);
                        $resep_detail['axis'] = Defense::Sanitize($daftar_resep['right_axis'][$key]);
                        $resep_detail['prism'] = Defense::Sanitize($daftar_resep['right_prism'][$key]);
                        $resep_detail['base'] = Defense::Sanitize($daftar_resep['right_base'][$key]);
                        $resep_detail['add'] = Defense::Sanitize($daftar_resep['right_add'][$key]);
                        $resep_detail['dist_pd'] = Defense::Sanitize($daftar_resep['right_dist_pd'][$key]);
                        $resep_detail['near_pd'] = Defense::Sanitize($daftar_resep['right_near_pd'][$key]);   
                        $resep_detail->insert();
                      //update detil resep kanan - end
                      
                    }
                  }
                  
                //update informasi resep detail - end
  
                FAudit::add(
                  'CUST', 
                  'EDIT',
                  $userid_actor, 
                  'id=' . $idcustomer
                );
                
                //tampilkan informasi sukses menambahkan record lokasi
                $bread_crumb_list = 
                  '<li>Data Master</li>' .
                  
                  '<li>'.
                    '<span> > </span>'.
                    '<a href="#" onclick="ShowCustomerList('.$userid_actor.');">Kustomer</a>'.
                  '</li>'.
                  
                  '<li>'.
                    '<span> > </span>'.
                    'Edit Kustomer'.
                  '</li>';
                  
                $html = $this->renderPartial(
                  'v_editcustomer_success',
                  array(
                    'form' => $form,
                    'userid_actor' => $userid_actor,
                    'idcustomer' => $idcustomer,
                    'daftar_resep' => $daftar_resep,
                    'gender_list' => $gender_list,
                    'cust_type_list' => $cust_type_list
                  ),
                  true
                );

                //get template sms untuk update kustomer
                $smstplCC = new CDbCriteria();
                $smstplCC->condition = 'tpl_id = 2 AND is_deact = "N"';
                $smstpl = Smstemplate::model()->find($smstplCC);
        
                //send sms
                $smssend = new Smssend();
                $smssend['dest_name'] = $mtr_customer['name'];
                $smssend['dest_mobile'] = $mtr_customer['mobile'];
                $smssend['content'] = $smstpl['content'];
                $smssend['date_created'] = new CDbExpression('NOW()');
                $smssend['created_by'] = FHelper::GetUserName($userid_actor);
                $smssend['is_proc'] = 'N';
                
                //echo "<pre>".print_r($smssend)."</pre>";
                //exit();
        
                if (!empty($smssend['dest_mobile']))
                {  
                  $smssend->save();
                  $idsms = $smssend->getPrimaryKey();
                  
                  //integrasi gampp sms api
                  $hasil = FHelper::KirimSms($mtr_customer['mobile'], $smstpl['content']);
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
                }
              }
              else
              {
                //validation failed
                
                //tampilkan form
                $form_resep = $this->renderPartial(
                  'vfrm_resep',
                  array(
                    'resep' => $resep,               // <---- array yang menampung isian form resep
                    'frmResep' => $frmResep,         // <---- form model yang menampung kesalahan isian form
                    'examiner_list' => $examiner_list
                  ),
                  true
                );
      
                //show form add customer
                $bread_crumb_list = 
                  '<li>Data Master</li>' .
                  
                  '<li>'.
                    '<span> > </span>'.
                    '<a href="#" onclick="ShowCustomerList('.$userid_actor.');">Kustomer</a>'.
                  '</li>'.
                  
                  '<li>'.
                    '<span> > </span>'.
                    'Edit Kustomer'.
                  '</li>';
                  
                $html = $this->renderPartial(
                  'vfrm_editcustomer_temp',
                  array(
                    'form' => $form,
                    'form_resep' => $form_resep,      // <--- hasil render vfrm_resep
                    'daftar_resep' => $daftar_resep,  // <---- array yang menampung daftar resep detail
                    'userid_actor' => $userid_actor,
                    'idcustomer' => $idcustomer,
                    'gender_list' => $gender_list,
                    'cust_type_list' => $cust_type_list
                  ),
                  true
                );
              }
            }
            else
            {
              //batal edit
              //kembali ke daftar customer
              $Criteria = new CDbCriteria();
              $Criteria->condition = 'is_del = 0';
  
              $userid_actor = Yii::app()->request->getParam('userid_actor');
              $customers = mtr_customer::model()->findAll($Criteria);
              
              $bread_crumb_list = 
                '<li>Data Master</li>' .
                
                '<li>'.
                  '<span> > </span>'.
                  'Kustomer'.
                '</li>';
  
              $html = $this->renderPartial(
                'v_list_customer',
                array(
                  'userid_actor' => $userid_actor,
                  'customers' => $customers,
                  'menuid' => $menuid
                ),
                true
              );
            }
  
          }
          else
          {
            //tampilkan form edit customer
            
            $Criteria = new CDbCriteria();
            $Criteria->condition = 'customer_id = :idcustomer';
            $Criteria->params = array(':idcustomer' => $idcustomer);
    
            $mtr_customer = mtr_customer::model()->find($Criteria);
    
            //ambil informasi customer dari database - begin
              $form = new frmEditCustomer();
              $form['idcustomer'] = $mtr_customer['customer_id'];
              $form['name'] = $mtr_customer['name'];
              $form['address'] = $mtr_customer['address'];
              $form['phone'] = $mtr_customer['phone'];
              $form['fax'] = $mtr_customer['fax'];
              $form['email'] = $mtr_customer['email'];      
              $form['zip'] = $mtr_customer['zip'];
              $form['ktp'] = $mtr_customer['ktp'];                             
              $form['mobile'] = $mtr_customer['mobile'];
              $form['fax'] = $mtr_customer['fax'];
              $form['gender_id'] = $mtr_customer['gender_id'];
              $form['reg_date'] = $mtr_customer['reg_date'];                                          
              $form['date_created'] = $mtr_customer['date_created'];
              $form['created_by'] = $mtr_customer['created_by'];
              $form['date_update'] = $mtr_customer['date_update'];
              $form['update_by'] = $mtr_customer['update_by'];
              $form['cust_type_id'] = $mtr_customer['cust_type_id'];
              $form['city'] = $mtr_customer['city'];
              $form['birth_place'] = $mtr_customer['birth_place'];
              $form['birth_date'] = date('Y-m-j', strtotime($mtr_customer['birth_date']));       
            //ambil informasi customer dari database - end
            
            //ambil informasi resep dan detail resep dari database - begin
              $Criteria = new CDbCriteria();
              $Criteria->condition = 'customer_id = :customer_id';
              $Criteria->params = array(':customer_id' => $idcustomer);
              
              $pos_customer_presc_list = pos_customer_presc::model()->findAll($Criteria);
              foreach($pos_customer_presc_list as $pos_customer_presc)
              {
                $daftar_resep['frame_rec'][] = $pos_customer_presc['frame_rec'];
                $daftar_resep['lens_rec'][] = $pos_customer_presc['lens_rec'];
                $daftar_resep['wearing_sch'][] = $pos_customer_presc['wearing_sch'];
                $daftar_resep['note'][] = $pos_customer_presc['note'];
                $daftar_resep['examiner_id'][] = $pos_customer_presc['examiner_id'];
                $daftar_resep['examiner_name'][] = $pos_customer_presc->examiner['name'];
                
                //ambil detail resep dari database
                $id_resep = $pos_customer_presc['presc_id'];
                
                //ambil detil resep kiri dari database - begin
                  $Criteria_resep = new CDbCriteria();
                  $Criteria_resep->condition = 'presc_id = :id_resep AND side = "L" ';
                  $Criteria_resep->params = array(':id_resep' => $id_resep);
                  
                  $daftar_resep_detail = pos_customer_presc_detail::model()->find($Criteria_resep);
                  $daftar_resep['left_sph'][] = $daftar_resep_detail['sph'];
                  $daftar_resep['left_cyl'][] = $daftar_resep_detail['cyl'];
                  $daftar_resep['left_axis'][] = $daftar_resep_detail['axis'];
                  $daftar_resep['left_prism'][] = $daftar_resep_detail['prism'];
                  $daftar_resep['left_base'][] = $daftar_resep_detail['base'];
                  $daftar_resep['left_add'][] = $daftar_resep_detail['add'];
                  $daftar_resep['left_dist_pd'][] = $daftar_resep_detail['dist_pd'];           
                  $daftar_resep['left_near_pd'][] = $daftar_resep_detail['near_pd'];
                //ambil detil resep kiri dari database - end
                
                
                //ambil detil resep kanan dari database - begin
                  $Criteria_resep->condition = 'presc_id = :id_resep AND side = "R" ';
                  $Criteria_resep->params = array(':id_resep' => $id_resep);
                  
                  $daftar_resep_detail = pos_customer_presc_detail::model()->find($Criteria_resep);
                  $daftar_resep['right_sph'][] = $daftar_resep_detail['sph'];
                  $daftar_resep['right_cyl'][] = $daftar_resep_detail['cyl'];
                  $daftar_resep['right_axis'][] = $daftar_resep_detail['axis'];
                  $daftar_resep['right_prism'][] = $daftar_resep_detail['prism'];
                  $daftar_resep['right_base'][] = $daftar_resep_detail['base'];
                  $daftar_resep['right_add'][] = $daftar_resep_detail['add'];
                  $daftar_resep['right_dist_pd'][] = $daftar_resep_detail['dist_pd'];           
                  $daftar_resep['right_near_pd'][] = $daftar_resep_detail['near_pd'];
                //ambil detil resep kanan dari database - end
                
              } //loop resep si customer
                
            //ambil informasi resep dan detail resep dari database - end
            
            $bread_crumb_list = 
              '<li>Data Master</li>' .
              
              '<li>'.
                '<span> > </span>'.
                '<a href="#" onclick="ShowCustomerList('.$userid_actor.');">Kustomer</a>'.
              '</li>'.
              
              '<li>'.
                '<span> > </span>'.
                'Edit Kustomer'.
              '</li>';
            
            $form_resep = $this->renderPartial(
              'vfrm_resep',
              array(
                'resep' => $resep,               // <---- array yang menampung isian form resep
                'frmResep' => $frmResep,         // <---- form model yang menampung kesalahan isian form
                'examiner_list' => $examiner_list
              ),
              true
            );
  
            //show form add customer
            $html = $this->renderPartial(
              'vfrm_editcustomer_temp',
              array(
                'form' => $form,
                'form_resep' => $form_resep,      // <--- hasil render vfrm_resep
                'daftar_resep' => $daftar_resep,  // <---- array yang menampung daftar resep detail
                'userid_actor' => $userid_actor,
                'idcustomer' => $idcustomer,
                'gender_list' => $gender_list,
                'cust_type_list' => $cust_type_list
              ),
              true
            );
          }
  
          echo CJSON::encode(
            array(
              'html' => $html, 
              'bread_crumb_list' => $bread_crumb_list
            )
          );
        }
        else
        {
          $this->actionShowInvalidAccess($userid_actor);
        }
	      
	      
	      
	    }

	    /*
	      actionDeleteCustomer

	      Deskripsi
	      Action untuk mengubah flag is_del pada record sys_customer
	    */
	    public function actionDeleteCustomer()
	    {
	      $menuid = 15;
	      $parentmenuid = 6;
	      $userid_actor = Yii::app()->request->getParam('userid_actor');
	      $idcustomer = Yii::app()->request->getParam('idcustomer');
	      
	      
	      $idgroup = FHelper::GetGroupId($userid_actor);
        
        if(FHelper::AllowMenu($menuid, $idgroup, 'delete'))
        {
          $Criteria = new CDbCriteria();
          $Criteria->condition = 'customer_id = :idcustomer';
          $Criteria->params = array(':idcustomer' => $idcustomer);
  
          //update record di tabel
          $sys_customer = mtr_customer::model()->find($Criteria);
          $sys_customer['is_del'] = 'Y';
          $sys_customer->update();
          
          $this->actionListCustomer();
        }
        else
        {
          $this->actionShowInvalidAccess($userid_actor);
        }
	    }
	    
	    /*
	      actionViewCustomer
	      
	      Deskripsi
	      Action untuk menampilkan informasi kustomer. Juga memberikan akses untuk 
	      mengedit atau menghapus kustomer.
	      
	      Parameter
	      userid_actor
	        Integer. Id user yang menggunakan system.
	      idcustomer
	        Integer. Menerangkan id customer yang ditampilkan.
	        
	      Return
	      Interface view data customer.
	    */
	    public function actionViewCustomer()
	    {
	      $menuid = 15;
	      $parentmenuid = 6;
	      $userid_actor = Yii::app()->request->getParam('userid_actor');
	      $idcustomer = Yii::app()->request->getParam('idcustomer');
	      
	      
	      $idgroup = FHelper::GetGroupId($userid_actor);
        
        if(FHelper::AllowMenu($menuid, $idgroup, 'read'))
        {
          $gender_list = FHelper::GetGenderListData();
	      
          $Criteria = new CDbCriteria();
          $Criteria->condition = 'customer_id = :idcustomer';
          $Criteria->params = array(':idcustomer' => $idcustomer);
  
          $mtr_customer = mtr_customer::model()->find($Criteria);
  
          //ambil informasi customer dari database - begin
            $form = new frmEditCustomer();
            $form['idcustomer'] = $mtr_customer['customer_id'];
            $form['name'] = $mtr_customer['name'];
            $form['address'] = $mtr_customer['address'];
            $form['phone'] = $mtr_customer['phone'];
            $form['fax'] = $mtr_customer['fax'];
            $form['email'] = $mtr_customer['email'];
            $form['zip'] = $mtr_customer['zip'];
            $form['ktp'] = $mtr_customer['ktp'];
            $form['mobile'] = $mtr_customer['mobile'];
            $form['fax'] = $mtr_customer['fax'];
            $form['gender_id'] = $mtr_customer['gender_id'];
            $form['reg_date'] = $mtr_customer['reg_date'];
            $form['is_del'] = $mtr_customer['is_del'] = 0;
            $form['is_deact'] = $mtr_customer['is_deact'] = 0;
            $form['date_created'] = $mtr_customer['date_created'];
            $form['created_by'] = $mtr_customer['created_by'];
            $form['date_update'] = $mtr_customer['date_update'];
            $form['update_by'] = $mtr_customer['update_by'];
            $form['cust_type_id'] = $mtr_customer['cust_type_id'];
            $form['city'] = $mtr_customer['city'];
            $form['birth_place'] = $mtr_customer['birth_place'];
            $form['birth_date'] = $mtr_customer['birth_date']; 
          //ambil informasi customer dari database - end
          
          //ambil informasi resep dan detail resep dari database - begin
            $Criteria = new CDbCriteria();
            $Criteria->condition = 'customer_id = :customer_id';
            $Criteria->params = array(':customer_id' => $idcustomer);
            
            $pos_customer_presc_list = pos_customer_presc::model()->findAll($Criteria);
            foreach($pos_customer_presc_list as $pos_customer_presc)
            {
              $daftar_resep['frame_rec'][] = $pos_customer_presc['frame_rec'];
              $daftar_resep['lens_rec'][] = $pos_customer_presc['lens_rec'];
              $daftar_resep['wearing_sch'][] = $pos_customer_presc['wearing_sch'];
              $daftar_resep['note'][] = $pos_customer_presc['note'];
              $daftar_resep['examiner_id'][] = $pos_customer_presc['examiner_id'];
              
              //ambil detail resep dari database
              $id_resep = $pos_customer_presc['presc_id'];
              
              //ambil detil resep kiri dari database - begin
                $Criteria_resep = new CDbCriteria();
                $Criteria_resep->condition = 'presc_id = :id_resep AND side = "L" ';
                $Criteria_resep->params = array(':id_resep' => $id_resep);
                
                $daftar_resep_detail = pos_customer_presc_detail::model()->find($Criteria_resep);
                $daftar_resep['left_sph'][] = $daftar_resep_detail['sph'];
                $daftar_resep['left_cyl'][] = $daftar_resep_detail['cyl'];
                $daftar_resep['left_axis'][] = $daftar_resep_detail['axis'];
                $daftar_resep['left_prism'][] = $daftar_resep_detail['prism'];
                $daftar_resep['left_base'][] = $daftar_resep_detail['base'];
                $daftar_resep['left_add'][] = $daftar_resep_detail['add'];
                $daftar_resep['left_dist_pd'][] = $daftar_resep_detail['dist_pd'];           
                $daftar_resep['left_near_pd'][] = $daftar_resep_detail['near_pd'];
              //ambil detil resep kiri dari database - end
              
              
              //ambil detil resep kanan dari database - begin
                $Criteria_resep->condition = 'presc_id = :id_resep AND side = "R" ';
                $Criteria_resep->params = array(':id_resep' => $id_resep);
                
                $daftar_resep_detail = pos_customer_presc_detail::model()->find($Criteria_resep);
                $daftar_resep['right_sph'][] = $daftar_resep_detail['sph'];
                $daftar_resep['right_cyl'][] = $daftar_resep_detail['cyl'];
                $daftar_resep['right_axis'][] = $daftar_resep_detail['axis'];
                $daftar_resep['right_prism'][] = $daftar_resep_detail['prism'];
                $daftar_resep['right_base'][] = $daftar_resep_detail['base'];
                $daftar_resep['right_add'][] = $daftar_resep_detail['add'];
                $daftar_resep['right_dist_pd'][] = $daftar_resep_detail['dist_pd'];           
                $daftar_resep['right_near_pd'][] = $daftar_resep_detail['near_pd'];
              //ambil detil resep kanan dari database - end
              
            } //loop resep si customer
              
          //ambil informasi resep dan detail resep dari database - end
          
          $bread_crumb_list = 
            '<li>Data Master</li>' .
            
            '<li>'.
              '<span> > </span>'.
              '<a href="#" onclick="ShowCustomerList('.$userid_actor.');">Kustomer</a>'.
            '</li>'.
            
            '<li>'.
              '<span> > </span>'.
              'View Kustomer'.
            '</li>';
            
          //show view customer
          $html = $this->renderPartial(
            'v_view_customer',
            array(
              'form' => $form,
              'daftar_resep' => $daftar_resep,  // <---- array yang menampung daftar resep detail
              'userid_actor' => $userid_actor,
              'idcustomer' => $idcustomer,
              'gender_list' => $gender_list,
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
        }
        else
        {
          $this->actionShowInvalidAccess($userid_actor);
        }
	      
	      
	      
	    }
	    
	    /*
	      actionListActionCustomer
	      
	      Deskripsi
	      Action untuk mengolah action terhadap list data master (general). Untuk
	      melakukan set_active = 0 atau set_active = 1.
	      
	      Parameter
	      data_master_action_type
	        Integer. 1: set_active = 1; 1: set_active = 0
	        
        Return
        List data master yang dipaketkan dalam JSON.
	    */
	    public function actionListActionCustomer()
	    {
	      $action_type = Yii::app()->request->getParam('data_master_action_type');
	      $item_list = Yii::app()->request->getParam('selected_item_list');
	      
	      $Criteria = new CDbCriteria();
	      $Criteria->condition = 'customer_id = :customer_id';
            
	      if($action_type > 0)
	      {
	        foreach($item_list as $key => $value)
          {
            $Criteria->params = array(':customer_id' => $value);
            $supplier = mtr_customer::model()->find($Criteria);
            
            $supplier['is_deact'] = ($action_type == 1 ? 'Y' : 'N');
            $supplier->update();
          }
          
          $this->actionListCustomer();
	      }
	    }

	  //customer perorangan - end



	  //customer optik - begin



	    /*
	      actionMasterListCustomerOPtik

	      Deskripsi
	      Action untuk menampilkan daftar customer optik

	    */
	    public function actionCustomerOptik()
	    {
	      $Criteria = new CDbCriteria();
	      $Criteria->condition = 'is_del = 0 AND tipe = 2';

	      $userid_actor = Yii::app()->request->getParam('userid_actor');
	      $customers = sys_customer::model()->findAll($Criteria);
	      $TheMenu = FHelper::RenderMenu(0, $userid_actor);

	      $this->layout = 'setting';
	      $TheContent = $this->renderPartial(
          'v_list_customeroptik',
          array(
            'userid_actor' => $userid_actor,
            'customers' => $customers
          ),
          true
        );

        $this->render(
          'index_datamaster_customeroptik',
          array(
            'TheMenu' => $TheMenu,
            'TheContent' => $TheContent,
            'userid_actor' => $userid_actor
          )
        );
	    }

	    /*
	      actionListCustomerOptik

	      Deskripsi
	      Action untuk menampilkan daftar customer optik

	    */
	    public function actionListCustomerOptik()
	    {
	      $Criteria = new CDbCriteria();
	      $Criteria->condition = 'is_del = 0 AND tipe = 2';

	      $userid_actor = Yii::app()->request->getParam('userid_actor');
	      $customers = sys_customer::model()->findAll($Criteria);
	      $TheMenu = FHelper::RenderMenu(0, $userid_actor);

	      $this->layout = 'setting';
	      $TheContent = $this->renderPartial(
          'v_list_customeroptik',
          array(
            'userid_actor' => $userid_actor,
            'customers' => $customers
          ),
          true
        );

        echo CJSON::encode(array('html' => $TheContent));
	    }

      /*
        actionAddCustomerOptik

        Deskripsi
        Action untuk menampilkan form penambahan customer optik dan mengolah form
        submission.
      */
	    public function actionAddCustomerOptik()
	    {
	      $userid_actor = Yii::app()->request->getParam('userid_actor');
	      $customers = sys_customer::model()->findAll();

	      $form = new frmEditCustomerOptik();

	      $do_add = Yii::app()->request->getParam('do_add');

	      if(isset($do_add))
	      {
	        if($do_add == 1)
	        {
	          //proses form submission

            $form->attributes = Yii::app()->request->getParam('frmEditCustomerOptik');

            if($form->validate())
            {
              //simpan record ke tabel
              $sys_customer = new sys_customer();
              $sys_customer['nama'] = $form['nama'];
              $sys_customer['alamat'] = $form['alamat'];
              $sys_customer['telepon'] = $form['telepon'];
              $sys_customer['email'] = $form['email'];
              $sys_customer['tipe'] = 2;
              $sys_customer->save();

              //tampilkan informasi sukses menambahkan record lokasi
              $html = $this->renderPartial(
                'v_addcustomeroptik_success',
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
                'vfrm_addcustomeroptik',
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
	          //batal menambah lokasi.
	          //alihkan ke view list lokasi.
	          $Criteria = new CDbCriteria();
            $Criteria->condition = 'is_del = 0 AND tipe = 2';

            $userid_actor = Yii::app()->request->getParam('userid_actor');
            $customers = sys_customer::model()->findAll($Criteria);

            $html = $this->renderPartial(
              'v_list_customeroptik',
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
	        //show form add lokasi
	        $html = $this->renderPartial(
            'vfrm_addcustomeroptik',
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
	      actionEditCustomerOptik

	      Deskripsi
	      Action untuk menampilkan form edit customer optik dan mengolah form submission.
	    */
	    public function actionEditCustomerOptik()
	    {
	      $userid_actor = Yii::app()->request->getParam('userid_actor');
	      $idcustomer = Yii::app()->request->getParam('idcustomer');
	      $do_edit = Yii::app()->request->getParam('do_edit');

	      if(isset($do_edit))
	      {
	        if($do_edit == 1)
	        {
	          //proses edit form submission

            $form = new frmEditCustomerOptik();
            $form->attributes = Yii::app()->request->getParam('frmEditCustomerOptik');

            if($form->validate())
            {
              $Criteria = new CDbCriteria();
              $Criteria->condition = 'id = :idcustomer';
              $Criteria->params = array(':idcustomer' => $idcustomer);

              //simpan record ke tabel
              $sys_customer = sys_customer::model()->find($Criteria);
              $sys_customer['nama'] = $form['nama'];
              $sys_customer['alamat'] = $form['alamat'];
              $sys_customer['telepon'] = $form['telepon'];
              $sys_customer['email'] = $form['email'];
              $sys_customer->update();

              //tampilkan informasi sukses menambahkan record lokasi
              $html = $this->renderPartial(
                'v_editcustomeroptik_success',
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
                'vfrm_editcustomeroptik',
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
	          //batal edit
	          //kembali ke daftar customer
	          $Criteria = new CDbCriteria();
            $Criteria->condition = 'is_del = 0 AND tipe = 2';

            $userid_actor = Yii::app()->request->getParam('userid_actor');
            $customers = sys_customer::model()->findAll($Criteria);

            $html = $this->renderPartial(
              'v_list_customeroptik',
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

          $customers = sys_customer::model()->find($Criteria);

          $form = new frmEditCustomerOptik();
          $form['nama'] = $customers['nama'];
          $form['alamat'] = $customers['alamat'];
          $form['telepon'] = $customers['telepon'];
          $form['email'] = $customers['email'];

	        //show form add customer
	        $html = $this->renderPartial(
            'vfrm_editcustomeroptik',
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
	      actionDeleteCustomerOptik

	      Deskripsi
	      Action untuk mengubah flag is_del pada record sys_customer
	    */
	    public function actionDeleteCustomerOptik()
	    {
	      $userid_actor = Yii::app()->request->getParam('userid_actor');
	      $idcustomer = Yii::app()->request->getParam('idcustomer');

	      $Criteria = new CDbCriteria();
        $Criteria->condition = 'id = :idcustomer';
        $Criteria->params = array(':idcustomer' => $idcustomer);

        //update record di tabel
        $sys_customer = sys_customer::model()->find($Criteria);
        $sys_customer['is_del'] = 1;
        $sys_customer->update();

        //tampilkan informasi sukses menambahkan record lokasi
        $html = $this->renderPartial(
          'v_deletecustomeroptik_success',
          array(
            'userid_actor' => $userid_actor
          ),
          true
        );

        echo CJSON::encode(array('html' => $html));
	    }

	  //customer optik - end


	  //produk - begin

	    /*
	      GetKategoriProduk

	      Deskripsi
	      Fungsi untuk mengembalikan kategori produk berdasarkan tipe_produk

	      Parameter
	      tipe_produk
	        String, menyatakan tipe produk (lensa, frame, softlens, solution...)
	    */
	    private function GetKategoriProduk($tipe_produk)
	    {
	      /*
	        !!! PERHATIAN !!!

	        Name --> value
	        pada block switch ini menjadi rujukan
	      */

        switch($tipe_produk)
        {
          case 'lensa' :    return 1;
          case 'frame' :    return 2;
          case 'softlens' : return 3;
          case 'solution' : return 4;
          case 'accessories' : return 5;
          case 'services' : return 6;
          case 'other' :    return 7;
          case 'supplies' : return 8;
          case 'paket' : return 9;
        }
	    }
	    
	    /*
	      GetTipeProdukJudul

	      Deskripsi
	      Fungsi untuk mengembalikan string kategori produk berdasarkan tipe_produk
	      untuk ditampilkan sebagai judul

	      Parameter
	      tipe_produk
	        String, menyatakan tipe produk (lensa, frame, softlens, solution...)
	    */
	    private function GetTipeProdukJudul($tipe_produk)
	    {
	      /*
	        !!! PERHATIAN !!!

	        Name --> value
	        pada block switch ini menjadi rujukan
	      */

        switch($tipe_produk)
        {
          case 'lensa' :    return 'Lensa';
          case 'frame' :    return 'Frame';
          case 'softlens' : return 'Softlens';
          case 'solution' : return 'Solution';
          case 'accessories' : return 'Accessories';
          case 'services' : return 'Services';
          case 'other' :    return 'Other';
          case 'supplies' : return 'Supplies';
          case 'paket' : return 'Paket';
        }
	    }
	    
	    private function SimpanInventory($form, $tipe_produk)
	    {
	      $inv_inventory = new inv_inventory();
        $inv_inventory['nama'] = ($form['nama']);
        $inv_inventory['brand'] = ($form['brand']);
        $inv_inventory['idsupplier'] = ($form['id_supplier']);
        $inv_inventory['idkategori'] = ($form['id_kategori']);
        $inv_inventory['tanggal_daftar'] = date('Y-m-j H:i:s');
        $inv_inventory['tanggal_update'] = date('Y-m-j H:i:s');
        $inv_inventory->save();
        
        do
        {
          $inv_id = $inv_inventory->getPrimaryKey();
          
          Yii::log('MasterController::SimpanInventory >> $tipe_produk = ' . $tipe_produk, 'info');
          Yii::log('MasterController::SimpanInventory >> $inv_id = ' . $inv_id, 'info');
          
          sleep(1);
        } while($inv_id == null);
	      
        //masukkan record ke inv_type_[tipe_produk]
	      switch($tipe_produk)
        {
          case 'lensa' :
            
            $Criteria = new CDbCriteria();
            $Criteria->condition = '
              id_material = :id_material and
              id_lens_type = :id_lens_type and
              material = :material and
              base_curve = :base_curve and
              add_1 = :add_1 and
              sph_min = :sph_min and
              cyl_min = :cyl_min and
              index_of_refraction = :index_of_refraction and
              uv_uvx = :uv_uvx and
              uv_uvx_charge = :uv_uvx_charge and
              color = :color and
              harga_minimum = :harga_minimum and
              id_production_type = :id_production_type and
              id_availability_type = :id_availability_type and
              diameter = :diameter
            ';
            $Criteria->params = array(
              ':id_material' => $form['id_basic_material'],
              ':id_lens_type' => $form['id_lens_type'],
              ':material' => $form['material'],
              ':base_curve' => $form['base_curve'],
              ':add_1' => $form['add_1'],
              ':sph_min' => $form['sph_min'],
              ':cyl_min' => $form['cyl_min'],
              ':index_of_refraction' => $form['index_of_refraction'],
              ':uv_uvx' => $form['uv_uvx'],
              ':uv_uvx_charge' => $form['uv_uvx_charge'],
              ':color' => $form['color'],
              ':harga_minimum' => $form['harga_minimum'],
              ':id_production_type' => $form['id_production_type'],
              ':id_availability_type' => $form['id_availability_type'],
              ':diameter' => $form['diameter'],
            );
            $count = inv_type_lens::model()->count($Criteria);
            
            Yii::log('count = ' . $count, 'info');
            
            if($count == 0)
            {
              $inv_type_lens = new inv_type_lens();
              $inv_type_lens['id_item'] = $inv_id;
              $inv_type_lens['id_material'] = ($form['id_basic_material']);
              $inv_type_lens['id_lens_type'] = ($form['id_lens_type']);
              $inv_type_lens['material'] = ($form['material']);
              $inv_type_lens['base_curve'] = ($form['base_curve']);
              $inv_type_lens['add_1'] = ($form['add_1']);
              $inv_type_lens['sph_min'] = ($form['sph_min']);
              $inv_type_lens['cyl_min'] = ($form['cyl_min']);
              $inv_type_lens['index_of_refraction'] = ($form['index_of_refraction']);
              $inv_type_lens['uv_uvx'] = ($form['uv_uvx']);
              $inv_type_lens['uv_uvx_charge'] = ($form['uv_uvx_charge']);
              $inv_type_lens['color'] = ($form['color']);
              $inv_type_lens['color_charge'] = ($form['color_charge']);
              $inv_type_lens['harga_minimum'] = ($form['harga_minimum']);
              $inv_type_lens['harga_pp'] = ($form['harga_pp']);
              $inv_type_lens['id_production_type'] = ($form['id_production_type']);
              $inv_type_lens['id_availability_type'] = ($form['id_availability_type']);
              $inv_type_lens['diameter'] = ($form['diameter']); 
              
              $inv_type_lens->save();
  
              /*
              $inv_type_lens['sph_min'] = $form['sph_min'];
              $inv_type_lens['soh_max'] = $form['sph_max'];
              $inv_type_lens['cyl_min'] = $form['cyl_min'];
              $inv_type_lens['cyl_max'] = $form['cyl_max'];
              $inv_type_lens['add_1'] = $form['add_1'];
              $inv_type_lens['add_2'] = $form['add_2'];
              $inv_type_lens['max_comb'] = $form['max_comb'];
              */
  
              
            }
            else
            {
              //duplikat 
              inv_inventory::model()->deleteByPk($inv_id);
              
              $inv_id = -1;
            }

            return $inv_id;
          case 'frame' :
            
            $Criteria = new CDbCriteria();
            $Criteria->condition = '
              nama_tipe = :nama_tipe and
              id_frame_type = :id_frame_type and
              material = :material and
              eye_size = :eye_size and
              dbl = :dbl and
              ed = :ed and
              vertical = :vertical and
              gcd = :gcd and
              temple = :temple and
              color = :color and
              harga_minimum = :harga_minimum
            ';
            $Criteria->params = array(
              ':nama_tipe' => $form['nama_tipe'],
              ':id_frame_type' => $form['id_frame_type'],
              ':material' => $form['material'],
              ':eye_size' => $form['eye_size'],
              ':dbl' => $form['dbl'],
              ':ed' => $form['ed'],
              ':vertical' => $form['vertical'],
              ':gcd' => $form['gcd'],
              ':temple' => $form['temple'],
              ':color' => $form['color'],
              ':harga_minimum' => $form['harga_minimum'],
            );
            $count = inv_type_frame::model()->count($Criteria);
            
            if($count == 0)
            {
              $inv_type_frame = new inv_type_frame();
              $inv_type_frame['id_item'] = $inv_id;
              $inv_type_frame['nama_tipe'] = ($form['nama_tipe']);
              $inv_type_frame['id_frame_type'] = ($form['id_frame_type']);
              $inv_type_frame['material'] = ($form['material']);
              $inv_type_frame['eye_size'] = ($form['eye_size']);
              $inv_type_frame['dbl'] = ($form['dbl']);
              $inv_type_frame['ed'] = ($form['ed']);
              $inv_type_frame['vertical'] = ($form['vertical']);
              $inv_type_frame['gcd'] = ($form['gcd']);
              $inv_type_frame['temple'] = ($form['temple']);
              $inv_type_frame['color'] = ($form['color']);
              $inv_type_frame['harga_minimum'] = ($form['harga_minimum']);
              $inv_type_frame['harga_pp'] = ($form['harga_pp']);
              
              if($inv_type_frame->validate())
              {
                $inv_type_frame->save();
              }
              else
              {
                $kesalahan = $inv_type_frame->getErrors();
                
                Yii::log("kesalahan waktu menyimpan inv_type_frame : " . print_r($kesalahan, true), 'info');
              }
  
              
                
            }
            else
            {
              //duplikat 
              inv_inventory::model()->deleteByPk($inv_id);
              
              $inv_id = -1;
            }

            return $inv_id;
          case 'softlens' :
            
            $Criteria = new CDbCriteria();
            $Criteria->condition = '
              nama_tipe = :nama_tipe and
              id_softlens_type = :id_softlens_type and
              material = :material and
              diameter = :diameter and
              water = :water and
              base_curve = :base_curve and
              permeability = :permeability and
              transmisibility = :transmisibility and
              center_thickness = :center_thickness and
              id_wearing_type = :id_wearing_type and
              id_change_type = :id_change_type and
              color = :color and
              sph_min = :sph_min and
              cyl_min = :cyl_min and
              add_1 = :add_1 and
              harga_minimum = :harga_minimum
            ';
            $Criteria->params = array(
              'nama_tipe' => $form['nama_tipe'],
              'id_softlens_type' => $form['id_softlens_type'],
              'material' => $form['material'],
              'diameter' => $form['diameter'],
              'water' => $form['water'],
              'base_curve' => $form['base_curve'],
              'permeability' => $form['permeability'],
              'transmisibility' => $form['transmisibility'],
              'center_thickness' => $form['center_thickness'],
              'id_wearing_type' => $form['id_wearing_type'],
              'id_change_type' => $form['id_change_type'],
              'color' => $form['color'],
              'sph_min' => $form['sph_min'],
              'cyl_min' => $form['cyl_min'],
              'add_1' => $form['add_1'],
              'harga_minimum' => $form['harga_minimum']
            );
            $count = inv_type_softlens::model()->count($Criteria);
            
            if($count == 0)
            {
              $inv_type_softlens = new inv_type_softlens();
              $inv_type_softlens['id_item'] = $inv_id;
              $inv_type_softlens['nama_tipe'] = ($form['nama_tipe']);
              $inv_type_softlens['id_softlens_type'] = ($form['id_softlens_type']);
              $inv_type_softlens['material'] = ($form['material']);
              $inv_type_softlens['diameter'] = ($form['diameter']);
              $inv_type_softlens['water'] = ($form['water']);
              $inv_type_softlens['base_curve'] = ($form['base_curve']);
              $inv_type_softlens['permeability'] = ($form['permeability']);
              $inv_type_softlens['transmisibility'] = ($form['transmisibility']);
              $inv_type_softlens['center_thickness'] = ($form['center_thickness']);
              $inv_type_softlens['id_wearing_type'] = ($form['id_wearing_type']);
              $inv_type_softlens['id_change_type'] = ($form['id_change_type']);
              $inv_type_softlens['color'] = ($form['color']);
              $inv_type_softlens['sph_min'] = ($form['sph_min']);
              $inv_type_softlens['cyl_min'] = ($form['cyl_min']);
              $inv_type_softlens['add_1'] = ($form['add_1']);
              $inv_type_softlens['harga_minimum'] = ($form['harga_minimum']);
              $inv_type_softlens['harga_pp'] = ($form['harga_pp']);
  
              $inv_type_softlens->save();
            }
            else
            {
              //duplikat 
              inv_inventory::model()->deleteByPk($inv_id);
              
              $inv_id = -1;
            }

            return $inv_id;
          case 'solution' :
            $inv_type_solution = new inv_type_solution();
            $inv_type_solution['id_item'] = $inv_id;
            $inv_type_solution['nama_tipe'] = ($form['nama_tipe']);
            $inv_type_solution['harga_minimum'] = ($form['harga_minimum']);
            $inv_type_solution['harga_pp'] = ($form['harga_pp']);
                                           
            $inv_type_solution->save();

            return $inv_id;
          case 'accessories' :
            $inv_type_accessories = new inv_type_accessories();
            $inv_type_accessories['id_item'] = $inv_id;
            $inv_type_accessories['nama_tipe'] = ($form['nama_tipe']);
            $inv_type_accessories['harga_minimum'] = ($form['harga_minimum']);
            $inv_type_accessories['harga_pp'] = ($form['harga_pp']);

            $inv_type_accessories->save();

            return $inv_id;
          case 'services' :
            $inv_type_services = new inv_type_services();
            $inv_type_services['id_item'] = $inv_id;
            $inv_type_services['nama_tipe'] = ($form['nama_tipe']);
            $inv_type_services['harga_minimum'] = ($form['harga_minimum']);
            $inv_type_services['harga_pp'] = ($form['harga_pp']);

            $inv_type_services->save();

            return $inv_id;
          case 'other' :
            $inv_type_other = new inv_type_other();
            $inv_type_other['id_item'] = $inv_id;
            $inv_type_other['nama_tipe'] = ($form['nama_tipe']);
            $inv_type_other['harga_minimum'] = ($form['harga_minimum']);
            $inv_type_other['harga_pp'] = ($form['harga_pp']);

            $inv_type_other->save();

            return $inv_id;
          case 'supplies' :
            $inv_type_supplies = new inv_type_supplies();
            $inv_type_supplies['id_item'] = $inv_id;
            $inv_type_supplies['nama_tipe'] = ($form['nama_tipe']);
            $inv_type_supplies['harga_minimum'] = ($form['harga_minimum']);
            $inv_type_supplies['harga_pp'] = ($form['harga_pp']);

            $inv_type_supplies->save();

            return $inv_id;

          case 'paket' : //simpan inventory tipe paket
            $inv_type_paket = new inv_type_paket();
            $inv_type_paket['id_item'] = $inv_id;
            $inv_type_paket['nama'] = ($form['nama']);
            $inv_type_paket['brand'] = ($form['brand']);
            $inv_type_paket['harga_minimum'] = ($form['harga_minimum']); 
            $inv_type_paket->save();

            $id_paket = $inv_type_paket->getPrimaryKey();

            foreach($form['item_paket'] as $key => $item)
            {
              $inv_paket_detail = new inv_paket_detail();
              $inv_paket_detail['idpaket'] = $id_paket;
              $inv_paket_detail['iditem'] = $item;
              $inv_paket_detail['hpp'] = ($form['item_paket_harga'][$key] == null ? 0 : $form['item_paket_harga'][$key]);
              
              try
              {
                $inv_paket_detail->save();
              }
              catch(Exception $e)
              {
              }       
              
            }

            return $inv_id;
        }
	    }

	    /*
	      GetInventory($tipe_produk, $idproduk)
	      
	      Deskripsi
	      Mengembalikan object form yang disesuaikan dengan tipe_produk dan idproduk
	    */
	    private function GetInventory($tipe_produk, $idproduk)
	    {
	      switch($tipe_produk)
        {
          case 'lensa' :
            $Criteria = new CDbCriteria();
            $Criteria->condition = 'id = :idproduk';
            $Criteria->params = array(':idproduk' => $idproduk);

            $inv_inventory = inv_inventory::model()->with('lensa')->find($Criteria);

            $Criteria->condition = 'id_item = :idproduk';
            $Criteria->params = array(':idproduk' => $idproduk);
            $inv_type_lens = inv_type_lens::model()->find($Criteria);

            $form = $this->GetAddProdukForm($tipe_produk);

            //ambil data inv_inventory
              $form['id_produk'] = $idproduk;
              $form['nama'] = $inv_inventory['nama'];
              $form['brand'] = $inv_inventory['brand'];
              $form['id_supplier'] = $inv_inventory['idsupplier'];
              $form['id_kategori'] = $inv_inventory['idkategori'];

            //ambil data inv_type_lens
              $form['id_basic_material'] = $inv_type_lens['id_material'];
              $form['id_lens_type'] = $inv_type_lens['id_lens_type'];
              $form['material'] = $inv_type_lens['material'];
              
              $form['sph_min'] = $inv_type_lens['sph_min'];
              $form['cyl_min'] = $inv_type_lens['cyl_min'];
              $form['add_1'] = $inv_type_lens['add_1'];
              
              $form['base_curve'] = $inv_type_lens['base_curve'];
              $form['index_of_refraction'] = $inv_type_lens['index_of_refraction'];
              $form['uv_uvx'] = $inv_type_lens['uv_uvx'];
              $form['uv_uvx_charge'] = $inv_type_lens['uv_uvx_charge'];
              $form['color'] = $inv_type_lens['color'];
              $form['color_charge'] = $inv_type_lens['color_charge'];
              $form['harga_minimum'] = $inv_type_lens['harga_minimum'];
              $form['harga_pp'] = $inv_type_lens['harga_pp'];
              $form['id_production_type'] = $inv_type_lens['id_production_type'];
              $form['id_availability_type'] = $inv_type_lens['id_availability_type'];
              $form['diameter'] = $inv_type_lens['diameter'];

              return $form;
          case 'frame' :
            $Criteria = new CDbCriteria();
            $Criteria->condition = 'id = :idproduk';
            $Criteria->params = array(':idproduk' => $idproduk);

            $inv_inventory = inv_inventory::model()->with('lensa')->find($Criteria);

            $Criteria->condition = 'id_item = :idproduk';
            $Criteria->params = array(':idproduk' => $idproduk);
            $inv_type_frame = inv_type_frame::model()->find($Criteria);

            $form = $this->GetAddProdukForm($tipe_produk);

            //ambil data inv_inventory
              $form['id_produk'] = $idproduk;
              $form['nama'] = $inv_inventory['nama'];
              $form['brand'] = $inv_inventory['brand'];
              $form['id_supplier'] = $inv_inventory['idsupplier'];
              $form['id_kategori'] = $inv_inventory['idkategori'];

            //ambil data inv_type_frame
              $form['nama_tipe'] = $inv_type_frame['nama_tipe'];
              $form['id_frame_type'] = $inv_type_frame['id_frame_type'];
              $form['material'] = $inv_type_frame['material'];
              $form['eye_size'] = $inv_type_frame['eye_size'];
              $form['dbl'] = $inv_type_frame['dbl'];
              $form['ed'] = $inv_type_frame['ed'];
              $form['vertical'] = $inv_type_frame['vertical'];
              $form['gcd'] = $inv_type_frame['gcd'];
              $form['temple'] = $inv_type_frame['temple'];
              $form['color'] = $inv_type_frame['color'];
              $form['harga_minimum'] = $inv_type_frame['harga_minimum'];
              $form['harga_pp'] = $inv_type_frame['harga_pp'];

              return $form;
          case 'softlens' :
            $Criteria = new CDbCriteria();
            $Criteria->condition = 'id = :idproduk';
            $Criteria->params = array(':idproduk' => $idproduk);

            $inv_inventory = inv_inventory::model()->with('lensa')->find($Criteria);

            $Criteria->condition = 'id_item = :idproduk';
            $Criteria->params = array(':idproduk' => $idproduk);
            $inv_type_softlens = inv_type_softlens::model()->find($Criteria);

            $form = new frmEditProdukSoftlens();

            //ambil data inv_inventory
              $form['id_produk'] = $idproduk;
              $form['nama'] = $inv_inventory['nama'];
              $form['brand'] = $inv_inventory['brand'];
              $form['id_supplier'] = $inv_inventory['idsupplier'];
              $form['id_kategori'] = $inv_inventory['idkategori'];

            //ambil data inv_type_softlens
              $form['nama_tipe'] = $inv_type_softlens['nama_tipe'];
              $form['id_softlens_type'] = $inv_type_softlens['id_softlens_type'];
              $form['material'] = $inv_type_softlens['material'];
              $form['diameter'] = $inv_type_softlens['diameter'];
              $form['water'] = $inv_type_softlens['water'];
              $form['base_curve'] = $inv_type_softlens['base_curve'];
              $form['permeability'] = $inv_type_softlens['permeability'];
              $form['transmisibility'] = $inv_type_softlens['transmisibility'];
              $form['center_thickness'] = $inv_type_softlens['center_thickness'];
              $form['id_wearing_type'] = $inv_type_softlens['id_wearing_type'];
              $form['id_change_type'] = $inv_type_softlens['id_change_type'];
              $form['color'] = $inv_type_softlens['color'];
              $form['sph_min'] = $inv_type_softlens['sph_min'];
              $form['cyl_min'] = $inv_type_softlens['cyl_min'];
              $form['add_1'] = $inv_type_softlens['add_1'];
              $form['harga_minimum'] = $inv_type_softlens['harga_minimum'];
              $form['harga_pp'] = $inv_type_softlens['harga_pp'];

            return $form;
          case 'solution' :
            $Criteria = new CDbCriteria();
            $Criteria->condition = 'id = :idproduk';
            $Criteria->params = array(':idproduk' => $idproduk);

            $inv_inventory = inv_inventory::model()->with('solution')->find($Criteria);

            $Criteria->condition = 'id_item = :idproduk';
            $Criteria->params = array(':idproduk' => $idproduk);
            $inv_type_solution = inv_type_solution::model()->find($Criteria);

            $form = $this->GetAddProdukForm($tipe_produk);

            //ambil data inv_inventory
              $form['id_produk'] = $idproduk;
              $form['nama'] = $inv_inventory['nama'];
              $form['brand'] = $inv_inventory['brand'];
              $form['id_supplier'] = $inv_inventory['idsupplier'];
              $form['id_kategori'] = $inv_inventory['idkategori'];

            //ambil data inv_type_solution
              $form['nama_tipe'] = $inv_type_solution['nama_tipe'];
              $form['harga_minimum'] = $inv_type_solution['harga_minimum'];
              $form['harga_pp'] = $inv_type_solution['harga_pp'];

            return $form;
          case 'accessories' :
            $Criteria = new CDbCriteria();
            $Criteria->condition = 'id = :idproduk';
            $Criteria->params = array(':idproduk' => $idproduk);

            $inv_inventory = inv_inventory::model()->with('accessories')->find($Criteria);

            $Criteria->condition = 'id_item = :idproduk';
            $Criteria->params = array(':idproduk' => $idproduk);
            $inv_type_accessories = inv_type_accessories::model()->find($Criteria);

            $form = $this->GetAddProdukForm($tipe_produk);

            //ambil data inv_inventory
              $form['id_produk'] = $idproduk;
              $form['nama'] = $inv_inventory['nama'];
              $form['brand'] = $inv_inventory['brand'];
              $form['id_supplier'] = $inv_inventory['idsupplier'];
              $form['id_kategori'] = $inv_inventory['idkategori'];

            //ambil data inv_type_accesories
              $form['nama_tipe'] = $inv_type_accessories['nama_tipe'];
              $form['harga_minimum'] = $inv_type_accessories['harga_minimum'];
              $form['harga_pp'] = $inv_type_accessories['harga_pp'];

            return $form;
          case 'services' :
            $Criteria = new CDbCriteria();
            $Criteria->condition = 'id = :idproduk';
            $Criteria->params = array(':idproduk' => $idproduk);

            $inv_inventory = inv_inventory::model()->with('services')->find($Criteria);

            $Criteria->condition = 'id_item = :idproduk';
            $Criteria->params = array(':idproduk' => $idproduk);
            $inv_type_services = inv_type_services::model()->find($Criteria);

            $form = $this->GetAddProdukForm($tipe_produk);

            //ambil data inv_inventory
              $form['id_produk'] = $idproduk;
              $form['nama'] = $inv_inventory['nama'];
              $form['brand'] = $inv_inventory['brand'];
              $form['id_supplier'] = $inv_inventory['idsupplier'];
              $form['id_kategori'] = $inv_inventory['idkategori'];

            //ambil data inv_type_services
              $form['nama_tipe'] = $inv_type_services['nama_tipe'];
              $form['harga_minimum'] = $inv_type_services['harga_minimum'];
              $form['harga_pp'] = $inv_type_services['harga_pp'];

            return $form;
          case 'other' :
            $Criteria = new CDbCriteria();
            $Criteria->condition = 'id = :idproduk';
            $Criteria->params = array(':idproduk' => $idproduk);

            $inv_inventory = inv_inventory::model()->with('other')->find($Criteria);

            $Criteria->condition = 'id_item = :idproduk';
            $Criteria->params = array(':idproduk' => $idproduk);
            $inv_type_other = inv_type_other::model()->find($Criteria);

            $form = $this->GetAddProdukForm($tipe_produk);

            //ambil data inv_inventory
              $form['id_produk'] = $idproduk;
              $form['nama'] = $inv_inventory['nama'];
              $form['brand'] = $inv_inventory['brand'];
              $form['id_supplier'] = $inv_inventory['idsupplier'];
              $form['id_kategori'] = $inv_inventory['idkategori'];

            //ambil data inv_type_other
              $form['nama_tipe'] = $inv_type_other['nama_tipe'];
              $form['harga_minimum'] = $inv_type_other['harga_minimum'];
              $form['harga_pp'] = $inv_type_other['harga_pp'];

            return $form;
          case 'supplies' :
            $Criteria = new CDbCriteria();
            $Criteria->condition = 'id = :idproduk';
            $Criteria->params = array(':idproduk' => $idproduk);

            $inv_inventory = inv_inventory::model()->with('supplies')->find($Criteria);

            $Criteria->condition = 'id_item = :idproduk';
            $Criteria->params = array(':idproduk' => $idproduk);
            $inv_type_supplies = inv_type_supplies::model()->find($Criteria);

            $form = $this->GetAddProdukForm($tipe_produk);

            //ambil data inv_inventory
              $form['id_produk'] = $idproduk;
              $form['nama'] = $inv_inventory['nama'];
              $form['brand'] = $inv_inventory['brand'];
              $form['id_supplier'] = $inv_inventory['idsupplier'];
              $form['id_kategori'] = $inv_inventory['idkategori'];

            //ambil data inv_type_other
              $form['nama_tipe'] = $inv_type_supplies['nama_tipe'];
              $form['harga_minimum'] = $inv_type_supplies['harga_minimum'];
              $form['harga_pp'] = $inv_type_supplies['harga_pp'];

            return $form;

          case 'paket' :
            //ambil record inventory - paket
            
            $Criteria = new CDbCriteria();
            $Criteria->condition = 't.id = :idproduk';
            $Criteria->params = array(':idproduk' => $idproduk);
            
            $inv_inventory = inv_inventory::model()->with('paket')->find($Criteria);
            $paket = $inv_inventory->paket;
            $inv_paket_detail = $paket->details;
            
            /*
            $Criteria = new CDbCriteria();
            $Criteria->condition = 'id = :idproduk';
            $Criteria->params = array(':idproduk' => $idproduk);
            $inv_type_paket = inv_type_paket::model()->find($Criteria);

            $Criteria->condition = 'idpaket = :idproduk';
            $Criteria->params = array(':idproduk' => $idproduk);
            $inv_paket_detail = inv_paket_detail::model()->findAll($Criteria);
            */
            
            $form = $this->GetAddProdukForm($tipe_produk);

            //ambil data inv_inventory
              $form['id_produk'] = $idproduk;
              $form['id_kategori'] = $this->GetKategoriProduk($tipe_produk);
              $form['nama'] = $inv_inventory['nama'];
              $form['brand'] = $inv_inventory['brand'];
              $form['item_paket'] = $inv_paket_detail;
              $form['harga_minimum'] = $paket['harga_minimum'];

            return $form;
        }
	    }

	    /*
	      UpdateInventory($form, $tipe_produk, $idproduk)
	      
	      Deskripsi
	      Melakukan update data inventory. Logika update disesuaikan dengan
	      tipe_produk.
	    */
	    private function UpdateInventory($form, $tipe_produk, $idproduk)
	    {
	      switch($tipe_produk)
        {
          case 'lensa' :
            $Criteria = new CDbCriteria();

            //simpan info inv_inventory
              $Criteria->condition = 'id = :idlensa';
              $Criteria->params = array(':idlensa' => $idproduk);
              $inv_inventory = inv_inventory::model()->find($Criteria);

              $inv_inventory['nama'] = $form['nama'];
              $inv_inventory['brand'] = $form['brand'];
              $inv_inventory['idsupplier'] = $form['id_supplier'];
              $inv_inventory['idkategori'] = $form['id_kategori'];
              $inv_inventory['tanggal_update'] = date('Y-m-j H:i:s');
              $inv_inventory->update();

            //simpan info inv_type_lens
              $Criteria->condition = 'id_item = :idlensa';
              $Criteria->params = array(':idlensa' => $idproduk);
              $inv_type_lens = inv_type_lens::model()->find($Criteria);

              $inv_type_lens['id_material'] = $form['id_basic_material'];
              $inv_type_lens['id_lens_type'] = $form['id_lens_type'];
              $inv_type_lens['material'] = $form['material'];
              $inv_type_lens['base_curve'] = $form['base_curve'];
              
              $inv_type_lens['add_1'] = $form['add_1'];
              $inv_type_lens['sph_min'] = $form['sph_min'];
              $inv_type_lens['cyl_min'] = $form['cyl_min'];
              
              $inv_type_lens['index_of_refraction'] = $form['index_of_refraction'];
              $inv_type_lens['uv_uvx'] = $form['uv_uvx'];
              $inv_type_lens['uv_uvx_charge'] = $form['uv_uvx_charge'];
              $inv_type_lens['color'] = $form['color'];
              $inv_type_lens['color_charge'] = $form['color_charge'];
              $inv_type_lens['harga_minimum'] = $form['harga_minimum'];
              $inv_type_lens['harga_pp'] = $form['harga_pp'];
              $inv_type_lens['id_production_type'] = $form['id_production_type'];
              $inv_type_lens['id_availability_type'] = $form['id_availability_type'];
              $inv_type_lens['diameter'] = $form['diameter'];
              $inv_type_lens->update();

              break;
          case 'frame' :
            $Criteria = new CDbCriteria();

            //simpan info inv_inventory
              $Criteria->condition = 'id = :idproduk';
              $Criteria->params = array(':idproduk' => $idproduk);
              $inv_inventory = inv_inventory::model()->find($Criteria);

              $inv_inventory['nama'] = $form['nama'];
              $inv_inventory['brand'] = $form['brand'];
              $inv_inventory['idsupplier'] = $form['id_supplier'];
              $inv_inventory['idkategori'] = $form['id_kategori'];
              $inv_inventory['tanggal_update'] = date('Y-m-j H:i:s');
              $inv_inventory->update();

            //simpan info inv_type_frame
              $Criteria->condition = 'id_item = :idproduk';
              $Criteria->params = array(':idproduk' => $idproduk);
              $inv_type_frame = inv_type_frame::model()->find($Criteria);

              $inv_type_frame['id_item'] = $idproduk;
              $inv_type_frame['nama_tipe'] = $form['nama_tipe'];
              $inv_type_frame['id_frame_type'] = $form['id_frame_type'];
              $inv_type_frame['material'] = $form['material'];
              $inv_type_frame['eye_size'] = $form['eye_size'];
              $inv_type_frame['dbl'] = $form['dbl'];
              $inv_type_frame['ed'] = $form['ed'];
              $inv_type_frame['vertical'] = $form['vertical'];
              $inv_type_frame['gcd'] = $form['gcd'];
              $inv_type_frame['temple'] = $form['temple'];
              $inv_type_frame['color'] = $form['color'];
              $inv_type_frame['harga_minimum'] = $form['harga_minimum'];
              $inv_type_frame['harga_pp'] = $form['harga_pp'];

              $inv_type_frame->update();

              break;
          case 'softlens' :
            $Criteria = new CDbCriteria();

            //simpan info inv_inventory
              $Criteria->condition = 'id = :idproduk';
              $Criteria->params = array(':idproduk' => $idproduk);
              $inv_inventory = inv_inventory::model()->find($Criteria);

              $inv_inventory['nama'] = $form['nama'];
              $inv_inventory['brand'] = $form['brand'];
              $inv_inventory['idsupplier'] = $form['id_supplier'];
              $inv_inventory['idkategori'] = $form['id_kategori'];
              $inv_inventory['tanggal_update'] = date('Y-m-j H:i:s');
              $inv_inventory->update();

            //simpan info inv_type_softlens
              $Criteria->condition = 'id_item = :idproduk';
              $Criteria->params = array(':idproduk' => $idproduk);
              $inv_type_softlens = inv_type_softlens::model()->find($Criteria);

              $inv_type_softlens['id_item'] = $idproduk;
              $inv_type_softlens['nama_tipe'] = $form['nama_tipe'];
              $inv_type_softlens['id_softlens_type'] = $form['id_softlens_type'];
              $inv_type_softlens['id_wearing_type'] = $form['id_wearing_type'];
              $inv_type_softlens['id_change_type'] = $form['id_change_type'];
              $inv_type_softlens['material'] = $form['material'];
              $inv_type_softlens['diameter'] = $form['diameter'];
              $inv_type_softlens['water'] = $form['water'];
              $inv_type_softlens['base_curve'] = $form['base_curve'];
              $inv_type_softlens['permeability'] = $form['permeability'];
              $inv_type_softlens['transmisibility'] = $form['transmisibility'];
              $inv_type_softlens['center_thickness'] = $form['center_thickness'];
              $inv_type_softlens['color'] = $form['color'];
              $inv_type_softlens['sph_min'] = $form['sph_min'];
              $inv_type_softlens['cyl_min'] = $form['cyl_min'];
              $inv_type_softlens['add_1'] = $form['add_1'];
              $inv_type_softlens['harga_minimum'] = $form['harga_minimum'];
              $inv_type_softlens['harga_pp'] = $form['harga_pp'];

              $inv_type_softlens->update();

            break;
          case 'solution' :
            $Criteria = new CDbCriteria();

            //simpan info inv_inventory
              $Criteria->condition = 'id = :idproduk';
              $Criteria->params = array(':idproduk' => $idproduk);
              $inv_inventory = inv_inventory::model()->find($Criteria);

              $inv_inventory['nama'] = $form['nama'];
              $inv_inventory['brand'] = $form['brand'];
              $inv_inventory['idsupplier'] = $form['id_supplier'];
              $inv_inventory['idkategori'] = $form['id_kategori'];
              $inv_inventory['tanggal_update'] = date('Y-m-j H:i:s');
              $inv_inventory->update();

            //simpan info inv_type_softlens
              $Criteria->condition = 'id_item = :idproduk';
              $Criteria->params = array(':idproduk' => $idproduk);
              $inv_type_solution = inv_type_solution::model()->find($Criteria);

              $inv_type_solution['nama_tipe'] = $form['nama_tipe'];
              $inv_type_solution['harga_minimum'] = $form['harga_minimum'];
              $inv_type_solution['harga_pp'] = $form['harga_pp'];

              $inv_type_solution->update();

            break;
          case 'accessories' :
            $Criteria = new CDbCriteria();

            //simpan info inv_inventory
              $Criteria->condition = 'id = :idproduk';
              $Criteria->params = array(':idproduk' => $idproduk);
              $inv_inventory = inv_inventory::model()->find($Criteria);

              $inv_inventory['nama'] = $form['nama'];
              $inv_inventory['brand'] = $form['brand'];
              $inv_inventory['idsupplier'] = $form['id_supplier'];
              $inv_inventory['idkategori'] = $form['id_kategori'];
              $inv_inventory['tanggal_update'] = date('Y-m-j H:i:s');
              $inv_inventory->update();

            //simpan info inv_type_softlens
              $Criteria->condition = 'id_item = :idproduk';
              $Criteria->params = array(':idproduk' => $idproduk);
              $inv_type_accessories = inv_type_accessories::model()->find($Criteria);

              $inv_type_accessories['nama_tipe'] = $form['nama_tipe'];
              $inv_type_accessories['harga_minimum'] = $form['harga_minimum'];
              $inv_type_accessories['harga_pp'] = $form['harga_pp'];

              $inv_type_accessories->update();

            break;
          case 'services' :
            $Criteria = new CDbCriteria();

            //simpan info inv_inventory
              $Criteria->condition = 'id = :idproduk';
              $Criteria->params = array(':idproduk' => $idproduk);
              $inv_inventory = inv_inventory::model()->find($Criteria);

              $inv_inventory['nama'] = $form['nama'];
              $inv_inventory['brand'] = $form['brand'];
              $inv_inventory['idsupplier'] = $form['id_supplier'];
              $inv_inventory['idkategori'] = $form['id_kategori'];
              $inv_inventory['tanggal_update'] = date('Y-m-j H:i:s');
              $inv_inventory->update();

            //simpan info inv_type_softlens
              $Criteria->condition = 'id_item = :idproduk';
              $Criteria->params = array(':idproduk' => $idproduk);
              $inv_type_services = inv_type_services::model()->find($Criteria);

              $inv_type_services['nama_tipe'] = $form['nama_tipe'];
              $inv_type_services['harga_minimum'] = $form['harga_minimum'];
              $inv_type_services['harga_pp'] = $form['harga_pp'];

              $inv_type_services->update();

            break;
          case 'other' :
            $Criteria = new CDbCriteria();

            //simpan info inv_inventory
              $Criteria->condition = 'id = :idproduk';
              $Criteria->params = array(':idproduk' => $idproduk);
              $inv_inventory = inv_inventory::model()->find($Criteria);

              $inv_inventory['nama'] = $form['nama'];
              $inv_inventory['brand'] = $form['brand'];
              $inv_inventory['idsupplier'] = $form['id_supplier'];
              $inv_inventory['idkategori'] = $form['id_kategori'];
              $inv_inventory['tanggal_update'] = date('Y-m-j H:i:s');
              $inv_inventory->update();

            //simpan info inv_type_softlens
              $Criteria->condition = 'id_item = :idproduk';
              $Criteria->params = array(':idproduk' => $idproduk);
              $inv_type_other = inv_type_other::model()->find($Criteria);

              $inv_type_other['nama_tipe'] = $form['nama_tipe'];
              $inv_type_other['harga_minimum'] = $form['harga_minimum'];
              $inv_type_other['harga_pp'] = $form['harga_pp'];

              $inv_type_other->update();

            break;
          case 'supplies' :
            $Criteria = new CDbCriteria();

            //simpan info inv_inventory
              $Criteria->condition = 'id = :idproduk';
              $Criteria->params = array(':idproduk' => $idproduk);
              $inv_inventory = inv_inventory::model()->find($Criteria);

              $inv_inventory['nama'] = $form['nama'];
              $inv_inventory['brand'] = $form['brand'];
              $inv_inventory['idsupplier'] = $form['id_supplier'];
              $inv_inventory['idkategori'] = $form['id_kategori'];
              $inv_inventory['tanggal_update'] = date('Y-m-j H:i:s');
              $inv_inventory->update();

            //simpan info inv_type_softlens
              $Criteria->condition = 'id_item = :idproduk';
              $Criteria->params = array(':idproduk' => $idproduk);
              $inv_type_supplies = inv_type_supplies::model()->find($Criteria);

              $inv_type_supplies['nama_tipe'] = $form['nama_tipe'];
              $inv_type_supplies['harga_minimum'] = $form['harga_minimum'];
              $inv_type_supplies['harga_pp'] = $form['harga_pp'];

              $inv_type_supplies->update();

            break;

          case 'paket' : //UpdateInventory case 'paket'
            $Criteria = new CDbCriteria();

            //update info inv_type_paket
              $Criteria->condition = 'id = :idproduk';
              $Criteria->params = array(':idproduk' => $idproduk);
              $inv_inventory = inv_inventory::model()->find($Criteria);
              
              $inv_inventory['nama'] = $form['nama'];
              $inv_inventory['brand'] = $form['brand'];
              $inv_inventory['idsupplier'] = -1;
              $inv_inventory['idkategori'] = 9;
              $inv_inventory['tanggal_update'] = date('Y-m-j H:i:s');
              $inv_inventory->update();
              
              $Criteria->condition = 'id_item = :idproduk';
              $Criteria->params = array(':idproduk' => $idproduk);
              $inv_type_paket = inv_type_paket::model()->find($Criteria);
              
              $inv_type_paket['nama'] = $form['nama'];
              $inv_type_paket['brand'] = $form['brand'];
              $inv_type_paket['harga_minimum'] = $form['harga_minimum'];
              $inv_type_paket->update();

            //update info inv_paket_detail
              $Criteria->condition = 'idpaket = :idproduk';
              $Criteria->params = array(':idproduk' => $idproduk);
              $inv_paket_detail = new inv_paket_detail();
              $inv_paket_detail->deleteAll($Criteria);

              $item_paket = Yii::app()->request->getParam('item_paket');
              $item_paket_harga = Yii::app()->request->getParam('item_paket_jumlah');

              foreach($item_paket as $key => $item)
              {
                $inv_paket_detail = new inv_paket_detail();
                $inv_paket_detail['iditem'] = $item;
                $inv_paket_detail['idpaket'] = $idproduk;
                $inv_paket_detail['jumlah'] = $item_paket_harga[$key];

                $inv_paket_detail->save();
              }

            break;
        }
	    }

	    private function GetSuccessAddViewName($tipe_produk)
	    {
	      switch($tipe_produk)
        {
          case 'lensa' :    return 'v_addproduklensa_success';
          case 'frame' :    return 'v_addprodukframe_success';
          case 'softlens' : return 'v_addproduksoftlens_success';
          case 'solution' : return 'v_addprodukgeneral_success';
          case 'accessories' : return 'v_addprodukgeneral_success';
          case 'services' : return 'v_addprodukgeneral_success';
          case 'other' :    return 'v_addprodukgeneral_success';
          case 'supplies' : return 'v_addprodukgeneral_success';
          case 'paket' : return 'v_addprodukpaket_success';
        }
	    }

	    private function GetSuccessEditViewName($tipe_produk)
	    {
	      switch($tipe_produk)
        {
          case 'lensa' :    return 'v_editproduklensa_success';
          case 'frame' :    return 'v_editprodukframe_success';
          case 'softlens' : return 'v_editproduksoftlens_success';
          case 'solution' : return 'v_editprodukgeneral_success';
          case 'accessories' : return 'v_editprodukgeneral_success';
          case 'services' : return 'v_editprodukgeneral_success';
          case 'other' :    return 'v_editprodukgeneral_success';
          case 'supplies' : return 'v_editprodukgeneral_success';
          case 'paket' : return 'v_editprodukpaket_success';
        }
	    }

	    private function GetSuccessDeleteViewName($tipe_produk)
	    {
	      switch($tipe_produk)
        {
          case 'lensa' :    return 'v_deleteproduklensa_success';
          case 'frame' :    return 'v_deleteprodukframe_success';
          case 'softlens' : return 'v_deleteproduksoftlens_success';
          case 'solution' : return 'v_deleteproduksolution_success';
          case 'accessories' : return 'v_deleteprodukaccessories_success';
          case 'services' : return 'v_deleteprodukservices_success';
          case 'other' :    return 'v_deleteprodukother_success';
          case 'supplies' : return 'v_deleteproduksupplies_success';
          case 'paket' : return 'v_deleteprodukpaket_success';
        }
	    }

	    private function GetAddFormViewName($tipe_produk)
	    {
	      switch($tipe_produk)
        {
          case 'lensa' :    return 'vfrm_addproduklensa';
          case 'frame' :    return 'vfrm_addprodukframe';
          case 'softlens' : return 'vfrm_addproduksoftlens';
          case 'solution' : return 'vfrm_addprodukgeneral';
          case 'accessories' : return 'vfrm_addprodukgeneral';
          case 'services' : return 'vfrm_addprodukgeneral';
          case 'other' :    return 'vfrm_addprodukgeneral';
          case 'supplies' : return 'vfrm_addprodukgeneral';
          case 'paket' : return 'vfrm_addprodukpaket';
        }
	    }

	    private function GetEditFormViewName($tipe_produk)
	    {
	      switch($tipe_produk)
        {
          case 'lensa' :    return 'vfrm_editproduklensa';
          case 'frame' :    return 'vfrm_editprodukframe';
          case 'softlens' : return 'vfrm_editproduksoftlens';
          case 'solution' : return 'vfrm_editprodukgeneral';
          case 'accessories' : return 'vfrm_editprodukgeneral';
          case 'services' : return 'vfrm_editprodukgeneral';
          case 'other' :    return 'vfrm_editprodukgeneral';
          case 'supplies' : return 'vfrm_editprodukgeneral';
          case 'paket' : return 'vfrm_editprodukpaket';
        }
	    }

	    /*
	      GetListViewName

	      Deskripsi
	      Fungsi untuk mengembalikan view name berdasarkan tipe produk.

	      Parameter
	      tipe_produk. String yang menyatakan tipe produk (lensa, frame, softlens...)
	    */
	    private function GetListViewName($tipe_produk)
	    {
	      switch($tipe_produk)
        {
          case 'lensa' :    return 'v_list_produk_lensa';
          case 'frame' :    return 'v_list_produk_frame';
          case 'softlens' : return 'v_list_produk_softlens';
          case 'solution' : return 'v_list_produk_general';
          case 'accessories' : return 'v_list_produk_general';
          case 'services' : return 'v_list_produk_general';
          case 'other' :    return 'v_list_produk_general';
          case 'supplies' : return 'v_list_produk_general';
          case 'paket' : return 'v_list_produk_paket';
        }
	    }
	    
	    /*
	      GetListFieldCari

	      Deskripsi
	      Fungsi untuk mengembalikan daftar field pencarian tipe produk.

	      Parameter
	      tipe_produk. String yang menyatakan tipe produk (lensa, frame, softlens...)
	    */
	    private function GetListFieldCari($tipe_produk)
	    {
	      $field_cari['nama'] = 'Nama';
        $field_cari['brand'] = 'Brand';
            
	      switch($tipe_produk)
        {
          case 'lensa' :
            $field_cari['material'] = 'Material';
            $field_cari['ukuran'] = 'Ukuran';
            
            return $field_cari;
          case 'frame' :
            $field_cari['tipe'] = 'Tipe';
            $field_cari['material'] = 'Material';
            $field_cari['warna'] = 'Warna';
            $field_cari['ukuran'] = 'Ukuran';
            
            return $field_cari;
          case 'softlens' : 
            $field_cari['material'] = 'Material';
            $field_cari['ukuran'] = 'Ukuran';
            
            return $field_cari;
          case 'solution' : 
          case 'accessories' : 
          case 'services' : 
          case 'other' :    
          case 'supplies' : 
            //$field_cari['tipe'] = 'Tipe';
            
            return $field_cari;
        }
	    }
	    
	    /*
	      GetViewViewName

	      Deskripsi
	      Fungsi untuk mengembalikan view name berdasarkan tipe produk.

	      Parameter
	      tipe_produk. String yang menyatakan tipe produk (lensa, frame, softlens...)
	    */
	    private function GetViewViewName($tipe_produk)
	    {
	      switch($tipe_produk)
        {
          case 'lensa' :    return 'v_view_produk_lensa';
          case 'frame' :    return 'v_view_produk_frame';
          case 'softlens' : return 'v_view_produk_softlens';
          case 'solution' : return 'v_view_produk_general';
          case 'accessories' : return 'v_view_produk_general';
          case 'services' : return 'v_view_produk_general';
          case 'other' :    return 'v_view_produk_general';
          case 'supplies' : return 'v_view_produk_general';
          case 'paket' : return 'v_view_produk_paket';
        }
	    }

	    private function GetAddProdukForm($tipe_produk)
	    {
	      switch($tipe_produk)
        {
          case 'lensa' :    return new frmEditProdukLensa();
          case 'frame' :    return new frmEditProdukFrame();
          case 'softlens' : return new frmEditProdukSoftlens();
          case 'solution' : return new frmEditProdukGeneral();
          case 'accessories' : return new frmEditProdukGeneral();
          case 'services' : return new frmEditProdukGeneral();
          case 'other' :    return new frmEditProdukGeneral();
          case 'supplies' : return new frmEditProdukGeneral();
          case 'paket' : return new frmEditProdukPaket();
        }
	    }

	    private function GetProdukFormName($tipe_produk)
	    {
	      switch($tipe_produk)
        {
          case 'lensa' :    return 'frmEditProdukLensa';
          case 'frame' :    return 'frmEditProdukFrame';
          case 'softlens' : return 'frmEditProdukSoftlens';
          case 'solution' : return 'frmEditProdukGeneral';
          case 'accessories' : return 'frmEditProdukGeneral';
          case 'services' : return 'frmEditProdukGeneral';
          case 'other' :    return 'frmEditProdukGeneral';
          case 'supplies' : return 'frmEditProdukGeneral';
          case 'paket' : return 'frmEditProdukPaket';
        }
	    }
	    
	    /*
	      actionProduk

	      Deskripsi
	      Action untuk menampilkan daftar produk

	    */
	    public function actionProduk()
	    {
	      $menuid = 11;
	      $parentmenuid = 6;
	      $userid_actor = Yii::app()->request->getParam('userid_actor');
	      $this->idlokasi = Yii::app()->request->cookies['idlokasi']->value;
	      $idgroup = FHelper::GetGroupId($userid_actor);
	      $rows_per_page = 20;
        
        if(FHelper::AllowMenu($menuid, $idgroup, 'read'))
        {
          $daftar_fieldcari = $this->GetListFieldCari('lensa');
          
          //hitung pagecount
          $Criteria = new CDbCriteria();
          $Criteria->condition = 'is_del = 0 AND idkategori = 1';
          $records = inv_inventory::model()->count($Criteria);
          
          $pagecount = (int)($records / $rows_per_page);
          if(($records % $rows_per_page) > 0)
            $pagecount++;
          
          $Criteria = new CDbCriteria();
          $Criteria->condition = 'is_del = 0 AND idkategori = 1';
          $Criteria->offset = $pageno * $rows_per_page;
          $Criteria->limit = $rows_per_page;
          $products = inv_inventory::model()->findAll($Criteria);
          
          
          $TheMenu = FHelper::RenderMenu(0, $userid_actor, $parentmenuid);
  
          $this->userid_actor = $userid_actor;
          $this->parentmenuid = $parentmenuid;
          
          $this->bread_crumb_list = '
            <li>
              Data Master
            </li>
            <li>
              <span>></span>
            </li>
            <li>
              Produk
            </li>';
          
          $this->layout = 'layout-baru';
          $TheContent = $this->renderPartial(
            'v_list_produk_lensa',
            array(
              'userid_actor' => $userid_actor,
              'products' => $products,
              'daftar_fieldcari' => $daftar_fieldcari,
              'fieldcari' => 'nama',
              'tipe_produk' => 'lensa',
              'TipeProduk_Parameter' => 'lensa',
              'TipeProduk_Judul' => 'Lensa',
              'menuid' => $menuid,
              'pageno' => $pageno,
              'pagecount' => $pagecount
            ),
            true
          );
  
          $this->render(
            'index_datamaster_produk',
            array(
              'TheMenu' => $TheMenu,
              'TheContent' => $TheContent,
              'userid_actor' => $userid_actor
            )
          );
        }
        else
        {
          $this->actionShowInvalidAccess($userid_actor, false);
        }
	    }
	    
	    
	    public function actionProdukSetHargaSerempak()
	    {
	      $item_list = Yii::app()->request->getParam('selected_item_list');
	      $stock_minimum = Yii::app()->request->getParam('stock_minimum');
	      $harga_minimum = Yii::app()->request->getParam('harga_minimum');
	      $harga_pp = Yii::app()->request->getParam('harga_pp');
	      $harga_jual = Yii::app()->request->getParam('harga_jual');
	      $harga_diskon = Yii::app()->request->getParam('harga_diskon');
	      $tipe_produk = Yii::app()->request->getParam('tipe_produk');
	      
	      //ambil daftar toko
        $command = Yii::app()->db->createCommand()
          ->select('*')
          ->from('mtr_branch');
        $command->order = 'name asc';
        $daftar_lokasi = $command->queryAll(); 
	      
	      foreach($item_list as $key => $idinventory)
        {
          foreach($daftar_lokasi as $lokasi)
          {
            //update inv_harga_jual
              Yii::app()->db->createCommand()
                ->update(
                  'inv_harga_jual',
                  array(
                    'harga_jual' => $harga_jual,
                    'diskon' => $harga_diskon,
                  ),
                  'id_toko = :idlokasi AND
                  id_item = :idinventory',
                  array(
                    'idlokasi' => $lokasi['branch_id'],
                    'idinventory' => $idinventory,
                  )
                );
            //update inv_harga_jual
            
            
            //update inv_type_[tipe_produk]
            
              $tipe_produk = FHelper::GetTipeProdukId($tipe_produk);
              switch($tipe_produk)
              {
                case 1: //lensa
                  $nama_tabel = 'inv_type_lens';
                  break;
                  
                case 2: //frame
                  $nama_tabel = 'inv_type_frame';
                  break;
                  
                case 3: //softlens
                  $nama_tabel = 'inv_type_softlens';
                  break;
                  
                case 4: //solution
                  $nama_tabel = 'inv_type_solution';
                  break;
                  
                case 5: //accessories
                  $nama_tabel = 'inv_type_accessories';
                  break;
                  
                case 6: //services
                  $nama_tabel = 'inv_type_service';
                  break;
                  
                case 7: //other
                  $nama_tabel = 'inv_type_other';
                  break;
                  
                case 8: //supplies
                  $nama_tabel = 'inv_type_supplies';
                  break;
                  
                case 9: //paket
                  $nama_tabel = 'inv_type_paket';
                  break;
              }
            //update inv_type_[tipe_produk]
            
            
            Yii::app()->db->createCommand()
              ->update(
                $nama_tabel,
                array(
                  'harga_minimum' => $harga_minimum,
                  'harga_pp' => $harga_pp
                ),
                'id_item = :idinventory',
                array(
                  ':idinventory' => $idinventory
                )
              );
              
            //reset stock minimum
              Yii::app()->db->createCommand()
                ->delete(
                  'inv_min_stock',
                  'idinventory = :idinventory AND
                  idlokasi = :idlokasi',
                  array(
                    ':idinventory' => $idinventory,
                    ':idlokasi' => $lokasi['branch_id'],
                  )
                );
                
              Yii::app()->db->createCommand()
                ->insert(
                  'inv_min_stock',
                  array(
                    'idinventory' => $idinventory,
                    'idlokasi' => $lokasi['branch_id'],
                    'minimum_stock' => $stock_minimum
                  )
                );
            //reset stock minimum  
              
          }//loop lokasi
        }//loop item
        
        echo CJSON::encode(array('html' => ''));
	    }

	    /*
	      actionListProduct

	      Deskripsi
	      Action untuk menampilkan daftar produk

	      Parameter
	      tipe_produk
	        lensa, frame, softlens, solution, other, supplies

	    */
	    public function actionListProdukTemp()
	    {
	      $menuid = 11;
	      $parentmenuid = 6;
 	      $userid_actor = Yii::app()->request->getParam('userid_actor');
	      $tipe_produk = Yii::app()->request->getParam('tipe_produk');
	      $TipeProdukJudul = $this->GetTipeProdukJudul($tipe_produk);
	      $TipeProdukParameter = $tipe_produk;
	      $idgroup = FHelper::GetGroupId($userid_actor);
        
        if(FHelper::AllowMenu($menuid, $idgroup, 'read'))
        {
          Yii::log('tipe_produk = ' . $tipe_produk, 'info');
          Yii::log('kategori_produk = ' . $this->GetKategoriProduk($tipe_produk), 'info');
          
          $Criteria = new CDbCriteria();
          $Criteria->condition = 'is_del = 0 AND idkategori = ' . $this->GetKategoriProduk($tipe_produk);
          $products = inv_inventory::model()->findAll($Criteria);
  
          if($tipe_produk == 'paket')
          {
            $Criteria = new CDbCriteria();
            $Criteria->condition = 'is_del = 0';
            $products = inv_type_paket::model()->findAll($Criteria);
          }
  
          $this->layout = 'setting';
          $view_name = $this->GetListViewName($tipe_produk);
  
          $bread_crumb_list =
            '<li>Data Master</li>' .
  
            '<li>'.
              '<span> > </span>'.
              '<a href="#" onclick="ShowProdukList('.$userid_actor.', \''.$TipeProdukParameter.'\');">Produk</a>'.
            '</li>'.
  
            '<li>'.
              '<span> > </span>'.
              'Daftar '. $TipeProdukJudul .
            '</li>';
  
  
          //render product list
          switch($tipe_produk)
          {
            case 'lensa' :
            case 'frame' :
            case 'softlens' :
            case 'paket' :
              $html = $this->renderPartial(
                $view_name,
                array(
                  'userid_actor' => $userid_actor,
                  'products' => $products,
                  'tipe_produk' => $tipe_produk,
                  'TipeProduk_Parameter' => $tipe_produk,
                  'TipeProduk_Judul' => $TipeProdukJudul,
                  'menuid' => $menuid
                ),
                true
              );
              break;
  
            case 'solution' :
            case 'accessories' :
            case 'services' :
            case 'other' :
            case 'supplies' :
              $html = $this->renderPartial(
                $view_name,
                array(
                  'userid_actor' => $userid_actor,
                  'products' => $products,
                  'tipe_produk' => $tipe_produk,
                  'TipeProduk_Parameter' => $tipe_produk,
                  'TipeProduk_Judul' => $TipeProdukJudul,
                  'menuid' => $menuid
                ),
                true
              );
              break;
          }
  
          echo CJSON::encode(
            array(
              'html' => $html,
              'bread_crumb_list' => $bread_crumb_list
            )
          );
        }
        else
        {
          $this->actionShowInvalidAccess($userid_actor);
        }

	      
	    }
	    
	    
	    private function PencarianDetilProduk($tipe_produk, $fieldcari, $cari)
	    {
	      Yii::log('analisa : tipe_produk = ' . $tipe_produk, 'info');
	      Yii::log('analisa : fieldcari = ' . $fieldcari, 'info');
	      Yii::log('analisa : cari = ' . $cari, 'info');
	      
	      $Criteria = new CDbCriteria();
	      $kondisi = '';
	      
	      if($cari != '')
	      {
	        
	        switch($tipe_produk)
          {
            case 'lensa':
              switch($fieldcari)
              {
                case 'nama' :
                  $kondisi = '
                    nama like :nilai';
                    
                  $Criteria->condition = $kondisi;
                  $Criteria->params = array(':nilai' => '%' .$cari . '%');
                  break;
                case 'brand' :
                  $kondisi = '
                    brand like :nilai';
                    
                  $Criteria->condition = $kondisi;
                  $Criteria->params = array(':nilai' => '%' .$cari . '%');
                  break;
                case 'material':
                  $kondisi = '
                    material like :nilai';
                    
                  $Criteria->condition = $kondisi;
                  $Criteria->params = array(':nilai' => '%' .$cari . '%');
                  break;
                case 'ukuran':
                  $kondisi = '
                    base_curve = :nilai
                    or
                    sph_min = :nilai
                    or
                    cyl_min = :nilai
                    or
                    add_1 = :nilai
                  ';
                  
                  $Criteria->condition = $kondisi;
                  $Criteria->params = array(':nilai' => $cari);
                  break;
              }
              
              //$daftar_spesifik = inv_type_lens::model()->findAll($Criteria);
              
              $namatabel = 'inv_type_lens';
              break;
            case 'frame':
              switch($fieldcari)
              {
                case 'nama' :
                  $kondisi = '
                    nama like :nilai';
                    
                  $Criteria->condition = $kondisi;
                  $Criteria->params = array(':nilai' => '%' .$cari . '%');
                  break;
                case 'brand' :
                  $kondisi = '
                    brand like :nilai';
                    
                  $Criteria->condition = $kondisi;
                  $Criteria->params = array(':nilai' => '%' .$cari . '%');
                  break;
                case 'tipe':
                  $kondisi = '
                    nama_tipe like :nilai
                  ';
                  
                  $Criteria->condition = $kondisi;
                  $Criteria->params = array(':nilai' => '%' .$cari . '%');
                  break;
                case 'material':
                  $kondisi = '
                    material like :nilai
                  ';
                  
                  $Criteria->condition = $kondisi;
                  $Criteria->params = array(':nilai' => '%' .$cari . '%');
                  break;
                case 'warna':
                  $kondisi = '
                    color like :nilai
                  ';
                  
                  $Criteria->condition = $kondisi;
                  $Criteria->params = array(':nilai' => '%' .$cari . '%');
                  break;
                case 'ukuran':
                  $kondisi = '
                    dbl = :nilai
                    or
                    eye_size = :nilai
                    or
                    temple = :nilai
                  ';
                  
                  $Criteria->condition = $kondisi;
                  $Criteria->params = array(':nilai' => $cari);
                  break;
              }
              
              //$daftar_spesifik = inv_type_frame::model()->findAll($Criteria);
              
              $namatabel = 'inv_type_frame';
              break;
            case 'softlens':
              switch($fieldcari)
              {
                case 'nama' :
                  $kondisi = '
                    nama like :nilai';
                    
                  $Criteria->condition = $kondisi;
                  $Criteria->params = array(':nilai' => '%' .$cari . '%');
                  break;
                case 'brand' :
                  $kondisi = '
                    brand like :nilai';
                    
                  $Criteria->condition = $kondisi;
                  $Criteria->params = array(':nilai' => '%' .$cari . '%');
                  break;
                case 'material':
                  $kondisi = '
                    material like :nilai';
                    
                  $Criteria->condition = $kondisi;
                  $Criteria->params = array(':nilai' => '%' .$cari . '%');
                  break;
                case 'ukuran':
                  $kondisi = '
                    base_curve = :nilai
                    or
                    sph_min = :nilai
                    or
                    cyl_min = :nilai
                    or
                    add_1 = :nilai
                  ';
                  
                  $Criteria->condition = $kondisi;
                  $Criteria->params = array(':nilai' => $cari);
                  break;
              }
              
              //$daftar_spesifik = inv_type_softlens::model()->findAll($Criteria);
              
              $namatabel = 'inv_type_softlens';
              break;
            default:
              switch($fieldcari)
              {
                case 'nama' :
                  $kondisi = '
                    nama like :nilai';
                    
                  $Criteria->condition = $kondisi;
                  $Criteria->params = array(':nilai' => '%' .$cari . '%');
                  break;
                case 'brand' :
                  $kondisi = '
                    brand like :nilai';
                    
                  $Criteria->condition = $kondisi;
                  $Criteria->params = array(':nilai' => '%' .$cari . '%');
                  break;
                case 'tipe':
                  $kondisi = '
                    nama_tipe like :nilai
                  ';
                  
                  $Criteria->condition = $kondisi;
                  $Criteria->params = array(':nilai' => '%' .$cari . '%');
                  break;
                
              }
              
              switch($tipe_produk)
              {
                case 'solution':
                  //$daftar_spesifik = inv_type_solution::model()->findAll($Criteria);
                  
                  $namatabel = 'inv_type_solution';
                  break;
                case 'services':
                  //$daftar_spesifik = inv_type_services::model()->findAll($Criteria);
                  
                  $namatabel = 'inv_type_services';
                  break;
                case 'accessories':
                  //$daftar_spesifik = accessories::model()->findAll($Criteria);
                  
                  $namatabel = 'inv_type_accessories';
                  break;
                case 'other':
                  //$daftar_spesifik = inv_type_other::model()->findAll($Criteria);
                  
                  $namatabel = 'inv_type_other';
                  break;
                case 'supplies':
                  //$daftar_spesifik = inv_type_supplies::model()->findAll($Criteria);
                  
                  $namatabel = 'inv_type_supplies';
                  break;
              }
                
              break;
          } //switch membuat criteria berdasarkan tipe produk dan field pencarian
	        
	        
	      }
	      
	      
        
        //loop untuk membuat daftar products (array of inv_inventory) berdasarkan
        //id_inventory dari $daftar_spesifik
        
        
        
        $hasil['namatabel'] = $namatabel;
        $hasil['criteria'] = $Criteria;
        $hasil['kondisi'] = $kondisi;
        
        Yii::log('analisa : hasil = ' . print_r($hasil, true), 'info');
        
        return $hasil;
	    }
	    
	    public function GetSorting($tipe_produk, $sortby, $asc)
	    {
	      switch($tipe_produk)
	      {
          case 'lensa' :
            switch($sortby)
            {
              case 'nama':
                $hasil = 'inv_inventory.nama ';
                $hasil_alias = 't.nama ';
                $nama_tabel = 'inv_inventory';
                break;
              case 'brand':
                $hasil = 'inv_inventory.brand ';
                $hasil_alias = 'inv_inventory.brand ';
                $nama_tabel = 'inv_inventory';
                break;
              case 'material':
                $hasil = 'inv_type_lens.material ';
                $hasil_alias = 't.material ';
                $nama_tabel = 'inv_type_lens';
                break;
              case 'ukuran':
                $hasil = 'inv_type_lens.sph_min ';
                $hasil_alias = 't.sph_min ';
                $nama_tabel = 'inv_type_lens';
                break;
              case 'harga':
                $hasil = 'inv_type_lens.harga_minimum ';
                $hasil_alias = 't.harga_minimum ';
                $nama_tabel = 'inv_type_lens';
                break;
              default:
                $hasil = 'inv_inventory.nama ';
                $hasil_alias = 't.nama ';
                $nama_tabel = 'inv_inventory';
                break;
            }
            
            break;
          case 'frame' :
            
            switch($sortby)
            {
              case 'nama':
                $hasil = 'inv_inventory.nama ';
                $hasil_alias = 't.nama ';
                $nama_tabel = 'inv_inventory';
                break;
              case 'brand':
                $hasil = 'inv_inventory.brand ';
                $hasil_alias = 't.brand ';
                $nama_tabel = 'inv_inventory';
                break;
              case 'tipe' :
                $hasil = 'inv_type_frame.nama_tipe ';
                $hasil_alias = 't.nama_tipe ';
                $nama_tabel = 'inv_type_frame';
                break;
              case 'warna' :
                $hasil = 'inv_type_frame.color ';
                $hasil_alias = 't.color ';
                $nama_tabel = 'inv_type_frame';
                break;
              case 'material':
                $hasil = 'inv_type_frame.material ';
                $hasil_alias = 't.material ';
                $nama_tabel = 'inv_type_frame';
                break;
              case 'ukuran':
                $hasil = 'inv_type_frame.dbl ';
                $hasil_alias = 't.dbl ';
                $nama_tabel = 'inv_type_frame';
                break;
              case 'harga':
                $hasil = 'inv_type_frame.harga_minimum ';
                $hasil_alias = 't.harga_minimum ';
                $nama_tabel = 'inv_type_frame';
                break;
              default:
                $hasil = 'inv_inventory.nama ';
                $hasil_alias = 't.nama ';
                $nama_tabel = 'inv_inventory';
                break;
            }
            
            break;
          case 'softlens' :
            
            switch($sortby)
            {
              case 'nama':
                $hasil = 'inv_inventory.nama ';
                $hasil_alias = 't.nama ';
                $nama_tabel = 'inv_inventory';
                break;
              case 'brand':
                $hasil = 'inv_inventory.brand ';
                $hasil_alias = 't.brand ';
                $nama_tabel = 'inv_inventory';
                break;
              case 'material':
                $hasil = 'inv_type_softlens.material ';
                $hasil_alias = 't.material ';
                $nama_tabel = 'inv_type_softlens';
                break;
              case 'ukuran':
                $hasil = 'inv_type_softlens.sph_min ';
                $hasil_alias = 't.sph_min ';
                $nama_tabel = 'inv_type_softlens';
                break;
              default:
                $hasil = 'inv_inventory.nama ';
                $hasil_alias = 't.nama ';
                $nama_tabel = 'inv_inventory';
                break;
            }
            
            break;
          default: //produk2 general
            switch($sortby)
            {
              case 'nama':
                $hasil = 'inv_inventory.nama ';
                $hasil_alias = 't.nama ';
                $nama_tabel = 'inv_inventory';
                break;
              case 'brand':
                $hasil = 'inv_inventory.brand ';
                $hasil_alias = 't.brand ';
                $nama_tabel = 'inv_inventory';
                break;
              default:
                $hasil = 'inv_inventory.nama ';
                $hasil_alias = 't.nama ';
                $nama_tabel = 'inv_inventory';
                break;
            }
            break;
	      }
	      
	      $hasil .= ($asc == 'asc' ? 'asc' : 'desc');
	      $hasil_alias .= ($asc == 'asc' ? 'asc' : 'desc');
	      
	      Yii::log('analisa : sorting : tipeproduk/sortby/asc = ' . $tipe_produk . '/' . $sortby . '/' . $asc, 'info');
	      Yii::log('analisa : sorting : hasil = ' . $hasil, 'info');
	      
	      $sorting['order'] = $hasil;
	      $sorting['order_alias'] = $hasil_alias;
	      $sorting['nama_tabel'] = $nama_tabel;
	      
	      return $sorting;
	    }
	    
	    public function GetCommand($tipe_produk)
	    {
	      switch($tipe_produk)
	      {
          case 'lensa':
            $command = Yii::app()->db->createCommand()
              ->select('id')
              ->from('inv_inventory')
              ->join('inv_type_lens', 'inv_inventory.id = inv_type_lens.id_item');
            break;
          case 'frame':
            $command = Yii::app()->db->createCommand()
              ->select('id')
              ->from('inv_inventory')
              ->join('inv_type_frame', 'inv_inventory.id = inv_type_frame.id_item');
            break;
          case 'softlens':
            $command = Yii::app()->db->createCommand()
              ->select('id')
              ->from('inv_inventory')
              ->join('inv_type_softlens', 'inv_inventory.id = inv_type_softlens.id_item');
            break;
          case 'solution':
            $command = Yii::app()->db->createCommand()
              ->select('id')
              ->from('inv_inventory')
              ->join('inv_type_solution', 'inv_inventory.id = inv_type_solution.id_item');
            break;
          case 'services':
            $command = Yii::app()->db->createCommand()
              ->select('id')
              ->from('inv_inventory')
              ->join('inv_type_services', 'inv_inventory.id = inv_type_services.id_item');
            break;
          case 'accessories':
            $command = Yii::app()->db->createCommand()
              ->select('id')
              ->from('inv_inventory')
              ->join('inv_type_accessories', 'inv_inventory.id = inv_type_accessories.id_item');
            break;
          case 'other':
            $command = Yii::app()->db->createCommand()
              ->select('id')
              ->from('inv_inventory')
              ->join('inv_type_other', 'inv_inventory.id = inv_type_other.id_item');
            break;
          case 'supplies':
            $command = Yii::app()->db->createCommand()
              ->select('id')
              ->from('inv_inventory')
              ->join('inv_type_supplies', 'inv_inventory.id = inv_type_supplies.id_item');
            break;
          case 'paket':
            $command = Yii::app()->db->createCommand()
              ->select('id')
              ->from('inv_inventory')
              ->join('inv_type_paket', 'inv_inventory.id = inv_type_paket.id_item');
            break;
	      }
	      
	      return $command;
	    }
	    
	    /*
	      actionListProduct

	      Deskripsi
	      Action untuk menampilkan daftar produk

	      Parameter
	      tipe_produk
	        lensa, frame, softlens, solution, other, supplies

	    */
	    public function actionListProduk()
	    {
	      $menuid = 11;
	      $parentmenuid = 6;
 	      $userid_actor = Yii::app()->request->cookies['userid_actor']->value;
	      $tipe_produk = Yii::app()->request->getParam('tipe_produk');
	      $TipeProdukJudul = $this->GetTipeProdukJudul($tipe_produk);
	      $TipeProdukParameter = $tipe_produk;
	      $idgroup = FHelper::GetGroupId($userid_actor);
	      $rows_per_page = 20;
	      $sortby = Yii::app()->request->getParam('sortby');
	      $asc = Yii::app()->request->getParam('asc');
	      
	      if(isset($sortby) == false || $sortby == '')
	      {
	        $sortby = 'nama';
	      }
	      
	      if(isset($asc) == false)
	      {
	        $asc = 'asc';
	      }
        
        if(FHelper::AllowMenu($menuid, $idgroup, 'read'))
        {
          $pageno = Yii::app()->request->getParam('pageno');
          $pageno = Defense::Sanitize2($pageno);
          if(!isset($pageno))
          {
            $pageno = 0;
          }
          
          $cari = Yii::app()->request->getParam('cari');
          Yii::log("cari = $cari", 'info');
          $fieldcari = Yii::app()->request->getParam('fieldcari');
          $cari = Defense::Sanitize2($cari);
          
          //hitung pagecount
          $Criteria = new CDbCriteria();
          
          $sorting = $this->GetSorting($tipe_produk, $sortby, $asc);
          $hasil_pencarian = $this->PencarianDetilProduk($tipe_produk, $fieldcari, $cari);
          
          if($hasil_pencarian['kondisi'] == '')
          {
            //$command->where('is_del = 0 ', array());
            $where_condition = 'is_del = 0';
            $where_params = array();
          }
          else
          {
            switch($fieldcari)
            {
              case 'nama':
              case 'brand':
              case 'tipe':
              case 'material':
              case 'warna' :
                $real_cari = '%' . $cari . '%';
                break;
              default:
                $real_cari = $cari;
                break;
            }
            //$command->where('is_del = 0 ' . ' and ' . '(' .$hasil_pencarian['kondisi'] . ')', array(':nilai' => $real_cari));
            $where_condition = 'is_del = 0 ' . ' and ' . '(' .$hasil_pencarian['kondisi'] . ')';
            $where_params = array(':nilai' => $real_cari);
          }
          
          //count records count
          $command = $this->GetCommand($tipe_produk);
          $command->where($where_condition, $where_params);
          $daftar_spesifik = $command->queryAll();
          
          $records = count($daftar_spesifik);
          $pagecount = (int)($records / $rows_per_page);
          if(($records % $rows_per_page) > 0)
            $pagecount++;
                  
          //re-query with offset and limit
          $command = $this->GetCommand($tipe_produk);
          $command->order($sorting['order']);
          $command->where($where_condition, $where_params);
          $command->offset($pageno * $rows_per_page);
          $command->limit($rows_per_page);
          $daftar_spesifik = $command->queryAll();
          
          //konversi array menjadi activerecord. Berguna untuk penayangan daftar produk
          foreach($daftar_spesifik as $spesifik)
          {
            $produk = inv_inventory::model()->findByPk($spesifik['id']);
            
            $products[] = $produk;
          }
  
          $this->layout = 'setting';
          $view_name = $this->GetListViewName($tipe_produk);
          $daftar_fieldcari = $this->GetListFieldCari($tipe_produk);
  
          $bread_crumb_list =
            '<li>Data Master</li>' .
  
            '<li>'.
              '<span> > </span>'.
              '<a href="#" onclick="ShowProdukList('.$userid_actor.', \''.$TipeProdukParameter.'\');">Produk</a>'.
            '</li>'.
  
            '<li>'.
              '<span> > </span>'.
              'Daftar '. $TipeProdukJudul .
            '</li>';
  
  
          //render product list
          switch($tipe_produk)
          {
            case 'lensa' :
            case 'frame' :
            case 'softlens' :
            case 'paket' :
              $html = $this->renderPartial(
                $view_name,
                array(
                  'userid_actor' => $userid_actor,
                  'products' => $products,
                  'tipe_produk' => $tipe_produk,
                  'daftar_fieldcari' => $daftar_fieldcari,
                  'fieldcari' => $fieldcari,
                  'TipeProduk_Parameter' => $tipe_produk,
                  'TipeProduk_Judul' => $TipeProdukJudul,
                  'menuid' => $menuid,
                  'pageno' => $pageno,
                  'pagecount' => $pagecount,
                  'cari' => $cari,
                  'asc' => $asc,
                  'sortby' => $sortby
                ),
                true
              );
              break;
  
            case 'solution' :
            case 'accessories' :
            case 'services' :
            case 'other' :
            case 'supplies' :
              $html = $this->renderPartial(
                $view_name,
                array(
                  'userid_actor' => $userid_actor,
                  'products' => $products,
                  'tipe_produk' => $tipe_produk,
                  'daftar_fieldcari' => $daftar_fieldcari,
                  'fieldcari' => $fieldcari,
                  'TipeProduk_Parameter' => $tipe_produk,
                  'TipeProduk_Judul' => $TipeProdukJudul,
                  'menuid' => $menuid,
                  'pagecount' => $pagecount,
                  'cari' => $cari,
                  'asc' => $asc,
                  'sortby' => $sortby
                ),
                true
              );
              break;
          }
  
          echo CJSON::encode(
            array(
              'html' => $html,
              'bread_crumb_list' => $bread_crumb_list
            )
          );
        }
        else
        {
          $this->actionShowInvalidAccess($userid_actor);
        }

	      
	    }
	    
	    



	    //master data - add products - begin

	      /*
          actionAddProduct

          Deskripsi
          Action untuk menampilkan form penambahan produk dan mengolah form
          submission.
        */
        public function actionAddProduk()
        {
          $menuid = 11;
          $parentmenuid = 6;
          $userid_actor = Yii::app()->request->getParam('userid_actor');
          $tipe_produk = Yii::app()->request->getParam('tipe_produk');
          $do_add = Yii::app()->request->getParam('do_add');
          $idgroup = FHelper::GetGroupId($userid_actor);
        
          if(FHelper::AllowMenu($menuid, $idgroup, 'write'))
          {
            $TipeProdukJudul = $this->GetTipeProdukJudul($tipe_produk);
            $TipeProdukParameter = $tipe_produk;
            $kategori_produk = $this->GetKategoriProduk($tipe_produk);
            $form_name = $this->GetProdukFormName($tipe_produk);
            $success_add_view_name = $this->GetSuccessAddViewName($tipe_produk);
            $add_form_view_name = $this->GetAddFormViewName($tipe_produk);
            $list_view_name = $this->GetListViewName($tipe_produk);
            $form = $this->GetAddProdukForm($tipe_produk);
  
            $form['id_kategori'] = $kategori_produk;
  
            $daftar_toko = FHelper::GetLocationListData();
            $lens_type_list = FHelper::GetLensType();
            $softlens_type_list = FHelper::GetSoftlensType();
            $frame_type_list = FHelper::GetFrameType();
            $produsen_list = FHelper::GetVendor();
            $lens_material_list = FHelper::GetLensMaterial();
            $softlens_material_list = FHelper::GetSoftlensMaterial();
            $production_type_list = FHelper::GetProductionType();
            $wearing_type_list = FHelper::GetWearingType();
            $change_type_list = FHelper::GetChangeType();
            $availability_type_list = FHelper::GetAvailabilityType();
  
            if(isset($do_add))
            {
              if($do_add == 1)
              {
                //proses form submission
  
                //ambil data harga jual
                $form->attributes = Yii::app()->request->getParam($form_name);
                $form['hargajual_toko'] = Yii::app()->request->getParam('hargajual_toko');
                $form['hargajual_harga'] = Yii::app()->request->getParam('hargajual_harga');
                $form['hargajual_minimum_stock'] = Yii::app()->request->getParam('hargajual_minimum_stock');
                $form['hargajual_diskon'] = Yii::app()->request->getParam('hargajual_diskon');
                
                $form['hargajual_harga'] = preg_replace('/[^0-9]/', '', $form['hargajual_harga']);
                $form['hargajual_diskon'] = preg_replace('/[^0-9]/', '', $form['hargajual_diskon']);
  
                if($tipe_produk == 'paket')
                {
                  $form['item_paket'] = Yii::app()->request->getParam('item_paket');
                }
  
                if($form->validate())
                {
                  //simpan record ke tabel
                  $inv_id = $this->SimpanInventory($form, $tipe_produk);
                  
                  //pastikan produk berhasil masuk tabel
                  if($inv_id != -1)
                  {
                    $duplikat = 0;
                    
                    //masukkan daftar harga jual
  
                      foreach($form['hargajual_toko'] as $key => $value)
                      {
                        if($tipe_produk == 'paket')
                        {
                          $Criteria = new CDbCriteria();
                          $Criteria->condition = 'id_toko = :id_toko AND id_item = :id_paket';
                          $Criteria->params =
                            array
                            (
                              ':id_paket' => $inv_id,
                              ':id_toko' => $form['hargajual_toko'][$key]
                            );
    
                          $jumlah = inv_harga_jual::model()->count($Criteria);
                          if($jumlah == 1)
                          {
                            $inv_harga_jual = inv_harga_jual::model()->find($Criteria);
                                           
                            $inv_harga_jual['id_item'] = $inv_id;
                            $inv_harga_jual['id_toko'] = $form['hargajual_toko'][$key];
                            $inv_harga_jual['harga_jual'] = $form['hargajual_harga'][$key];
                            //$inv_harga_jual['diskon'] = $form['hargajual_diskon'][$key];
                          }
                          else
                          {
                            $inv_harga_jual = new inv_harga_jual();
                                           
                            $inv_harga_jual['id_item'] = $inv_id;
                            $inv_harga_jual['id_toko'] = $form['hargajual_toko'][$key];
                            $inv_harga_jual['harga_jual'] = $form['hargajual_harga'][$key];
                            //$inv_harga_jual_paket['diskon'] = $form['hargajual_diskon'][$key];
                          }
    
                          $inv_harga_jual->save();
                        }
                        else
                        {
                          //simpan harga jual - begin
                            $Criteria = new CDbCriteria();
                            $Criteria->condition = 'id_toko = :id_toko AND id_item = :id_item';
                            $Criteria->params =
                              array
                              (
                                ':id_item' => $inv_id,
                                ':id_toko' => $form['hargajual_toko'][$key]
                              );
      
                            $jumlah = inv_harga_jual::model()->count($Criteria);
                            if($jumlah == 1)
                            {
                              $inv_harga_jual = inv_harga_jual::model()->find($Criteria);
      
                              $inv_harga_jual['id_item'] = $inv_id;
                              $inv_harga_jual['id_toko'] = $form['hargajual_toko'][$key];
                              $inv_harga_jual['harga_jual'] = $form['hargajual_harga'][$key];
                              $inv_harga_jual['diskon'] = $form['hargajual_diskon'][$key];
                            }
                            else
                            {
                              $inv_harga_jual = new inv_harga_jual();
      
                              $inv_harga_jual['id_item'] = $inv_id;
                              $inv_harga_jual['id_toko'] = $form['hargajual_toko'][$key];
                              $inv_harga_jual['harga_jual'] = $form['hargajual_harga'][$key];
                              $inv_harga_jual['diskon'] = $form['hargajual_diskon'][$key];
                            }
      
                            $inv_harga_jual->save();
                          //simpan harga jual - end
                          
                          //simpan minimum stock - begin
                            $Criteria = new CDbCriteria();
                            $Criteria->condition = 'idlokasi = :idlokasi AND idinventory = :idinventory';
                            $Criteria->params =
                              array
                              (
                                ':idinventory' => $inv_id,
                                ':idlokasi' => $form['hargajual_toko'][$key]
                              );
      
                            $jumlah = inv_min_stock::model()->count($Criteria);
                            if($jumlah == 1)
                            {
                              $min_stock = inv_min_stock::model()->find($Criteria);
                              
                              $min_stock['idinventory'] = $inv_id;
                              $min_stock['idlokasi'] = $form['hargajual_toko'][$key];
                              $min_stock['minimum_stock'] = $form['hargajual_minimum_stock'][$key];
                            }
                            else
                            {
                              $min_stock = new inv_min_stock();
      
                              $min_stock['idinventory'] = $inv_id;
                              $min_stock['idlokasi'] = $form['hargajual_toko'][$key];
                              $min_stock['minimum_stock'] = $form['hargajual_minimum_stock'][$key];
                            }
      
                            $min_stock->save();
                          //simpan minimum stock - end
                        }
    
                      }
    
                    //tampilkan informasi sukses menambahkan record produk
                    
                    $bread_crumb_list =
                      '<li>Data Master</li>' .
            
                      '<li>'.
                        '<span> > </span>'.
                        '<a href="#" onclick="ShowProdukList('.$userid_actor.', \''.$TipeProdukParameter.'\');">Produk</a>'.
                      '</li>'.
            
                      '<li>'.
                        '<span> > </span>'.
                        'Tambah '. $TipeProdukJudul .
                      '</li>';
                    
                    $form['id_produk'] = $inv_id;
                    
                    if($tipe_produk == 'paket')
                    {
                      $html = $this->renderPartial(
                        $success_add_view_name,
                        array(
                          'form' => $form,
                          'userid_actor' => $userid_actor,
      
                          'lens_type_list' => $lens_type_list,
                          'softlens_type_list' => $softlens_type_list,
                          'frame_type_list' => $frame_type_list,
                          'produsen_list' => $produsen_list,
                          'lens_material_list' => $lens_material_list,
                          'softlens_material_list' => $softlens_material_list,
                          'production_type_list' => $production_type_list,
                          'availability_type_list' => $availability_type_list,
                          'wearing_type_list' => $wearing_type_list,
                          'change_type_list' => $change_type_list,
                          'daftar_toko' => $daftar_toko,
                          'id_kategori' => $kategori_produk,
                          'rows_item_paket' => $form['item_paket'],
                          'rows_item_jumlah' => Yii::app()->request->getParam('item_paket_jumlah'),
                          'hargajual_toko' => $form['hargajual_toko'],
                          'hargajual_harga' => $form['hargajual_harga'],
                          'hargajual_diskon' => $form['hargajual_diskon'],
                          'TipeProdukJudul' => $TipeProdukJudul,
                          'TipeProdukParameter' => $TipeProdukParameter,
                        ),
                        true
                      );
                    }
                    else
                    {
                      $html = $this->renderPartial(
                        $success_add_view_name,
                        array(
                          'form' => $form,
                          'userid_actor' => $userid_actor,
      
                          'lens_type_list' => $lens_type_list,
                          'softlens_type_list' => $softlens_type_list,
                          'frame_type_list' => $frame_type_list,
                          'produsen_list' => $produsen_list,
                          'lens_material_list' => $lens_material_list,
                          'softlens_material_list' => $softlens_material_list,
                          'production_type_list' => $production_type_list,
                          'availability_type_list' => $availability_type_list,
                          'wearing_type_list' => $wearing_type_list,
                          'change_type_list' => $change_type_list,
                          'daftar_toko' => $daftar_toko,
                          'id_kategori' => $kategori_produk,
                          'hargajual_toko' => $form['hargajual_toko'],
                          'hargajual_harga' => $form['hargajual_harga'],
                          'hargajual_diskon' => $form['hargajual_diskon'],
                          'TipeProdukJudul' => $TipeProdukJudul,
                          'TipeProdukParameter' => $TipeProdukParameter,
                        ),
                        true
                      );
                    }
                    
                  }
                  else
                  {
                    $duplikat = 1;
                  }
                }
                else
                {
                  $duplikat = 0;
                  
                  //validation failed... tampilkan form
                  
                  if($tipe_produk == 'paket')
                  {
                    $html = $this->renderPartial(
                      $add_form_view_name,
                      array(
                        'form' => $form,
                        'userid_actor' => $userid_actor,
    
                        'lens_type_list' => $lens_type_list,
                        'softlens_type_list' => $softlens_type_list,
                        'frame_type_list' => $frame_type_list,
                        'produsen_list' => $produsen_list,
                        'lens_material_list' => $lens_material_list,
                        'softlens_material_list' => $softlens_material_list,
                        'production_type_list' => $production_type_list,
                        'availability_type_list' => $availability_type_list,
                        'wearing_type_list' => $wearing_type_list,
                        'change_type_list' => $change_type_list,
                        'daftar_toko' => $daftar_toko,
                        'id_kategori' => $kategori_produk,
                        'hargajual_toko' => $form['hargajual_toko'],
                        'hargajual_harga' => $form['hargajual_harga'],
                        'hargajual_diskon' => $form['hargajual_diskon'],
                        'rows_item_paket' => $form['item_paket'],
                        'TipeProdukJudul' => $TipeProdukJudul,
                        'TipeProdukParameter' => $TipeProdukParameter,
                      ),
                      true
                    );
                  }
                  else
                  {
                    $html = $this->renderPartial(
                      $add_form_view_name,
                      array(
                        'form' => $form,
                        'userid_actor' => $userid_actor,
    
                        'lens_type_list' => $lens_type_list,
                        'softlens_type_list' => $softlens_type_list,
                        'frame_type_list' => $frame_type_list,
                        'produsen_list' => $produsen_list,
                        'lens_material_list' => $lens_material_list,
                        'softlens_material_list' => $softlens_material_list,
                        'production_type_list' => $production_type_list,
                        'availability_type_list' => $availability_type_list,
                        'wearing_type_list' => $wearing_type_list,
                        'change_type_list' => $change_type_list,
                        'daftar_toko' => $daftar_toko,
                        'id_kategori' => $kategori_produk,
                        'hargajual_toko' => $form['hargajual_toko'],
                        'hargajual_harga' => $form['hargajual_harga'],
                        'hargajual_diskon' => $form['hargajual_diskon'],
                        'TipeProdukJudul' => $TipeProdukJudul,
                        'TipeProdukParameter' => $TipeProdukParameter,
                      ),
                      true
                    );
                  }
                  
                }
              }
              else
              {
                $duplikat = 0;
                
                //batal menambah produk.
                //alihkan ke view list produk.
                $Criteria = new CDbCriteria();
                $Criteria->condition = 'is_del = 0 AND idkategori = ' . $kategori_produk;
  
                $userid_actor = Yii::app()->request->getParam('userid_actor');
                $products = inv_inventory::model()->findAll($Criteria);
  
                $html = $this->renderPartial(
                  $list_view_name,
                  array(
                    'userid_actor' => $userid_actor,
                    'products' => $products,
                    'menuid' => $menuid
                  ),
                  true
                );
              }
  
            }
            else
            {
              $duplikat = 0;
              
              //show form add produk
              $bread_crumb_list =
                '<li>Data Master</li>' .
      
                '<li>'.
                  '<span> > </span>'.
                  '<a href="#" onclick="ShowProdukList('.$userid_actor.', \''.$TipeProdukParameter.'\');">Produk</a>'.
                '</li>'.
      
                '<li>'.
                  '<span> > </span>'.
                  'Tambah '. $TipeProdukJudul .
                '</li>';
                
              $html = $this->renderPartial(
                $add_form_view_name,
                array(
                  'form' => $form,
                  'userid_actor' => $userid_actor,
                  'lens_type_list' => $lens_type_list,
                  'softlens_type_list' => $softlens_type_list,
                  'frame_type_list' => $frame_type_list,
                  'produsen_list' => $produsen_list,
                  'lens_material_list' => $lens_material_list,
                  'softlens_material_list' => $softlens_material_list,
                  'production_type_list' => $production_type_list,
                  'availability_type_list' => $availability_type_list,
                  'wearing_type_list' => $wearing_type_list,
                  'change_type_list' => $change_type_list,
                  'daftar_toko' => $daftar_toko,
                  'id_kategori' => $kategori_produk,
                  'TipeProdukJudul' => $TipeProdukJudul,
                  'TipeProdukParameter' => $TipeProdukParameter
                ),
                true
              );
            }
  
            echo CJSON::encode(
              array(
                'html' => $html,
                'bread_crumb_list' => $bread_crumb_list,
                'duplikat' => $duplikat
              )
            );
          }
          else
          {
            $this->actionShowInvalidAccess($userid_actor);
          }
          

          
        }

	    //master data - add products - end


	    //master data - edit products - begin

        /*
          actionEditProduct

          Deskripsi
          Action untuk menampilkan form edit produk dan mengolah form submission.

          Parameter
          tipe_produk
            String yang menyatakan tipe produk (lensa, frame, softlens, ...)
        */
        public function actionEditProduk()
        {
          $menuid = 11;
          $parentmenuid = 6;
          $userid_actor = Yii::app()->request->getParam('userid_actor');
          $idproduk = Yii::app()->request->getParam('idproduk');
          $tipe_produk = Yii::app()->request->getParam('tipe_produk');
          $do_edit = Yii::app()->request->getParam('do_edit');
          $kategori_produk = $this->GetKategoriProduk($tipe_produk);
          $form_name = $this->GetProdukFormName($tipe_produk);
          $success_edit_view_name = $this->GetSuccessEditViewName($tipe_produk);
          $edit_form_view_name = $this->GetEditFormViewName($tipe_produk);
          $list_view_name = $this->GetListViewName($tipe_produk);
          $form = $this->GetAddProdukForm($tipe_produk);
          
          
          $idgroup = FHelper::GetGroupId($userid_actor);
        
          if(FHelper::AllowMenu($menuid, $idgroup, 'edit'))
          {
            $TipeProdukJudul = $this->GetTipeProdukJudul($tipe_produk);
            $TipeProdukParameter = $tipe_produk;
            
            $Criteria = new CDbCriteria();
            $Criteria->condition = 'id_item = :idproduk';
            $Criteria->params = array(':idproduk' => $idproduk);
            $rows_hargajual = inv_harga_jual::model()->findAll($Criteria);
  
            $daftar_toko = FHelper::GetLocationListData();
            $lens_type_list = FHelper::GetLensType();
            $softlens_type_list = FHelper::GetSoftlensType();
            $frame_type_list = FHelper::GetFrameType();
            $produsen_list = FHelper::GetVendor();
            $lens_material_list = FHelper::GetLensMaterial();
            $softlens_material_list = FHelper::GetSoftlensMaterial();
            $production_type_list = FHelper::GetProductionType();
            $availability_type_list = FHelper::GetAvailabilityType();
            $wearing_type_list = FHelper::GetWearingType();
            $change_type_list = FHelper::GetChangeType();
  
            if(isset($do_edit))
            {
              if($do_edit == 1)
              {
                //proses edit form submission
  
                $form->attributes = Yii::app()->request->getParam($form_name);
                $form['hargajual_toko'] = Yii::app()->request->getParam('hargajual_toko');
                $form['hargajual_harga'] = Yii::app()->request->getParam('hargajual_harga');
                $form['hargajual_diskon'] = 
                  (Yii::app()->request->getParam('hargajual_diskon') == null ? 0 : Yii::app()->request->getParam('hargajual_diskon'));
                
                //$form['hargajual_harga'] = $form['hargajual_harga'];
                //$form['hargajual_diskon'] = $form['hargajual_diskon'];
  
                if($tipe_produk == 'paket')
                {
                  $form['item_paket'] = Yii::app()->request->getParam('item_paket');
                  $form['item_paket_harga'] = Yii::app()->request->getParam('item_paket_harga');
                }
  
                if($form->validate())
                {
                  $Criteria = new CDbCriteria();
  
                  $this->UpdateInventory($form, $tipe_produk, $form['id_produk']);
  
                  //simpan daftar harga jual
                    if($tipe_produk == 'paket')
                    {
                      $Criteria = new CDbCriteria();
                      $Criteria->condition = 'id_item = :id_item';
                      $Criteria->params = array(':id_item' => $form['id_produk']);
                      inv_harga_jual::model()->deleteAll($Criteria);
  
                      foreach($form['hargajual_toko'] as $key => $value)
                      {
                        $Criteria->condition = 'id_toko = :id_toko AND id_item = :id_item';
                        $Criteria->params =
                          array
                          (
                            ':id_item' => $form['id_produk'],
                            ':id_toko' => $form['hargajual_toko'][$key]
                          );
  
                        $jumlah = inv_harga_jual::model()->count($Criteria);
                        if($jumlah == 1)
                        {
                          $inv_harga_jual = inv_harga_jual::model()->find($Criteria);
  
                          $inv_harga_jual['id_item'] = $form['id_produk'];
                          $inv_harga_jual['id_toko'] = $form['hargajual_toko'][$key];
                          $inv_harga_jual['harga_jual'] = preg_replace('/\./', '', $form['hargajual_harga'][$key]);
                          $inv_harga_jual['diskon'] = preg_replace('/\./', '', $form['hargajual_diskon'][$key]);
                          $inv_harga_jual->update();
                        }
                        else
                        {
                          $inv_harga_jual = new inv_harga_jual();
  
                          $inv_harga_jual['id_item'] = $form['id_produk'];
                          $inv_harga_jual['id_toko'] = $form['hargajual_toko'][$key];
                          $inv_harga_jual['harga_jual'] = preg_replace('/\./', '', $form['hargajual_harga'][$key]);
                          $inv_harga_jual['diskon'] = preg_replace('/\./', '', $form['hargajual_diskon'][$key]);
                          $inv_harga_jual->save();
                        }
                      }
                      
                      
                      /*
                      $Criteria = new CDbCriteria();
                      $Criteria->condition = 'id_paket = :id_paket';
                      $Criteria->params = array(':id_paket' => $form['id_produk']);
                      inv_harga_jual_paket::model()->deleteAll($Criteria);
  
                      foreach($form['hargajual_toko'] as $key => $value)
                      {
                        $Criteria->condition = 'id_toko = :id_toko AND id_paket = :id_paket';
                        $Criteria->params =
                          array
                          (
                            ':id_paket' => $form['id_produk'],
                            ':id_toko' => $form['hargajual_toko'][$key]
                          );
  
                        $jumlah = inv_harga_jual_paket::model()->count($Criteria);
                        if($jumlah == 1)
                        {
                          $inv_harga_jual_paket = inv_harga_jual_paket::model()->find($Criteria);
  
                          $inv_harga_jual_paket['id_paket'] = $form['id_produk'];
                          $inv_harga_jual_paket['id_toko'] = $form['hargajual_toko'][$key];
                          $inv_harga_jual_paket['harga_jual'] = preg_replace('.', '', $form['hargajual_harga'][$key]);
                          $inv_harga_jual_paket['diskon'] = $form['hargajual_diskon'][$key];
                          $inv_harga_jual_paket->update();
                        }
                        else
                        {
                          $inv_harga_jual_paket = new inv_harga_jual_paket();
  
                          $inv_harga_jual_paket['id_paket'] = $form['id_produk'];
                          $inv_harga_jual_paket['id_toko'] = $form['hargajual_toko'][$key];
                          $inv_harga_jual_paket['harga_jual'] = preg_replace('.', '', $form['hargajual_harga'][$key]);
                          $inv_harga_jual_paket['diskon'] = $form['hargajual_diskon'][$key];
                          $inv_harga_jual_paket->save();
                        }
                      }
                      */
                    }
                    else
                    {
                      //selain tipe_produk == paket
  
                      $Criteria = new CDbCriteria();
                      $Criteria->condition = 'id_item = :id_item';
                      $Criteria->params = array(':id_item' => $form['id_produk']);
                      inv_harga_jual::model()->deleteAll($Criteria);
  
                      foreach($form['hargajual_toko'] as $key => $value)
                      {
                        $Criteria->condition = 'id_toko = :id_toko AND id_item = :id_item';
                        $Criteria->params =
                          array
                          (
                            ':id_item' => $form['id_produk'],
                            ':id_toko' => $form['hargajual_toko'][$key]
                          );
  
                        $jumlah = inv_harga_jual::model()->count($Criteria);
                        if($jumlah == 1)
                        {
                          $inv_harga_jual = inv_harga_jual::model()->find($Criteria);
  
                          $inv_harga_jual['id_item'] = $form['id_produk'];
                          $inv_harga_jual['id_toko'] = $form['hargajual_toko'][$key];
                          $inv_harga_jual['harga_jual'] = preg_replace('/\./', '', $form['hargajual_harga'][$key]);
                          $inv_harga_jual['diskon'] = preg_replace('/\./', '', $form['hargajual_diskon'][$key]);
                          $inv_harga_jual->update();
                        }
                        else
                        {
                          $inv_harga_jual = new inv_harga_jual();
  
                          $inv_harga_jual['id_item'] = $form['id_produk'];
                          $inv_harga_jual['id_toko'] = $form['hargajual_toko'][$key];
                          $inv_harga_jual['harga_jual'] = preg_replace('/\./', '', $form['hargajual_harga'][$key]);
                          $inv_harga_jual['diskon'] = preg_replace('/\./', '', $form['hargajual_diskon'][$key]);
                          $inv_harga_jual->save();
                        }
                      }
                    }
  
  
  
                  //tampilkan informasi sukses mengubah record produk lensa
                  
                  $form = $this->GetInventory($tipe_produk, $form['id_produk']);
                  
                  $Criteria->condition = 'id_item = :idproduk';
                  $Criteria->params = array(':idproduk' => $form['id_produk']);
                  $rows_hargajual = inv_harga_jual::model()->findAll($Criteria);
                  
                  if($tipe_produk == 'paket')
                  {
                    //$Criteria->condition = 'id_paket = :idproduk';
                    //$Criteria->params = array(':idproduk' => $form['id_produk']);
                    //$rows_hargajual = inv_harga_jual_paket::model()->findAll($Criteria);
                  }
                  
                  $bread_crumb_list =
                    '<li>Data Master</li>' .
          
                    '<li>'.
                      '<span> > </span>'.
                      '<a href="#" onclick="ShowProdukList('.$userid_actor.', \''.$TipeProdukParameter.'\');">Produk</a>'.
                    '</li>'.
          
                    '<li>'.
                      '<span> > </span>'.
                      'Edit '. $TipeProdukJudul .
                    '</li>';
                    
                  if($tipe_produk == 'paket')
                  {
                    $html = $this->renderPartial(
                      $success_edit_view_name,
                      array(
                        'form' => $form,
                        'userid_actor' => $userid_actor,
                        'idlensa' => $idlensa,
    
                        'lens_type_list' => $lens_type_list,
                        'softlens_type_list' => $softlens_type_list,
                        'frame_type_list' => $frame_type_list,
                        'produsen_list' => $produsen_list,
                        'lens_material_list' => $lens_material_list,
                        'softlens_material_list' => $softlens_material_list,
                        'production_type_list' => $production_type_list,
                        'availability_type_list' => $availability_type_list,
                        'wearing_type_list' => $wearing_type_list,
                        'change_type_list' => $change_type_list,
                        'daftar_toko' => $daftar_toko,
                        'id_kategori' => $kategori_produk,
                        'rows_item_paket' => $form['item_paket'],
                        'rows_hargajual' => $rows_hargajual,
                        'TipeProdukJudul' => $TipeProdukJudul,
                        'TipeProdukParameter' => $TipeProdukParameter,
                      ),
                      true
                    );
                  }
                  else
                  {
                    $html = $this->renderPartial(
                      $success_edit_view_name,
                      array(
                        'form' => $form,
                        'userid_actor' => $userid_actor,
                        'idlensa' => $idlensa,
    
                        'lens_type_list' => $lens_type_list,
                        'softlens_type_list' => $softlens_type_list,
                        'frame_type_list' => $frame_type_list,
                        'produsen_list' => $produsen_list,
                        'lens_material_list' => $lens_material_list,
                        'softlens_material_list' => $softlens_material_list,
                        'production_type_list' => $production_type_list,
                        'availability_type_list' => $availability_type_list,
                        'wearing_type_list' => $wearing_type_list,
                        'change_type_list' => $change_type_list,
                        'daftar_toko' => $daftar_toko,
                        'id_kategori' => $kategori_produk,
                        'rows_hargajual' => $rows_hargajual,
                        'TipeProdukJudul' => $TipeProdukJudul,
                        'TipeProdukParameter' => $TipeProdukParameter,
                      ),
                      true
                    );
                  }
                  
                }
                else
                {
                  //validation failed... tampilkan form
                  
                  $Criteria->condition = 'id_item = :idproduk';
                  $Criteria->params = array(':idproduk' => $form['id_produk']);
                  $rows_hargajual = inv_harga_jual::model()->findAll($Criteria);
  
                  if($tipe_produk == 'paket')
                  {
                    $Criteria->condition = 'id_paket = :idproduk';
                    $Criteria->params = array(':idproduk' => $idproduk);
                    $rows_hargajual = inv_harga_jual_paket::model()->findAll($Criteria);
                  }
                  
                  $bread_crumb_list =
                    '<li>Data Master</li>' .
          
                    '<li>'.
                      '<span> > </span>'.
                      '<a href="#" onclick="ShowProdukList('.$userid_actor.', \''.$TipeProdukParameter.'\');">Produk</a>'.
                    '</li>'.
          
                    '<li>'.
                      '<span> > </span>'.
                      'Edit '. $TipeProdukJudul .
                    '</li>';
  
                  $html = $this->renderPartial(
                    $edit_form_view_name,
                    array(
                      'form' => $form,
                      'userid_actor' => $userid_actor,
                      'idlensa' => $idlensa,
  
                      'lens_type_list' => $lens_type_list,
                      'softlens_type_list' => $softlens_type_list,
                      'frame_type_list' => $frame_type_list,
                      'produsen_list' => $produsen_list,
                      'lens_material_list' => $lens_material_list,
                      'softlens_material_list' => $softlens_material_list,
                      'production_type_list' => $production_type_list,
                      'availability_type_list' => $availability_type_list,
                      'wearing_type_list' => $wearing_type_list,
                      'change_type_list' => $change_type_list,
                      'daftar_toko' => $daftar_toko,
                      'id_kategori' => $kategori_produk,
                      'rows_hargajual' => $rows_hargajual,
                      'TipeProdukJudul' => $TipeProdukJudul,
                      'TipeProdukParameter' => $TipeProdukParameter,
                    ),
                    true
                  );
                }
              }
              else
              {
                //batal edit
                //kembali ke daftar produk lensa
                $Criteria = new CDbCriteria();
                $Criteria->condition = 'is_del = 0 AND idkategori = ' . $kategori_produk;
  
                $userid_actor = Yii::app()->request->getParam('userid_actor');
                $products = inv_inventory::model()->findAll($Criteria);
  
                $bread_crumb_list =
                  '<li>Data Master</li>' .
        
                  '<li>'.
                    '<span> > </span>'.
                    'Produk'.
                  '</li>';
                  
                $html = $this->renderPartial(
                  $list_view_name,
                  array(
                    'userid_actor' => $userid_actor,
                    'products' => $products,
                    'menuid' => $menuid
                  ),
                  true
                );
              }
  
            }
            else
            {
              //tampilkan form edit produk
              $form = $this->GetInventory($tipe_produk, $idproduk);
  
              $Criteria->condition = 'id_item = :idproduk';
              $Criteria->params = array(':idproduk' => $idproduk);
              $rows_hargajual = inv_harga_jual::model()->findAll($Criteria);
  
              if($tipe_produk == 'paket')
              {
                //$Criteria->condition = 'id_paket = :idproduk';
                //$Criteria->params = array(':idproduk' => $idproduk);
                //$rows_hargajual = inv_harga_jual_paket::model()->findAll($Criteria);
                
                $rows_item_paket = $form['item_paket'];
              }
  
              //show form add produk lensa
              $bread_crumb_list =
                '<li>Data Master</li>' .
      
                '<li>'.
                  '<span> > </span>'.
                  '<a href="#" onclick="ShowProdukList('.$userid_actor.', \''.$TipeProdukParameter.'\');">Produk</a>'.
                '</li>'.
      
                '<li>'.
                  '<span> > </span>'.
                  'Edit '. $TipeProdukJudul .
                '</li>';
                
              $html = $this->renderPartial(
                $edit_form_view_name,
                array(
                  'form' => $form,
                  'userid_actor' => $userid_actor,
                  'idproduk' => $idproduk,
                  'lens_type_list' => $lens_type_list,
                  'softlens_type_list' => $softlens_type_list,
                  'frame_type_list' => $frame_type_list,
                  'produsen_list' => $produsen_list,
                  'lens_material_list' => $lens_material_list,
                  'softlens_material_list' => $softlens_material_list,
                  'production_type_list' => $production_type_list,
                  'availability_type_list' => $availability_type_list,
                  'wearing_type_list' => $wearing_type_list,
                  'change_type_list' => $change_type_list,
                  'daftar_toko' => $daftar_toko,
                  'id_kategori' => $kategori_produk,
                  'rows_item_paket' => $rows_item_paket,
                  'rows_hargajual' => $rows_hargajual,
                  'rows_item_paket' => $rows_item_paket,
                  'TipeProdukJudul' => $TipeProdukJudul,
                  'TipeProdukParameter' => $TipeProdukParameter,
                ),
                true
              );
            }
  
            echo CJSON::encode(
              array(
                'html' => $html,
                'bread_crumb_list' => $bread_crumb_list
              )
            );
          }
          else
          {
            //user is not valid to edit produk
            
            $this->actionShowInvalidAccess($userid_actor);
          }
        }



	    //master data - edit products - end


	    //master data - delete products - begin

        /*
          actionDeleteProduk

          Deskripsi
          Action untuk mengubah flag is_del pada record inv_inventory
        */
        public function actionDeleteProduk()
        {
          $menuid = 11;
          $parentmenuid = 6;
          $userid_actor = Yii::app()->request->getParam('userid_actor');
          $idproduk = Yii::app()->request->getParam('idproduk');
          $tipe_produk = Yii::app()->request->getParam('tipe_produk');
          $success_delete_view_name = $this->GetSuccessDeleteViewName($tipe_produk);
          $idgroup = FHelper::GetGroupId($userid_actor);
        
          if(FHelper::AllowMenu($menuid, $idgroup, 'delete'))
          {
            $Criteria = new CDbCriteria();
            $Criteria->condition = 'id = :idproduk';
            $Criteria->params = array(':idproduk' => $idproduk);
            
            if($tipe_produk == 'paket')
            {
              //update record di tabel
              $inv_inventory = inv_type_paket::model()->find($Criteria);
              $inv_inventory['is_del'] = 1;
              $inv_inventory->update();
            }
            else
            {
              //update record di tabel
              $inv_inventory = inv_inventory::model()->find($Criteria);
              $inv_inventory['is_del'] = 1;
              $inv_inventory->update();
            }
            
            $this->actionListProduk();
          }
          else
          {
            $this->actionShowInvalidAccess($userid_actor);
          }
          

          
        }
        
        public function actionViewProduk()
        {
          $menuid = 11;
          $parentmenuid = 6;
          $userid_actor = Yii::app()->request->getParam('userid_actor');
          $idproduk = Yii::app()->request->getParam('idproduk');
          $tipe_produk = Yii::app()->request->getParam('tipe_produk');
          $do_edit = Yii::app()->request->getParam('do_edit');
          $kategori_produk = $this->GetKategoriProduk($tipe_produk);
          $form_name = $this->GetProdukFormName($tipe_produk);
          $success_edit_view_name = $this->GetSuccessEditViewName($tipe_produk);
          $edit_form_view_name = $this->GetEditFormViewName($tipe_produk);
          $view_view_name = $this->GetViewViewName($tipe_produk);
          $form = $this->GetInventory($tipe_produk, $idproduk);
          
          
          $idgroup = FHelper::GetGroupId($userid_actor);
        
          if(FHelper::AllowMenu($menuid, $idgroup, 'read'))
          {
            $TipeProdukJudul = $this->GetTipeProdukJudul($tipe_produk);
            $TipeProdukParameter = $tipe_produk;
            
            $Criteria = new CDbCriteria();
            $Criteria->condition = 'id_item = :idproduk';
            $Criteria->params = array(':idproduk' => $idproduk);
            $rows_hargajual = inv_harga_jual::model()->findAll($Criteria);
  
            $lens_type_list = FHelper::GetLensType();
            $softlens_type_list = FHelper::GetSoftlensType();
            $frame_type_list = FHelper::GetFrameType();
            $produsen_list = FHelper::GetVendor();
            $lens_material_list = FHelper::GetLensMaterial();
            $softlens_material_list = FHelper::GetSoftlensMaterial();
            $production_type_list = FHelper::GetProductionType();
            $availability_type_list = FHelper::GetAvailabilityType();
            $wearing_type_list = FHelper::GetWearingType();
            $change_type_list = FHelper::GetChangeType();
  
            //tampilkan form edit produk
            $form = $this->GetInventory($tipe_produk, $idproduk);
  
            $Criteria->condition = 'id_item = :idproduk';
            $Criteria->params = array(':idproduk' => $idproduk);
            $rows_hargajual = inv_harga_jual::model()->findAll($Criteria);
  
            
            if($tipe_produk == 'paket')
            {
              //$Criteria->condition = 'id_paket = :idproduk';
              //$Criteria->params = array(':idproduk' => $idproduk);
              //$rows_hargajual = inv_harga_jual_paket::model()->findAll($Criteria);
              
              $rows_item_paket = $form['item_paket'];
            }
            
            
            //show form add produk lensa
            $bread_crumb_list =
              '<li>Data Master</li>' .
    
              '<li>'.
                '<span> > </span>'.
                '<a href="#" onclick="ShowProdukList('.$userid_actor.', \''.$TipeProdukParameter.'\');">Produk</a>'.
              '</li>'.
    
              '<li>'.
                '<span> > </span>'.
                'View '. $TipeProdukJudul .
              '</li>';
              
            if($tipe_produk == 'paket')
            {
              $html = $this->renderPartial(
                $view_view_name,
                array(
                  'form' => $form,
                  'userid_actor' => $userid_actor,
                  'idproduk' => $idproduk,
                  'lens_type_list' => $lens_type_list,
                  'softlens_type_list' => $softlens_type_list,
                  'frame_type_list' => $frame_type_list,
                  'produsen_list' => $produsen_list,
                  'lens_material_list' => $lens_material_list,
                  'softlens_material_list' => $softlens_material_list,
                  'production_type_list' => $production_type_list,
                  'availability_type_list' => $availability_type_list,
                  'wearing_type_list' => $wearing_type_list,
                  'change_type_list' => $change_type_list,
                  'id_kategori' => $kategori_produk,
                  'rows_item_paket' => $rows_item_paket,
                  'rows_hargajual' => $rows_hargajual,
                  'rows_item_paket' => $rows_item_paket,
                  'TipeProdukJudul' => $TipeProdukJudul,
                  'TipeProdukParameter' => $TipeProdukParameter,
                  'menuid' => $menuid
                ),
                true
              );
            }
            else
            {
              $html = $this->renderPartial(
                $view_view_name,
                array(
                  'form' => $form,
                  'userid_actor' => $userid_actor,
                  'idproduk' => $idproduk,
                  'lens_type_list' => $lens_type_list,
                  'softlens_type_list' => $softlens_type_list,
                  'frame_type_list' => $frame_type_list,
                  'produsen_list' => $produsen_list,
                  'lens_material_list' => $lens_material_list,
                  'softlens_material_list' => $softlens_material_list,
                  'production_type_list' => $production_type_list,
                  'availability_type_list' => $availability_type_list,
                  'wearing_type_list' => $wearing_type_list,
                  'change_type_list' => $change_type_list,
                  'id_kategori' => $kategori_produk,
                  'rows_hargajual' => $rows_hargajual,
                  'rows_item_paket' => $rows_item_paket,
                  'TipeProdukJudul' => $TipeProdukJudul,
                  'TipeProdukParameter' => $TipeProdukParameter,
                  'menuid' => $menuid
                ),
                true
              );
            }
            
  
            echo CJSON::encode(
              array(
                'html' => $html,
                'bread_crumb_list' => $bread_crumb_list
              )
            );
          }
          else
          {
            $this->actionShowInvalidAccess($userid_actor);
          }
          

          
        }
        
        /*
          actionProdukListAction
          
          Deskripsi
          Action untuk menerima ajax call untuk menangani table wise action.
          
          Parameter
          action
            Integer. Menentukan action yang diambil terhadap list.
            1 = delete
            2 = set inactive
            3 = set active
            
          items
            Array. Berisi id produk.
            
          Return
          List lokasi.
        */
        public function actionProdukListAction()
        {
          $produk_action_type = Yii::app()->request->getParam('produk_action_type');
          $tipe_produk = Yii::app()->request->getParam('tipe_produk');
          $item_list = Yii::app()->request->getParam('selected_item_list');
          
          $Criteria = new CDbCriteria();
          $Criteria->condition = 'id = :idproduk';
              
          if($produk_action_type > 0)
          {
            foreach($item_list as $key => $value)
            {
              $Criteria->params = array(':idproduk' => $value);
              $produk = inv_inventory::model()->find($Criteria);
              
              switch($produk_action_type)
              {
                case 1: //set tidak aktif
                  $produk['is_deact'] = 1;
                  break;
                case 2: //set aktif
                  $produk['is_deact'] = 0;
                  break;
                case 3: //hapus
                  $produk['is_del'] = 1;
                  break;
              }
              
              $produk->update();
            }
          }
          
          $this->actionListProduk();
        }

	    //master data - delete products - end


	    //master data - ambil daftar item paket - begin

        /*
          actionGetDaftarItemPaket

          Deskripsi
          Fungsi untuk mengambil daftar item untuk dipilih menyusun paket.
          Fungsi ini hanya melayani AJAX request dari vfrm_addprodukpaket.php
          pada event onclick yang diikat pada object combobox bernama
          #kategori_produk

          Parameter
            idkategori
              Kategori item sebagai filter pengambilan record dari tabel
              inv_inventory

        */
        public function actionGetDaftarItemPaket()
        {
          $idkategori = Yii::app()->request->getParam('idkategori');
          
          //MakeTable variables setup - begin
          
            $maketable = new MakeTable();
            
            $rowsperpage = Yii::app()->request->getParam('rowsperpage');
            $rowsperpage = ( isset($rowsperpage) == false ? 20 : $rowsperpage );
            $rowsperpage = ($rowsperpage > 0 ? $rowsperpage : 20);
            
            $sort_by = Yii::app()->request->getParam('sortby');
            
            $pageno = Yii::app()->request->getParam('pageno');
            $pageno = ( isset($pageno) == false ? 1 : $pageno );
            
            $search = Yii::app()->request->getParam('search');
            
            if($search != '')
            {
              $Criteria = new CDbCriteria();
              $Criteria->condition = '
                is_del = 0 AND 
                idkategori = :idkategori AND
                nama like :search
              ';
              $Criteria->params = array(
                ':idkategori' => $idkategori,
                ':search' => "%$search%"
              );
            }
            else
            {
              $Criteria = new CDbCriteria();
              $Criteria->condition = 'is_del = 0 AND idkategori = :idkategori';
              $Criteria->params = array(':idkategori' => $idkategori);
            }
            
            $rows_item = inv_inventory::model()->findAll($Criteria);
            $rows = count($rows_item);
            
            $maketable->pages = intval($rows / $rowsperpage);
            
            if( ($rows % $rowsperpage) > 0)
            {
              $maketable->pages++;
            }
            
            if($maketable->pages == 0)
            {
              $maketable->pages = 1;
            }
            
            for($pageke = 1; $pageke <= $maketable->pages; $pageke++)
            {
              $array_goto_page[$pageke] = $pageke;
            }
            
            if($search != '')
            {
              $Criteria = new CDbCriteria();
              $Criteria->condition = '
                is_del = 0 AND 
                idkategori = :idkategori AND
                nama like :search
              ';
              $Criteria->params = array(
                ':idkategori' => $idkategori,
                ':search' => "%$search%"
              );
              
              $Criteria->order = "nama asc";
              $Criteria->limit = $rowsperpage;
              $Criteria->offset = 0;
            }
            else
            {
              $Criteria = new CDbCriteria();
              $Criteria->condition = 'is_del = 0 AND idkategori = :idkategori';
              $Criteria->params = array(':idkategori' => $idkategori);
              
              $Criteria->order = "nama asc";
              $Criteria->limit = $rowsperpage;
              $Criteria->offset = $pageno * $rowsperpage;
            }
            
            $rows_item = inv_inventory::model()->findAll($Criteria);
            
            $array_rows_per_page[10] = 10;
            $array_rows_per_page[20] = 20;
            $array_rows_per_page[40] = 40;
            $array_rows_per_page[50] = 50;
            $array_rows_per_page[100] = 100;
            $array_rows_per_page[200] = 200;
            
            $html = $this->renderPartial(
              'v_list_produk_paket_pilih_item',
              array(
                'rows_item' => $rows_item
              ),
              true
            );
          
          //MakeTable variables setup - end
            

          //MakeTable execution - begin
            //$maketable->pages = intval($rows / $rowsperpage);
            $maketable->list_type = "DaftarItemPaket";
            $maketable->pageno = $pageno;
            $maketable->table_content = $html;
            $maketable->array_rows_per_page = $array_rows_per_page;
            $maketable->array_sort_by = $array_sort_by;
            $maketable->array_goto_page = $array_goto_page;
            $maketable->rows_per_page = $rowsperpage;
            $maketable->sort_by = $sort_by;
            $maketable->sort_direction = 0;
            $maketable->search = $search;
            $maketable->action_name = "DaftarItemPaket_RefreshDetil";
            
            $html = $maketable->Render($maketable);
          //MakeTable execution - end
          
          echo CJSON::encode(array('html' => $html));
        }

	    //master data - ambil daftar item paket - end

	  //produk - end


	//Setting - Data Masters - end


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