<?php

namespace PhpSoft\ShoppingCart\Controllers;

use Input;
use Validator;
use Illuminate\Http\Request;

use App\Http\Requests;

/**
 * Category REST
 */
class CategoryController extends Controller
{
    private $categoryModel = '';

    /**
     * Construct controller
     */
    public function __construct()
    {
        $this->categoryModel = config('phpsoft.shoppingcart.categoryModel');
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index(Request $request)
    {
        $categoryModel = $this->categoryModel;
        $categories = $categoryModel::browse([
            'order'     => [ Input::get('sort', 'id') => Input::get('direction', 'desc') ],
            'limit'     => ($limit = (int)Input::get('limit', 25)),
            'offset'    => (Input::get('page', 1) - 1) * $limit,
            'cursor'    => Input::get('cursor'),
            'filters'   => $request->all(),
        ]);

        return response()->json(arrayView('phpsoft.shoppingcart::category/browse', [
            'categories' => $categories,
        ]), 200);
    }

    /**
     * Create resource action
     * 
     * @param  Request $request
     * @return Response
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'alias' => 'regex:/^[a-z0-9\-]+/|unique:shop_categories',
            'image' => 'string',
            'description' => 'string',
            'parent_id' => 'numeric' . ($request->parent_id == 0 || $request->parent_id == null ? '' : '|exists:shop_categories,id'),
            'order' => 'numeric',
            'status' => 'numeric',
        ]);

        if ($validator->fails()) {
            return response()->json(arrayView('phpsoft.shoppingcart::errors/validation', [
                'errors' => $validator->errors()
            ]), 400);
        }

        $categoryModel = $this->categoryModel;
        $category = $categoryModel::create($request->all());

        return response()->json(arrayView('phpsoft.shoppingcart::category/read', [
            'category' => $category
        ]), 201);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function show($id)
    {
        $categoryModel = $this->categoryModel;
        $category = $categoryModel::findByIdOrAlias($id);

        if (empty($category)) {
            return response()->json(null, 404);
        }

        return response()->json(arrayView('phpsoft.shoppingcart::category/read', [
            'category' => $category
        ]), 200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  int     $id
     * @param  Request $request
     * @return Response
     */
    public function update($id, Request $request)
    {
        $categoryModel = $this->categoryModel;
        $category = $categoryModel::find($id);

        // check exists
        if (empty($category)) {
            return response()->json(null, 404);
        }

        // validate
        $validator = Validator::make($request->all(), [
            'name' => 'string',
            'alias' => 'regex:/^[a-z0-9\-]+/|unique:shop_categories,alias,' . $category->id,
            'image' => 'string',
            'description' => 'string',
            'parent_id' => 'numeric|not_in:' . $id . ($request->parent_id == 0 || $request->parent_id == null ? '' : '|exists:shop_categories,id'),
            'order' => 'numeric',
            'status' => 'numeric',
        ]);
        if ($validator->fails()) {
            return response()->json(arrayView('phpsoft.shoppingcart::errors/validation', [
                'errors' => $validator->errors()
            ]), 400);
        }

        // update
        $category = $category->update($request->all());

        // respond
        return response()->json(arrayView('phpsoft.shoppingcart::category/read', [
            'category' => $category
        ]), 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function destroy($id)
    {
        $categoryModel = $this->categoryModel;

        // retrieve category
        $category = $categoryModel::find($id);

        // check exists
        if (empty($category)) {
            return response()->json(null, 404);
        }

        if (!$category->delete()) {
            return response()->json(null, 500);
        }

        return response()->json(null, 204);
    }
}
