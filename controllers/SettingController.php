<?php

/*
  SettingController

  Controller untuk mengatur action di dalam menu Setting
*/

class SettingController extends FController
{
	public function actionIndex()
	{
		$this->render('index');
	}

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


	/*
	  GetUserList

	  Deskripsi
	  Fungsi untuk membaca record daftar user dan mengembalikan dalam bentuk html
	*/
	private function GetUserList($idgroup)
	{
	  $Criteria = new CDbCriteria();
		if ($idgroup == 1 or $idgroup == 4) {
			$Criteria->condition = "t.is_del = 0";
		} else {
			$Criteria->condition = "t.is_del = 0 AND (t.idgroup <> 1 AND t.idgroup <> 4)";
		}
		$Criteria->order = 'username asc';

	  $Users = sys_user::model()->count($Criteria);

	  if($Users > 0)
	  {
	    $Users = sys_user::model()->findAll($Criteria);
	  }

	  return $Users;
	}

	/*
	  GetUserList

	  Deskripsi
	  Fungsi untuk membaca record daftar user dan mengembalikan dalam bentuk html
	*/
	private function GetGroupList($idgroup)
	{
		$Criteria = new CDbCriteria();
		if ($idgroup == 1 or $idgroup == 4) {
			$Criteria->condition = "t.is_del = 0";
		} else {
			$Criteria->condition = "t.is_del = 0 AND (t.id <> 1 AND t.id <> 4)";
		}
		$Criteria->order = 'nama asc';
		$Group = sys_user_group::model()->count($Criteria);

		if($Group > 0)
		{
			$Group = sys_user::model()->findAll($Criteria);
		}

		return $Group;
	}


	/*setting - users - begin*/

	  /*
      actionUsers

      Deskripsi
      Action untuk menampilkan daftar user
    */
    public function actionUsers()
    {
      $userid_actor = Yii::app()->request->getParam('userid_actor');

      $this->userid_actor = $userid_actor;
      $this->parentmenuid = 1;
      $this->menuid = 2;

      $idgroup = FHelper::GetGroupId($userid_actor);

      if(FHelper::AllowMenu($this->menuid, $idgroup, 'read'))
      {
        $this->layout = 'layout-baru';
        $this->bread_crumb_list =
          '<li>Setting</li>'.
          '<li>></li>'.
          '<li>Users</li>';

        $TheMenu = FHelper::RenderMenu(0, $userid_actor, 1);
        $Users = $this->GetUserList($idgroup);
        $UserList = $this->renderPartial(
          'v_list_user',
          array(
            'users' => $Users,
            'userid_actor' => $userid_actor
          ),
          true
        );

        $this->render(
          'index_general',
          array(
            'TheMenu' => $TheMenu,
            'TheContent' => $UserList,
            'userid_actor' => $userid_actor
          )
        );
      }
      else
      {
        $this->actionShowInvalidAccess($this->userid_actor, false);
      }



    }


	  /*
      actionUserList

      Deskripsi
      Action untuk mengembalikan interface daftar user ke ajax caller.
    */
    public function actionUserList()
    {
      $userid_actor = Yii::app()->request->cookies['userid_actor']->value;
      $this->userid_actor = $userid_actor;
      $this->parentmenuid = 1;
      $this->menuid = 2;

      $idgroup = FHelper::GetGroupId($userid_actor);

      if(FHelper::AllowMenu($this->menuid, $idgroup, 'read'))
      {
        $UserList = $this->renderPartial(
          'v_list_user',
          array(
            'users' => $this->GetUserList($idgroup),
            'userid_actor' => $userid_actor
          ),
          true
        );

        echo CJSON::encode(array('html' => $UserList));
      }
      else
      {
        $this->actionShowInvalidAccess($userid_actor);
      }


    }

    /*
      actionUsersCreate()

      Deskripsi
      Action untuk menampilkan interface Create User dan menangani form submission.
    */
    public function actionUsersCreate()
    {
      Yii::log('SettingController::actionUsersCreate() - begin', 'info');

      $userid_actor = Yii::app()->request->getParam('userid_actor');
      $this->parentmenuid = 1;
      $this->menuid = 2;
      $idgroup = FHelper::GetGroupId($userid_actor);

      if(FHelper::AllowMenu($this->menuid, $idgroup, 'write'))
      {
        //listData untuk user group
        $Criteria = new CDbCriteria();
		if ($idgroup == 1 or $idgroup == 4) {
			$Criteria->condition = "t.is_del = 0";
	    } else {
			$Criteria->condition = "t.is_del = 0 AND (t.id <> 1 AND t.id <> 4)";
		}
		$Criteria->order = 'nama asc';
        $groups = sys_user_group::model()->findAll($Criteria);
        $listUserGroup = CHtml::listData($groups, 'id', 'nama');

        //listData untuk lokasi
        $branches = mtr_branch::model()->findAll();
        $listBranches = CHtml::listData($branches, 'branch_id', 'name');

        //listData untuk lokasi
        $Criteria = new CDbCriteria();
        $Criteria->condition = 'is_del = 0';
        $Criteria->order = 'nama asc';
        $karyawan = sys_karyawan::model()->findAll($Criteria);
        $listKaryawan = CHtml::listData($karyawan, 'id', 'nama');

        $Form = new frmEditUser();

        $test = Yii::app()->request->getParam('do_create');
        if(isset($test))
        {
          //memproses form submission
          $Form->attributes = Yii::app()->request->getParam('frmEditUser');
          $Form['foto'] = CUploadedFile::getInstance($Form, 'foto');
          $do_create = Yii::app()->request->getParam('do_create');

          if($do_create == 1)
          {
            $validate_list = array(
              'username', 'nama', 'hpnumber', 'email',
              'password', 'password2', 'foto'
            );

            if($Form->validate($validate_list))
            {
              //proses record ke tabel
              $User = new sys_user();
              $User['username'] = $Form['username'];
              $User['nama'] = $Form['nama'];
              $User['hp_number'] = $Form['hpnumber'];
              $User['email'] = $Form['email'];
              $User['password'] = sha1('123'.$Form['password'].'123');
              $User['idgroup'] = $Form['idgroup'];
              $User['id_location'] = $Form['idlokasi'];

              if($Form['foto'] != NULL)
              {
                $file_name =  $Form['username'] . '-' . $Form['foto']->name;
                $User['foto'] = $file_name;
              }

              if($Form['iskaryawan'] == 1)
              {
                $User['id_karyawan'] = $Form['idkaryawan'];
              }
              else
              {
                $User['id_karyawan'] = -1;
              }

              $User->save();
              $userid_target = $User->getPrimaryKey();

              //simpan file foto
              $Form['foto'] = CUploadedFile::getInstance($Form, 'foto');
              Yii::log('Form[foto] = ' . $Form['foto'], 'info');

              if($Form['foto'] != NULL)
              {
                $Form['foto']->saveAs(
                  Yii::app()->basePath .
                  DIRECTORY_SEPARATOR . '..' .
                  DIRECTORY_SEPARATOR . 'images' .
                  DIRECTORY_SEPARATOR . 'user_images' .
                  DIRECTORY_SEPARATOR . $file_name
                );
              }
              else
              {

              }

              $params = array('userid_actor' => $userid_actor, 'userid_target' => $userid_target);
              $url = $this->createUrl('setting/viewuser', $params);
              $this->redirect($url);
            }
            else
            {
              //gagal validasi form submission
              $Form['userid_actor'] = $userid_actor;
              $html = $this->renderPartial(
                'vfrm_useradd',
                array(
                  'form' => $Form,
                  'listUserGroup' => $listUserGroup,
                  'listBranches' => $listBranches,
                  'listKaryawan' => $listKaryawan
                ),
                true
              );
            }
          }
          else
          {
            //tampilkan daftar users
            $html = $this->renderPartial(
              'v_list_user',
              array(
                'users' => $this->GetUserList($idgroup),
                'userid_actor' => $userid_actor
              ),
              true
            );
          }


        }
        else
        {
          Yii::log('Show form', 'info');

          //show form
          $Form['userid_actor'] = $userid_actor;
          $html = $this->renderPartial(
            'vfrm_useradd',
            array(
              'form' => $Form,
              'listUserGroup' => $listUserGroup,
              'listBranches' => $listBranches,
              'listKaryawan' => $listKaryawan
            ),
            true
          );
        }

        echo CJSON::encode(array('html' => $html));
      }
      else
      {
        $this->actionShowInvalidAccess($userid_actor);
      }


    }

