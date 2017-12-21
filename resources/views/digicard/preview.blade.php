@extends('layout.app')
@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-3 p0">
                <div>
                    <div class="digicard_profile">
                        <div class="background"></div>
                        <div class="profile_image">
                            <img src="https://app.sumatealexito.com/images/digicard/e{{ $data->empresa_id }}" />
                        </div>
                    </div>
                </div>
                <div class="row ml10 mr10">
                    <div class="col-md-12 text-center mt10 mb20">
                        <h2 class="text-primary-2 text-capitalize m0">{{$data->nombre}}</h2>
                    </div>
                    <div class="col-md-12 col-xs-12">
                        <div class="row text-center mb20">
                            <div class="col-md-6 col-xs-6">
                                <div><i class="fa fa-code fa-2x text-muted"></i></div>
                                <div class="h4 small">{{$data->profesion}}</div>
                            </div>
                            <div class="col-md-6 col-xs-6">
                                <div><i class="fa fa-home fa-2x text-muted"></i></div>
                                <div class="h4 small">{{$data->empresa_nombre}}</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-12 col-xs-12">
                        <h4 class="text-primary-2">Acerca de mí</h4>
                        <div class="form-group text-muted">{{$data->descripcion}}</div>
                    </div>
                    <div class="col-md-12 col-xs-12">
                        <h4 class="text-primary-2">Empresa</h4>
                        <div class="form-group text-muted">{{$data->empresa_descripcion}}</div>
                    </div>
                </div>
            </div>
            <div class="col-lg-9">
                <div class="hidden-xs" style="width: 1px; position: absolute; left: 0px; height: 100%; border-left: 1px solid #e0e0e0; ">&nbsp;</div>
                <div class="row">
                    <div class="col-md-12 col-xs-12 mt10 mb10">
                        <h4 class="text-primary-2">Mis Productos y Servicios</h4>
                    </div>
                    @if(count($data->productos)<=0)
                    <div class="col-md-12 col-xs-12 text-center mb20">No ha registro algún producto o servicio.</div>
                    @endif
                    @foreach($data->productos as $item)
                        <div class="col-md-4 col-xs-12">
                            <div class="row">
                                <div class="col-md-12 col-xs-12">
                                    <img style="border-radius: 4px;" src="https://app.sumatealexito.com/images/digicard/p{{$item->id}}" alt="" width="100%" height="200px">
                                </div>
                                <div class="col-md-8 col-xs-8 h5">
                                    <div><b>{{$item->nombre}}</b></div>
                                </div>
                                <div class="col-md-4 col-xs-4 text-right">
                                    <div class="h5" style="color:#ef6c00;"><b>S/.{{$item->precio}}.00</b></div>
                                </div>
                                <div class="col-md-12 col-xs-12">
                                    <div class="form-group text-muted h6">{{$item->descripcion}}</div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                    <div class="col-md-12 col-xs-12">
                        <hr class="m0">
                        <h4 class="text-primary-2 mt20 mb10">Encuéntrame</h4>
                        <iframe width="100%" height="250" frameborder="0" style="border:0" src="https://www.google.com/maps/embed/v1/place?key=AIzaSyA-f7leSL0hmgrCPdA7CkBWOFfH59zia3U&q={{$data->local->latitud}},{{$data->local->longitud}}" allowfullscreen></iframe>
                        <div class="text-center mt10"><b>{{ $data->local->direccion }}</b></div>
                        <div class="text-center"><small><b>{{ $data->local->distrito2 }}</b></small></div>
                    </div>
                    <div class="col-md-6 col-xs-12">
                        <div class="row">
                            <div class="col-md-12 col-xs-12 mt20 mb10">
                                <h4 class="text-primary-2">Mis Redes Sociales</h4>
                            </div>
                            <div class="col-md-12 col-xs-12">
                                <div class="row text-center">
                                    <div class="col-md-3 col-xs-6">
                                        <div class="form-group">
                                            <a href="{{ $data->facebook}}" target="_blank" class="btn btn-circle bg-facebook"><i class="fa fa-facebook"></i></a>
                                            <div class="text-facebook">Facebook</div>
                                        </div>
                                    </div>
                                    <div class="col-md-3 col-xs-6">
                                        <div class="form-group">
                                            <a href="{{ $data->twitter}}" target="_blank" class="btn btn-circle bg-twitter"><i class="fa fa-twitter"></i></a>
                                            <div class="text-twitter">Twitter</div>
                                        </div>
                                    </div>
                                    <div class="col-md-3 col-xs-6">
                                        <div class="form-group">
                                            <a href="{{ $data->linkedin}}" target="_blank" class="btn btn-circle bg-linkedin"><i class="fa fa-linkedin"></i></a>
                                            <div class="text-linkedin">Linkedin</div>
                                        </div>
                                    </div>
                                    <div class="col-md-3 col-xs-6">
                                        <div class="form-group">
                                            <a href="{{ $data->web}}" target="_blank" class="btn btn-circle bg-web"><i class="fa fa-globe"></i></a>
                                            <div class="text-web">Web</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 col-xs-12">
                        <div class="row">
                            <div class="col-md-12 col-xs-12 mt20 mb10">
                                <h4 class="text-primary-2">Contáctame</h4>
                            </div>
                            <div class="col-md-12 col-xs-12">
                                <div class="row text-center">
                                    <div class="col-md-4 col-xs-4">
                                        <div class="form-group">
                                            <a href="mailto:{{$data->email}}" class="btn btn-circle btn-danger"><i class="fa fa-envelope"></i></a>
                                            <div>Email</div>
                                        </div>
                                    </div>
                                    <div class="col-md-4 col-xs-4">
                                        <div class="form-group">
                                            <a href="tel:{{$data->telefono}}" class="btn btn-circle btn-warning"><i class="fa fa-phone"></i></a>
                                            <div>Llamar</div>
                                        </div>
                                    </div>
                                    <div class="col-md-4 col-xs-4">
                                        <div class="form-group">
                                            <a href="whatsapp://send?abid=+{{$data->whatsapp}}" class="btn btn-circle btn-success"><i class="fa fa-whatsapp"></i></a>
                                            <div>Whatsapp</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>        
    </div>
@endsection