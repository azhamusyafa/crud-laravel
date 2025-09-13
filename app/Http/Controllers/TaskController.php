<?php

namespace App\Http\Controllers;

use App\Models\Task;
use Illuminate\Http\Request;
use App\Http\Requests\StoreTaskRequest;
use App\Http\Requests\UpdateTaskRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Carbon\Carbon;

class TaskController extends Controller
{
    public function index(Request $request): View|JsonResponse
    {
        $query = Task::query();
        
        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $query->where(function($q) use ($searchTerm) {
                $q->where('title', 'like', "%{$searchTerm}%")
                  ->orWhere('description', 'like', "%{$searchTerm}%");
            });
        }
        
        if ($request->filled('status')) {
            $query->filterByStatus($request->status);
        }
        
        if ($request->filled('sort') && $request->sort === 'due_at') {
            $order = $request->get('order', 'asc');
            $query->sortByDueAt($order);
        } else {
            $query->latest('created_at');
        }
        
        $perPage = $query->count() > 100 ? 50 : 25;
        $tasks = $query->paginate($perPage)->withQueryString();
        
        $statuses = Task::STATUSES;
        
        $stats = [
            'total' => Task::count(),
            'todo' => Task::where('status', Task::STATUS_TODO)->count(),
            'in_progress' => Task::where('status', Task::STATUS_IN_PROGRESS)->count(),
            'done' => Task::where('status', Task::STATUS_DONE)->count(),
            'overdue' => Task::overdue()->count(),
            'upcoming' => Task::upcoming()->count(),
        ];
        
        if ($request->filled('ajax_stats')) {
            return response()->json(['stats' => $stats]);
        }
        
        return view('tasks.index', compact('tasks', 'statuses', 'stats'));
    }

    public function create(): View
    {
        $task = new Task();
        $statuses = Task::STATUSES;
        
        return view('tasks.create', compact('task', 'statuses'));
    }

    public function store(StoreTaskRequest $request): JsonResponse
    {
        try {
            $validatedData = $request->validated();
            
            if (!empty($validatedData['due_at'])) {
                $validatedData['due_at'] = Carbon::parse($validatedData['due_at']);
            }
            
            $task = Task::create($validatedData);
            
            return response()->json([
                'success' => true,
                'message' => 'Tugas berhasil dibuat!',
                'data' => [
                    'id' => $task->id,
                    'title' => $task->title,
                    'status' => $task->status,
                    'due_at_formatted' => $task->due_at_formatted,
                    'status_badge_class' => $task->status_badge_class,
                    'is_overdue' => $task->is_overdue,
                    'is_upcoming' => $task->is_upcoming,
                ]
            ], 201);
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Data tidak valid',
                'errors' => $e->errors()
            ], 422);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan server',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    public function show(Task $task): View|JsonResponse
    {
        if (request()->ajax()) {
            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $task->id,
                    'title' => $task->title,
                    'description' => $task->description,
                    'status' => $task->status,
                    'due_at' => $task->due_at_input, 
                    'due_at_formatted' => $task->due_at_formatted,
                    'status_badge_class' => $task->status_badge_class,
                    'is_overdue' => $task->is_overdue,
                    'is_upcoming' => $task->is_upcoming,
                    'created_at' => $task->created_at->format('d M Y H:i'),
                    'updated_at' => $task->updated_at->format('d M Y H:i'),
                ]
            ]);
        }
        
        return view('tasks.show', compact('task'));
    }

    public function edit(Task $task): View
    {
        $statuses = Task::STATUSES;
        
        return view('tasks.edit', compact('task', 'statuses'));
    }

    public function update(UpdateTaskRequest $request, Task $task): JsonResponse
    {
        try {
            $validatedData = $request->validated();
            
            if (!empty($validatedData['due_at'])) {
                $validatedData['due_at'] = Carbon::parse($validatedData['due_at']);
            } else {
                $validatedData['due_at'] = null;
            }
            
            $task->update($validatedData);
            
            return response()->json([
                'success' => true,
                'message' => 'Tugas berhasil diperbarui!',
                'data' => [
                    'id' => $task->id,
                    'title' => $task->title,
                    'description' => $task->description,
                    'status' => $task->status,
                    'due_at_formatted' => $task->due_at_formatted,
                    'status_badge_class' => $task->status_badge_class,
                    'is_overdue' => $task->is_overdue,
                    'is_upcoming' => $task->is_upcoming,
                ]
            ]);
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Data tidak valid',
                'errors' => $e->errors()
            ], 422);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan server',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    public function destroy(Task $task): JsonResponse
    {
        try {
            $taskTitle = $task->title;
            $task->delete();
            
            return response()->json([
                'success' => true,
                'message' => "Tugas '{$taskTitle}' berhasil dihapus!"
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus tugas',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    public function toggleStatus(Task $task): JsonResponse
    {
        try {
            $newStatus = match ($task->status) {
                Task::STATUS_TODO => Task::STATUS_IN_PROGRESS,
                Task::STATUS_IN_PROGRESS => Task::STATUS_DONE,
                Task::STATUS_DONE => Task::STATUS_TODO,
            };
            
            $task->update(['status' => $newStatus]);
            
            return response()->json([
                'success' => true,
                'message' => 'Status tugas berhasil diubah!',
                'data' => [
                    'id' => $task->id,
                    'status' => $task->status,
                    'status_badge_class' => $task->status_badge_class,
                ]
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengubah status',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }
}