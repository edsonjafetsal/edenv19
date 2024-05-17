$(document).ready(function($){
    $('#phone').mask('+00 (000) 000-0000',{placeholder:'(___) ___-___'});
    $('#fax').mask('+00 (000) 000-0000',{placeholder:'(___) ___-___'});
    $('#extrarow-societe_phone_extension').mask('+00',{placeholder:'+__'});

    // Ajustar el formato al número de teléfono existente cuando se carga la página
    $('#phone').trigger('input');
    $('#fax').trigger('input');
    $('#extrarow-societe_phone_extension').trigger('input');

    // Verificar y quitar el signo "+" si el número de teléfono tiene 10 dígitos y el signo "+"
    if ($('#phone').val().replace(/\D/g, '').length === 10 && $('#phone').val().charAt(0) === '+') {
        $('#phone').val($('#phone').val().substring(1));
    }
    if ($('#fax').val().replace(/\D/g, '').length === 10 && $('#fax').val().charAt(0) === '+') {
        $('#fax').val($('#fax').val().substring(1));
    }

    // Función para formatear el número de teléfono
    function formatPhoneNumber(input) {
        var phoneNumber = input.val().replace(/\D/g,'');
        var formattedPhoneNumber;

        if (phoneNumber.length === 10) {
            formattedPhoneNumber = phoneNumber.replace(/(\d{3})(\d{3})(\d{4})/, '($1) $2-$3');
        } else if (phoneNumber.length === 11) {
            formattedPhoneNumber = phoneNumber.replace(/(\d)(\d{3})(\d{3})(\d{4})/, '+$1 ($2) $3-$4');
        } else if (phoneNumber.length === 12) {
            formattedPhoneNumber = phoneNumber.replace(/(\d{2})(\d{3})(\d{3})(\d{4})/, '+$1 ($2) $3-$4');
        }else if (phoneNumber.length === 2) {
            formattedPhoneNumber = phoneNumber.replace(/(\d{2})/, '+$1');
        }
         else {
            formattedPhoneNumber = phoneNumber;
        }

        return formattedPhoneNumber;
    }

    // Detectar la longitud del número ingresado y ajustar el formato cuando el usuario termina de ingresar el número
    $('#phone').on('blur', function() {
        var input = $(this);
        var formattedPhoneNumber = formatPhoneNumber(input);
        input.val(formattedPhoneNumber);
    });

    $('#fax').on('blur', function() {
        var input = $(this);
        var formattedPhoneNumber = formatPhoneNumber(input);
        input.val(formattedPhoneNumber);
    });

    $('#extrarow-societe_phone_extension').on('blur', function() {
        var input = $(this);
        var formattedPhoneNumber = formatPhoneNumber(input);
        input.val(formattedPhoneNumber);
    });

    // Simular un clic fuera del campo de entrada para ajustar el formato automáticamente
    $('#phone').blur();
    $('#fax').blur();
    $('#extrarow-societe_phone_extension').blur();
});
