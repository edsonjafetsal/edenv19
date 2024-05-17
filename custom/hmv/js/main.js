$(buscar_datos());

function buscar_datos (consulta){
	$.ajax({
		url: 'app/buscar.php',
		type: 'POST',
		dataType: 'html',
		data: {consulta: consulta},
	})
		.done(function(respuesta) {
			$("#datos").html(respuesta);
		})
		.fail(function() {
			console.log("error");
		})
}

$(document).ready(function(){
	$('#product').on('change',function(){
		let valor = $('#product').val();
		console.log(valor);
		$.ajax({
			type: 'POST',
			url : 'hmvindex.php',
			data: {value: valor, cat: valor1, cli: valor2, date: valor3, rep: valor4},
			success: function(data)
			{
				location.reload();
			}
		});
	});
});

$(document).on('keyup', '#search_box', function(){
	var valor = $(this).val();
	if(valor != ""){
		buscar_datos(valor);
	} else {
		buscar_datos();
	}
});
