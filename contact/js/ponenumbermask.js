$(document).ready(function(){
    // Aplicar las máscaras de teléfono
    $('#phone_pro').mask('+00 (000) 000-0000',{placeholder:'(___) ___-___'});
    $('#fax').mask('+00 (000) 000-0000',{placeholder:'(___) ___-___'});
    $('#phone_perso').mask('+00 (000) 000-0000',{placeholder:'(___) ___-___'});
    $('#phone_mobile').mask('+00 (000) 000-0000',{placeholder:'(___) ___-___'});
    $('#extrarow-societe_phone_extension').mask('+00',{placeholder:'+__'});

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
        } else {
            formattedPhoneNumber = phoneNumber;
        }

        return formattedPhoneNumber;
    }

    // Detectar la longitud del número ingresado y ajustar el formato
    $('#phone_pro, #fax, #phone_perso, #phone_mobile, #extrarow-societe_phone_extension').on('blur', function() {
        var input = $(this);
        var formattedPhoneNumber = formatPhoneNumber(input);
        input.val(formattedPhoneNumber);
    });

    // Simular un clic fuera del campo de entrada para ajustar el formato automáticamente
    $('#phone_pro').blur();
    $('#fax').blur();
    $('#phone_perso').blur();
    $('#phone_mobile').blur();
    $('#extrarow-societe_phone_extension').blur();
});