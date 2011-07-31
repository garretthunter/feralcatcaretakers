<?php
	$pix = '<img alt="" src="modules/'.__PNADDRESSBOOK__.'/pnimages/pix.gif" width="1" height="1">';
	$custom_tab = pnModGetVar(__PNADDRESSBOOK__,'custom_tab');
	$priv = pnModGetVar(__PNADDRESSBOOK__,'globalprotect');
	if ((empty($custom_tab)) || ($custom_tab == '')) { $addrow = 0; }
	else { $addrow = 2; }
	
	$output->Text('<table width="50%" align="center" border="0" cellpadding="0" cellspacing="0">');
	//row 1 pix
	$output->Text('<tr>');
	$output->Text('<td width="1" bgcolor="'.$bc2.'">'.$pix.'</td>');
	$output->Text('<td bgcolor="'.$bc2.'">'.$pix.'</td>');
	$output->Text('<td width="1" bgcolor="'.$bc2.'">'.$pix.'</td>');
	$output->Text('<td bgcolor="'.$bc2.'">'.$pix.'</td>');
	$output->Text('<td width="1">'.$pix.'</td>');
	$output->Text('<td bgcolor="'.$bc2.'">'.$pix.'</td>');
	$output->Text('<td width="1">'.$pix.'</td>');
	$output->Text('<td bgcolor="'.$bc2.'">'.$pix.'</td>');
	$output->Text('<td width="1">'.$pix.'</td>');
	if ($addrow > 0) {
		$output->Text('<td bgcolor="'.$bc2.'">'.$pix.'</td>');
		$output->Text('<td width="1">'.$pix.'</td>');
	}
	$output->Text('<td width="20%">'.$pix.'</td>');
	$output->Text('<td width="1">'.$pix.'</td>');
	$output->Text('</tr>');
	// row 2 register labels
	$output->Text('<tr>');
	$output->Text('<td width="1"  height="20" bgcolor="'.$bc2.'">'.$pix.'</td>');
	$output->Text('<td bgcolor="'.$bc1.'" align="center" valign="middle">&nbsp;&nbsp;<b>'.pnVarPrepHTMLDisplay(_NAME).'</b>&nbsp;&nbsp;</td>');
	$output->Text('<td width="1" bgcolor="'.$bc2.'">'.$pix.'</td>');
	$output->Text('<td align="center" valign="middle">&nbsp;&nbsp;<b><a href="javascript:document.addcontact.submit();" onclick="javascript:document.addcontact.action.value=1;">'.pnVarPrepHTMLDisplay(_pnAB_ADDRESS).'</a></b>&nbsp;&nbsp;</td>');
	$output->Text('<td width="1" bgcolor="'.$bc2.'">'.$pix.'</td>');
	$output->Text('<td align="center" valign="middle">&nbsp;&nbsp;<b><a href="javascript:document.addcontact.submit();" onclick="javascript:document.addcontact.action.value=2;">'.pnVarPrepHTMLDisplay(_pnAB_CONTACT).'</a></b>&nbsp;&nbsp;</td>');
	$output->Text('<td width="1" bgcolor="'.$bc2.'">'.$pix.'</td>');
	if ($addrow > 0) {
		$output->Text('<td align="center" valign="middle">&nbsp;&nbsp;<b><a href="javascript:document.addcontact.submit();" onclick="javascript:document.addcontact.action.value=3;">'.pnVarPrepHTMLDisplay(pnModGetVar(__PNADDRESSBOOK__,'custom_tab')).'</a></b>&nbsp;&nbsp;</td>');
		$output->Text('<td width="1" bgcolor="'.$bc2.'">'.$pix.'</td>');
	}
	$output->Text('<td align="center" valign="middle">&nbsp;&nbsp;<b><a href="javascript:document.addcontact.submit();" onclick="javascript:document.addcontact.action.value=4;">'.pnVarPrepHTMLDisplay(_pnAB_NOTETAB).'</a></b>&nbsp;&nbsp;</td>');
	$output->Text('<td width="1" bgcolor="'.$bc2.'">'.$pix.'</td>');
	$output->Text('<td>'.$pix.'</td>');
	$output->Text('<td width="1">'.$pix.'</td>');
	$output->Text('</tr>');
	// row 3 pix
	$output->Text('<tr>');
	$output->Text('<td width="1" bgcolor="'.$bc2.'">'.$pix.'</td>');
	$output->Text('<td bgcolor="'.$bc1.'">'.$pix.'</td>');
	$output->Text('<td width="1" bgcolor="'.$bc2.'">'.$pix.'</td>');
	$output->Text('<td bgcolor="'.$bc2.'">'.$pix.'</td>');
	$output->Text('<td width="1" bgcolor="'.$bc2.'">'.$pix.'</td>');
	$output->Text('<td bgcolor="'.$bc2.'">'.$pix.'</td>');
	$output->Text('<td width="1" bgcolor="'.$bc2.'">'.$pix.'</td>');
	if ($addrow > 0) {
		$output->Text('<td bgcolor="'.$bc2.'">'.$pix.'</td>');
		$output->Text('<td width="1" bgcolor="'.$bc2.'">'.$pix.'</td>');
	}	
	$output->Text('<td bgcolor="'.$bc2.'">'.$pix.'</td>');
	$output->Text('<td width="1" bgcolor="'.$bc2.'">'.$pix.'</td>');
	$output->Text('<td bgcolor="'.$bc2.'">'.$pix.'</td>');
	$output->Text('<td width="1" bgcolor="'.$bc2.'">'.$pix.'</td>');
	$output->Text('</tr>');
	// row 4 name information
	$output->Text('<tr>');
	$output->Text('<td width="1" bgcolor="'.$bc2.'">'.$pix.'</td>');
	$output->Text('<td colspan="'.($addrow + 9).'" bgcolor="'.$bc1.'">');
	// start form area
	$output->TableStart('','','0','100','5','0');
	// Prefix
	if (pnModGetVar(__PNADDRESSBOOK__,'use_prefix')==1) {
		$pres = pnModAPIFunc(__PNADDRESSBOOK__,'user','getFormPrefixes');
		if(!is_array($pres)) {
	        $output->Text($pres);
	        return $output->GetOutput();
	    }
		$formdata = array();
		$formdata[] = array('id'=>'0',
							 'name'=>_pnAB_NOPREFIX); 
		
		foreach($pres as $pre) {
			$formdata[] = array('id'=>$pre['nr'],
							 	 'name'=>$pre['name']); 
		}
		$output->TableRowStart();
		$output->TableColStart(1,'left','middle');
		$output->Text(pnVarPrepHTMLDisplay(_pnAB_PREFIX).':');
		$output->TableColEnd();
		$output->TableColStart(1,'left','middle');
		$output->FormSelectMultiple('prfx',$formdata, 0, 1, $prfx, '');
		$output->TableColEnd();
		$output->TableRowEnd();
	}
	else {
		$output->FormHidden('prfx',0);
	}
	$output->TableRowStart();
	$output->TableColStart(1,'left','middle');
	$output->Text(pnVarPrepHTMLDisplay(_pnAB_LASTNAME).':');
	$output->TableColEnd();
	$output->TableColStart(1,'left','middle');
	$output->FormText('lname',$lname,60,100);
	$output->TableColEnd();
	$output->TableRowEnd();
	$output->TableRowStart();
	$output->TableColStart(1,'left','middle');
	$output->Text(pnVarPrepHTMLDisplay(_pnAB_FIRSTNAME).':');
	$output->TableColEnd();
	$output->TableColStart(1,'left','middle');
	$output->FormText('fname',$fname,60,60);
	$output->TableColEnd();
	$output->TableRowEnd();
	$output->TableRowStart();
	$output->TableColStart(1,'left','middle');
	$output->Text(pnVarPrepHTMLDisplay(_pnAB_TITLE).':');
	$output->TableColEnd();
	$output->TableColStart(1,'left','middle');
	$output->FormText('title',$title,60,100);
	$output->TableColEnd();
	$output->TableRowEnd();
	$output->TableRowStart();
	$output->TableColStart(1,'left','middle');
	$output->Text(pnVarPrepHTMLDisplay(_pnAB_COMPANY).':');
	$output->TableColEnd();
	$output->TableColStart(1,'left','middle');
	$output->FormText('company',$company,60,100);
	$output->TableColEnd();
	$output->TableRowEnd();
	$output->TableRowStart();
	$output->TableColStart(1,'left','middle');
	//$output->Text((_pnAB_COMPANY).':');
	$output->TableColEnd();
	$output->TableColStart(1,'left','middle');
	//$output->FormText('company',$company,60,100);
	$companies = pnModAPIFunc(__PNADDRESSBOOK__,'user','getCompanies');
	if(!is_array($companies)) {
        $output->Text($companies);
        return $output->GetOutput();
    }
	$args = array('fieldname'=>'comp_lookup',
						'data'=>$companies,
						'multiple'=>'0',
						'size'=>'1',
						'selected'=>'0',
						'accesskey'=>'',
						'lookup'=>'1');
	$output->Text(pnModAPIFunc(__PNADDRESSBOOK__,'user','FormSelect',$args));
	
	$output->TableColEnd();
	$output->TableRowEnd();
	// Image (only for admins)
	if ((pnSecAuthAction(0, 'pnAddressBook::', '::', ACCESS_ADMIN)) && (pnModGetVar(__PNADDRESSBOOK__,'use_img')==1)){
		$dir = 'modules/'.__PNADDRESSBOOK__.'/img';
	 	$handle = @opendir($dir);
	    $formdata_img[] = array('id'=>'', 'name'=>_pnAB_NOIMAGE); 
		while ($file = @readdir ($handle)) {
			if (eregi("^\.{1,2}$",$file)) {
				continue;
			}
			else {
				$formdata_img[] = array('id'=>$file, 'name'=>$file); 
			}
		}
		@closedir($handle); 
		$output->TableRowStart();
		$output->TableColStart(1,'left','middle');
		$output->Text(pnVarPrepHTMLDisplay(_pnAB_IMAGE).':');
		$output->TableColEnd();
		$output->TableColStart(1,'left','middle');
		$output->FormSelectMultiple('img',$formdata_img, 0, 1,$img,'');
		$output->TableColEnd();
		$output->TableRowEnd();
	}
	else {
		$output->FormHidden('img','NULL');
	}
	// end image
	$output->TableEnd();
	$output->Text('</td>');
	$output->Text('<td width="1" bgcolor="'.$bc2.'">'.$pix.'</td>');
	$output->Text('</tr>');
	// end form area
	// row 5 pix	
	$output->Text('<tr>');
	$output->Text('<td colspan="'.($addrow + 11).'" width="1" bgcolor="'.$bc2.'">'.$pix.'</td>');
	$output->Text('</tr>');
	// row 6 category/private
	$output->Text('<tr>');
	$output->Text('<td colspan="'.($addrow + 11).'" align="left" valign="top">');
	$cats = pnModAPIFunc(__PNADDRESSBOOK__,'user','getFormCategories');
	if(!is_array($cats)) {
        $output->Text($cats);
        return $output->GetOutput();
    }
	$formdata = array();
	$formdata[] = array('id'=>'0',
						 'name'=>_pnAB_UNFILED); 
	
	foreach($cats as $cat) {
		$formdata[] = array('id'=>$cat['nr'],
						 	 'name'=>$cat['name']); 
	}
	$output->TableStart('','','0','100%','5','0');
	$output->TableRowStart();
	$output->TableColStart(1,'left','middle');
	$output->Text(pnVarPrepForDisplay(_pnAB_CATEGORY).': ');
	$output->TableColEnd();
	$output->TableColStart(1,'left','middle');
	$output->FormSelectMultiple('cat_id',$formdata, 0, 1, $cat_id, '');
	$output->TableColEnd();
	$output->Text('<td width="100%"></td>');

	$output->TableColStart(1,'right','middle');
	if ($priv) {$output->Text(' ');}
	else {$output->Text(pnVarPrepForDisplay(_pnAB_PRIVATE).': ');}
	$output->TableColEnd();
	$output->TableColStart(1,'left','middle');
	if ($priv) {$output->FormHidden('private',0);}
	else { 
		(isset($private)) ? $output->FormCheckbox('private',$private) : $output->FormCheckbox('private',0); 
	}
	$output->TableColEnd();
	$output->TableRowEnd();
	$output->TableEnd();
	$output->Text('</td></tr>');
	$output->Text('</table>');
	$output->Text(pnAddressBook_themetable('end'));
	
	// Hidden fields
	// custom fields
	if ($addrow > 0) {
		$cus_fields = pnModAPIFunc(__PNADDRESSBOOK__,'user','customFieldInformation',array('id'=>$id));
		$temp = pnVarCleanFromInput('custom_1');
		if (!$temp) {
			foreach($cus_fields as $cus) {
				if ((!strstr($cus['type'],'tinyint')) && (!strstr($cus['type'],'smallint'))) {
					$the_name = 'custom_'.$cus['nr'];
					if ($cus['type']=='date default NULL') {
						$cus['value'] = pnModAPIFunc(__PNADDRESSBOOK__,'user','stamp2date',array('idate'=>$cus['value']));
					}
					if ($cus['type']=='decimal(10,2) default NULL') {
						$cus['value'] = pnModAPIFunc(__PNADDRESSBOOK__,'user','num4display',array('inum'=>$cus['value']));
					}
					$output->FormHidden($the_name,$cus['value']);
				}
			}
		}
		else {
			foreach($cus_fields as $cus) {
				if ((!strstr($cus['type'],'tinyint')) && (!strstr($cus['type'],'smallint'))) {
					$the_name = 'custom_'.$cus['nr'];
					$output->FormHidden($the_name,pnVarCleanFromInput($the_name));
				}
			}
		}
	}
	$output->FormHidden('catview',$catview);
	$output->FormHidden('all',$all);
	$output->FormHidden('sortview',$sortview);
	$output->FormHidden('menuprivate',$menuprivate);
	$output->FormHidden('total',$total);
	$output->FormHidden('page',$page);
	$output->FormHidden('char',$char);
	$output->FormHidden('formcall',$formcall);
	$output->FormHidden('id',$id);
	$output->FormHidden('zip',$zip);
	$output->FormHidden('city',$city);
	$output->FormHidden('address1',$address1);
	$output->FormHidden('address2',$address2);
	$output->FormHidden('state',$state);
	$output->FormHidden('country',$country);
	$output->FormHidden('contact_1',$contact_1);
	$output->FormHidden('contact_2',$contact_2);
	$output->FormHidden('contact_3',$contact_3);
	$output->FormHidden('contact_4',$contact_4);
	$output->FormHidden('contact_5',$contact_5);
	$output->FormHidden('c_label_1',$c_label_1);
	$output->FormHidden('c_label_2',$c_label_2);
	$output->FormHidden('c_label_3',$c_label_3);
	$output->FormHidden('c_label_4',$c_label_4);
	$output->FormHidden('c_label_5',$c_label_5);
	$output->FormHidden('c_main',$c_main);
	$output->FormHidden('note',$note);
	$output->FormHidden('user_id',$user_id);
?>