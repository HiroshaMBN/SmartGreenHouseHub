<?php

namespace App\Http\Controllers\Stocks;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Exception;
use App\Models\fertilization;
use App\Models\seeds;

class StocksController extends Controller
{
    //fertilization stock get insert
    public function fertilizationStocks(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                "fertilize_name" => "required",
                "availability" => "required",
                "unit_type" => "required|string",
                "next_stock_date" => "string",
                "unit_price" => "",
                "stock_level" => "string"

            ]);
            if ($validator->fails()) {
                return response()->json(["message" => $validator->errors()->all(), "status" => 406]);
            }
            $result = fertilization::create([
                "fertilize_name" => $request->fertilize_name,
                "availability" => $request->availability,
                "unit_type" => $request->unit_type,
                "next_stock_date" => $request->next_stock_date,
                "unit_price" => $request->unit_price,
                "total_price" => $request->availability * $request->unit_price,
                "stock_level" => $request->stock_level,
            ]);

            return response()->json(["message" => "Stock updated", "status" => 200]);
        } catch (Exception $exception) {
            return response()->json(["message" => $exception->getMessage(), "status" => 406]);
        }
    }
    //fertilization stock get insert
    public function seedStocks(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                "seed_name" => "required",
                "availability" => "required",
                "unit_type" => "required|string",
                "next_stock_date" => "string",
                "unit_price" => "",
                "stock_level" => "string"


            ]);
            if ($validator->fails()) {
                return response()->json(["message" => $validator->errors()->all(), "status" => 406]);
            }

            $result = seeds::create([
                "seed_name" => $request->seed_name,
                "availability" => $request->availability,
                "unit_type" => $request->unit_type,
                "next_stock_date" => $request->next_stock_date,
                "unit_price" => $request->unit_price,
                "total_price" => $request->availability * $request->unit_price,
                "stock_level" => $request->stock_level,
            ]);
            return response()->json(["message" => "Seed Stocks updated", "status" => 200]);
        } catch (Exception $exception) {
            return response()->json(["message" => $exception->getMessage(), "status" => 406]);
        }
    }


    //update fertilization stock
    public function updateFertilizationStock(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "fertilize_name" => "required",
            "availability" => "required",
            "nextStock" => "string",
            "unitPrice" => "",
            "stockLevel" => "string"
        ]);
    }


     //show fertilization stocks
    public function showFertilizationStocks(){
        $result = fertilization::all();
        return $result;
    }
    //show seeds stocks
    public function showSeedStocks(){
        $result = seeds::all();
        return $result;
    }
}