    /*
      actionUsersEdit

      Deskripsi
      Action untuk menampilkan interface untuk mengedit data user atau password
      user. Dan memproses form submission.
    */
    public function actionUsersEdit()
    {
      $userid_actor = Yii::app()->request->cookies['userid_actor']->value;
      $this->parentmenuid = 1;
      $this->menuid = 2;
      $idgroup = FHelper::GetGroupId($userid_actor);

      if(FHelper::AllowMenu($this->menuid, $idgroup, 'edit'))
      {
        //instantiate an form object
        $Form = new frmEditUser();
        $FormPassword = new frmEditUser();

        //periksa apakah submit edit form?
        $test = Yii::app()->request->getParam('frmEditUser');
        $test2 = Yii::app()->request->getParam('frmChangePassword');

        //listData untuk user group
        $Criteria = new CDbCriteria();
        if ($idgroup == 1 or $idgroup == 4) {
          $Criteria->condition = "t.is_del = 0";
        } else {
          $Criteria->condition = "t.is_del = 0 AND (t.id <> 1 AND t.id <> 4)";
        }
        $Criteria->order = 'nama asc';
        $groups = sys_user_group::model()->findAll($Criteria);
        $listUserGroup = CHtml::listData($groups, 'id', 'nama');

        //listData untuk lokasi
        $branches = mtr_branch::model()->findAll();
        $listBranches = CHtml::listData($branches, 'branch_id', 'name');

        //listData untuk lokasi
        $Criteria = new CDbCriteria();
        $Criteria->condition = 'is_del = 0';
        $Criteria->order = 'nama asc';
        $karyawan_list = sys_karyawan::model()->findAll($Criteria);
        $listKaryawan = CHtml::listData($karyawan_list, 'id', 'nama');

        if(isset($test) == true || isset($test2) == true)
        {

          //handle user edit submission
          if(isset($test))
          {
            $Form->attributes = Yii::app()->request->getParam('frmEditUser');

            //validasi form entry
            if($Form->validate(array('nama', 'email', 'hp_number'), true))
            {
              //get target user info from database
              $Criteria = new CDbCriteria();
              $Criteria->condition = 'id = :userid_target';
              $Criteria->params = array(':userid_target' => $Form['userid_target']);

              $User = sys_user::model()->find($Criteria);

              //update data user
              $User['nama'] = $Form['nama'];
              $User['email'] = $Form['email'];
              $User['hp_number'] = $Form['hpnumber'];
              $User['update_time'] = date('Y-m-d H:i:s', mktime());
              $User['update_by'] = $Form['userid_actor'];
              $User['idgroup'] = $Form['idgroup'];
              //$User['id_location'] = $Form['idlokasi'];

              if($Form['iskaryawan'] == 1)
              {
                $User['id_karyawan'] = $Form['idkaryawan'];
              }
              else
              {
                $User['id_karyawan'] = -1;
              }

              $User->update();

              $Form['username'] = $User['username'];
              $Form['idlokasi'] = $User['id_location'];
              
              //render 'update berhasil'
              $html = $this->renderPartial(
                'v_useredit_success',
                array(
                  'form' => $Form,
                  'listUserGroup' => $listUserGroup,
                  'listBranches' => $listBranches,
                  'listKaryawan' => $listKaryawan
                ),
                true
              );
            }
            else
            {
              //rendering user edit form, grab the html
              $html = $this->renderPartial(
                'vfrm_useredit',
                array(
                  'form' => $Form,
                  'userid_actor' => $userid_actor,
                  'formpassword' => $FormPassword,
                  'listUserGroup' => $listUserGroup,
                  'listBranches' => $listBranches,
                  'listKaryawan' => $listKaryawan
                ),
                true
              );
            }
          }

          //handle change password submission
          if(isset($test2))
          {
            //instantiate an form object
            $frmChangePassword = Yii::app()->request->getParam('frmChangePassword');
            $FormPassword['userid_actor'] = $frmChangePassword['userid_actor'];
            $FormPassword['userid_target'] = $frmChangePassword['userid_target'];
            $FormPassword['password'] = $frmChangePassword['password'];
            $FormPassword['password2'] = $frmChangePassword['password2'];

            Yii::log('FormPassword[\'userid_actor\'] = ' . $FormPassword['userid_actor'], 'info');
            Yii::log('FormPassword[\'password\'] = ' . $FormPassword['password'], 'info');
            Yii::log('FormPassword[\'password2\'] = ' . $FormPassword['password2'], 'info');

            //validasi form entry
            if($FormPassword->validate(array('password'), true))
            {
              //get target user info from database
                $Criteria = new CDbCriteria();
                $Criteria->condition = 'id = :userid_target';
                $Criteria->params = array(':userid_target' => $FormPassword['userid_target']);

                $User = sys_user::model()->find($Criteria);

              //update data user
              $User['password'] = sha1('123'.$FormPassword['password'].'123');
              $User['update_time'] = date('Y-m-d H:i:s', mktime());
              $User['update_by'] = $Form['userid_actor'];

              $User->update();

              //render 'update berhasil'
              $html = $this->renderPartial(
                'v_changepassword_success',
                array(
                  'userid_actor' => $Form['userid_actor']
                ),
                true
              );
            }
            else
            {
              //rendering user edit form, grab the html
              $html = $this->renderPartial(
                'vfrm_useredit',
                array(
                  'form' => $Form,
                  'userid_actor' => $userid_actor,
                  'formpassword' => $FormPassword,
                  'listUserGroup' => $listUserGroup,
                  'listBranches' => $listBranches,
                  'listKaryawan' => $listKaryawan
                ),
                true
              );
            }
          }
        }
        else
        {
          //get target user info from database
            $Criteria = new CDbCriteria();
            $Criteria->condition = 'id = :userid_target';
            $Criteria->params = array(':userid_target' => Yii::app()->request->getParam('userid_target'));

            $User = sys_user::model()->find($Criteria);

          //pass on user's data from table
          $Form->userid_actor = Yii::app()->request->getParam('userid_actor');
          $Form->userid_target = Yii::app()->request->getParam('userid_target');
          $Form->username = $User['username'];
          $Form->nama = $User['nama'];
          $Form->hpnumber = $User['hp_number'];
          $Form->email = $User['email'];
          $Form->idgroup = $User['idgroup'];
          $Form['idlokasi'] = $User['id_location'];
          $Form['idkaryawan'] = $User['id_karyawan'];
          $Form['iskaryawan'] = ($Form['idkaryawan'] == -1 ? 0 : 1);


          //pass on user's data from table
          $FormPassword->userid_actor = Yii::app()->request->getParam('userid_actor');
          $FormPassword->userid_target = Yii::app()->request->getParam('userid_target');

          //rendering user edit form, grab the html
          $html = $this->renderPartial(
            'vfrm_useredit',
            array(
              'form' => $Form,
              'userid_actor' => $userid_actor,
              'formpassword' => $FormPassword,
              'listUserGroup' => $listUserGroup,
              'listBranches' => $listBranches,
              'listKaryawan' => $listKaryawan
            ),
            true
          );
        }

        //return the html, wrapped in json format
        echo CJSON::encode(array('html' => $html));
      }
      else
      {
        $this->actionShowInvalidAccess($userid_actor);
      }


    }

    /*
      actionUsersDelete

      Deskripsi
      Action untuk menghapus user.
    */
    public function actionUsersDelete()
    {
      $userid_actor = Yii::app()->request->getParam('userid_actor');
      $userid_target = Yii::app()->request->getParam('userid_target');
      $this->parentmenuid = 1;
      $this->menuid = 2;
      $do_delete = Yii::app()->request->getParam('do_delete');
      $idgroup = FHelper::GetGroupId($userid_actor);

      if(FHelper::AllowMenu($this->menuid, $idgroup, 'delete'))
      {
        //lakukan delete berdasarkan userid_target
        $Criteria = new CDbCriteria();
        $Criteria->condition = 'id = :idusertarget';
        $Criteria->params = array(':idusertarget' => $userid_target);
        $sys_user = sys_user::model()->find($Criteria);
        $sys_user['is_del'] = 1;
        $sys_user->save();

        //tampilkan info record sudah didelete
        $this->actionUserList();
      }
      else
      {
        $this->actionShowInvalidAccess($userid_actor);
      }


    }

