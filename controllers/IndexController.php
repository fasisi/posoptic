<?php

class IndexController extends FController
{  
	public function filters()
	{
		return array(
			array('application.filters.CheckSessionFilter - index'),
			array('application.filters.CheckLokasiUserFilter - index, showinvalidlocation')
		);
	}
  
  public function actionIndex()
	{
	  $this->layout = 'outside';
	  
	  //Periksa apakah ada record dalam tabel sys_user.
	  if($this->CekRecordPengguna() == false)
	  {
	    $frmAdminBaru = new frmAdminBaru();
	    
	    //apakah submit frmAdminBaru??
	    if(Yii::app()->request->getParam('frmAdminBaru') != NULL)
	    {
	      $frmAdminBaru->attributes = Yii::app()->request->getParam('frmAdminBaru');
	      
	      if($frmAdminBaru->validate())
	      {
	        //simpan admin pertama ke tabel sys_user
	        $this->SimpanAdminBaru();
	        
	        //tampilkan interface login
	        $form = new frmLogin();
        
          $this->layout = 'outside';
          $this->render(
            'vfrm_login',
            array(
              'form' => $form
            )
          );
	      }
	      else
	      {
	        //tampilkan error message
	        $this->layout = 'outside';
	        $this->render(
            'vfrm_adminbaru',
            array(
              'form' => $frmAdminBaru
            )
          );
	      }
	    }
	    else
	    {
	      //jika tidak ada, tampilkan interface untuk membuat user administrator pertama
	      $this->layout = 'outside';
	      $this->render(
	        'vfrm_adminbaru',
	        array(
	          'form' => $frmAdminBaru
          )
        );
	    }
	  }
	  else
	  {
	    $daftar_lokasi = FHelper::GetLocationListData(false);
	    //unset($daftar_lokasi[0]);
	    
	    //periksa, apakah submit form login?
	    if(Yii::app()->request->getParam('frmLogin') != NULL)
	    {
	      $form = new frmLogin();
	      $form->attributes = Yii::app()->request->getParam('frmLogin');
	      
	      //validasi login
	      if($form->validate_login() == true)
	      {
	        $this->userid_actor = $form->userid;
	        $this->idlokasi = $form->idlokasi;
	        
	        //tampilkan tampilan utama setelah login
	        $this->TampilanUtama($form->userid, $form->idlokasi);
	      }
	      else
	      {
	        //jika login salah, tampilkan interface login
	        
	        $form->addErrors(array(
	            'username' => 'Username, password dan lokasi tidak cocok',
	            'password' => '',
	            'idlokasi' => ''
	          )
          );
	        
          $this->layout = 'outside';
          $this->render(
            'vfrm_login',
            array(
              'form' => $form,
              'daftar_lokasi' => $daftar_lokasi
            )
          );
	      }
	    }
	    else
	    {
	      //jika ada record dalam tabel pengguna, tampilkan interface login
	    
        $form = new frmLogin();
        
        $this->layout = 'outside';
        $this->render(
          'vfrm_login',
          array(
            'form' => $form,
            'daftar_lokasi' => $daftar_lokasi
          )
        );
	    }
	    
	    
	  }
	}
	
	public function actionHome()
	{
		//tampilkan tampilan utama setelah login
		$userid = Yii::app()->request->cookies['userid_actor']->value;

		$UserLoginInfo = FHelper::GetUserLoginInfo($userid);
		$UsersOnline = FHelper::GetUserOnlineInfo();

		$idgroup = $UserLoginInfo['idgroup'];

		$this->idlokasi = Yii::app()->request->cookies['idlokasi']->value;
		$this->userid_actor = $userid;
		$this->menuid = 46;
		$this->parentmenuid = 46;
		$this->bread_crumb_list =
		'<li>Home</li>';

		$themenu = FHelper::RenderMenu(0, $userid);

		$BeritaInformasi = FHelper::GetBeritaInformasi();

		$month = date("m");
		$year = date("Y");

		if (date("l") != "Sunday") $date_from = date("Y-m-d 00:00:00", strtotime("last sunday"));
		else $date_from = date("Y-m-d 00:00:00");

		if (date("l") != "Saturday") $date_to = date("Y-m-d 23:59:59", strtotime("next saturday"));
		else $date_to = date("Y-m-d 23:59:59");

		$ItemMonthly = FHelper::GetPenjualanItemTertinggiBulanan($month, $year, 15);
		$ItemWeekly = FHelper::GetPenjualanItemTertinggiTgl($date_from, $date_to, 15);

		$SalesOutstandingRec = FHelper::GetSalesOutstanding(Yii::app()->request->cookies['idlokasi']->value);

		$this->layout = 'layout-baru';
		$this->render(
			'main',
			array(
				'TheMenu' => $themenu,
				'userid_actor' => $userid,
				'idgroup' =>  $idgroup,
				'BeritaInformasi' => $BeritaInformasi,
				'UserLoginInfo' => $UserLoginInfo,
				'UsersOnline' => $UsersOnline,
				'ItemMonthly' => $ItemMonthly,
				'ItemWeekly' => $ItemWeekly,
				'SalesOutstandingRec' => $SalesOutstandingRec
			)
		);
	}

