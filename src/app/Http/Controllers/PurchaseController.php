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

class PurchaseController extends Controller
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

    public function purchaseOrder(Request $request) {
        // Check auth
        $this->checkAuth();
        
        // Check validation
        $validator = Validator::make($request->all(), [
            'data' => 'required',
        ]);

        if($validator->fails()){
            return response()->json($validator->errors()->toJson(), 400);
        }

        // Process data import
        if (auth($this->guard)->user()->role_id == config('const.customer')) {
            try {
                DB::beginTransaction();
                $totalPurchase = 0;
                $totalQty = 0;
                $jsonData = json_decode($request->data);
                
                // Create purchase
                $purchases = Purchases::create([
                    'users_id' => auth($this->guard)->user()->id,
                    'company_id' => $jsonData[0]->restaurant_id,
                    'pr_no' => GH::getPrNo(),
                    'total' => 0,
                    'pay_status' => config('const.paid'),
                    'qty_total' => 0
                ]);

                for ($i = 0; $i < count($jsonData); $i++) {
                    if ($i > 0) {
                        if ($jsonData[$i]->restaurant_id != $jsonData[$i-1]->restaurant_id) {
                            return response()->json([
                                "message" => "Can't Order from Different Restaurant"
                            ], 400);
                        }
                    }
                    // Get products price
                    $products = Products::select('price')
                                          ->where('id', '=', $jsonData[$i]->dish_id)
                                          ->first();
                    // Create purchase detail
                    $purchaseDetail = PurchaseDetail::create([
                        'purchases_id' => $purchases->id,
                        'product_id' => $jsonData[$i]->dish_id,
                        'price' => $products->price,
                        'qty' => $jsonData[$i]->qty
                    ]);
                    
                    $totalPurchase = $totalPurchase + ($jsonData[$i]->qty * $products->price);
                    $totalQty = $totalQty + $jsonData[$i]->qty;
                }

                // Update purchases
                $updatePurchases = Purchases::where('id', '=', $purchases->id)
                                              ->update(['total' => $totalPurchase,
                                                        'qty_total' => $totalQty
                                                        ]);

                // Create company balance
                $companyBalances = CompanyBalances::create([
                    'ac_code' => config('const.company_cash'),
                    'company_id' => $jsonData[0]->restaurant_id,
                    'debit' => $totalPurchase,
                    'refno' => $purchases->pr_no,
                    'description' => 'Purchase with transaction ID '.$purchases->pr_no
                ]);

                // Create customer balance
                $customerBalances = CustomerBalances::create([
                    'ac_code' => config('const.customer_cash'),
                    'users_id' => auth($this->guard)->user()->id,
                    'credit' => $totalPurchase,
                    'refno' => $purchases->pr_no,
                    'description' => 'Purchase with transaction ID '.$purchases->pr_no
                ]);
    
                DB::commit();
    
                if($purchases) {
                    return response()->json([
                        "message" => "Create Purchase Order Success"
                    ], 201);
                } else {
                    return response()->json([
                        "message" => "Create Purchase Order Failed"
                    ], 400);
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
}