    /*
      actionViewUser

      Deskripsi
      Action untuk menampilkan data user

      Parameter
      userid_actor
        Integer

      userid_target
        Integer

      Return
      View data user
    */
    public function actionViewUser()
    {
      $userid_actor = Yii::app()->request->getParam('userid_actor');
      $userid_target = Yii::app()->request->getParam('userid_target');
      $this->userid_actor = $userid_actor;
      $this->parentmenuid = 1;
      $this->menuid = 2;
      $idgroup = FHelper::GetGroupId($userid_actor);

      if(FHelper::AllowMenu($this->menuid, $idgroup, 'read'))
      {
        $Form = new frmEditUser();

        //listData untuk user group
		$Criteria = new CDbCriteria();
		if ($idgroup == 1 or $idgroup == 4) {
			$Criteria->condition = "t.is_del = 0";
		} else {
			$Criteria->condition = "t.is_del = 0 AND (t.id <> 1 AND t.id <> 4)";
		}
		$Criteria->order = 'nama asc';
        $groups = sys_user_group::model()->findAll($Criteria);
        $listUserGroup = CHtml::listData($groups, 'id', 'nama');

        //listData untuk lokasi
        $branches = mtr_branch::model()->findAll();
        $listBranches = CHtml::listData($branches, 'branch_id', 'name');

        //listData untuk lokasi
        $karyawan_list = sys_karyawan::model()->findAll();
        $listKaryawan = CHtml::listData($karyawan_list, 'id', 'nama');

        //get target user info from database
          $Criteria = new CDbCriteria();
          $Criteria->condition = 'id = :userid_target';
          $Criteria->params = array(':userid_target' => $userid_target);

          $User = sys_user::model()->find($Criteria);

        //update data user
        $Form['nama'] = $User['nama'];
        $Form['email'] = $User['email'];
        $Form['username'] = $User['username'];
        $Form['hpnumber'] = $User['hp_number'];
        $Form['idgroup'] = $User['idgroup'];
        $Form['idlokasi'] = $User['id_location'];
        $Form['idkaryawan'] = $User['id_karyawan'];

        if($User['id_karyawan'] != -1)
        {
          $Form['iskaryawan'] = 1;
        }
        else
        {
          $Form['iskaryawan'] = 0;
        }

        //render 'update berhasil'
        $html = $this->renderPartial(
          'v_view_user',
          array(
            'form' => $Form,
            'userid_actor' => $userid_actor,
            'userid_target' => $userid_target,
            'listUserGroup' => $listUserGroup,
            'listBranches' => $listBranches,
            'listKaryawan' => $listKaryawan
          ),
          true
        );

        echo CJSON::encode(array('html' => $html));
      }
      else
      {
        $this->actionShowInvalidAccess($userid_actor);
      }


    }

    /*
      actionUserListAction

      Deskripsi
      Action untuk mengolah operasi terhadap list user

      Parameter
      user_action_type
        Integer. Kode perintah operasi terhadap list user.
        1 = set tidak aktif, 2 = set aktif, 3 = hapus

      Return
        Memanggil actionShowUserList.
    */
    public function actionUserListAction()
    {
      $action_type = Yii::app()->request->getParam('user_action_type');
      $item_list = Yii::app()->request->getParam('selected_item_list');
      $this->parentmenuid = 1;
      $this->menuid = 2;


      $Criteria = new CDbCriteria();
      $Criteria->condition = 'id = :id';

      if($action_type > 0)
      {
        foreach($item_list as $key => $value)
        {
          $Criteria->params = array(':id' => $value);
          $user = sys_user::model()->find($Criteria);

          switch($action_type)
          {
            case 1: //set tidak aktif
              $user['is_inactive'] = 1;
              break;
            case 2: //set aktif
              $user['is_inactive'] = 0;
              break;
            case 3: //hapus
              $user['is_del'] = 1;
              break;
          }

          $user->update();
        }

        $this->actionUserList();
      }
    }



	/*setting - users - end*/


	/*setting - karyawan - begin*/

