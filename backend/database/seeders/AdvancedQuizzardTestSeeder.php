<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;

class AdvancedQuizzardTestSeeder extends Seeder
{
    public function run(): void
    {
        DB::beginTransaction();

        try {
            $now = now();

            // -------------------------------------------------
            // 1) TEACHER
            // -------------------------------------------------
            $teacherId = $this->firstOrCreateUser([
                'name' => 'Reports Teacher',
                'email' => 'reportsteacher@quizzard.com',
                'password' => Hash::make('Teacher@1234'),
                'role' => 'teacher',
                'status' => 'active',
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            // -------------------------------------------------
            // 2) STUDENTS
            // -------------------------------------------------
            $studentIds = [];

            for ($i = 1; $i <= 40; $i++) {
                $studentIds[] = $this->firstOrCreateUser([
                    'name' => "Student $i",
                    'email' => "student{$i}@quizzardtest.com",
                    'password' => Hash::make('Student@1234'),
                    'role' => 'student',
                    'status' => 'active',
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }

            // -------------------------------------------------
            // 3) CLASSES
            // -------------------------------------------------
            $classAId = $this->createClassRecord($teacherId, [
                'name' => 'Reports Test Class A',
                'description' => 'Advanced seeded class for reports testing',
                'class_code' => 'RPTA101',
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            $classBId = $this->createClassRecord($teacherId, [
                'name' => 'Reports Test Class B',
                'description' => 'Advanced seeded class for results testing',
                'class_code' => 'RPTB102',
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            // -------------------------------------------------
            // 4) ENROLL STUDENTS INTO CLASSES
            // -------------------------------------------------
            $first20 = array_slice($studentIds, 0, 20);
            $last20  = array_slice($studentIds, 20, 20);

            foreach ($first20 as $studentId) {
                $this->attachStudentToClass($classAId, $studentId, $now);
            }

            foreach ($last20 as $studentId) {
                $this->attachStudentToClass($classBId, $studentId, $now);
            }

            foreach (array_slice($studentIds, 5, 5) as $studentId) {
                $this->attachStudentToClass($classBId, $studentId, $now);
            }

            // -------------------------------------------------
            // 5) QUIZZES
            // -------------------------------------------------
            $quiz1Id = $this->createQuizRecord($teacherId, [
                'title' => 'Reports Demo Quiz 1',
                'description' => 'Used for ranking, pass/fail, and search testing',
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            $quiz2Id = $this->createQuizRecord($teacherId, [
                'title' => 'Reports Demo Quiz 2',
                'description' => 'Used for tie scores and sorting tests',
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            $quiz3Id = $this->createQuizRecord($teacherId, [
                'title' => 'Reports Demo Quiz 3',
                'description' => 'Used for class and quiz assignment testing',
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            // -------------------------------------------------
            // 6) ASSIGN QUIZZES TO CLASSES
            // -------------------------------------------------
            $this->assignQuizToClass($classAId, $quiz1Id, $now);
            $this->assignQuizToClass($classAId, $quiz2Id, $now);
            $this->assignQuizToClass($classBId, $quiz2Id, $now);
            $this->assignQuizToClass($classBId, $quiz3Id, $now);

            // -------------------------------------------------
            // 7) ATTEMPTS FOR QUIZ 1
            // -------------------------------------------------
            $quiz1Scores = [
                20, 19, 18, 18, 17, 16, 15, 14, 13, 12,
                12, 11, 10, 10, 9, 8, 7, 6, 5, 4,
            ];
            $this->seedAttemptsForQuiz($quiz1Id, $first20, $quiz1Scores, 20, Carbon::now()->subDays(2));

            // -------------------------------------------------
            // 8) ATTEMPTS FOR QUIZ 2
            // -------------------------------------------------
            $quiz2StudentPool = array_slice($studentIds, 10, 20);
            $quiz2Scores = [
                15, 15, 15, 14, 14, 13, 13, 12, 12, 11,
                10, 10, 9, 9, 8, 8, 7, 6, 5, 3,
            ];
            $this->seedAttemptsForQuiz($quiz2Id, $quiz2StudentPool, $quiz2Scores, 15, Carbon::now()->subDay());

            // -------------------------------------------------
            // 9) ATTEMPTS FOR QUIZ 3
            // -------------------------------------------------
            $quiz3Scores = [
                25, 23, 22, 21, 20, 19, 18, 17, 16, 15,
                14, 13, 12, 11, 10, 9, 8, 7, 6, 5,
            ];
            $this->seedAttemptsForQuiz($quiz3Id, $last20, $quiz3Scores, 25, Carbon::now()->subHours(18));

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    private function firstOrCreateUser(array $data): int
    {
        $existing = DB::table('users')->where('email', $data['email'])->first();

        if ($existing) {
            $update = [
                'name' => $data['name'],
                'updated_at' => now(),
            ];

            if (Schema::hasColumn('users', 'password')) {
                $update['password'] = $data['password'];
            }
            if (Schema::hasColumn('users', 'role')) {
                $update['role'] = $data['role'];
            }
            if (Schema::hasColumn('users', 'status')) {
                $update['status'] = $data['status'];
            }

            DB::table('users')->where('id', $existing->id)->update($update);

            return (int) $existing->id;
        }

        $insert = [
            'name' => $data['name'],
            'email' => $data['email'],
            'created_at' => $data['created_at'],
            'updated_at' => $data['updated_at'],
        ];

        if (Schema::hasColumn('users', 'password')) {
            $insert['password'] = $data['password'];
        }
        if (Schema::hasColumn('users', 'role')) {
            $insert['role'] = $data['role'];
        }
        if (Schema::hasColumn('users', 'status')) {
            $insert['status'] = $data['status'];
        }

        return DB::table('users')->insertGetId($insert);
    }

    private function createClassRecord(int $teacherId, array $data): int
    {
        $table = 'classes';

        $existing = DB::table($table)->where('name', $data['name'])->first();

        if ($existing) {
            DB::table($table)->where('id', $existing->id)->update([
                'teacher_id' => $teacherId,
                'description' => $data['description'],
                'class_code' => $data['class_code'],
                'updated_at' => now(),
            ]);

            return (int) $existing->id;
        }

        return DB::table($table)->insertGetId([
            'teacher_id' => $teacherId,
            'name' => $data['name'],
            'description' => $data['description'],
            'class_code' => $data['class_code'],
            'created_at' => $data['created_at'],
            'updated_at' => $data['updated_at'],
        ]);
    }

    private function attachStudentToClass(int $classId, int $studentId, $now): void
    {
        $pivotTable = $this->guessExistingTable([
            'class_students',
            'class_student',
            'class_user',
        ]);

        if (!$pivotTable) {
            return;
        }

        $row = [];
        if (Schema::hasColumn($pivotTable, 'class_id')) {
            $row['class_id'] = $classId;
        }
        if (Schema::hasColumn($pivotTable, 'student_id')) {
            $row['student_id'] = $studentId;
        } elseif (Schema::hasColumn($pivotTable, 'user_id')) {
            $row['user_id'] = $studentId;
        }

        if (Schema::hasColumn($pivotTable, 'created_at')) {
            $row['created_at'] = $now;
        }
        if (Schema::hasColumn($pivotTable, 'updated_at')) {
            $row['updated_at'] = $now;
        }

        if (empty($row)) {
            return;
        }

        $query = DB::table($pivotTable)->where('class_id', $classId);
        if (isset($row['student_id'])) {
            $query->where('student_id', $studentId);
        } elseif (isset($row['user_id'])) {
            $query->where('user_id', $studentId);
        }

        if (!$query->exists()) {
            DB::table($pivotTable)->insert($row);
        }
    }

    private function createQuizRecord(int $teacherId, array $data): int
    {
        $table = 'quizzes';

        $existing = DB::table($table)->where('title', $data['title'])->first();

        if ($existing) {
            DB::table($table)->where('id', $existing->id)->update([
                'teacher_id'   => $teacherId,
                'description'  => $data['description'],
                'is_published' => true,
                'updated_at'   => now(),
            ]);

            return (int) $existing->id;
        }

        return DB::table($table)->insertGetId([
            'teacher_id'   => $teacherId,
            'title'        => $data['title'],
            'description'  => $data['description'],
            'is_published' => true,
            'created_at'   => $data['created_at'],
            'updated_at'   => $data['updated_at'],
        ]);
    }

    private function assignQuizToClass(int $classId, int $quizId, $now): void
    {
        $pivotTable = $this->guessExistingTable([
            'class_quizzes',
            'class_quiz',
        ]);

        if (!$pivotTable) {
            return;
        }

        $row = [];
        if (Schema::hasColumn($pivotTable, 'class_id')) {
            $row['class_id'] = $classId;
        }
        if (Schema::hasColumn($pivotTable, 'quiz_id')) {
            $row['quiz_id'] = $quizId;
        }

        if (Schema::hasColumn($pivotTable, 'created_at')) {
            $row['created_at'] = $now;
        }
        if (Schema::hasColumn($pivotTable, 'updated_at')) {
            $row['updated_at'] = $now;
        }

        $exists = DB::table($pivotTable)
            ->where('class_id', $classId)
            ->where('quiz_id', $quizId)
            ->exists();

        if (!$exists) {
            DB::table($pivotTable)->insert($row);
        }
    }

    private function seedAttemptsForQuiz(
        int $quizId,
        array $studentIds,
        array $scores,
        int $totalPoints,
        Carbon $baseTime
    ): void {
        $table = $this->guessExistingTable([
            'quiz_attempts',
            'attempts',
        ]);

        if (!$table) {
            return;
        }

        foreach ($studentIds as $index => $studentId) {
            $score = $scores[$index] ?? rand(0, $totalPoints);

            $startedAt = $baseTime->copy()->addMinutes($index * 6);
            $completedAt = $startedAt->copy()->addMinutes(rand(5, 25));

            $alreadyExists = DB::table($table)
                ->where('quiz_id', $quizId)
                ->where(function ($q) use ($studentId, $table) {
                    if (Schema::hasColumn($table, 'student_id')) {
                        $q->where('student_id', $studentId);
                    } elseif (Schema::hasColumn($table, 'user_id')) {
                        $q->where('user_id', $studentId);
                    }
                })
                ->exists();

            if ($alreadyExists) {
                continue;
            }

            $insert = [
                'quiz_id' => $quizId,
            ];

            if (Schema::hasColumn($table, 'student_id')) {
                $insert['student_id'] = $studentId;
            } elseif (Schema::hasColumn($table, 'user_id')) {
                $insert['user_id'] = $studentId;
            }

            if (Schema::hasColumn($table, 'score')) {
                $insert['score'] = $score;
            }
            if (Schema::hasColumn($table, 'total_points')) {
                $insert['total_points'] = $totalPoints;
            }
            if (Schema::hasColumn($table, 'status')) {
                $insert['status'] = 'completed';
            }
            if (Schema::hasColumn($table, 'started_at')) {
                $insert['started_at'] = $startedAt;
            }
            if (Schema::hasColumn($table, 'completed_at')) {
                $insert['completed_at'] = $completedAt;
            }
            if (Schema::hasColumn($table, 'created_at')) {
                $insert['created_at'] = $startedAt;
            }
            if (Schema::hasColumn($table, 'updated_at')) {
                $insert['updated_at'] = $completedAt;
            }

            DB::table($table)->insert($insert);
        }
    }

    private function guessExistingTable(array $names): ?string
    {
        foreach ($names as $name) {
            if (Schema::hasTable($name)) {
                return $name;
            }
        }

        return null;
    }
}
