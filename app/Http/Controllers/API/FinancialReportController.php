<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\FinancialReport;
use Illuminate\Http\Request;

class FinancialReportController extends Controller
{
    public function index()
    {
        return FinancialReport::all();
    }

    public function store(Request $request)
    {
        $request->validate([
            'owner_id' => 'required|exists:users,id',
            'report_date' => 'required|date',
            'total_revenue' => 'required|numeric',
        ]);

        return FinancialReport::create($request->all());
    }

    public function show(FinancialReport $financialReport)
    {
        return $financialReport;
    }

    public function update(Request $request, FinancialReport $financialReport)
    {
        $request->validate([
            'owner_id' => 'sometimes|exists:users,id',
            'report_date' => 'sometimes|date',
            'total_revenue' => 'sometimes|numeric',
        ]);

        $financialReport->update($request->all());

        return $financialReport;
    }

    public function destroy(FinancialReport $financialReport)
    {
        $financialReport->delete();

        return response()->json(['message' => 'Financial report deleted successfully']);
    }
}
