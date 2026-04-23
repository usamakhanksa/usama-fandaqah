<?php

namespace App\Http\Controllers;

use App\Models\WorkspaceTask;
use App\Http\Requests\SaveWorkspaceTaskRequest;
use Illuminate\Http\Request;
use Inertia\Inertia;

class WorkspaceController extends Controller {
    public function index(Request $request) {
        $tasks = WorkspaceTask::query()
            ->when($request->search, fn($q, $s) => $q->search($s))
            ->orderByRaw("FIELD(priority, 'urgent', 'high', 'medium', 'low')")
            ->orderBy('due_at')
            ->paginate(20)
            ->withQueryString();

        return Inertia::render('FrontDesk/Workspace', [
            'tasks' => $tasks,
            'filters' => $request->only(['search'])
        ]);
    }

    public function store(SaveWorkspaceTaskRequest $request) {
        WorkspaceTask::create($request->validated());
        return redirect()->back()->with('success', 'Task created.');
    }

    public function update(SaveWorkspaceTaskRequest $request, WorkspaceTask $workspaceTask) {
        $workspaceTask->update($request->validated());
        return redirect()->back()->with('success', 'Task updated.');
    }

    public function destroy(WorkspaceTask $workspaceTask) {
        $workspaceTask->delete();
        return redirect()->back()->with('success', 'Task removed.');
    }
}
