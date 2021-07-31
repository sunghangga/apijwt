<?php

namespace App\Http\Controllers;

use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use DB;

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
        // $validator = Validator::make($request->all(), [
        //     'file' => 'required|mimes:json'
        // ]);

        // if($validator->fails()){
        //     return response()->json($validator->errors()->toJson(), 400);
        // }

        // Process data import
        // try {
            DB::beginTransaction();
            $jsonData = json_decode(file_get_contents($request->file('file')), true);
            
            foreach ($jsonData as $value) {
                $location = explode(",", $value['location']);
                $company = Company::create([
                    'name' => $value['name'],
                    'location' => DB::raw("GeomFromText('POINT(".$location[0]." ".$location[1].")')")
                ]);
            }

            DB::commit();

            if(true) {
                return response()->json([
                    "message" => "Import Success",
                    "data" => $jsonData
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
}
