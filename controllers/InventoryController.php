<?php

class InventoryController extends FController
{
  
  public function filters()
  {
    $this->userid_actor = Yii::app()->request->cookies['userid_actor']->value;
    $this->idlokasi = Yii::app()->request->cookies['idlokasi']->value;
    
    return array(
      array('CheckSessionFilter')
    );
  }
  
	public function actionIndex()
	{
		$this->render('index');

	}

	
	//inventory - purchase order - begin
	
	     /*
	          actionPurchaseOrder
	          
	          Deskripsi
	          Action untuk menampilkan daftar purchase order. Secara default
	          dipanggil dari menu item.
	          
	     */
	     public function actionPurchaseOrder()
          {
               $this->layout = 'layout-baru';
     
               $Criteria = new CDbCriteria();
               $Criteria->condition = 'is_del = 0 AND idstatus in (1, 2)';
     
               $userid_actor = Yii::app()->request->getParam('userid_actor');
               $this->idlokasi = Yii::app()->request->cookies['idlokasi']->value;
               $this->userid_actor = $userid_actor;
               $this->menuid = 34;
               $this->parentmenuid = 9;
               $this->bread_crumb_list = 
                  '<li>Inventory</li>'.
                  '<li>></li>'.
                  '<li>Purchase Order</li>';
     
               //$TheMenu = FHelper::RenderMenu(0, $userid_actor, $this);
               $PO = inv_po_ro::model()->findAll($Criteria);
               $POList = $this->renderPartial(
                    'v_list_purchaseorder',
                    array(
                         'purchase_order_list' => $PO,
                         'userid_actor' => $userid_actor
                    ),
                    true
               );
     
               $this->render(
                    'index_general',
                    array(
                         'TheMenu' => $TheMenu,
                         'TheContent' => $POList,
                         'userid_actor' => $userid_actor
                    )
               );
          }
	
          /*
               actionPurchaseOrderList
               
               Deskripsi
               Action untuk menampilkan daftar purchase order.
          */
          public function actionPurchaseOrderList()
          {
               $Criteria = new CDbCriteria();
               $Criteria->condition = 'is_del = 0 AND idstatus in (1, 2)';
     
               $userid_actor = Yii::app()->request->getParam('userid_actor');
               
               $this->userid_actor = $userid_actor;
               $this->menuid = 34;
               $this->parentmenuid = 9;
               $this->layout = 'layout-baru';
     
               $TheMenu = FHelper::RenderMenu(0, $userid_actor, 9);
               $PO = inv_po_ro::model()->findAll($Criteria);
               $html = $this->renderPartial(
                    'v_list_purchaseorder',
                    array(
                         'purchase_order_list' => $PO,
                         'userid_actor' => $userid_actor
                    ),
                    true
               );
               
               echo CJSON::encode(array('html' => $html));
          }
     
