<?php

namespace App\Http\Controllers;

use GuzzleHttp\Exception\RequestException;
use Illuminate\Http\Request;

use GuzzleHttp\Client;
use GuzzleHttp\Promise;



class WeatherController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {

       //get posted form input
       $location = $request->input('location');
       $lat = $request->input('lat');
       $lng = $request->input('lng');
       $lang = $request->input('lang');
       $units = $request->input('units');

//       check if latitude and longitude is empty()
        if($lat == "" && $lng == ""){
            //  get google_api_key from environmental variable (.env) file
            $google_api_key = env('GOOGLE_GEOCODING_API_KEY');

            $client = new Client();

//            get lat and long from google geolocation api
            $geocode_stats = json_decode($client->get("https://maps.googleapis.com/maps/api/geocode/json?key=" . $google_api_key . "&address=" . $location . "&sensor=false")->getBody(), true);

//             If Status Code is ZERO_RESULTS, OVER_QUERY_LIMIT, REQUEST_DENIED or INVALID_REQUEST
            if ($geocode_stats['status'] != 'OK') {
                return back()->with('status', 'Location Not Found!');
            }
            else{
                $lat= $geocode_stats['results'][0]['geometry']['location']['lat'];
               $lng = $geocode_stats['results'][0]['geometry']['location']['lng'];
            }
             //forecast function to get the weather forecast
             $forecast_data = $this->getForecast($lat,$lng,$lang,$units);
            //timemachine function to get the weather forecast for the past 30 days
             $observed_data = $this->getTimeMachineWeather($lat,$lng,$lang,$units);
        }
        else
            {
            // forecast function to get the weather forecast
             $forecast_data = $this->getForecast($lat,$lng,$lang,$units);
           //timemachine function to get the weather forecast for the past 30 days
             $observed_data = $this->getTimeMachineWeather($lat,$lng,$lang,$units);
             }


