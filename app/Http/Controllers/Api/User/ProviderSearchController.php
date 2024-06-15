<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Models\Provider;
use App\Models\favorites;
use App\Traits\GeneralTrait;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class ProviderSearchController extends Controller
{
    use GeneralTrait;
    private $providers;

    public function __construct(){
        $this->providers = DB::table("providers")
            ->select();
//
    }
    /**
     * @throws \Illuminate\Validation\ValidationException
     *//*
    public function SearchProvider(Request $request): \Illuminate\Http\JsonResponse
    {
        $validate = $this->Validate_IsTrue($request);
        $start_date = $request->start_date  ?? null;
        $end_date =  $request->end_date ?? null;
        if($validate->fails()){
            return response()->json(["Error"=>$validate->errors()]);
        }
        if($start_date!==null&&$end_date!==null){
            if(!$this->Check_Date($start_date,$end_date)){
                return response()->json(["Error"=>"The Problem in Date"]);
            }
        }
        try {
        $this->num_values = $this->NumberOfValues($request);
        $this->facilities = $this->Available();
        $this->facilities = $this->Name_Fac($request);
        $this->facilities = $this->Location($request);
        $this->facilities = $this->Type($request);
        $this->facilities = $this->Cost($request);
        $this->facilities = $this->BetweenCost($request);
        $this->facilities = $this->BestRate($request);
        $this->facilities = $this->Rate($request);
        $this->facilities = $this->Num_Guest($request);
        $this->facilities = $this->Num_Room($request);
        $this->facilities = $this->Wifi($request);
        $this->facilities = $this->TV($request);
        $this->facilities = $this->Fridge($request);
        $this->facilities = $this->AirCondition($request);
        $this->facilities = $this->CoffeeMachine($request);
        $finalSearch = $this->Date($request)
            ->orderBy("facilities.".$this->Order($request),"desc")
            ->paginate($this->num_values);
        $FinalAllData = $this->Paginate("facilities",$finalSearch);
        foreach ($FinalAllData["facilities"] as $item){
            $item->photos = DB::table("photos_facility")
                ->select(["photos_facility.id as id_photo","photos_facility.path_photo"])
                ->where("photos_facility.id_facility",$item->id)
                ->get();
            $user = auth("userapi")->user();
            if($user!=null){
                $fav = DB::table("favorites")->where("id_facility",$item->id)->first();
                if($fav!==null){
                    $item->favorite = true;
                }
            }
        }
        return response()->json($FinalAllData);
        }catch (\Exception $exception){
            return response()->json([
                "Error" => $exception->getMessage()
            ]);
        }
    }
}
*/