          /*
               actionPurchaseOrderAdd
     
               Deskripsi
               Action untuk menampilkan dan mengolah purchase order form
               submission.
               
          */
          public function actionPurchaseOrderAdd()
          {
               $userid_actor = Yii::app()->request->getParam('userid_actor');
               $do_add = Yii::app()->request->getParam('do_add');
               $supplier_list = FHelper::GetSupplierLIstData();
               $DaftarMetodeBayar = FHelper::GetMetodeBayarListData();
               $DaftarBank = FHelper::GetBankListData();
     
               if(isset($do_add))
               {
                    //periksa for submission
     
                    if($do_add == 1)
                    {
                         //lakukan proses form submission
     
                         //ambil data purchase order
                         $form = new frmEditPurchaseOrder();
                         $form->attributes = Yii::app()->request->getParam('frmEditPurchaseOrder');
                         
                         /*
                         //ambil data pembelian
                         $daftar_pembelian = Yii::app()->request->getParam('daftar_pembelian');
     
                         //ambil data pembayaran
                         $daftar_pembayaran = Yii::app()->request->getParam('daftar_pembayaran');
                         */
                         
                         //lakukan validasi
                         if($form->validate())
                         {
                              //simpan informasi purchase order
                              
                              //bind isian form ke model tabel inv_po_ro
                              $inv_po_ro = new inv_po_ro();
                              $inv_po_ro['idsupplier'] = $form['idsupplier'];
                              $inv_po_ro['tanggal_jatuh_tempo'] = date('Y-m-j H:i:s', strtotime($form['tanggal_jatuh_tempo']));
                              $inv_po_ro['idstatus'] = 1; //1=baru; 2=post; 3=submit; 4=terima (RO); 5=selesai
                              $inv_po_ro['tanggal_bikin'] = date('Y-m-j H:i:s');
                              $inv_po_ro->save();
                              $id_po_ro = $inv_po_ro->getPrimaryKey();
                              
                              $bread_crumb_list =
                                   '<li>Inventory</li>' .
                              
                                   '<li>'.
                                     '<span> > </span>'.
                                     '<a href="#" onclick="ShowPurchaseOrderList('.$userid_actor.');">Purchase Order</a>'.
                                   '</li>'.
                              
                                   '<li>'.
                                     '<span> > </span>'.
                                     'Tambah Purchase Order' .
                                   '</li>';
                              
                              $form['id_po_ro'] = $id_po_ro;
                              $html = $this->renderPartial(
                                   'vfrm_editpurchaseorder',
                                   array(
                                        'form' => $form,
                                        'userid_actor' => $userid_actor,
                                        'iduser_bikin' => $userid_actor,
                                        'id_po_ro' => $id_po_ro,
                                        'supplier_list' => $supplier_list,
                                        'DaftarBank' => $DaftarBank,
                                        'DaftarMetodeBayar' => $DaftarMetodeBayar
                                   ),
                                   true
                              );
                         }
                         else
                         {
                              //gagal validasi
                              $bread_crumb_list =
                                   '<li>Inventory</li>' .
                              
                                   '<li>'.
                                     '<span> > </span>'.
                                     '<a href="#" onclick="ShowPurchaseOrderList('.$userid_actor.');">Purchase Order</a>'.
                                   '</li>'.
                              
                                   '<li>'.
                                     '<span> > </span>'.
                                     'Tambah Purchase Order' .
                                   '</li>';
                              
                              $html = $this->renderPartial(
                                   'vfrm_addpurchaseorder',
                                   array(
                                        'userid_actor' => $userid_actor,
                                        'form' => $form,
                                        'supplier_list' => $supplier_list
                                   ),
                                   true
                              );
                         }
                    }
                    else
                    {
                         //tampilkan daftar purchase order
                         
                         $bread_crumb_list =
                              '<li>Inventory</li>' .
                         
                              '<li>'.
                                '<span> > </span>'.
                                'Purchase Order'.
                              '</li>';
                         
                         $html = $this->renderPartial(
                              'v_list_purchaseorder',
                              array(
                                   'userid_actor' => $userid_actor
                              ),
                              true
                         );
                    }
               }
               else
               {
                    //tampilkan form daftar purchase order
                    
                    $bread_crumb_list =
                         '<li>Inventory</li>' .
                    
                         '<li>'.
                           '<span> > </span>'.
                           '<a href="#" onclick="ShowPurchaseOrderList('.$userid_actor.');">Purchase Order</a>'.
                         '</li>'.
                    
                         '<li>'.
                           '<span> > </span>'.
                           'Tambah Purchase Order' .
                         '</li>';
                    
                    $form = new frmEditPurchaseOrder();
                    $form['iduser_bikin'] = $userid_actor;
                    
                    $html = $this->renderPartial(
                         'vfrm_addpurchaseorder',
                         array(
                              'userid_actor' => $userid_actor,
                              'form' => $form,
                              'supplier_list' => $supplier_list
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
     
          /*
               actionPurchaseOrderEdit
               
               Deskripsi
               Action untuk menampilkan interface form edit purchase order dan
               mengolah form submission.
               
               Parameter
               userid_actor
               id_po_ro
          */
          public function actionPurchaseOrderEdit()
          {
               $userid_actor = Yii::app()->request->getParam('userid_actor');
               $id_po_ro = Yii::app()->request->getParam('id_po_ro');
               $do_edit = Yii::app()->request->getParam('do_edit');
               $supplier_list = FHelper::GetSupplierLIstData();
               $DaftarMetodeBayar = FHelper::GetMetodeBayarListData();
               $DaftarBank = FHelper::GetBankListData();
               
     
               if(isset($do_edit))
               {
                    //periksa for submission
     
                    if($do_edit == 1)
                    {
                         //lakukan proses form submission
     
                         $daftar_pembelian = '';
                         
                         //ambil data purchase order dari form
                         $form = new frmEditPurchaseOrder();
                         $form->attributes = Yii::app()->request->getParam('frmEditPurchaseOrder');
                         
                         $id_po_ro = Yii::app()->request->getParam('id_po_ro');
     
                         //ambil data pembelian dari form
                         $daftar_pembelian = Yii::app()->request->getParam('daftar_pembelian');
     
                         //ambil data pembayaran dari form
                         $daftar_pembayaran = Yii::app()->request->getParam('daftar_pembayaran');
     
                         //lakukan validasi
                         if($form->validate())
                         {
                              //update informasi purchase order
                              
                              $Criteria = new CDbCriteria();
                              $Criteria->condition = 'id_po_ro = :id_po_ro';
                              $Criteria->params = array(':id_po_ro' => $id_po_ro);
                              
                              //bind isian form ke model tabel inv_po_ro
                              $inv_po_ro = new inv_po_ro($Criteria);
                              $inv_po_ro['idsupplier'] = $form['idsupplier'];
                              $inv_po_ro['tanggal_jatuh_tempo'] = $form['tanggal_jatuh_tempo'];
                              $inv_po_ro['status'] = 1; //1=baru; 2=post; 3=submit; 4=terima (RO); 5=selesai
                              $inv_po_ro['tanggal_bikin'] = date('Y-m-d H:i:s');
                              $inv_po_ro->update();
                              
                              //update daftar pembelian
                              
                              //update daftar pembayaran
                              
                              $html = $this->renderPartial(
                                   'v_editpurchaseorder_success',
                                   array(
                                        'userid_actor' => $userid_actor
                                   ),
                                   true
                              );
                         }
                         else
                         {
                              //gagal validasi
                              
                              $html = $this->renderPartial(
                                   'vfrm_editpurchaseorder',
                                   array(
                                        'userid_actor' => $userid_actor,
                                        'form' => $form,
                                        'daftar_pembelian' => $daftar_pembelian,
                                        'daftar_pembayaran' => $daftar_pembayaran,
                                        'supplier_list' => $supplier_list,
                                        'DaftarBank' => $DaftarBank,
                                        'DaftarMetodeBayar' => $DaftarMetodeBayar,
                                   ),
                                   true
                              );
                         }
                    }
                    else
                    {
                         //tampilkan form edit purchase order
                         
                         $Criteria = new CDbCriteria();
                         $Criteria->condition = 'is_del = 0';
                         $PO = inv_po_ro::model()->findAll($Criteria);
               
                         $html = $this->renderPartial(
                              'v_list_purchaseorder',
                              array(
                                   'userid_actor' => $userid_actor,
                                   'purchase_order_list' => $PO
                              ),
                              true
                         );
                    }
               }
               else
               {
                    //tampilkan form edit purchase order
                    
                    $Criteria = new CDbCriteria();
                    $Criteria->condition = 'id = :id_po_ro';
                    $Criteria->params = array(':id_po_ro' => $id_po_ro);
                    $inv_po_ro = inv_po_ro::model()->find($Criteria);
                    
                    Yii::log('id_po_ro = ' . $id_po_ro , 'info');
                    Yii::log('inv_po_ro[tanggal_jatuh_tempo] = ' . $inv_po_ro['tanggal_jatuh_tempo'] , 'info');
                    
                    $daftar_pembelian = '';
                    
                    $form = new frmEditPurchaseOrder();
                    $form['id_po_ro'] = $inv_po_ro['id'];
                    $form['idstatus'] = $inv_po_ro['idstatus'];
                    $form['iduser_bikin'] = $inv_po_ro['iduser_bikin'];
                    $form['idsupplier'] = $inv_po_ro['idsupplier'];
                    $form['tanggal_jatuh_tempo'] = date('j-M-Y', strtotime($inv_po_ro['tanggal_jatuh_tempo']));
                    $form['tanggal_bikin'] = $inv_po_ro['tanggal_bikin'];
          
                    $html = $this->renderPartial(
                         'vfrm_editpurchaseorder',
                         array(
                              'userid_actor' => $userid_actor,
                              'form' => $form,
                              'daftar_pembelian' => $daftar_pembelian,
                              'daftar_pembayaran' => $daftar_pembayaran,
                              'supplier_list' => $supplier_list,
                              'DaftarBank' => $DaftarBank,
                              'DaftarMetodeBayar' => $DaftarMetodeBayar,
                         ),
                         true
                    );
               }
               
               echo CJSON::encode(array('html' => $html));
          }
          
          /*
               actionDaftarProduk
               
               Deskripsi
               Action untuk mengembalikan daftar produk ke interface edit purchase order.
               
               Parameter
               idsupplier
                    Integer.
               idkategori
                    Integer.
               tipe_produk
                    String.
                    
               Return
               View list daftar produk, dibungkus dalam JSON.
          */
          public function actionDaftarProduk()
          {
               $menuid = 11;
               $parentmenuid = 6;
               $userid_actor = Yii::app()->request->getParam('userid_actor');
	          $tipe_produk = Yii::app()->request->getParam('tipe_produk');
	          $idsupplier = Yii::app()->request->getParam('idsupplier');
	          $idkategori = $this->GetKategoriProduk($tipe_produk);
	          
	          
	          $Criteria = new CDbCriteria();
	          $Criteria->condition = 
                    'idkategori = :idkategori' .
                    ' AND '.
                    'idsupplier = :idsupplier'.
                    ' AND '.
                    'is_deact = 0' .
                    ' AND '.
                    'is_del = 0';
                    
               $Criteria->params = array(
                    ':idkategori' => $idkategori,
                    ':idsupplier' => $idsupplier
               );
               
               Yii::log('idkategori = ' . $idkategori, 'info');
               Yii::log('idsupplier = ' . $idsupplier, 'info');
               
               $products = inv_inventory::model()->findAll($Criteria);
               
               $barcodeitem_list_view_name = $this->GetProdukListViewName($tipe_produk);
               $TipeBarcodeItem_Parameter = $tipe_produk;
               $TipeBarcodeItem_Judul = FHelper::GetBarcodeItemJudul($tipe_produk); 
               
               $html = $this->renderPartial(
                    $barcodeitem_list_view_name,
                    array(
                         'products' => $products,
                         'userid_actor' => $userid_actor,
                         'TipeBarcodeItem_Parameter' => $TipeBarcodeItem_Parameter,
                         'TipeBarcodeItem_Judul' => $TipeBarcodeItem_Judul
                    ),
                    true
               );
               
               echo CJSON::encode(array('html' => $html));
          }
          
          /*
               actionShowDaftarPembelian
               
               Deskripsi
               Action untuk mengembalikan daftar pembelian berdasarkan id_po_ro
               
               Parameter
               id_po_ro
                    Integer. id purchase order.
                    
               Return
               Daftar pembelian suatu purchase order, dibungkus dalam JSON.
          */
          public function actionShowDaftarPembelian()
          {
               Yii::log('Menampilkan record pembelian', 'info');
               
               $userid_actor = Yii::app()->request->getParam('userid_actor');
               $id_po_ro = Yii::app()->request->getParam('id_po_ro');
               
               $Criteria = new CDbCriteria();
               $Criteria->condition = 'id_po_ro = :id_po_ro';
               $Criteria->params = array(':id_po_ro' => $id_po_ro);
               
               $daftar_pembelian = inv_po_ro_pembelian::model()->findAll($Criteria);
               
               Yii::log('userid_actor = ' . $userid_actor, 'info'); 
               
               $html = $this->renderPartial(
                    'v_list_pembelian',
                    array(
                         'daftar_pembelian' => $daftar_pembelian,
                         'userid_actor' => $userid_actor
                    ),
                    true
               );
               
               echo CJSON::encode(array('html' => $html));
          }
          
          /*
               actionPurchaseOrderAddDaftarPembelian
               
               Deskripsi
               Action untuk menambah record ke daftar pembelian berdasarkan id_po_ro.
               
               Parameter
               id_po_ro
                    Integer
               idproduk
                    Integer
               jumlah
                    Integer
                    
               Return
               Daftar pembelian suatu id_po_ro dibungkus dalam JSON.
          */
          public function actionAddDaftarPembelian()
          {
               $userid_actor = Yii::app()->request->getParam('userid_actor');
               $id_po_ro = Yii::app()->request->getParam('id_po_ro');
               $idproduk = Yii::app()->request->getParam('idproduk');
               $jumlah = Yii::app()->request->getParam('jumlah');
               $nilai = Yii::app()->request->getParam('nilai');
               
               $inv_po_ro_pembelian = new inv_po_ro_pembelian();
               $inv_po_ro_pembelian['id_po_ro'] = $id_po_ro;
               $inv_po_ro_pembelian['idproduk'] = $idproduk;
               $inv_po_ro_pembelian['jumlah'] = $jumlah;
               $inv_po_ro_pembelian['nilai'] = $nilai;
               
               try
               {
                    Yii::log('Mencoba menambah record pembelian', 'info');
                    
                    $inv_po_ro_pembelian->save();
               }
               catch(Exception $e)
               {
                    Yii::log('Gagal menambah record pembelian', 'info');
               }
               
               
               $this->actionShowDaftarPembelian();
          }
          
          /*
               actionPurchaseOrderDeletePembelian
               
               Deskripsi
               Action untuk menambah record ke daftar pembelian berdasarkan id_po_ro.
               
               Parameter
               id_po_ro
                    Integer
               idproduk
                    Integer. id produk pada tabel inv_inventory
                    
               Return
               Daftar pembelian suatu id_po_ro dibungkus dalam JSON.
          */
          public function actionDeletePembelian()
          {
               $userid_actor = Yii::app()->request->getParam('userid_actor');
               $id_po_ro = Yii::app()->request->getParam('id_po_ro');
               $idproduk = Yii::app()->request->getParam('idproduk');
               
               $Criteria = new CDbCriteria();
               $Criteria->condition = 'id_po_ro = :id_po_ro AND idproduk = :idproduk';
               $Criteria->params = array(
                    ':id_po_ro' => $id_po_ro, 
                    ':idproduk' => $idproduk
               );
               
               $inv_po_ro_pembelian = inv_po_ro_pembelian::model()->find($Criteria);
               $inv_po_ro_pembelian->delete();
               
               $this->actionShowDaftarPembelian();
          }
          
          /*
               actionShowDaftarPembayaran
               
               Deskripsi
               Action untuk menampilkan daftar pembayaran (rencana dan realisasi)
               
               Parameter
               id_po_ro
                    Integer. Id purchase order untuk menampilkan daftar pembayaran.
                    
               Return
               View daftar pembayaran yang dibungkus dalam JSON.
          */
          public function actionShowDaftarPembayaran()
          {
               $userid_actor = Yii::app()->request->getParam('userid_actor');
               $id_po_ro = Yii::app()->request->getParam('id_po_ro');
               
               Yii::log('userid_actor = ' . $userid_actor, 'info');
               
               $Criteria = new CDbCriteria();
               $Criteria->condition = '
                 id_po_ro = :id_po_ro AND 
                 is_del = 0 AND 
                 tipe = "rencana"
               ';
               $Criteria->order = 'tanggal_bayar_rencana ASC';
               $Criteria->params = array(':id_po_ro' => $id_po_ro);
               
               $daftar_pembayaran = inv_po_ro_pembayaran::model()->findAll($Criteria);
               
               $html = $this->renderPartial(
                    'v_list_pembayaran',
                    array(
                         'daftar_pembayaran' => $daftar_pembayaran,
                         'userid_actor' => $userid_actor,
                         'id_po_ro' => $id_po_ro
                    ),
                    true
               );
               
               echo CJSON::encode(array('html' => $html));
          }
          
          /*
               actionAddDaftarPembayaran
               
               Deskripsi
               Action untuk menambahkan record ke daftar pembayaran
               
               Parameter
               id_po_ro
                    Integer
               TanggalRencana
                    String
               BankRencana
                    Integer
               MetodeBayarRencana
                    Integer
               NilaiRencana
                    Integer
                    
               Return
               Action akan memanggil fungsi lain yang mengembalikan daftar 
               pembayaran yang baru.
          */
          public function actionAddDaftarPembayaran()
          {
               $userid_actor = Yii::app()->request->getParam('userid_actor');
               $id_po_ro = Yii::app()->request->getParam('id_po_ro');
               $TanggalRencana = Yii::app()->request->getParam('tanggalrencana');
               $BankRencana = Yii::app()->request->getParam('idbankrencana');
               $MetodeBayarRencana = Yii::app()->request->getParam('idmetodebayarrencana');
               $NilaiRencana = Yii::app()->request->getParam('nilairencana');
               
               $inv_po_ro_pembayaran = new inv_po_ro_pembayaran();
               $inv_po_ro_pembayaran['id_po_ro'] = $id_po_ro;
               $inv_po_ro_pembayaran['tanggal_bayar_rencana'] = date('Y-m-j', strtotime($TanggalRencana));
               $inv_po_ro_pembayaran['idbank_rencana'] = $BankRencana;
               $inv_po_ro_pembayaran['idmetodebayar_rencana'] = $MetodeBayarRencana;
               $inv_po_ro_pembayaran['nilai_bayar_rencana'] = $NilaiRencana;
               $inv_po_ro_pembayaran['tipe'] = 'rencana';
               
               try
               {
                    $inv_po_ro_pembayaran->save();
               }
               catch(Exception $e)
               {
               }
               
               
               $this->actionShowDaftarPembayaran();
          }
          
          /*
               actionUpdateDaftarPembayaran
               
               Deskripsi
               Action untuk mengupdate data pembayaran
               
               Parameter
               id_po_ro
                    Integer
               id_pembayaran
                    Integer
               TanggalRealisasi
                    String
               BankRealisasi
                    Integer
               MetodeBayarRealisasi
                    Integer
               NilaiRealisasi
                    Integer
                    
               Return
               Action akan memanggil fungsi lain yang mengembalikan daftar 
               pembayaran yang baru.
          */
          public function actionUpdateDaftarPembayaran()
          {
               $userid_actor = Yii::app()->request->getParam('userid_actor');
               $id_po_ro = Yii::app()->request->getParam('id_po_ro');
               $id_pembayaran = Yii::app()->request->getParam('id_pembayaran');
               
               $TanggalRencana = Yii::app()->request->getParam('tanggalrencana');
               $BankRencana = Yii::app()->request->getParam('idbankrencana');
               $MetodeBayarRencana = Yii::app()->request->getParam('idmetodebayarrencana');
               $NilaiRencana = Yii::app()->request->getParam('nilairencana');
               
               $TanggalRealisasi = Yii::app()->request->getParam('tanggalreal');
               $BankRealisasi = Yii::app()->request->getParam('idbankreal');
               $MetodeBayarRealisasi = Yii::app()->request->getParam('idmetodebayarreal');
               $NilaiRealisasi = Yii::app()->request->getParam('nilaireal');
               
               $Criteria = new CDbCriteria();
               $Criteria->condition = '
                    id_po_ro = :id_po_ro AND 
                    tanggal_bayar_rencana = :tanggal_bayar_rencana AND
                    idbank_rencana = :idbank_rencana';
               $Criteria->params = array(
                    ':id_po_ro' => $id_po_ro, 
                    ':tanggal_bayar_rencana' => date('Y-m-j', strtotime($TanggalRencana)),
                    ':idbank_rencana' => $BankRencana,
               );
               
               Yii::log('TanggalRencana = ' . date('Y-m-j', strtotime($TanggalRencana)), 'info');
               
               $inv_po_ro_pembayaran = inv_po_ro_pembayaran::model()->find($Criteria);
               $inv_po_ro_pembayaran['tanggal_bayar_rencana'] = date('Y-m-j', strtotime($TanggalRencana));
               $inv_po_ro_pembayaran['idbank_rencana'] = $BankRencana;
               $inv_po_ro_pembayaran['idmetodebayar_rencana'] = $MetodeBayarRencana;
               $inv_po_ro_pembayaran['nilai_bayar_rencana'] = $NilaiRencana;
               
               $inv_po_ro_pembayaran['tanggal_bayar_real'] = date('Y-m-j', strtotime($TanggalRealisasi));
               $inv_po_ro_pembayaran['idbank_real'] = $BankRealisasi;
               $inv_po_ro_pembayaran['idmetodebayar_real'] = $MetodeBayarRealisasi;
               $inv_po_ro_pembayaran['nilai_bayar_real'] = $NilaiRealisasi;
               
               
               $inv_po_ro_pembayaran->update();
               
               
               $this->actionShowDaftarPembayaran();
          }
          
          
          /*
               actionDeletePembayaran
               
               Deskripsi
               Action untuk menghapus data pembayaran
               
               Parameter
               id_po_ro
                    Integer
               id_pembayaran
                    Integer
                    
               Return
               Action akan memanggil fungsi lain yang mengembalikan daftar 
               pembayaran yang baru.
          */
          public function actionDeletePembayaran()
          {
               $userid_actor = Yii::app()->request->getParam('userid_actor');
               $id_po_ro = Yii::app()->request->getParam('id_po_ro');
               $id_pembayaran = Yii::app()->request->getParam('id_pembayaran');
               
               Yii::log('id_po_ro = ' . $id_po_ro, 'info');
               Yii::log('id_pembayaran = ' . $id_pembayaran, 'info');
               
               $Criteria = new CDbCriteria();
               $Criteria->condition = '
                    id = :id_pembayaran
               ';
               $Criteria->params = array(
                    ':id_pembayaran' => $id_pembayaran
               );
               
               $inv_po_ro_pembayaran = inv_po_ro_pembayaran::model()->find($Criteria);
               $inv_po_ro_pembayaran['is_del'] = '1';
               $inv_po_ro_pembayaran->update();
               
               $html = $this->RenderDaftarRealisasiPembayaran($id_po_ro);
               
               echo CJSON::encode(array('html' => $html));
          }
     
          /*
               actionPurchaseOrderDelete
               
               Deskripsi
               Action untuk membuat nasi goreng kambing pakai pete.
          */
          public function actionPurchaseOrderDelete()
          {
               $userid_actor = Yii::app()->request->getParam('userid_actor');
               $id_po_ro = Yii::app()->request->getParam('id_po_ro');
               
               $Criteria = new CDbCriteria();
               $Criteria->condition = 'id = :id_po_ro';
               $Criteria->params = array(':id_po_ro' => $id_po_ro);
               
               //update record di tabel
               $inv_po_ro = inv_po_ro::model()->find($Criteria);
               $inv_po_ro['is_del'] = 1;
               $inv_po_ro->update();
               
               $this->actionPurchaseOrderList();
          }
     
          /*
               actionPurchaseOrderPost
               
               Deskripsi
               Action untuk mengubah status purchase order dari baru menjadi post.
          */
          public function actionPurchaseOrderPost()
          {
               $userid_actor = Yii::app()->request->getParam('userid_actor');
               $id_po_ro = Yii::app()->request->getParam('id_po_ro');
               
               $Criteria = new CDbCriteria();
               $Criteria->condition = 'id = :id_po_ro';
               $Criteria->params = array(':id_po_ro' => $id_po_ro);
               
               //update record di tabel
               $inv_po_ro = inv_po_ro::model()->find($Criteria);
               $inv_po_ro['idstatus'] = 2;  // <---- lihat pada script table creation untuk kamus field status
               $inv_po_ro['tanggal_post'] = date('Y-m-j');
               $inv_po_ro->update();
               
               //tampilkan informasi sukses menambahkan record lokasi
               $this->actionPurchaseOrderList();
          }
          
          private function KirimEmailPurchaseOrder($message)
          {
               /*
                $subject = 'Purchase Order dari JH Moriska';
               $message =
               'Purchase Order dari JH Moriska'.'<br/>'.
               '<hr>'.
               'No PO : '.$model['nama'].'<br/>'.
               'Supplier : '.$model['email'].'<br/>'.
               'Daftar pemesanan : '.$model['pesan'].
               '<hr>';
               */
               $mail = new PHPMailer();
               $mail->IsSMTP();
               $mail->SMTPDebug = false;
               $mail->SMTPAuth = true;
               
               //gmail
               /*
                    $mail->Host = "smtp.gmail.com";
                    $mail->Port = 587; // <--- gmail
                    $mail->SMTPSecure = "tls";
                    $mail->Username = "frans@mumpuni.com";
                    $mail->Password = "s4p1 t3rb4ng 0m3g47";
                    $mail->From = 'frans@mumpuni.com';
                    $mail->FromName = 'Admin POSOM';
               */
               
               //jhmoriska.com
               
               $mail->Host = "mail.jhmoriska.com";
               $mail->Port = 587;
               $mail->Username = "noreply@jhmoriska.com";
               $mail->Password = "noreply";
               $mail->From = 'noreply@jhmoriska.com';
               $mail->FromName = 'JH Moriska';
               
               
               $mail->Subject = $message['subject'];
               //$mail->AddBCC("hasudungan@biocert.co.id", "Hasudungan");
               //$mail->AddBCC("internal@mumpuni.com", "Dummy Supplier");
               $mail->AddBCC("frans.indroyono@gmail.com", "Dummy Supplier");
               $mail->AddAddress($message['email'], "");
               $mail->MsgHTML($message['pesan']);
               
               $mail->Send();
          }
          
          
     
          /*
               actionPurchaseOrderSubmit
               
               Deskripsi
               Action untuk mengubah status purchase order dari post ke submit
               dan mengirim email kepada supplier.
          */
          public function actionPurchaseOrderSubmit()
          {
               $userid_actor = Yii::app()->request->getParam('userid_actor');
               $id_po_ro = Yii::app()->request->getParam('id_po_ro');
               
               $Criteria = new CDbCriteria();
               $Criteria->condition = 'id = :id_po_ro';
               $Criteria->params = array(':id_po_ro' => $id_po_ro);
               
               //update record di tabel
               $inv_po_ro = inv_po_ro::model()->find($Criteria);
               $inv_po_ro['idstatus'] = 3;  // <---- lihat pada script table creation untuk kamus field status
               $inv_po_ro['tanggal_submit'] = date('Y-m-j');
               $idsupplier = $inv_po_ro['idsupplier'];
               
               $inv_po_ro->update();
               
               //kirim email ke supplier (jika ada)
               $Criteria->condition = 'supplier_id = :idsupplier';
               $Criteria->params = array(':idsupplier' => $idsupplier);
               $mtr_supplier = mtr_supplier::model()->find($Criteria);
               $email = $mtr_supplier['email'];
               
               $message['email'] = $email;
               $message['subject'] = 'Purchase Order dari JH Moriska';
               $message['pesan'] = 
                  'Purchase Order dari JH Moriska'.'<br/>'.
                  '<hr>'.
                  'No PO : '.$id_po_ro.'<br/>'.
                  'Supplier : '.$inv_po_ro->supplier['name'].'<br/>'.
                  'Daftar pemesanan : '.$model['pesan'].
                  '<hr>';
                  
               $daftar_pembelian = $inv_po_ro->pembelian;
               foreach($daftar_pembelian as $pembelian)
               {
                 $produk = $pembelian->produk;
                 $message['pesan'] .=
                  'nama produk: ' . $produk['nama'] . ' - ' .
                  'jumlah: ' . $pembelian['jumlah'] . ' - ' .
                  'nilai: ' . $pembelian['nilai'] . '<br/>';
               }
               
               $this->KirimEmailPurchaseOrder($message);
               
               //tampilkan informasi sukses menambahkan record lokasi
               $this->actionPurchaseOrderList();
          }
          
          /*
               actionReceiveOrder
               
               Deskripsi
               Action untuk mencatat RO atas sebuah PO.
               
               Parameter
               userid_actor
               id_po_ro
          */
          public function actionReceiveOrder()
          {
               $this->layout = 'layout-baru';
     
               $Criteria = new CDbCriteria();
               $Criteria->condition = 'is_del = 0 AND idstatus in (3, 4, 5)';
     
               $userid_actor = Yii::app()->request->getParam('userid_actor');
               $this->idlokasi = Yii::app()->request->cookies['idlokasi']->value;
               $this->userid_actor = $userid_actor;
               $this->menuid = 50;
               $this->parentmenuid = 9;
               $this->bread_crumb_list = 
                  '<li>Inventory</li>'.
                  '<li>></li>'.
                  '<li>Receive Order</li>';
     
               //$TheMenu = FHelper::RenderMenu(0, $userid_actor, $this);
               $PO = inv_po_ro::model()->findAll($Criteria);
               $POList = $this->renderPartial(
                    'v_list_receiveorder',
                    array(
                         'purchase_order_list' => $PO,
                         'userid_actor' => $userid_actor
                    ),
                    true
               );
     
               $this->render(
                    'index_general',
                    array(
                         'TheMenu' => $TheMenu,
                         'TheContent' => $POList,
                         'userid_actor' => $userid_actor
                    )
               );
          }
          
          /*
            actionReceiveOrderList
            
            Deskripsi
            Action untuk menampilkan daftar PO dalam proses receive order
            
            Parameter
            userid_actor
              Integer
            
            Return
            View daftar PO dalam status proses receive order.
          */
          public function actionReceiveOrderList()
          {
            $Criteria = new CDbCriteria();
            $Criteria->condition = 'is_del = 0 AND idstatus in (3, 4, 5)';
            
            $userid_actor = Yii::app()->request->getParam('userid_actor');
            $this->userid_actor = $userid_actor;
            $this->menuid = 50;
            $this->parentmenuid = 9;
            $this->bread_crumb_list = 
              '<li>Inventory</li>'.
              '<li>></li>'.
              '<li>Receive Order</li>';
            
            //$TheMenu = FHelper::RenderMenu(0, $userid_actor, $this);
            $PO = inv_po_ro::model()->findAll($Criteria);
            $html = $this->renderPartial(
              'v_list_receiveorder',
              array(
               'purchase_order_list' => $PO,
               'userid_actor' => $userid_actor
              ),
              true
            );
            
            echo CJSON::encode(array('html' => $html));
          }
          
          /*
               RenderDaftarRencanaPembayaran
               
               Deskripsi
               Action untuk mengembalikan render view daftar rencana pembayaran 
               suatu po.
               
               Parameter
               id_po_ro
                    Integer. Id purchase order untuk menampilkan daftar pembayaran.
                    
               Return
               View daftar pembayaran yang dibungkus dalam JSON.
          */
          private function RenderDaftarRencanaPembayaran($id_po_ro)
          {
               $Criteria = new CDbCriteria();
               $Criteria->condition = 'id_po_ro = :id_po_ro AND is_del = 0 AND tipe = "rencana"';
               $Criteria->order = 'tanggal_bayar_rencana ASC';
               $Criteria->params = array(':id_po_ro' => $id_po_ro);
               
               $daftar_pembayaran = inv_po_ro_pembayaran::model()->findAll($Criteria);
               
               $html = $this->renderPartial(
                    'v_list_rencana_pembayaran',
                    array(
                         'daftar_pembayaran' => $daftar_pembayaran,
                         'userid_actor' => $userid_actor
                    ),
                    true
               );
               
               return $html;
          }
          
          /*
               RenderDaftarRealisasiPembayaran
               
               Deskripsi
               Action untuk mengembalikan render view daftar realisasi pembayaran 
               suatu po.
               
               Parameter
               id_po_ro
                    Integer. Id purchase order untuk menampilkan daftar pembayaran.
                    
               Return
               View daftar pembayaran yang dibungkus dalam JSON.
          */
          private function RenderDaftarRealisasiPembayaran($id_po_ro)
          {
             $Criteria = new CDbCriteria();
             $Criteria->condition = 'id_po_ro = :id_po_ro AND is_del = 0 AND tipe = "real"';
             $Criteria->order = 'tanggal_bayar_rencana ASC';
             $Criteria->params = array(':id_po_ro' => $id_po_ro);
             
             $daftar_pembayaran = inv_po_ro_pembayaran::model()->findAll($Criteria);
             
             $html = $this->renderPartial(
              'v_list_realisasi_pembayaran',
              array(
               'daftar_pembayaran' => $daftar_pembayaran,
               'userid_actor' => $userid_actor
              ),
              true
             );
             
             return $html;
          }
          
          /*
            actionShowProcessPurchaseOrder
            
            Deskripsi
            Action untuk memproses penerimaan barang dan pembayaran suatu
            purchase order.
            
            Parameter
            id_po_ro
              Integer
              
            Return
            Mengembalikan interface untuk memasukkan data penerimaan barang dan
            data pembayaran suatu purchase order
          */
          public function actionShowProcessReceiveOrder()
          {
            $userid_actor = Yii::app()->request->getParam('userid_actor');
            $id_po_ro = Yii::app()->request->getParam('id_po_ro');
            Yii::log('id_po_ro = ' . $id_po_ro, 'info');
            
            $this->userid_actor = $userid_actor;
            $this->menuid = 50;
            $this->parentmenuid = 9;
            $this->bread_crumb_list = 
              '<li>Inventory</li>'.
              '<li>></li>'.
              '<li>Receive Order</li>';
            
            $Criteria = new CDbCriteria();
            $Criteria->condition = 'id_po_ro = :id_po_ro';
            $Criteria->params = array(':id_po_ro' => $id_po_ro);
            
            $daftar_pemesanan = inv_po_ro_pembelian::model()->findAll($Criteria);
            
            $form = new frmEditPurchaseOrder();
            $Criteria = new CDbCriteria();
            $Criteria->condition = 'id = :id_po_ro';
            $Criteria->params = array(':id_po_ro' => $id_po_ro);
            $inv_po_ro = inv_po_ro::model()->find($Criteria);
            
            $form['id_po_ro'] = $inv_po_ro['id'];
            $form['idsupplier'] = $inv_po_ro['idsupplier'];
            $form['tanggal_jatuh_tempo'] = date('j-M-Y', strtotime($inv_po_ro['tanggal_jatuh_tempo']));
            
            $supplier_list = FHelper::GetSupplierListData();
            $DaftarMetodeBayar = FHelper::GetMetodeBayarListData();
            $DaftarBank = FHelper::GetBankListData();
            
            $DaftarRencanaPembayaran = $this->RenderDaftarRencanaPembayaran($inv_po_ro['id']);
            $DaftarRealisasiPembayaran = $this->RenderDaftarRealisasiPembayaran($inv_po_ro['id']);
            
            $formPembayaran = new frmPembayaranPurchaseOrder();
            $html = $this->renderPartial(
              'vfrm_processreceiveorder',
              array(
                'daftar_pemesanan' => $daftar_pemesanan,
                'userid_actor' => $userid_actor,
                'supplier_list' => $supplier_list,
                'form' => $form,
                'formPembayaran' => $formPembayaran,
                'DaftarRencanaPembayaran' => $DaftarRencanaPembayaran,
                'DaftarRealisasiPembayaran' => $DaftarRealisasiPembayaran,
                'DaftarMetodeBayar' => $DaftarMetodeBayar,
                'DaftarBank' => $DaftarBank
              ),
              true
            );
            
            echo CJSON::encode(array('html' => $html));
          }
          
          /*
            actionReceiveOrderTambahPembayaran
            
            Deskripsi
            Action untuk menampilkan interface edit data pembayaran receive order
            
            Parameter
            idpembayaran
              Integer
            
            id_po_ro
              Integer
              
            Return
            
          */
          public function actionReceiveOrderTambahPembayaran()
          {
            $id_po_ro = Yii::app()->request->getParam('id_po_ro');
            $userid_actor = Yii::app()->request->getParam('userid_actor');
            
            $form = new frmPembayaranPurchaseOrder();
            $form->attributes = Yii::app()->request->getParam('frmPembayaranPurchaseOrder'); 
            
            //submit changes into database
            $inv_po_ro_pembayaran = new inv_po_ro_pembayaran();
            $inv_po_ro_pembayaran['tanggal_bayar_rencana'] = date('Y-m-j', strtotime($form['realisasiTanggal']));
            $inv_po_ro_pembayaran['nilai_bayar_rencana'] = $form['realisasiNilai'];
            $inv_po_ro_pembayaran['idbank_rencana'] = $form['realisasiBank'];
            $inv_po_ro_pembayaran['idmetodebayar_rencana'] = $form['realisasiMetode'];
            $inv_po_ro_pembayaran['id_po_ro'] = $id_po_ro;
            $inv_po_ro_pembayaran['tipe'] = 'real';
            
            $inv_po_ro_pembayaran->save();
            
            $html = $this->RenderDaftarRealisasiPembayaran($id_po_ro);
            
            echo CJSON::encode(array('html' => $html));
          }
          
          /*
            actionReceiveOrderUpdatePembayaran
            
            Deskripsi
            Action untuk menampilkan interface edit data pembayaran receive order
            
            Parameter
            idpembayaran
              Integer
            
            id_po_ro
              Integer
              
            Return
            
          */
          public function actionReceiveOrderUpdatePembayaran()
          {
            $idpembayaran = Yii::app()->request->getParam('idpembayaran');
            $id_po_ro = Yii::app()->request->getParam('id_po_ro');
            $userid_actor = Yii::app()->request->getParam('userid_actor');
            
            if(isset($idpembayaran))
            {
              //submit changes into database
              $Criteria = new CDbCriteria();
              $Criteria->condition = 'id = :idpembayaran and is_del = 0 AND tipe = "real"';
              $Criteria->params = array(':idpembayaran' => $idpembayaran);
              
              $form = new frmPembayaranPurchaseOrder();             
              $form->attributes = Yii::app()->request->getParam('frmPembayaranPurchaseOrder');
              
              $inv_po_ro_pembayaran = inv_po_ro_pembayaran::model()->find($Criteria);
              $inv_po_ro_pembayaran['tanggal_bayar_rencana'] = date('Y-m-j', strtotime($form['realisasiTanggal']));
              $inv_po_ro_pembayaran['nilai_bayar_rencana'] = $form['realisasiNilai'];
              $inv_po_ro_pembayaran['idbank_rencana'] = $form['realisasiBank'];              
              $inv_po_ro_pembayaran['idmetodebayar_rencana'] = $form['realisasiMetode'];    
              
              $inv_po_ro_pembayaran->save();
            }
            
            $html = $this->RenderDaftarRealisasiPembayaran($id_po_ro);
            
            echo CJSON::encode(array('html' => $html));
          }
          
          /*
               actionReceiveOrderDeletePembayaran
               
               Deskripsi
               Action untuk menghapus data realisasi pembayaran
               
               Parameter
               id_po_ro
                    Integer
               id_pembayaran
                    Integer
                    
               Return
               Action akan memanggil fungsi lain yang mengembalikan daftar 
               pembayaran yang baru.
          */
          public function actionReceiveOrderDeletePembayaran()
          {
             $userid_actor = Yii::app()->request->getParam('userid_actor');
             $id_po_ro = Yii::app()->request->getParam('id_po_ro');
             $id_pembayaran = Yii::app()->request->getParam('id_pembayaran');
             
             Yii::log('id_po_ro = ' . $id_po_ro, 'info');
             Yii::log('id_pembayaran = ' . $id_pembayaran, 'info');
             
             $Criteria = new CDbCriteria();
             $Criteria->condition = 'id_po_ro = :id_po_ro AND id = :id_pembayaran';
             $Criteria->params = array(
              ':id_po_ro' => $id_po_ro, 
              ':id_pembayaran' => $id_pembayaran
             );
             
             $inv_po_ro_pembayaran = inv_po_ro_pembayaran::model()->find($Criteria);
             $inv_po_ro_pembayaran['is_del'] = '1';
             $inv_po_ro_pembayaran->update();
             
             $this->actionShowDaftarPembayaran();
          }
          
          /*
            actionReceiveOrderTambahPenerimaan
            
            Deskripsi
            Action untuk menyimpan data penerimaan barang dari suatu purchase
            order.
            
            Parameter
            idpembelian
              Integer. id pembelian dari tabel inv_po_ro_pembelian
            idproduk
              Integer. id produk yang dipesan.
            jumlahterima
              Integer. jumlah barang yang diterima.
              
            Return
            Mengembalikan render view daftar barang yang sudah diterima.
          */
          public function actionReceiveOrderTambahPenerimaan()
          {
            $idpembelian = Yii::app()->request->getParam('idpembelian');
            $jumlahterima = Yii::app()->request->getParam('jumlahterima');
            $userid_actor = Yii::app()->request->getParam('userid_actor');
            
            $inv_po_ro_penerimaan = new inv_po_ro_penerimaan();
            $inv_po_ro_penerimaan['idpembelian'] = $idpembelian;
            $inv_po_ro_penerimaan['jumlah'] = $jumlahterima;
            $inv_po_ro_penerimaan['add_by'] = $userid_actor;
            $inv_po_ro_penerimaan['tanggal_terima'] = date('Y-m-j H:i:s');
            
            $inv_po_ro_penerimaan->save();
            
            $this->actionReceiveOrderShowPenerimaan();
          }
          
          /*
            actionReceiveOrderHapusPenerimaan
            
            Deskripsi
            Action untuk menghapus data penerimaan barang dari suatu purchase
            order.
            
            Parameter
            idpenerimaan
              Integer. id penerimaan dari tabel inv_po_ro_penerimaan
              
            Return
            Mengembalikan render view daftar barang yang sudah diterima.
          */
          public function actionReceiveOrderHapusPenerimaan()
          {
            $idpenerimaan = Yii::app()->request->getParam('idpenerimaan');
            
            $Criteria = new CDbCriteria();
            $Criteria->condition = 'id = :idpeneriaam';
            $Criteria->params = array(':idpeneriaam' => $idpenerimaan);
            
            $inv_po_ro_penerimaan = inv_po_ro_penerimaan::model()->find($Criteria);
            $inv_po_ro_penerimaan->delete();
            
            $this->actionReceiveOrderShowPenerimaan();
          }
          
          /*
            actionReceiveOrderShowPenerimaan
            
            Deskripsi
            Action untuk menampilkan daftar penerimaan barang dari suatu purchase
            order.
            
            Parameter
            idpembelian
              Integer. id pembelian dari tabel inv_po_ro_pembelian
              
            Return
            Mengembalikan render view daftar barang yang sudah diterima.
          */
          public function actionReceiveOrderShowPenerimaan()
          {
            $userid_actor = Yii::app()->request->getParam('userid_actor');
            $idpembelian = Yii::app()->request->getParam('idpembelian');
            
            $Criteria = new CDbCriteria();
            $Criteria->condition = 'idpembelian = :idpembelian';
            $Criteria->params = array(':idpembelian' => $idpembelian);
            
            Yii::log('idpembelian = ' . $idpembelian, 'info');
            
            $daftar_penerimaan = inv_po_ro_penerimaan::model()->findAll($Criteria);
            
            $Criteria->condition = 'id = :idpembelian';
            $Criteria->params = array(':idpembelian' => $idpembelian);
            $pembelian = inv_po_ro_pembelian::model()->find($Criteria);
            $produk = $pembelian->produk;
            
            $html = $this->renderPartial(
              'v_list_receiveorder_daftar_penerimaan',
              array(
                'userid_actor' => $userid_actor,
                'daftar_penerimaan' => $daftar_penerimaan,
                'produk' => $produk
              ),
              true
            );
            
            echo CJSON::encode(array('html' => $html));
          }
          
     
          /*
               actionPurchaseOrderPrint
               
               Deskripsi
               Action untuk menampilkan print preview purchase order
          */
          public function actionPurchaseOrderPrint()
          {
          }
          
          /*
               actionPurchaseOrderByJobOrder
               
               Deskripsi
               Action untuk membuat PO berdsasarkan JO.
               
               Parameter
               idjoborder
               userid_action
          */
          public function actionPurchaseOrderByJobOrder()
          {
          }
	
	//inventory - purchase order - end


	//inventory - barcode - begin
	
	
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
                    case 'lensa' :    return 'v_list_barcode_item_lensa';
                    case 'frame' :    return 'v_list_barcode_item_frame';
                    case 'softlens' : return 'v_list_barcode_item_softlens';
                    case 'solution' : return 'v_list_barcode_item_general';
                    case 'accessories' : return 'v_list_barcode_item_general';
                    case 'services' : return 'v_list_barcode_item_general';
                    case 'other' :    return 'v_list_barcode_item_general';
                    case 'supplies' : return 'v_list_barcode_item_general';
                    case 'paket' : return 'v_list_barcode_item_paket';
               }
          }
          
          /*
               GetPembelianListViewName
               
               Deskripsi
               Fungsi untuk mengembalikan view name berdasarkan tipe produk.
               
               Parameter
               tipe_produk. String yang menyatakan tipe produk (lensa, frame, softlens...)
          */
          private function GetPembelianListViewName($tipe_produk)
          {
               switch($tipe_produk)
               {
                    case 'lensa' :    return 'v_list_pembelian_lensa';
                    case 'frame' :    return 'v_list_pembelian_frame';
                    case 'softlens' : return 'v_list_pembelian_softlens';
                    case 'solution' : return 'v_list_pembelian_general';
                    case 'accessories' : return 'v_list_pembelian_general';
                    case 'services' : return 'v_list_pembelian_general';
                    case 'other' :    return 'v_list_pembelian_general';
                    case 'supplies' : return 'v_list_pembelian_general';
                    case 'paket' : return 'v_list_pembelian_paket';
               }
          }
          
          /*
               GetProdukListViewName
               
               Deskripsi
               Fungsi untuk mengembalikan view name berdasarkan tipe produk.
               
               Parameter
               tipe_produk. String yang menyatakan tipe produk (lensa, frame, softlens...)
          */
          private function GetProdukListViewName($tipe_produk)
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
          
          
      public function actionHapusBarcode()
      {
        $barcode = Yii::app()->request->getParam('barcode');
        $idlokasi = Yii::app()->request->cookies['idlokasi']->value;
        
        if($barcode != '')
        {
          $Criteria = new CDbCriteria();
          $Criteria->condition = 'barcode = :barcode and idlokasi = ' . $idlokasi;
          $Criteria->params = array(':barcode' => $barcode);
          
          $jumlah = inv_item::model()->deleteAll($Criteria);
          
          if($jumlah == 0)
          {
            echo CJSON::encode(
              array(
                'status' => 'not ok',
                'pesan' => 'Barcode ' . $barcode . ' tidak ada dalam sistem'
              )
            );
          }
          else
          {
            echo CJSON::encode(
              array(
                'status' => 'ok',
                'pesan' => 'Barcode ' . $barcode . ' sudah dihapus'
              )
            );
          } //if jumlah == 0
        } //if barcode != ''
        else
        {
          echo CJSON::encode(
            array(
              'status' => 'not ok',
              'pesan' => 'Tidak ada barcode untuk dihapus'
            )
          );
        }
      }
	
	     /*
	          actionBarcode
	          
	          Deskripsi
	          Action default untuk menampilkan interface pembuatan barcode.
	     */
	     public function actionBarcode()
	     {
	          $userid_actor = Yii::app()->request->getParam('userid_actor');
	          $this->idlokasi = Yii::app()->request->cookies['idlokasi']->value;
	          
	          $this->userid_actor = $userid_actor;
	          $this->menuid = 37;
	          $this->parentmenuid = 9;
	          $this->bread_crumb_list = 
             '<li>Inventory</li>'.
             '<li>></li>'.
             '<li>Barcode</li>';
	          
	          $TheMenu = FHelper::RenderMenu(0, $userid_actor, 9);
	          
	          //$this->layout = 'inventory';
	          $this->layout = 'layout-baru';
             $TheContent = $this->renderPartial(
                  'v_list_barcodeitem',
                  array(
                       'userid_actor' => $userid_actor,
                  ),
                  true
             );
             
             $Criteria = new CDbCriteria();
             $Criteria->condition = 'is_del = "N"';
             
             $mtr_supplier_list = mtr_supplier::model()->findAll($Criteria);
             $supplier_list = CHtml::listData($mtr_supplier_list, 'supplier_id', 'name');
             
             $this->render(
                  'index_barcode',
                  array(
                       'TheMenu' => $TheMenu,
                       'TheContent' => $TheContent,
                       'userid_actor' => $userid_actor,
                       'supplier_list' => $supplier_list
                  )
             );
	     }
	
	    /*
	         actionBarcodeItemList
	         
	         Deskripsi
	         Action untuk menampilkan daftar produk berdasarkan kategori dan
	         supplier yang dipilih user.
	         
	         Parameter
	         tipe_produk
            String. tipe produk yang dipilih user
          idsupplier
               Integer. id supplier yang dipilih user.
          idkategori
               Integer.
               
          Return
          Render data table yang berisi daftar produk. Dibungkus dalam JSON.
	    */
	    public function actionBarcodeItemList()
	    {
	      $e = "kotretan";
	      $html = "";
	      
        try
        {
          $html = $this->MakeViewContent();
        }
        catch(Exception $e)
        {
          $exception = $e;
        }
              
        echo CJSON::encode(array('html' => $html, 'exception' => $e));
      }
      
      private function MakeViewContent()
      {
        try
        {
          ini_set('display_errors', '1');
          ini_set('memory_limit', '1000M');
          error_reporting(E_ALL);
          
          $userid_actor = Yii::app()->request->getParam('userid_actor');
          $tipe_produk = Yii::app()->request->getParam('tipe_produk');
          $idsupplier = Yii::app()->request->getParam('idsupplier');
          $sortby = Yii::app()->request->getParam('sortby');
          $search = Yii::app()->request->getParam('search');
          $idkategori = $this->GetKategoriProduk($tipe_produk);
          
          $search = ( isset($search) == false ? "" : $search );
          
          //maketable's variables - begin
            $rowsperpage = Yii::app()->request->getParam('rowsperpage');
            $rowsperpage = ( $rowsperpage > 0 ? $rowsperpage : 50 );
            
            //$sortby = Yii::app()->request->getParam('sortby');
            
            
            $pageno = Yii::app()->request->getParam('pageno');
            $pageno = ( $pageno == 0 ? 1 : $pageno );
            
            $search = Yii::app()->request->getParam('search');
            $search = ( $search == "" ? "" : $search );
            
            $list_type = "MediaGallery";
            
            $array_rows_per_page = array();
            $array_rows_per_page[50] = 50;
            $array_rows_per_page[100] = 100;
            $array_rows_per_page[150] = 150;
            $array_rows_per_page[200] = 200;
            $array_rows_per_page[400] = 400;
            $array_rows_per_page[500] = 500;
            
            $array_sort_by[1] = '';
          //maketable's variables - end
             
          /*
          $Criteria = new CDbCriteria();
          $Criteria->condition = 
            'idkategori = :idkategori' .
            ' AND '.
            'idsupplier = :idsupplier'.
            ' AND '.
            'is_deact = 0' .
            ' AND '.
            'is_del = 0';
                  
          $Criteria->params = array(
             ':idkategori' => $idkategori,
             ':idsupplier' => $idsupplier
          );
          
          $temp_products = inv_inventory::model()->findAll($Criteria);
          */
          
          $search_terms = explode(" ", $search );
          $search_clause = "";
          
          $command = Yii::app()->db->createCommand();
          switch($tipe_produk)
          {
            case 'lensa':
              $command->select("
                inv.*, 
                lensa.material, lensa.base_curve, lensa.sph_min, 
                lensa.cyl_min, lensa.add_1");
              $command->from("inv_inventory inv");
              $command->join("inv_type_lens lensa", "lensa.id_item = inv.id");
              
              if( $search != "" )
              {
                foreach($search_terms as $term)
                {
                  $temp_search_clause = 
                    "nama like '%$term%' OR
                    brand like '%$term%' OR
                    material like '%$term%' OR
                    base_curve like '%$term%' OR
                    sph_min like '%$term%' OR
                    cyl_min like '%$term%' OR
                    add_1 like '%$term%'";
                    
                  $temp_search_clause = "(" . $temp_search_clause . ")";
                  
                  if($search_clause != "")
                  {
                    $search_clause .= " AND ";
                  }
                  
                  $search_clause .= $temp_search_clause;
                }
              }
                
              
              break;
              
            case 'frame':
              $command->select("
                inv.*, 
                frame.nama_tipe, frame.color, frame.material, frame.eye_size, 
                frame.id_frame_type, frame.dbl, frame.gcd, frame.vertical,
                frame.temple");
              $command->from("inv_inventory inv");
              $command->join("inv_type_frame frame", "frame.id_item = inv.id");
              
              if( $search != "" )
              {
                foreach($search_terms as $term)
                {
                  $temp_search_clause = 
                    "nama like '%$term%' OR
                    brand like '%$term%' OR
                    nama_tipe like '%$term%' OR
                    color like '%$term%' OR
                    material like '%$term%' OR
                    eye_size like '%$term%' OR
                    id_frame_type like '%$term%' OR
                    dbl like '%$term%' OR 
                    gcd like '%$term%' OR 
                    vertical like '%$term%' OR
                    temple like '%$term%'";
                    
                  $temp_search_clause = "(" . $temp_search_clause . ")";
                  
                  if($search_clause != "")
                  {
                    $search_clause .= " AND ";
                  }
                  
                  $search_clause .= $temp_search_clause;
                }
              }
                
              
              break;
              
            case 'softlens':
              $command->select("
                inv.*, 
                softlens.material, softlens.nama_tipe, softlens.id_softlens_type,
                softlens.color, softlens.diameter, softlens.water,
                softlens.base_curve, softlens.permeability,
                softlens.sph_min, softlens.cyl_min");
              $command->from("inv_inventory inv");
              $command->join("inv_type_softlens softlens", "softlens.id_item = inv.id");
              
              if( $search != "" )
              {
                foreach($search_terms as $term)
                {
                  $temp_search_clause = 
                    "nama like '%$term%' OR
                    brand like '%$term%' OR
                    material like '%$term%' OR
                    nama_tipe like '%$term%' OR
                    id_softlens_type like '%$term%' OR
                    color like '%$term%' OR
                    diameter like '%$term%' OR 
                    water like '%$term%' OR 
                    base_curve like '%$term%' OR 
                    permeability like '%$term%' OR
                    sph_min like '%$term%' OR
                    cyl_min like '%$term%'";
                    
                  $temp_search_clause = "(" . $temp_search_clause . ")";
                  
                  if($search_clause != "")
                  {
                    $search_clause .= " AND ";
                  }
                  
                  $search_clause .= $temp_search_clause;
                }
              }
                
              
              break;
              
            case 'solution':
              $command->select("
                inv.*, 
                solution.*");
              $command->from("inv_inventory inv");
              $command->join("inv_type_solution solution", "solution.id_item = inv.id");
              
              if( $search != "" )
              {
                foreach($search_terms as $term)
                {
                  $temp_search_clause = 
                    "nama like '%$term%' OR
                    brand like '%$term%' OR
                    nama_tipe like '%$term%'";
                    
                  $temp_search_clause = "(" . $temp_search_clause . ")";
                  
                  if($search_clause != "")
                  {
                    $search_clause .= " AND ";
                  }
                  
                  $search_clause .= $temp_search_clause;
                }
              }
              
              break;
              
            case 'accessories':
              $command->select("
                inv.*, 
                accessories.*");
              $command->from("inv_inventory inv");
              $command->join("inv_type_accessories accessories", "accessories.id_item = inv.id");
              
              if( $search != "" )
              {
                foreach($search_terms as $term)
                {
                  $temp_search_clause = 
                    "nama like '%$term%' OR
                    brand like '%$term%' OR
                    nama_tipe like '%$term%'";
                    
                  $temp_search_clause = "(" . $temp_search_clause . ")";
                  
                  if($search_clause != "")
                  {
                    $search_clause .= " AND ";
                  }
                  
                  $search_clause .= $temp_search_clause;
                }
              }
              break;
              
            case 'services':
              $command->select("
                inv.*, 
                services.*");
              $command->from("inv_inventory inv");
              $command->join("inv_type_services services", "services.id_item = inv.id");
              
              if( $search != "" )
              {
                foreach($search_terms as $term)
                {
                  $temp_search_clause = 
                    "nama like '%$term%' OR
                    brand like '%$term%' OR
                    nama_tipe like '%$term%'";
                    
                  $temp_search_clause = "(" . $temp_search_clause . ")";
                  
                  if($search_clause != "")
                  {
                    $search_clause .= " AND ";
                  }
                  
                  $search_clause .= $temp_search_clause;
                }
              }
              break;
              
            case 'supplies':
              $command->select("
                inv.*, 
                supplies.*");
              $command->from("inv_inventory inv");
              $command->join("inv_type_supplies", "supplies.id_item = inv.id");
              
              if( $search != "" )
              {
                foreach($search_terms as $term)
                {
                  $temp_search_clause = 
                    "nama like '%$term%' OR
                    brand like '%$term%' OR
                    nama_tipe like '%$term%'";
                    
                  $temp_search_clause = "(" . $temp_search_clause . ")";
                  
                  if($search_clause != "")
                  {
                    $search_clause .= " AND ";
                  }
                  
                  $search_clause .= $temp_search_clause;
                }
              }
              break;
              
            case 'other':
              $command->select("
                inv.*, 
                other.*");
              $command->from("inv_inventory inv");
              $command->join("inv_type_other", "other.id_item = inv.id");
              
              if( $search != "" )
              {
                foreach($search_terms as $term)
                {
                  $temp_search_clause = 
                    "nama like '%$term%' OR
                    brand like '%$term%' OR
                    nama_tipe like '%$term%'";
                    
                  $temp_search_clause = "(" . $temp_search_clause . ")";
                  
                  if($search_clause != "")
                  {
                    $search_clause .= " AND ";
                  }
                  
                  $search_clause .= $temp_search_clause;
                }
              }
              break;
          }
          
          if($search_clause != "")
          {
            $search_clause = " AND " . $search_clause;
          }
          
          $command->where(
            "inv.idkategori = :idkategori AND
            inv.idsupplier = :idsupplier AND
            inv.is_deact = 0 AND
            inv.is_del = 0 
            $search_clause",
            array(
              ':idkategori' => $idkategori,
              ':idsupplier' => $idsupplier
            )
          );
          
          $command->order = "inv.nama asc";
          
          Yii::log("command = " . print_r($command, true), "info");
          
          $temp_products = $command->queryAll();
          
          $command->text = "";
          $command->offset = ($pageno - 1) * $rowsperpage;
          $command->limit = $rowsperpage;
          $products = $command->queryAll();
          
          /*
          $Criteria->order = "nama asc";
          $Criteria->offset = ($pageno - 1) * $rowsperpage;
          $Criteria->limit = $rowsperpage;
          */
          
          Yii::log('idkategori = ' . $idkategori, 'info');
          Yii::log('idsupplier = ' . $idsupplier, 'info');
          
          /*
          $products = inv_inventory::model()->findAll($Criteria);
          */
          
          $barcodeitem_list_view_name = $this->GetListViewName($tipe_produk);
          $TipeBarcodeItem_Parameter = $tipe_produk;
          $TipeBarcodeItem_Judul = FHelper::GetBarcodeItemJudul($tipe_produk); 
          
          /**/
          $table_content = $this->renderPartial(
            $barcodeitem_list_view_name,
            array(
              'products' => $products,
              'userid_actor' => $userid_actor,
              'TipeBarcodeItem_Parameter' => $TipeBarcodeItem_Parameter,
              'TipeBarcodeItem_Judul' => $TipeBarcodeItem_Judul
            ),
            true
          );
          
          $maketable = new MakeTable();
          
          $rows = count($temp_products);
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
          
          $maketable->list_type = "barcodeitem";
          $maketable->search = $search;
          $maketable->pageno = $pageno;
          $maketable->table_content = $table_content;
          $maketable->array_rows_per_page = $array_rows_per_page;
          $maketable->rows_per_page = $rowsperpage;
          $maketable->array_sort_by = $array_sort_by;
          $maketable->sort_by = $sortby;
          $maketable->array_goto_page = $array_goto_page;
          $maketable->action_name = "BarcodeItem_RefreshTable";
          $maketable->action_name2 = "BarcodeItem_RefreshTable2";
          
          $html = $maketable->Render($maketable);
          
          return $html;
        }
        catch(Exception $e)
        {
          echo "Exception: " . $e->getMessage() . "<br/>";
        }
        
          
      }
      
      public function actionGetViewContent()
      {
        $html = $this->GetViewContent();
        
        echo CJSON::encode( array('html' => $html) );
      }
	     
	     /*
	          actionSupplierList
	          
	          Deskripsi
	          Action untuk menampilkan daftar supplier berdasarkan kategori 
	          yang dipilih user.
	          
	          Parameter
	          idkategori
	               Integer. id kategori yang dipilih user.
                    
               Return
               Render list data yang berisi daftar supplier. Dibungkus dalam JSON.
	     */
	     public function actionSupplierList()
	     {
	          $userid_actor = Yii::app()->request->getParam('userid_actor');
	          $tipe_produk = Yii::app()->request->getParam('tipe_produk');
	          $idkategori = $this->GetKategoriProduk($tipe_produk);
	          
	          $Criteria = new CDbCriteria();
	          $Criteria->select = 'idsupplier';
	          $Criteria->distinct = true;
	          $Criteria->group = 'idsupplier';
               $Criteria->condition = 
                    'idkategori = :idkategori' .
                    ' AND '.
                    'is_deact = 0' .
                    ' AND '.
                    'is_del = 0';
                    
               $Criteria->params = array(
                    ':idkategori' => $idkategori
               );
               Yii::log('idkategori = ' . $idkategori, 'info');
               
               $temp_supplier_list = inv_inventory::model()->findAll($Criteria);
               
               $supplier_list = array();
               $CriteriaSupplier = new CDBCriteria();
               $CriteriaSupplier->condition = 'supplier_id = :supplier_id';
               foreach($temp_supplier_list as $temp_supplier)
               {
                    $CriteriaSupplier->params = array(':supplier_id' => $temp_supplier['idsupplier']);
                    $mtr_supplier = mtr_supplier::model()->find($CriteriaSupplier);
                    
                    $supplier_list[$mtr_supplier['supplier_id']] = $mtr_supplier['name'];
               }
               
               $html = CHtml::dropDownList(
                    'idsupplier',
                    '',
                    $supplier_list,
                    array(
                         'id' => 'idsupplier'
                    )
               );
               
               echo CJSON::encode(array('html' => $html));
	     }
	     
	     /*
	          actionBarcodeGenerate
	          
	          Deskripsi
	          Action untuk membuat dan mencetak barcode berdasarkan id inventory
	          dan jumlah barcode yang mau dibikin.
	          
	          Parameter
	          idinventory
	               Integer. Id inventory yang dipilih user untuk dibuatkan barcode.
               jumlah
                    Integer. Jumlah barcode yang mau dibikin.
                    
               Return
               Render view yang menampilkan image barcode berserta data penyertanya.
	     */
	     public function actionGenerateBarcode()
	     {
          $tipe_produk = Yii::app()->request->getParam('tipe_produk');
          $userid_actor = Yii::app()->request->getParam('userid_actor');
          $idproduk = Yii::app()->request->getParam('idproduk');
          $jumlah = Yii::app()->request->getParam('jumlah');
          $mode = Yii::app()->request->getParam('mode');
          $idlokasi = Yii::app()->request->cookies['idlokasi']->value;
          
          /*
           format barcode : yyyymmjj-hhii-xxxxx
           yyyy = tahun
           mm = bulan
           jj = tanggal
           hh = jam
           ii = menit
           xxxxx = running number
         */
         
         //ambil informasi produk spesifik berdasarkan tipe produk
         $Criteria = new CDbCriteria();
         $Criteria->condition = 'id = :idproduk';
         $Criteria->params = array(':idproduk' => $idproduk);
         
         $produk = inv_inventory::model()->find($Criteria);
         $running_number = $produk['running'];
         
         switch($tipe_produk)
         {
            case 'lensa':
                 $spesifik_produk = $produk->lensa;
                 break;
            case 'frame' :
                 $spesifik_produk = $produk->frame;
                 break;
            case 'softlens' :
                 $spesifik_produk = $produk->softlens;
                 break;
            case 'solution' :
                 $spesifik_produk = $produk->solution;
                 break;
            case 'accessories' :
                 $spesifik_produk = $produk->accessories;
                 break;
            case 'supplies' :
                 $spesifik_produk = $produk->supplies;
                 break;
            case 'other' :
                 $spesifik_produk = $produk->other;
                 break;
            case 'services' :
                 $spesifik_produk = $produk->services;
                 break;
         }
         
         $barcode_array = array();
         $CriteriaStatus = new CDbCriteria();
         for($barcodeke = 0; $barcodeke < $jumlah; $barcodeke++)
         {
            //bikin barcode
            //$barcode = date('ymd-His') . '-' . sprintf('%04d', $idproduk) . '-' . sprintf('%03d', $barcodeke);
            $running_number++;
            $barcode_display = sprintf('%04d', $idproduk) . '-' . sprintf('%07d', $running_number);
            $barcode_data = sprintf('%04d', $idproduk) . sprintf('%07d', $running_number);
            $inv_item = new inv_item();
            $inv_item['idinventory'] = $idproduk;
            $inv_item['idlokasi'] = -1;
            $inv_item['idstatus'] = -1;
            $inv_item['barcode'] = $barcode_data;
            $inv_item['waktu'] = date('Y-m-d H:i:s');
            $inv_item->save();
            $pk_item = $inv_item->getPrimaryKey();
            
            //simpan data item ke array
            Yii::log('produk[nama] = ' . $produk['nama'], 'info');
            Yii::log('produk[brand] = ' . $produk['brand'], 'info');
            
            $barcode_array['barcode_data'][] = $barcode_data;
            $barcode_array['barcode_display'][] = $barcode_display;
            $barcode_array['idproduk'][] = $idproduk;
            $barcode_array['nama'][] = $produk['nama'];
            $barcode_array['brand'][] = $produk['brand'];
            
            switch($produk['idkategori'])
            {
              case 1: //lensa
                $spesifik = $produk->lensa;
                $ukuran = 'sph=' . $spesifik['sph_min'] . '; '.
                          'cyl=' . $spesifik['cyl_min'] . ';"\n' .
                          'add=' . $spesifik['add_1'];
                $warna = $spesifik['color'];
                $tipe = $spesifik['nama_tipe'];
                break;
              case 2: //frame
                $spesifik = $produk->frame;
                
                /*
                $ukuran = 
                  'eye=' . $spesifik['eye_size'] . '; ' .
                  'dbl=' . $spesifik['dbl'] . '; ' .
                  'ed=' . $spesifik['ed'] . '; ' .
                  'gcd=' . $spesifik['gcd'] . '; ' .
                  'vert=' . $spesifik['vertical'] . '; ' .
                  'temp=' . $spesifik['temple'] . '; ';
                */
                
                $ukuran = '';
                $tipe = $spesifik['nama_tipe'];
                $warna = $spesifik['color'];
                break;
              case 3: //softlens
                $spesifik = $produk->softlens;
                $ukuran = 'sph=' . $spesifik['sph_min'];
                $tipe = $spesifik['nama_tipe'];
                $warna = $spesifik['color'];
                break;
              default:
                $ukuran = '';
                $color = '';
                break;
            }
            
            $barcode_array['ukuran'][] = $ukuran;
            $barcode_array['tipe'][] = $tipe;
            $barcode_array['warna'][] = $warna;
            $barcode_array['harga_minimum'][] = $spesifik_produk['harga_minimum'];
            
            //ambil info harga dan diskon berdasarkan idproduk dan idlokasi
            $info_harga = Yii::app()->db->createCommand()
              ->select('*')
              ->from('inv_harga_jual')
              ->where(
                'id_toko = :idlokasi AND id_item = :idproduk', 
                array(':idlokasi' => $idlokasi, ':idproduk' => $idproduk))
              ->queryAll();
            $barcode_array['harga_jual'][] = $info_harga[0]['harga_jual'];
            $barcode_array['diskon'][] = $info_harga[0]['diskon'];
            
            //catat status item ke tabel inv_latest_status - begin
            
               $CriteriaStatus->condition = 'iditem = :iditem';
               $CriteriaStatus->params = array(':iditem' => $pk_item);
               $inv_latest_status = inv_latest_status::model()->count($CriteriaStatus);
               
               if($inv_latest_status == 0)
               {
                    $inv_latest_status = new inv_latest_status();
                    $inv_latest_status['iditem'] = $pk_item;
                    $inv_latest_status['idstatus'] = -1; //-1 = belum verifikasi, 1 = gudang; 2 = out; 3 = in; 4 = customer
                    $inv_latest_status['idlokasi'] = -1;
                    $inv_latest_status['waktu'] = date('Y-m-j H:i:s');
                    $inv_latest_status['iduser'] = $userid_actor;
               }
               else
               {
                    $inv_latest_status = inv_latest_status::model()->find($CriteriaStatus);
                    $inv_latest_status['idstatus'] = -1; //-1 = belum verifikasi, 1 = gudang; 2 = out; 3 = in; 4 = customer
                    $inv_latest_status['idlokasi'] = -1;
                    $inv_latest_status['waktu'] = date('Y-m-j H:i:s');
                    $inv_latest_status['iduser'] = $userid_actor;
               }
               
               $inv_latest_status->save();
                 
            //catat status item ke tabel inv_latest_status - end
            
            //catat status item ke tabel inv_status_history - begin
               $inv_status_history = new inv_status_history();
               $inv_status_history['iditem'] = $pk_item;
               $inv_status_history['idstatus'] = -1; //-1 = belum verifikasi, 1 = gudang; 2 = out; 3 = in; 4 = customer
               $inv_status_history['idlokasi'] = -1;
               $inv_status_history['waktu'] = date('Y-m-j H:i:s');
               $inv_status_history['iduser'] = $userid_actor;
               
               $inv_status_history->save();
            //catat status item ke tabel inv_status_history - end
         } //loop barcode
         
         //update running number produk
         $produk['running'] = $running_number;
         $produk->save();
         
         if($mode == 'apli')
         {
           //pass array ke view untuk menampilkan tabel barcode
           $html = $this->renderPartial(
                //'v_print_barcode_tomjerry',
                //'v_print_barcode_koala',
                'v_print_barcode_apli',
                array(
                     'barcode_array' => $barcode_array
                ),
                true
           );
           echo CJSON::encode(array('html' => $html));
         }
         
         if($mode == 'zebra')
         {
           /*
           $html = $this->renderPartial(
                //'v_print_barcode_tomjerry',
                //'v_print_barcode_koala',
                'v_print_barcode_zebra',
                array(
                     'barcode_array' => $barcode_array
                ),
                true
           );
           */
           
           $html = $this->BikinEPL($barcode_array, $tipe_produk);
           
           $mencetak = $this->renderPartial(
             'v_mencetak',
             array('daftar_barcode' => $html),
             true
           );
           
           echo CJSON::encode(array('html' => $html, 'mencetak' => $mencetak));
         }
         //render view
               
     }
     
     private function BikinEPL($barcode_array, $tipe_produk)
     {
        $jumlah_barcode = count($barcode_array['barcode_data']);
        
        $posisi_label = 1;
        
        
        for($barcode_ke = 0; $barcode_ke < $jumlah_barcode; $barcode_ke++)
        {
          switch($posisi_label)
          {
            case 1:
              $left = 5;
              break;
            case 2:
              $left = 290;
              break;
            case 3:
              $left = 580;
              break;
          }
          
          $teks_barcode = $barcode_array['barcode_data'][$barcode_ke];
          
          $nama = $barcode_array['nama'][$barcode_ke];
          $brand = $barcode_array['brand'][$barcode_ke];
          $tipe = $barcode_array['tipe'][$barcode_ke];
          $warna = $barcode_array['warna'][$barcode_ke];
          $ukuran = $barcode_array['ukuran'][$barcode_ke];
          $harga = $barcode_array['harga_jual'][$barcode_ke];
          $diskon = $barcode_array['diskon'][$barcode_ke];
          
          $temp = array();
          
          if($posisi_label == 1)
          {
            $temp[]= 'N\n';
            $temp[]= 'q1000\n';
            $temp[]= 'Q114,22\n';
          }
          
          /*
            master EPL command template
            
            $temp[]= 'N\n';
            $temp[]= 'q1000\n';
            $temp[]= 'Q114,22\n';
            $temp[]= 'B' . $left . ',1,0,1,2,4,25,B,"' . $teks_barcode . '"\n';
            $temp[]= 'A' . $left . ',60,0,1,1,1,N,"' . $nama . '"\n';
            $temp[]= 'A' . $left . ',75,0,1,1,1,N,"' . $tipe . ' | ' . $ukuran . '"\n';
            $temp[]= 'A' . $left . ',90,0,1,1,1,N,"' . $harga . '/' . $diskon . '"\n';
          */
          
          $temp[]= 'B' . $left . ',1,0,1,2,4,25,B,"' . $teks_barcode . '"\n';
          $temp[]= 'A' . $left . ',60,0,1,1,1,N,"' . $nama . '"\n';
          
          switch($tipe_produk)
          {
            case "softlens":
              $temp[]= 'A' . $left . ',75,0,1,1,1,N,"' . $warna . ' | ' . $ukuran . '"\n';
              $temp[]= 'A' . $left . ',90,0,1,1,1,N,"' . $harga . '/' . $diskon . '"\n';
              break;
             
            //case "lensa"
            //Perubahan spesifikasi pencetakan atas permintaan user.
            //Oleh: Frans Indroyono
            //Tanggal: 2015-1021
            case "lensa":
              $temp[]= 'A' . $left . ',75,0,1,1,1,N,"' . $ukuran . '"\n';
              $temp[]= 'A' . $left . ',90,0,1,1,1,N,"' . '"\n';
              break;
              
              
            default:
              $temp[]= 'A' . $left . ',75,0,1,1,1,N,"' . $warna . ' | ' . $ukuran . '"\n';
              $temp[]= 'A' . $left . ',90,0,1,1,1,N,"' . $harga . '/' . $diskon . '"\n';
              
              break;
          }
          
          
          
          if( ($barcode_ke > 0 && $barcode_ke % 3 == 2) ||
               $barcode_ke == $jumlah_barcode - 1)
          {
            $temp[] = 'P\n';
          }
          
          $hasil[] = $temp;
          
          $posisi_label++;
          if($posisi_label == 4)
          {
            $posisi_label = 1;
          }
          
        } //loop label
     
         return $hasil;
       
     }
	     
	     /*
	          actionBarcodeImage
	          
	          Deskripsi
	          Action untuk mengembalikan image barcode berdasarkan string yang diberikan.
	          
	          Parameter
	          value
	               Sring. Nilai yang mau di konversi menjadi barcode
	     */
	     public function actionBarcodeImage()
	     {
	          $value = Yii::app()->request->getParam('value');
	          $display = Yii::app()->request->getParam('display');
	          
	          /*
                    // The arguments are R, G, B for color.
                    $color_black = new BCGColor(0, 0, 0);
                    $color_white = new BCGColor(255, 255, 255);
     
                    // Barcode Part
                    $code = new BCGcode39();
                    $code->setScale(2);
                    $code->setThickness(30);
                    $code->setForegroundColor($color_black);
                    $code->setBackgroundColor($color_white);
                    //$code->setFont($font);
                    $code->setChecksum(false);
                    $code->parse($value);
                     
                    // Drawing Part
                    $drawing = new BCGDrawing('', $color_white);
                    $drawing->setBarcode($code);
                    $drawing->draw();
                     
                    header('Content-Type: image/png');
                     
                    $drawing->finish(BCGDrawing::IMG_FORMAT_PNG);
               */
               
               /*
                    barcode::Barcode39($value, 160, 80, 100, 1, '');
               */
               
               $bc = new BarcodeGenerator();
               $bc->init('jpeg', 50, 1, 2, 5);
               $bc->build($display, $value, true);
	     }
	
	//inventory - barcode - end
	
	
	
	/* inventory - transfer order - begin */
	
	  public function actionSample()
	  {
	    $userid_actor = Yii::app()->request->getParam('userid_actor');
	    $this->menuid = 
	    $this->parentmenuid = 
	    $this->userid_actor = $userid_actor;
	    $idgroup = FHelper::GetGroupId($userid_actor);
	    
	    
	    if(FHelper::AllowMenu($this->menuid, $idgroup, 'read'))
	    {
	      //do this
	    }
	    else
	    {
	      $this->actionShowInvalidAccess($userid_actor, false);
	    }
	  }
	
	  /*
	    actionTransferOrder
	    
	    Deskripsi
	    Action untuk menampilkan index view transfer order.
	  */
	  public function actionTransferOrder()
	  {
	    $this->menuid = 36;
	    $this->parentmenuid = 9; 
	    $this->userid_actor = Yii::app()->request->cookies['userid_actor']->value;
	    $this->idlokasi = Yii::app()->request->cookies['idlokasi']->value;
	    $idgroup = FHelper::GetGroupId($this->userid_actor);
	    
	    
	    if(FHelper::AllowMenu($this->menuid, $idgroup, 'read'))
	    {
	      $this->layout = 'layout-baru';
	      
	      $list_index = $this->renderPartial(
	        'index_transferorder_list',
	        array(
	          
          ),
	        true
        );
	      
	      $this->render(
	        'index_general',
	        array(
	          'TheContent' => $list_index
          )
        );
	    }
	    else
	    {
	      $url = $this->createUrl(
	        'index/showinvalidaccess',
	        array('userid_actor' => $this->userid_actor)
        );
        
	      $this->redirect($url);
	    }
	  }
	  
	  /*
	    actionTransferOrderShowMain
	    
	    Deskripsi
	    Action untuk menampilkan index view transfer order.
	  */
	  public function actionTransferOrderShowMain()
	  {
	    $this->userid_actor = Yii::app()->request->cookies['userid_actor']->value;
	    $this->menuid = 36;
	    $this->parentmenuid = 9; 
	    $idgroup = FHelper::GetGroupId($this->userid_actor);
	    
	    
	    if(FHelper::AllowMenu($this->menuid, $idgroup, 'read'))
	    {
	      $html = $this->renderPartial(
	        'index_transferorder_list',
	        array(
          ),
          true
        );
        
        echo CJSON::encode(
          array(
            'html' => $html,
            'valid' => 'ok'
          )
        );
	    }
	    else
	    {
	      echo CJSON::encode(
	        array(
	          'valid' => 'not ok'
          )
        );
	    }
	  }
	  
	  /*
	    actionTransferOrderShowList
	    
	    Deskripsi
	    Action untuk menampilkan daftar transfer order berdasarkan tipe transfer.
	    
	    Parameter
	    tipe_transfer
	      Integer. 1 = [a] --> b; 2 = a --> [b]; 3 = [a] <-- b; 4 = a <-- [b]
	      
      Return
      Render daftar transfer order dibungkus dalam json.
	  */
    public function actionTransferOrderShowList()
    {
      $this->userid_actor = Yii::app()->request->cookies['userid_actor']->value;
      $idlokasi_cookie = Yii::app()->request->cookies['idlokasi']->value;
      $this->menuid = 36;
	    $this->parentmenuid = 9; 
      $idgroup = FHelper::GetGroupId($this->userid_actor);
      Yii::log('idgroup = ' . $idgroup, 'info');
      
      if(FHelper::AllowMenu($this->menuid, $idgroup, 'read'))
      {
        $tipe_transfer = Yii::app()->request->getParam('tipe_transfer');
        
        
        switch($tipe_transfer)
        {
          case 1: // [A] -> B (melihat pengiriman yang saya bikin)
            $viewname = 'v_list_transfer_type1';
            
            $Criteria = new CDbCriteria();
            $Criteria->condition =
              'idlokasidari = :idlokasi'.
              ' AND '.
              'tipe = 1' . 
              ' AND '.
              '(idstatus = 1 OR idstatus = 2)';
              //1 = sedang dibuat; 2 = disetujui
            $Criteria->params = array(
              ':idlokasi' => $idlokasi_cookie
            );
            $transfer_list = inv_transfer::model()->findAll($Criteria);
            break;
          case 4: // A <- [B] (melihat pengiriman yang dikirimkan oleh dia)
            $viewname = 'v_list_transfer_type4';
            
            $Criteria = new CDbCriteria();
            $Criteria->condition =
              'idlokasike = :idlokasi'.
              ' AND '.
              'tipe = 1' . //dia yang kirim ke saya 
              ' AND '.
              '(idstatus = 3 OR idstatus = 4)';
              //3 = sedang dikirim; 4 = pengiriman sudah diterima
            $Criteria->params = array(
              ':idlokasi' => $idlokasi_cookie
            );
            
            $transfer_list = inv_transfer::model()->findAll($Criteria);
            break;
            
            
          case 3: // [A] <- B (melihat pengiriman yang diminta oleh saya)
            $viewname = 'v_list_transfer_type3';
            
            $Criteria = new CDbCriteria();
            $Criteria->condition =
              'idlokasike = :idlokasi'.
              ' AND '.
              'tipe = 3' . //saya minta dari dia 
              ' AND '.
              '(idstatus = 1 OR idstatus = 2 OR idstatus = 203 OR idstatus = 204)';
              //1 = sedang dibikin; 2 = disetujui; 203 = sedang dikirim; 204 = permintaan sudah diterima
            $Criteria->params = array(
              ':idlokasi' => $idlokasi_cookie
            );
            
            $transfer_list = inv_transfer::model()->findAll($Criteria);
            break;
          case 2: // A -> [B] (melihat pengiriman yang diminta dia)
            $viewname = 'v_list_transfer_type2';
            
            $Criteria = new CDbCriteria();
            $Criteria->condition =
              'idlokasidari = :idlokasi'.
              ' AND '.
              'tipe = 3' . //saya minta dari dia 
              ' AND '.
              '(idstatus = 200 OR idstatus = 201 OR idstatus = 202)';
              //200 = daftar permintaan sedang dikirim; 201 = sedang diisi barang; 202 = disetujui
            $Criteria->params = array(
              ':idlokasi' => $idlokasi_cookie
            );
            
            $transfer_list = inv_transfer::model()->findAll($Criteria);
            break;
          
        }
        
        $html = $this->renderPartial(
          $viewname,
          array(
            'transfer_list' => $transfer_list,
            'userid_actor' => $this->userid_actor,
            'menuid' => $this->menuid
          ),
          true
        );
        
        echo CJSON::encode(
          array(
            'html' => $html,
            'valid' => 'ok',
            'userid_actor' => $this->userid_actor
          )
        );
      }
      else
      {
        echo CJSON::encode(
          array(
            'valid' => 'not ok',
            'userid_actor' => $this->userid_actor
          )
        );
      }
    }
    
    /*
      actionTransferOrderSimpanKeterangan
      
      Deskripsi
      Fungsi untuk menyimpan keterangan suatu transfer order.
      
      Parameter
      idtransfer
        Integer
        
      Result
      Mengembalikan tulisan yang menandakan keterangan sudah disimpan.
    */
    public function actionTransferOrderSimpanKeterangan()
    {
      $form = new frmEditTransferOrder();
      $form->attributes = Yii::app()->request->getParam('frmEditTransferOrder');
      $idtransfer = $form['id'];
      
      $Criteria = new CDbCriteria();
      $Criteria->condition = 'id = :idtransfer';
      $Criteria->params = array(':idtransfer' => $idtransfer);
      
      try
      {
        $inv_transfer = inv_transfer::model()->find($Criteria);
        $inv_transfer['keterangan'] = $form['keterangan'];
        $inv_transfer->save();
        
        echo CJSON::encode(
          array(
            'pesan' => 'Keterangan berhasil disimpan'
          )
        );
      }
      catch(Exception $e)
      {
        echo CJSON::encode(
          array(
            'pesan' => 'Keterangan gagal disimpan. Coba lagi...'
          )
        );
      }
    }
    
    
    //----- transfer order type1 - begin
    
    
    
    /*
      actionTransferOrderType1Add
      
      Deskripsi
      Action untuk menampilkan form pembuatan transfer order type1.
    */
    public function actionTransferOrderType1Add()
    {
      $this->userid_actor = Yii::app()->request->cookies['userid_actor']->value;
      $this->menuid = 36;
	    $this->parentmenuid = 9;
	    $idlokasi = Yii::app()->request->cookies['idlokasi']->value;
      $idgroup = FHelper::GetGroupId($this->userid_actor);
      
      if(FHelper::AllowMenu($this->menuid, $idgroup, 'read'))
      {
        //$daftar_kurir = FHelper::GetDaftarKurir();
        $daftar_kurir = FHelper::GetKaryawanListData();
        
        $daftar_lokasi = FHelper::GetLocationListData();
        unset($daftar_lokasi[0]);
        
        $do_add = Yii::app()->request->getParam('do_add');
        
        if(isset($do_add))
        {
          $form = new frmEditTransferOrder();
          $form->attributes = Yii::app()->request->getParam('frmEditTransferOrder');
          
          $validate_list[] = 'tanggal_kirim';
          
          if($form->validate($validate_list))
          {
            //masukkan data ke tabel
            $inv_transfer = new inv_transfer();
            $inv_transfer['iduserbikin'] = $this->userid_actor;
            $inv_transfer['tanggal_bikin'] = date('Y-m-j H:i:s');
            $inv_transfer['tanggal_kirim'] = date('Y-m-j', strtotime($form['tanggal_kirim']));
            $inv_transfer['idlokasidari'] = $idlokasi;
            $inv_transfer['idlokasike'] = $form['idlokasike'];
            $inv_transfer['keterangan'] = $form['keterangan'];
            $inv_transfer['last_update'] = date('Y-m-j H:i:s');
            $inv_transfer['update_by'] = $this->userid_actor;
            $inv_transfer['tipe'] = 1;
            $inv_transfer['idstatus'] = 1; //1 = sedang dibuat; 2 = disetujui; 3 = sedang dikirim; 4 = sudah diterima
            
            $inv_transfer->save();
            $pk_transfer = $inv_transfer->getPrimaryKey();
            $form['id'] = $pk_transfer;
            
            //tampilkan form edit transfer order - begin
            
              //ambil record daftar itam yang sudah dibacakan untuk transfer order ini
              $Criteria = new CDbCriteria();
              $Criteria->condition = 'idtransfer = :idtransfer';
              $Criteria->params = array(':idtransfer' => $pk_transfer);
              
              $html = $this->renderPartial(
                'vfrm_edittransferorder_type1',
                array(
                  'form' => $form,
                  'idstatus' => 1,
                  'idtransfer' => $pk_transfer,
                  'userid_actor' => $this->userid_actor,
                  'daftar_kurir' => $daftar_kurir,
                  'daftar_lokasi' => $daftar_lokasi
                ),
                true
              );
            
            //tampilkan form edit transfer order - end
            
            echo CJSON::encode(
              array(
                'valid' => 'ok',
                'html' => $html,
                'userid_actor' => $this->userid_actor,
              )
            );
          }
          else
          {
            //tampilkan form add transfer order (karena validation failed)
            
            $html = $this->renderPartial(
              'vfrm_addtransferorder_type1',
              array(
                'form' => $form,
                'userid_actor' => $this->userid_actor,
                'daftar_kurir' => $daftar_kurir,
                'daftar_lokasi' => $daftar_lokasi
              ),
              true
            );
            
            echo CJSON::encode(
              array(
                'valid' => 'ok',
                'html' => $html,
                'userid_actor' => $this->userid_actor,
              )
            );
          }
        }
        else
        {
          //tampilkan form pembuatan transfer order
          $form = new frmEditTransferOrder();
          
          $html = $this->renderPartial(
            'vfrm_addtransferorder_type1',
            array(
              'form' => $form,
              'userid_actor' => $this->userid_actor,
              'daftar_kurir' => $daftar_kurir,
              'daftar_lokasi' => $daftar_lokasi
            ),
            true
          );
          
          echo CJSON::encode(
            array(
              'valid' => 'ok',
              'html' => $html,
              'userid_actor' => $this->userid_actor,
            )
          );
        }
      }
      else
      {
        echo CJSON::encode(
          array(
            'valid' => 'not ok',
            'userid_actor' => $this->userid_actor,
          )
        );
      }
    }
    
    /*
      actionTransferOrderType1Edit
      
      Deskripsi
      Action untuk menampilkan form edit transfer order type1. Tidak perlu 
      ada proses validasi form transfer order, karena transfer order type1
      hanya menambah atau mengurangi daftar barang. Penambahan atau pengurangan
      barang dilakukan lewat action yang lain.
    */
    public function actionTransferOrderType1Edit()
    {
      $this->userid_actor = Yii::app()->request->cookies['userid_actor']->value;
      $idtransfer = Yii::app()->request->getParam('idtransfer');
      $this->menuid = 36;
	    $this->parentmenuid = 9; 
      $idgroup = FHelper::GetGroupId($this->userid_actor);
      
      if(FHelper::AllowMenu($this->menuid, $idgroup, 'edit'))
      {
        //$daftar_kurir = FHelper::GetDaftarKurir();
        $daftar_kurir = FHelper::GetKaryawanListData();
        
        $daftar_lokasi = FHelper::GetLocationListData();
        unset($daftar_lokasi[0]);
        
        //tampilkan form edit transfer order
        $form = new frmEditTransferOrder();
        
        //ambil record inv_transfer berdasarkan idtransfer
        $Criteria = new CDbCriteria();
        $Criteria->condition = 'id = :idtransfer';
        $Criteria->params = array(':idtransfer' => $idtransfer);
        
        $inv_transfer = inv_transfer::model()->find($Criteria);
        $form['tanggal_kirim'] = date('D, d M Y', strtotime($inv_transfer['tanggal_kirim']));
        $form['id'] = $inv_transfer['id'];
        $form['idlokasike'] = $inv_transfer['idlokasike'];
        $form['keterangan'] = $inv_transfer['keterangan'];
        $form['idstatus'] = $inv_transfer['idstatus']; //1 = sedang dibuat; 2 = disetujui; 3 = sedang dikirim; 4 = sudah diterima
        
        //ambil daftar summary
        $Criteria->condition = 'idtransfer = :idtransfer';
        $Criteria->params = array(':idtransfer' => $idtransfer);
        $daftar_summary = inv_transfer_summary::model()->findAll($Criteria);
        
        //ambil daftar detail
        $daftar_item = Yii::app()->db->createCommand()
          ->select('detail.*')
          ->from('inv_transfer_detail detail')
          ->join('inv_transfer_summary summary', 'detail.idsummary = summary.id')
          ->join('inv_transfer transfer', 'summary.idtransfer = transfer.id')
          ->where('transfer.id = :idtransfer', array(':idtransfer' => $idtransfer))
          ->queryAll();
        
        $html = $this->renderPartial(
          'vfrm_edittransferorder_type1',
          array(
            'form' => $form,
            'idstatus' => $form['idstatus'],
            'idtransfer' => $idtransfer,
            'userid_actor' => $this->userid_actor,
            'daftar_kurir' => $daftar_kurir,
            'daftar_lokasi' => $daftar_lokasi,
            'daftar_item' => $daftar_item,
            'daftar_summary' => $daftar_summary
          ),
          true
        );
        
        echo CJSON::encode(
          array(
            'valid' => 'ok',
            'html' => $html,
            'userid_actor' => $this->userid_actor,
          )
        );
      }
      else
      {
        //pelanggaran hak akses
        
        echo CJSON::encode(
          array(
            'valid' => 'not ok',
            'userid_actor' => $this->userid_actor,
          )
        );
      }
    }
    
    /*
      actionTransferOrderType1TambahItem
      
      Deskripsi
      Action untuk menambahkan item ke transfer order
      
      Parameter
      barcode
        String
      idtransfer
        Integer
      mode
        String. 'tambah' atau 'hapus'.
        
      Return
      Render view list item yang sudah dibaca dan view summary-nya.
    */
    public function actionTransferOrderType1TambahItem()
    {
      $this->userid_actor = Yii::app()->request->cookies['userid_actor']->value;
      $idlokasi_cookie = Yii::app()->request->cookies['idlokasi']->value;
      $idtransfer = Yii::app()->request->getParam('idtransfer');
      $barcode = Yii::app()->request->getParam('barcode');
      $mode = Yii::app()->request->getParam('mode');
      $this->menuid = 36;
	    $this->parentmenuid = 9; 
      $idgroup = FHelper::GetGroupId($this->userid_actor);
      
      //periksa apakah barcode ada di inv_item dengan idlokasi yang cocok dengan idlokasi login
        $Criteria = new CDbCriteria();
        $Criteria->condition =
          'idlokasi = :idlokasi'.
          ' AND '.
          'barcode = :barcode';
        $Criteria->params = array(
          ':barcode' => $barcode,
          ':idlokasi' => $idlokasi_cookie
        );
        $count = inv_item::model()->count($Criteria);
      
      if($count  == 1)
      {
        //item ditemukan...
        $item = inv_item::model()->find($Criteria);
        $idlokasi_item = $item['idlokasi'];
        $idproduk = $item['idinventory'];
        $iditem = $item['id'];
        
        switch($mode)
        {
          case 'tambah' :
            
            //masukkan ke inv_transfer_detail - begin

              //pastikan statusnya = 1 (sedang di gudang) atau 3 (sedang di toko)
              switch($item['idstatus'])
              {
              case 1://sedang di gudang
              case 3://sedang di toko
                
                //update dulu record di inv_transfer_summary - begin
              
                  //periksa apakah produk sudah ada dalam inv_transfer_summary
                    $Criteria->condition = 'idproduk = :idproduk AND idtransfer = :idtransfer';
                    $Criteria->params = array(
                      ':idproduk' => $idproduk, 
                      ':idtransfer' => $idtransfer
                    );
                    $count = inv_transfer_summary::model()->count($Criteria);
                  
                  if($count == 0)
                  {
                    //tambahkan record summary yang baru
                    $inv_transfer_summary = new inv_transfer_summary();
                    $inv_transfer_summary['idtransfer'] = $idtransfer;
                    $inv_transfer_summary['idproduk'] = $idproduk;
                    $inv_transfer_summary['jumlah'] = 1;
                    
                    $inv_transfer_summary->save();
                    $pk_summary = $inv_transfer_summary->getPrimaryKey();
                  }
                  else
                  {
                    //update record summary yang sudah ada
                    $Criteria->condition = 'idproduk = :idproduk AND idtransfer = :idtransfer';
                    $Criteria->params = array(
                      ':idproduk' => $idproduk, 
                      ':idtransfer' => $idtransfer
                    );
                  
                    $inv_transfer_summary = inv_transfer_summary::model()->find($Criteria);
                    $inv_transfer_summary['jumlah'] = $inv_transfer_summary['jumlah'] + 1;
                    
                    $inv_transfer_summary->update();
                    $pk_summary = $inv_transfer_summary['id'];
                  }
                
                //update dulu record di inv_transfer_summary - end
                
                //masukkan item ke inv_transfer_detail - begin
                  $inv_transfer_detail = new inv_transfer_detail();
                  $inv_transfer_detail['idsummary'] = $pk_summary;
                  $inv_transfer_detail['iditem'] = $iditem;
                  
                  $inv_transfer_detail->save();
                //masukkan item ke inv_transfer_detail - end
                
                
                //update status item di tabel inv_item - begin
                  $Criteria = new CDbCriteria();
                  $Criteria->condition =
                    'idlokasi = :idlokasi'.
                    ' AND '.
                    'barcode = :barcode';
                  $Criteria->params = array(
                    ':barcode' => $barcode,
                    ':idlokasi' => $idlokasi_cookie
                  );
                  
                  $item = inv_item::model()->find($Criteria);
                  $item['idstatus'] = 2;
                  $item->save();
                //update status item di tabel inv_item - end
                
                //update trans_history dan latest_status
                FHelper::ItemStatusUpdate($iditem, 2, $idlokasi_cookie, $this->userid_actor, -1, $idtransfer);
                
                //update inv_transfer - begin
                  $Criteria->condition = 'id = :idtransfer';
                  $Criteria->params = array(':idtransfer' => $idtransfer);
                  
                  $inv_transfer = inv_transfer::model()->find($Criteria);
                  $inv_transfer['last_update'] = date('Y-m-d H:i:s');
                  $inv_transfer->save();
                //update inv_transfer - end
            
                //refresh list
                $this->actionTransferOrderType1RefreshList();
                
                break;
              case 2://sedang di luar gudang/toko
                echo CJSON::encode(
                  array(
                    'status' => 'not ok',
                    'valid' => 'ok',
                    'pesan' => 'Item sudah dibacakan'
                  )
                );
                break;
              case 4://di customer
                echo CJSON::encode(
                  array(
                    'status' => 'not ok',
                    'valid' => 'ok',
                    'pesan' => 'Item ada pada customer'
                  )
                );
                break;
              }
            
            //masukkan ke inv_transfer_detail - end
            break;
          case 'hapus' :
            
            //pastikan item yang dibacakan ada dalam inv_transfer_detail
            
            if($item['idstatus'] == 2)
            {
              //update/hapus record dari inv_transfer_summary - begin
        
                //ambil idproduk berdasarkan iditem
                $CriteriaItem = new CDbCriteria();
                $CriteriaItem->condition = 'id = :iditem';
                $CriteriaItem->params = array(':iditem' => $iditem);
                $inv_item = inv_item::model()->find($CriteriaItem);
                $idproduk = $inv_item['idinventory'];
              
                $CriteriaSummary = new CDbCriteria();
                $CriteriaSummary->condition = 
                  'idproduk = :idproduk'. 
                  ' AND ' . 
                  'idtransfer = :idtransfer';
                $CriteriaSummary->params = array(
                  ':idproduk' => $idproduk, 
                  ':idtransfer' => $idtransfer
                );
                $count = inv_transfer_summary::model()->count($CriteriaSummary);
                
                if($count > 0)
                {
                  $inv_transfer_summary = inv_transfer_summary::model()->find($CriteriaSummary);
                  $pk_summary = $inv_transfer_summary['id'];
                  
                  
                  //hapus dari inv_transfer_detail - begin
                    $CriteriaDetail = new CDbCriteria();
                    $CriteriaDetail->condition = 'iditem = :iditem AND idsummary = :idsummary';
                    $CriteriaDetail->params = array(
                      ':iditem' => $iditem,
                      ':idsummary' => $pk_summary
                    );
                    $inv_transfer_detail = inv_transfer_detail::model()->find($CriteriaDetail);
                    $inv_transfer_detail->delete();
                  
                  //hapus dari inv_transfer_detail - end
                  
                  
                  $inv_transfer_summary['jumlah'] = $inv_transfer_summary['jumlah'] - 1;
                  
                  if($inv_transfer_summary['jumlah'] <= 0)
                  {
                    $inv_transfer_summary->delete();
                  }
                  else
                  {
                    $inv_transfer_summary->save();
                  }
                }
              
              //update/hapus record dari inv_transfer_summary - end
              
              
              
              
              //update status item di inv_item - begin
                $Criteria = new CDbCriteria();
                $Criteria->condition =
                  'idlokasi = :idlokasi'.
                  ' AND '.
                  'barcode = :barcode';
                $Criteria->params = array(
                  ':barcode' => $barcode,
                  ':idlokasi' => $idlokasi_cookie
                );
                
                $item = inv_item::model()->find($Criteria);
                $item['idstatus'] = 1;
                $item->save();
              //update status item di inv_item - end
              
              //update trans_history dan latest_status
              FHelper::ItemStatusUpdate($iditem, 1, $idlokasi_cookie, $this->userid_actor, -1, $idtransfer);
              
              //update inv_transfer - begin
                $Criteria->condition = 'id = :idtransfer';
                $Criteria->params = array(':idtransfer' => $idtransfer);
                
                $inv_transfer = inv_transfer::model()->find($Criteria);
                $inv_transfer['last_update'] = date('Y-m-d H:i:s');
                $inv_transfer->save();
              //update inv_transfer - end
              
              //render view list detail dan summary
              $this->actionTransferOrderType1RefreshList();
            }
            else
            {
              //status item bukan 2.
              
              echo CJSON::encode(
                array(
                  'status' => 'not ok',
                  'valid' => 'ok',
                  'pesan' => 'Item belum pernah dibacakan'
                )
              );
            }
            
            break;
        }//switch(mode)
      }
      else
      {
        //item tidak ditemukan dalam inv_item berdasarkan barcode dan idlokasi_cookie
        
        //menyusun kalimat pemberitahuan kepada user
        $Criteria = new CDbCriteria();
        $Criteria->condition =
          'barcode = :barcode';
        $Criteria->params = array(
          ':barcode' => $barcode
        );
        $item = inv_item::model()->find($Criteria);
                
        switch($item['idstatus'])
        {
          case 1: //gudang
            $status = 'ada di gudang';
            break;
          case 2: //out
            $status = 'sedang ditransfer';
            break;
          case 3: //in
            $status = 'ada di toko';
            break;
          case 4: //customer
            $status = 'sudah dibeli customer';
            break;
          default: //cuatomer
            $status = 'tidak dikenal';
            break;
        }
        
        $Criteria = new CDbCriteria();
        $Criteria->condition = 'branch_id = :idlokasi';
        $Criteria->params = array(':idlokasi' => $item['idlokasi']);
        $mtr_branch = mtr_branch::model()->find($Criteria);
        
        if($mtr_branch != null)
        {
          $lokasi = $mtr_branch['name'];
        }
        else
        {
          $lokasi = ' tidak diketahui';
        }
        
        echo CJSON::encode(
          array(
            'status' => 'not ok',
            'valid' => 'ok',
            'pesan' => 'Item ' . $status . ', lokasi: ' . $lokasi
          )
        );
      }
    }
	
    /*
      actionTransferOrderType1RefreshList
      
      Deskripsi
      Action untuk menampilkan list detail dan summary suatu transfer order
      berdasarkan idtransfer.
      
      Parameter
      idtransfer
        Integer
        
      Return
      Render view list detail dan list summary.
    */
    public function actionTransferOrderType1RefreshList()
    {
      $idtransfer = Yii::app()->request->getParam('idtransfer');
      
      //ambil daftar detail
      $daftar_detail = Yii::app()->db->createCommand()
        ->select('detail.*')
        ->from('inv_transfer_detail detail')
        ->join('inv_transfer_summary summary', 'detail.idsummary = summary.id')
        ->join('inv_transfer transfer', 'summary.idtransfer = transfer.id')
        ->where('transfer.id = :idtransfer', array(':idtransfer' => $idtransfer))
        ->queryAll();
      
      $Criteria = new CDbCriteria();
      $Criteria->condition = 'idtransfer = :idtransfer';
      $Criteria->params = array(':idtransfer' => $idtransfer);
      $daftar_summary = inv_transfer_summary::model()->findAll($Criteria);
      
      $Criteria->condition = 'id = :idtransfer';
      $Criteria->params = array(':idtransfer' => $idtransfer);
      $inv_transfer = inv_transfer::model()->find($Criteria);
      
      //render view list detail dan view list summary
      $view_summary = $this->renderPartial(
        'v_list_summary_transferorder_type1',
        array(
          'daftar_summary' => $daftar_summary
        ),
        true
      );
      
      $view_detail = $this->renderPartial(
        'v_list_item_transferorder_type1',
        array(
          'daftar_detail' => $daftar_detail,
          'idstatus' => $inv_transfer['idstatus'],
          'idtransfer' => $idtransfer
        ),
        true
      );
      
      echo CJSON::encode(
        array(
          'valid' => 'ok',
          'status' => 'ok',
          'daftar_detail' => $view_detail,
          'daftar_summary' => $view_summary,
          'jumlah_summary' => count($daftar_summary),
          'jumlah_detail' => count($daftar_detail)
        )
      );
    }
    
    public function actionTransferOrderType1Approval()
	  {
	    $this->userid_actor = Yii::app()->request->cookies['userid_actor']->value;
	    $this->menuid = 36;
	    $this->parentmenuid = 9; 
	    $idgroup = FHelper::GetGroupId($this->userid_actor);
	    
	    if(FHelper::AllowMenu($this->menuid, $idgroup, 'edit'))
	    {
	      //do this
	      $form = new frmEditTransferOrder();
	      $form->attributes = Yii::app()->request->getParam('frmEditTransferOrder');
	      
	      $Criteria = new CDbCriteria();
	      $Criteria->condition = 'id = :idtransfer';
	      $Criteria->params = array(':idtransfer' => $form['id']);
	      
	      $inv_transfer = inv_transfer::model()->find($Criteria);
	      $inv_transfer['last_update'] = date('Y-m-d H:i:s');
	      $inv_transfer['idstatus'] = 2;
	      $inv_transfer->save();
	      
	      $this->actionTransferOrderType1Edit();
	    }
	    else
	    {
	      echo CJSON::enode(array('valid' => 'not ok'));
	    }
	  }
	  
	  public function actionTransferOrderType1Kirim()
	  {
	    $this->userid_actor = Yii::app()->request->cookies['userid_actor']->value;
	    $this->menuid = 36;
	    $this->parentmenuid = 9; 
	    $idgroup = FHelper::GetGroupId($this->userid_actor);
	    
	    if(FHelper::AllowMenu($this->menuid, $idgroup, 'edit'))
	    {
	      //do this
	      $form = new frmEditTransferOrder();
	      $form->attributes = YIi::app()->request->getParam('frmEditTransferOrder');
	      
	      $Criteria = new CDbCriteria();
	      $Criteria->condition = 'id = :idtransfer';
	      $Criteria->params = array(':idtransfer' => $form['id']);
	      
	      $inv_transfer = inv_transfer::model()->find($Criteria);
	      $inv_transfer['last_update'] = date('Y-m-d H:i:s');
	      $inv_transfer['idstatus'] = 3;
	      $inv_transfer->save();
	      
	      $this->actionTransferOrderType1Edit();
	    }
	    else
	    {
	      echo CJSON::enode(array('valid' => 'not ok'));
	    }
	  }
	  
	  public function actionTransferOrderType1_DownloadDaftarBarang()
	  {
	    $this->userid_actor = Yii::app()->request->cookies['userid_actor']->value;
	    $this->menuid = 36;
	    $this->parentmenuid = 9; 
	    $idgroup = FHelper::GetGroupId($this->userid_actor);
	    
	    if(FHelper::AllowMenu($this->menuid, $idgroup, 'read'))
	    {
	      //ambil idtransferorder
	      $idtransferorder = Yii::app()->request->getParam('idtransfer');
	      
	      //ambil info transfer order
	      $command = Yii::app()->db->createCommand()
	        ->select('*')
	        ->from('inv_transfer')
	        ->where('id = :idtransferorder', array(':idtransferorder' => $idtransferorder));
	      $transfer = $command->queryRow();
	      
	      //ambil daftar barang yang dikirim
	      $command = Yii::app()->db->createCommand()
	        ->select('inventory.nama, inventory.id, item.barcode')
	        ->from('inv_transfer_detail detil')
	        ->join('inv_transfer_summary summary', 'detil.idsummary = summary.id')
	        ->join('inv_transfer transfer', 'summary.idtransfer = transfer.id')
	        ->join('inv_item item', 'item.id = detil.iditem')
	        ->join('inv_inventory inventory', 'item.idinventory = inventory.id')
	        ->where('transfer.id = :idtransfer', array(':idtransfer' => $idtransferorder))
	        ->order('item.idinventory asc');
	      $daftar_barang = $command->queryAll();
	      
	      //bikin file excelnya
	      $nama_file = $this->TransferOrderType1_BikinDaftarBarang($transfer, $daftar_barang);
	      
	      //kembalikan url untuk ambil file excel
	      $html = CHtml::link('ambil file', $nama_file);
	      
	      echo CJSON::encode(array('valid' => 'ok', 'html' => $html));
	    }
	    else
	    {
	      echo CJSON::enode(array('valid' => 'not ok'));
	    }
	  }
	  
	  private function TransferOrderType1_BikinDaftarBarang($transfer, $daftar_barang)
	  {
	    //ambil info asal-tujuan
	    $dari = FHelper::GetLocationName($transfer['idlokasidari'], true);
	    $ke = FHelper::GetLocationName($transfer['idlokasike'], true);
	    $idkurir = FHelper::GetNamaKurir($transfer['idkurir']);
	    
	    //ambil info tanggal bikin
	    $tanggal_bikin = date('d-M-Y', strtotime($transfer['tanggal_bikin']));
	    
	    //ambil info tanggal kirim
	    $tanggal_kirim = date('d-M-Y', strtotime($transfer['tanggal_kirim']));
	    
	    //siapkan object phpexcel
	    $xls = new PHPExcel();
	    $ws = $xls->getActiveSheet();
	    $ws->setTitle('Daftar Barang');
	    $kolom = 0;
	    $baris = 1;
	    
	    //tulis info transfer order
	    $ws->setCellValueByColumnAndRow($kolom, $baris, 'Dari'); $kolom++;
	    $ws->setCellValueByColumnAndRow($kolom, $baris, $dari);
	    
	    $baris++; $kolom = 0;
	    $ws->setCellValueByColumnAndRow($kolom, $baris, 'Ke'); $kolom++;
	    $ws->setCellValueByColumnAndRow($kolom, $baris, $ke);
	    
	    $baris++; $kolom = 0;
	    $ws->setCellValueByColumnAndRow($kolom, $baris, 'Dibuat'); $kolom++;
	    $ws->setCellValueByColumnAndRow($kolom, $baris, $tanggal_bikin); $kolom++;
	    
	    $baris++; $kolom = 0;
	    $ws->setCellValueByColumnAndRow($kolom, $baris, 'Dikirim'); $kolom++;
	    $ws->setCellValueByColumnAndRow($kolom, $baris, $tanggal_kirim); $kolom++;
	    
	    $baris++; $kolom = 0;
	    $ws->setCellValueByColumnAndRow($kolom, $baris, 'Kurir'); $kolom++;
	    $ws->setCellValueByColumnAndRow($kolom, $baris, $nama_kurir); $kolom++;
	    
	    //tulis daftar barang
	    $baris++; $baris++; $kolom = 0;
	    
	    $nama_barang = "";
	    $ukuran = "";
	    $idbarang = -1;
	    foreach($daftar_barang as $barang)
	    {
	      $temp_ukuran = FHelper::GetProdukUkuran($barang['id']);
	      $temp_idbarang = $barang['id'];
	      
	      if($idbarang != $temp_idbarang)
	      {
	        $baris++; $baris++; $kolom = 0;
	        $ws->setCellValueByColumnAndRow($kolom, $baris, "Jumlah : $jumlah_barang"); $baris++;
	        
	        
	        $jumlah_barang = 0;
	        $idbarang = $temp_idbarang;
	        $baris++; $baris++; $kolom = 0;
	        $ukuran = FHelper::GetProdukUkuran($idbarang);
	        
	        $ws->setCellValueByColumnAndRow($kolom, $baris, $barang['nama']); $baris++;
	        $ws->setCellValueByColumnAndRow($kolom, $baris, $ukuran); $baris++;
	        
	        $nama_barang = $barang['nama'];
	      }
	      
	      $ws->setCellValueExplicitByColumnAndRow($kolom, $baris, $barang['barcode']); $kolom++;
	      $jumlah_barang++;
	      
	      if( $kolom % 5 == 0 )
	      {
	        $kolom = 0;
	        $baris++;
	      }
	    }
	    
	    //kembalikan nama file
	    $file_name = "transfer_order_{$transfer['id']}_{$tanggal_bikin}.xlsx";
	    
	    $writer = new PHPExcel_Writer_Excel2007($xls);
	    $writer->save($file_name);
	    
	    return $file_name;
	  }
	  
	  
	  
	  //----- transfer order type1 - end 
	  
	  
	  
	  
	  
	  
	  //----- transfer order type2 - begin
	  
	  /*
	    actionTransferOrderType2Edit
	    
	    Deskripsi
	    Action untuk update status transfer order type2. (proses pengiriman barang 
	    akibat permintaan dari dia.)
	  */
	  public function actionTransferOrderType2Edit()
	  {
	    $this->userid_actor = Yii::app()->request->cookies['userid_actor']->value;
      $idtransfer = Yii::app()->request->getParam('idtransfer');
      $this->menuid = 36;
	    $this->parentmenuid = 9; 
      $idgroup = FHelper::GetGroupId($this->userid_actor);
      
      if(FHelper::AllowMenu($this->menuid, $idgroup, 'edit'))
      {
        //$daftar_kurir = FHelper::GetDaftarKurir();
        $daftar_kurir = FHelper::GetKaryawanListData();
        
        $daftar_lokasi = FHelper::GetLocationListData();
        unset($daftar_lokasi[0]);
        
        //tampilkan form edit transfer order
        $form = new frmEditTransferOrder();
        
        //ambil record inv_transfer berdasarkan idtransfer
        $Criteria = new CDbCriteria();
        $Criteria->condition = 'id = :idtransfer';
        $Criteria->params = array(':idtransfer' => $idtransfer);
        $inv_transfer = inv_transfer::model()->find($Criteria);
        
        $form['tanggal_kirim'] = date('D, d M Y', strtotime($inv_transfer['tanggal_kirim']));
        $form['id'] = $inv_transfer['id'];
        $form['idlokasike'] = $inv_transfer['idlokasike'];
        $form['keterangan'] = $inv_transfer['keterangan'];
        $form['idstatus'] = $inv_transfer['idstatus']; //1 = sedang dibuat; 2 = disetujui; 3 = sedang dikirim; 4 = sudah diterima
        
        //ambil daftar summary
        $Criteria->condition = 'idtransfer = :idtransfer';
        $Criteria->params = array(':idtransfer' => $idtransfer);
        $daftar_summary = inv_transfer_summary::model()->findAll($Criteria);
        
        //ambil daftar detail
        $daftar_item = Yii::app()->db->createCommand()
          ->select('detail.*')
          ->from('inv_transfer_detail detail')
          ->join('inv_transfer_summary summary', 'detail.idsummary = summary.id')
          ->join('inv_transfer transfer', 'summary.idtransfer = transfer.id')
          ->where('transfer.id = :idtransfer', array(':idtransfer' => $idtransfer))
          ->queryAll();
        
        $html = $this->renderPartial(
          'vfrm_edittransferorder_type2',
          array(
            'form' => $form,
            'idstatus' => $form['idstatus'],
            'idtransfer' => $idtransfer,
            'userid_actor' => $this->userid_actor,
            'daftar_kurir' => $daftar_kurir,
            'daftar_lokasi' => $daftar_lokasi,
            'daftar_item' => $daftar_item,
            'daftar_summary' => $daftar_summary
          ),
          true
        );
        
        echo CJSON::encode(
          array(
            'valid' => 'ok',
            'html' => $html,
            'userid_actor' => $this->userid_actor,
          )
        );
      }
      else
      {
        //pelanggaran hak akses
        
        echo CJSON::encode(
          array(
            'valid' => 'not ok',
            'userid_actor' => $this->userid_actor,
          )
        );
      }
	  }
	  
	  /*
      actionTransferOrderType2RefreshList
      
      Deskripsi
      Action untuk menampilkan list detail dan summary suatu transfer order
      berdasarkan idtransfer.
      
      Parameter
      idtransfer
        Integer
        
      Return
      Render view list detail dan list summary.
    */
    public function actionTransferOrderType2RefreshList()
    {
      $idtransfer = Yii::app()->request->getParam('idtransfer');
      
      //ambil daftar detail
      $daftar_detail = Yii::app()->db->createCommand()
        ->select('detail.*')
        ->from('inv_transfer_detail detail')
        ->join('inv_transfer_summary summary', 'detail.idsummary = summary.id')
        ->join('inv_transfer transfer', 'summary.idtransfer = transfer.id')
        ->where('transfer.id = :idtransfer', array(':idtransfer' => $idtransfer))
        ->queryAll();
      
      //ambil daftar summary
      $Criteria = new CDbCriteria();
      $Criteria->condition = 'idtransfer = :idtransfer';
      $Criteria->params = array(':idtransfer' => $idtransfer);
      $daftar_summary = inv_transfer_summary::model()->findAll($Criteria);
      
      $Criteria->condition = 'id = :idtransfer';
      $Criteria->params = array(':idtransfer' => $idtransfer);
      $inv_transfer = inv_transfer::model()->find($Criteria);
      
      //render view list detail dan view list summary
      $view_summary = $this->renderPartial(
        'v_list_summary_transferorder_type2',
        array(
          'daftar_summary' => $daftar_summary
        ),
        true
      );
      
      $view_detail = $this->renderPartial(
        'v_list_item_transferorder_type2',
        array(
          'daftar_detail' => $daftar_detail,
          'idstatus' => $inv_transfer['idstatus'],
          'idtransfer' => $idtransfer
        ),
        true
      );
      
      echo CJSON::encode(
        array(
          'valid' => 'ok',
          'status' => 'ok',
          'daftar_detail' => $view_detail,
          'daftar_summary' => $view_summary,
          'jumlah_summary' => count($daftar_summary),
          'jumlah_detail' => count($daftar_detail)
        )
      );
    }
    
    /*
      actionTransferOrderType2TambahItem
      
      Deskripsi
      Action untuk menambahkan item ke transfer order
      
      Parameter
      barcode
        String
      idtransfer
        Integer
      mode
        String. 'tambah' atau 'hapus'.
        
      Return
      Render view list item yang sudah dibaca dan view summary-nya.
    */
    public function actionTransferOrderType2TambahItem()
    {
      $this->userid_actor = Yii::app()->request->cookies['userid_actor']->value;
      $idlokasi_cookie = Yii::app()->request->cookies['idlokasi']->value;
      $idtransfer = Yii::app()->request->getParam('idtransfer');
      $barcode = Yii::app()->request->getParam('barcode');
      $mode = Yii::app()->request->getParam('mode');
      $this->menuid = 36;
	    $this->parentmenuid = 9; 
      $idgroup = FHelper::GetGroupId($this->userid_actor);
      
      //periksa apakah barcode ada di inv_item dengan idlokasi yang cocok dengan 
      //idlokasi login
        $Criteria = new CDbCriteria();
        $Criteria->condition =
          'idlokasi = :idlokasi'.
          ' AND '.
          'barcode = :barcode';
        $Criteria->params = array(
          ':barcode' => $barcode,
          ':idlokasi' => $idlokasi_cookie
        );
        $count = inv_item::model()->count($Criteria);
        
        Yii::log('idlokasi = ' . $idlokasi_cookie, 'info');
        Yii::log('barcode = ' . $barcode, 'info');
        Yii::log('count = ' . $count, 'info');
      
      if($count  == 1)
      {
        //item ditemukan...
        $item = inv_item::model()->find($Criteria);
        $idlokasi_item = $item['idlokasi'];
        $idproduk = $item['idinventory'];
        $iditem = $item['id'];
        
        switch($mode)
        {
          case 'tambah' :
            
            //masukkan ke inv_transfer_detail - begin

              //pastikan statusnya = 1 (sedang digudang atau in (stok toko))
              if($item['idstatus'] == 1 || $item['idstatus'] == 3)
              {
                //periksa apakah produk sudah ada dalam inv_transfer_summary
                  $Criteria->condition = 'idproduk = :idproduk AND idtransfer = :idtransfer';
                  $Criteria->params = array(
                    ':idproduk' => $idproduk, 
                    ':idtransfer' => $idtransfer
                  );
                  $count = inv_transfer_summary::model()->count($Criteria);
                
                //pastikan barcode ini adalah barang yang diminta
                if($count == 0)
                {
                  //barcode tidak termasuk barang yang diminta
                  
                  //tolak barang
                  echo CJSON::encode(
                    array(
                      'status' => 'not ok',
                      'valid' => 'ok',
                      'pesan' => 'Item tidak dalam kategori yang diminta'
                    )
                  );
                }
                else
                {
                  //update record summary yang sudah ada
                  $Criteria->condition = 
                    'idproduk = :idproduk AND 
                    idtransfer = :idtransfer';
                  $Criteria->params = array(
                    ':idproduk' => $idproduk, 
                    ':idtransfer' => $idtransfer
                  );
                
                  $inv_transfer_summary = inv_transfer_summary::model()->find($Criteria);
                  //$inv_transfer_summary['jumlah'] = $inv_transfer_summary['jumlah'] + 1;
                  //$inv_transfer_summary->update();
                  $pk_summary = $inv_transfer_summary['id'];
                  
                  //masukkan item ke inv_transfer_detail - begin
                    $inv_transfer_detail = new inv_transfer_detail();
                    $inv_transfer_detail['idsummary'] = $pk_summary;
                    $inv_transfer_detail['iditem'] = $iditem;
                    
                    $inv_transfer_detail->save();
                  //masukkan item ke inv_transfer_detail - end
                  
                  
                  //update status item di tabel inv_item - begin
                    $Criteria = new CDbCriteria();
                    $Criteria->condition =
                      'idlokasi = :idlokasi'.
                      ' AND '.
                      'barcode = :barcode';
                    $Criteria->params = array(
                      ':barcode' => $barcode,
                      ':idlokasi' => $idlokasi_cookie
                    );
                    
                    $item = inv_item::model()->find($Criteria);
                    $item['idstatus'] = 2; //1 = gudang; 2 = out; 3 = in; 4 = customer
                    $item->save();
                  //update status item di tabel inv_item - end
                  
                  //update trans_history dan latest_status
                  FHelper::ItemStatusUpdate($iditem, 2, $idlokasi_cookie, $this->userid_actor, -1, $idtransfer);
                
                  //update inv_transfer - begin
                    $Criteria->condition = 'id = :idtransfer';
                    $Criteria->params = array(':idtransfer' => $idtransfer);
                    
                    $inv_transfer = inv_transfer::model()->find($Criteria);
                    $inv_transfer['idstatus'] = 201;
                    $inv_transfer['last_update'] = date('Y-m-d H:i:s');
                    $inv_transfer->save();
                  //update inv_transfer - end
                  
                  //refresh list
                    $this->actionTransferOrderType2RefreshList();
                }
              }
              else
              {
                //idstatus != 1 && idstatus != 3 (bukan di gudang, bukan di stok toko.
                
                echo CJSON::encode(
                  array(
                    'status' => 'not ok',
                    'valid' => 'ok',
                    'pesan' => 'Item sudah dibacakan'
                  )
                );
              }
            
            //masukkan ke inv_transfer_detail - end
            break;
          case 'hapus' :
            
            //pastikan item yang dibacakan ada dalam inv_transfer_detail
            
            if($item['idstatus'] == 2) //1 = gudang; 2 = out; 3 = in; 4 = customer
            {
              //update/hapus record dari inv_transfer_summary - begin
        
                //ambil idproduk berdasarkan iditem
                $CriteriaItem = new CDbCriteria();
                $CriteriaItem->condition = 'id = :iditem';
                $CriteriaItem->params = array(':iditem' => $iditem);
                $inv_item = inv_item::model()->find($CriteriaItem);
                $idproduk = $inv_item['idinventory'];
              
                $CriteriaSummary = new CDbCriteria();
                $CriteriaSummary->condition = 
                  'idproduk = :idproduk'. 
                  ' AND ' . 
                  'idtransfer = :idtransfer';
                $CriteriaSummary->params = array(
                  ':idproduk' => $idproduk, 
                  ':idtransfer' => $idtransfer
                );
                $count = inv_transfer_summary::model()->count($CriteriaSummary);
                
                if($count == 1)
                {
                  $inv_transfer_summary = inv_transfer_summary::model()->find($CriteriaSummary);
                  $pk_summary = $inv_transfer_summary['id'];
                  $inv_transfer_summary['jumlah'] = $inv_transfer_summary['jumlah'] - 1;
                  
                  if($inv_transfer_summary['jumlah'] <= 0)
                  {
                    $inv_transfer_summary->delete();
                  }
                  else
                  {
                    $inv_transfer_summary->save();
                  }
                }
              
              //update/hapus record dari inv_transfer_summary - end
              
              
              //hapus dari inv_transfer_detail - begin
                $CriteriaDetail = new CDbCriteria();
                $CriteriaDetail->condition = 'iditem = :iditem AND idsummary = :idsummary';
                $CriteriaDetail->params = array(
                  ':iditem' => $iditem,
                  ':idsummary' => $pk_summary
                );
                $inv_transfer_detail = inv_transfer_detail::model()->find($CriteriaDetail);
                $inv_transfer_detail->delete();
              
              //hapus dari inv_transfer_detail - end
              
              //update status item di inv_item - begin
                $Criteria = new CDbCriteria();
                $Criteria->condition =
                  'idlokasi = :idlokasi'.
                  ' AND '.
                  'barcode = :barcode';
                $Criteria->params = array(
                  ':barcode' => $barcode,
                  ':idlokasi' => $idlokasi_cookie
                );
                
                $item = inv_item::model()->find($Criteria);
                $item['idstatus'] = 3; //1 = gudang; 2 = out; 3 = in; 4 = customer
                $item->save();
              //update status item di inv_item - end
              
              //update inv_transfer - begin
                $Criteria->condition = 'id = :idtransfer';
                $Criteria->params = array(':idtransfer' => $idtransfer);
                
                $inv_transfer = inv_transfer::model()->find($Criteria);
                $inv_transfer['last_update'] = date('Y-m-d H:i:s');
                $inv_transfer->save();
              //update inv_transfer - end
              
              //render view list detail dan summary
                $this->actionTransferOrderType2RefreshList();
            }
            else
            {
              //status item bukan 2.
              
              echo CJSON::encode(
                array(
                  'status' => 'not ok',
                  'valid' => 'ok',
                  'pesan' => 'Item belum pernah dibacakan'
                )
              );
            }
            
            break;
        }//switch(mode)
      }
      else
      {
        //item tidak ditemukan dalam inv_item berdasarkan barcode dan idlokasi_cookie
        
        //menyusun kalimat pemberitahuan kepada user
        $Criteria = new CDbCriteria();
        $Criteria->condition =
          'barcode = :barcode';
        $Criteria->params = array(
          ':barcode' => $barcode
        );
        $item = inv_item::model()->find($Criteria);
                
        switch($item['idstatus'])
        {
          case 1: //gudang
            $status = 'ada di gudang';
            break;
          case 2: //out
            $status = 'sedang ditransfer';
            break;
          case 3: //in
            $status = 'ada di stok toko';
            break;
          case 4: //cuatomer
            $status = 'sudah dibeli customer';
            break;
          default: //cuatomer
            $status = 'tidak dikenal';
            break;
        }
        
        $Criteria = new CDbCriteria();
        $Criteria->condition = 'branch_id = :idlokasi';
        $Criteria->params = array(':idlokasi' => $item['idlokasi']);
        $mtr_branch = mtr_branch::model()->find($Criteria);
        
        if($mtr_branch != null)
        {
          $lokasi = $mtr_branch['name'];
        }
        else
        {
          $lokasi = ' tidak diketahui';
        }
        
        echo CJSON::encode(
          array(
            'status' => 'not ok',
            'valid' => 'ok',
            'pesan' => 'Item ' . $status . ', lokasi: ' . $lokasi
          )
        );
      }
    }
	  
    public function actionTransferOrderType2Approval()
	  {
	    $this->userid_actor = Yii::app()->request->cookies['userid_actor']->value;
	    $this->menuid = 36;
	    $this->parentmenuid = 9; 
	    $idgroup = FHelper::GetGroupId($this->userid_actor);
	    
	    if(FHelper::AllowMenu($this->menuid, $idgroup, 'edit'))
	    {
	      //do this
	      $form = new frmEditTransferOrder();
	      $form->attributes = Yii::app()->request->getParam('frmEditTransferOrder');
	      
	      $Criteria = new CDbCriteria();
	      $Criteria->condition = 'id = :idtransfer';
	      $Criteria->params = array(':idtransfer' => $form['id']);
	      
	      $inv_transfer = inv_transfer::model()->find($Criteria);
	      $inv_transfer['last_update'] = date('Y-m-d H:i:s');
	      $inv_transfer['idstatus'] = 202;
	      $inv_transfer->save();
	      
	      $this->actionTransferOrderType2Edit();
	    }
	    else
	    {
	      echo CJSON::enode(array('valid' => 'not ok'));
	    }
	  }
	  
	  public function actionTransferOrderType2Kirim()
	  {
	    $this->userid_actor = Yii::app()->request->cookies['userid_actor']->value;
	    $this->menuid = 36;
	    $this->parentmenuid = 9; 
	    $idgroup = FHelper::GetGroupId($this->userid_actor);
	    
	    if(FHelper::AllowMenu($this->menuid, $idgroup, 'edit'))
	    {
	      //do this
	      $form = new frmEditTransferOrder();
	      $form->attributes = YIi::app()->request->getParam('frmEditTransferOrder');
	      
	      $Criteria = new CDbCriteria();
	      $Criteria->condition = 'id = :idtransfer';
	      $Criteria->params = array(':idtransfer' => $form['id']);
	      
	      $inv_transfer = inv_transfer::model()->find($Criteria);
	      $inv_transfer['last_update'] = date('Y-m-d H:i:s');
	      $inv_transfer['idstatus'] = 203;
	      $inv_transfer->save();
	      
	      $this->actionTransferOrderType1Edit();
	    }
	    else
	    {
	      echo CJSON::enode(array('valid' => 'not ok'));
	    }
	  }
	  
	  public function actionTransferOrderType2_DownloadDaftarBarang()
	  {
	    $this->userid_actor = Yii::app()->request->cookies['userid_actor']->value;
	    $this->menuid = 36;
	    $this->parentmenuid = 9; 
	    $idgroup = FHelper::GetGroupId($this->userid_actor);
	    
	    if(FHelper::AllowMenu($this->menuid, $idgroup, 'read'))
	    {
	      //ambil idtransferorder
	      $idtransferorder = Yii::app()->request->getParam('idtransfer');
	      
	      //ambil info transfer order
	      $command = Yii::app()->db->createCommand()
	        ->select('*')
	        ->from('inv_transfer')
	        ->where('id = :idtransferorder', array(':idtransferorder' => $idtransferorder));
	      $transfer = $command->queryRow();
	      
	      //ambil daftar barang yang dikirim
	      $command = Yii::app()->db->createCommand()
	        ->select('inventory.nama, inventory.id, item.barcode')
	        ->from('inv_transfer_detail detil')
	        ->join('inv_transfer_summary summary', 'detil.idsummary = summary.id')
	        ->join('inv_transfer transfer', 'summary.idtransfer = transfer.id')
	        ->join('inv_item item', 'item.id = detil.iditem')
	        ->join('inv_inventory inventory', 'item.idinventory = inventory.id')
	        ->where('transfer.id = :idtransfer', array(':idtransfer' => $idtransferorder))
	        ->order('item.idinventory asc');
	      $daftar_barang = $command->queryAll();
	      
	      //bikin file excelnya
	      $nama_file = $this->TransferOrderType2_BikinDaftarBarang($transfer, $daftar_barang);
	      
	      //kembalikan url untuk ambil file excel
	      $html = CHtml::link('ambil file', $nama_file);
	      
	      echo CJSON::encode(array('valid' => 'ok', 'html' => $html));
	    }
	    else
	    {
	      echo CJSON::enode(array('valid' => 'not ok'));
	    }
	  }
	  
	  private function TransferOrderType2_BikinDaftarBarang($transfer, $daftar_barang)
	  {
	    //ambil info asal-tujuan
	    $dari = FHelper::GetLocationName($transfer['idlokasidari'], true);
	    $ke = FHelper::GetLocationName($transfer['idlokasike'], true);
	    
	    //ambil info tanggal bikin
	    $tanggal_bikin = date('d-M-Y', strtotime($transfer['tanggal_bikin']));
	    
	    //ambil info tanggal kirim
	    $tanggal_kirim = date('d-M-Y', strtotime($transfer['tanggal_kirim']));
	    
	    //siapkan object phpexcel
	    $xls = new PHPExcel();
	    $ws = $xls->getActiveSheet();
	    $ws->setTitle('Daftar Barang');
	    $kolom = 0;
	    $baris = 1;
	    
	    //tulis info transfer order
	    $ws->setCellValueByColumnAndRow($kolom, $baris, 'Dari'); $kolom++;
	    $ws->setCellValueByColumnAndRow($kolom, $baris, $dari);
	    
	    $baris++; $kolom = 0;
	    $ws->setCellValueByColumnAndRow($kolom, $baris, 'Ke'); $kolom++;
	    $ws->setCellValueByColumnAndRow($kolom, $baris, $ke);
	    
	    $baris++; $kolom = 0;
	    $ws->setCellValueByColumnAndRow($kolom, $baris, 'Dibuat'); $kolom++;
	    $ws->setCellValueByColumnAndRow($kolom, $baris, $tanggal_bikin); $kolom++;
	    
	    $baris++; $kolom = 0;
	    $ws->setCellValueByColumnAndRow($kolom, $baris, 'Dikirim'); $kolom++;
	    $ws->setCellValueByColumnAndRow($kolom, $baris, $tanggal_kirim); $kolom++;
	    
	    //tulis daftar barang
	    $baris++; $baris++; $kolom = 0;
	    
	    $nama_barang = "";
	    $idbarang = -1;
	    foreach($daftar_barang as $barang)
	    {
	      $temp_idbarang = $barang['id'];
	      
	      
	      if($idbarang != $temp_idbarang)
	      {
	        $baris++; $baris++; $kolom = 0;
	        $ws->setCellValueByColumnAndRow($kolom, $baris, "Jumlah : $jumlah_barang"); $baris++;
	        
	        $jumlah_barang = 0;
	        $idbarang = $temp_idbarang;
	        $baris++; $baris++; $kolom = 0;
	        $ukuran = FHelper::GetProdukUkuran($barang['id']);
	        
	        $ws->setCellValueByColumnAndRow($kolom, $baris, $barang['nama']); $baris++;
	        $ws->setCellValueByColumnAndRow($kolom, $baris, $ukuran); $baris++;
	        
	        $nama_barang = $barang['nama'];
	      }
	      
	      $ws->setCellValueExplicitByColumnAndRow($kolom, $baris, $barang['barcode']); $kolom++;
	      $jumlah_barang++;
	      
	      if( $kolom % 5 == 0 )
	      {
	        $kolom = 0;
	        $baris++;
	      }
	    }
	    
	    //kembalikan nama file
	    $file_name = "transfer_order_{$transfer['id']}_{$tanggal_bikin}.xlsx";
	    
	    $writer = new PHPExcel_Writer_Excel2007($xls);
	    $writer->save($file_name);
	    
	    return $file_name;
	  }
    
	  //----- transfer order type2 - end 
	  
	  
	  
	  
	  //----- transfer order type3 - begin
	  
	  /*
      actionTransferOrderType3Add
      
      Deskripsi
      Action untuk menampilkan form pembuatan transfer order type3. (saya minta
      dia kirim ke saya)
    */
    public function actionTransferOrderType3Add()
    {
      $this->userid_actor = Yii::app()->request->cookies['userid_actor']->value;
      $this->menuid = 36;
	    $this->parentmenuid = 9;
	    $this->idlokasi = Yii::app()->request->cookies['idlokasi']->value;
      $idgroup = FHelper::GetGroupId($this->userid_actor);
      
      if(FHelper::AllowMenu($this->menuid, $idgroup, 'read'))
      {
        //$daftar_kurir = FHelper::GetDaftarKurir();
        $daftar_kurir = FHelper::GetKaryawanListData();
        
        $daftar_lokasi = FHelper::GetLocationListData();
        unset($daftar_lokasi[0]);
        
        $do_add = Yii::app()->request->getParam('do_add');
        
        if(isset($do_add))
        {
          $form = new frmEditTransferOrder();
          $form->attributes = Yii::app()->request->getParam('frmEditTransferOrder');
          
          $validate_list[] = 'tanggal_kirim';
          
          if($form->validate($validate_list))
          {
            //masukkan data ke tabel
            $inv_transfer = new inv_transfer();
            $inv_transfer['iduserbikin'] = $this->userid_actor;
            $inv_transfer['tanggal_bikin'] = date('Y-m-j H:i:s');
            $inv_transfer['tanggal_kirim'] = date('Y-m-j', strtotime($form['tanggal_kirim']));
            $inv_transfer['idlokasike'] = $this->idlokasi;
            $inv_transfer['idlokasidari'] = $form['idlokasidari'];
            $inv_transfer['last_update'] = date('Y-m-j H:i:s');
            $inv_transfer['update_by'] = $this->userid_actor;
            $inv_transfer['tipe'] = 3; //saya minta dia kirim ke saya
            $inv_transfer['idstatus'] = 1; //1 = sedang dibuat; 2 = disetujui; 3 = sedang dikirim; 4 = sudah diterima
            
            $inv_transfer->save();
            $pk_transfer = $inv_transfer->getPrimaryKey();
            $form['id'] = $pk_transfer;
            
            //tampilkan form edit transfer order - begin
            
              //ambil record daftar itam yang sudah dibacakan untuk transfer order ini
              $Criteria = new CDbCriteria();
              $Criteria->condition = 'idtransfer = :idtransfer';
              $Criteria->params = array(':idtransfer' => $pk_transfer);
              
              $daftar_produsen[-1] = 'Pilih kategori dulu';
              $daftar_produk[-1] = 'Pilih produsen dulu';
              
              $html = $this->renderPartial(
                'vfrm_edittransferorder_type3',
                array(
                  'form' => $form,
                  'idstatus' => 1,
                  'idtransfer' => $pk_transfer,
                  'userid_actor' => $this->userid_actor,
                  'daftar_kurir' => $daftar_kurir,
                  'daftar_lokasi' => $daftar_lokasi,
                  'daftar_produsen' => $daftar_produsen,
                  'daftar_produk' => $daftar_produk
                ),
                true
              );
            
            //tampilkan form edit transfer order - end
            
            echo CJSON::encode(
              array(
                'valid' => 'ok',
                'html' => $html,
                'userid_actor' => $this->userid_actor,
              )
            );
          }
          else
          {
            //tampilkan form add transfer order (karena validation failed)
            
            $html = $this->renderPartial(
              'vfrm_addtransferorder_type3',
              array(
                'form' => $form,
                'userid_actor' => $this->userid_actor,
                'daftar_kurir' => $daftar_kurir,
                'daftar_lokasi' => $daftar_lokasi
              ),
              true
            );
            
            echo CJSON::encode(
              array(
                'valid' => 'ok',
                'html' => $html,
                'userid_actor' => $this->userid_actor,
              )
            );
          }
        }
        else
        {
          //tampilkan form pembuatan transfer order
          $form = new frmEditTransferOrder();
          
          $html = $this->renderPartial(
            'vfrm_addtransferorder_type3',
            array(
              'form' => $form,
              'userid_actor' => $this->userid_actor,
              'daftar_kurir' => $daftar_kurir,
              'daftar_lokasi' => $daftar_lokasi
            ),
            true
          );
          
          echo CJSON::encode(
            array(
              'valid' => 'ok',
              'html' => $html,
              'userid_actor' => $this->userid_actor,
            )
          );
        }
      }
      else
      {
        echo CJSON::encode(
          array(
            'valid' => 'not ok',
            'userid_actor' => $this->userid_actor,
          )
        );
      }
    }
    
    public function actionTransferOrderType3Approval()
	  {
	    $this->userid_actor = Yii::app()->request->cookies['userid_actor']->value;
	    $this->menuid = 36;
	    $this->parentmenuid = 9; 
	    $idgroup = FHelper::GetGroupId($this->userid_actor);
	    
	    if(FHelper::AllowMenu($this->menuid, $idgroup, 'edit'))
	    {
	      //do this
	      $form = new frmEditTransferOrder();
	      $form->attributes = Yii::app()->request->getParam('frmEditTransferOrder');
	      
	      $Criteria = new CDbCriteria();
	      $Criteria->condition = 'id = :idtransfer';
	      $Criteria->params = array(':idtransfer' => $form['id']);
	      
	      $inv_transfer = inv_transfer::model()->find($Criteria);
	      $inv_transfer['last_update'] = date('Y-m-d H:i:s');
	      $inv_transfer['idstatus'] = 2;
	      $inv_transfer->save();
	      
	      $this->actionTransferOrderType3Edit();
	    }
	    else
	    {
	      echo CJSON::enode(array('valid' => 'not ok'));
	    }
	  }
    
    /*
      actionTransferOrderType3Edit
      
      Deskripsi
      Action untuk menampilkan form edit transfer order type3. Tidak perlu 
      ada proses validasi form transfer order, karena transfer order type3
      hanya menambah atau mengurangi daftar barang. Penambahan atau pengurangan
      barang dilakukan lewat action yang lain.
    */
    public function actionTransferOrderType3Edit()
    {
      $this->userid_actor = Yii::app()->request->cookies['userid_actor']->value;
      $idtransfer = Yii::app()->request->getParam('idtransfer');
      $this->menuid = 36;
	    $this->parentmenuid = 9; 
      $idgroup = FHelper::GetGroupId($this->userid_actor);
      
      if(FHelper::AllowMenu($this->menuid, $idgroup, 'edit'))
      {
        //$daftar_kurir = FHelper::GetDaftarKurir();
        $daftar_kurir = FHelper::GetKaryawanListData();
        
        $daftar_lokasi = FHelper::GetLocationListData();
        unset($daftar_lokasi[0]);
        
        //tampilkan form edit transfer order
        $form = new frmEditTransferOrder();
        
        //ambil record inv_transfer berdasarkan idtransfer
        $Criteria = new CDbCriteria();
        $Criteria->condition = 'id = :idtransfer';
        $Criteria->params = array(':idtransfer' => $idtransfer);
        
        $inv_transfer = inv_transfer::model()->find($Criteria);
        $form['tanggal_kirim'] = date('D, d M Y', strtotime($inv_transfer['tanggal_kirim']));
        $form['id'] = $inv_transfer['id'];
        $form['keterangan'] = $inv_transfer['keterangan'];
        $form['idlokasike'] = $inv_transfer['idlokasike'];
        $form['idlokasidari'] = $inv_transfer['idlokasidari'];
        $form['idstatus'] = $inv_transfer['idstatus']; 
        //1 = sedang dibuat; 202 = disetujui; 203 = sedang dikirim; 204 = permintaan sudah diterima
        
        //menentukan viewname berdasarkan idstatus transfer order type3
        switch($inv_transfer['idstatus'])
        {
          case 203: //sedang dikirim
            $viewname = 'vfrm_edittransferorder_type3_203';
            break;
          case 204: //permintaan sudah diterima
            $viewname = 'vfrm_edittransferorder_type3_204';
            break;
          default:
            $viewname = 'vfrm_edittransferorder_type3';
            break;
        }
        
        //ambil daftar summary
        $Criteria->condition = 'idtransfer = :idtransfer';
        $Criteria->params = array(':idtransfer' => $idtransfer);
        $daftar_summary = inv_transfer_summary::model()->findAll($Criteria);
        
        //ambil daftar detail
        $daftar_item = Yii::app()->db->createCommand()
          ->select('detail.*')
          ->from('inv_transfer_detail detail')
          ->join('inv_transfer_summary summary', 'detail.idsummary = summary.id')
          ->join('inv_transfer transfer', 'summary.idtransfer = transfer.id')
          ->where('transfer.id = :idtransfer', array(':idtransfer' => $idtransfer))
          ->queryAll();
        
        $daftar_produsen[-1] = 'Pilih kategori dulu';
        $daftar_produk[-1] = 'Pilih produsen dan ketik nama produk';
        
        $html = $this->renderPartial(
          $viewname,
          array(
            'form' => $form,
            'idstatus' => $form['idstatus'],
            'idtransfer' => $idtransfer,
            'userid_actor' => $this->userid_actor,
            'daftar_kurir' => $daftar_kurir,
            'daftar_lokasi' => $daftar_lokasi,
            'daftar_item' => $daftar_item,
            'daftar_summary' => $daftar_summary,
            'daftar_produsen' => $daftar_produsen,
            'daftar_produk' => $daftar_produk
          ),
          true
        );
        
        echo CJSON::encode(
          array(
            'valid' => 'ok',
            'html' => $html,
            'userid_actor' => $this->userid_actor,
          )
        );
      }
      else
      {
        //pelanggaran hak akses
        
        echo CJSON::encode(
          array(
            'valid' => 'not ok',
            'userid_actor' => $this->userid_actor,
          )
        );
      }
    }
    
    public function actionTransferOrderType3Kirim()
	  {
	    $this->userid_actor = Yii::app()->request->cookies['userid_actor']->value;
	    $this->menuid = 36;
	    $this->parentmenuid = 9; 
	    $idgroup = FHelper::GetGroupId($this->userid_actor);
	    
	    if(FHelper::AllowMenu($this->menuid, $idgroup, 'edit'))
	    {
	      //do this
	      $form = new frmEditTransferOrder();
	      $form->attributes = YIi::app()->request->getParam('frmEditTransferOrder');
	      
	      $Criteria = new CDbCriteria();
	      $Criteria->condition = 'id = :idtransfer';
	      $Criteria->params = array(':idtransfer' => $form['id']);
	      
	      $inv_transfer = inv_transfer::model()->find($Criteria);
	      $inv_transfer['last_update'] = date('Y-m-d H:i:s');
	      $inv_transfer['idstatus'] = 200;
	      $inv_transfer->save();
	      
	      $this->actionTransferOrderType3Edit();
	    }
	    else
	    {
	      echo CJSON::enode(array('valid' => 'not ok'));
	    }
	  }
    
	  /*
      actionTransferOrderType3RefreshList
      
      Deskripsi
      Action untuk menampilkan list summary suatu transfer order
      berdasarkan idtransfer.
      
      Parameter
      idtransfer
        Integer
        
      Return
      Render view list detail dan list summary.
    */
    public function actionTransferOrderType3RefreshList()
    {
      $idtransfer = Yii::app()->request->getParam('idtransfer');
      
      $Criteria = new CDbCriteria();
      $Criteria->condition = 'idtransfer = :idtransfer';
      $Criteria->params = array(':idtransfer' => $idtransfer);
      $daftar_summary = inv_transfer_summary::model()->findAll($Criteria);
      
      //render view list detail dan view list summary
      $view_summary = $this->renderPartial(
        'v_list_summary_transferorder_type3',
        array(
          'daftar_summary' => $daftar_summary
        ),
        true
      );
      
      echo CJSON::encode(
        array(
          'valid' => 'ok',
          'status' => 'ok',
          'daftar_summary' => $view_summary,
          'jumlah_summary' => count($daftar_summary)
        )
      );
    }
    
    /*
      actionTransferOrderType3203RefreshList
      
      Deskripsi
      Action untuk menampilkan list summary suatu transfer order
      berdasarkan idtransfer.
      
      Parameter
      idtransfer
        Integer
        
      Return
      Render view list detail dan list summary.
    */
    public function actionTransferOrderType3203RefreshList()
    {
      $idtransfer = Yii::app()->request->getParam('idtransfer');
      
      //ambil daftar detail
      $daftar_detail = Yii::app()->db->createCommand()
        ->select('detail.*')
        ->from('inv_transfer_detail detail')
        ->join('inv_transfer_summary summary', 'detail.idsummary = summary.id')
        ->join('inv_transfer transfer', 'summary.idtransfer = transfer.id')
        ->where('transfer.id = :idtransfer', array(':idtransfer' => $idtransfer))
        ->queryAll();
        
      $Criteria = new CDbCriteria();
      $Criteria->condition = 'idtransfer = :idtransfer';
      $Criteria->params = array(':idtransfer' => $idtransfer);
      $daftar_summary = inv_transfer_summary::model()->findAll($Criteria);
      
      //render view list detail dan view list summary
      $view_summary = $this->renderPartial(
        'v_list_summary_transferorder_type3_203',
        array(
          'daftar_summary' => $daftar_summary
        ),
        true
      );
      
      $view_detail = $this->renderPartial(
        'v_list_item_transferorder_type3_203',
        array(
          'daftar_detail' => $daftar_detail,
          'idstatus' => $inv_transfer['idstatus'],
          'idtransfer' => $idtransfer
        ),
        true
      );
      
      echo CJSON::encode(
        array(
          'valid' => 'ok',
          'status' => 'ok',
          'daftar_detail' => $view_detail,
          'daftar_summary' => $view_summary,
          'jumlah_summary' => count($daftar_summary),
          'jumlah_detail' => count($daftar_detail)
        )
      );
    }
    
    /*
      actionTransferOrderType3TambahItem
      
      Deskripsi
      Action untuk menambahkan item ke transfer order type 3 (minta dikirim barang)
      
      Parameter
      idproduk
        integer
      idtransfer
        Integer
      jumlah
        Integer
      mode
        String. 'tambah' atau 'hapus'.
        
      Return
      Render view summary item yang sudah dibaca.
    */
    public function actionTransferOrderType3TambahItem()
    {
      $this->userid_actor = Yii::app()->request->cookies['userid_actor']->value;
      $idlokasi_cookie = Yii::app()->request->cookies['idlokasi']->value;
      $idtransfer = Yii::app()->request->getParam('idtransfer');
      $idproduk = Yii::app()->request->getParam('idproduk');
      $mode = Yii::app()->request->getParam('mode');
      $jumlah = Yii::app()->request->getParam('jumlah');
      $this->menuid = 36;
	    $this->parentmenuid = 9; 
      $idgroup = FHelper::GetGroupId($this->userid_actor);
      
      switch($mode)
      {
        case 'tambah' :
          
          //update record di inv_transfer_summary - begin
            
            //periksa apakah produk sudah ada dalam inv_transfer_summary
              $Criteria = new CDbCriteria();
              $Criteria->condition = 
                'idproduk = :idproduk AND idtransfer = :idtransfer';
              $Criteria->params = array(
                ':idproduk' => $idproduk, 
                ':idtransfer' => $idtransfer
              );
              $count = inv_transfer_summary::model()->count($Criteria);
            
            if($count == 0)
            {
              //tambahkan record summary yang baru
              $inv_transfer_summary = new inv_transfer_summary();
              $inv_transfer_summary['idtransfer'] = $idtransfer;
              $inv_transfer_summary['idproduk'] = $idproduk;
              $inv_transfer_summary['jumlah'] = $jumlah;
              
              $inv_transfer_summary->save();
              $pk_summary = $inv_transfer_summary->getPrimaryKey();
            }
            else
            {
              //update record summary yang sudah ada
              $Criteria->condition = 'idproduk = :idproduk AND idtransfer = :idtransfer';
              $Criteria->params = array(
                ':idproduk' => $idproduk, 
                ':idtransfer' => $idtransfer
              );
            
              $inv_transfer_summary = inv_transfer_summary::model()->find($Criteria);
              $inv_transfer_summary['jumlah'] = $inv_transfer_summary['jumlah'] + $jumlah;
              
              $inv_transfer_summary->update();
              $pk_summary = $inv_transfer_summary['id'];
            }
          
          //update record di inv_transfer_summary - end
          
          //update inv_transfer - begin
            $Criteria->condition = 'id = :idtransfer';
            $Criteria->params = array(':idtransfer' => $idtransfer);
            
            $inv_transfer = inv_transfer::model()->find($Criteria);
            $inv_transfer['last_update'] = date('Y-m-d H:i:s');
            $inv_transfer->save();
          //update inv_transfer - end
          break;
        case 'hapus' :
          
          //ambil idproduk berdasarkan iditem
            $CriteriaSummary = new CDbCriteria();
            $CriteriaSummary->condition = 
              'idproduk = :idproduk'. 
              ' AND ' . 
              'idtransfer = :idtransfer';
            $CriteriaSummary->params = array(
              ':idproduk' => $idproduk, 
              ':idtransfer' => $idtransfer
            );
            $count = inv_transfer_summary::model()->count($CriteriaSummary);
            
            if($count > 0)
            {
              $inv_transfer_summary = inv_transfer_summary::model()->find($CriteriaSummary);
              $pk_summary = $inv_transfer_summary['id'];
              $inv_transfer_summary['jumlah'] = $inv_transfer_summary['jumlah'] - $jumlah;
              
              if($inv_transfer_summary['jumlah'] <= 0)
              {
                $inv_transfer_summary->delete();
              }
              else
              {
                $inv_transfer_summary->save();
              }
            }
          
          //update/hapus record dari inv_transfer_summary - end
          
          //update inv_transfer - begin
            $Criteria->condition = 'id = :idtransfer';
            $Criteria->params = array(':idtransfer' => $idtransfer);
            
            $inv_transfer = inv_transfer::model()->find($Criteria);
            $inv_transfer['last_update'] = date('Y-m-d H:i:s');
            $inv_transfer->save();
          //update inv_transfer - end
          break;
      }//switch(mode)
      
      $this->actionTransferOrderType3RefreshList();
    }
    
    /*
      actionTransferOrderType3203TambahItem
      
      Deskripsi
      Action untuk menambahkan item ke transfer order type 3 (minta dikirim barang)
      yang sudah berstatus 203 (penerimaan barang)
      
      Parameter
      idproduk
        integer
      idtransfer
        Integer
      mode
        String. 'tambah' atau 'hapus'.
        
      Return
      Render view summary item yang sudah dibaca.
    */
    public function actionTransferOrderType3203TambahItem()
    {
      $this->userid_actor = Yii::app()->request->cookies['userid_actor']->value;
      $idlokasi_cookie = Yii::app()->request->cookies['idlokasi']->value;
      $idtransfer = Yii::app()->request->getParam('idtransfer');
      $barcode = Yii::app()->request->getParam('barcode');
      $mode = Yii::app()->request->getParam('mode');
      $this->menuid = 36;
	    $this->parentmenuid = 9; 
      $idgroup = FHelper::GetGroupId($this->userid_actor);
      
      //periksa apakah barcode ada di inv_item
        $Criteria = new CDbCriteria();
        $Criteria->condition = 'barcode = :barcode';
        $Criteria->params = array(':barcode' => $barcode);
        
        $item = inv_item::model()->find($Criteria);
        
        //ambil data item dari tabel inv_item
        if($item != null)
        {
          //memastikan barcode yang dibacakan ada dalam daftar inv_transfer_detail
          $iditem = $item['id'];
          $idlokasi_item = $item['idlokasi'];
          $idproduk = $item['idinventory'];
          
          $detail = Yii::app()->db->createCommand()
            ->select('detail.*')
            ->from('inv_transfer_detail detail')
            ->join('inv_transfer_summary summary', 'summary.id = detail.idsummary')
            ->join('inv_transfer transfer', 'transfer.id = summary.idtransfer')
            ->where(
              'transfer.id = :idtransfer' .
              ' AND '.
              'detail.iditem = :iditem', 
              array(
                  ':idtransfer' => $idtransfer,
                  ':iditem' => $iditem
                )
              )
            ->queryAll();
        
          $detail_count = count($detail);
        
          if($detail_count  == 1)
          {
            //item ditemukan dalam daftar transfer...
            
            switch($mode)
            {
              case 'tambah' :
                
                //jika record pada inv_transfer_detail masih berstatus 'dikirim'
                if($detail[0]['idstatus'] == 1) //idstatus transfer: 1 = dikirim; 2 = diterima; 3 = tambahan
                {
                  $idsummary = $detail[0]['idsummary'];
                  
                  //tandai record pada inv_transfer_detail sebagai 'diterima'
                    $Criteria->condition = 'idsummary = :idsummary AND iditem = :iditem';
                    $Criteria->params = array(
                      ':idsummary' => $idsummary,
                      ':iditem' => $iditem
                    );
                    
                    $inv_transfer_detail = inv_transfer_detail::model()->find($Criteria);
                    $inv_transfer_detail['idstatus'] = 2; //diterima
                    $inv_transfer_detail->save();
                  
                  //lalu update jumlah penerimaan pada tabel inv_transfer_summary
                    $Criteria->condition = 'id = :idsummary';
                    $Criteria->params = array(
                      ':idsummary' => $idsummary
                    );
                    
                    $inv_transfer_summary = inv_transfer_summary::model()->find($Criteria);
                    $inv_transfer_summary['jumlah_terima'] = $inv_transfer_summary['jumlah_terima'] + 1;
                    $inv_transfer_summary->save();
                  
                  //update inv_item, trans_history dan latest_status
                  FHelper::ItemStatusUpdate($iditem, 3, $idlokasi_cookie, $this->userid_actor, -1, $idtransfer);
                  
                  $this->actionTransferOrderType3203RefreshList();
                }
                else
                {
                  //record sudah ditandai sebagai 'diterima'
                  
                  echo CJSON::encode(
                    array(
                      'valid' => 'ok',
                      'status' => 'not ok',
                      'pesan' => 'Item sudah dibacakan'
                    )
                  );
                }
                break;
              case 'hapus' :
                
                //jika status di inv_transfer_detail 'diterima'
                
                switch($detail[0]['idstatus'])
                {
                  case 2 : //barang sudah diterima
                    
                    //kembalikan idstatus menjadi 'dikirim'
                    
                    $idsummary = $detail[0]['idsummary'];
                  
                    //update status di inv_transfer_detail menjadi 'dikirim'
                      $Criteria->condition = 'idsummary = :idsummary AND iditem = :iditem';
                      $Criteria->params = array(
                        ':idsummary' => $idsummary,
                        ':iditem' => $iditem
                      );
                      
                      $inv_transfer_detail = inv_transfer_detail::model()->find($Criteria);
                      $inv_transfer_detail['idstatus'] = 1;
                      $inv_transfer_detail->save();
                    
                    //update trans_history dan latest_status
                    FHelper::ItemStatusUpdate($iditem, 2, $idlokasi_cookie, $this->userid_actor);
                      
                    //update jumlah_diterima pada inv_transfer_summary
                      $Criteria->condition = 'id = :idsummary';
                      $Criteria->params = array(
                        ':idsummary' => $idsummary
                      );
                      
                      $inv_transfer_summary = inv_transfer_summary::model()->find($Criteria);
                      $inv_transfer_summary['jumlah_terima'] = $inv_transfer_summary['jumlah_terima'] - 1;
                      $inv_transfer_summary->save();
                      
                    $this->actionTransferOrderType3203RefreshList();
                    break;
                  default:
                    //item sudah berstatus 'dikirim'
                  
                    echo CJSON::encode(
                      array(
                        'valid' => 'ok',
                        'status' => 'not ok',
                        'pesan' => 'Item sudah dihapus'
                      )
                    );
                    break;
                } //$detail[0]['idstatus']
                
                break;
            }//switch(mode)
          }
          else
          {
            //item tidak ditemukan dalam inv_transfer_detail
            
            //tolak penerimaan barang
            
            echo CJSON::encode(
              array(
                'valid' => 'ok',
                'status' => 'not ok',
                'pesan' => 'Item tidak valid'
              )
            );
          }
        }
        else
        {
          //barcode tidak valid
          
          echo CJSON::encode(
            array(
              'valid' => 'ok',
              'status' => 'not ok',
              'pesan' => 'Item tidak ada dalam database'
            )
          );
        }
    }
    
    
    /*
      actionTransferOrderType3PilihKategori
      
      Deskripsi
      Action untuk menampilkan daftar produk akibat pemilihan produsen dan 
      kategori pada form transfer order type3.
      
      Parameter
      idprodusen, idkategori
      
      Return
      Render dropdownlist produk dan dropdownlist produk.
    */
    public function actionTransferOrderType3PilihKategori()
    {
      $idkategori = Yii::app()->request->getParam('idkategori');
      
      if($idkategori != 'pilih')
      {
        //ambil daftar supplier.
        //kembalikan daftar produk dengan isi 'pilih supplier...'
        
        $idkategori = FHelper::GetTipeProdukId($idkategori);
        
        $daftar_produsen = Yii::app()->db->createCommand()
          ->select('supplier.supplier_id, supplier.name')
          ->from('mtr_supplier supplier')
          ->join('inv_inventory inventory', 'supplier.supplier_id = inventory.idsupplier')
          ->where(
              'inventory.is_del = 0 AND 
               inventory.is_deact = 0 AND 
               inventory.idkategori = :idkategori',
              array(':idkategori' => $idkategori)
            )
          ->group('supplier.supplier_id, supplier.name')
          ->queryAll();
        
        $listdataProdusen = CHtml::listData(
          $daftar_produsen,
          'supplier_id',
          'name'
        );
        $temp[-1] = 'Pilih supplier...';
        $listdataProdusen = $temp + $listdataProdusen;
        
        $dropdownlistProdusen = CHtml::dropdownList(
          'cbProdusen',
          '-1',
          $listdataProdusen,
          array(
            'class' => 'fl-space2',
            'id' => 'cbProdusen',
            'onchange' => 'TransferOrderType3_PilihProdusen();'
          )
        );
        
        $listdataProduk[-1] = 'Pilih supplier dulu...';
        $dropdownlistProduk = CHtml::dropdownList(
          'cbProduk',
          '-1',
          $listdataProduk,
          array(
            'class' => 'fl-space2',
            'id' => 'cbProduk',
            'onchange' => 'TransferOrderType3_PilihProduk();'
          )
        );
      }
      else
      {
        //user memilih kategori = pilih
        
        //kembalikan daftar supplier hanya berisi 'pilih kategori...'
        $listdataProdusen[-1] = 'Pilih kategori dulu...';
        $dropdownlistProdusen = CHtml::dropdownList(
          'cbProdusen',
          '-1',
          $listdataProdusen,
          array(
            'class' => 'fl-space2',
            'id' => 'cbProdusen',
            'onchange' => 'TransferOrderType3_PilihProdusen();'
          )
        );
        
        //kembalikan daftar produk dengan isi 'pilih supplier...'
        $listdataProduk[-1] = 'Pilih supplier dulu...';
        $dropdownlistProduk = CHtml::dropdownList(
          'cbProduk',
          '-1',
          $listdataProduk,
          array(
            'class' => 'fl-space2',
            'id' => 'cbProduk',
            'onchange' => 'TransferOrderType3_PilihProduk();'
          )
        );
      }
      
      echo CJSON::encode(
        array(
          'dropdownlistProdusen' => $dropdownlistProdusen,
          'dropdownlistProduk' => $dropdownlistProduk
        )
      );
    }
    
    /*
      actionTransferOrderType3PilihProdusen
      
      Deskripsi
      Action untuk menampilkan daftar produk akibat pemilihan produsen dan 
      kategori pada form transfer order type3.
      
      Parameter
      idprodusen, idkategori
      
      Return
      Render dropdownlist produk dan dropdownlist produk.
    */
    public function actionTransferOrderType3PilihProdusen()
    {
      $idkategori = Yii::app()->request->getParam('idkategori');
      $idprodusen = Yii::app()->request->getParam('idprodusen');
      
      if($idkategori != 'pilih' && $idprodusen != -1)
      {
        //ambil daftar produk berdasarkan idkategori dan idprodusen.
        //kembalikan daftar produk.
        
        $idkategori = FHelper::GetTipeProdukId($idkategori);
        
        $Criteria = new CDbCriteria();
        $Criteria->condition = 
          'is_del = 0 AND 
           is_deact = 0 AND 
           idkategori = :idkategori AND
           idsupplier = :idsupplier';
        $Criteria->params = array(
          ':idkategori' => $idkategori,
          ':idsupplier' => $idprodusen
        );
        
        $daftar_produk = inv_inventory::model()->findAll($Criteria);
        
        foreach($daftar_produk as $produk)
        {
          $id = $produk['id'];
          
          switch($idkategori)
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
          } //switch($idkategori)
          
          $tipe = $produk['nama'] . ' : ' . $spesifik['nama_tipe'];
          $listdataProduk[$id] = $tipe;
        }
          
        /*
        $listdataProduk = CHtml::listData(
          $daftar_produk,
          'id',
          'nama'
        );
        */
        
        $temp[-1] = 'Pilih produk...';
        $listdataProduk = $temp + $listdataProduk;
        
        $dropdownlistProduk = CHtml::dropdownList(
          'cbProduk',
          '-1',
          $listdataProduk,
          array(
            'class' => 'fl-space2',
            'id' => 'cbProduk',
            'onchange' => 'TransferOrderType3_PilihProduk();'
          )
        );
      }
      else
      {
        //user memilih produsen = pilih
        
        //kembalikan daftar produk hanya berisi 'pilih supplier...'
        $listdataProduk[-1] = 'Pilih supplier dulu...';
        $dropdownlistProduk = CHtml::dropdownList(
          'cbProduk',
          '-1',
          $listdataProduk,
          array(
            'class' => 'fl-space2',
            'id' => 'cbProduk',
            'onchange' => 'TransferOrderType3_PilihProduk();'
          )
        );
      }
      
      echo CJSON::encode(
        array(
          'dropdownlistProduk' => $dropdownlistProduk
        )
      );
    }
    
    /*
      actionTransferOrderType3CariProduk
      
      Deskripsi
      Action untuk menampilkan daftar produk berdasarkan nama produk yang diketik
      user pada form transfer order type3.
      
      Parameter
      idprodusen, idkategori
      
      Return
      Render dropdownlist produk dan dropdownlist produk.
    */
    public function actionTransferOrderType3CariProduk()
    {
      $idkategori = Yii::app()->request->getParam('idkategori');
      $idprodusen = Yii::app()->request->getParam('idprodusen');
      $namaproduk = Yii::app()->request->getParam('namaproduk');
      
      if($idkategori != 'pilih' && $idprodusen != -1)
      {
        //ambil daftar produk berdasarkan idkategori dan idprodusen.
        //kembalikan daftar produk.
        
        $idkategori = FHelper::GetTipeProdukId($idkategori);
        
        $Criteria = new CDbCriteria();
        $Criteria->condition = 
          'is_del = 0 AND 
           is_deact = 0 AND 
           idkategori = :idkategori AND
           idsupplier = :idsupplier AND
           nama like :namaproduk';
        $Criteria->params = array(
          ':idkategori' => $idkategori,
          ':idsupplier' => $idprodusen,
          ':namaproduk' => '%' . $namaproduk . '%'
        );
        $Criteria->order = 'nama asc';
        
        $daftar_produk = inv_inventory::model()->findAll($Criteria);
        $listdataProduk = array();
        
        foreach($daftar_produk as $produk)
        {
          $id = $produk['id'];
          
          switch($idkategori)
          {
            case 1: //lensa
              $spesifik = $produk->lensa;
              $info = 
                $produk['brand'] .
                ' | mat.:' . $spesifik['material'] .
                ' | sph:' . $spesifik['sph_min'] . 
                ' | cyl:' . $spesifik['cyl_min'] . 
                ' | add:' . $spesifik['add_1'];
              break;
            case 2: //frame
              $spesifik = $produk->frame;
              $info = 
                $produk['brand'] .
                ' | tipe:' . $spesifik['nama_tipe'] .
                ' | mat.:' . $spesifik['material'] .
                ' | warna:' . $spesifik['color'] .
                ' | dbl:' . $spesifik['dbl'] . 
                ' | eye size:' . $spesifik['eye_size'] . 
                ' | temple:' . $spesifik['temple'];
              break;
            case 3: //softlens
              $spesifik = $produk->softlens;
              $info = 
                $produk['brand'] .
                ' | mat.:' . $spesifik['material'] .
                ' | sph:' . $spesifik['sph_min'] . 
                ' | cyl:' . $spesifik['cyl_min'] . 
                ' | add:' . $spesifik['add_1'];
              break;
            case 4: //solution
              $spesifik = $produk->solution;
              $info = 
                $produk['brand'] .
                ' | tipe:' . $spesifik['nama_tipe'];
              break;
            case 5: //accessories
              $spesifik = $produk->accessories;
              $info = 
                $produk['brand'] .
                ' | tipe:' . $spesifik['nama_tipe'];
              break;
            case 6: //services
              $spesifik = $produk->services;
              $info = 
                $produk['brand'] .
                ' | tipe:' . $spesifik['nama_tipe'];
              break;
            case 7: //other
              $spesifik = $produk->other;
              $info = 
                $produk['brand'] .
                ' | tipe:' . $spesifik['nama_tipe'];
              break;
            case 8: //supplies
              $spesifik = $produk->supplies;
              $info = 
                $produk['brand'] .
                ' | tipe:' . $spesifik['nama_tipe'];
              break;
          } //switch($idkategori)
          
          $tipe = $produk['nama'] . ' | ' . $info;
          $listdataProduk[$id] = $tipe;
        }
          
        /*
        $listdataProduk = CHtml::listData(
          $daftar_produk,
          'id',
          'nama'
        );
        */
        
        if(count($listdataProduk) > 0)
        {
          $temp[-1] = 'Pilih produk...';
          $listdataProduk = $temp + $listdataProduk;
        }
        else
        {
          $temp[-1] = 'Produk tidak ditemukan';
          $listdataProduk = $temp + $listdataProduk;
        }
        
        
        $dropdownlistProduk = CHtml::dropdownList(
          'cbProduk',
          '-1',
          $listdataProduk,
          array(
            'class' => 'fl-space2',
            'id' => 'cbProduk',
            'onchange' => 'TransferOrderType3_PilihProduk();'
          )
        );
      }
      else
      {
        //user memilih produsen = pilih
        
        //kembalikan daftar produk hanya berisi 'pilih supplier...'
        $listdataProduk[-1] = 'Pilih supplier dulu...';
        $dropdownlistProduk = CHtml::dropdownList(
          'cbProduk',
          '-1',
          $listdataProduk,
          array(
            'class' => 'fl-space2',
            'id' => 'cbProduk',
            'onchange' => 'TransferOrderType3_PilihProduk();'
          )
        );
      }
      
      echo CJSON::encode(
        array(
          'dropdownlistProduk' => $dropdownlistProduk
        )
      );
    }
    
    /*
      actionTransferOrderType3Terima
      
      Deskripsi
      Action untuk menampilkan interface penerimaan barang transfer order type3. 
      Sudut pandang si penerima.
      
      Parameter
      idtransfer
      
      Return
      Render view transfer order type3.
    */	  
	  public function actionTransferOrderType3Terima()
	  {
	    $this->userid_actor = Yii::app()->request->cookies['userid_actor']->value;
      $idtransfer = Yii::app()->request->getParam('idtransfer');
      $this->menuid = 36;
	    $this->parentmenuid = 9; 
      $idgroup = FHelper::GetGroupId($this->userid_actor);
      
      if(FHelper::AllowMenu($this->menuid, $idgroup, 'edit'))
      {
        //$daftar_kurir = FHelper::GetDaftarKurir();
        $daftar_kurir = FHelper::GetKaryawanListData();
        
        $daftar_lokasi = FHelper::GetLocationListData();
        unset($daftar_lokasi[0]);
        
        //tampilkan form edit transfer order
        $form = new frmEditTransferOrder();
        
        //ambil record inv_transfer berdasarkan idtransfer
        $Criteria = new CDbCriteria();
        $Criteria->condition = 'id = :idtransfer';
        $Criteria->params = array(':idtransfer' => $idtransfer);
        
        $inv_transfer = inv_transfer::model()->find($Criteria);
        $form['tanggal_kirim'] = date('D, d M Y', strtotime($inv_transfer['tanggal_kirim']));
        $form['id'] = $inv_transfer['id'];
        $form['idlokasike'] = $inv_transfer['idlokasike'];
        $form['idstatus'] = $inv_transfer['idstatus']; //1 = sedang dibuat; 2 = disetujui; 3 = sedang dikirim; 4 = sudah diterima
        
        //ambil daftar summary
        $Criteria->condition = 'idtransfer = :idtransfer';
        $Criteria->params = array(':idtransfer' => $idtransfer);
        $daftar_summary = inv_transfer_summary::model()->findAll($Criteria);
        
        //ambil daftar detail
        $daftar_item = Yii::app()->db->createCommand()
          ->select('detail.*')
          ->from('inv_transfer_detail detail')
          ->join('inv_transfer_summary summary', 'detail.idsummary = summary.id')
          ->join('inv_transfer transfer', 'summary.idtransfer = transfer.id')
          ->where('transfer.id = :idtransfer', array(':idtransfer' => $idtransfer))
          ->queryAll();
        
        $html = $this->renderPartial(
          'vfrm_terimatransferorder_type4',
          array(
            'form' => $form,
            'idstatus' => $form['idstatus'],
            'idtransfer' => $idtransfer,
            'userid_actor' => $this->userid_actor,
            'daftar_kurir' => $daftar_kurir,
            'daftar_lokasi' => $daftar_lokasi,
            'daftar_item' => $daftar_item,
            'daftar_summary' => $daftar_summary
          ),
          true
        );
        
        echo CJSON::encode(
          array(
            'valid' => 'ok',
            'html' => $html,
            'userid_actor' => $this->userid_actor,
          )
        );
      }
      else
      {
        //pelanggaran hak akses
        
        echo CJSON::encode(
          array(
            'valid' => 'not ok',
            'userid_actor' => $this->userid_actor,
          )
        );
      }
	  }
    
    
	  
	  //----- transfer order type3 - end
	  
	  
	  
	  
	  
	  //----- transfer order type4 - begin 

    /*
      actionTransferOrderType4View
      
      Deskripsi
      Action untuk menampilkan data transfer order type1. Sudut pandang si penerima.
      
      Parameter
      idtransfer
      
      Return
      Render view transfer order type1.
    */	  
	  public function actionTransferOrderType4View()
	  {
	    $this->userid_actor = Yii::app()->request->cookies['userid_actor']->value;
      $idtransfer = Yii::app()->request->getParam('idtransfer');
      $this->menuid = 36;
	    $this->parentmenuid = 9; 
      $idgroup = FHelper::GetGroupId($this->userid_actor);
      
      if(FHelper::AllowMenu($this->menuid, $idgroup, 'edit'))
      {
        //$daftar_kurir = FHelper::GetDaftarKurir();
        $daftar_kurir = FHelper::GetKaryawanListData();
        
        $daftar_lokasi = FHelper::GetLocationListData();
        unset($daftar_lokasi[0]);
        
        //tampilkan form edit transfer order
        $form = new frmEditTransferOrder();
        
        //ambil record inv_transfer berdasarkan idtransfer
        $Criteria = new CDbCriteria();
        $Criteria->condition = 'id = :idtransfer';
        $Criteria->params = array(':idtransfer' => $idtransfer);
        
        $inv_transfer = inv_transfer::model()->find($Criteria);
        $form['tanggal_kirim'] = date('D, d M Y', strtotime($inv_transfer['tanggal_kirim']));
        $form['id'] = $inv_transfer['id'];
        $form['idlokasike'] = $inv_transfer['idlokasike'];
        $form['idlokasidari'] = $inv_transfer['idlokasidari'];
        $form['idstatus'] = $inv_transfer['idstatus']; //1 = sedang dibuat; 2 = disetujui; 3 = sedang dikirim; 4 = sudah diterima
        
        //ambil daftar summary
        $Criteria->condition = 'idtransfer = :idtransfer';
        $Criteria->params = array(':idtransfer' => $idtransfer);
        $daftar_summary = inv_transfer_summary::model()->findAll($Criteria);
        
        //ambil daftar detail
        $daftar_item = Yii::app()->db->createCommand()
          ->select('detail.*')
          ->from('inv_transfer_detail detail')
          ->join('inv_transfer_summary summary', 'detail.idsummary = summary.id')
          ->join('inv_transfer transfer', 'summary.idtransfer = transfer.id')
          ->where('transfer.id = :idtransfer', array(':idtransfer' => $idtransfer))
          ->queryAll();
        
        $html = $this->renderPartial(
          'vfrm_viewtransferorder_type4',
          array(
            'form' => $form,
            'idstatus' => $form['idstatus'],
            'idtransfer' => $idtransfer,
            'userid_actor' => $this->userid_actor,
            'daftar_kurir' => $daftar_kurir,
            'daftar_lokasi' => $daftar_lokasi,
            'daftar_item' => $daftar_item,
            'daftar_summary' => $daftar_summary
          ),
          true
        );
        
        echo CJSON::encode(
          array(
            'valid' => 'ok',
            'html' => $html,
            'userid_actor' => $this->userid_actor,
          )
        );
      }
      else
      {
        //pelanggaran hak akses
        
        echo CJSON::encode(
          array(
            'valid' => 'not ok',
            'userid_actor' => $this->userid_actor,
          )
        );
      }
	  }
	  
	  /*
      actionTransferOrderType4Terima
      
      Deskripsi
      Action untuk menampilkan data transfer order type1. Sudut pandang si penerima.
      
      Parameter
      idtransfer
      
      Return
      Render view transfer order type1.
    */	  
	  public function actionTransferOrderType4Terima()
	  {
	    $this->userid_actor = Yii::app()->request->cookies['userid_actor']->value;
      $idtransfer = Yii::app()->request->getParam('idtransfer');
      $this->menuid = 36;
	    $this->parentmenuid = 9; 
      $idgroup = FHelper::GetGroupId($this->userid_actor);
      
      if(FHelper::AllowMenu($this->menuid, $idgroup, 'edit'))
      {
        $do_edit = Yii::app()->request->getParam('do_edit');
        
        if(isset($do_edit))
        {
          //update status transfer order menjadi 4 (sudah diterima)
          
          //pastikan semua barang yang diterima sudah dibacakan
          $command = Yii::app()->db->createCommand()
            ->select('*')
            ->from('inv_transfer_detail detil')
            ->join('inv_transfer_summary summary', 'summary.id = detil.idsummary')
            ->join('inv_transfer transfer', 'transfer.id = summary.idtransfer')
            ->where(
              'transfer.id = :idtransfer AND
              detil.idstatus <> 2', 
              array(':idtransfer' => $idtransfer));
          $hasil = $command->queryAll();
          
          if( count($hasil) > 0)
          {
            echo CJSON::encode(
              array(
                'valid' => 'not ok',
                'type' => 'not complete',
                'html' => $html,
                'userid_actor' => $this->userid_actor,
              )
            );
          }
          else
          {
            //ambil record inv_transfer berdasarkan idtransfer
            $Criteria = new CDbCriteria();
            $Criteria->condition = 'id = :idtransfer';
            $Criteria->params = array(':idtransfer' => $idtransfer);
            
            $inv_transfer = inv_transfer::model()->find($Criteria);
            $inv_transfer['idstatus'] = 4;
            $inv_transfer->save();
            
            //menampilkan daftar transfer order
            $this->actionTransferOrderShowMain();
          }
          
            
        }
        else
        {
          //$daftar_kurir = FHelper::GetDaftarKurir();
          $daftar_kurir = FHelper::GetKaryawanListData();
          
          $daftar_lokasi = FHelper::GetLocationListData();
          unset($daftar_lokasi[0]);
          
          //tampilkan form edit transfer order
          $form = new frmEditTransferOrder();
          
          //ambil record inv_transfer berdasarkan idtransfer
          $Criteria = new CDbCriteria();
          $Criteria->condition = 'id = :idtransfer';
          $Criteria->params = array(':idtransfer' => $idtransfer);
          
          $inv_transfer = inv_transfer::model()->find($Criteria);
          $form['tanggal_kirim'] = date('D, d M Y', strtotime($inv_transfer['tanggal_kirim']));
          $form['id'] = $inv_transfer['id'];
          $form['idlokasike'] = $inv_transfer['idlokasike'];
          $form['idlokasidari'] = $inv_transfer['idlokasidari'];
          $form['idstatus'] = $inv_transfer['idstatus']; //1 = sedang dibuat; 2 = disetujui; 3 = sedang dikirim; 4 = sudah diterima
          
          //ambil daftar summary
          $Criteria->condition = 'idtransfer = :idtransfer';
          $Criteria->params = array(':idtransfer' => $idtransfer);
          $daftar_summary = inv_transfer_summary::model()->findAll($Criteria);
          
          //ambil daftar detail
          $daftar_item = Yii::app()->db->createCommand()
            ->select('detail.*')
            ->from('inv_transfer_detail detail')
            ->join('inv_transfer_summary summary', 'detail.idsummary = summary.id')
            ->join('inv_transfer transfer', 'summary.idtransfer = transfer.id')
            ->where('transfer.id = :idtransfer', array(':idtransfer' => $idtransfer))
            ->queryAll();
          
          $html = $this->renderPartial(
            'vfrm_terimatransferorder_type4',
            array(
              'form' => $form,
              'idstatus' => $form['idstatus'],
              'idtransfer' => $idtransfer,
              'userid_actor' => $this->userid_actor,
              'daftar_kurir' => $daftar_kurir,
              'daftar_lokasi' => $daftar_lokasi,
              'daftar_item' => $daftar_item,
              'daftar_summary' => $daftar_summary
            ),
            true
          );
          
          echo CJSON::encode(
            array(
              'valid' => 'ok',
              'html' => $html,
              'userid_actor' => $this->userid_actor,
            )
          );
        }
        
        
      }
      else
      {
        //pelanggaran hak akses
        
        echo CJSON::encode(
          array(
            'valid' => 'not ok',
            'type' => 'access violation',
            'userid_actor' => $this->userid_actor,
          )
        );
      }
	  }
	  
	  /*
      actionTransferOrderType4TambahItem
      
      Deskripsi
      Action untuk menambahkan item penerimaan ke transfer order type1. Type4
      adalah sudut pandang si penerima terhadap transfer order type1.
      
      Parameter
      barcode
        String
      idtransfer
        Integer
      mode
        String. 'tambah' atau 'hapus'.
        
      Return
      Render view list item yang sudah dibaca dan view summary-nya.
    */
    public function actionTransferOrderType4TambahItem()
    {
      $this->userid_actor = Yii::app()->request->cookies['userid_actor']->value;
      $idlokasi_cookie = Yii::app()->request->cookies['idlokasi']->value;
      $idtransfer = Yii::app()->request->getParam('idtransfer');
      $barcode = Yii::app()->request->getParam('barcode');
      $mode = Yii::app()->request->getParam('mode');
      $this->menuid = 36;
	    $this->parentmenuid = 9; 
      $idgroup = FHelper::GetGroupId($this->userid_actor);
      
      //periksa apakah barcode ada di inv_item
        $Criteria = new CDbCriteria();
        $Criteria->condition = 'barcode = :barcode';
        $Criteria->params = array(':barcode' => $barcode);
        
        $item = inv_item::model()->find($Criteria);
        
        //ambil data item dari tabel inv_item
        if($item != null)
        {
          //memastikan barcode yang dibacakan ada dalam daftar inv_transfer_detail
          $iditem = $item['id'];
          $idlokasi_item = $item['idlokasi'];
          $idproduk = $item['idinventory'];
          
          $detail = Yii::app()->db->createCommand()
            ->select('detail.*')
            ->from('inv_transfer_detail detail')
            ->join('inv_transfer_summary summary', 'summary.id = detail.idsummary')
            ->join('inv_transfer transfer', 'transfer.id = summary.idtransfer')
            ->where(
              'transfer.id = :idtransfer' .
              ' AND '.
              'detail.iditem = :iditem', 
              array(
                  ':idtransfer' => $idtransfer,
                  ':iditem' => $iditem
                )
              )
            ->queryAll();
        
          $detail_count = count($detail);
        
          if($detail_count  == 1)
          {
            //item ditemukan dalam daftar transfer...
            
            switch($mode)
            {
              case 'tambah' :
                
                //jika record pada inv_transfer_detail masih berstatus 'dikirim'
                if($detail[0]['idstatus'] == 1) //idstatus transfer: 1 = dikirim; 2 = diterima; 3 = tambahan
                {
                  $idsummary = $detail[0]['idsummary'];
                  
                  //tandai record pada inv_transfer_detail sebagai 'diterima'
                    $Criteria->condition = 'idsummary = :idsummary AND iditem = :iditem';
                    $Criteria->params = array(
                      ':idsummary' => $idsummary,
                      ':iditem' => $iditem
                    );
                    
                    $inv_transfer_detail = inv_transfer_detail::model()->find($Criteria);
                    $inv_transfer_detail['idstatus'] = 2; //diterima
                    $inv_transfer_detail->save();
                  
                  //lalu update jumlah penerimaan pada tabel inv_transfer_summary
                    $Criteria->condition = 'id = :idsummary';
                    $Criteria->params = array(
                      ':idsummary' => $idsummary
                    );
                    
                    $inv_transfer_summary = inv_transfer_summary::model()->find($Criteria);
                    $inv_transfer_summary['jumlah_terima'] = $inv_transfer_summary['jumlah_terima'] + 1;
                    $inv_transfer_summary->save();
                  
                  //update trans_history dan latest_status
                  FHelper::ItemStatusUpdate($iditem, 3, $idlokasi_cookie, $this->userid_actor);
                  
                  $this->actionTransferOrderType4RefreshList();
                }
                else
                {
                  //record sudah ditandai sebagai 'diterima'
                  
                  echo CJSON::encode(
                    array(
                      'valid' => 'ok',
                      'status' => 'not ok',
                      'pesan' => 'Item sudah dibacakan'
                    )
                  );
                }
                break;
              case 'hapus' :
                
                //jika status di inv_transfer_detail 'diterima'
                
                switch($detail[0]['idstatus'])
                {
                  case 2 : //barang sudah diterima
                    
                    //kembalikan idstatus menjadi 'dikirim'
                    
                    $idsummary = $detail[0]['idsummary'];
                  
                    //update status di inv_transfer_detail menjadi 'dikirim'
                      $Criteria->condition = 'idsummary = :idsummary AND iditem = :iditem';
                      $Criteria->params = array(
                        ':idsummary' => $idsummary,
                        ':iditem' => $iditem
                      );
                      
                      $inv_transfer_detail = inv_transfer_detail::model()->find($Criteria);
                      $inv_transfer_detail['idstatus'] = 1;
                      $inv_transfer_detail->save();
                    
                    //update trans_history dan latest_status
                    FHelper::ItemStatusUpdate($iditem, 2, $idlokasi_cookie, $this->userid_actor);
                      
                    //update jumlah_diterima pada inv_transfer_summary
                      $Criteria->condition = 'id = :idsummary';
                      $Criteria->params = array(
                        ':idsummary' => $idsummary
                      );
                      
                      $inv_transfer_summary = inv_transfer_summary::model()->find($Criteria);
                      $inv_transfer_summary['jumlah_terima'] = $inv_transfer_summary['jumlah_terima'] - 1;
                      $inv_transfer_summary->save();
                      
                    $this->actionTransferOrderType4RefreshList();
                    break;
                  case 3 : //barang sudah diterima tapi diluar daftar resmi
                    
                    
                    //update angka jumlah pada inv_transfer_summary
                    
                    $idsummary = $detail[0]['idsummary'];
                  
                    //hapus data item dari inv_transfer_detail
                      $Criteria->condition = 'idsummary = :idsummary AND iditem = :iditem';
                      $Criteria->params = array(
                        ':idsummary' => $idsummary,
                        ':iditem' => $iditem
                      );
                      
                      inv_transfer_detail::model()->deleteAll($Criteria);
                      
                    //update trans_history dan latest_status
                    FHelper::ItemStatusUpdate($iditem, 3, $idlokasi_cookie, $this->userid_actor);
                      
                    //update jumlah_diterima pada inv_transfer_summary
                      $Criteria->condition = 'id = :idsummary';
                      $Criteria->params = array(
                        ':idsummary' => $idsummary
                      );
                      
                      $inv_transfer_summary = inv_transfer_summary::model()->find($Criteria);
                      $inv_transfer_summary['jumlah_terima'] = $inv_transfer_summary['jumlah_terima'] - 1;
                      $inv_transfer_summary->save();
                      
                    $this->actionTransferOrderType4RefreshList();
                    
                    break;
                  default:
                    //item sudah berstatus 'dikirim'
                  
                    echo CJSON::encode(
                      array(
                        'valid' => 'ok',
                        'status' => 'not ok',
                        'pesan' => 'Item sudah dihapus'
                      )
                    );
                    break;
                } //$detail[0]['idstatus']
                
                break;
            }//switch(mode)
          }
          else
          {
            //item tidak ditemukan dalam inv_transfer_detail
            
            //tolak penerimaan barang
            
            echo CJSON::encode(
              array(
                'valid' => 'ok',
                'status' => 'not ok',
                'pesan' => 'Item tidak valid'
              )
            );
          }
          
        }
        else
        {
          //barcode tidak valid
          
          echo CJSON::encode(
            array(
              'valid' => 'ok',
              'status' => 'not ok',
              'pesan' => 'Item tidak ada dalam database'
            )
          );
        }
        
    }
    
    /*
      actionTransferOrderType4RefreshList
      
      Deskripsi
      Action untuk menampilkan list detail dan summary suatu transfer order
      berdasarkan idtransfer.
      
      Parameter
      idtransfer
        Integer
        
      Return
      Render view list detail dan list summary.
    */
    public function actionTransferOrderType4RefreshList()
    {
      $idtransfer = Yii::app()->request->getParam('idtransfer');
      
      //ambil daftar detail
      $daftar_detail = Yii::app()->db->createCommand()
        ->select('detail.*')
        ->from('inv_transfer_detail detail')
        ->join('inv_transfer_summary summary', 'detail.idsummary = summary.id')
        ->join('inv_transfer transfer', 'summary.idtransfer = transfer.id')
        ->where('transfer.id = :idtransfer', array(':idtransfer' => $idtransfer))
        ->queryAll();
      
      $Criteria = new CDbCriteria();
      $Criteria->condition = 'idtransfer = :idtransfer';
      $Criteria->params = array(':idtransfer' => $idtransfer);
      $daftar_summary = inv_transfer_summary::model()->findAll($Criteria);
      
      $Criteria->condition = 'id = :idtransfer';
      $Criteria->params = array(':idtransfer' => $idtransfer);
      $inv_transfer = inv_transfer::model()->find($Criteria);
      
      //render view list detail dan view list summary
      $view_summary = $this->renderPartial(
        'v_list_summary_transferorder_type4',
        array(
          'daftar_summary' => $daftar_summary
        ),
        true
      );
      
      $view_detail = $this->renderPartial(
        'v_list_item_transferorder_type4',
        array(
          'daftar_detail' => $daftar_detail,
          'idstatus' => $inv_transfer['idstatus'],
          'idtransfer' => $idtransfer
        ),
        true
      );
      
      echo CJSON::encode(
        array(
          'valid' => 'ok',
          'status' => 'ok',
          'daftar_detail' => $view_detail,
          'daftar_summary' => $view_summary,
          'jumlah_summary' => count($daftar_summary),
          'jumlah_detail' => count($daftar_detail)
        )
      );
    }
    
	  //----- transfer order type4 - end 
	/* inventory - transfer order - end */
	

	
	/* inventory - job order - begin */
	
	
	  public function actionJobOrder()
	  {
	    $userid_actor = Yii::app()->request->getParam('userid_actor');
      $this->idlokasi = Yii::app()->request->cookies['idlokasi']->value;
      
      $this->userid_actor = $userid_actor;
      $this->menuid = 37;
      $this->parentmenuid = 9;
      $this->bread_crumb_list = 
        '<li>Inventory</li>'.
        '<li>></li>'.
        '<li>Job Order</li>';
      
      $TheMenu = FHelper::RenderMenu(0, $userid_actor, 9);
      
      $Criteria = new CDbCriteria();
      $Criteria->condition = 'is_del = 0';
      $Criteria->order = 'waktu desc';
      $joborders = inv_joborder::model()->findAll($Criteria);
      
      $this->layout = 'layout-baru';
      $TheContent = $this->renderPartial(
        'v_list_joborder',
        array(
          'userid_actor' => $userid_actor,
          'joborders' => $joborders
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
	  
	  
	  
	  /*
	    actionJobOrderList
	    
	    Deskripsi
	    Fungsi untuk menampilkan daftar job order. Sudut pandang user yang mengerjakan
	    job order. Job order yang ditampilkan berstatus 2 (submitted) dan 3 (sedang dikerjakan).
	    
	    Parameter
	    idlokasi
	      Integer. Sebagai filter pada field idlokasi_pengolah.
	  */
	  public function actionJobOrderList()
	  {
	    ini_set('max_execution_time', 0);
	    
	    $userid_actor = Yii::app()->request->cookies['userid_actor']->value;
      $this->idlokasi = Yii::app()->request->cookies['idlokasi']->value;
      $idlokasi = Yii::app()->request->cookies['idlokasi']->value;
      
      $this->userid_actor = $userid_actor;
      $this->menuid = 37;
      $this->parentmenuid = 9;
      
      $html = $this->JobOrder_GenerateView();
      
      $TheMenu = FHelper::RenderMenu(9, $userid_actor, 0);
      
      $this->layout = 'layout-baru';
      $this->render(
        'index_general',
        array(
          'TheMenu' => $TheMenu,
          'TheContent' => $html,
          'userid_actor' => $userid_actor
        )
      );
	  }
	  
	  public function actionJobOrderRefreshList()
	  {
	    $this->JobOrder_GenerateView();
	  }
	  
	  private function JobOrder_GenerateView()
	  {
	    $userid_actor = Yii::app()->request->cookies['userid_actor']->value;
      $this->idlokasi = Yii::app()->request->cookies['idlokasi']->value;
      $idlokasi = Yii::app()->request->cookies['idlokasi']->value;
      
	    $Criteria = new CDbCriteria();
      $Criteria->condition = '
        is_del = 0 AND
        idstatus in (2, 3) AND
        idlokasi_pengolah = :idlokasi AND
        waktu_dibikin >= :tanggal'; //idstatus >> 2 = submit; 3 = sedang dikerjakan
        
      $Criteria->params = array(
        ':idlokasi' => $idlokasi,
        ':tanggal' => date('Y-m-j 00:00:00', strtotime('-240 day'))
      );
      $Criteria->order = 'waktu_dibikin desc';
      $joborders = inv_joborder::model()->findAll($Criteria);
      
      $maketable = new MakeTable();
      
      //setup maketable pages
      $maketable->list_type = 'joborder';
      $maketable->action_name = 'Joborder_RefreshTable';
      $maketable->action_name2 = 'Joborder_RefreshTable2';
      $maketable->SetupPages( count($joborders) );
      
      //prepare for maketable's current view - begin
        $Criteria->offset = $maketable->offset;
        $Criteria->limit = $maketable->rows_per_page;
        $joborders = inv_joborder::model()->findAll($Criteria);
        
        $html = $this->renderPartial(
          'v_list_joborder_global',
          array(
            'userid_actor' => $userid_actor,
            'joborders' => $joborders
          ),
          true
        );
        
        $maketable->table_content = $html;
        $html = $maketable->Render($maketable);
      //prepare for maketable's current view - end
      
      if(Yii::app()->request->getIsAjaxRequest())
      {
        echo CJSON::encode( array('html' => $html) );
      }
      else
      {
        return $html;
      }
	  }
	  
	  public function actionJobOrderLihatResep()
	  {
	    $idsales = Yii::app()->request->getParam('idsales');
	    
	    $Criteria = new CDbCriteria();
      $Criteria->condition = 'sales_id = :sales_presc_id';
      $Criteria->params = array(':sales_presc_id' => $idsales);
      $pos_sales_presc = pos_sales_presc::model()->find($Criteria);
      
      $resep = 
        'R sph: ' . $pos_sales_presc['r_sph'] .
        ' cyl: ' . $pos_sales_presc['r_cyl'] .
        ' axis: ' . $pos_sales_presc['r_axis'] .
        ' prism: ' . $pos_sales_presc['r_prism'] .
        ' base: ' . $pos_sales_presc['r_base'] .
        ' add: ' . $pos_sales_presc['r_add'] .
        ' dist: ' . $pos_sales_presc['r_dist_pd'] .
        ' near: ' . $pos_sales_presc['r_near_pd'] . 
        
        '<br/>' .
        
        'L sph: ' . $pos_sales_presc['l_sph'] .
        ' cyl: ' .   $pos_sales_presc['l_cyl'] .
        ' axis: ' .  $pos_sales_presc['l_axis'] .
        ' prism: ' . $pos_sales_presc['l_prism'] .
        ' base: ' .  $pos_sales_presc['l_base'] .
        ' add: ' .   $pos_sales_presc['l_add'] .
        ' dist: ' .  $pos_sales_presc['l_dist_pd'] .
        ' near: ' .  $pos_sales_presc['l_near_pd'];
        
      $precal = FHelper::GetPrecalByIdSales2($idsales);
      
      $hasil = "$resep<br/><br/>$precal";
      
      echo CJSON::encode(array('html' => $hasil));
	  }
	  
	  public function actionJobOrderListBySales()
	  {
	    $userid_actor = Yii::app()->request->cookies['userid_actor']->value;
      $this->idlokasi = Yii::app()->request->cookies['idlokasi']->value;
      
      $this->userid_actor = $userid_actor;
      $this->menuid = 37;
      $this->parentmenuid = 9;
      
      $idsales = Yii::app()->request->getParam('idsales');
      
      //ambil daftar joborder berdasarkan idsales
      $command = Yii::app()->db->createCommand()
        ->select('*')
        ->from('inv_joborder')
        ->where('id_sales = :idsales', array(':idsales' => $idsales));
      $daftar_joborder = $command->queryAll();
      
      $html = $this->renderPartial(
        'v_list_joborder_by_sales',
        array(
          'daftar_joborder' => $daftar_joborder
        ),
        true
      );
      
      echo CJSON::encode(
        array(
          'html' => $html
        )
      );
	  }
	  
	  /*
	    actionJobOrderTambah
	    
	    Deskripsi
	    Fungsi untuk memulai pembuatan job order baru.
	    
	    Parameter
	    idsales
	      Integer
	    id_sales_presc
	      Integer. id resep yang dipakai pada sales.
	      
	    Return
	    Mengembalikan view form pengisian job order.
	  */
	  public function actionJobOrderTambah()
	  {
	    $idsales = Yii::app()->request->getParam('idsales');
	    $idlokasi_pengolah = Yii::app()->request->getParam('idlokasipengolah');
	    $catatan = Yii::app()->request->getParam('catatan');
	    $idinventory = Yii::app()->request->getParam('idinventory');
	    $kirikanan = Yii::app()->request->getParam('kirikanan');
	    $prioritas = Yii::app()->request->getParam('prioritas');
      
      $job_order = new inv_joborder();
	    $job_order['id_sales'] = $idsales;
	    //$job_order['id_cust_presc'] = $idsales_presc;
	    $job_order['waktu_dibikin'] = date('Y-m-d H:n:s');
	    $job_order['idinventory_pesan'] = $idinventory;
	    $job_order['idlokasi_penerbit'] = Yii::app()->request->cookies['idlokasi']->value;
	    $job_order['idlokasi_pengolah'] = $idlokasi_pengolah;
	    $job_order['catatan'] = $catatan;
	    $job_order['idstatus'] = 1; //1: baru; 2 = submitted; 3 = dikerjakan; 4 = selesai dikerjakan; 5 = barang sudah diterima
	    $job_order['leftright'] = $kirikanan;
	    $job_order['prioritas'] = $prioritas;
      
      Yii::log("kirikanan = $kirikanan", 'info');
	    
	    try
	    {
	      if($job_order->save())
        {
          echo CJSON::encode(array('status' => 'ok'));
        }
        else
        {
          echo CJSON::encode(array('status' => 'not ok'));
          
          //Yii::log('error = ' . print_r($job_order->getErrors(), true), 'info');
        }
	    }
	    catch(Exception $e)
	    {
	      //Yii::log('error = ' . print_r($job_order->getErrors(), true), 'info');
	      //Yii::log('error = ' . print_r($e, true), 'info');
	    }
	    
	  }
	  
	  /*
	    actionJobOrderHapus
	    
	    Deskripsi
	    Fungsi untuk menghapus suatu job order
	    
	    Parameter
	    idjoborder
	      Integer
	  */
	  public function actionJobOrderHapus()
	  {
	    $idjoborder = Yii::app()->request->getParam('idjoborder');
	    
	    $job_order = inv_joborder::model()->findByPk($idjoborder);
	    
	    try
	    {
	      $job_order->delete();
	      
	      Yii::log('error = ' . print_r($job_order->getErrors(), true), 'info');
	    }
	    catch(Exception $e)
	    {
	      Yii::log('error = ' . print_r($job_order->getErrors(), true), 'info');
	      
	      Yii::log('error = ' . print_r($e, true), 'info');
	    }
	    
	    
	    echo CJSON::encode(array('status' => 'ok'));
	  }
	  
	  /*
	    actionJobOrderSubmit
	    
	    Deskripsi
	    Fungsi untuk menyatakan suatu job order dikirim kepada pengolah job order
	    
	    Parameter
	    idjoborder
	      Integer
	  */
	  public function actionJobOrderSubmit()
	  {
	    $idjoborder = Yii::app()->request->getParam('idjoborder');
	    
	    $job_order = inv_joborder::model()->findByPk($idjoborder);
	    $job_order['idstatus'] = 2;
	    
	    
	    if($job_order->save())
	    {
	      echo CJSON::encode(array('status' => 'ok'));
	    }
	    else
	    {
	      echo CJSON::encode(
	        array(
	          'status' => 'not ok',
	          'pesan' => 'Gagal submit job order.'));
	      
	      
	      Yii::log('error = ' . print_r($job_order->getErrors(), true), 'info');
	    }
	  }
	  
	  
	  /*
	    actionJobOrderSelesai
	    
	    Deskripsi
	    Fungsi untuk menyatakan suatu job order sudah selesai dikerjakan
	    
	    Parameter
	    idjoborder
	      Integer
	  */
	  public function actionJobOrderSelesai()
	  {
	    $idjoborder = Yii::app()->request->getParam('idjoborder');
	    
	    $job_order = inv_joborder::model()->findByPk($idjoborder);
	    
	    //update status item akibat penyelesaian job order. Item yang dipilih 
	    //dalam penyelesaian job order langsung dianggap sebagai inventory cabang
	    //yang memesan
	    
	    $iditem = $job_order['id_item'];
	    $idstatus = 4;
	    $idlokasi = $job_order['idlokasi_penerbit'];
	    $iduser = Yii::app()->request->cookies['userid_actor']->value;
	    FHelper::ItemStatusUpdate($iditem, $idstatus, $idlokasi, $iduser);
	    
	    //update informasi job order
	    $job_order['idstatus'] = 4;
	    $id_sales_det = $job_order['id_sales']; // << ini adalah foreign key ke tabel pos_sales_det.id
	    
	    if($job_order->save())
	    {
	      //set informasi barcode pada pos_sales_det. dilakukan berdasarkan
	      //id_sales_det dan iditem pada inv_joborder
	      
	      $barcode = FHelper::GetBarcodeByIdItem($job_order['id_item']);
	      
	      $hasil = Yii::app()->db->createCommand()
	        ->update(
	          'pos_sales_det',
	          array(
	            'barcode' => $barcode,
	            "item_id" => $job_order['id_item'],
	            'nobarcode' => 0
            ),
	          'id = :id_sales_det',
	          array(
	            ':id_sales_det' => $id_sales_det
            )
          );
	      
	      echo CJSON::encode(array('status' => 'ok'));
	    }
	    else
	    {
	      echo CJSON::encode(
	        array(
	          'status' => 'not ok',
	          'pesan' => 'Job order gagal ditandai sebagai "selesai".')
        );
	    }
	  }
	  
	  
	  /*
	    actionJobOrderDikerjakan
	    
	    Deskripsi
	    Fungsi untuk menyatakan suatu job order sedang dikerjakan. Menampilkan view
	    form pengerjaan job order. Form akan menentukan barang aktual yang dikerjakan
	    dan pemberian barcode.
	    
	    Parameter
	    idjoborder
	      Integer
	  */
	  public function actionJobOrderDikerjakan()
	  {
	    $idjoborder = Yii::app()->request->getParam('idjoborder');
	    $iduser = Yii::app()->request->cookies['userid_actor']->value;
	    
	    //tampilkan form pengerjaan job order
      
      $command = Yii::app()->db->createCommand()
        ->select('*')
        ->from('inv_joborder')
        ->where('id = :idjoborder', array(':idjoborder' => $idjoborder));
      $joborder = $command->queryRow();
      
      $html = $this->renderPartial(
        'vfrm_joborder_pengerjaan',
        array(
          'joborder' => $joborder,
          'idjoborder' => $idjoborder
        ),
        true
      );
      
      echo CJSON::encode(array('html' => $html));
	  }
	  
	  /*
	    actionJobOrderSimpanPengerjaan
	    
	    Deskripsi
	    Fungsi untuk menyimpan informasi pengerjaan dari suatu job order.
	    
	    Parameter
	    idjoborder
	      Integer.
	    idinventory_aktual
	      Integer
	    barcode
	      String
	  */
	  public function actionJobOrderSimpanPengerjaan()
	  {
	    $idjoborder = Yii::app()->request->getParam('idjoborder');
	    $iduser = Yii::app()->request->cookies['userid_actor']->value;
	    $barcode = Yii::app()->request->getParam('barcode');
	    $idinventory_aktual = Yii::app()->request->getParam('idinventory');
	    $aktualsama = Yii::app()->request->getParam('aktualsama');
	    
	    //ambil idproduk dipesan berdasarkan idjoborder
	    $command = Yii::app()->db->createCommand()
        ->select('*')
        ->from('inv_joborder')
        ->where('id = :idjoborder', array(':idjoborder' => $idjoborder));
      $joborder = $command->queryRow();
      $idinventory_dipesan = $joborder['idinventory_pesan'];
	    
      //$frmJoborder = new frmJoborder();
      //$frmJoborder->attributes = Yii::app()->request->getParam('frmJoborder');
      
      //ambil iditem berdasarkan barcode
      $command = Yii::app()->db->createCommand()
        ->select('*')
        ->from('inv_item')
        ->where('barcode = :barcode', array(':barcode' => $barcode));
      $item = $command->queryRow();
      
      //apakah barang aktual sama dengan barang dipesan??
      if($aktualsama)
      {
        
        
        //pastikan barang yang di-scan idinventory-nya sama dengan barang yang dipesan
        
        if($item['idinventory'] == $idinventory_dipesan)
        {
          
          //update tabel inv_joborder
          
          $hasil = $this->UpdateJobOrder(
            $idjoborder, 
            $item['idinventory'], 
            $item['id'], 
            $iduser, 
            3 //dikerjakan
          );
          
          if($hasil == true)
          {
            echo CJSON::encode(
              array(
                'status' => 'ok',
                'idjoborder' => $idjoborder
              )
            );
          }
          else
          {
            echo CJSON::encode(
              array(
                'status' => 'not ok',
                'idjoborder' => $idjoborder,
                'pesan' => 'Barcode sudah pernah dibacakan pada job order yang lain.'
              )
            );
          }
            
        }
        else
        {
          //barang yang di-scan bukan barang yang sama dengan pesanan
          
          echo CJSON::encode(
            array(
              'status' => 'not ok',
              'pesan' => 'Jenis barang dan barcode tidak cocok'
            )
          );
        }
        
      }
      else
      {
        //barang aktual berbeda dari barang pesanan.
        
        //pastikan barcode yang di-scan adalah barang yang kategorinya sama dengan 
        //barang yang dipilih
        
        $produk_aktual = FHelper::GetRecordProduk($idinventory_aktual);
        $produk_dipesan = FHelper::GetRecordProduk($item['idinventory']);
        
        
        if( $produk_aktual['idkategori'] == $produk_dipesan['idkategori'] )
        {
          $hasil = $this->UpdateJobOrder(
            $idjoborder, 
            $item['idinventory'], 
            $item['id'], 
            $iduser, 
            3 //dikerjakan
          );
          
          if($hasil == true)
          {
            echo CJSON::encode(
              array(
                'status' => 'ok',
                'idjoborder' => $idjoborder)
            );
          }
          else
          {
            echo CJSON::encode(
              array(
                'status' => 'not ok',
                'idjoborder' => $idjoborder,
                'pesan' => 'Barcode sudah pernah dibacakan pada job order yang lain.')
            );
          }
        }
        else
        {
          //barcode dan barang yang dipilih berbeda jenis (kategori).
          
          echo CJSON::encode(
            array(
              'status' => 'not ok',
              'pesan' => 'Jenis barang dan barcode tidak cocok'
            )
          );
        }
        
      } //apakah barang aktual berbeda dari barang dipesan??
	  }
	  
	  /*
	    UpdateJobOrder()
	    
	    Deskripsi
	    Fungsi untuk update status suatu record job order. Update dilakukan
	    berdasarkan idjoborder. Field yang di-update: idinventory_aktual,
	    iditem, waktu_dikerjakan, iduser_lab, status
	    
	    Parameter
	  */
	  private function UpdateJobOrder($idjoborder, $idinventory_aktual, $iditem, $iduser, $status)
	  {
	    try
      {
        Yii::app()->db->createCommand()
          ->update(
            'inv_joborder',
            array(
              'idinventory_aktual' => $idinventory_aktual,
              'id_item' => $iditem,
              'waktu_dikerjakan' => date('Y-m-d H:n:s'),
              'iduser_lab' => $iduser,
              'idstatus' => $status
            ),
            'id = :idjoborder',
            array(
              ':idjoborder' => $idjoborder
            )
          );
        
        return true;
      }
      catch(Exception $e)
      {
        Yii::log("UpdateJobOrder with exception : {$e->getMessage()}", 'info');
        
        return false;
      }
	  }
	  
	  /*
	    actionJobOrderHapusPengerjaan
	    
	    Deskripsi
	    Fungsi untuk menghapus informasi pengerjaan dari suatu job order.
	    
	    Parameter
	    idjoborder
	      Integer.
	    idinventory_aktual
	      Integer
	    barcode
	      String
	  */
	  public function actionJobOrderHapusPengerjaan()
	  {
	    $idjoborder = Yii::app()->request->getParam('idjoborder');
	    $iduser = Yii::app()->request->cookies['userid_actor']->value;
	    $barcode = Yii::app()->request->getParam('barcode');
	    
	    //ambil entry form
      $frmJoborder = new frmJoborder();
      $frmJoborder->attributes = Yii::app()->request->getParam('frmJoborder');
      
      //ambil iditem berdasarkan barcode
      $command = Yii::app()->db->createCommand()
        ->select('*')
        ->from('inv_item')
        ->where('barcode = :barcode', array(':barcode' => $barcode));
      $item = $command->queryRow();
      
      //update tabel inv_joborder
      Yii::app()->db->createCommand()
        ->update(
          'inv_joborder',
          array(
            'idinventory_aktual' => '',
            'id_item' => 0,
            'waktu_dikerjakan' => date('Y-m-d H:n:s'),
            'iduser_lab' => $iduser,
            'idstatus' => '3' //sedang dikerjakan
          ),
          'id = :idjoborder',
          array(
            ':idjoborder' => $idjoborder
          )
        );
      
      //tampilkan view berhasil update
	    echo CJSON::encode(array('status' => 'ok'));
	  }
	  
	  
	  
	  /*
	    actionJobOrderTerima
	    
	    Deskripsi
	    Fungsi untuk menyatakan barang dari suatu job order sudah diterima
	    
	    Parameter
	    idjoborder
	      Integer
	  */
	  public function actionJobOrderTerima()
	  {
	    $idjoborder = Yii::app()->request->getParam('idjoborder');
	    $barcode = Yii::app()->request->getParam('barcode');
	    
	    //pastikan barcode yang dibacakan sesuai dengan iditem pada joborder
	    $iditem = FHelper::GetIdItemByBarcode($barcode);
	    
	    //kecuali checkbox 'pengganti' di-checked
	    
	    
	    //ambil iditem berdasarkan idjoborder
	    $command = Yii::app()->db->createCommand()
	      ->select('*')
	      ->from('inv_joborder')
	      ->where('id = :idjoborder', array(':idjoborder' => $idjoborder));
	    $joborder = $command->queryRow();
	    
	    if($joborder['id_item'] == $iditem)
	    {
	      $job_order = inv_joborder::model()->findByPk($idjoborder);
        $job_order['idstatus'] = 5;
        $job_order->save();
        
        echo CJSON::encode(
          array(
            'status' => 'ok',
            'pesan' => 'Barang sudah diterima'
          )
        );
	    }
	    else
	    {
	      echo CJSON::encode(
          array(
            'status' => 'not ok',
            'pesan' => 'Barang yang dibacakan tidak sesuai dengan barang pada job order.'
          )
        );
	    }
	  }
	  
	  
	  /*
	    actionJobOrderEdit
	    
	    Deskripsi
	    Fungsi untuk menampilkan form edit job order.
	    
	    Parameter
	    idjoborder
	      Integer
	      
	    Result
	    Menampilkan view form edit joborder.
	  */
	  public function actionJoborderEdit()
	  {
	    $userid_actor = Yii::app()->request->getParam('userid_actor');
      $this->idlokasi = Yii::app()->request->cookies['idlokasi']->value;
      
      $this->userid_actor = $userid_actor;
      $this->menuid = 37;
      $this->parentmenuid = 9;
      $this->bread_crumb_list = 
        '<li>Inventory</li>'.
        '<li>></li>'.
        CHtml::link('Job Order', 'inventory/joborder').
        '<li>></li>'.
        '<li>Edit Job Order</li>';
        
      $idjoborder = Yii::app()->request->getParam('idjoborder');
      
      $joborder = inv_joborder::model()->findByPk($idjoborder);
      
      $form = new frmEditJoborder();
      
      $form['idjoborder'] = $joborder['id'];
      
      $idproduk = $joborder->sales_item['item_id'];
      $nama_produk = FHelper::GetProdukName($idproduk);
      $brand_produk = FHelper::GetProdukBrand($idproduk);
      $ukuran_produk = FHelper::GetProdukUkuran($idproduk);
      $barang_dipesan = $nama_produk . ' | ' .
                        $brand_produk . ' | ' .
                        $ukuran_produk;
                        
      //jumlah dipesan
      $jumlah_dipesan = $joborder->sales_item['item_qty'];
                        
      //ambil info barang aktual
      $idproduk = $joborder['replacement_item_id'];
      $barang_aktual = 'Tidak ada';
      if($idproduk != -1)
      {
        $real_nama_produk = FHelper::GetProdukName($idproduk);
        $real_brand_produk = FHelper::GetProdukBrand($idproduk);
        $real_ukuran_produk = FHelper::GetProdukUkuran($idproduk);
        $barang_aktual = $real_nama_produk . ' | ' .
                         $real_brand_produk . ' | ' .
                         $real_ukuran_produk;
      }
                        
      //ambil info resep (via joborder[sales_presc_id]
      $sales_presc_id = $joborder['sales_presc_id'];
      
      $resep = FHelper::GetResepByIdSales($sales_presc_id);
       
      //ambil status
      switch($joborder['idstatus'])
      {
        case 1: //baru
          $status = 'Baru';
          break;
        case 2: //sedang dikerjakan
          $status = 'Sedang dikerjakan';
          break;
        case 3: //selesai dikerjakan
          $status = 'Sudah selesai dikerjakan';
          break;
      }
            
      $form['idjoborder'] = $joborder['id'];
      $form['waktu'] = $joborder['waktu'];
      $form['idstatus'] = $joborder['idstatus'];
      
      $html = $this->renderPartial(
        'vfrm_editjoborder',
        array(
          'form' => $form,
          'barang_dipesan' => $barang_dipesan,
          'barang_aktual' => $barang_aktual,
          'jumlah_dipesan' => $jumlah_dipesan,
          'resep' => $resep,
          'status' => $status,
          'joborder' => $joborder
        ),
        true
      );
      
      echo CJSON::encode(
        array(
          'html' => $html
        )
      );
	  }
	  
	  /*
	    actionJobOrderView
	    
	    Deskripsi
	    Fungsi untuk menampilkan form view job order.
	    
	    Parameter
	    idjoborder
	      Integer
	      
	    Result
	    Menampilkan view info joborder.
	  */
	  public function actionJobOrderView()
	  {
	    $userid_actor = Yii::app()->request->cookies['userid_actor']->value;
      $this->idlokasi = Yii::app()->request->cookies['idlokasi']->value;
      $idsales = Yii::app()->request->getParam('idsales');
      
      //ambil id_sales_presc berdasarkan idsales
      $command = Yii::app()->db->createCommand()
        ->select('*')
        ->from('pos_sales_presc')
        ->where('sales_id = :idsales', array(':idsales' => $idsales));
      $sales_presc = $command->queryRow();
      $sales_presc_id = $sales_presc['presc_id'];
      
      //ambil daftar joborder berdasarkan idsales
      $command = Yii::app()->db->createCommand()
        ->select('*')
        ->from('inv_joborder')
        ->where('id_sales = :idsales', array(':idsales' => $idsales));
      $daftar_joborder = $command->queryAll();
      
      //siapkan tampilan view joborder
      $this->userid_actor = $userid_actor;
      $this->menuid = 37;
      $this->parentmenuid = 9;
      $this->bread_crumb_list = 
        '<li>Inventory</li>'.
        '<li>></li>'.
        CHtml::link('Job Order', 'inventory/joborder').
        '<li>></li>'.
        '<li>View Job Order</li>';
        
      $form = new frmEditJoborder();
      
      /*
      $idproduk = $joborder->sales_item['item_id'];
      $nama_produk = FHelper::GetProdukName($idproduk);
      $brand_produk = FHelper::GetProdukBrand($idproduk);
      $ukuran_produk = FHelper::GetProdukUkuran($idproduk);
      $barang_dipesan = $nama_produk . ' | ' .
                        $brand_produk . ' | ' .
                        $ukuran_produk;
      */
      
      
      //jumlah dipesan
      //$jumlah_dipesan = $joborder->sales_item['item_qty'];
                        
      //ambil info barang aktual
      /*
      $idproduk = $joborder['replacement_item_id'];
      $barang_aktual = 'Tidak ada';
      if($idproduk != -1)
      {
        $real_nama_produk = FHelper::GetProdukName($idproduk);
        $real_brand_produk = FHelper::GetProdukBrand($idproduk);
        $real_ukuran_produk = FHelper::GetProdukUkuran($idproduk);
        $barang_aktual = $real_nama_produk . ' | ' .
                         $real_brand_produk . ' | ' .
                         $real_ukuran_produk;
      }
      */
      
      
      //ambil info resep (via joborder[sales_presc_id]
      $resep = FHelper::GetResepByIdSales2($idsales);
      
      //ambil status
      switch($joborder['idstatus'])
      {
        case 1: //baru
          $status = 'Baru';
          break;
        case 2: //submitted
          $status = 'Sudah dikirim';
          break;
        case 3: //sedang dikerjakan
          $status = 'Sedang dikerjakan';
          break;
        case 4: //selesai dikerjakan
          $status = 'Sudah selesai dikerjakan';
          break;
        case 5: //barang sudah diterima
          $status = 'Barang sudah diterima';
          break;
      }
      
      $daftar_lokasi = FHelper::GetLocationListData(false);
            
      $html = $this->renderPartial(
        'v_view_joborder',
        array(
          'form' => $form,
          'daftar_joborder' => $daftar_joborder,
          'daftar_lokasi' => $daftar_lokasi,
          'resep' => $resep,
          'status' => $status,
          'idsales' => $idsales
        ),
        true
      );
      
      echo CJSON::encode(
        array(
          'html' => $html
        )
      );
	  }
	  
	  /*
	    actionJobOrderCariBarang
	    
	    Deskripsi
	    Fungsi untuk mencari barang berdasarkan nama yang diketik user.
	    
	    Parameter
	    namabarang
	      String
	      
	    Result
	    Mengembalikan dropdown list untuk ditampilkan.
	  */
	  public function actionJobOrderCariBarang()
	  {
	    $userid_actor = Yii::app()->request->cookies['userid_actor']->value;
      $this->idlokasi = Yii::app()->request->cookies['idlokasi']->value;
      
      $this->userid_actor = $userid_actor;
      $this->menuid = 37;
      $this->parentmenuid = 9;
      
      $namabarang = Yii::app()->request->getParam('namabarang');
      $dropdownlist = array();
      $dropdownlist[-1] = 'Pilih lensa...';
      
      if($namabarang != '')
      {
        $command = Yii::app()->db->createCommand()
          ->select('*')
          ->from('inv_inventory produk')
          ->where(
            "produk.idkategori in (1, 3) AND
            produk.nama like :nama AND
            produk.is_del = 0 AND
            produk.is_deact = 0", 
            array(
              ':nama' => "%$namabarang%"
            ));
        $command->distinct = true;
        $daftar_produk = $command->queryAll();
        
        foreach($daftar_produk as $produk)
        {
          $idproduk = $produk['id'];
          
          $real_nama_produk = FHelper::GetProdukName($idproduk);
          $real_brand_produk = FHelper::GetProdukBrand($idproduk);
          $real_ukuran_produk = FHelper::GetProdukUkuran($idproduk);
          $barang = $real_nama_produk . ' | ' .
                    $real_brand_produk . ' | ' .
                    $real_ukuran_produk;
                           
          $dropdownlist[$idproduk] = $barang;
        }
      }
      
      $dropdownlist = CHtml::dropDownList(
        'cbNamaLensa',
        '-1',
        $dropdownlist
      );
      
      echo CJSON::encode(
        array(
          'dropdownlist' => $dropdownlist
        )
      );
	  }
	
	
	/* inventory - job order - end */
	
	
	
	/* inventory - hapus barcode - begin */
	
	
	  /*
	    actionRawBarcodeList
	    
	    Deskripsi
	    Fungsi untuk menampilkan daftar barcode yang belum diverifikasi.
	  */
	  public function actionRawBarcodeList()
	  {
	    $userid_actor = Yii::app()->request->cookies['userid_actor']->value;
      $this->idlokasi = Yii::app()->request->cookies['idlokasi']->value;
      $page_no = Yii::app()->request->getParam('page_no');
      
      if( isset($page_no) == false)
      {
        $page_no = 0;
      }
      
      $rows_per_page = 20;
      
      $this->userid_actor = $userid_actor;
      $this->menuid = 62;
      $this->parentmenuid = 9;
      
	    $refresh = Yii::app()->request->getParam('refresh');
	    
	    //---- hitung total halaman
        $command = Yii::app()->db->createCommand()
          ->select('*')
          ->from('inv_item')
          ->where('idstatus = -1 AND idlokasi = -1', array())
          ->order('barcode asc');
        $hasil = $command->queryAll();
        $rows = count($hasil);
        $pages = (int)($rows / $rows_per_page);
        if( ($rows % $rows_per_page) > 0 )
          $pages++;
	    //---- hitung total halaman
	    
	    $command = Yii::app()->db->createCommand()
	      ->select('*')
	      ->from('inv_item')
	      ->where('idstatus = -1 AND idlokasi = -1', array())
	      ->order('barcode asc')
	      ->offset($page_no * $rows_per_page)
	      ->limit($rows_per_page);
      
      $daftar = $command->queryAll();
      
      $tabel = $this->renderPartial(
        'v_table_rawbarcode',
        array(
          'daftar_rawbarcode' => $daftar,
          'pages' => $pages,
          'page_no' => $page_no
        ),
        true
      );
      
      if(isset($refresh) == false)
      {
        $this->layout = 'layout-baru';
        $this->render(
          'v_list_rawbarcode',
          array(
            'tabel' => $tabel,
            'daftar_rawbarcode' => $daftar
          )
        );
      }
      else
      {
        echo CJSON::encode(array('html' => $tabel));
      }
      
	  }
	  
	  
	  /*
	    actionRawBarcodeRead
	    
	    Deskripsi
	    Fungsi untuk menerima pembacaan barcode dan melakukan verifikasi
	  */
	  public function actionRawBarcodeRead()
	  {
	    $barcode = Yii::app()->request->getParam('barcode');
	    $idlokasi = Yii::app()->request->cookies['idlokasi']->value;
	    $iduser = Yii::app()->request->cookies['userid_actor']->value;
	    
	    $command = Yii::app()->db->createCommand()
	      ->select('*')
	      ->from('inv_item')
	      ->where(
	        'idstatus = -1 AND
	        barcode = :barcode', 
	        array(':barcode' => $barcode));
      
      $item = $command->queryAll();
      
      //pastikan barcode ada dan valid
      if(count($item) == 1)
      {
        //barcode ditemukan
        
        //lakukan verifikasi barcode
        $hasil = Yii::app()->db->createCommand()
          ->update(
            'inv_item',
            array(
              'idstatus' => '3',
              'idlokasi' => $idlokasi
            ),
            'idstatus = -1 AND barcode = :barcode',
            array(':barcode' => $barcode)
          );
          
        //catat status history
        FHelper::ItemStatusUpdate($item[0]['id'], 3, $idlokasi, $iduser);
        
        $respon_status = 'ok';
        $respon_pesan = 'Barcode sudah diverifikasi';
      }
      else
      {
        //barcode tidak ditemukan
        
        $respon_status = 'not ok';
        $respon_pesan = 'Barcode tidak valid atau sudah diverifikasi.';
      }
      
      echo CJSON::encode(
        array(
          'status' => $respon_status, 
          'pesan' => $respon_pesan, 
          'barcode' => $barcode));
	  }
	  
	  
	  /*
	    actionRawBarcodeDelete
	    
	    Deskripsi
	    Fungsi untuk menghapus barcode yang belum diverifikasi berdasarkan batas 
	    umur barcode yang belum diverifikasi. Umur dinyatakan dalam satuan hari.
	  */
	  public function actionRawBarcodeDelete()
	  {
	    //mengambil preferensi batas umur barcode untuk menghapus barcode belum
	    //diverifikasi.
	    
	    $command = Yii::app()->db->createCommand()
	      ->select('value')
	      ->from('sys_setting')
	      ->where('name = "Umur Verifikasi Barcode"');
	    $pref = $command->queryRow();
	    
	    $tanggal = strtotime("-{$pref['value']} days");
	    
	    $command = Yii::app()->db->createCommand();
	    $command->text = "
	      delete inv_item from
	      inv_item inner join inv_latest_status on inv_item.id = inv_latest_status.iditem
	      where
	      inv_item.idstatus = -1 and
	      inv_latest_status.waktu <= :tanggal
      ";
      $hasil = $command->execute(array(':tanggal' => date("Y-m-d H:i:s", $tanggal)));
	    
	    $respon_status = 'ok';
      $respon_pesan = "Barcode yang belum diverifikasi berhasil dihapus. Jumlah record yang dihapus $hasil.";
      
      echo CJSON::encode(array('status' => $respon_status, 'pesan' => $respon_pesan));
	  }
	
	
	/* inventory - hapus barcode - begin */
	
	
	/* inventory - stock opname - begin */
	
	
	  /*
	    actionStockOpnameIndex
	    
	    Deskripsi
	    Fungsi untuk menampilkan daftar stock opname suatu toko
	    
	    Parameter
	    userid_actor
	      Integer
	    idlokasi
	      Integer
	      
	    Return
	    Mengembalikan interface yang menampilkan daftar stock opname.
	  */
	  public function actionStockOpnameIndex()
	  {
	    $idlokasi = Yii::app()->request->cookies['idlokasi']->value;
	    $this->parentmenuid = 9; 
	    $this->userid_actor = Yii::app()->request->cookies['userid_actor']->value;
	    
	    $command = Yii::app()->db->createCommand()
	      ->select('*')
	      ->from('inv_stock_opname')
	      ->where('is_del = 0')
	      ->order('tanggal desc');
	    $daftar_stock_opname = $command->queryAll();
	    
	    $this->layout = 'layout-baru';
	    
	    $tabel_stock_opname = $this->renderPartial(
	      'v_list_stock_opname',
	      array('daftar_stock_opname' => $daftar_stock_opname),
	      true
      );
	    
	    $this->render(
	      'v_stock_opname',
	      array('tabel_stock_opname' => $tabel_stock_opname)
      );
	  }
	  
	  /*
	    actionStockOpnameTambah
	    
	    Deskripsi
	    Fungsi untuk menampilkan interface pembuatan stock opname baru.
	    
	    Parameter
	    userid_actor
	      Integer
	      
	    Return
	    Interface pembuatan stock opname baru
	  */
	  public function actionStockOpnameTambah()
	  {
	    $html = $this->renderPartial(
	      'vfrm_stockopname',
	      array(),
	      true
      );
      
      echo CJSON::encode(array('html' => $html));
	  }

    /*
      actionStockOpnameSimpan
      
      Deskripsi
      Fungsi untuk menyimpan stock opname baru.
      
      Parameter
      idlokase
        Integer
      tanggal
        String
        
      Return
      Mengembalikan interface untuk melakukan proses stock opname.
    */	  
	  public function actionStockOpnameSimpan()
	  {
	    $tanggal = Yii::app()->request->getParam('tanggal');
	    $idlokasi = Yii::app()->request->cookies['idlokasi']->value;
	    
	    //simpan record stock opname baru.
	    $stock_opname = new inv_stock_opname();
	    $stock_opname['tanggal'] = $tanggal;
	    $stock_opname['idlokasi'] = $idlokasi;
	    $stock_opname['create_time'] = date('Y-m-j H:i:s');
	    $stock_opname['create_by'] = Yii::app()->request->cookies['userid_actor']->value;
	    $stock_opname->save();
	    
	      
	    
	    //ambil id_stock_opname
	    $idstockopname = $stock_opname->getPrimaryKey();
	    
	    
	    //bikin record-record inv_stock_opname_detail berdasarkan
	    //status inv_item saat ini.
	    $command = Yii::app()->db->createCommand()
	      ->select('*')
	      ->from('inv_item')
	      ->where(
	        'idstatus = 3 AND
	        idlokasi = :idlokasi', 
	        array(
	          ':idlokasi' => $idlokasi
          )
        );
      $daftar_detil = $command->queryAll();
      
      foreach($daftar_detil as $detil)
      {
        Yii::app()->db->createCommand()
          ->insert(
            'inv_stock_opname_detil',
            array(
              'id_stock_opname' => $idstockopname,
              'iditem_system' => $detil['id']
            )
          );
      }
      
      echo CJSON::encode(array('idstockopname' => $idstockopname));
	  }
	  
	  /*
      actionStockOpnameEdit
      
      Deskripsi
      Fungsi untuk membuka interface untuk mengupdate daftar pembacaan barcode.
      
      Parameter
      idstockopname
        Ineger
        
      Return
      Mengembalikan interface untuk mengedit daftar stock opname
    */
	  public function actionStockOpnameEdit()
	  {
	    $idstockopname = Yii::app()->request->getParam('idstockopname');
	    
	    //ambil info stock opname
	    $Criteria = new CDbCriteria();
	    $Criteria->condition = 'id = :idstockopname';
	    $Criteria->params = array(
	      ':idstockopname' => $idstockopname
      );
	    
	    $stock_opname = inv_stock_opname::model()->find($Criteria);
	    
	    $html = $this->renderPartial(
	      'vfrm_stockopname_edit',
	      array(
	        'stockopname' => $stock_opname
        ),
	      true
      );
      
      echo CJSON::encode(array('html' => $html));
	  }
	  
	  /*
      actionStockOpnameHapus
      
      Deskripsi
      Fungsi untuk menghapus stock opname.
      
      Parameter
      idstockopname
        Integer
        
      Return
      Mengembalikan status 'ok' atau 'not ok'
    */
	  public function actionStockOpnameHapus()
	  {
	    $idstockopname = Yii::app()->request->getParam('idstockopname');
	    
	    Yii::app()->db->createCommand()
	      ->update(
	        'inv_stock_opname',
	        array(
	          'is_del' => 1,
	          'delete_time' => date('Y-m-j H:i:s'),
	          'delete_by' => Yii::app()->request->cookies['userid_actor']->value
          ),
	        'id = :idstockopname',
	        array(':idstockopname' => $idstockopname)
        );
        
      echo CJSON::encode(array('status' => 'ok'));
	  }
	  
	  /*
      actionStockOpnameBaca
      
      Deskripsi
      Fungsi untuk menerima pembacaan barcode untuk dicatat ke dalam tabel inv_stock_opname_detail
      
      Parameter
      barcode
        String
        
      Return
      Mengembalikan status 'ok' atau 'not ok'
    */
	  public function actionStockOpnameBaca()
	  {
	    $barcode = Yii::app()->request->getParam('barcode');
	    $idstockopname = Yii::app()->request->getParam('idstockopname');
	    
	    //pastikan barcode valid
	    $command = Yii::app()->db->createCommand()
	      ->select('*')
	      ->from('inv_item')
	      ->where(
	        'barcode = :barcode', 
	        array(
	          ':barcode' => $barcode
          )
        );
	    $barcode = $command->queryRow();
	    
	    if($barcode != false)
	    {
	      $iditem = FHelper::GetIdItemByBarcode($barcode['barcode']);
	      
	      //periksa apakah barcode sudah pernah dibacakan sebelumnya
	      $command = Yii::app()->db->createCommand()
	        ->select('*')
	        ->from('inv_stock_opname_detil')
	        ->where(
	          'id_stock_opname = :idstockopname AND
	          iditem_manual = :iditem',
	          array(
	            ':iditem' => $iditem,
	            ':idstockopname' => $idstockopname
            )
          );
        $test = $command->queryRow();
        
        if($test == false)
        {
          $status = 'ok';
          $pesan = '';
          
          //masukkan barcode ke tabel inv_stock_opname_detil
          $command = Yii::app()->db->createCommand()
            ->select('*')
            ->from('inv_stock_opname_detil')
            ->where(
              'id_stock_opname = :idstockopname AND
              iditem_system = :iditem', 
              array(
                ':iditem' => $iditem,
                ':idstockopname' => $idstockopname
              )
            );
          $detil = $command->queryRow();
          
          if($detil != false)
          {
            //pasangkan barcode terbaca dengan barcode dari system
            Yii::app()->db->createCommand()
              ->update(
                'inv_stock_opname_detil',
                array(
                  'iditem_manual' => $iditem,
                  'scan_time' => date('Y-m-j H:i:s'),
                  'scan_by' => Yii::app()->request->cookies['userid_actor']->value
                ),
                'id_stock_opname = :idstockopname AND
                iditem_system = :iditem',
                array(
                  ':iditem' => $iditem, 
                  ':idstockopname' => $idstockopname
                )
              );
          }
          else
          {
            //masukkan barcode ke tabel inv_stock_opname_detil sebagai iditem_manual
            Yii::app()->db->createCommand()
              ->insert(
                'inv_stock_opname_detil',
                array(
                  'iditem_manual' => $iditem,
                  'id_stock_opname' => $idstockopname,
                  'scan_time' => date('Y-m-j H:i:s'),
                  'scan_by' => Yii::app()->request->cookies['userid_actor']->value
                )
              );
          }
          
          //mengupdate informasi waktu peng-update-an informasi stock opname
          Yii::app()->db->createCommand()
            ->update(
              'inv_stock_opname',
              array(
                'status' => 2,
                'update_time' => date('Y-m-j H:i:s'),
                'update_by' => Yii::app()->request->cookies['userid_actor']->value
              ),
              'id = :id_stock_opname',
              array(
                ':id_stock_opname' => $idstockopname
              )
            );
        }
        else
        {
          $status = 'not ok';
          $pesan = 'Barcode sudah pernah di-scan';
        }
	      
          
	    }
	    else
	    {
	      $status = 'not ok';
	      $pesan = 'Barcode tidak dikenal';
	    }
	    
	    echo CJSON::encode(array('status' => $status, 'pesan' => $pesan));
	  }
	  
	  /*
      actionStockOpnameCetak
      
      Deskripsi
      Fungsi untuk mencetak daftar pembacaan barcode pada suatu stock opname
      
      Parameter
      idstockopname
        Ineger
        
      Return
      Mengembalikan hasil pencetakan dalam bentuk file xls.
    */
	  public function actionStockOpnameCetak()
	  {
	    ini_set('max_execution_time', 3000);
	    
	    $xlsSO = new PHPExcel();
	    $wsSO = $xlsSO->getActiveSheet();
	    $wsSO->setTitle('Stock Opname');

      $idtoko = Yii::app()->request->cookies['idlokasi']->value;
      $idstockopname = Yii::app()->request->getParam('idstockopname');
      $idkategori = Yii::app()->request->getParam('idkategori');	    
	    $baris = 1;
	    $kolom = 0;
	    
	    $wsSO->GetCellByColumnAndRow($kolom, $baris)
        ->setValue('Stock Opname'); $baris++; $baris++;
        
      $wsSO->GetCellByColumnAndRow($kolom, $baris)
        ->setValue('Toko : ' . FHelper::GetLocationName($idtoko, true)); $baris++;
        
      $wsSO->GetCellByColumnAndRow($kolom, $baris)
        ->setValue('Waktu : ' . date('j M Y, H:i:s')); $baris++; $baris++;
        
      $wsSO->GetCellByColumnAndRow($kolom, $baris)
        ->setValue('Kategori : ' . FHelper::GetTipeProdukText($idkategori)); $baris++; $baris++;
        
      $command = Yii::app()->db->createCommand()
        ->select('sistem.idinventory idproduk_system, 
                  sistem.barcode barcode_system, 
                  manual.idinventory idproduk_manual, 
                  manual.barcode barcode_manual')
        ->from('inv_stock_opname_detil so')
        ->leftJoin('inv_item sistem', 'sistem.id = so.iditem_system')
        ->leftJoin('inv_item manual', 'manual.id = so.iditem_manual')
        ->order('sistem.idinventory, sistem.barcode asc')
        ->where(
            'so.id_stock_opname = :idstockopname', 
            array(
              ':idstockopname' => $idstockopname
            )
          );
      $daftar_so = $command->queryAll();
      
      $baris++; $baris++;
      
      $nama_barang_old = "";
      $ukuran_old = '';
      foreach($daftar_so as $so)
      {
        $kolom = 0;
        
        if($so['barcode_system'] != '')
        {
          $nama_barang = FHelper::GetProdukName($so['idproduk_system']);
          $ukuran = FHelper::GetProdukUkuran($so['idproduk_system']);
          $idproduk = $so['idproduk_system'];
        }
        else
        {
          $nama_barang = FHelper::GetProdukName($so['idproduk_manual']);
          $ukuran = FHelper::GetProdukUkuran($so['idproduk_manual']);
          $idproduk = $so['idproduk_manual'];
        }
        
        if($nama_barang_old != $nama_barang &&
           $ukuran_old != $ukuran)
        {
          
          $baris++; $baris++;
          
          $wsSO->GetCellByColumnAndRow($kolom, $baris)
            ->setValue("{$so['idproduk_system']} - {$nama_barang}"); $kolom++;
            
          $wsSO->GetCellByColumnAndRow($kolom, $baris)
            ->setValue($ukuran); $kolom++;
            
          $kolom = 0;
          $baris++;
          $wsSO->GetCellByColumnAndRow($kolom, $baris)
            ->setValue('Sistem'); $kolom++;
            
          $wsSO->GetCellByColumnAndRow($kolom, $baris)
            ->setValue('Manual'); $kolom++;
          
          $kolom = 0;
          $baris++;
          
          $nama_barang_old = $nama_barang;
          $ukuran_old = $ukuran;
        }
        
        $wsSO->GetCellByColumnAndRow($kolom, $baris)
          ->setValueExplicit($so['barcode_system'], PHPExcel_Cell_DataType::TYPE_STRING); $kolom++;
        $wsSO->GetCellByColumnAndRow($kolom, $baris)
          ->setValueExplicit($so['barcode_manual'], PHPExcel_Cell_DataType::TYPE_STRING); $kolom++;
          
        /*
        $wsSO->GetCellByColumnAndRow($kolom, $baris)
          ->setValue($idproduk); $kolom++;
        */
        
        $baris++;
        
      } //daftar_so
      
      //simpan file
      $writer = new PHPExcel_Writer_Excel2007($xlsSO);
      $writer->save('stock opname.xlsx');
      
      $html = CHtml::link('file stock opname', 'stock opname.xlsx');
      
      echo CJSON::encode(array('html' => $html));
	  }
	  
	  /*
      actionStockOpnameLock
      
      Deskripsi
      Fungsi untuk mengunci stock opname sehingga tidak bisa diedit lagi.
      Selain itu, fungsi ini juga mengupdate atau membuat record kartu stock.
      Fungsi akan menghitung stock opname sebagai jumlah barang per id_item
      
      Parameter
      idstockopname
        Ineger
        
      Return
      Mengembalikan hasil pencetakan dalam bentuk file xls.
    */
	  public function actionStockOpnameLock()
	  {
	    $idstockopname = Yii::app()->request->getParam('idstockopname');
	    
	    //hitung stock opname per id_item
      //hitung jumlah item dimana iditem_system = iditem_manual
      $command = Yii::app()->db->createCommand()
        ->select('iditem_system, count(iditem_system) as jumlah')
        ->from('inv_stock_opname_detil')
        ->where(
          'id_stock_opname = :idstockopname AND
          iditem_system = iditem_manual AND
          iditem_system IS NOT NULL',
          array(
            ':idstockopname' => $idstockopname
          )
        );
      $command->distinct = true;
      $command->group = "iditem_system";
      $daftar_item_system = $command->queryAll();
      
      //hitung jumlah item ...
      $command = Yii::app()->db->createCommand()
        ->select('inventory.id, count(detil.iditem_manual) as jumlah')
        ->from('inv_stock_opname_detil detil')
        ->join('inv_item item', 'item.id = detil.iditem_manual')
        ->join('inv_inventory inventory', 'inventory.id = item.idinventory')
        ->where(
          'detil.id_stock_opname = :idstockopname 
          AND
          (
            detil.iditem_system = detil.iditem_manual 
            OR
            (
              detil.iditem_system IS NULL 
              AND 
              detil.iditem_manual IS NOT NULL
            )
          )',
          array(
            ':idstockopname' => $idstockopname
          )
        );
      $command->distinct = true;
      $command->group = "id";
      $daftar_inventory = $command->queryAll();
      
      //update / insert record kartu stock
      $idlokasi = Yii::app()->request->cookies['idlokasi']->value;
      $tanggal = date("Y-m-j 00:00:00");
      if( count($daftar_inventory) > 0)
      {
        foreach($daftar_inventory as $data)
        {
          //periksa apakah update atau insert record kartu stock
          $command = Yii::app()->db->createCommand()
            ->select('*')
            ->from('kartu_stock')
            ->where(
              "idlokasi = :idlokasi AND
              idinventory = :idinventory AND
              tanggal = :tanggal",
              array(
                ':idlokasi' => $idlokasi,
                ':idinventory' => $data['id'],
                ':tanggal' => $tanggal,
              )
            );
            
          $hasil = $command->queryRow();
          
          if($hasil == false)
          {
            //insert record kartu stock
            Yii::app()->db->createCommand()
              ->insert(
                'kartu_stock',
                array(
                  'idlokasi' => $idlokasi,
                  'idinventory' => $data['id'],
                  'tanggal' => $tanggal,
                  'stock_awal' => 0,
                  'total_in' => 0,
                  'total_out' => 0,
                  'stock_akhir' => 0,
                  'stock_opname' => $data['jumlah'],
                  'stock_kartu_time' => date('Y-m-j H:i:s'),
                  'opname_time' => date('Y-m-j H:i:s')
                )
              );
          }
          else
          {
            //update record kartu stock
            Yii::app()->db->createCommand()
              ->update(
                'kartu_stock',
                array(
                  'stock_opname' => $data['jumlah'],
                  'opname_time' => date('Y-m-j H:i:s')
                ),
                'idlokasi = :idlokasi AND
                idinventory = :idinventory AND
                tanggal = :tanggal',
                array(
                  'idlokasi' => $idlokasi,
                  'idinventory' => $data['id'],
                  'tanggal' => $tanggal
                )
              );
          }// insert atau update
        }//loop daftar iditem_system
      }
        
      
      //update status record inv_stock_opname
	    Yii::app()->db->createCommand()
	      ->update(
	        'inv_stock_opname',
	        array(
	          'status' => 3
          ),
	        'id = :idstockopname',
	        array(
	          ':idstockopname' => $idstockopname
          )
        );
        
      echo CJSON::encode( array('status' => 'ok') );
	  }
	  
	  /*
	    actionStockOpnameValidateLogin()
	    
	    Deskripsi
	    Fungsi untuk melakukan validasi login
	  */
	  public function actionStockOpnameValidateLoginPostSO()
	  {
	    $username = Yii::app()->request->getParam('username');
	    $password = Yii::app()->request->getParam('password');
	    
	    //cek pasangan user-password terhadap tabel wp_user
	    $command = Yii::app()->db->createCommand()
	      ->select('count(id) as jumlah')
	      ->from('sys_user')
	      ->where(
	        "username = :username AND
	        password = :password AND
	        idgroup in (1, 2, 3, 4, 9, 12)", //admin, admin inventory, manager area, owner, audit, it & promo
	        array(
	          ':username' => $username,
	          ':password' => sha1("123{$password}123")
          )
        );
        
      $hasil = $command->queryRow();
      
      $status = ($hasil['jumlah'] == 1 ? 'ok' : 'not ok');
      
      echo CJSON::encode( array('status' => $status) );
	  }
	  
	  /*
      actionStockOpnameRefreshDetil
      
      Deskripsi
      Fungsi untuk mengambil detil daftar barcode suatu stock opname
      
      Parameter
      idstockopname
        Ineger
        
      Return
      Mengembalikan daftar detil barcode suatu stock opname
    */
	  public function actionStockOpnameRefreshDetil()
	  {
	    $idstockopname = Yii::app()->request->getParam('idstockopname');
	    $idkategori = Yii::app()->request->getParam('idkategori');
	    
	    if( is_null($idkategori) || isset($idkategori) == false)
	    {
	      $idkategori = 1;
	    }
	    
	    $maketable = new MakeTable();
	    
	    $rowsperpage = Yii::app()->request->getParam('rowsperpage');
      $rowsperpage = ( isset($rowsperpage) == false ? 20 : $rowsperpage );
      $rowsperpage = ($rowsperpage > 0 ? $rowsperpage : 20);
      
      $sort_by = Yii::app()->request->getParam('sortby');
      
      $pageno = Yii::app()->request->getParam('pageno');
      $pageno = ( isset($pageno) == false ? 1 : $pageno );
      
      $search = Yii::app()->request->getParam('search');
      //$search = ( $search == "" ? "" : $search );
	    
      
      // hitung rows dan pages count - begin
      
        Yii::log('StockOpname::RefreshDetil : hitung rows - begin', 'info');
      
        if($search != '')
        {
          //with search
          
          //count #1
          $command = Yii::app()->db->createCommand()
            ->select('count(so.id_stock_opname) as jumlah')
            ->from('inv_stock_opname_detil so')
            ->join('inv_item item1', 'item1.id = so.iditem_system')
            ->join('inv_inventory inventory', 'inventory.id = item1.idinventory')
            ->where(
              " id_stock_opname = :idstockopname AND
              item1.barcode like :barcode1 AND
              inventory.idkategori = :idkategori", 
              array(
                ':idstockopname' => $idstockopname,
                ':barcode1' => "%$search%",
                ':idkategori' => $idkategori
              )
          );
          $hasil = $command->queryRow();
          $rows = $hasil['jumlah'];
          
          //count #2
          $command = Yii::app()->db->createCommand()
            ->select('count(so.id_stock_opname) as jumlah')
            ->from('inv_stock_opname_detil so')
            ->join('inv_item item1', 'item1.id = so.iditem_manual')
            ->join('inv_inventory inventory', 'inventory.id = item1.idinventory')
            ->where(
              " id_stock_opname = :idstockopname AND
              item1.barcode like :barcode1 AND
              inventory.idkategori = :idkategori", 
              array(
                ':idstockopname' => $idstockopname,
                ':barcode1' => "%$search%",
                ':idkategori' => $idkategori
              )
          );
          $hasil = $command->queryRow();
          $rows += $hasil['jumlah'];
        }
        else
        {
          //without search
          
          //count #1
          $command = Yii::app()->db->createCommand()
            ->select('count(so.id_stock_opname) as jumlah')
            ->from('inv_stock_opname_detil so')
            ->join('inv_item item1', 'item1.id = so.iditem_system')
            ->join('inv_inventory inventory', 'inventory.id = item1.idinventory')
            ->where(
              'id_stock_opname = :idstockopname AND
              inventory.idkategori = :idkategori', 
              array(
                ':idstockopname' => $idstockopname,
                ':idkategori' => $idkategori
              )
            );
          $hasil = $command->queryRow();
          $rows = $hasil['jumlah'];
          
          //count #2
          $command = Yii::app()->db->createCommand()
            ->select('count(so.id_stock_opname) as jumlah')
            ->from('inv_stock_opname_detil so')
            ->join('inv_item item1', 'item1.id = so.iditem_manual')
            ->join('inv_inventory inventory', 'inventory.id = item1.idinventory')
            ->where(
              'id_stock_opname = :idstockopname AND
              inventory.idkategori = :idkategori', 
              array(
                ':idstockopname' => $idstockopname,
                ':idkategori' => $idkategori
              )
            );
          $hasil = $command->queryRow();
          $rows += $hasil['jumlah'];
        }
        
          
        
        
        Yii::log("StockOpname::RefreshDetil : rows = $rows", 'info');
        
        Yii::log('StockOpname::RefreshDetil : hitung rows - end', 'info');
        
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
        
        //koreksi pageno jika pageno > maketable->pages
        if($pageno > $maketable->pages)
        {
          $pageno = $maketable->pages - 1;
        }
        
      // hitung rows dan pages count - end
      
      //ambil records untuk pageno saat ini - begin
      
        Yii::log('StockOpname::RefreshDetil : get rows by pageno - begin', 'info');
      
        if($search != '')
        {
          //collect #1
          $command = Yii::app()->db->createCommand()
            ->select('so.*')
            ->from('inv_stock_opname_detil so')
            ->join('inv_item item1', 'item1.id = so.iditem_system')
            ->join('inv_inventory inventory', 'inventory.id = item1.idinventory')
            ->where(
              " id_stock_opname = :idstockopname AND
              item1.barcode like :barcode1 AND
              inventory.idkategori = :idkategori", 
              array(
                ':idstockopname' => $idstockopname,
                ':barcode1' => "%$search%",
                ':idkategori' => $idkategori
              )
          );
          
          $command->order = "item1.barcode";
          $command->limit = $rowsperpage;
          $command->offset = ($pageno - 1) * $rowsperpage;
          
          $temp_rows = $command->queryAll();
          foreach($temp_rows as $temp)
          {
            $daftar_detil[] = $temp;
          }
          
          //collect #2
          $command = Yii::app()->db->createCommand()
            ->select('so.*')
            ->from('inv_stock_opname_detil so')
            ->join('inv_item item1', 'item1.id = so.iditem_manual')
            ->join('inv_inventory inventory', 'inventory.id = item1.idinventory')
            ->where(
              " id_stock_opname = :idstockopname AND
              item1.barcode like :barcode1 AND
              inventory.idkategori = :idkategori", 
              array(
                ':idstockopname' => $idstockopname,
                ':barcode1' => "%$search%",
                ':idkategori' => $idkategori
              )
          );
          
          $command->order = "item1.barcode";
          $command->limit = $rowsperpage;
          $command->offset = ($pageno - 1) * $rowsperpage;
          
          $temp_rows = $command->queryAll();
          foreach($temp_rows as $temp)
          {
            $daftar_detil[] = $temp;
          }
        }
        else
        {
          //collect #1
          $command = Yii::app()->db->createCommand()
            ->select('so.*')
            ->from('inv_stock_opname_detil so')
            ->join('inv_item item1', 'item1.id = so.iditem_system')
            ->join('inv_inventory inventory', 'inventory.id = item1.idinventory')
            ->where(
              'id_stock_opname = :idstockopname AND
              inventory.idkategori = :idkategori', 
              array(
                ':idstockopname' => $idstockopname,
                ':idkategori' => $idkategori
              )
            );
            
          $command->order = "item1.barcode";
          $command->limit = $rowsperpage;
          $command->offset = ($pageno - 1) * $rowsperpage;
          
          $temp_rows = $command->queryAll();
          foreach($temp_rows as $temp)
          {
            $daftar_detil[] = $temp;
          }
          
          //collect #2
          $command = Yii::app()->db->createCommand()
            ->select('so.*')
            ->from('inv_stock_opname_detil so')
            ->join('inv_item item1', 'item1.id = so.iditem_manual')
            ->join('inv_inventory inventory', 'inventory.id = item1.idinventory')
            ->where(
              'id_stock_opname = :idstockopname AND
              inventory.idkategori = :idkategori', 
              array(
                ':idstockopname' => $idstockopname,
                ':idkategori' => $idkategori
              )
            );
            
          $command->order = "item1.barcode";
          $command->limit = $rowsperpage;
          $command->offset = ($pageno - 1) * $rowsperpage;
          
          $temp_rows = $command->queryAll();
          foreach($temp_rows as $temp)
          {
            $daftar_detil[] = $temp;
          }
        }
        
        
        
        
        Yii::log('StockOpname::RefreshDetil : get rows by pageno - end', 'info');
        
      //ambil records untuk pageno saat ini - end
      
      $html = $this->renderPartial(
        'v_list_detil_stock_opname',
        array(
          'daftar_detil' => $daftar_detil,
          'idkategori' => $idkategori
        ),
        true
      );
      
      $array_rows_per_page[10] = 10;
      $array_rows_per_page[20] = 20;
      $array_rows_per_page[40] = 40;
      $array_rows_per_page[50] = 50;
      $array_rows_per_page[100] = 100;
      $array_rows_per_page[200] = 200;
      
      $array_sort_by[1] = 'barcode';
      
      
      $maketable->list_type = "StockOpname";
      $maketable->pageno = $pageno;
      $maketable->table_content = $html;
      $maketable->array_rows_per_page = $array_rows_per_page;
      $maketable->array_sort_by = $array_sort_by;
      $maketable->array_goto_page = $array_goto_page;
      $maketable->rows_per_page = $rowsperpage;
      $maketable->sort_by = $sort_by;
      $maketable->sort_direction = 0;
      $maketable->search = $search;
      $maketable->action_name = "StockOpname_RefreshDetil";
      
      $html = $maketable->Render($maketable);
      
      echo CJSON::encode(array('html' => $html));
	  }
	  
	  
	
	
	/* inventory - stock opname - end */
	
	
	
	
	/* inventory - cari barcode - begin */
	
	  public function actionCariBarcode()
	  {
	    $iduser = Yii::app()->request->cookies['userid_actor']->value;
	    $idgrup = FHelper::GetGroupId($iduser);
	    $idmenu = 65;
	    
	    if( FHelper::AllowMenu($idmenu, $idgrup, 'read') )
	    {
	      $this->layout = 'layout-baru';
	      $html = $this->renderPartial(
	        'vfrm_caribarcode',
	        array(),
	        true
        );
        
        $this->render(
          'index_general',
          array('TheContent' => $html)
        );
	    }
	    else
	    {
	      $this->redirect('?r=index/showinvalidaccess');
	    }
	  }
	  
	  public function actionAmbilInfoBarcode()
	  {
	    $iduser = Yii::app()->request->cookies['userid_actor']->value;
	    $idgrup = FHelper::GetGroupId($iduser);
	    $idmenu = 65;
	    
	    if( FHelper::AllowMenu($idmenu, $idgrup, 'read') )
	    {
	      $barcode = Yii::app()->request->getParam('barcode');
	      
	      $command = Yii::app()->db->createCommand()
	        ->select('lokasi.name lokasi, inventory.nama nama, inventory.id, item.barcode')
	        ->from('inv_item item')
	        ->join('inv_inventory inventory', 'item.idinventory = inventory.id')
	        ->join('mtr_branch lokasi', 'lokasi.branch_id = item.idlokasi')
	        ->where('item.barcode = :barcode', array(':barcode' => $barcode));
	        
        $item = $command->queryRow();
        
        if( $item != false )
        {
          $ukuran = FHelper::GetProdukUkuran($item['id']);
          
          $html = $this->renderPartial(
            'v_info_barcode',
            array(
              'item' => $item,
              'ukuran' => $ukuran
            ),
            true
          );
          
          $status = 'ok';
        }
        else
        {
          $html = $this->renderPartial(
            'v_info_barcode',
            array(
              'item' => null
            ),
            true
          );
          
          $status = 'not ok';
        }
        
        echo CJSON::encode(array('status' => $status, 'html' => $html));
	    }
	    else
	    {
	      $this->redirect('?r=index/showinvalidaccess');
	    }
	  }
	
	/* inventory - cari barcode - end */
	
	
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