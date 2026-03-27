<?php
namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Services\Employee\ResultService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class ResultController extends Controller
{
    protected $resultService;

    public function __construct(ResultService $resultService)
    {
        $this->resultService = $resultService;
    }

    public function index(Request $request)
    {
        $filters = $request->only(['year', 'status', 'keyword']);
        $results = $this->resultService->getMyResults(Auth::id(), $filters);

        return Inertia::render('Employee/Results/Index', [
            'results' => $results,
            'filters' => $filters
        ]);
    }
}