<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Traits\GeneralTrait;
use Illuminate\Http\Request;

class CategoriesController extends Controller
{
    use GeneralTrait;
    public function index() {

       // $categories = Category::select('id','name_'.app() -> getLocale() . ' as name ')->get();
        $categories = Category::selection()->get();
        //return response() -> json($categories);

        return $this -> returnData('categories', $categories);
        
    }

    public function getCategoryById(Request $request) 
    {
        $category = Category::selection()->find($request -> id);
        if(!$category)
          return $this->returnError('001', 'this id is not found');

          return $this->returnData('category',$category);


       // return response() -> json($category);

    }
    public function changeStatus(Request $request)
    {
        Category::where('id',$request -> id) -> update (['active' => $request -> active]);

        return $this -> returnSuccessMessage('Successfully changed status');


       // return response() -> json($category);
    }
}