	public function actionChat()
	{
		$userid = Yii::app()->request->getParam('userid_actor');
		$username = FHelper::GetUserName($userid);
		$this->redirect('http://jhmoriska.com/apps/chat1/simple.php?u='.$username);
	}

	public function actionLogout()
	{
		Yii::log('IndexController::actionLogout >> destroy session', 'info');
		Yii::app()->session->destroy(Yii::app()->session->sessionID);

		Yii::log('IndexController::actionLogout >> clear cookies', 'info');
		Yii::app()->request->cookies->clear();

		//melakukan logout
		$userid = Yii::app()->request->getParam('userid_actor');
		$daftar_lokasi = FHelper::GetLocationListData(false);
		//unset($daftar_lokasi[0]);

		$this->LogoutTheUser($userid);

		$this->redirect(
			array(
				'index/index'
			)
		);
	}
	
	private function TampilanUtama($userid, $idlokasi)
	{
		Yii::log('idlokasi = ' . $idlokasi, 'info');


		//tampilkan tampilan utama setelah login

		$this->userid_actor = $userid;
		$this->idlokasi = $idlokasi;
		$this->menuid = 46;
		$this->parentmenuid = 46;
		$this->bread_crumb_list =
		'<li>Home</li>';

		//bikin sessionID
		$sessionID = Yii::app()->session->sessionID;

		//jodohkan user dengan sessionID
		Yii::app()->db->createCommand()
		->update(
			'sys_user',
			array(
				'sessionid' => $sessionID
			),
			'id = :iduser',
			array(':iduser' => $userid)
		);

		//bikin cookie
		$Cookie = new CHttpCookie('userid_actor', $userid);
		Yii::app()->request->cookies->add('userid_actor', $Cookie);

		$Cookie = new CHttpCookie('idlokasi', $idlokasi);
		Yii::app()->request->cookies->add('idlokasi', $Cookie);
		//bikin cookie

		$themenu = FHelper::RenderMenu(0, $this->userid_actor, $this->idlokasi);

		$UserLoginInfo = FHelper::GetUserLoginInfo($userid);
		$UsersOnline = FHelper::GetUserOnlineInfo();

		$idgroup = $UserLoginInfo['idgroup'];

		$BeritaInformasi = FHelper::GetBeritaInformasi();
		$month = date("m");
		$year = date("Y");

		if (date("l") != "Sunday") $date_from = date("Y-m-d 00:00:00", strtotime("last sunday"));
		else $date_from = date("Y-m-d 00:00:00");

		if (date("l") != "Saturday") $date_to = date("Y-m-d 23:59:59", strtotime("next saturday"));
		else $date_to = date("Y-m-d 23:59:59");

		$ItemMonthly = FHelper::GetPenjualanItemTertinggiBulanan($month, $year, 15);
		$ItemWeekly = FHelper::GetPenjualanItemTertinggiTgl($date_from, $date_to, 15);

		$SalesOutstandingRec = FHelper::GetSalesOutstanding(Yii::app()->request->cookies['idlokasi']->value);
		
		$this->layout = 'layout-baru';
		$this->render(
			'main',
			array(
				'TheMenu' => $themenu,
				'userid_actor' => $userid,
				'idgroup' =>  $idgroup,
				'BeritaInformasi' => $BeritaInformasi,
				'UserLoginInfo' => $UserLoginInfo,
				'UsersOnline' => $UsersOnline,
				'ItemMonthly' => $ItemMonthly,
				'ItemWeekly' => $ItemWeekly,
				'SalesOutstandingRec' => $SalesOutstandingRec
			)
		);
	}
	
