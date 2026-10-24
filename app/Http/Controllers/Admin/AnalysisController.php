<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Analysis;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AnalysisController extends Controller
{
    /**
     * Every analysis, newest first. Read-only for admin and super_admin.
     */
    public function index(Request $request): View
    {
        $status = $request->query('status');

        $analyses = Analysis::query()
            ->with('user')
            ->when($status, fn ($query) => $query->where('status', $status))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.analyses.index', [
            'analyses' => $analyses,
            'status' => $status,
        ]);
    }
}
