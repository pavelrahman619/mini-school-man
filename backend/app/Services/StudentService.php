<?php

namespace App\Services;

use App\Models\Student;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class StudentService
{
    /**
     * Create a new student.
     *
     * @param array $data Student data
     * @return Student Created student instance
     */
    public function createStudent(array $data): Student
    {
        // Handle photo upload if present
        if (isset($data['photo']) && $data['photo'] instanceof UploadedFile) {
            $data['photo'] = $this->storePhoto($data['photo']);
        }
        
        $student = Student::create($data);
        
        Log::info('Student created', [
            'student_id' => $student->id,
            'student_number' => $student->student_id,
        ]);
        
        return $student;
    }
    
    /**
     * Update an existing student.
     *
     * @param Student $student Student instance to update
     * @param array $data Updated student data
     * @return Student Updated student instance
     */
    public function updateStudent(Student $student, array $data): Student
    {
        // Handle photo upload if present
        if (isset($data['photo']) && $data['photo'] instanceof UploadedFile) {
            // Delete old photo if exists
            if ($student->photo) {
                $this->deletePhoto($student->photo);
            }
            
            $data['photo'] = $this->storePhoto($data['photo']);
        }
        
        $student->update($data);
        
        Log::info('Student updated', [
            'student_id' => $student->id,
            'student_number' => $student->student_id,
        ]);
        
        return $student->fresh();
    }

    /**
     * Upload and store a student photo.
     *
     * @param UploadedFile $photo Photo file to upload
     * @return string Stored file path
     */
    public function uploadPhoto(UploadedFile $photo): string
    {
        return $this->storePhoto($photo);
    }
    
    /**
     * Store photo file in storage.
     *
     * @param UploadedFile $photo Photo file
     * @return string Stored file path
     */
    protected function storePhoto(UploadedFile $photo): string
    {
        // Validate file type
        $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/jpg', 'image/gif', 'image/webp'];
        
        if (!in_array($photo->getMimeType(), $allowedMimeTypes)) {
            throw new \InvalidArgumentException('Invalid photo file type. Only JPEG, PNG, GIF, and WebP are allowed.');
        }
        
        // Validate file size (max 5MB)
        if ($photo->getSize() > 5 * 1024 * 1024) {
            throw new \InvalidArgumentException('Photo file size must not exceed 5MB.');
        }
        
        // Generate unique filename
        $filename = Str::uuid() . '.' . $photo->getClientOriginalExtension();
        
        // Store in public disk under students directory
        $path = $photo->storeAs('students', $filename, 'public');
        
        Log::info('Student photo uploaded', [
            'path' => $path,
            'original_name' => $photo->getClientOriginalName(),
        ]);
        
        return $path;
    }
    
    /**
     * Delete a photo from storage.
     *
     * @param string $path Photo path
     * @return bool Success status
     */
    protected function deletePhoto(string $path): bool
    {
        try {
            if (Storage::disk('public')->exists($path)) {
                Storage::disk('public')->delete($path);
                
                Log::info('Student photo deleted', [
                    'path' => $path,
                ]);
                
                return true;
            }
        } catch (\Exception $e) {
            Log::error('Failed to delete student photo', [
                'path' => $path,
                'error' => $e->getMessage(),
            ]);
        }
        
        return false;
    }
}