	private function RenderMenu($idparent)
	{
	  $Criteria = new CDbCriteria();
	  $Hasil = '';
	  
	  $Criteria->condition = 'idparent = :idparent AND is_hidden = 0';
	  $Criteria->params = array(':idparent' => $idparent);
	  
	  $rows = sys_menu::model()->count($Criteria);
	  
	  if($rows > 0)
	  {
	    $rows = sys_menu::model()->findAll($Criteria);
	  
      //$Hasil .= '<ul>';
      
      foreach($rows as $row)
      {
        $Hasil .= '<li>';
        $Hasil .= '<a href="'.$row['url'].'">'.$row['title'].'</a>';
        $Temp = $this->RenderMenu($row['id']);
        
        if(strlen($Temp) > 0)
        {
          $Hasil .= '<ul>' . $Temp . '</ul>';
        }
        
        $Hasil .= '</li>';
      }
      
      //$Hasil .= '</ul>';
	  }
	  
	  return $Hasil;
	}
	
	private function SimpanAdminBaru()
	{
	  $pengguna = new sys_user();
	  $group_pengguna = new sys_user_group();
	  $Criteria = new CDbCriteria();
	  
	  $Criteria->condition = 'nama = "Administrator"';
	  $group_pengguna = sys_user_group::model()->find($Criteria);
	  
	  $params = Yii::app()->request->getParam('frmAdminBaru'); 
	  
	  $pengguna->username = $params['username'];
	  $pengguna->nama = $params['nama'];
	  $pengguna->password = sha1('123' . $params['password'] . '123');
	  $pengguna->idgroup = $group_pengguna['id'];
	  $pengguna->insert();
	}
	
	public function actionShowInvalidAccess()
	{
	  $userid_actor = Yii::app()->request->cookies['userid_actor']->value;
	  $this->userid_actor = $userid_actor;
	  $this->menuid = 46;
    $this->parentmenuid = 46;
	  
	  $this->layout = 'layout-baru';
	  
	  $this->render(
	    'v_not_auth',
	    array('userid_actor' => $userid_actor)
    );
	}
	
	public function actionShowInvalidLocation()
	{
	  $userid_actor = Yii::app()->request->getParam('userid_actor');
	  $this->userid_actor = $userid_actor;
	  $this->menuid = 46;
    $this->parentmenuid = 46;
	  
	  $this->layout = 'outside';
	  
	  $this->render(
	    'v_location_invalid',
	    array('userid_actor' => $userid_actor)
    );
	}
	
	public function actionAbsensi()
	{
	  $form = new frmAbsensi();
	  $userid = Yii::app()->request->getParam('userid_actor');
	  $this->userid_actor = $userid;
    $this->menuid = 46;
    $this->parentmenuid = 46;
    $this->bread_crumb_list =
      '<li>Home</li>'.
      '<li>></li>'.
      '<li>Absensi</li>';
	  
	  //tampilkan interface absen
	  $this->layout = 'layout-baru';
	  
	  $FormAbsensi = $this->renderPartial(
      'vfrm_absensi',
      array(
        'form' => $form
      ),
      true
    );
    
    $themenu = FHelper::RenderMenu(0, $userid);
    
    $this->render(
      'index_absensi',
      array(
        'TheContent' => $FormAbsensi,
        'TheMenu' => $themenu,
        'userid_actor' => $userid
      )
    );
    
    //AuditLog
		$data = 
		  'userid = '.$userid;

		FAudit::add(
		  'Index::actionAbsensi', 
		  'View', 
		  FHelper::GetUserName($userid), 
		  $data
    );
	}
	
