<?php

namespace App\Http\Controllers;

use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Carbon\Carbon;

class DataTableController extends Controller
{
    public function tasksServerSide(Request $request): JsonResponse
    {
        $draw = $request->get('draw', 1);
        $start = $request->get('start', 0);
        $length = $request->get('length', 25);
        $searchValue = $request->get('search')['value'] ?? '';
        
        $query = Task::query();
        
        if (!empty($searchValue)) {
            $query->where(function($q) use ($searchValue) {
                $q->where('title', 'like', "%{$searchValue}%")
                  ->orWhere('description', 'like', "%{$searchValue}%")
                  ->orWhere('status', 'like', "%{$searchValue}%");
            });
        }
        
        if ($request->filled('status_filter')) {
            $query->where('status', $request->status_filter);
        }
        
        $totalRecords = Task::count();
        $filteredRecords = $query->count();
        
        $orderColumnIndex = $request->get('order')[0]['column'] ?? 4;
        $orderDirection = $request->get('order')[0]['dir'] ?? 'desc';
        
        $columns = ['id', 'title', 'status', 'due_at', 'created_at', 'actions'];
        $orderColumn = $columns[$orderColumnIndex] ?? 'created_at';
        
        if ($orderColumn === 'due_at') {
            $query->sortByDueAt($orderDirection);
        } else {
            $query->orderBy($orderColumn, $orderDirection);
        }
        
        $tasks = $query->skip($start)->take($length)->get();
        
        $data = [];
        foreach ($tasks as $index => $task) {
            $rowNumber = $start + $index + 1;
            
            $data[] = [
                'DT_RowId' => $task->id,
                'DT_RowAttr' => [
                    'data-task-id' => $task->id
                ],
                '0' => $rowNumber, // Row number
                '1' => [
                    'title' => $task->title,
                    'description' => $task->description ? \Str::limit($task->description, 100) : null
                ],
                '2' => [
                    'status' => $task->status,
                    'badge_class' => $task->status_badge_class,
                    'is_overdue' => $task->is_overdue,
                    'is_upcoming' => $task->is_upcoming
                ],
                '3' => [
                    'due_at_formatted' => $task->due_at_formatted,
                    'is_overdue' => $task->is_overdue,
                    'is_upcoming' => $task->is_upcoming
                ],
                '4' => $task->created_at->format('d M Y H:i'),
                '5' => $task->id // For actions column
            ];
        }
        
        return response()->json([
            'draw' => intval($draw),
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $filteredRecords,
            'data' => $data
        ]);
    }
}