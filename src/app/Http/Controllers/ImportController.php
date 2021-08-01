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

class ImportController extends Controller
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

    public function importJsonRestaurant(Request $request) {
        // Check auth
        $this->checkAuth();
        
        // Check validation
        $validator = Validator::make($request->all(), [
            'file' => 'required|file'
        ]);

        if($validator->fails()){
            return response()->json($validator->errors()->toJson(), 400);
        }

        // Process data import
        if (auth($this->guard)->user()->role_id == config('const.admin')) {
            try {
                DB::beginTransaction();
                $jsonData = json_decode(file_get_contents($request->file('file')), true);
                
                foreach ($jsonData as $value) {
                    // Create company table
                    $location = explode(",", $value['location']);
                    $company = Company::create([
                        'name' => $value['name'],
                        'location' => DB::raw("GeomFromText('POINT(".$location[0]." ".$location[1].")')")
                    ]);
    
                    // Create business hours
                    if (! is_null($value['business_hours'])) {
                        $arrHours = explode(" | ", $value['business_hours']);
                        foreach ($arrHours as $dayHour) {
                            $dayHour = explode(": ", $dayHour);
                            $days = explode(", ", $dayHour[0]);
                            $hour = explode(" - ", $dayHour[1]);
                            
                            foreach ($days as $day) {
                                $businessHours = BusinessHours::create([
                                    'day' => GH::dayToInt($day),
                                    'open_time' => Carbon::parse($hour[0])->format('H:i:s'),
                                    'end_time' => Carbon::parse($hour[1])->format('H:i:s'),
                                    'company_id' => $company->id
                                ]);
                            }
                        }
                    }
    
                    // Create balance
                    $balance = CompanyBalances::create([
                        'ac_code' => config('const.company_cash'),
                        'company_id' => $company->id,
                        'debit' => $value['balance'],
                        'description' => 'FIRST BALANCES'
                    ]);
    
                    // Create product
                    foreach ($value['menu'] as $menu) {
                        $products = Products::create([
                            'name' => $menu['name'],
                            'price' => $menu['price'],
                            'is_active' => 1,
                            'company_id' => $company->id
                        ]);
                    }
                    
                }
    
                DB::commit();
    
                if($company) {
                    return response()->json([
                        "message" => "Import Success"
                    ], 201);
                } else {
                    return response()->json([
                        "message" => "Import Failed"
                    ], 201);
                }             
            } catch (\Exception $e) {
                DB::rollback();
                return response()->json([              
                    'message' => 'Unknown Error',     
                    'error' => $e               
                ], 520);
            }
        }
        else {
            return response()->json([
                "message" => "Permission Denied!"
            ], 201);
        }
    }

    public function importJsonUser(Request $request) {
        // Check auth
        $this->checkAuth();
        
        // Check validation
        $validator = Validator::make($request->all(), [
            'file' => 'required|file'
        ]);

        if($validator->fails()){
            return response()->json($validator->errors()->toJson(), 400);
        }

        // Process data import
        if (auth($this->guard)->user()->role_id == config('const.admin')) {
            // try {
                DB::beginTransaction();
                $jsonData = json_decode(file_get_contents($request->file('file')), true);
                
                foreach ($jsonData as $value) {
                    // Create users table
                    $location = explode(",", $value['location']);
                    $user = User::create([
                        'name' => $value['name'],
                        'email' => GH::randMail(),
                        'password' => GH::randPass(),
                        'role_id' => config('const.admin'),
                        'location' => DB::raw("GeomFromText('POINT(".$location[0]." ".$location[1].")')")
                    ]);
    
                    // Create balance
                    $balance = CustomerBalances::create([
                        'ac_code' => config('const.customer_cash'),
                        'users_id' => $user->id,
                        'debit' => $value['balance'],
                        'description' => 'FIRST BALANCES'
                    ]);
    
                    // Create purchase
                    foreach ($value['purchases'] as $purchase) {
                        $purchases = Purchases::create([
                            'users_id' => $user->id,
                            'company_id' => ,
                            'pr_no' => ,
                            'total' => ,
                            'pay_status' => 
                        ]);

                        $purchaseDetail = PurchaseDetail::create([
                            'purchases_id' => $purchases->id,
                            'product_id' => ,
                            'price' => ,
                            'qty' => 
                        ]);
                    }
                    
                }
    
                DB::commit();
    
                if($user) {
                    return response()->json([
                        "message" => "Import Success"
                    ], 201);
                } else {
                    return response()->json([
                        "message" => "Import Failed"
                    ], 201);
                }             
            // } catch (\Exception $e) {
            //     DB::rollback();
            //     return response()->json([              
            //         'message' => 'Unknown Error',     
            //         'error' => $e               
            //     ], 520);
            // }
        }
        else {
            return response()->json([
                "message" => "Permission Denied!"
            ], 201);
        }
    }
}