	public function actionSubmitAbsensi()
	{
	  //proses absen form submission
    $form = new frmAbsensi();
    $form->attributes = Yii::app()->request->getParam('frmAbsensi');
    
    if($form->validate())
    {
      //periksa validitas username vs password
      $Criteria = new CDbCriteria();
      $Criteria->condition = 'username = :username';
      $Criteria->params = array(':username' => $form['username']);
      
      $user = sys_user::model()->count($Criteria);

      if($user == 1)
      {
        //periksa apakah passwordnya cocok
        
        $Criteria->condition = 'username = :username';
        $Criteria->params = array(':username' => $form['username']);
        $user = sys_user::model()->find($Criteria);
        
        $password_temp = $form['password'];
        $password_temp = sha1('123' . $form['password'] . '123');
        
        if($password_temp == $user['password'])
        {
          //username dan password cocok
          
          //AuditLog
          $data = 
            'username = '.$form['username']. ', ' .
            'password = ' . $form['password'];
      
          FAudit::add(
            'Index::actionSubmitAbsensi', 
            'Insert', 
            FHelper::GetUserName($user['id']), 
            $data
          );
          
          //catat ke database
          $iduser = (int)$user['id'];
          $keluarmasuk = (int)$form['keluarmasuk'];
          $tahun = (int) date('Y');
          $bulan = (int) date('m');
          $tanggal = (int) date('d');
          
          $idlokasi = Yii::app()->request->cookies['idlokasi']->value;
          
          $Criteria = new CDbCriteria();
          $Criteria->condition = '
            tahun = :tahun AND
            bulan = :bulan AND
            tanggal = :tanggal AND
            keluarmasuk = :keluarmasuk AND
            iduser = :iduser AND
            idlokasi = :idlokasi';
          $Criteria->params = array(
            ':tahun' => $tahun,
            ':bulan' => $bulan,
            ':tanggal' => $tanggal,
            ':keluarmasuk' => $keluarmasuk,
            ':iduser' => $iduser,
            ':idlokasi' => $idlokasi);
          $jumlah = absensi::model()->count($Criteria);
          
          if($jumlah == 0)
          {
            //insert
            $absensi = new absensi();
            $absensi['tahun'] = $tahun;
            $absensi['bulan'] = $bulan;
            $absensi['tanggal'] = $tanggal;
            $absensi['keluarmasuk'] = $keluarmasuk;
            $absensi['iduser'] = $iduser;
            $absensi['idlokasi'] = $idlokasi;
            $absensi['waktu'] = date('Y-m-j H:i:s');
            
            $absensi->save();
          }
          else
          {
            //update
            $absensi = absensi::model()->find($Criteria);
            $absensi['waktu'] = date('Y-m-j H:i:s');
            
            $absensi->save();
          }
          
          $html = $this->renderPartial(
            'v_absensi_success',
            array(
              'userid_actor' => $user['id']
            ),
            true
          );
        }
        else
        {
          //password tidak cocok. tampilkan pesan kesalahan
          
          $form->addError('password', 'Password tidak ditemukan');
        
          $html = $this->renderPartial(
            'vfrm_absensi',
            array(
              'form' => $form
            ),
            true
          );
        } //check password
      } //username check
      else
      {
        //tidak menemukan username. report kesalahan
        
        $form->addError('username', 'Username tidak ditemukan');
        
        $html = $this->renderPartial(
          'vfrm_absensi',
          array(
            'form' => $form
          ),
          true
        );
      } //username check
    }  //validating form submission
    else
    {
      //form validation failed
      
      $html = $this->renderPartial(
        'vfrm_absensi',
        array(
          'form' => $form
        ),
        true
      );
    } //validating form submission
	  
	  echo CJSON::encode(array('html' => $html));
	}
	
	/*
	  CekRecordPengguna()
	  
	  Deskripsi
	  Fungsi untuk memeriksa apakah ada record dalam tabel sys_user.
	*/
	private function CekRecordPengguna()
	{
	  $Criteria = new CDbCriteria();
	  
	  $pengguna = sys_user::model()->count();
	  
	  return $pengguna > 0;
	}
	
	public function actionMenu()
	{
	  if($this->CekValiditas())
	  {
	  }
	  else
	  {
	    //tidak valid. logout-kan user
	    $this->LogoutTheUser();
	  }
	  
	  echo CJSON::encode(
	    array('status' => 'not ok')
    );
	}
	
	/*
	  LogoutTheUser
	  
	  Deskripsi
	  Fungsi untuk me-logout-kan user yang sudah tidak valid (lastactivity > 60 menit)
	  
	  Parameter
	  userid
	*/
	private function LogoutTheUser($userid)
	{
	  $Criteria = new CDbCriteria();
	  $Criteria->condition = 'id = :userid';
	  $Criteria->params = array(
	    ':userid' => $userid
    );
    $pengguna = sys_user::model()->find($Criteria);
    
    $pengguna->is_login = 0;
	$pengguna->last_logout = date("Y-m-d H:i:s");
    $pengguna->last_activity = date("Y-m-d H:i:s");
    $pengguna->update();
    
    //AuditLog
    $data = 
      'username = '.$form['username']. ', ' .
      'password = ' . $form['password'];

    FAudit::add(
      'Index::LogoutTheUser', 
      'Insert', 
      FHelper::GetUserName($userid), $data
    );
	}
	
