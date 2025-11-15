<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Services\StudentService;
use App\Http\Resources\StudentResource;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class StudentController extends Controller
{
    protected StudentService $studentService;

    public function __construct(StudentService $studentService)
    {
        $this->studentService = $studentService;
    }

    /**
     * Display a paginated list of students with optional search and filters.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $query = Student::query();

        // Search by name or student_id
        if ($request->has('search') && $request->search) {
            $searchTerm = $request->search;
            $query->where(function ($q) use ($searchTerm) {
                $q->where('name', 'like', "%{$searchTerm}%")
                  ->orWhere('student_id', 'like', "%{$searchTerm}%");
            });
        }

        // Filter by class
        if ($request->has('class') && $request->class) {
            $query->byClass($request->class);
        }

        // Filter by section
        if ($request->has('section') && $request->section) {
            $query->bySection($request->section);
        }

        // Pagination
        $perPage = $request->get('per_page', 15);
        $students = $query->orderBy('name', 'asc')->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => StudentResource::collection($students),
            'meta' => [
                'current_page' => $students->currentPage(),
                'last_page' => $students->lastPage(),
                'per_page' => $students->perPage(),
                'total' => $students->total(),
            ],
        ], 200);
    }

    /**
     * Store a newly created student.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'student_id' => 'required|string|max:50|unique:students,student_id',
            'class' => 'required|string|max:50',
            'section' => 'required|string|max:10',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120', // 5MB max
        ]);

        try {
            $student = $this->studentService->createStudent($validated);

            return response()->json([
                'success' => true,
                'message' => 'Student created successfully',
                'data' => new StudentResource($student),
            ], 201);
        } catch (\Exception $e) {
            Log::error('Failed to create student', [
                'error' => $e->getMessage(),
                'data' => $validated,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to create student',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified student.
     *
     * @param Student $student
     * @return JsonResponse
     */
    public function show(Student $student): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => new StudentResource($student),
        ], 200);
    }

    /**
     * Update the specified student.
     *
     * @param Request $request
     * @param Student $student
     * @return JsonResponse
     */
    public function update(Request $request, Student $student): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'student_id' => 'sometimes|required|string|max:50|unique:students,student_id,' . $student->id,
            'class' => 'sometimes|required|string|max:50',
            'section' => 'sometimes|required|string|max:10',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120', // 5MB max
        ]);

        try {
            $updatedStudent = $this->studentService->updateStudent($student, $validated);

            return response()->json([
                'success' => true,
                'message' => 'Student updated successfully',
                'data' => new StudentResource($updatedStudent),
            ], 200);
        } catch (\Exception $e) {
            Log::error('Failed to update student', [
                'student_id' => $student->id,
                'error' => $e->getMessage(),
                'data' => $validated,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to update student',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove the specified student.
     *
     * @param Student $student
     * @return JsonResponse
     */
    public function destroy(Student $student): JsonResponse
    {
        try {
            // Delete associated photo if exists
            if ($student->photo) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($student->photo);
            }

            $student->delete();

            Log::info('Student deleted', [
                'student_id' => $student->id,
                'student_number' => $student->student_id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Student deleted successfully',
            ], 200);
        } catch (\Exception $e) {
            Log::error('Failed to delete student', [
                'student_id' => $student->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete student',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
