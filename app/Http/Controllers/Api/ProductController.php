<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\{
    User,
    Product
};
use Illuminate\Support\Facades\{Auth,Mail,Response};
use Illuminate\Support\Collection;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $products = Product::with('user')->where('user_id', Auth::id())->get();

        return Response::json([
            'status' => 1,
            'message' => 'Products',
            'data' => $products
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // validation
        $request->validate([
            'name' => 'required',
            'price' => 'required',
            'type' => 'required',
            'status' => 'required',
        ]);

        $product = new Product();

        $product->user_id = Auth::id();
        $product->name = $request->name;
        $product->price = $request->price;
        $product->status = $request->status;
        $product->type = $request->type;

        $product->save();

        try{
            Mail::send('email.emailTemplate', ['product' => $product], function ($message) use ($request) {
                $message->to(Auth()->user()->email, Auth()->user()->name);
                $message->subject('Product created successfully');
            });

            $response = [
                'status' => 1,
                'message' => 'Product created successfully',
            ];

        }catch (\Exception $e) {
            $response = [
                'status' => 0,
                'msg' => sprintf("Product created successfully, but problem in sending mail to the user : %s", $e->getMessage())
            ];
        }

        // send response
        return Response::json($response);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        if(Product::where([
            'id' => $id,
            'user_id' => Auth::id()
        ])->exists())
        {
            $details = Product::with('user')->where([
                'id' => $id,
                'user_id' => Auth::id()
            ])->get();

            return Response::json([
                'status' => 1,
                'message' => 'Product Details',
                'data' => $details
            ]);

        }else{
            return Response::json([
                'status' => 0,
                'message' => 'Product Not Found',
            ], 404);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {

        // validation
        $request->validate([
            'name' => 'required',
            'price' => 'required',
            'type' => 'required',
            'status' => 'required',
        ]);

        if(Product::where([
            'id' => $id,
            'user_id' => Auth::id()
        ])->exists())
        {
            Product::where([
                'id' => $id,
                'user_id' => Auth::id()
            ])->update([
                'name' => $request->name,
                'price' => $request->price,
                'status' => $request->status,
                'type' => $request->type,
            ]);

            // send response
            return Response::json([
                'status' => 1,
                'message' => 'Product updated successfully',
            ]);

        }else{
            return Response::json([
                'status' => 0,
                'message' => 'Product Not Found',
            ], 404);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        if(Product::where([
            'id' => $id,
            'user_id' => Auth::id()
        ])->exists())
        {
            $project = Product::where([
                'id' => $id,
                'user_id' => Auth::id()
            ])->first();

            $project->delete();

            return Response::json([
                'status' => 1,
                'message' => 'Product Deleted Successfully',
            ]);

        }else{
            return Response::json([
                'status' => 0,
                'message' => 'Product Not Found',
            ], 404);
        }
    }

    public function history()
    {
        $products = Product::with('user')->get();

        return Response::json([
            'status' => 1,
            'message' => 'Products',
            'data' => $products
        ]);
    }

    public function list()
    {

        // $products = Product::all()->pluck('user.name', 'name');

        $products = @collect(Product::all())->map(function($item, $key){
            return [
                'product_name' => $item->name,
                'user' => $item->user->name
            ];
        })->toArray();
        // })->toJson(JSON_PRETTY_PRINT);

        // return $products;

        return Response::json([
            'list' => $products
        ]);
    }
}