	/*
	  CekValiditas
	  
	  Deskripsi
	  Fungsi untuk memastikan bahwa setiap menu yang dipanggil, dilakukan oleh
	  user yang sudah login dan aktifitas terakhir <= 60 menit.
	  
	  Parameter
	  userid
	*/
	private function CekValiditas()
	{
	  //pastikan user sedang login
	  $Criteria = new CDbCriteria();
	  $Criteria->condition = 'id = :userid';
	  $Criteria->params = array(
	    ':userid' => Yii::app()->request->getParam('userid')
    );
    $pengguna = sys_user::model()->find($Criteria);
    $islogin = $pengguna->islogin;
	  
	  //pastikan aktifitas terakhir <= 60 menit
	  $selisih = mktime() - strtotime($pengguna->lastactivity); //values are in seconds
	  
	  return $islogin == 1 && $selisih <= 60 * 1;
	}
	
	/*
	  actionUserProfile
	  
	  Deskripsi
	  Action untuk menampilkan data user profile
	  
	  Parameter
	  userid_actor
	  
	  Return
	  View form user profile
	*/
	public function actionUserProfile()
	{
	  $userid_actor = Yii::app()->request->getParam('userid_actor');
	  $this->userid_actor = $userid_actor;
    $this->parentmenuid = 46;
    $this->menuid = 3;
    $this->layout = 'layout-baru';
    $this->bread_crumb_list =
      '<li>Home</li>'.
      '<li>></li>' .
      '<li>User Profile</li>';
	  
	  //listData untuk user group
    $groups = sys_user_group::model()->findAll();
    $listUserGroup = CHtml::listData($groups, 'id', 'nama');
    
    //listData untuk lokasi
    $branches = mtr_branch::model()->findAll();
    $listBranches = CHtml::listData($branches, 'branch_id', 'name');
    
    //listData untuk lokasi
    $karyawan = sys_karyawan::model()->findAll();
    $listKaryawan = CHtml::listData($karyawan, 'id', 'nama');
	  
	  $Criteria = new CDbCriteria();
	  $Criteria->condition = 'id = :userid_actor';
	  $Criteria->params = array(':userid_actor' => $userid_actor);
	  
	  //ambil record user
	  $sys_user = sys_user::model()->find($Criteria);
	  
	  $form = new frmEditUser();
	  $form['userid_actor'] = $userid_actor;
	  $form['username'] = $sys_user['username'];
	  $form['nama'] = $sys_user['nama'];
	  $form['hpnumber'] = $sys_user['hp_number'];
	  $form['email'] = $sys_user['email'];
	  $form['idgroup'] = $sys_user['idgroup'];
	  $form['idlokasi'] = $sys_user['id_location'];
	  $form['idkaryawan'] = $sys_user['id_karyawan'];
	  $form['foto'] = $sys_user['foto'];
	  
	  $form_user_profile = $this->renderPartial(
	    'vfrm_user_profile',
	    array(
	      'form' => $form,
	      'userid_actor' => $userid_actor,
	      'listUserGroup' => $listUserGroup,
        'listBranches' => $listBranches,
        'listKaryawan' => $listKaryawan
      ),
      true
    );
    
    
    $themenu = FHelper::RenderMenu(0, $userid_actor);
    
    $this->layout = 'layout-baru';
    $this->render(
      'index_general',
      array(
        'TheContent' => $form_user_profile,
        'TheMenu' => $themenu,
        'userid_actor' => $userid_actor
      )
    );
    
    //AuditLog
    $data = 
      'username = '.$sys_user['username']. ', ' .
      'nama = ' . $sys_user['nama']. ', '.
      'hpnumber = ' . $sys_user['hp_number'] . ', ' .
      'email = ' . $sys_user['email'] . ', ' .
      'idgroup = ' . $sys_user['idgroup'] . ', ' .
      'idlokasi = ' . $sys_user['id_location'] . ', ' .
      'idkaryawan = ' . $sys_user['id_karyawan'] . ', ' .
      'foto = ' . $sys_user['foto'];

    FAudit::add(
      'Index::actionUserProfile', 
      'View', 
      FHelper::GetUserName($userid_actor), $data
    );
    
	}
	
