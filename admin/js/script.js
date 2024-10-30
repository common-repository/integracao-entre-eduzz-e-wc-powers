jQuery(document).ready(function($) { 

    $( "#wep-form-token" ).submit(function( event ) {
        var ajaxurl = $("#ajaxurl").val();
        var email = $("#email-input").val();
        var key = $("#key-input").val();
        var apikey = $("#apikey-input").val();
        if (email == '' || key == '' || apikey == '') {
            alert('Preencha todos os campos');
            return false;
        }
        toggleLoad($('#tokenInput'));
        $.ajax({
            url: ajaxurl, 
            type: 'POST',
            data: {
                'action': 'wep_conecta',
                'wep_email': email,  
                'wep_key': key,
                'wep_apikey': apikey
            },
            success: function( data ){
               toggleLoad($('#tokenInput'));
               if (data == 'true') {
                     $('#tokenInput').append('<span class="dashicons dashicons-yes-alt"></span>');
               } else{
                    $('#tokenInput').append('<span class="dashicons dashicons-no"></span>');
                    alert('Acesso inválido');
               }
            }
        });
        return false;
    });

    $( "#wep-form-options" ).submit(function( event ) {
        event.preventDefault();
        toggleLoad($('#optionsInput'));
        $.ajax({
            url: ajaxurl, 
            type: 'POST',
            data: {
                'action': 'wep_opcoes',
                'dados': $(this).serialize(),
            },
            success: function( data ){
               toggleLoad($('#optionsInput'));
               $('#optionsInput').append('<span class="dashicons dashicons-yes-alt"></span>');
            }
        });
        return false;
    });

    $('.action-importar').click(function(e) {
        e.preventDefault();
        if (confirm('Deseja realmente importar este pedido?')) {
            var ajaxurl = $("#ajaxurl").val();
            var saleId = $(this).attr('data-saleid');
            var tdPai = $(this).parent('td');
            toggleLoad(tdPai);
            $(this).hide();
            $.ajax({
                url: ajaxurl, 
                type: 'POST',
                data: {
                    'action': 'wep_importa_venda',
                    'wep_sale_id': saleId,  
                },
                success: function( data ){
                   toggleLoad($('#tokenInput'));
                   if (data == 'true') {
                        $(tdPai).html('Importado <span class="dashicons dashicons-yes-alt"></span>');
                   } else{
                        alert('Falha ao importar');
                         $(this).show();
                   }
                }
            });
        }
    });

    $('.action-reembolso').click(function(e) {
        e.preventDefault();
        if (confirm('Deseja realmente reembolsar este pedido?')) {
            var ajaxurl = $("#ajaxurl").val();
            var saleId = $(this).attr('data-saleid');
            var tdPai = $(this).parent('td');
            var linked = $(this);
            toggleLoad(tdPai);
            $(linked).hide();
            $.ajax({
                url: ajaxurl, 
                type: 'POST',
                data: {
                    'action': 'wep_reembolsa_venda',
                    'wep_sale_id': saleId,  
                },
                success: function( data ){
                   toggleLoad($('#tokenInput'));
                   if (data == 'true') {
                        $(tdPai).html('Pedido de reembolso solicitado com sucesso! <span class="dashicons dashicons-yes-alt"></span>');
                   } else{
                        alert('Falha ao solicitar reembolso');
                         $(linked).show();
                   }
                }
            });
        }
    });
        
    

    

    function toggleLoad(elemento){
        var pluginurl = $("#pluginurl").val();
        $(elemento).find('.dashicons').remove();
        if ($('#loadGiff').length) {
            $('#loadGiff').remove();
        } else {
            $(elemento).append('<img src="'+pluginurl+'/img/load.gif" id="loadGiff" />');
        }
    }



});

