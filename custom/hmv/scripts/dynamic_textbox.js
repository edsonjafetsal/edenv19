/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */


jQuery(document).ready(function(){
 
    var counter = 1;
    jQuery('#mois').bind('input', function() { 
        
    $('#TextBoxDiv1').html('')
  $loopcount = $(this).val(); // get the selected value
            for (var i = 1; i <= $loopcount; i++)
            {
                $('#TextBoxDiv1').append('<div><label>Textbox #'+i+'</label><input type="text" name="textbox'+i+'" class="textbox2" value="" /></div>');
            }
    

} );
 
    jQuery("#addButton").click(function () {
 
	
 
	var newTextBoxDiv = jQuery(document.createElement('div'))
	     .attr("id", 'TextBoxDiv' + counter);
 
	newTextBoxDiv.after().html('<label>Textbox #'+ counter + ' : </label>' +
	      '<input type="text" name="textbox' + counter + 
	      '" id="textbox' + counter + '" value="" >');
 
	newTextBoxDiv.appendTo("#TextBoxesGroup");
 
 
	counter++;
     });
 
     jQuery("#removeButton").click(function () {
	if(counter==1){
          alert("No more textbox to remove");
          return false;
       }   
 
	counter--;
 
        jQuery("#TextBoxDiv" + counter).remove();
 
     });
 
     jQuery("#getButtonValue").click(function () {
 
	var msg = '';
	for(i=1; i<counter; i++){
   	  msg += "\n Textbox #" + i + " : " + $('#textbox' + i).val();
	}
    	  alert(msg);
     });
  });