	/*
    actionEditUserProfile()
    
    Deskripsi
    Action untuk menampilkan interface Create User dan menangani form submission.
  */
  public function actionEditUserProfile()
  {
    $Form = new frmEditUser();
    
    //var_dump($_FILES);
    
    //memproses form submission
    $Form->attributes = Yii::app()->request->getParam('frmEditUser');
    $Form['foto'] = CUploadedFile::getInstance($Form, 'foto');
    
    $Criteria = new CDbCriteria();
    $Criteria->condition = 'id = :id';
    $Criteria->params = array(':id' => $Form['userid_actor']);
    $User = sys_user::model()->find($Criteria);
    
    if($Form->validate(array('nama', 'hpnumber', 'email', 'foto')))
    {
      //proses record ke tabel
      
      $User['nama'] = $Form['nama'];
      $User['hp_number'] = $Form['hpnumber'];
      $User['email'] = $Form['email'];
      
      if($Form['foto'] != NULL)
      {
        $file_name =  $User['username'] . '-' . $Form['foto']->name;
        $User['foto'] = $file_name;
      }
      else
      {
        if($User['foto'] == '')
        {
          $file_name = '';
        }
        else
        {
          $file_name = $User['foto'];
        }
        
      }
      
      Yii::log('filename = ' . $filename, 'info');
      
      $User->save();
      
      //AuditLog
      $data = 
        'nama = ' . $Form['nama'] . ', ' .
        'hp_number = ' . $Form['hpnumber'] . ', ' .
        'email = ' . $Form['email'];
  
      FAudit::add(
        'Index::UserProfile', 
        'Edit', 
        FHelper::GetUserName($Form['userid_actor']), $data
      );
      
      //simpan file foto
      $Form['foto'] = CUploadedFile::getInstance($Form, 'foto');
      
      if($Form['foto'] != NULL)
      {
        $Form['foto']->saveAs(
          Yii::app()->basePath . 
          DIRECTORY_SEPARATOR . '..' .
          DIRECTORY_SEPARATOR . 'images' .
          DIRECTORY_SEPARATOR .'user_images' . 
          DIRECTORY_SEPARATOR . $file_name
        );
      }
      
      //tampilkan informasi sukses menambah user
      $html = $this->renderPartial(
        'v_userprofileedit_success',
        array(
          'form' => $Form,
          'filename' => $file_name
        ),
        true
      );
    }
    else
    {
      //gagal validasi form submission
      $Form['foto'] = $User['foto'];
      $html = $this->renderPartial(
        'vfrm_edit_user_profile',
        array(
          'form' => $Form,
          'listKaryawan' => $listKaryawan
        ),
        true
      );
    }
    
    $foto_profile = FHelper::GetProfilePicture($Form['userid_actor']);
    
    echo CJSON::encode(
      array(
        'html' => $html, 
        'foto_profile' => $foto_profile
      )
    );
  }

  public function actionEditPassword()
  {
    $userid_actor = Yii::app()->request->cookies['userid_actor']->value;
    $passwordlama = Yii::app()->request->getParam('passwordlama');
    $passwordbaru1 = Yii::app()->request->getParam('passwordbaru1');
    $passwordbaru2 = Yii::app()->request->getParam('passwordbaru2');
    
    //periksa password lama. pastikan sama dengan yang tersimpan di tabel.
    $command = Yii::app()->db->createCommand()
      ->select('*')
      ->from('sys_user')
      ->where(
        'id = :iduser AND
        password = :password', 
        array(
          ':iduser' => $userid_actor,
          ':password' => sha1('123' . $passwordlama . '123')
        ));
    $user = $command->queryRow();
    
    if( $user != false )
    {
      //periksa apakah passwordbaru1 == passwordbaru2
      if( $passwordbaru1 == $passwordbaru2 && $passwordbaru1 != '' )
      {
        $hasil = Yii::app()->db->createCommand()
          ->update(
            'sys_user',
            array(
              'password' => sha1('123' . $passwordbaru1 . '123')
            ),
            'id = :iduser',
            array(':iduser' => $userid_actor)
          );
          
        if($hasil == 1)
        {
          $status = 'ok';
          $pesan = 'Berhasil mengganti password';
        }
        else
        {
          $status = 'not ok';
          $pesan = 'Ada kesalahan dalam mengganti password';
        }
        
      }
      else
      {
        $status = 'not ok';
        $pesan = 'Password baru belum diketik ulang dengan benar.';
      }
    }
    else
    {
      $status = 'not ok';
      $pesan = 'Password lama tidak cocok';
    }
    
    echo CJSON::encode(array('status' => $status, 'pesan' => $pesan));
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