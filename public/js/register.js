function setMessage(type, message){
	$('#alert-message').html('<div class="alert alert-'+type+' text-center">' + message + '</div>');
	setTimeout(function(){
		$('#alert-message').empty();
	}, 8000);
}

function openForm(data){
  console.log(data);
  $('#register-container').removeClass('hidden');
  $('#social-container').addClass('hidden');

  $('#nombre').val(data.name);
  $('#email').val(data.email);
  $('#' + data.service + '_id').val(data.social_id);
}

jQuery(document).ready(function() {
  "use strict";

  $('#btn-fb-register, #btn-goo-register, #btn-mail-register').on('click', function(e){
  	e.preventDefault();
    var service = $(e.target).attr('data-auth');
    if(service == 'email'){
      $('#register-container').removeClass('hidden');
      $('#social-container').addClass('hidden');
    } else {
      var auth = $(e.target).attr('data-auth');
      window.open('/auth/' + auth + '?src=register', '', 'width=800,height=600');      
    }
  });

  $('#frm-register').on('submit', function(e){
  	e.preventDefault();
  	if($('#chk_terms:checked').length<=0){
  		setMessage('danger', 'Debe aceptar los términos de uso');
  		return false;
  	}
    var $form = $(this);
  	var params = {
      nombre: $('#nombre').val(),
      ruc: $('#empresa_ruc').val(),
      email: $('#email').val(),
      telefono: $('#telefono').val(),
      facebook_id: $('#facebook_id').val(),
      google_id: $('#google_id').val(),
      local_id: $('#local_id').val(),
      pbx: 'on'
    };


    if((/^[\d]+$/g).exec(params.ruc) == null){
      setMessage('danger', 'Debes ingresar un DNI o RUC válido');
      return false;
    } else if (params.ruc.length != 8 && params.ruc.length != 11) {
      setMessage('danger', 'Número de digitos inválido');
      return false;
    } else if (params.ruc.length == 11 && ( (['10','15','20']).indexOf( params.ruc.substr(0,2) ) < 0 )) {
      setMessage('danger', 'Número RUC inválido');
      return false;
    }

    $form.find(':input').prop('disabled', true);

    $.ajax({
      url: '/register',
      type: 'POST',
      data: params,
      headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
      }
    }).done(function(r){
      
      if(r.pbx !== undefined){
        $('#pbx-number').text( r.pbx.result.number + " Anexo 101" );
      } else {
        $('#pbx-info').addClass('hidden');
      }

      if(r.local_direccion !== undefined){
        $('#local-address').text( r.local_direccion );
      }

      $('#first-step').addClass('hidden');
      $('#second-step').removeClass('hidden');
      //$('#pbx-content').removeClass('hidden');

    }).fail(function(e){
      setMessage('danger', e.responseJSON.message);
      console.log(e);
    }).always(function(){
      $form.find(':input').prop('disabled', false);
    });

  });
  /*
  $('#frm-pbx-create').on('submit', function(e){
    e.preventDefault();
    var $form = $(this), params = $form.serialize(), $inputs = $form.find(':input');
    $inputs.prop('disabled', true);
    $.ajax({
      url: '/register/pbx',
      type: 'POST',
      data: params,
      headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
      }
    }).done(function(r){
      setMessage('success','Felicidades, tu cuenta ya esta registrada y tu central ya está activa, el número asignado es ' + r.result.number);
    }).fail(function(e){
      setMessage('danger',e.responseJSON.message);
      console.log(e);
    }).always(function(){
      $inputs.prop('disabled', false);
    });
  });*/
});