	  /*
      actionKaryawan

      Deskripsi
      Action untuk menampilkan daftar karyawan
    */
    public function actionKaryawan()
    {
      $userid_actor = Yii::app()->request->cookies['userid_actor']->value;
      $this->idlokasi = Yii::app()->request->cookies['idlokasi']->value;
      $this->userid_actor = $userid_actor;
      $this->parentmenuid = 6;
      $this->menuid = 49;
      $this->layout = 'layout-baru';
      $this->bread_crumb_list =
        '<li>Setting</li>'.
        '<li>></li>'.
        '<li>Karyawan</li>';

      $idgroup = FHelper::GetGroupId($userid_actor);

      if(FHelper::AllowMenu($this->menuid, $idgroup, 'read'))
      {
        $this->layout = 'layout-baru';

        $Criteria = new CDbCriteria();
        $Criteria->condition = 'is_del = 0';

        $TheMenu = FHelper::RenderMenu(0, $userid_actor, 1);
        $Karyawan = sys_karyawan::model()->findAll($Criteria);
        $KaryawanList = $this->renderPartial(
          'v_list_karyawan',
          array(
            'karyawan_list' => $Karyawan,
            'userid_actor' => $userid_actor
          ),
          true
        );

        $this->render(
          'index_general',
          array(
            'TheMenu' => $TheMenu,
            'TheContent' => $KaryawanList,
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
      actionKaryawanList

      Deskripsi
      Action untuk mengembalikan interface daftar karyawan ke ajax caller.
    */
    public function actionKaryawanList()
    {
      $userid_actor = Yii::app()->request->cookies['userid_actor']->value;
      $this->userid_actor = $userid_actor;
      $this->parentmenuid = 6;
      $this->menuid = 49;
      $idgroup = FHelper::GetGroupId($userid_actor);

      if(FHelper::AllowMenu($this->menuid, $idgroup, 'read'))
      {
        $Criteria = new CDbCriteria();
        $Criteria->condition = 'is_del = 0';
        $Karyawan = sys_karyawan::model()->findAll($Criteria);

        $html = $this->renderPartial(
          'v_list_karyawan',
          array(
            'karyawan_list' => $Karyawan,
            'userid_actor' => $userid_actor
          ),
          true
        );

        echo CJSON::encode(array('html' => $html));
      }
      else
      {
        $this->actionShowInvalidAccess($userid_actor);
      }
    }

    /*
      actionKaryawanCreate()

      Deskripsi
      Action untuk menampilkan interface Create User dan menangani form submission.
    */
    public function actionKaryawanAdd()
    {
      $userid_actor = Yii::app()->request->cookies['userid_actor']->value;
      $this->userid_actor = $userid_actor;
      $this->parentmenuid = 6;
      $this->menuid = 49;
      $idgroup = FHelper::GetGroupId($userid_actor);

      if(FHelper::AllowMenu($this->menuid, $idgroup, 'write'))
      {
        $Form = new frmEditKaryawan();

        $test = Yii::app()->request->getParam('do_add');
        if(isset($test))
        {
          Yii::log('Memproses form submission', 'info');

          //memproses form submission
          $Form->attributes = Yii::app()->request->getParam('frmEditKaryawan');

          if($Form->validate())
          {
            Yii::log('Form submission validated', 'info');

            //proses record ke tabel
            $Karyawan = new sys_karyawan();
            $Karyawan['nama'] = $Form['nama'];
            $Karyawan['email'] = $Form['email'];
            $Karyawan['hp_number'] = $Form['hpnumber'];

            $Karyawan->save();
            $Form['karyawanid_target'] = $Karyawan->getPrimaryKey();

            //tampilkan informasi sukses menambah user
            $html = $this->renderPartial(
              'v_karyawanadd_success',
              array(
                'form' => $Form,
              ),
              true
            );
          }
          else
          {
            Yii::log('Form submission validation failed', 'info');

            //gagal validasi form submission
            $Form['userid_actor'] = $userid_actor;
            $html = $this->renderPartial(
              'vfrm_karyawanadd',
              array(
                'form' => $Form,
                'userid_actor' => $userid_actor
              ),
              true
            );
          }
        }
        else
        {
          Yii::log('Show form', 'info');

          //show form
          $Form['userid_actor'] = $userid_actor;
          $html = $this->renderPartial(
            'vfrm_karyawanadd',
            array(
              'form' => $Form,
              'userid_actor' => $userid_actor
            ),
            true
          );
        }

        echo CJSON::encode(array('html' => $html));
      }
      else
      {
        $this->actionShowInvalidAccess($userid_actor);
      }


    }

    /*
      actionKaryawanEdit

      Deskripsi
      Action untuk menampilkan interface untuk mengedit data karyawan. Dan
      memproses form submission.

    */
    public function actionKaryawanEdit()
    {
      $userid_actor = Yii::app()->request->cookies['userid_actor']->value;
      $this->userid_actor = $userid_actor;
      $this->parentmenuid = 6;
      $this->menuid = 49;
      $idgroup = FHelper::GetGroupId($userid_actor);

      if(FHelper::AllowMenu($this->menuid, $idgroup, 'edit'))
      {
        //instantiate an form object
        $Form = new frmEditKaryawan();

        //periksa apakah submit edit form?
        $test = Yii::app()->request->getParam('frmEditKaryawan');

        if(isset($test) == true)
        {
          $do_edit = Yii::app()->request->getParam('do_edit');

          //handle user edit submission
          if($do_edit == 1)
          {
            $Form->attributes = Yii::app()->request->getParam('frmEditKaryawan');

            //validasi form entry
            if($Form->validate())
            {
              //get target user info from database
                $Criteria = new CDbCriteria();
                $Criteria->condition = 'id = :idkaryawan';
                $Criteria->params = array(':idkaryawan' => $Form['karyawanid_target']);

                $Karyawan = sys_karyawan::model()->find($Criteria);

              //update data user
              $Karyawan['nama'] = $Form['nama'];
              $Karyawan['email'] = $Form['email'];
              $Karyawan['hp_number'] = $Form['hpnumber'];

              $Karyawan->update();

              //render 'update berhasil'
              $html = $this->renderPartial(
                'v_karyawanedit_success',
                array(
                  'form' => $Form
                ),
                true
              );
            }
            else
            {
              //validasi gagal. tampilkan frm edit karyawan

              //rendering user edit form, grab the html
              $html = $this->renderPartial(
                'vfrm_karyawanedit',
                array(
                  'form' => $Form,
                ),
                true
              );
            }
          }
          else
          {
            //tampilkan form edit karyawan

            //rendering user edit form, grab the html
            $html = $this->renderPartial(
              'vfrm_karyawanedit',
              array(
                'form' => $Form,
              ),
              true
            );
          }

        }
        else
        {
          //tampilkan form edit karyawan

          //get target user info from database
            $Criteria = new CDbCriteria();
            $Criteria->condition = 'id = :idkaryawan';
            $Criteria->params = array(':idkaryawan' => Yii::app()->request->getParam('idkaryawan'));

            $Karyawan = sys_karyawan::model()->find($Criteria);

          //pass on user's data from table
          $Form->userid_actor = $userid_actor;
          $Form->karyawanid_target = Yii::app()->request->getParam('idkaryawan');
          $Form->nama = $Karyawan['nama'];
          $Form->email = $Karyawan['email'];
          $Form->hpnumber = $Karyawan['hp_number'];

          //rendering user edit form, grab the html
          $html = $this->renderPartial(
            'vfrm_karyawanedit',
            array(
              'form' => $Form,
              'userid_actor' => $userid_actor
            ),
            true
          );
        }

        //return the html, wrapped in json format
        echo CJSON::encode(array('html' => $html));
      }
      else
      {
        $this->actionShowInvalidAccess($userid_actor);
      }


    }

    /*
      actionKaryawanDelete

      Deskripsi
      Action untuk menghapus karyawan.
    */
    public function actionKaryawanDelete()
    {
      $this->parentmenuid = 6;
      $this->menuid = 49;
      $userid_actor = Yii::app()->request->cookies['userid_actor']->value;
      $karyawanid_target = Yii::app()->request->getParam('karyawanid_target');

      $idgroup = FHelper::GetGroupId($userid_actor);

      if(FHelper::AllowMenu($this->menuid, $idgroup, 'delete'))
      {
        $Criteria = new CDbCriteria();
        $Criteria->condition = 'id = :karyawanid_target';
        $Criteria->params = array(':karyawanid_target' => $karyawanid_target);

        //update record di tabel
        $sys_karyawan = sys_karyawan::model()->find($Criteria);
        $sys_karyawan['is_del'] = 1;
        $sys_karyawan->update();

        $this->actionKaryawanList();
      }
      else
      {
        $this->actionShowInvalidAccess($userid_actor);
      }
    }

    public function actionKaryawanListAction()
    {
      $action_type = Yii::app()->request->getParam('karyawan_action_type');
      $item_list = Yii::app()->request->getParam('selected_item_list');

      $Criteria = new CDbCriteria();
      $Criteria->condition = 'id = :id';

      if($action_type > 0)
      {
        foreach($item_list as $key => $value)
        {
          $Criteria->params = array(':id' => $value);
          $karyawan = sys_karyawan::model()->find($Criteria);

          switch($action_type)
          {
            case 1: //set tidak aktif
              $karyawan['is_inactive'] = 1;
              break;
            case 2: //set aktif
              $karyawan['is_inactive'] = 0;
              break;
            case 3: //hapus
              $karyawan['is_del'] = 1;
              break;
          }

          $karyawan->update();
        }

        $this->actionKaryawanList();
      }
    }

    /*
      actionViewKaryawan

      Deskripsi
      Action untuk menampilkan data user

      Parameter
      userid_actor
        Integer

      idkaryawan
        Integer

      Return
      View data user
    */
    public function actionViewKaryawan()
    {
      $this->parentmenuid = 6;
      $this->menuid = 49;
      $userid_actor = Yii::app()->request->cookies['userid_actor']->value;
      $karyawanid_target = Yii::app()->request->getParam('karyawanid_target');

      $idgroup = FHelper::GetGroupId($userid_actor);

      if(FHelper::AllowMenu($this->menuid, $idgroup, 'read'))
      {
      }
      else
      {
        $this->actionShowInvalidAccess($userid_actor);
      }

      $Form = new frmEditKaryawan();

      //get target user info from database
        $Criteria = new CDbCriteria();
        $Criteria->condition = 'id = :karyawanid_target';
        $Criteria->params = array(':karyawanid_target' => $karyawanid_target);

        $Karyawan = sys_karyawan::model()->find($Criteria);

      //update data user
      $Form['userid_actor'] = $userid_actor;
      $Form['karyawanid_target'] = $karyawanid_target;
      $Form['nama'] = $Karyawan['nama'];
      $Form['email'] = $Karyawan['email'];
      $Form['hpnumber'] = $Karyawan['hp_number'];

      //render 'update berhasil'
      $html = $this->renderPartial(
        'v_view_karyawan',
        array(
          'form' => $Form,
          'userid_actor' => $userid_actor,
          'karyawanid_target' => $karyawanid_target,
        ),
        true
      );

      echo CJSON::encode(array('html' => $html));
    }

	/*setting - karyawan - end*/


	/*setting - user group - begin*/

	  /*UserGroup*/
	  function actionUserGroup()
	  {
	    $userid_actor = Yii::app()->request->getParam('userid_actor');
      $this->userid_actor = $userid_actor;
      $this->parentmenuid = 1;
      $this->menuid = 18;
      $this->layout = 'layout-baru';
      $this->bread_crumb_list =
        '<li>Setting</li>'.
        '<li>></li>'.
        '<li>User Group</li>';

      $idgroup = FHelper::GetGroupId($userid_actor);

      if(FHelper::AllowMenu($this->menuid, $idgroup, 'read'))
      {
        $TheMenu = FHelper::RenderMenu(0, $userid_actor, 1);

		$Criteria = new CDbCriteria();
		if ($idgroup == 1 or $idgroup == 4) {
			$Criteria->condition = "t.is_del = 0";
		} else {
			$Criteria->condition = "t.is_del = 0 AND (t.id <> 1 AND t.id <> 4)";
		}
		$Criteria->order = 'nama asc';
		$GroupList = sys_user_group::model()->findAll($Criteria);

        $UserGroupList = $this->renderPartial(
          'v_list_usergroup',
          array(
            'usergroup_list' => $GroupList,
            'userid_actor' => $userid_actor
          ),
          true
        );

        $this->layout = 'layout-baru';

        $this->render(
          'index_general',
          array(
            'TheMenu' => $TheMenu,
            'TheContent' => $UserGroupList,
            'userid_actor' => $userid_actor
          )
        );
      }
      else
      {
        $this->actionShowInvalidAccess($userid_actor, false);
      }


	  }

	  /*UserGroup List*/
	  function actionUserGroupList()
	  {
	    $userid_actor = Yii::app()->request->getParam('userid_actor');
	    $this->parentmenuid = 1;
      $this->menuid = 18;
	    $idgroup = FHelper::GetGroupId($userid_actor);

      if(FHelper::AllowMenu($this->menuid, $idgroup, 'read'))
      {
		$Criteria = new CDbCriteria();
		if ($idgroup == 1 or $idgroup == 4) {
			$Criteria->condition = "t.is_del = 0";
		} else {
			$Criteria->condition = "t.is_del = 0 AND (t.id <> 1 AND t.id <> 4)";
		}
		$Criteria->order = 'nama asc';
		$GroupList = sys_user_group::model()->findAll($Criteria);

        $UserGroupList = $this->renderPartial(
          'v_list_usergroup',
          array(
            'usergroup_list' => $GroupList,
            'userid_actor' => $userid_actor,
            'menuid' => $menuidr
          ),
          true
        );

        echo CJSON::encode(array('html' => $UserGroupList));
      }
      else
      {
        $this->actionShowInvalidAccess($userid_actor, false);
      }


	  }

	  /*UserGroup Edit*/
	  function actionUserGroupEdit()
	  {
	    $userid_actor = Yii::app()->request->getParam('userid_actor');
	    $this->userid_actor = $userid_actor;
	    $this->parentmenuid = 1;
      $this->menuid = 18;
	    $idgroup = FHelper::GetGroupId($userid_actor);

      if(FHelper::AllowMenu($this->menuid, $idgroup, 'edit'))
      {
        //instantiate an form object
        $Form = new frmEditUserGroup();

        //periksa apakah submit edit form?
        $test = Yii::app()->request->getParam('frmEditUserGroup');

        //listData untuk user group
		$Criteria = new CDbCriteria();
		if ($idgroup == 1 or $idgroup == 4) {
			$Criteria->condition = "t.is_del = 0";
		} else {
			$Criteria->condition = "t.is_del = 0 AND (t.id <> 1 AND t.id <> 4)";
		}
		$Criteria->order = 'nama asc';
        $groups = sys_user_group::model()->findAll($Criteria);

        if(isset($test) == true)
        {
          $do_edit = Yii::app()->request->getParam('do_edit');

          if($do_edit == 1)
          {
            Yii::log('processing user group edit form submission', 'info');

            $Form->attributes = Yii::app()->request->getParam('frmEditUserGroup');

            Yii::log('form[usergroupid_target] = ' . $Form['usergroupid_target'], 'info');

            //validasi form entry
            if($Form->validate())
            {
              Yii::log('user group edit form submission validated', 'info');

              //get target user info from database
                $Criteria = new CDbCriteria();
                $Criteria->condition = 'id = :usergroupid_target';
                $Criteria->params = array(':usergroupid_target' => $Form['usergroupid_target']);

                $User = sys_user_group::model()->find($Criteria);

              //update data user group
              $User['nama'] = $Form['nama'];

              $User->update();

              //render 'update berhasil'
              $html = $this->renderPartial(
                'v_usergroup_edit_success',
                array(
                  'form' => $Form
                ),
                true
              );
            }
            else
            {
              Yii::log('user group edit form validation failed', 'info');

              //rendering user edit form, grab the html
              $html = $this->renderPartial(
                'vfrm_usergroup_edit',
                array(
                  'userid_actor' => $userid_actor,
                  'form' => $Form,
                ),
                true
              );
            }
          }
          else
          {
            //batal edit
            //tampilkan usergroup list

			$Criteria = new CDbCriteria();
			if ($idgroup == 1 or $idgroup == 4) {
				$Criteria->condition = "t.is_del = 0";
			} else {
				$Criteria->condition = "t.is_del = 0 AND (t.id <> 1 AND t.id <> 4)";
			}
			$Criteria->order = 'nama asc';
            $GroupList = sys_user_group::model()->findAll($Criteria);

            $html = $this->renderPartial(
              'v_list_usergroup',
              array(
                'usergroup_list' => $GroupList,
                'userid_actor' => Yii::app()->request->getParam('userid_actor')
              ),
              true
            );
          }

        }
        else
        {
          Yii::log('open form', 'info');

          //get target user info from database
            $Criteria = new CDbCriteria();
            $Criteria->condition = 'id = :usergroupid_target';
            $Criteria->params = array(':usergroupid_target' => Yii::app()->request->getParam('usergroupid_target'));

            $User = sys_user_group::model()->find($Criteria);

          //pass on user's data from table
          $Form->userid_actor = Yii::app()->request->getParam('userid_actor');
          $Form->usergroupid_target = Yii::app()->request->getParam('usergroupid_target');
          $Form->nama = $User['nama'];

          //rendering user edit form, grab the html
          $html = $this->renderPartial(
            'vfrm_usergroup_edit',
            array(
              'userid_actor' => $userid_actor,
              'form' => $Form,
            ),
            true
          );
        }

        //return the html, wrapped in json format
        echo CJSON::encode(array('html' => $html));
      }
      else
      {
        $this->actionShowInvalidAccess($userid_actor, false);
      }


	  }



	/*setting - user group - end*/


	/*setting - menu - begin*/

	  /*Menu*/
	  function actionMenu()
	  {
	    $this->layout = 'layout-baru';

      $userid_actor = Yii::app()->request->getParam('userid_actor');

      $this->userid_actor = $userid_actor;
      $this->parentmenuid = 1;
      $this->menuid = 19;
      $this->layout = 'layout-baru';
      $this->bread_crumb_list =
        '<li>Setting</li>'.
        '<li>></li>'.
        '<li>Menu</li>';

      $edit_menu_list = '';
	    $edit_menu_list = $this->GenerateEditMenuList($userid_actor, 0, 0);

      $TheMenu = FHelper::RenderMenu(0, $userid_actor, 1);
      $Menus = sys_menu::model()->findAll();
      $MenuList = $this->renderPartial(
        'v_list_menu',
        array(
          'menulist' => $edit_menu_list,
          'userid_actor' => $userid_actor
        ),
        true
      );

      $this->render(
        'index_general',
        array(
          'TheMenu' => $TheMenu,
          'TheContent' => $MenuList,
          'userid_actor' => $userid_actor
        )
      );
	  }

	  /*Menu List*/
	  function actionMenuList()
	  {
	    $this->parentmenuid = 1;
      $this->menuid = 19;

	    $MenuList = sys_menu::model()->findAll();
	    $userid_actor = Yii::app()->request->getParam('userid_actor');

	    $edit_menu_list = '';
	    $edit_menu_list = $this->GenerateEditMenuList($userid_actor, 0, 0);

	    $UserGroupList = $this->renderPartial(
        'v_list_menu',
        array(
          'menulist' => $edit_menu_list,
          'userid_actor' => $userid_actor
        ),
        true
      );

      echo CJSON::encode(array('html' => $UserGroupList));
	  }

	  /*Menu Edit*/
	  function actionMenuEdit()
	  {
	    $this->parentmenuid = 1;
      $this->menuid = 19;

	    //instantiate an form object
      $Form = new frmEditMenu();

      //periksa apakah submit edit form?
      $test = Yii::app()->request->getParam('frmEditMenu');

      //listData untuk user group
      $menus = sys_menu::model()->findAll();

      if(isset($test) == true)
      {

        $Form->attributes = Yii::app()->request->getParam('frmEditMenu');

        //validasi form entry
        if($Form->validate())
        {
          //get target user info from database
            $Criteria = new CDbCriteria();
            $Criteria->condition = 'id = :menuid_target';
            $Criteria->params = array(':menuid_target' => $Form['menuid_target']);

            $Menu = sys_menu::model()->find($Criteria);

          //update data user group
            $Menu['title'] = $Form['title'];
            $Menu->update();

          //render 'update berhasil'
          $html = $this->renderPartial(
            'v_menu_edit_success',
            array(
              'form' => $Form
            ),
            true
          );
        }
        else
        {
          //rendering user edit form, grab the html
          $html = $this->renderPartial(
            'vfrm_menu_edit',
            array(
              'form' => $Form,
              'userid_actor' => $Form['userid_actor']
            ),
            true
          );
        }
      }
      else
      {
        //get target user info from database
          $Criteria = new CDbCriteria();
          $Criteria->condition = 'id = :menuid_target';
          $Criteria->params = array(':menuid_target' => Yii::app()->request->getParam('menuid_target'));

          $User = sys_menu::model()->find($Criteria);

        //pass on user's data from table
        $Form->userid_actor = Yii::app()->request->getParam('userid_actor');
        $Form->menuid_target = Yii::app()->request->getParam('menuid_target');
        $Form->title = $User['title'];

        //rendering user edit form, grab the html
        $html = $this->renderPartial(
          'vfrm_menu_edit',
          array(
            'form' => $Form,
            'userid_actor' => $Form['userid_actor']
          ),
          true
        );
      }

      //return the html, wrapped in json format
      echo CJSON::encode(array('html' => $html));
	  }



	/*setting - menu - end*/


	/*setting - application setting - begin*/

	  /*Application Setting*/
	  function actionAppSetting()
	  {
	    $userid_actor = Yii::app()->request->getParam('userid_actor');
      $this->userid_actor = $userid_actor;
      $this->parentmenuid = 1;
      $this->menuid = 17;
      $this->layout = 'layout-baru';
      $this->bread_crumb_list =
        '<li>Setting</li>'.
        '<li>></li>'.
        '<li>Application Setting</li>';

      $idgroup = FHelper::GetGroupId($userid_actor);

      if(FHelper::AllowMenu($this->menuid, $idgroup, 'read'))
      {
        $Criteria = new CDbCriteria();
        $Criteria->group = 'group_name';
        $Criteria->order = 'group_name ASC';
        $SettingGroupList = sys_setting::model()->find($Criteria);
        $GroupName = $SettingGroupList['group_name'];

        $SettingGroupList = sys_setting::model()->findAll($Criteria);
        $SettingGroupListData = CHtml::listData($SettingGroupList, 'set_id', 'group_name');

        $Criteria = new CDbCriteria();
        $Criteria->condition = 'group_name = :group_name';
        $Criteria->params = array(':group_name' => $GroupName);
        $SettingGroupDetail = sys_setting::model()->findAll($Criteria);

        $TheMenu = FHelper::RenderMenu(0, $userid_actor, 1);
        $AppSetting = $this->renderPartial(
          'v_show_appsetting',
          array(
            'GroupName' => $GroupName,
            'SettingGroupDetail' => $SettingGroupDetail,
            'userid_actor' => $userid_actor
          ),
          true
        );

        $this->layout = 'layout-baru';

        $this->render(
          'index_appsetting',
          array(
            'TheMenu' => $TheMenu,
            'SettingGroupList' => $SettingGroupList,
            'SettingGroupListData' => $SettingGroupListData,
            'AppSetting' => $AppSetting,
            'userid_actor' => $userid_actor
          )
        );
      }
      else
      {
        $this->actionShowInvalidAccess($userid_actor, false);
      }


	  }

	  /*Application Setting List*/
	  function actionAppSettingList()
	  {
	    $userid_actor = Yii::app()->request->getParam('userid_actor');
	    $this->userid_actor = $userid_actor;
      $this->parentmenuid = 1;
      $this->menuid = 17;
	    $idgroup = FHelper::GetGroupId($userid_actor);

      if(FHelper::AllowMenu($this->menuid, $idgroup, 'read'))
      {
        $Criteria = new CDbCriteria();
        $Criteria->condition = 'set_id = :settingid_target';
        $Criteria->order = 'group_name ASC';
        $Criteria->params = array(
          ':settingid_target' => Yii::app()->request->getParam('settingid_target')
        );

        $SettingGroupList = sys_setting::model()->find($Criteria);
        $GroupName = $SettingGroupList['group_name'];

        $Criteria = new CDbCriteria();
        $Criteria->condition = 'group_name = :group_name';
        $Criteria->params = array(':group_name' => $GroupName);
        $SettingGroupDetail = sys_setting::model()->findAll($Criteria);

        $TheMenu = FHelper::RenderMenu(0, $userid_actor, 1);
        $html = $this->renderPartial(
          'v_show_appsetting',
          array(
            'GroupName' => $GroupName,
            'SettingGroupDetail' => $SettingGroupDetail,
            'userid_actor' => $userid_actor
          ),
          true
        );

        echo CJSON::encode(array('html' => $html));
      }
      else
      {
        $this->actionShowInvalidAccess($userid_actor, false);
      }



	  }

	  /*Application Setting Edit*/
	  function actionAppSettingEdit()
	  {
	    $userid_actor = Yii::app()->request->getParam('userid_actor');
	    $this->parentmenuid = 1;
      $this->menuid = 17;
	    $idgroup = FHelper::GetGroupId($userid_actor);

      if(FHelper::AllowMenu($this->menuid, $idgroup, 'edit'))
      {
        //instantiate an form object
        $Form = new frmEditAppSetting();

        //periksa apakah submit edit form?
        $test = Yii::app()->request->getParam('frmEditAppSetting');

        //listData untuk setting aplikasi
        $settings = sys_setting::model()->findAll();

        if(isset($test) == true)
        {
          $do_edit = Yii::app()->request->getParam('do_edit');

          $Form->attributes = Yii::app()->request->getParam('frmEditAppSetting');

          if($do_edit == 1)
          {
            Yii::log('processing setting aplikasi edit form submission', 'info');

            //validasi form entry
            if($Form->validate())
            {
              //get target setting info from database
                $Criteria = new CDbCriteria();
                $Criteria->condition = 'set_id = :settingid_target';
                $Criteria->params = array(':settingid_target' => $Form['settingid_target']);

                $sys_setting = sys_setting::model()->find($Criteria);

              //update data user group
                $sys_setting->name = $Form->name;
                $sys_setting->value = $Form->value;
                $sys_setting->update();

              //render 'update berhasil'
              $html = $this->renderPartial(
                'v_appsetting_edit_success',
                array(
                  'form' => $Form
                ),
                true
              );
            }
            else
            {
              //rendering user edit form, grab the html
              $html = $this->renderPartial(
                'vfrm_appsetting_edit',
                array(
                  'form' => $Form,
                  'userid_actor' => $userid_actor
                ),
                true
              );
            }
          }
          else
          {
            //reset view setting aplikasi

            $Criteria = new CDbCriteria();
            $Criteria->order = 'group_name ASC';
            $SettingGroupList = sys_setting::model()->find($Criteria);
            $GroupName = $SettingGroupList['group_name'];

            $Criteria = new CDbCriteria();
            $Criteria->condition = 'group_name = :group_name';
            $Criteria->params = array(':group_name' => $GroupName);
            $SettingGroupDetail = sys_setting::model()->findAll($Criteria);

            $TheMenu = FHelper::RenderMenu(0, $userid_actor, 1);
            $html = $this->renderPartial(
              'v_show_appsetting',
              array(
                'GroupName' => $GroupName,
                'SettingGroupDetail' => $SettingGroupDetail,
                'userid_actor' => $Form['userid_actor']
              ),
              true
            );
          }

        }
        else
        {
          //get target user info from database
            $Criteria = new CDbCriteria();
            $Criteria->condition = 'set_id = :settingid_target';
            $Criteria->params = array(
              ':settingid_target' => Yii::app()->request->getParam('settingid_target')
            );

            $setting = sys_setting::model()->find($Criteria);

          //pass on user's data from table
          $Form->userid_actor = $userid_actor;
          $Form->settingid_target = Yii::app()->request->getParam('settingid_target');
          $Form->name = $setting['name'];
          $Form->value = $setting['value'];

          //rendering user edit form, grab the html
          $html = $this->renderPartial(
            'vfrm_appsetting_edit',
            array(
              'form' => $Form,
            ),
            true
          );
        }

        //return the html, wrapped in json format
        echo CJSON::encode(array('html' => $html));
      }
      else
      {
        $this->actionShowInvalidAccess($userid_actor, false);
      }
	  }



	/*setting - application setting - end*/




	/*setting - company profile - begin*/

	  /*Company Profile Setting*/
	  function actionCompanyProfile()
	  {
	    $this->layout = 'layout-baru';

	    $Criteria = new CDbCriteria();
	    $Criteria->condition = 'branch_parent_id = 0';

      $userid_actor = Yii::app()->request->getParam('userid_actor');

      $this->userid_actor = $userid_actor;
      $this->parentmenuid = 1;
      $this->menuid = 48;
      $this->layout = 'layout-baru';
      $this->bread_crumb_list =
        '<li>Setting</li>'.
        '<li>></li>'.
        '<li>Company Profile</li>';

      $TheMenu = FHelper::RenderMenu(0, $userid_actor, 1);
      $Branch = mtr_branch::model()->find($Criteria);
      $CompanyProfile = $this->renderPartial(
        'v_show_companyprofile',
        array(
          'userid_actor' => $userid_actor,
          'branch' => $Branch,
          'menuid' => $this->menuid
        ),
        true
      );

      $this->render(
        'index_general',
        array(
          'TheMenu' => $TheMenu,
          'TheContent' => $CompanyProfile,
          'userid_actor' => $userid_actor
        )
      );
	  }

	  /*Company Profile Show*/
	  function actionCompanyProfileShow()
	  {
	    $Criteria = new CDbCriteria();
	    $Criteria->condition = 'branch_parent_id = 0';

	    $Branch = mtr_branch::model()->find($Criteria);
	    $AppSettingList = $this->renderPartial(
        'v_show_companyprofile',
        array(
          'userid_actor' => Yii::app()->request->getParam('userid_actor'),
          'branch' => $Branch
        ),
        true
      );

      echo CJSON::encode(array('html' => $AppSettingList));
	  }

	  /*Company Profile Edit*/
	  function actionCompanyProfileEdit()
	  {
	    Yii::log('SettingController::actionCompanyProfileEdit() - begin', 'info');

	    $this->parentmenuid = 1;
      $this->menuid = 48;
      $userid_actor = Yii::app()->request->getParam('userid_actor');

      //instantiate an form object
      $Form = new frmEditCompanyProfile();

      //periksa apakah submit edit form?
      $test = Yii::app()->request->getParam('frmEditCompanyProfile');

      //listData untuk user group
      $settings = mtr_branch::model()->findAll();

      if(isset($test) == true)
      {
        Yii::log('processing user edit form submission', 'info');

        $Form->attributes = Yii::app()->request->getParam('frmEditCompanyProfile');


        $do_edit = Yii::app()->request->getParam('do_edit');

        if($do_edit == 1)
        {
          //validasi form entry
          if($Form->validate())
          {
            Yii::log('company profile edit form submission validated', 'info');

            //get target user info from database
              $Criteria = new CDbCriteria();
              $Criteria->condition = 'branch_parent_id = 0';

              $branch = mtr_branch::model()->find($Criteria);

            //update data user group
              $branch['name'] = $Form['name'];
              $branch['address'] = $Form['address'];
              $branch['phone'] = $Form['phone'];
              $branch['fax'] = $Form['fax'];
              $branch->update();

            //render 'update berhasil'
            /*
            $html = $this->renderPartial(
              'v_companyprofile_edit_success',
              array(),
              true
            );
            */

            $Criteria = new CDbCriteria();
            $Criteria->condition = 'branch_parent_id = 0';

            $Branch = mtr_branch::model()->find($Criteria);
            $html = $this->renderPartial(
              'v_show_companyprofile',
              array(
                'userid_actor' => $userid_actor,
                'branch' => $Branch,
                'menuid' => $this->menuid
              ),
              true
            );
          }
          else
          {
            Yii::log('company profile edit form validation failed', 'info');

            //rendering user edit form, grab the html
            $html = $this->renderPartial(
              'vfrm_companyprofile_edit',
              array(
                'form' => $Form,
                'userid_actor' => $userid_actor
              ),
              true
            );
          }
        }
        else
        {
          $Criteria = new CDbCriteria();
          $Criteria->condition = 'branch_parent_id = 0';

          $Branch = mtr_branch::model()->find($Criteria);
          $html = $this->renderPartial(
            'v_show_companyprofile',
            array(
              'userid_actor' => $userid_actor,
              'branch' => $Branch,
              'menuid' => $this->menuid
            ),
            true
          );
        }
      }
      else
      {
        Yii::log('open form', 'info');

        //get target user info from database
          $Criteria = new CDbCriteria();
          $Criteria->condition = 'branch_parent_id = 0';

          $mtr_branch = mtr_branch::model()->find($Criteria);

        //pass on user's data from table
        $Form->userid_actor = Yii::app()->request->getParam('userid_actor');
        //$Form->branchid_target = Yii::app()->request->getParam('branchid_target');
        $Form['name'] = $mtr_branch['name'];
        $Form['address'] = $mtr_branch['address'];
        $Form['phone'] = $mtr_branch['phone'];
        $Form['fax'] = $mtr_branch['fax'];

        //rendering user edit form, grab the html
        $html = $this->renderPartial(
          'vfrm_companyprofile_edit',
          array(
            'form' => $Form,
            'userid_actor' => $userid_actor
          ),
          true
        );
      }

      //return the html, wrapped in json format
      echo CJSON::encode(array('html' => $html));
	  }



	/*setting - company profile - end*/



	//Hak Akses - begin




	  /*
	    actionHakAkses

	    Deskripsi
	    Fungsi untuk menampilkan interface utama pengaturan Hak Akses

	    Parameter
	    iduser_actor
	      iduser yang melakukan proses
	  */
	  public function actionHakAkses()
	  {
	    $userid_actor = Yii::app()->request->getParam('userid_actor');
	    $this->userid_actor = $userid_actor;
      $this->parentmenuid = 1;
      $this->menuid = 3;
      $this->layout = 'layout-baru';
      $this->bread_crumb_list =
        '<li>Setting</li>'.
        '<li>></li>'.
        '<li>Hak Akses</li>';

      $idgroup = FHelper::GetGroupId($userid_actor);

      if(FHelper::AllowMenu($this->menuid, $idgroup, 'read'))
      {
        $TheMenu = FHelper::RenderMenu(0, $userid_actor, 1);

        $form = new frmEditHakAkses();
        $Criteria = new CDbCriteria();
        if ($idgroup == 1 or $idgroup == 4) {
          $Criteria->condition = "t.is_del = 0";
        } else {
          $Criteria->condition = "t.is_del = 0 AND (t.id <> 1 AND t.id <> 4)";
        }
        $Criteria->order = 'nama asc';
        $user_groups = sys_user_group::model()->findAll($Criteria);
        $GroupList = CHtml::listData($user_groups, 'id', 'nama');

        $HakAksesList = $this->renderPartial(
          'v_list_hakakses',
          array(
            'form' => $form,
            'GroupList' => $GroupList,
            'userid_actor' => $userid_actor,
            'ug_auts' => $ug_auts
          ),
          true
        );

        $this->layout = 'layout-baru';

        $this->render(
          'index_hakakses',
          array(
            'TheMenu' => $TheMenu,
            'TheContent' => $HakAksesList,
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
	    GenerateHakAksesList

	    Deskripsi
	    Fungsi untuk mengehasilkan view list hak akses berdasarkan idgroup dan
	    level untuk dekorasi visual.

	    Parameter
	    idgroup
	      Integer.

      Return
      View daftar hak akses
	  */
	  private function GenerateHakAksesList($idgroupuser, $idgroup, $idparent, $level)
	  {
	    $CriteriaMenu = new CDbCriteria();
      //$CriteriaMenu->condition = 'idparent = :idparent';	 
      if ($idgroupuser == 1 or $idgroupuser == 4) {
        $CriteriaMenu->condition = 'idparent = :idparent';
      } else {
        $CriteriaMenu->condition = 'idparent = :idparent AND t.id NOT IN (8,24,25,29,30,31,29,45,54,55,56,65,66,44,27,28,19)';
      }
      //exit($idgroupuser);		
      $CriteriaMenu->order = 'line_no ASC';
	    $CriteriaMenu->params = array(':idparent' => $idparent);

	    $sys_menu_list = sys_menu::model()->findAll($CriteriaMenu);

	    $Criteria = new CDbCriteria();
	    $Criteria->condition = 'idug = :idgroup  AND idmenumod = :idmenu AND usorgr = "group"';

	    $hasil = '';

	    foreach($sys_menu_list as $sys_menu)
	    {
	      //ambil hak akses menu ini terhadap idgroup

	      $Criteria->params = array(':idgroup' => $idgroup, ':idmenu' => $sys_menu['id']);

	      $ug_auts = sys_ug_aut::model()->count($Criteria);

        if($ug_auts == 0)
        {
          FHelper::InitUserAut($idgroup);
        }

        $ug_auts = sys_ug_aut::model()->findAll($Criteria);

        foreach($ug_auts as $ug_aut)
        {
          $indent = '';

          $levelke = 0;
          for($levelke = 0; $levelke < $level; $levelke++)
          {
            $indent .= ' &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;';
          }

          $hasil .= '<tr>';

          $hasil .= '<td>'.
                      $indent . $sys_menu['title'].
                    '</td>';
          $hasil .= '<td>'.
                      CHtml::hiddenField('ug_aut['.$ug_aut['idmenumod'].'][idmenu]', $ug_aut['idmenumod']) .
                      CHtml::checkBox('ug_aut['. $ug_aut['idmenumod'] . '][is_read]', $ug_aut['is_read']).
                    '</td>';
          $hasil .= '<td>'.CHtml::checkBox('ug_aut['. $ug_aut['idmenumod'] . '][is_write]', $ug_aut['is_write']).'</td>';
          $hasil .= '<td>'.CHtml::checkBox('ug_aut['. $ug_aut['idmenumod'] . '][is_edit]', $ug_aut['is_edit']).'</td>';
          $hasil .= '<td>'.CHtml::checkBox('ug_aut['. $ug_aut['idmenumod'] . '][is_delete]', $ug_aut['is_delete']).'</td>';

          $hasil .= '</tr>';



          $hasil .= $this->GenerateHakAksesList($idgroupuser, $idgroup, $sys_menu['id'], ($level + 1));
        }//loop user_group auth list
	    }//loop menu



	    return $hasil;
	  }

	  /*
	    GenerateEditMenuList

	    Deskripsi
	    Fungsi untuk mengehasilkan view list hak akses berdasarkan idgroup dan
	    level untuk dekorasi visual.

	    Parameter
	    idgroup
	      Integer.

      Return
      View daftar hak akses
	  */
	  private function GenerateEditMenuList($userid_actor, $idparent, $level)
	  {
	    $CriteriaMenu = new CDbCriteria();
	    $CriteriaMenu->condition = 'idparent = :idparent';
	    $CriteriaMenu->params = array(':idparent' => $idparent);

	    $sys_menu_list = sys_menu::model()->findAll($CriteriaMenu);

	    $hasil = '';

	    foreach($sys_menu_list as $sys_menu)
	    {
	      $indent = '';

        $levelke = 0;
        for($levelke = 0; $levelke < $level; $levelke++)
        {
          $indent .= ' &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;';
        }

        $hasil .= '<tr>';

        $hasil .=
          '<td>'. $indent . $sys_menu['title'].'</td>';

        if(FHelper::AllowMenu($this->menuid, FHelper::GetGroupId($userid_actor), 'edit'))
        {
          $hasil .=
            '<td>'.
              '<a href="#" onclick="ShowEditMenu('.$userid_actor.', '.$sys_menu['id'].');"><img class="icon16 fl-space2" src="images/ico_edit_16.png" title="edit"></a>'.
            '</td>';
        }
        else
        {
          $hasil .= '<td></td>';
        }

        $hasil .= '</tr>';

        $hasil .= $this->GenerateEditMenuList($userid_actor, $sys_menu['id'], ($level + 1));
	    }//loop menu



	    return $hasil;
	  }

	  /*
	    actionHakAksesList

	    Deskripsi
	    Action untuk menampilkan daftar hak akses berdasarkan group user yang dipilih.

	    Parameter
	    idgroupuser
	      idgroup yang ditampilkan hak aksesnya

	    iduser_actor
	      iduser yang melakukan proses
	  */
	  public function actionHakAksesList()
	  {
	    $userid_actor = Yii::app()->request->getParam('userid_actor');
	    $this->userid_actor = $userid_actor;
      $this->parentmenuid = 1;
      $this->menuid = 3;
	    $idgroupuser = FHelper::GetGroupId($userid_actor);

      if(FHelper::AllowMenu($this->menuid, $idgroupuser, 'read'))
      {
        $Criteria = new CDbCriteria();
        if ($idgroupuser == 1 or $idgroupuser == 4) {
          $Criteria->condition = "t.is_del = 0";
        } else {
          $Criteria->condition = "t.is_del = 0 AND (t.id <> 1 AND t.id <> 4)";
        }
        $Criteria->order = 'nama asc';
        $user_groups = sys_user_group::model()->findAll($Criteria);
        $GroupList = CHtml::listData($user_groups, 'id', 'nama');

        $form = new frmEditHakAkses();
        $form->attributes = Yii::app()->request->getParam('frmEditHakAkses');

        $idgroup = $form['idgroup'];

        /*
        $Criteria = new CDbCriteria();
        $Criteria->condition = 'idug = :idgroup AND usorgr = "group"';
        $Criteria->params = array(':idgroup' => $idgroup);

        $ug_auts = sys_ug_aut::model()->count($Criteria);

        if($ug_auts == 0)
        {
          Yii::log('form[\'idgroup\'] = ' . $idgroup, 'info');
          Yii::log('userid_actor = ' . $userid_actor, 'info');

          FHelper::InitUserAut($form['idgroup']);
        }

        $ug_auts = sys_ug_aut::model()->findAll($Criteria);
        */

        $hak_akses_list = '';
        $hak_akses_list = $this->GenerateHakAksesList($idgroupuser, $idgroup, 0, 0);

        $html = $this->renderPartial(
          'v_list_hakakses',
          array(
            'form' => $form,
            'GroupList' => $GroupList,
            'idgroup' => $idgroup,
            'ug_auts' => $ug_auts,
            'hak_akses_list' => $hak_akses_list,
            'userid_actor' => $userid_actor
          ),
          true
        );

        echo CJSON::encode(array('html' => $html));
      }
      else
      {
        $this->actionShowInvalidAccess($userid_actor, false);
      }
	  }

	  /*
	    actionHakAksesUpdate

	    Deskripsi
	    Action untuk menyimpan perubahan hak akses berdasarkan group user yang dipilih.

	    Parameter
	    idgroupuser
	      idgroup yang diupdate hak aksesnya

	    iduser_actor
	      iduser yang melakukan proses
	  */
	  public function actionHakAksesUpdate()
	  {
	    $userid_actor = Yii::app()->request->getParam('userid_actor');
	    $this->userid_actor = $userid_actor;
      $this->parentmenuid = 1;
      $this->menuid = 3;
	    $idgroup = FHelper::GetGroupId($userid_actor);

      if(FHelper::AllowMenu($this->menuid, $idgroup, 'edit'))
      {
        $form = new frmEditHakAkses();
        $form->attributes = Yii::app()->request->getParam('frmEditHakAkses');

        //proses hak akses
        $ug_auts = Yii::app()->request->getParam('ug_aut');

        $Criteria = new CDbCriteria();
        $Criteria->condition = 'idug = :idgroup AND idmenumod = :idmenumod';

        foreach($ug_auts as $idmenumod => $selections)
        {
          $Criteria->params = array(
            ':idgroup' => $form['idgroup'],
            ':idmenumod' => $idmenumod
          );

          $ug_aut = sys_ug_aut::model()->find($Criteria);

          $ug_aut['is_read'] = isset($selections['is_read']) == true ? 1 : 0;
          $ug_aut['is_write'] = isset($selections['is_write']) == true ? 1 : 0;
          $ug_aut['is_edit'] = isset($selections['is_edit']) == true ? 1 : 0;
          $ug_aut['is_delete'] = isset($selections['is_delete']) == true ? 1 : 0;

          $ug_aut->update();
        }
      }
      else
      {
        $this->actionShowInvalidAccess($userid_actor, false);
      }


	    echo CJSON::encode(array('' => ''));
	  }

	//Hak Akses - end

	/*setting - penempatan user - begin*/

	  /*
	    actionPenempatanShowList

	    Deskripsi
	    Fungsi untuk menampilkan daftar user
	  */
	  public function actionPenempatanShowList()
	  {
	    $iduser = Yii::app()->request->cookies['userid_actor']->value;
	    $this->userid_actor = $iduser;
	    $idgroup = FHelper::GetGroupId($iduser);
	    $idmenu = 67;

	    if( FHelper::AllowMenu($idmenu, $idgroup, 'read') )
	    {
	      //ambil daftar user
        $command = Yii::app()->db->createCommand()
          ->select('sys_user.id, sys_user.nama as nama, grup.nama as grup, branch.name as lokasi')
          ->from('sys_user')
          ->join('mtr_branch branch', 'sys_user.id_location = branch.branch_id')
          ->join('sys_user_group grup', 'sys_user.idgroup = grup.id')
          ->where(
            'sys_user.is_inactive = 0 AND
            sys_user.is_del = 0');
        $daftar_user = $command->queryAll();

        $this->layout = 'layout-baru';
        $list = $this->renderPartial(
          'v_list_penempatan_user',
          array(
            'daftar_user' => $daftar_user,
            'daftar_lokasi' => FHelper::GetLocationListData(false)
          ),
          true
        );

        $TheMenu = FHelper::RenderMenu(0, $userid_actor, 1);

        $this->render(
          'index_general',
          array(
            'TheMenu' => $TheMenu,
            'TheContent' => $list
          )
        );
	    }
	    else
	    {
	      //redirect
	      $this->redirect('?r=index/showinvalidaccess');
	    }
	  }

	  /*
	    actionPenempatanEdit

	    Deskripsi
	    Fungsi untuk menampilkan form edit penempatan user
	  */
	  public function actionPenempatanEdit()
	  {
	    $iduser = Yii::app()->request->getParam('iduser');
	    $this->userid_actor = Yii::app()->request->cookies['userid_actor']->value;
	    $idgroup = FHelper::GetGroupId($this->userid_actor);
	    $idmenu = 67;

	    if( FHelper::AllowMenu($idmenu, $idgroup, 'read') )
	    {
	      //ambil nama dan idlokasi
        $command = Yii::app()->db->createCommand()
          ->select('*')
          ->from('sys_user')
          ->where('id = :iduser', array(':iduser' => $iduser));
        $user = $command->queryRow();

        echo CJSON::encode(array(
          'status' => 'ok',
          'id' => $user['id'],
          'namauser' => $user['nama'],
          'idlokasi' => $user['id_location'])
        );
	    }
	    else
	    {
	      //redirect
	    }
	  }

	  /*
	    actionPenempatanSubmit

	    Deskripsi
	    Fungsi untuk menerima perubahan penempatan user
	  */
	  public function actionPenempatanSubmit()
	  {
	    $iduser = Yii::app()->request->cookies['userid_actor']->value;
	    $this->userid_actor = $iduser;
	    $idgroup = FHelper::GetGroupId($iduser);
	    $idmenu = 67;

	    if( FHelper::AllowMenu($idmenu, $idgroup, 'read') )
	    {
	      $iduser = Yii::app()->request->getParam('iduser');
        $idlokasi = Yii::app()->request->getParam('idlokasi');

        //mengupdate data penempatan
        $hasil = $command = Yii::app()->db->createCommand()
          ->update(
            'sys_user',
            array(
              'id_location' => $idlokasi
            ),
            'id = :iduser',
            array(':iduser' => $iduser)
          );
          
        $userid_actor = Yii::app()->request->cookies['userid_actor']->value;
        $nama_pelaku = FHelper::GetUserName($userid_actor);
        $nama_karyawan = FHelper::GetUserName($iduser);
        $nama_cabang = FHelper::GetLocationName($idlokasi, true);
        
        FAudit::add(
          'Setting::PenempatanSubmit',
          'submit',
          "{$nama_pelaku}",
          "karyawan = {$nama_karyawan}, lokasi_baru = {$nama_cabang}"
        );
          
        if($hasil == 1)
        {
          $status = 'ok';
        }
        else
        {
          $status = 'not ok';
        }

        echo CJSON::encode(array('status' => $status));
	    }
	    else
	    {
	      //redirect
	    }
	  }


	/*setting - penempatan user - end*/


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