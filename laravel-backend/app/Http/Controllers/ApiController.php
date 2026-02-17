<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Contact;
use App\Models\Company;
use App\Models\Deal;
use App\Models\Task;
use App\Models\Activity;
use App\Models\Product;
use App\Models\Quote;
use App\Models\Invoice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class ApiController extends Controller
{
    // Auth
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => 'Validation error', 'errors' => $validator->errors()], 422);
        }

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(['success' => false, 'message' => 'Invalid credentials'], 401);
        }

        $token = $user->createToken('auth-token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Login successful',
            'data' => [
                'user' => $user,
                'token' => $token
            ]
        ]);
    }

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => 'Validation error', 'errors' => $validator->errors()], 422);
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => $request->password,
            'role' => 'user',
        ]);

        $token = $user->createToken('auth-token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Registration successful',
            'data' => [
                'user' => $user,
                'token' => $token
            ]
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Logout successful'
        ]);
    }

    public function user(Request $request)
    {
        return response()->json([
            'success' => true,
            'data' => $request->user()
        ]);
    }

    // Dashboard
    public function dashboard(Request $request)
    {
        $user = $request->user();

        $stats = [
            'total_contacts' => Contact::where('owner_id', $user->id)->count(),
            'total_companies' => Company::where('owner_id', $user->id)->count(),
            'total_deals' => Deal::where('owner_id', $user->id)->count(),
            'total_deals_value' => Deal::where('owner_id', $user->id)->sum('amount'),
            'won_deals' => Deal::where('owner_id', $user->id)->where('stage', 'Closed Won')->count(),
            'won_deals_value' => Deal::where('owner_id', $user->id)->where('stage', 'Closed Won')->sum('amount'),
            'open_tasks' => Task::where('assigned_to', $user->id)->where('status', '!=', 'completed')->count(),
            'overdue_tasks' => Task::where('assigned_to', $user->id)->where('due_date', '<', now())->where('status', '!=', 'completed')->count(),
            'activities_this_week' => Activity::where('owner_id', $user->id)->where('created_at', '>=', now()->startOfWeek())->count(),
            'deals_by_stage' => Deal::select('stage')
                ->selectRaw('COUNT(*) as count, SUM(amount) as value')
                ->where('owner_id', $user->id)
                ->groupBy('stage')
                ->get()
                ->map(fn($d) => ['stage' => $d->stage, 'count' => $d->count, 'value' => $d->value])
                ->toArray(),
            'recent_activities' => Activity::where('owner_id', $user->id)
                ->latest()
                ->limit(5)
                ->get()
        ];

        return response()->json([
            'success' => true,
            'data' => $stats
        ]);
    }

    // Contacts
    public function contacts(Request $request)
    {
        $query = Contact::with(['company', 'owner'])->where('owner_id', $request->user()->id);

        if ($request->search) {
            $query->where(function($q) use ($request) {
                $q->where('first_name', 'like', "%{$request->search}%")
                  ->orWhere('last_name', 'like', "%{$request->search}%")
                  ->orWhere('email', 'like', "%{$request->search}%");
            });
        }

        $contacts = $query->latest()->paginate($request->per_page ?? 20);

        return response()->json($contacts);
    }

    public function contact(Request $request, $id)
    {
        $contact = Contact::with(['company', 'owner'])->find($id);

        if (!$contact) {
            return response()->json(['success' => false, 'message' => 'Contact not found'], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $contact
        ]);
    }

    public function createContact(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => 'Validation error', 'errors' => $validator->errors()], 422);
        }

        $contact = Contact::create(array_merge($request->all(), ['owner_id' => $request->user()->id]));

        return response()->json([
            'success' => true,
            'message' => 'Contact created successfully',
            'data' => $contact->fresh(['company', 'owner'])
        ]);
    }

    public function updateContact(Request $request, $id)
    {
        $contact = Contact::find($id);

        if (!$contact) {
            return response()->json(['success' => false, 'message' => 'Contact not found'], 404);
        }

        $contact->update($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Contact updated successfully',
            'data' => $contact->fresh(['company', 'owner'])
        ]);
    }

    public function deleteContact(Request $request, $id)
    {
        $contact = Contact::find($id);

        if (!$contact) {
            return response()->json(['success' => false, 'message' => 'Contact not found'], 404);
        }

        $contact->delete();

        return response()->json([
            'success' => true,
            'message' => 'Contact deleted successfully'
        ]);
    }

    // Companies
    public function companies(Request $request)
    {
        $query = Company::with(['owner'])->where('owner_id', $request->user()->id);

        if ($request->search) {
            $query->where('name', 'like', "%{$request->search}%");
        }

        $companies = $query->latest()->paginate($request->per_page ?? 20);

        return response()->json($companies);
    }

    public function company(Request $request, $id)
    {
        $company = Company::with(['owner', 'contacts', 'deals'])->find($id);

        if (!$company) {
            return response()->json(['success' => false, 'message' => 'Company not found'], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $company
        ]);
    }

    public function createCompany(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => 'Validation error', 'errors' => $validator->errors()], 422);
        }

        $company = Company::create(array_merge($request->all(), ['owner_id' => $request->user()->id]));

        return response()->json([
            'success' => true,
            'message' => 'Company created successfully',
            'data' => $company->fresh(['owner'])
        ]);
    }

    public function updateCompany(Request $request, $id)
    {
        $company = Company::find($id);

        if (!$company) {
            return response()->json(['success' => false, 'message' => 'Company not found'], 404);
        }

        $company->update($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Company updated successfully',
            'data' => $company->fresh(['owner'])
        ]);
    }

    public function deleteCompany(Request $request, $id)
    {
        $company = Company::find($id);

        if (!$company) {
            return response()->json(['success' => false, 'message' => 'Company not found'], 404);
        }

        $company->delete();

        return response()->json([
            'success' => true,
            'message' => 'Company deleted successfully'
        ]);
    }

    // Deals
    public function deals(Request $request)
    {
        $query = Deal::with(['contact', 'company', 'owner'])->where('owner_id', $request->user()->id);

        if ($request->search) {
            $query->where('name', 'like', "%{$request->search}%");
        }

        if ($request->stage) {
            $query->where('stage', $request->stage);
        }

        $deals = $query->latest()->paginate($request->per_page ?? 20);

        return response()->json($deals);
    }

    public function deal(Request $request, $id)
    {
        $deal = Deal::with(['contact', 'company', 'owner'])->find($id);

        if (!$deal) {
            return response()->json(['success' => false, 'message' => 'Deal not found'], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $deal
        ]);
    }

    public function createDeal(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'amount' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => 'Validation error', 'errors' => $validator->errors()], 422);
        }

        $deal = Deal::create(array_merge($request->all(), ['owner_id' => $request->user()->id]));

        return response()->json([
            'success' => true,
            'message' => 'Deal created successfully',
            'data' => $deal->fresh(['contact', 'company', 'owner'])
        ]);
    }

    public function updateDeal(Request $request, $id)
    {
        $deal = Deal::find($id);

        if (!$deal) {
            return response()->json(['success' => false, 'message' => 'Deal not found'], 404);
        }

        $deal->update($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Deal updated successfully',
            'data' => $deal->fresh(['contact', 'company', 'owner'])
        ]);
    }

    public function updateDealStage(Request $request, $id)
    {
        $deal = Deal::find($id);

        if (!$deal) {
            return response()->json(['success' => false, 'message' => 'Deal not found'], 404);
        }

        $deal->update(['stage' => $request->stage]);

        return response()->json([
            'success' => true,
            'message' => 'Deal stage updated successfully',
            'data' => $deal->fresh(['contact', 'company', 'owner'])
        ]);
    }

    public function deleteDeal(Request $request, $id)
    {
        $deal = Deal::find($id);

        if (!$deal) {
            return response()->json(['success' => false, 'message' => 'Deal not found'], 404);
        }

        $deal->delete();

        return response()->json([
            'success' => true,
            'message' => 'Deal deleted successfully'
        ]);
    }

    // Tasks
    public function tasks(Request $request)
    {
        $query = Task::with(['assignedTo', 'owner'])->where('owner_id', $request->user()->id);

        if ($request->status) {
            $query->where('status', $request->status);
        }

        if ($request->assigned_to) {
            $query->where('assigned_to', $request->assigned_to);
        }

        $tasks = $query->latest()->paginate($request->per_page ?? 20);

        return response()->json($tasks);
    }

    public function task(Request $request, $id)
    {
        $task = Task::with(['assignedTo', 'owner'])->find($id);

        if (!$task) {
            return response()->json(['success' => false, 'message' => 'Task not found'], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $task
        ]);
    }

    public function createTask(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'due_date' => 'required|date',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => 'Validation error', 'errors' => $validator->errors()], 422);
        }

        $task = Task::create(array_merge($request->all(), [
            'owner_id' => $request->user()->id,
            'assigned_to' => $request->assigned_to ?? $request->user()->id,
        ]));

        return response()->json([
            'success' => true,
            'message' => 'Task created successfully',
            'data' => $task->fresh(['assignedTo', 'owner'])
        ]);
    }

    public function updateTask(Request $request, $id)
    {
        $task = Task::find($id);

        if (!$task) {
            return response()->json(['success' => false, 'message' => 'Task not found'], 404);
        }

        $task->update($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Task updated successfully',
            'data' => $task->fresh(['assignedTo', 'owner'])
        ]);
    }

    public function completeTask(Request $request, $id)
    {
        $task = Task::find($id);

        if (!$task) {
            return response()->json(['success' => false, 'message' => 'Task not found'], 404);
        }

        $task->update(['status' => 'completed']);

        return response()->json([
            'success' => true,
            'message' => 'Task completed successfully',
            'data' => $task->fresh(['assignedTo', 'owner'])
        ]);
    }

    public function deleteTask(Request $request, $id)
    {
        $task = Task::find($id);

        if (!$task) {
            return response()->json(['success' => false, 'message' => 'Task not found'], 404);
        }

        $task->delete();

        return response()->json([
            'success' => true,
            'message' => 'Task deleted successfully'
        ]);
    }

    // Activities
    public function activities(Request $request)
    {
        $query = Activity::with(['owner'])->where('owner_id', $request->user()->id);

        if ($request->type) {
            $query->where('type', $request->type);
        }

        if ($request->related_to_type && $request->related_to_id) {
            $query->where('related_to_type', $request->related_to_type)
                  ->where('related_to_id', $request->related_to_id);
        }

        $activities = $query->latest()->paginate($request->per_page ?? 20);

        return response()->json($activities);
    }

    public function createActivity(Request $request)
    {
        $activity = Activity::create(array_merge($request->all(), ['owner_id' => $request->user()->id]));

        return response()->json([
            'success' => true,
            'message' => 'Activity created successfully',
            'data' => $activity->fresh(['owner'])
        ]);
    }

    public function deleteActivity(Request $request, $id)
    {
        $activity = Activity::find($id);

        if (!$activity) {
            return response()->json(['success' => false, 'message' => 'Activity not found'], 404);
        }

        $activity->delete();

        return response()->json([
            'success' => true,
            'message' => 'Activity deleted successfully'
        ]);
    }

    // Products
    public function products(Request $request)
    {
        $query = Product::where('owner_id', $request->user()->id);

        if ($request->search) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('code', 'like', "%{$request->search}%");
            });
        }

        $products = $query->latest()->paginate($request->per_page ?? 20);

        return response()->json($products);
    }

    public function product(Request $request, $id)
    {
        $product = Product::find($id);

        if (!$product) {
            return response()->json(['success' => false, 'message' => 'Product not found'], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $product
        ]);
    }

    public function createProduct(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:255|unique:products',
            'unit_price' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => 'Validation error', 'errors' => $validator->errors()], 422);
        }

        $product = Product::create(array_merge($request->all(), ['owner_id' => $request->user()->id]));

        return response()->json([
            'success' => true,
            'message' => 'Product created successfully',
            'data' => $product
        ]);
    }

    public function updateProduct(Request $request, $id)
    {
        $product = Product::find($id);

        if (!$product) {
            return response()->json(['success' => false, 'message' => 'Product not found'], 404);
        }

        $product->update($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Product updated successfully',
            'data' => $product
        ]);
    }

    public function deleteProduct(Request $request, $id)
    {
        $product = Product::find($id);

        if (!$product) {
            return response()->json(['success' => false, 'message' => 'Product not found'], 404);
        }

        $product->delete();

        return response()->json([
            'success' => true,
            'message' => 'Product deleted successfully'
        ]);
    }

    // Quotes
    public function quotes(Request $request)
    {
        $query = Quote::with(['contact', 'company', 'owner'])->where('owner_id', $request->user()->id);

        if ($request->status) {
            $query->where('status', $request->status);
        }

        $quotes = $query->latest()->paginate($request->per_page ?? 20);

        return response()->json($quotes);
    }

    public function quote(Request $request, $id)
    {
        $quote = Quote::with(['contact', 'company', 'owner', 'items'])->find($id);

        if (!$quote) {
            return response()->json(['success' => false, 'message' => 'Quote not found'], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $quote
        ]);
    }

    public function createQuote(Request $request)
    {
        $quote = Quote::create(array_merge($request->all(), [
            'owner_id' => $request->user()->id,
            'quote_number' => 'QT-' . time(),
        ]));

        return response()->json([
            'success' => true,
            'message' => 'Quote created successfully',
            'data' => $quote->fresh(['contact', 'company', 'owner'])
        ]);
    }

    public function updateQuote(Request $request, $id)
    {
        $quote = Quote::find($id);

        if (!$quote) {
            return response()->json(['success' => false, 'message' => 'Quote not found'], 404);
        }

        $quote->update($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Quote updated successfully',
            'data' => $quote->fresh(['contact', 'company', 'owner'])
        ]);
    }

    public function deleteQuote(Request $request, $id)
    {
        $quote = Quote::find($id);

        if (!$quote) {
            return response()->json(['success' => false, 'message' => 'Quote not found'], 404);
        }

        $quote->delete();

        return response()->json([
            'success' => true,
            'message' => 'Quote deleted successfully'
        ]);
    }

    // Invoices
    public function invoices(Request $request)
    {
        $query = Invoice::with(['contact', 'company', 'owner'])->where('owner_id', $request->user()->id);

        if ($request->status) {
            $query->where('status', $request->status);
        }

        $invoices = $query->latest()->paginate($request->per_page ?? 20);

        return response()->json($invoices);
    }

    public function invoice(Request $request, $id)
    {
        $invoice = Invoice::with(['contact', 'company', 'owner', 'items', 'payments'])->find($id);

        if (!$invoice) {
            return response()->json(['success' => false, 'message' => 'Invoice not found'], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $invoice
        ]);
    }

    public function createInvoice(Request $request)
    {
        $invoice = Invoice::create(array_merge($request->all(), [
            'owner_id' => $request->user()->id,
            'invoice_number' => 'INV-' . time(),
        ]));

        return response()->json([
            'success' => true,
            'message' => 'Invoice created successfully',
            'data' => $invoice->fresh(['contact', 'company', 'owner'])
        ]);
    }

    public function updateInvoice(Request $request, $id)
    {
        $invoice = Invoice::find($id);

        if (!$invoice) {
            return response()->json(['success' => false, 'message' => 'Invoice not found'], 404);
        }

        $invoice->update($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Invoice updated successfully',
            'data' => $invoice->fresh(['contact', 'company', 'owner'])
        ]);
    }

    public function deleteInvoice(Request $request, $id)
    {
        $invoice = Invoice::find($id);

        if (!$invoice) {
            return response()->json(['success' => false, 'message' => 'Invoice not found'], 404);
        }

        $invoice->delete();

        return response()->json([
            'success' => true,
            'message' => 'Invoice deleted successfully'
        ]);
    }

    // Users (for assignment)
    public function users(Request $request)
    {
        $users = User::where('id', '!=', $request->user()->id)->get();

        return response()->json([
            'success' => true,
            'data' => $users
        ]);
    }

    // File Upload
    public function uploadPhoto(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'photo' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:5048',
            'entity_type' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => 'Validation error', 'errors' => $validator->errors()], 422);
        }

        if ($request->hasFile('photo')) {
            $file = $request->file('photo');
            $filename = time() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs('uploads', $filename, 'public');
            $url = asset('storage/' . $path);

            $entityType = $request->entity_type;
            $entityId = $request->entity_id;

            // Update the entity with the photo URL
            if ($entityId && $entityType) {
                if ($entityType === 'contact') {
                    $contact = Contact::find($entityId);
                    if ($contact) {
                        $contact->update(['avatar' => $url]);
                    }
                } elseif ($entityType === 'company') {
                    $company = Company::find($entityId);
                    if ($company) {
                        $company->update(['logo' => $url]);
                    }
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Photo uploaded successfully',
                'data' => [
                    'url' => $url,
                    'path' => $path
                ]
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'No photo uploaded'
        ], 400);
    }
}
