<?php

namespace App\Http\Controllers;

use App\CryptoMarket;
use App\Market;
use Illuminate\Support\Facades\Cache;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class SiteController extends Controller
{
    private $newsCache = 3*60*60;

    public function notfound(){
        abort(404);
    }

    public function index(){
        $news = Cache::has('latestnews') ? Cache::get('latestnews') : Cache::remember('latestnews', $this->newsCache, function() {
            return $this->loadNews('latest', 5);
        });

        return view('index', [
            'news' => $news->news
        ]);
    }

    public function open_live_account(){
        return view('pages.trading.open_live_account');
    }

    public function open_demo_account(){
        return view('pages.trading.open_demo_account');
    }

    public function copy_trade(){
        return view('pages.investments.copy_trade');
    }

    public function pamm(){
        return view('pages.investments.pamm');
    }

    public function broker(){
        return view('pages.partnership.broker');
    }

    public function affiliate(){
        return view('pages.partnership.affiliate');
    }

    public function forex(){
        $prices = $this->loadPrices('forex', "AUDUSD,EURTRY,EURUSD,GBPUSD,NZDUSD,USDCHF,USDJPY,USDTRY");
        return view('pages.products.forex', [
            'prices' => $prices
        ]);
    }

    public function commodities(){
        $prices = $this->loadPrices('commodities', "BRNTOIL,CRDOIL,USSGR,WHEAT,XAGUSD,XAUUSD");
//        echo "<pre>";
//        print_r($prices);
//        echo "</pre>";
//        exit();
        return view('pages.products.commodities', [
            'prices' => $prices
        ]);
    }

    public function indices(){
        $prices = $this->loadPrices('indices', "all");
//        echo "<pre>";
//        print_r($prices);
//        echo "</pre>";
//        exit();
        return view('pages.products.indices', [
        'prices' => $prices
        ]);
    }

    public function stocks(){
        $prices = $this->loadPrices('stocks', "all");
//        echo "<pre>";
//        print_r($prices);
//        echo "</pre>";
//        exit();
        return view('pages.products.stocks', [
            'prices' => $prices
        ]);
    }

    public function crypto(){
        $prices = $this->loadPrices('crypto', "BTCUSDT,ETHUSDT,LTCUSDT,XLMUSDT,XRPUSDT,BCHUSDT,LINKUSDT");
//        echo "<pre>";
//        print_r($prices);
//        echo "</pre>";
//        exit();
        return view('pages.products.crypto', [
            'prices' => $prices
        ]);
    }

    public function education(){
        return view('pages.education.education');
    }

    public function company(){
        return view('pages.company.company');
    }

    public function account_types(){
        return view('pages.trading.account_types');
    }

    public function economic_calendar(){
        $request_url = env('APIURL') . '/economic_calendar';

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => $request_url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30000,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
        ));
        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);

        if ($err) {
            abort(500);
        }

        $responsejson = json_decode($response);

        return view('pages.analysis.ecocal', [
            'calendarEvents' => $responsejson,
            'current_time' => date('H:i')
        ]);
    }

    public function analysis(Request $request){
        if(!$request->has('symbol')){
            return abort(404);
        }

        $symbol = $request->symbol;

        $cache_key = $symbol . 'analysis';
        $analysisData = Cache::has($cache_key) ? Cache::get($cache_key) : Cache::remember($cache_key, 36000, function() use($symbol) {
            $request_url = env('APIURL') . '/analysis/' . $symbol;
            $curl = curl_init();

            curl_setopt_array($curl, array(
                CURLOPT_URL => $request_url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 30000,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "GET",
            ));
            $response = curl_exec($curl);
            $err = curl_error($curl);
            curl_close($curl);

            if ($err) {
                abort(500);
            }
            $responsejson = json_decode($response);
            return $responsejson;
        });

        if(is_null($analysisData)){
            return abort(404);
        }

        return view('pages.analysis.analysis', [
            'analysisData' => $analysisData,
            'symbol' => $symbol
        ]);
    }

    public function news(){

        $fxNews = Cache::has('latestfxnews') ? Cache::get('latestfxnews') : Cache::remember('latestfxnews', $this->newsCache, function() {
            return $this->loadNews('forex', 6);
        });

        $ecoNews = Cache::has('latestEcoNews') ? Cache::get('latestEcoNews') : Cache::remember('latestEcoNews', $this->newsCache, function() {
            return $this->loadNews('economy', 6);
        });

        $ecoIndicators  = Cache::has('latestEcoInd') ? Cache::get('latestEcoInd') : Cache::remember('latestEcoInd', $this->newsCache, function() {
            return $this->loadNews('economic_indicators', 6);
        });


        return view('pages.news.news', [
            'fx_news' => $fxNews->news,
            'eco_news' => $ecoNews->news,
            'eco_ind' => $ecoIndicators->news,
        ]);
    }

    public function news_detail($newsid){
        $cache_key = $newsid . '_nd';
        $news = Cache::has($cache_key) ? Cache::get($cache_key) : Cache::rememberForever($cache_key, function() use($newsid) {
            return $this->loadNewsDetail($newsid);
        });


        $latestNews = Cache::has('latestnews') ? Cache::get('latestnews') : Cache::remember('latestnews', $this->newsCache, function() {
            return $this->loadNews('latest', 10);
        });

        return view('pages.news.news_detail', [
            'news' => $news,
            'latestNews' => $latestNews->news
        ]);
    }

    private function loadPrices($market, $symbols = "all"){
        $request_url = 'http://ziontrader.com/api/price/' . $market . '?symbols=' . $symbols;

        $headers = array(
            'Content-Type: application/x-www-form-urlencoded',
            'Accept: application/json',
            'x-api-key: kqB32HFostbAWklVxUNGJEQwkRGO4INy'
        );

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => $request_url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30000,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => $headers,
        ));
        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);

        if ($err) {
            abort(500);
        }

        $responsejson = json_decode($response);
        return $responsejson;
    }

    private function loadNews($type, $count){
        $request_url = 'http://ziontrader.com/api/news/' . $type . '?language=en&count=' . $count;

        $headers = array(
            'Content-Type: application/x-www-form-urlencoded',
            'Accept: application/json',
            'x-api-key: kqB32HFostbAWklVxUNGJEQwkRGO4INy'
        );

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => $request_url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30000,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => $headers,
        ));
        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);

        if ($err) {
            abort(500);
        }

        $responsejson = json_decode($response);
        return $responsejson;
    }

    protected function loadNewsDetail($newsid){
        $request_url = 'http://ziontrader.com/api/news/detail/' . $newsid;

        $headers = array(
            'Content-Type: application/x-www-form-urlencoded',
            'Accept: application/json',
            'x-api-key: kqB32HFostbAWklVxUNGJEQwkRGO4INy'
        );

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => $request_url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30000,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => $headers,
        ));
        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);

        if ($err) {
            abort(500);
        }

        $responsejson = json_decode($response);
        return $responsejson;
    }

    public function handle_demo_form(Request $request) {
        $result = $this->handle_account_form($request, 'demo', '0153f78bcdb3');
        //$result = array( 'success' => false, 'reason' => 'Valla oldu');
        return response()->json($result);
    }

    public function handle_live_form(Request $request) {
        $result = $this->handle_account_form($request, 'real', '7790e6073163');
        return response()->json($result);
    }

    protected function handle_account_form(Request $request, $account_type, $source_id){
        $validator = Validator::make($request->all(), [
            'firstname' => 'required',
            'lastname' => 'required',
            'email' => 'required|email',
            'phone' => 'required',
            'password' => 'required',
            'conf_pass' => 'required',
        ]);

        if($validator->fails()){

            return [ 'success' => false, 'reason' => $validator->errors() ];
        }

        $name = $request->input('firstname') . ' ' . $request->input('lastname');
        $phone = preg_replace("/[^0-9]/", "", $request->input('phone'));
        $phone = preg_replace('/\s+/', '', $phone);
        $email = $request->input('email');
        $password = $request->input('password');
        $password_confirmation = $request->input('conf_pass');

        $sca_result = $this->sendToSCA($account_type, $name, $phone, $email, $password, $password_confirmation);
        if(!$sca_result['success']){
            return $sca_result;
        }

        $crm_success = $this->sendToCRM($source_id, $name, $phone, $email);
        if(!$crm_success){
            return [ 'success' => false ];
        }

        return [ 'success' => true ];
    }

    protected function sendToSCA($account_type, $name, $number, $email, $password, $pass_conf){
        $headers = array(
            'Content-Type: application/x-www-form-urlencoded',
            'Accept: application/json'
        );
        $request_url = env("SCAREGISTERURL");
        $post_data = 'name=' . $name . '&account_type=' . $account_type . '&number=' . $number . '&email=' . $email . '&password=' . $password . '&password_confirmation=' . $pass_conf;
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => $request_url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30000,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => $post_data,
            CURLOPT_HTTPHEADER => $headers,
        ));
        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);

        if ($err) {
            return array( 'success' => false, 'reason' => 'A problem occured while creting your account. Please try again later');
        } else {
            $responsedata = json_decode($response);
            Log::info($response);
            if(isset($responsedata->errors)){
                if(isset($responsedata->errors->email)){
                    return array( 'success' => false, 'reason' => 'Email is in use. Please try another email adress.');
                }
                return array( 'success' => false, 'reason' => 'Unknown error');
            }
            return array( 'success' => true );
        }
    }

    protected function sendToCRM($source_id, $name, $number, $email){
        $headers = array(
            'Content-Type: application/x-www-form-urlencoded',
            'Accept: application/json'
        );
        $request_url = env("CRMURL");
        $post_data = 'source_id=' . $source_id . '&name=' . $name . '&phone=' . $number . '&email=' . $email;
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => $request_url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30000,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => $post_data,
            CURLOPT_HTTPHEADER => $headers,
        ));
        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);

        if ($err) {
            return false;
        } else {
            $responsedata = json_decode($response);
            Log::info($response);
//            if($responsedata->errors){
//                return false;
//            }
            return true;
        }
    }
}
