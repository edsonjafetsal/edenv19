$(obtain_registers());

function obtain_registers(rey)
{
	$.ajax({
		url : 'consulta.php',
		type : 'POST',
		dataType : 'html',
		data : { rey: rey },
		})

	.done(function(res){
		$("#tabla_resultado").html(res);
	})
}

$(document).on('keyup', '#search', function()
{
	var search=$(this).val();
	if (search!="")
	{
		obtain_registers(search);
	}
	else
		{
			obtain_registers();
		}
});
