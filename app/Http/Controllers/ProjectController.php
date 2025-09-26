namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProjectController extends Controller
{
    public function index()
    {
        $projects = Auth::user()->projects()->get();
        return response()->json(['success' => true, 'projects' => $projects]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $project = Auth::user()->projects()->create([
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'status' => 'draft',
        ]);

        return response()->json(['success' => true, 'project' => $project]);
    }

    public function show(Project $project)
    {
        $this->authorize('view', $project);
        $project->load(['sketches', 'mockups', 'patterns']);
        return response()->json(['success' => true, 'project' => $project]);
    }
}
