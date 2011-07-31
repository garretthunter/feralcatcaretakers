function insertNewline(obj)
{
  if(obj.document.selection.type=='Control') return;
  var Range = obj.document.selection.createRange();
  if(!Range.duplicate) return;
  Range.pasteHTML('<br>.');
  Range.findText('.',10,5)
  Range.select();
  obj.curword=Range.duplicate();
  obj.curword.text = ''; 
  Range.select();
}

function insertNewParagraph(obj)
{
  if(obj.document.selection.type=='Control') return;
  var Range = obj.document.selection.createRange();
  if(!Range.duplicate) return;
  Range.pasteHTML('<P>.');
  Range.findText('.',10,5)
  Range.select();
  obj.curword=Range.duplicate();
  obj.curword.text = '' ;
  Range.select();

}