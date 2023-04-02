<?php

namespace App\Http\Controllers\Api;

use App\Models\Order;
use App\Models\Invoice;
use App\Models\Product;
use App\Models\Customer;
use Illuminate\Http\Request;
use App\Events\ProductCreated;
use App\Events\ProductUpdated;
use App\Models\InvoiceProduct;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Http\Requests\ProductRequest;
use App\Models\Company;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;

class DashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum')->except('searchProducts');
    }
    //about product
    public function getproducts(Request $request) {
        $products = Product::paginate(7);
        $lastPage = $products->lastPage();

        return response()->json([
            "products" => $products,
            "lastPage" => $lastPage
        ]);
    }
    //create product
    public function createproduct(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'stock_quantity' => 'required|integer|min:0',
            'price' => 'required|numeric|min:0',
            'cost' => 'required|numeric|min:0',
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);
        // dd($validatedData);
        $validatedData = $validator->validated();

        if ($validator->fails()) {
            return response()->json([
                'error' => $validator->errors(),
            ], 422);
        }
        DB::beginTransaction();

        try {
            if ($request->hasFile("image")) {
                $file = $request->file("image");
                $path = $file->hashName();
                $file->store('public/products');
            }
            $product = new Product([
                'name' => $validatedData['name'],
                'description' => $validatedData['description'],
                'stock_quantity' => $validatedData['stock_quantity'],
                'price' => $validatedData['price'],
                'cost' => $validatedData['cost'],
                "image" => 'storage/products/'.$path
            ]);

            $product->save();
            event(new ProductCreated($product));

            DB::commit();

            return response()->json([
                "success" => "Product created successfully",
                "product_id" => $product->id
            ], 201);
        } catch (\Exception $e) {
            DB::rollback();

            return response()->json([
                "error" => "Failed to create product"
            ], 422);
        }
    }

    // public function show($id) {

    // }
    public function edit($id) {
        $product = Product::findorFail($id);
        return response()->json([
            "Oneproduct" => $product
        ]);
    }

    public function update(ProductRequest $request, $id) {
        $product = Product::findOrFail($id);
        DB::beginTransaction();
        try {
            //code...
        } catch (\Exception $e) {
            //throw $th;
        }
    }
    public function destroy($id) {
        $product = Product::findOrFail($id);
        $product->delete();
        return response()->json([
            "success" => "Product deleted successfully"
        ]);
    }

    public function searchProducts(Request $request)
    {
        $query = $request->query('dataSearch');

        if($query) {
            $products = Product::where('name', 'like', "%$query%")
            ->orWhere('description', 'like', "%$query%")
            ->orWhere('price', 'like', "%$query%")
            ->orWhere('cost', 'like', "%$query%")
            ->get();
        }

        return response()->json(['products' => $products ?? null]);
    }

    // about customer
    public function getCustomers() {
        // $lastOrderSubquery = Order::select('id')
        //     ->where('customer_id', $request->customer_id)
        //     ->latest('created_at')
        //     ->limit(1)
        //     ->first();
        //             // dd($lastOrderSubquery->id);
        // $lastInvoice = Invoice::join('orders', 'invoices.order_id', '=', 'orders.id')
        //     ->whereRaw("orders.id = ($lastOrderSubquery->id)")
        //     ->first();
        // return response()->json([
        //     "IsHasInvois" => $lastInvoice->id ?? null
        // ]);
        // $customers = Customer::with(['orders' => function ($query) {
        //     // Get the last order for each customer
        //     $query->latest("created_at")->get();
        //     dd($query);
        // }, 'orders.invoices' => function ($query) {
        //     // Get the last invoice for each order
        //     $query->latest("created_at")->limit(1);
        // }])->get();
        $customers = Customer::with("orders")->get();
        // dd($customers);
        // $lastPage = $customers->lastPage();

        return response()->json([
            "customers" => $customers,
            // "lastPage" => $lastPage
        ]);
    }
    public function showInvoice($id) {
        $customer = Customer::with(['orders' => function ($query) {
            $query->latest()->take(1);
        }])->findOrFail($id);
    
        return response()->json([
            "customer" => $customer,
        ]);
    }
    public function createCustomer(Request $request) {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'adress' => 'required|string',
            'email' => 'required|string',
            'city' => 'required|string',
            'postal_code' => 'required',
            "phone" => 'required|string'
        ]);
        
        $validatedData = $validator->validated();
        
        if ($validator->fails()) {
            return response()->json([
                'error' => $validator->errors(),
            ], 422);
        }
        DB::beginTransaction();

        try {
            $customer = new Customer([
                'name' => $validatedData['name'],
                'address' => $validatedData['adress'],
                'other_address' => $request->other_adress,
                'email' => $validatedData['email'],
                'phone' => $validatedData['phone'],
                'postal_code' => $validatedData['postal_code'],
                'city' => $validatedData['city'],
            ]);
            $customer->save();

            DB::commit();
            return response()->json([
                "success" => "Customer created successfully",
                "customer_id" => $customer->id
            ], 201);
        } catch (\Exception $e) {
            DB::rollback();

            return response()->json([
                "error" => "Failed to create product",
            ], 422);
        }
    }
    public function searchCustomer(Request $request)
    {
        $query = $request->query('customerSearch');

        if($query) {
            $customers = Customer::where('name', 'like', "%$query%")
            ->orWhere('email', 'like', "%$query%")
            ->orWhere('phone', 'like', "%$query%")
            ->orWhere('address', 'like', "%$query%")
            ->orWhere('city', 'like', "%$query%")
            ->get();
        }

        return response()->json(['customers' => $customers ?? null]);
    }
    public function getCustomerAnalytics(Request $request) {

        // if($request->Days === 7) {
            $class = "";
            $lastSevenDays = Carbon::now()->subDays($request->Days);
            $lastforteendays =  Carbon::now()->subDays($request->Days * 2);
            $lastCustomer = Customer::where('created_at', '>=', $lastSevenDays)
                            ->count();
            $num_prev_customers = Customer::where('created_at', '<=', $lastSevenDays)
                            ->where('created_at', '>=', $lastforteendays)
                            ->count();
            if ($num_prev_customers !== 0) {
                $percent = (($lastCustomer - $num_prev_customers) / $num_prev_customers) * 100;
                $percent = ($percent > 100) ? 100 : $percent;
                if($percent < 0) {
                    $percent = -($percent);
                    $class = "text-danger";
                } else {
                    $percent = $percent;
                    $class = "text-green";
                }
            } else {
                $percent = 0;
                $class = "text-yellow";
            }
            return response()->json([
                "lastCustomer" => $lastCustomer,
                "percent" => number_format($percent),
                "class" => $class
            ]);
        // }
    }
    public function getCustomerCharts(Request $request)
    {
        $lastDate = Carbon::now()->subDays($request->Days);
        $customers = Customer::where('created_at', '>=', $lastDate)
            ->groupBy('date')
            ->orderBy('date')
            ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->get();

        $labels = [];
        $counts = [];

        foreach ($customers as $customer) {
            $labels[] = $customer->date;
            $counts[] = $customer->count;
        }

        return response()->json([
            'labels' => $labels,
            'counts' => $counts,
        ]);
    }

    //about order
    public function createOrder(Request $request) {
        $validator = Validator::make($request->all(), [
            'customer_id' => 'required',
        ]);

        $validatedData = $validator->validated();

        if($validator->fails()) {
            return response()->json([
                "error" => "Please fill out all data correctly"
            ], 422);
        }
        DB::beginTransaction();

        try {
            $customer = new Order([
                'customer_id' => $validatedData['customer_id']
            ]);
            $customer->save();

            DB::commit();
            return response()->json([
                "success" => "Order created successfully",
                "order_id" => $customer->id
            ], 200);
        } catch (\Exception $e) {
            DB::rollback();

            return response()->json([
                "error" => "Failed to create product",
            ], 422);
        }
    }
    public function hasInvoice(Request $request) {
        $lastOrderSubquery = Order::select('id')
            ->where('customer_id', $request->customer_id)
            ->latest('created_at')
            ->limit(1)
            ->first();
                    // dd($lastOrderSubquery->id);
        $lastInvoice = Invoice::join('orders', 'invoices.order_id', '=', 'orders.id')
            ->whereRaw("orders.id = ($lastOrderSubquery->id)")
            ->first();
        return response()->json([
            "IsHasInvois" => $lastInvoice->id ?? null
        ]);

        
    }
    //about invoice
    public function createInvoice(Request $request) {
        $validator = Validator::make($request->all(), [
            'customer_id' => 'required',
            // 'order_id' => 'required',
            'total_amount' => 'required',
            'invoices' => 'required|array',
        ]);

        
        if ($validator->fails()) {
            return response()->json([
                'error' => $validator->errors(),
            ], 422);
        }

        DB::beginTransaction();
        
        try {
            $validatedData = $validator->validated();

            $order = new Order([
                'customer_id' => $request->customer_id
            ]);
            $order->save();
            
            $invoice = new Invoice([
                'customer_id' => $request->customer_id,
                'order_id' => $order->id,
                'total_amount' => $request->total_amount,
            ]);
            $invoice->save();
            $requestInvoices = $request->invoices; // Store the invoices array in a variable

            if ($invoice) {
                $invoices = [];
                foreach ($requestInvoices as $invoiceData) {
                    $invoices[] = [
                        'invoice_id' => $invoice->id,
                        'product_id' => $invoiceData["product_id"],
                        'quantity' => $invoiceData["quantity"]
                    ];
                }
                InvoiceProduct::insert($invoices); // Use insert() method to insert multiple records
            }

            DB::commit();
            return response()->json([
                "success" => "Order created successfully",
                "order_id" => $invoice->id
            ], 200);
        } catch (\Exception $e) {
            DB::rollback();

            return response()->json([
                "error" => "Failed to create product",
                'customer_id' => $request->customer_id,
                'order_id' => $request->order_id,
                'total_amount' => $request->total_amount,
                "invoices" => $request->invoices[0]["product_id"]
            ], 422);
        }
    }
    //create company
    public function createCompany(Request $request) {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'adress' => 'required|string',
            'email' => 'required|string',
            'city' => 'required|string',
            'postal_code' => 'required',
            "phone" => 'required|string',
            "rib" => 'required|string'
        ]);
        
        $validatedData = $validator->validated();
        
        if ($validator->fails()) {
            return response()->json([
                'error' => $validator->errors(),
            ], 422);
        }
        DB::beginTransaction();

        try {
            Company::updateOrCreate(
                ['name' => $validatedData['name']],
                [
                    'address' => $validatedData['adress'],
                    'email' => $validatedData['email'],
                    'phone' => $validatedData['phone'],
                    'postal_code' => $validatedData['postal_code'],
                    'RIB' => $validatedData['rib'],
                    'city' => $validatedData['city'],
                ]
            );            

            DB::commit();
            return response()->json([
                "success" => "Company created successfully",
                // "customer_id" => $customer->id
            ], 201);
        } catch (\Exception $e) {
            DB::rollback();

            return response()->json([
                "error" => "please validate you data or check later",
                "data" => $validatedData['email']
            ], 422);
        }
    }
    //payments
    public function getallclienthasinvoice(Request $request) {
        $lastOrdersForClients = Order::select('*')
                                ->from(DB::raw('(SELECT MAX(created_at) as last_order_date, customer_id FROM orders GROUP BY customer_id) as sub'))
                                ->get();

                                    $js = [];
                                foreach($lastOrdersForClients as $last) {
                                    // dump($last);
                                    $clients = Customer::where("id", "in", $last->customer_id)
                                            // ->where("orders.created_at", ">", )
                                            ->join("invoices", "invoices.order_id", $last->id)
                                            ->get();
                            
                                }
                                //  return "yes";   
        return response()->json([
                $clients
        ]);
        }
}
