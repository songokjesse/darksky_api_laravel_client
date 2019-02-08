<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>



    <!-- Fonts -->
    <link rel="dns-prefetch" href="https://fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css?family=Nunito" rel="stylesheet" type="text/css">
    <link href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet" integrity="sha384-wvfXpqpZZVQGK6TAh5PVlGOfQNHSoD2xbE+QkPxCAFlNEevoEH3Sl0sibVcOQVnN" crossorigin="anonymous">
    <!-- Styles -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.2.1/css/bootstrap.min.css" integrity="sha384-GJzZqFGwb1QTTN6wy59ffF1BuGJpLSa9DkKMp0DgiMDm4iYMj70gZWKYbI706tWS" crossorigin="anonymous">
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/weather-icons/2.0.9/css/weather-icons.css" />
{{----}}
</head>
<body>
<div id="app">
    <nav class="navbar navbar-expand-lg navbar-light navbar-laravel">
        <a class="navbar-brand" href="/">Weather Forecast</a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarSupportedContent">

        </div>
    </nav>
    <main class="py-2 container">
        @if (session('status'))
            <div class="alert alert-danger">
                {{ session('status') }}
            </div>
        @endif
        {{--location search form--}}
        <div align="right"><small> <a href="https://darksky.net/dev/docs">“Powered by Dark Sky” </a> </small></div>

                <form method="get" action="forecast" >
                    {{csrf_field()}}
                    <div class="row py-2">
                        <div class="col-md-6">
                            <div class="form-group">
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <div class="input-group-text"><i class="fa fa-globe" aria-hidden="true"></i></div>
                                    </div>
                                    <input name="location"  id="searchMapInput" class="form-control" type="text" placeholder="Enter a location" aria-label="Search"  autofocus required>
                                    <input type="hidden" name="lat" id="lat" value="">
                                    <input type="hidden" name="lng" id="lng" value="">

                                </div>
                            </div>

                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <div class="input-group-text">Lang</div>
                                            </div>
                                        <select class="form-control" name="lang">
                                            <option value="en">en</option>
                                            <option value="de">de</option>
                                            <option value="fr">fr</option>
                                            <option value="nl">nl</option>
                                        </select>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <div class="input-group">
                                        <div class="input-group-prepend">
                                            <div class="input-group-text">Units</div>
                                        </div>
                                    <select class="form-control" name="units">
                                        <option value="auto">auto</option>
                                        <option value="ca">ca</option>
                                        <option value="uk2">uk2</option>
                                        <option value="us">us</option>
                                        <option value="si">si</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2">

                            <button type="submit" class=" btn btn-primary "><i class="fa fa-search" aria-hidden="true"></i> </button>
                            <button type="button" class=" btn btn-secondary" disabled><i class="fa fa-map-marker" aria-hidden="true"></i></button>

                        </div>

                    </div>

                </form>

     {{--end search form--}}

        {{--Search results --}}

@isset($location)
    {{--current forecast results--}}
    <div class="py-2">
    <div class="card">
        <div class="card-body">
            <h3><u>Current Weather</u> </h3>
            <div class="row">
                <div class="col-md-3">
                    <i class="wi wi-{{$forecast_data['currently']['icon']}} display-1 m-3"></i>
                </div>
                <div class="col-md-9">
                    <h6>{{$location}}</h6>
                    <b>Lon:</b> {{$forecast_data['longitude']}} &nbsp;<b> Lat:</b> {{$forecast_data['latitude']}} &nbsp;
                    <div><b>Temp: </b>{{$forecast_data['currently']['temperature']}} &deg; </div>
                    <div> <b>Cloud Cover: </b>{{$forecast_data['currently']['cloudCover'] * 100 }} % </div>
                    <div><b> {{$forecast_data['currently']['summary']}} </b></div>

                </div>
            </div>
        </div>
    </div>

