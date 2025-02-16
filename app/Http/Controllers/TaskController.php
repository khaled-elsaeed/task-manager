<?php

namespace App\Http\Controllers;

use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Exception;

class TaskController extends Controller
{
    /**
     * Display a list of tasks for the authenticated user.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        try {
            $tasks = Task::where('user_id', Auth::id())->get();
            return response()->json($tasks);
        } catch (Exception $e) {
            Log::error("Failed to fetch tasks: " . $e->getMessage());
            return response()->json(['message' => 'Failed to fetch tasks', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Store a new task for the authenticated user.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        try {
            $request->validate([
                'title' => 'required|string|max:255',
                'status' => 'nullable|in:pending,in-progress,completed',
            ]);

            $task = Task::create([
                'title' => $request->title,
                'status' => $request->status ?? 'pending',
                'user_id' => Auth::id(),
            ]);
            return response()->json($task, 201);
        } catch (Exception $e) {
            Log::error("Task creation failed: " . $e->getMessage());
            return response()->json(['message' => 'Failed to create task', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Update an existing task if the user owns it.
     *
     * @param Request $request
     * @param Task $task
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, Task $task)
    {
        try {
            // Ensure the user owns the task
            if ($task->user_id !== Auth::id()) {
                Log::warning("Unauthorized task update attempt by user: " . Auth::id());
                return response()->json(['error' => 'Unauthorized'], 403);
            }

            $request->validate([
                'title' => 'sometimes|string|max:255',
                'status' => 'sometimes|in:pending,in-progress,completed',
            ]);

            $task->update($request->only(['title', 'status']));
            return response()->json($task);
        } catch (Exception $e) {
            Log::error("Task update failed: " . $e->getMessage());
            return response()->json(['message' => 'Failed to update task', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Delete a task if the user owns it.
     *
     * @param Task $task
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Task $task)
    {
        try {
            // Ensure the user owns the task
            if ($task->user_id !== Auth::id()) {
                Log::warning("Unauthorized task deletion attempt by user: " . Auth::id());
                return response()->json(['error' => 'Unauthorized'], 403);
            }

            $task->delete();
            return response()->json(['message' => 'Task deleted successfully'], 204);
        } catch (Exception $e) {
            Log::error("Task deletion failed: " . $e->getMessage());
            return response()->json(['message' => 'Failed to delete task', 'error' => $e->getMessage()], 500);
        }
    }
}
