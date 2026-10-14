<?php
namespace App\Modules\Core\Catalog\Http\Controllers;
use App\Http\Controllers\Controller;
use App\Modules\Core\Catalog\Models\Bank;
use Illuminate\Http\Request;
use Inertia\Inertia;
class BankController extends Controller {
    public function index() { return Inertia::render('Admin/Banks/Index', ['banks' => Bank::orderBy('name')->get(['id','name'])]); }
    public function store(Request $request) {
        $data = $request->validate(['name' => ['required','string','max:100','unique:core_banks,name']]);
        Bank::create($data);
        return back()->with('success', 'Bank ditambahkan ke pilihan Wedding Gift.');
    }
}
