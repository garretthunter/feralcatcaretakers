<?php
	$pix = '<img alt="" src="modules/'.__PNADDRESSBOOK__.'/pnimages/pix.gif" width="1" height="1">';
	$custom_tab = pnModGetVar(__PNADDRESSBOOK__,'custom_tab');
	$textareawidth = pnModGetVar(__PNADDRESSBOOK__,'textareawidth');
	$priv = pnModGetVar(__PNADDRESSBOOK__,'globalprotect');
	if ((empty($custom_tab)) || ($custom_tab == '')) { $addrow = 0; }
	else { $addrow = 2; }
	
// Add JavaScript for custom fields
$j_script = '
<script language="JavaScript">
<!--
function TrackCount(fieldObj,maxChars) {
var diff = maxChars - fieldObj.value.length;
if (diff < 0) {
fieldObj.value = fieldObj.value.substring(0,maxChars);
diff = maxChars - fieldObj.value.length;
}
}
function LimitText(fieldObj,maxChars) {
var result = true;
if (fieldObj.value.length >= maxChars) {
result = false;
}
if (window.event) {
window.event.returnValue = result;
}
return result;
}
//-->
</script>
';
$output->Text($j_script);
	
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
	$output->Text('<td bgcolor="'.$bc1.'" align="center" valign="middle">&nbsp;&nbsp;<b><a href="javascript:document.addcontact.submit();" onclick="document.addcontact.action.value=0;">'.pnVarPrepHTMLDisplay(_NAME).'</a></b>&nbsp;&nbsp;</td>');
	$output->Text('<td width="1" bgcolor="'.$bc2.'">'.$pix.'</td>');
	$output->Text('<td align="center" valign="middle">&nbsp;&nbsp;<b><a href="javascript:document.addcontact.submit();" onclick="document.addcontact.action.value=1;">'.pnVarPrepHTMLDisplay(_pnAB_ADDRESS).'</a></b>&nbsp;&nbsp;</td>');
	$output->Text('<td width="1" bgcolor="'.$bc2.'">'.$pix.'</td>');
	$output->Text('<td align="center" valign="middle">&nbsp;&nbsp;<b><a href="javascript:document.addcontact.submit();" onclick="document.addcontact.action.value=2;">'.pnVarPrepHTMLDisplay(_pnAB_CONTACT).'</a></b>&nbsp;&nbsp;</td>');
	$output->Text('<td width="1" bgcolor="'.$bc2.'">'.$pix.'</td>');
	if ($addrow > 0) {
		$output->Text('<td align="center" valign="middle">&nbsp;&nbsp;<b>'.pnVarPrepHTMLDisplay(pnModGetVar(__PNADDRESSBOOK__,'custom_tab')).'</b>&nbsp;&nbsp;</td>');
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
	$output->Text('<td bgcolor="'.$bc2.'">'.$pix.'</td>');
	$output->Text('<td width="1" bgcolor="'.$bc2.'">'.$pix.'</td>');
	if ($addrow > 0) {
		$output->Text('<td bgcolor="'.$bc1.'">'.$pix.'</td>');
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
	//*****************************************
	// start form area
	//*****************************************
	$output->TableStart('','','0','100%','5','0');
	if ($formcall == 'edit') {
		$cus_fields = pnModAPIFunc(__PNADDRESSBOOK__,'user','customFieldInformation',array('id'=>$id));
	}
	else {
		$cus_fields = pnModAPIFunc(__PNADDRESSBOOK__,'user','getCustomfields');
	}
	foreach($cus_fields as $cus) {
		$output->TableRowStart();
		$the_name = 'custom_'.$cus['nr'];
		switch ($cus['type']) {
//gehSTART		
			case 'int(1) default NULL':				//gehINSERT
				$output->TableColStart(1,'left','middle');		//gehINSERT
				$output->Text(pnVarPrepHTMLDisplay($cus['name']).':');	//gehINSERT
				$output->TableColEnd();					//gehINSERT
				$output->TableColStart(1,'left','middle');		//gehINSERT
				(isset($the_name)) ? $output->FormCheckbox($the_name,pnVarCleanFromInput($the_name)) : $output->FormCheckbox($the_name,0); //gehINSERT
				break;
//gehEND
			case 'varchar(60) default NULL':
				$output->TableColStart(1,'left','middle');
				$output->Text(pnVarPrepHTMLDisplay($cus['name']).':');
				$output->TableColEnd();
				$output->TableColStart(1,'left','middle');
				$output->FormText($the_name,pnVarCleanFromInput($the_name),60,60);
				break;
			case 'varchar(120) default NULL':
				$output->TableColStart(1,'left','top');
				$output->Text(pnVarPrepHTMLDisplay($cus['name']).':');
				$output->TableColEnd();
				$output->TableColStart(1,'left','top');
				$output->Text('<textarea name="'.$the_name.'" rows="2" cols="'.$textareawidth.'" onkeyup="TrackCount(this,120)" onkeypress="LimitText(this,120)" STYLE="overflow:hidden;">'.pnVarCleanFromInput($the_name).'</textarea>');
				break;
			case 'varchar(240) default NULL':
				$output->TableColStart(1,'left','top');
				$output->Text(pnVarPrepHTMLDisplay($cus['name']).':');
				$output->TableColEnd();
				$output->TableColStart(1,'left','top');
				$output->Text('<textarea name="'.$the_name.'" rows="4" cols="'.$textareawidth.'" onkeyup="TrackCount(this,240)" onkeypress="LimitText(this,240)" STYLE="overflow:hidden;">'.pnVarCleanFromInput($the_name).'</textarea>');
				break;
			case 'decimal(10,2) default NULL':
				$output->TableColStart(1,'left','middle');
				$output->Text(pnVarPrepHTMLDisplay($cus['name']).':');
				$output->TableColEnd();
				$output->TableColStart(1,'left','middle');
				$output->FormText($the_name,pnVarCleanFromInput($the_name),12,12);
				break;
			case 'int default NULL':
				$output->TableColStart(1,'left','middle');
				$output->Text(pnVarPrepHTMLDisplay($cus['name']).':');
				$output->TableColEnd();
				$output->TableColStart(1,'left','middle');
				$output->FormText($the_name,pnVarCleanFromInput($the_name),9,9);
				break;
			case 'date default NULL':
				$output->TableColStart(1,'left','middle');
				$output->Text(pnVarPrepHTMLDisplay($cus['name']).':');
				$output->TableColEnd();
				$output->TableColStart(1,'left','middle');
				$output->Text('<table border="0" cellpadding="0" cellspacing="0"><tr><td>');
				$output->FormText($the_name,pnVarCleanFromInput($the_name),10,10);
				if (pnModGetVar(__PNADDRESSBOOK__,'dateformat') == 0) {
					$output->Text('</td><td>&nbsp;&nbsp;('.pnVarPrepHTMLDisplay(_pnAB_DATEFORMAT_1).')</td></tr></table>');
				}
				else {
					$output->Text('</td><td>&nbsp;&nbsp;('.pnVarPrepHTMLDisplay(_pnAB_DATEFORMAT_2).')</td></tr></table>');
				}
				break;
			case 'tinyint default NULL':
				$output->TableColStart(2,'left','middle');
				$output->Text(pnVarPrepHTMLDisplay('<br>'));
				break;
			case 'smallint default NULL':
				$output->TableColStart(2,'left','middle');
				$output->Text(pnVarPrepHTMLDisplay('<hr>'));
				break;
			default:
				$output->TableColStart(1,'left','middle');
				$output->Text(pnVarPrepHTMLDisplay($cus['name']).':');
				$output->TableColEnd();
				$output->TableColStart(1,'left','middle');
				$output->FormText($the_name,pnVarCleanFromInput($the_name),60,60);
				break;
		}
		$output->TableColEnd();
		$output->TableRowEnd();
	}
	$output->TableEnd();
	$output->Text('</td>');
	$output->Text('<td width="1" bgcolor="'.$bc2.'">'.$pix.'</td>');
	$output->Text('</tr>');
	//*****************************************
	// end form area
	//*****************************************
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