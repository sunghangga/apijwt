<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Products;
use App\Models\CompanyBalances;
use App\Models\BusinessHours;
use App\Models\User;
use App\Models\CustomerBalances;
use App\Models\Purchases;
use App\Models\PurchaseDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use DB;
use Carbon\Carbon;
use App\Helpers\GlobalHelper as GH;

class ShowController extends Controller
{
    public function __construct() {
        $this->middleware('auth:api', ['except' => []]);
        $this->guard = "api";
    }

    public function checkAuth() {
        try {

            if (! $user = JWTAuth::parseToken()->authenticate()) {
                return response()->json(['user_not_found'], 404);
            }

        } catch (Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {

            return response()->json(['token_expired'], $e->getStatusCode());

        } catch (Tymon\JWTAuth\Exceptions\TokenInvalidException $e) {

            return response()->json(['token_invalid'], $e->getStatusCode());

        } catch (Tymon\JWTAuth\Exceptions\JWTException $e) {

            return response()->json(['token_absent'], $e->getStatusCode());

        }
    }

    public function listRestaurantByDatetime(Request $request) {
        // Check auth
        $this->checkAuth();
        
        // Check validation
        $validator = Validator::make($request->all(), [
            'datetime' => 'required|date_format:Y-m-d H:i:s',
            'offset' => 'required|numeric|min:0',
            'limit' => 'required|numeric|min:0'
        ]);

        if($validator->fails()){
            return response()->json($validator->errors()->toJson(), 400);
        }

        // Process data import
        if (auth($this->guard)->user()->role_id == config('const.customer')) {
            $dayTime = Carbon::parse($request->datetime)->isoFormat('dddd hh:mm:ss');
            $arrDayTime = explode(" ", $dayTime);

            $listRestaurant = Company::select('company.name AS restaurant_name', 'business_hours.open_time', 'business_hours.end_time AS close_time')
                                ->join('business_hours', 'company.id', '=', 'business_hours.company_id')
                                ->where('business_hours.day', '=', config('const.day_to_int')[$arrDayTime[0]])
                                ->whereTime('business_hours.open_time', '<=', $arrDayTime[1])
                                ->whereTime('business_hours.end_time', '>=', $arrDayTime[1])
                                ->offset($request->offset)
                                ->limit($request->limit)
                                ->get();
            
            return response()->json([
                "data" => $listRestaurant
            ], 201);           
        }
        else {
            return response()->json([
                "message" => "Permission Denied!"
            ], 201);
        }
    }

    public function listRestaurantByDistance(Request $request) {
        // Check auth
        $this->checkAuth();
        
        // Check validation
        $validator = Validator::make($request->all(), [
            'latitude' => 'between:-90,90',
            'longitude' => 'between:-180,180',
            'offset' => 'required|numeric|min:0',
            'limit' => 'required|numeric|min:0'
        ]);

        if($validator->fails()){
            return response()->json($validator->errors()->toJson(), 400);
        }

        // Process data import
        if (auth($this->guard)->user()->role_id == config('const.customer')) {
            if (is_null($request->latitude) || is_null($request->longitude)) {
                $userId = auth($this->guard)->user()->id;
                $listRestaurant = Company::selectRaw('name AS restaurant_name, ST_Distance(location, (SELECT location FROM users WHERE id = ?)) AS distance', [$userId])
                                ->orderBy('distance', 'asc')
                                ->offset($request->offset)
                                ->limit($request->limit)
                                ->get();
            }
            else {
                $listRestaurant = Company::selectRaw("name AS restaurant_name, ST_Distance(location, GeomFromText('POINT(".$request->latitude." ".$request->longitude.")')) AS distance")
                        ->orderBy('distance', 'asc')
                        ->offset($request->offset)
                        ->limit($request->limit)
                        ->get();
            }
            
            return response()->json([
                "data" => $listRestaurant
            ], 201);           
        }
        else {
            return response()->json([
                "message" => "Permission Denied!"
            ], 201);
        }
    }

    public function listRestaurantByOpenHours(Request $request) {
        // Check auth
        $this->checkAuth();
        
        // Check validation
        $validator = Validator::make($request->all(), [
            'start_range_time' => 'required|numeric|min:0',
            'end_range_time' => 'required|numeric|gte:start_range_time',
            'offset' => 'required|numeric|min:0',
            'limit' => 'required|numeric|min:0'
        ]);

        if($validator->fails()){
            return response()->json($validator->errors()->toJson(), 400);
        }

        // Process data import
        if (auth($this->guard)->user()->role_id == config('const.customer')) {
            $listRestaurant = Company::selectRaw('company.name AS restaurant_name, ST_AsText(company.location) AS location')
                                ->join('business_hours', 'company.id', '=', 'business_hours.company_id')
                                ->whereRaw('ABS(TIME_TO_SEC(TIMEDIFF(business_hours.end_time, business_hours.open_time))) BETWEEN ? and ?', [$request->start_range_time*3600, $request->end_range_time*3600])
                                ->groupBy('restaurant_name', 'location')
                                ->offset($request->offset)
                                ->limit($request->limit)
                                ->get();
            
            return response()->json([
                "data" => $listRestaurant
            ], 201);           
        }
        else {
            return response()->json([
                "message" => "Permission Denied!"
            ], 201);
        }
    }

    public function listRestaurantByPrice(Request $request) {
        // Check auth
        $this->checkAuth();
        
        // Check validation
        $validator = Validator::make($request->all(), [
            'lowest_price' => 'required|numeric|min:0',
            'highest_price' => 'required|numeric|min:0',
            'offset' => 'required|numeric|min:0',
            'limit' => 'required|numeric|min:0'
        ]);

        if($validator->fails()){
            return response()->json($validator->errors()->toJson(), 400);
        }

        // Process data import
        if (auth($this->guard)->user()->role_id == config('const.customer')) {
            $listRestaurant = Company::selectRaw('company.name AS restaurant_name, ST_AsText(company.location) AS location')
                                ->join('products', 'company.id', '=', 'products.company_id')
                                ->where('products.price', '>=', $request->lowest_price)
                                ->where('products.price', '<=', $request->highest_price)
                                ->groupBy('restaurant_name', 'location')
                                ->offset($request->offset)
                                ->limit($request->limit)
                                ->get();
            
            return response()->json([
                "data" => $listRestaurant
            ], 201);           
        }
        else {
            return response()->json([
                "message" => "Permission Denied!"
            ], 201);
        }
    }

    public function listRestaurantDish(Request $request) {
        // Check auth
        $this->checkAuth();
        
        // Check validation
        $validator = Validator::make($request->all(), [
            'search' => 'required',
            'offset' => 'required|numeric|min:0',
            'limit' => 'required|numeric|min:0'
        ]);

        if($validator->fails()){
            return response()->json($validator->errors()->toJson(), 400);
        }

        // Process data import
        if (auth($this->guard)->user()->role_id == config('const.customer')) {
            $listDish = Products::selectRaw('company_id, name, "dish" AS type')
                                ->whereRaw('LOWER(name) LIKE ? ', '%'.strtolower($request->search).'%');
            $listRestaurantDish = Company::selectRaw('id as company_id, name, "company" AS type')
                                ->whereRaw('LOWER(name) LIKE ? ', '%'.strtolower($request->search).'%')
                                ->union($listDish)
                                ->offset($request->offset)
                                ->limit($request->limit)
                                ->get();

            return response()->json([
                "data" => $listRestaurantDish
            ], 201);           
        }
        else {
            return response()->json([
                "message" => "Permission Denied!"
            ], 201);
        }
    }

    public function listRestaurantByDish(Request $request) {
        // Check auth
        $this->checkAuth();
        
        // Check validation
        $validator = Validator::make($request->all(), [
            'dish' => 'required',
            'offset' => 'required|numeric|min:0',
            'limit' => 'required|numeric|min:0'
        ]);

        if($validator->fails()){
            return response()->json($validator->errors()->toJson(), 400);
        }

        // Process data import
        if (auth($this->guard)->user()->role_id == config('const.customer')) {
            $listRestaurant = Company::selectRaw('company.name AS restaurant_name, ST_AsText(company.location) AS location')
                                ->join('products', 'company.id', '=', 'products.company_id')
                                ->whereRaw('LOWER(products.name) LIKE ? ', '%'.strtolower($request->dish).'%')
                                ->offset($request->offset)
                                ->limit($request->limit)
                                ->get();
            
            return response()->json([
                "data" => $listRestaurant
            ], 201);           
        }
        else {
            return response()->json([
                "message" => "Permission Denied!"
            ], 201);
        }
    }

    public function listUserByTransaction(Request $request) {
        // Check auth
        $this->checkAuth();
        
        // Check validation
        $validator = Validator::make($request->all(), [
            'start_date' => 'required|date_format:Y-m-d',
            'end_date' => 'required|date_format:Y-m-d',
            'offset' => 'required|numeric|min:0',
            'limit' => 'required|numeric|min:0'
        ]);

        if($validator->fails()){
            return response()->json($validator->errors()->toJson(), 400);
        }

        // Process data import
        if (auth($this->guard)->user()->role_id == config('const.admin')) {
            $listUsers = User::selectRaw('users.name, SUM(purchases.total) AS grand_total')
                                ->join('purchases', 'users.id', '=', 'purchases.users_id')
                                ->whereBetween('purchases.created_at', [$request->start_date, $request->end_date])
                                ->where('purchases.pay_status', '=', config('const.paid'))
                                ->orderBy('grand_total', 'desc')
                                ->groupBy('purchases.users_id', 'users.name')
                                ->offset($request->offset)
                                ->limit($request->limit)
                                ->get();
            
            return response()->json([
                "data" => $listUsers
            ], 201);           
        }
        else if (auth($this->guard)->user()->role_id == config('const.company')) {
            $listUsers = User::selectRaw('users.name, SUM(purchases.total) AS grand_total')
                                ->join('purchases', 'users.id', '=', 'purchases.users_id')
                                ->whereBetween('purchases.created_at', [$request->start_date, $request->end_date])
                                ->where('purchases.company_id', '=', auth($this->guard)->user()->company_id)
                                ->where('purchases.pay_status', '=', config('const.paid'))
                                ->orderBy('grand_total', 'desc')
                                ->groupBy('purchases.users_id', 'users.name')
                                ->offset($request->offset)
                                ->limit($request->limit)
                                ->get();
            
            return response()->json([
                "data" => $listUsers
            ], 201);           
        }
        else {
            return response()->json([
                "message" => "Permission Denied!"
            ], 201);
        }
    }

    public function listRestaurantByTransaction(Request $request) {
        // Check auth
        $this->checkAuth();
        
        // Check validation
        $validator = Validator::make($request->all(), [
            'start_date' => 'required|date_format:Y-m-d',
            'end_date' => 'required|date_format:Y-m-d',
            'offset' => 'required|numeric|min:0',
            'limit' => 'required|numeric|min:0'
        ]);

        if($validator->fails()){
            return response()->json($validator->errors()->toJson(), 400);
        }

        // Process data import
        if (auth($this->guard)->user()->role_id == config('const.admin') || auth($this->guard)->user()->role_id == config('const.customer')) {
            $listRestaurant = Company::selectRaw('company.name, SUM(purchases.total) AS grand_total, COUNT(purchases.company_id) AS number_of_transaction')
                                ->join('purchases', 'company.id', '=', 'purchases.company_id')
                                ->whereBetween('purchases.created_at', [$request->start_date, $request->end_date])
                                ->where('purchases.pay_status', '=', config('const.paid'))
                                ->orderBy('grand_total', 'desc')
                                ->groupBy('purchases.company_id', 'company.name')
                                ->offset($request->offset)
                                ->limit($request->limit)
                                ->get();
            
            return response()->json([
                "data" => $listRestaurant
            ], 201);           
        }
        else {
            return response()->json([
                "message" => "Permission Denied!"
            ], 201);
        }
    }

    public function totalUserByTransactionAmount(Request $request) {
        // Check auth
        $this->checkAuth();
        
        // Check validation
        $validator = Validator::make($request->all(), [
            'start_date' => 'required|date_format:Y-m-d',
            'end_date' => 'required|date_format:Y-m-d',
            'sign' => 'required|in:<,<=,=,>=,>',
            'amount' => 'required|numeric|min:0'
        ]);

        if($validator->fails()){
            return response()->json($validator->errors()->toJson(), 400);
        }

        // Process data import
        if (auth($this->guard)->user()->role_id == config('const.admin')) {
            $listUsers = Purchases::select('users_id')
                                ->whereBetween('created_at', [$request->start_date, $request->end_date])
                                ->where('total', $request->sign, $request->amount)
                                ->where('pay_status', '=', config('const.paid'))
                                ->groupBy('users_id')
                                ->get();
            
            return response()->json([
                "data" => count($listUsers)
            ], 201);           
        }
        else if (auth($this->guard)->user()->role_id == config('const.company')) {
            $listUsers = Purchases::select('users_id')
                                ->whereBetween('created_at', [$request->start_date, $request->end_date])
                                ->where('total', $request->sign, $request->amount)
                                ->where('company_id', '=', auth($this->guard)->user()->company_id)
                                ->where('pay_status', '=', config('const.paid'))
                                ->groupBy('users_id')
                                ->get();
            
            return response()->json([
                "data" => count($listUsers)
            ], 201);           
        }
        else {
            return response()->json([
                "message" => "Permission Denied!"
            ], 201);
        }
    }

    public function listTransaction(Request $request) {
        // Check auth
        $this->checkAuth();
        
        // Check validation
        $validator = Validator::make($request->all(), [
            'start_date' => 'required|date_format:Y-m-d',
            'end_date' => 'required|date_format:Y-m-d',
            'offset' => 'required|numeric|min:0',
            'limit' => 'required|numeric|min:0'
        ]);

        if($validator->fails()){
            return response()->json($validator->errors()->toJson(), 400);
        }

        // Process data import
        if (auth($this->guard)->user()->role_id == config('const.company')) {
            $listTransaction = Purchases::select('products.name AS dish', 'company.name AS restaurant_name', 'purchase_detail.price', 'purchase_detail.qty', 'purchases.pay_status', 'purchase_detail.created_at AS date')
                                ->join('company', 'company.id', '=', 'purchases.company_id')
                                ->join('purchase_detail', 'purchases.id', '=', 'purchase_detail.purchases_id')
                                ->join('products', 'products.id', '=', 'purchase_detail.product_id')
                                ->whereBetween('purchase_detail.created_at', [$request->start_date, $request->end_date])
                                ->where('purchases.company_id', '=', auth($this->guard)->user()->company_id)
                                ->offset($request->offset)
                                ->limit($request->limit)
                                ->get();
            
            return response()->json([
                "data" => $listTransaction
            ], 201);           
        }
        else if (auth($this->guard)->user()->role_id == config('const.customer')) {
            $listTransaction = Purchases::select('products.name AS dish', 'company.name AS restaurant_name', 'purchase_detail.price', 'purchase_detail.qty', 'purchases.pay_status', 'purchase_detail.created_at AS date')
                                ->join('company', 'company.id', '=', 'purchases.company_id')
                                ->join('purchase_detail', 'purchases.id', '=', 'purchase_detail.purchases_id')
                                ->join('products', 'products.id', '=', 'purchase_detail.product_id')
                                ->whereBetween('purchase_detail.created_at', [$request->start_date, $request->end_date])
                                ->where('purchases.users_id', '=', auth($this->guard)->user()->id)
                                ->offset($request->offset)
                                ->limit($request->limit)
                                ->get();
            
            return response()->json([
                "data" => $listTransaction
            ], 201);            
        }
        else {
            return response()->json([
                "message" => "Permission Denied!"
            ], 201);
        }
    }

    public function checkBalances(Request $request) {
        // Check auth
        $this->checkAuth();

        // Process data import
        if (auth($this->guard)->user()->role_id == config('const.company')) {
            $lastBalances = CompanyBalances::selectRaw('(SUM(debit) - SUM(credit)) AS last_balances')
                                ->where('ac_code', '=', config('const.company_cash'))
                                ->where('company_id', '=', auth($this->guard)->user()->company_id)
                                ->groupBy('company_id')
                                ->get();
            
            return response()->json([
                "data" => $lastBalances
            ], 201);           
        }
        else if (auth($this->guard)->user()->role_id == config('const.customer')) {
            $lastBalances = CustomerBalances::selectRaw('(SUM(debit) - SUM(credit)) AS last_balances')
                                ->where('ac_code', '=', config('const.customer_cash'))
                                ->where('users_id', '=', auth($this->guard)->user()->id)
                                ->groupBy('users_id')
                                ->get();
            
            return response()->json([
                "data" => $lastBalances
            ], 201);           
        }
        else {
            return response()->json([
                "message" => "Permission Denied!"
            ], 201);
        }
    }
}
