<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Prompt;
use App\Services\PromptService;
use Illuminate\Http\Request;

class PromptController extends Controller
{
    public function __construct(private readonly PromptService $promptService)
    {
    }

    public function index()
    {
        $prompts = Prompt::query()
            ->orderBy('slug')
            ->orderByDesc('version')
            ->paginate(15);

        return view('admin.prompts.index', compact('prompts'));
    }

    public function create()
    {
        return view('admin.prompts.create', [
            'prompt' => new Prompt(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request);
        $prompt = Prompt::create($data);
        $this->promptService->flush($prompt->slug);

        return redirect()->route('admin.prompts.index')->with('status', 'Prompt created.');
    }

    public function edit(Prompt $prompt)
    {
        return view('admin.prompts.edit', compact('prompt'));
    }

    public function update(Request $request, Prompt $prompt)
    {
        $prompt->update($this->validateData($request, $prompt));
        $this->promptService->flush($prompt->slug);

        return redirect()->route('admin.prompts.index')->with('status', 'Prompt updated.');
    }

    public function destroy(Prompt $prompt)
    {
        $prompt->update(['is_active' => ! $prompt->is_active]);
        $this->promptService->flush($prompt->slug);

        return redirect()->route('admin.prompts.index')->with('status', 'Prompt status updated.');
    }

    public function clone(Prompt $prompt)
    {
        $newPrompt = $prompt->replicate(['version', 'is_active']);
        $newPrompt->version = $prompt->version + 1;
        $newPrompt->is_active = true;
        $newPrompt->save();

        $prompt->update(['is_active' => false]);

        $this->promptService->flush($prompt->slug);

        return redirect()->route('admin.prompts.edit', $newPrompt)->with('status', 'Prompt cloned.');
    }

    private function validateData(Request $request, ?Prompt $prompt = null): array
    {
        $data = $request->validate([
            'slug' => ['required', 'string', 'max:255'],
            'name' => ['required', 'string', 'max:255'],
            'scope' => ['required', 'string', 'max:255'],
            'role' => ['required', 'in:system,user'],
            'version' => ['required', 'integer', 'min:1'],
            'is_active' => ['nullable', 'boolean'],
            'content' => ['required', 'string'],
            'description' => ['nullable', 'string', 'max:255'],
        ]);

        $data['is_active'] = $request->boolean('is_active', $prompt?->is_active ?? true);

        return $data;
    }
}
