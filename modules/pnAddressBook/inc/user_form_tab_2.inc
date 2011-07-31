<?php
	$pix = '<img alt="" src="modules/'.__PNADDRESSBOOK__.'/pnimages/pix.gif" width="1" height="1">';
	$output->Text('<table width="50%" align="center" border="0" cellpadding="0" cellspacing="0">');
	$custom_tab = pnModGetVar(__PNADDRESSBOOK__,'custom_tab');
	$priv = pnModGetVar(__PNADDRESSBOOK__,'globalprotect');
	if ((empty($custom_tab)) || ($custom_tab == '')) { $addrow = 0; }
	else { $addrow = 2; }
	
	$labels = pnModAPIFunc(__PNADDRESSBOOK__,'user','getLabels');
	if(!is_array($labels)) {
        $output->Text($labels);
        return $output->GetOutput();
    }
	$formlabdata = array();
	foreach($labels as $label) {
		$formlabdata[] = array('id'=>$label['nr'],
						 	 'name'=>$label['name']); 
	}

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
	$output->Text('<td align="center" valign="middle">&nbsp;&nbsp;<b><a href="javascript:document.addcontact.submit();" onclick="document.addcontact.action.value=0;">'.pnVarPrepHTMLDisplay(_NAME).'</a></b>&nbsp;&nbsp;</td>');
	$output->Text('<td width="1" bgcolor="'.$bc2.'">'.$pix.'</td>');
	$output->Text('<td align="center" valign="middle">&nbsp;&nbsp;<b><a href="javascript:document.addcontact.submit();" onclick="document.addcontact.action.value=1;">'.pnVarPrepHTMLDisplay(_pnAB_ADDRESS).'</a></b>&nbsp;&nbsp;</td>');
	$output->Text('<td width="1" bgcolor="'.$bc2.'">'.$pix.'</td>');
	$output->Text('<td bgcolor="'.$bc1.'" align="center" valign="middle">&nbsp;&nbsp;<b>'.pnVarPrepHTMLDisplay(_pnAB_CONTACT).'</b>&nbsp;&nbsp;</td>');
	$output->Text('<td width="1" bgcolor="'.$bc2.'">'.$pix.'</td>');
	if ($addrow > 0) {
		$output->Text('<td align="center" valign="middle">&nbsp;&nbsp;<b><a href="javascript:document.addcontact.submit();" onclick="document.addcontact.action.value=3;">'.pnVarPrepHTMLDisplay(pnModGetVar(__PNADDRESSBOOK__,'custom_tab')).'</a></b>&nbsp;&nbsp;</td>');
		$output->Text('<td width="1" bgcolor="'.$bc2.'">'.$pix.'</td>');
	}
	$output->Text('<td align="center" valign="middle">&nbsp;&nbsp;<b><a href="javascript:document.addcontact.submit();" onclick="document.addcontact.action.value=4;">'.pnVarPrepHTMLDisplay(_pnAB_NOTETAB).'</a></b>&nbsp;&nbsp;</td>');
	$output->Text('<td width="1" bgcolor="'.$bc2.'">'.$pix.'</td>');
	$output->Text('<td>'.$pix.'</td>');
	$output->Text('</td>');
	$output->Text('<td width="1">'.$pix.'</td>');
	$output->Text('</tr>');
	// row 3 pix
	$output->Text('<tr>');
	$output->Text('<td width="1" bgcolor="'.$bc2.'">'.$pix.'</td>');
	$output->Text('<td bgcolor="'.$bc2.'">'.$pix.'</td>');
	$output->Text('<td width="1" bgcolor="'.$bc2.'">'.$pix.'</td>');
	$output->Text('<td bgcolor="'.$bc2.'">'.$pix.'</td>');
	$output->Text('<td width="1" bgcolor="'.$bc2.'">'.$pix.'</td>');
	$output->Text('<td bgcolor="'.$bc1.'">'.$pix.'</td>');
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
	$output->TableStart('','','0','100%','5','0');
	$output->TableRowStart();
	$output->TableColStart(1,'center','middle');
	if ($c_main == 0) { $output->FormCheckbox('c_main',1,0,'radio'); }
	else { $output->FormCheckbox('c_main',0,0,'radio'); }
	$output->Text(' ');
	$output->TableColEnd();
	$output->TableColStart(1,'left','middle');
	$output->FormSelectMultiple('c_label_1',$formlabdata, 0, 1, $c_label_1, '');
	$output->TableColEnd();
	$output->TableColStart(1,'left','middle');
	$output->FormText('contact_1',$contact_1,40,80);
	$output->TableColEnd();
	$output->TableRowEnd();
	$output->TableRowStart();
	$output->TableColStart(1,'center','top');
	if ($c_main == 1) { $output->FormCheckbox('c_main',1,1,'radio'); }
	else { $output->FormCheckbox('c_main',0,1,'radio'); }
	$output->Text(' ');
	$output->TableColEnd();
	$output->TableColStart(1,'left','middle');
	$output->FormSelectMultiple('c_label_2',$formlabdata, 0, 1, $c_label_2, '');
	$output->TableColEnd();
	$output->TableColStart(1,'left','top');
	$output->FormText('contact_2',$contact_2,40,80);
	$output->TableColEnd();
	$output->TableRowEnd();
	$output->TableRowStart();
	$output->TableColStart(1,'center','top');
	if ($c_main == 2) { $output->FormCheckbox('c_main',1,2,'radio'); }
	else { $output->FormCheckbox('c_main',0,2,'radio'); }
	$output->Text(' ');
	$output->TableColEnd();
	$output->TableColStart(1,'left','middle');
	$output->FormSelectMultiple('c_label_3',$formlabdata, 0, 1, $c_label_3, '');
	$output->TableColEnd();
	$output->TableColStart(1,'left','top');
	$output->FormText('contact_3',$contact_3,40,80);
	$output->TableColEnd();
	$output->TableRowEnd();
	$output->TableRowStart();
	$output->TableColStart(1,'center','top');
	if ($c_main == 3) { $output->FormCheckbox('c_main',1,3,'radio'); }
	else { $output->FormCheckbox('c_main',0,3,'radio'); }
	$output->Text(' ');
	$output->TableColEnd();
	$output->TableColStart(1,'left','middle');
	$output->FormSelectMultiple('c_label_4',$formlabdata, 0, 1, $c_label_4, '');
	$output->TableColEnd();
	$output->TableColStart(1,'left','top');
	$output->FormText('contact_4',$contact_4,40,80);
	$output->TableColEnd();
	$output->TableRowEnd();
	$output->TableRowStart();
	$output->TableColStart(1,'center','top');
	if ($c_main == 4) { $output->FormCheckbox('c_main',1,4,'radio'); }
	else { $output->FormCheckbox('c_main',0,4,'radio'); }
	$output->Text(' ');
	$output->TableColEnd();
	$output->TableColStart(1,'left','middle');
	$output->FormSelectMultiple('c_label_5',$formlabdata, 0, 1, $c_label_5, '');
	$output->TableColEnd();
	$output->TableColStart(1,'left','top');
	$output->FormText('contact_5',$contact_5,40,80);
	$output->TableColEnd();
	$output->TableRowEnd();
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
		foreach($cus_fields as $cus) {
			if ((!strstr($cus['type'],'tinyint')) && (!strstr($cus['type'],'smallint'))) {
				$the_name = 'custom_'.$cus['nr'];
				$output->FormHidden($the_name,pnVarCleanFromInput($the_name));
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
	$output->FormHidden('prfx',$prfx);
	$output->FormHidden('lname',$lname);
	$output->FormHidden('fname',$fname);
	$output->FormHidden('title',$title);
	$output->FormHidden('company',$company);
	$output->FormHidden('img',$img);
	$output->FormHidden('zip',$zip);
	$output->FormHidden('city',$city);
	$output->FormHidden('address1',$address1);
	$output->FormHidden('address2',$address2);
	$output->FormHidden('state',$state);
	$output->FormHidden('country',$country);
	$output->FormHidden('note',$note);
	$output->FormHidden('user_id',$user_id);
?>