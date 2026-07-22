<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                if (Schema::hasColumn('users', 'first_name') && ! Schema::hasColumn('users', 'name')) {
                    $table->renameColumn('first_name', 'name');
                }
            });

            $nameParts = collect(['second_name', 'other_names'])
                ->filter(fn (string $column): bool => Schema::hasColumn('users', $column))
                ->all();

            if (Schema::hasColumn('users', 'name') && !empty($nameParts)) {
                $selectColumns = array_merge(['id', 'name'], $nameParts);

                DB::table('users')
                    ->select($selectColumns)
                    ->orderBy('id')
                    ->chunkById(100, function ($users) use ($nameParts): void {
                        foreach ($users as $user) {
                            $fullName = collect(array_merge([$user->name], array_map(fn (string $column) => $user->{$column} ?? null, $nameParts)))
                                ->filter()
                                ->implode(' ');

                            DB::table('users')
                                ->where('id', $user->id)
                                ->update(['name' => trim($fullName)]);
                        }
                    });
            }

            $dropColumns = collect([
                'second_name',
                'other_names',
                'date_of_birth',
                'gender',
                'national_id',
                'physical_address',
            ])->filter(fn (string $column): bool => Schema::hasColumn('users', $column))->all();

            if (!empty($dropColumns)) {
                Schema::table('users', function (Blueprint $table) use ($dropColumns) {
                    $table->dropColumn($dropColumns);
                });
            }
        }

        if (Schema::hasTable('tbl_employee')) {
            Schema::table('tbl_employee', function (Blueprint $table) {
                if (! Schema::hasColumn('tbl_employee', 'date_of_birth')) {
                    $table->date('date_of_birth')->nullable()->after('Phone_Number');
                }

                if (! Schema::hasColumn('tbl_employee', 'gender')) {
                    $table->string('gender', 50)->nullable()->after('date_of_birth');
                }

                if (! Schema::hasColumn('tbl_employee', 'national_id')) {
                    $table->string('national_id')->nullable()->after('gender');
                }

                if (! Schema::hasColumn('tbl_employee', 'physical_address')) {
                    $table->text('physical_address')->nullable()->after('national_id');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('tbl_employee')) {
            $dropColumns = collect([
                'date_of_birth',
                'gender',
                'national_id',
                'physical_address',
            ])->filter(fn (string $column): bool => Schema::hasColumn('tbl_employee', $column))->all();

            if (!empty($dropColumns)) {
                Schema::table('tbl_employee', function (Blueprint $table) use ($dropColumns) {
                    $table->dropColumn($dropColumns);
                });
            }
        }

        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                if (Schema::hasColumn('users', 'name') && ! Schema::hasColumn('users', 'first_name')) {
                    $table->renameColumn('name', 'first_name');
                }
            });

            Schema::table('users', function (Blueprint $table) {
                if (! Schema::hasColumn('users', 'second_name')) {
                    $table->string('second_name')->nullable()->after('first_name');
                }

                if (! Schema::hasColumn('users', 'other_names')) {
                    $table->string('other_names')->nullable()->after('second_name');
                }

                if (! Schema::hasColumn('users', 'date_of_birth')) {
                    $table->string('date_of_birth')->nullable()->after('other_names');
                }

                if (! Schema::hasColumn('users', 'gender')) {
                    $table->string('gender')->nullable()->after('date_of_birth');
                }

                if (! Schema::hasColumn('users', 'national_id')) {
                    $table->string('national_id')->nullable()->after('branch_id');
                }

                if (! Schema::hasColumn('users', 'physical_address')) {
                    $table->text('physical_address')->nullable()->after('privilege_id');
                }
            });
        }
    }
};