{{--end current forecast--}}
<br>

    <div class="py-2 ">
        <!-- Nav tabs -->
        <ul class="nav nav-tabs">
            <li class="nav-item">
                <a class="nav-link active" data-toggle="tab" href="#home">Forecast</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-toggle="tab" href="#menu1">Time Machine</a>
            </li>
        </ul>

        <!-- Tab panes -->
        <div class="tab-content">
            <div class="tab-pane container active" id="home">
            <br>
                {{--forecast for one week    --}}

                <div  class="accordion" id="accordion">
                        @foreach($forecast_data['daily']['data'] as $my_key=>$data)
                            <div class="card">
                                <div class="card-header">
                                    <button class="btn btn-link" type="button" data-toggle="collapse" data-target="#collapseForecast{{$my_key}}" aria-expanded="false" aria-controls="collapseOne">

                                        {{ strtoupper(\Carbon\Carbon::createFromTimestamp($data['time'])->format('D'))}}: &nbsp;
                                        {{ \Carbon\Carbon::createFromTimestamp($data['time'])->format('d-m-Y')}}
                                        {{--<i class="fa fa-plus-square-o" aria-hidden="true"></i>--}}

                                    </button>

                                </div>
                                <div id="collapseForecast{{$my_key}}" class="collapse" aria-labelledby="headingOne" data-parent="#accordion">
                                    <div class="card-body">
                                        <i class="wi wi-{{$data['icon']}} display-4"></i>
                                        <b>Temp: </b>{{$data['temperatureHigh']}} &deg;
                                        <div> <b>Cloud Cover: </b>{{$data['cloudCover'] * 100 }} % </div>
                                        <div><b> {{$data['summary']}} </b></div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    {{--end one week forecast--}}
            </div>
            <div class="tab-pane container fade" id="menu1">
                {{--observed weather for past 30 days --}}
                    <br>
                <div class="accordion" id="accordionTime">
                    @foreach($observed_data as $key=>$timemachine)
                        <div class="card">
                            <div class="card-header" >
                                <button class="btn btn-link" type="button" data-toggle="collapse" data-target="#collapse{{$key}}" aria-expanded="true" aria-controls="collapseOne">
                                    {{ \Carbon\Carbon::createFromTimestamp($timemachine['value']['daily']['data'][0]['time'])->format('d-m-Y')}}

                                </button>
                            </div>

                            <div id="collapse{{$key}}" class="collapse" aria-labelledby="headingOne" data-parent="#accordionTime">
                                <div class="card-body">
                                    <i class="wi wi-{{$timemachine['value']['daily']['data'][0]['icon']}} m-2 display-4"></i>
                                    <div><b>Temp High:</b> {{$timemachine['value']['daily']['data'][0]['temperatureHigh']}} &deg; &nbsp; <b>Temp Low: </b>{{$timemachine['value']['daily']['data'][0]['temperatureLow']}}&deg;</div>
                                    <div><b>Wind Speed :</b> {{$timemachine['value']['daily']['data'][0]['windSpeed']}}</div>
                                    <div><b>Cloud Cover :</b> {{$timemachine['value']['daily']['data'][0]['cloudCover']  * 100 }} % </div>
                                    {{$timemachine['value']['daily']['data'][0]['summary']}}

                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
                {{--end observed --}}
            </div>
        </div>
        </div>

    </div>
@endisset
{{--end search results--}}
        {{--<script src='https://darksky.net/map-embed/@temperature,39.000,-95.000,4.js?embed=true&timeControl=true&fieldControl=true&defaultField=temperature&defaultUnits=_f'></script>--}}


    </main>
</div>
<!-- Scripts -->
<script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.6/umd/popper.min.js" integrity="sha384-wHAiFfRlMFy6i5SRaxvfOCifBUQy1xHdJ/yoi7FRNXMRBu5WHdZYu1hA6ZOblgut" crossorigin="anonymous"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.2.1/js/bootstrap.min.js" integrity="sha384-B0UglyR+jN6CkvvICOB2joaf5I4l3gm9GU6Hc1og6Ls7i6U/mkkaduKaBhlAXv9k" crossorigin="anonymous"></script>
<script>

    // get and autocomplete the location input form element
function initMap() {
    var input = document.getElementById('searchMapInput');
  
    var autocomplete = new google.maps.places.Autocomplete(input);
   
    autocomplete.addListener('place_changed', function() {
        var place = autocomplete.getPlace();
        document.getElementById('lat').value = place.geometry.location.lat();
        document.getElementById('lng').value = place.geometry.location.lng();
    });
}

</script>
<script src="https://maps.googleapis.com/maps/api/js?key={{env("GOOGLE_GEOCODING_API_KEY")}}&libraries=places&callback=initMap" async defer></script>
</body>
</html>