      return view('search', compact('forecast_data', 'location', 'observed_data'));
    }

    public function getForecast($lat,$lng,$lang,$units){
//        get dark_api_key from environmental variable (.env) file
        $dark_api_key = env('DARK_SKY_ID');

        $client = new Client();

        //get weather forecast
        $forecast = json_decode($client->get('https://api.darksky.net/forecast/'. $dark_api_key.'/'.$lat.','.$lng.'?exclude=hourly&lang='.$lang.'&units='.$units)->getBody(), true);

//        Match Dark sky Api Icons with their css attributes
        $forecast_data = $this->getCurrentlyIcons($forecast);


        return $forecast_data;
    }

    public function getTimeMachineWeather($lat,$lng,$lang,$units){
//        get dark_api_key from environmental variable (.env) file
        $dark_api_key = env('DARK_SKY_ID');

        #RETURN DATE ARRAY
        $dates = $this->getCurrentDate();

        $client = new Client();
        $promises = [];

        // Get observed weather data
        foreach($dates as $date)
            $promises[] = $client->requestAsync('GET','https://api.darksky.net/forecast/'. $dark_api_key.'/'.$lat.','.$lng.','.$date.'?exclude=currently,hourly,flags&lang='.$lang.'&units='.$units)
                ->then(function($response) {
                    return json_decode($response->getBody(), true);
                });

        $observed = Promise\unwrap($promises);
        $observed = Promise\settle($promises)->wait();

        $observed_data = $this->getObservedDailyIcons($observed);

        return $observed_data;
    }

    public function getCurrentDate(){
        #SET CURRENT DATE
        $month = date("m");
        $day = date("d");
        $year = date("Y");

        #LOOP THROUGH DAYS
        for($i=1; $i<=30; $i++){
            $dates[] = strtotime(date('Y-m-d',mktime(0,0,0,$month,($day-$i),$year)));
        }

        return $dates;
    }

   public function getCurrentlyIcons($forecast){

            if ($forecast['currently']['icon'] == 'partly-cloudy-day') {
                $forecast['currently']['icon'] = 'day-sunny-overcast';
            }
            if ($forecast['currently']['icon'] == 'clear-day') {
                $forecast['currently']['icon'] = "day-sunny";
            } elseif ($forecast['currently']['icon'] == 'clear-night') {
                $forecast['currently']['icon'] = "night-clear";
            } elseif ($forecast['currently']['icon'] == 'partly-cloudy-night') {
                $forecast['currently']['icon'] = "night-alt-partly-cloudy";
            } elseif ($forecast['currently']['icon'] == 'cloudy') {
                $forecast['currently']['icon'] = "cloudy";
            } elseif ($forecast['currently']['icon'] == 'rain') {
                $forecast['currently']['icon'] = "rain";
            } elseif ($forecast['currently']['icon'] == 'sleet') {
                $forecast['currently']['icon'] = "sleet";
            } elseif ($forecast['currently']['icon'] == 'snow') {
                $forecast['currently']['icon'] = "snow";
            } elseif ($forecast['currently']['icon'] == 'wind') {
                $forecast['currently']['icon'] = "strong-wind";
            } elseif ($forecast['currently']['icon'] == 'fog') {
                $forecast['currently']['icon'] = "fog";
            }


       $daily = sizeof($forecast['daily']['data']);

       for($i =0; $i < $daily; $i++ ) {

               if ($forecast['daily']['data'][$i]['icon'] == 'partly-cloudy-day') {
                   $forecast['daily']['data'][$i]['icon'] = 'day-sunny-overcast';
               }
               if ($forecast['daily']['data'][$i]['icon'] == 'clear-day') {
                   $forecast['daily']['data'][$i]['icon'] = "day-sunny";
               } elseif ($forecast['daily']['data'][$i]['icon'] == 'clear-night') {
                   $forecast['daily']['data'][$i]['icon'] = "night-clear";
               } elseif ($forecast['daily']['data'][$i]['icon'] == 'partly-cloudy-night') {
                   $forecast['daily']['data'][$i]['icon'] = "night-alt-partly-cloudy";
               } elseif ($forecast['daily']['data'][$i]['icon'] == 'cloudy') {
                   $forecast['daily']['data'][$i]['icon'] = "cloudy";
               } elseif ($forecast['daily']['data'][$i]['icon'] == 'rain') {
                   $forecast['daily']['data'][$i]['icon'] = "rain";
               } elseif ($forecast['daily']['data'][$i]['icon'] == 'sleet') {
                   $forecast['daily']['data'][$i]['icon'] = "sleet";
               } elseif ($forecast['daily']['data'][$i]['icon'] == 'snow') {
                   $forecast['daily']['data'][$i]['icon'] = "snow";
               } elseif ($forecast['daily']['data'][$i]['icon'] == 'wind') {
                   $forecast['daily']['data'][$i]['icon'] = "strong-wind";
               } elseif ($forecast['daily']['data'][$i]['icon'] == 'fog') {
                   $forecast['daily']['data'][$i]['icon'] = "fog";
               }

       }
            return $forecast;

    }


    public function getObservedDailyIcons($observed){

        $item = sizeof($observed);
        for($j=0; $j < $item; $j++ ){

                            foreach ($observed[$j]['value']['daily']['data'] as $i=>$info) {
                                if ($observed[$j]['value']['daily']['data'][$i]['icon'] == 'partly-cloudy-day') {
                                    $observed[$j]['value']['daily']['data'][$i]['icon'] = 'day-sunny-overcast';
                                }
                                if ($observed[$j]['value']['daily']['data'][$i]['icon'] == 'clear-day') {
                                    $observed[$j]['value']['daily']['data'][$i]['icon'] = "day-sunny";
                                } elseif ($observed[$j]['value']['daily']['data'][$i]['icon'] == 'clear-night') {
                                    $observed[$j]['value']['daily']['data'][$i]['icon'] = "night-clear";
                                } elseif ($observed[$j]['value']['daily']['data'][$i]['icon'] == 'partly-cloudy-night') {
                                    $observed[$j]['value']['daily']['data'][$i]['icon'] = "night-partly-cloudy";
                                } elseif ($observed[$j]['value']['daily']['data'][$i]['icon'] == 'cloudy') {
                                    $observed[$j]['value']['daily']['data'][$i]['icon'] = "cloudy";
                                } elseif ($observed[$j]['value']['daily']['data'][$i]['icon'] == 'rain') {
                                    $observed[$j]['value']['daily']['data'][$i]['icon'] = "rain";
                                } elseif ($observed[$j]['value']['daily']['data'][$i]['icon'] == 'sleet') {
                                    $observed[$j]['value']['daily']['data'][$i]['icon'] = "sleet";
                                } elseif ($observed[$j]['value']['daily']['data'][$i]['icon'] == 'snow') {
                                    $observed[$j]['value']['daily']['data'][$i]['icon'] = "snow";
                                } elseif ($observed[$j]['value']['daily']['data'][$i]['icon'] == 'wind') {
                                    $observed[$j]['value']['daily']['data'][$i]['icon'] = "strong-wind";
                                } elseif ($observed[$j]['value']['daily']['data'][$i]['icon'] == 'fog') {
                                    $observed[$j]['value']['daily']['data'][$i]['icon'] = "fog";
                                }

                            }

                }

                return $observed;

        }

}
