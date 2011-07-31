<?php
	$pix = '<img alt="" src="modules/'.__PNADDRESSBOOK__.'/pnimages/pix.gif" width="1" height="1">';
	$custom_tab = pnModGetVar(__PNADDRESSBOOK__,'custom_tab');
	$priv = pnModGetVar(__PNADDRESSBOOK__,'globalprotect');
	if ((empty($custom_tab)) || ($custom_tab == '')) { $addrow = 0; }
	else { $addrow = 2; }
	$zipbeforecity = pnModGetVar(__PNADDRESSBOOK__,'zipbeforecity');
	
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
	$output->Text('<td align="center" valign="middle">&nbsp;&nbsp;<b><a href="javascript:document.addcontact.submit();" onclick="document.addcontact.action.value=0;">'.pnVarPrepHTMLDisplay(_NAME).'</a></b>&nbsp;&nbsp;</td>');
	$output->Text('<td width="1" bgcolor="'.$bc2.'">'.$pix.'</td>');
	$output->Text('<td bgcolor="'.$bc1.'" align="center" valign="middle">&nbsp;&nbsp;<b>'.pnVarPrepHTMLDisplay(_pnAB_ADDRESS).'</b>&nbsp;&nbsp;</td>');
	$output->Text('<td width="1" bgcolor="'.$bc2.'">'.$pix.'</td>');
	$output->Text('<td align="center" valign="middle">&nbsp;&nbsp;<b><a href="javascript:document.addcontact.submit();" onclick="document.addcontact.action.value=2;">'.pnVarPrepHTMLDisplay(_pnAB_CONTACT).'</a></b>&nbsp;&nbsp;</td>');
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
	$output->Text('<td bgcolor="'.$bc1.'">'.$pix.'</td>');
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
	$output->TableStart('','','0','100%','5','0');
	$output->TableRowStart();
	$output->TableColStart(1,'left','top');
	$output->Text(pnVarPrepHTMLDisplay(_pnAB_ADDRESS).':');
	$output->TableColEnd();
	$output->TableColStart(1,'left','top');
	$output->FormText('address1',$address1,60,100);
	$output->TableColEnd();
	$output->TableRowEnd();
	$output->TableRowStart();
	$output->TableColStart(1,'left','top');
	$output->Text('');
	$output->TableColEnd();
	$output->TableColStart(1,'left','top');
	$output->FormText('address2',$address2,60,100);
	$output->TableColEnd();
	$output->TableRowEnd();
	if ($zipbeforecity) {
		$output->TableRowStart();
		$output->TableColStart(1,'left','middle');
		$output->Text(pnVarPrepHTMLDisplay(_pnAB_ZIP).':');
		$output->TableColEnd();
		$output->TableColStart(1,'left','middle');
		$output->FormText('zip',$zip,60,100);
		$output->TableColEnd();
		$output->TableRowEnd();
	}
	$output->TableRowStart();
	$output->TableColStart(1,'left','middle');
	$output->Text(pnVarPrepHTMLDisplay(_pnAB_CITY).':');
	$output->TableColEnd();
	$output->TableColStart(1,'left','middle');
	$output->FormText('city',$city,60,60);
	$output->TableColEnd();
	$output->TableRowEnd();
	$output->TableRowStart();
	$output->TableColStart(1,'left','middle');
	$output->Text(pnVarPrepHTMLDisplay(_pnAB_STATE).':');
	$output->TableColEnd();
	$output->TableColStart(1,'left','middle');
	$output->FormText('state',$state,60,100);
	$output->TableColEnd();
	$output->TableRowEnd();
	if (!$zipbeforecity) {
		$output->TableRowStart();
		$output->TableColStart(1,'left','middle');
		$output->Text(pnVarPrepHTMLDisplay(_pnAB_ZIP).':');
		$output->TableColEnd();
		$output->TableColStart(1,'left','middle');
		$output->FormText('zip',$zip,60,100);
		$output->TableColEnd();
		$output->TableRowEnd();
	}
	$output->TableRowStart();
	$output->TableColStart(1,'left','middle');
	$output->Text(pnVarPrepHTMLDisplay(_pnAB_COUNTRY).':');
	$output->TableColEnd();
	$output->TableColStart(1,'left','middle');
	$output->FormText('country',$country,60,100);
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