<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('address')->nullable();
            $table->timestamps();
        });

        Schema::create('vehicles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained('customers')->cascadeOnDelete();
            $table->string('registration_no')->unique();
            $table->string('make')->nullable();
            $table->string('model')->nullable();
            $table->string('color')->nullable();
            $table->string('vin')->nullable();
            $table->timestamps();
        });

        Schema::create('job_cards', function (Blueprint $table) {
            $table->id();
            $table->string('job_no')->unique();
            $table->foreignId('customer_id')->constrained('customers')->restrictOnDelete();
            $table->foreignId('vehicle_id')->constrained('vehicles')->restrictOnDelete();
            $table->unsignedBigInteger('opened_by')->nullable();
            $table->date('opened_date');
            $table->unsignedInteger('odometer_reading')->nullable();
            $table->string('fuel_level', 40)->nullable();
            $table->text('reported_problems')->nullable();
            $table->string('priority', 30)->default('normal');
            $table->string('status', 30)->default('new');
            $table->text('remarks')->nullable();
            $table->date('expected_completion')->nullable();
            $table->date('completed_date')->nullable();
            $table->timestamps();

            $table->foreign('opened_by')->references('id')->on('users')->nullOnDelete();
        });

        Schema::create('repair_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_card_id')->constrained('job_cards')->cascadeOnDelete();
            $table->string('repair_type');
            $table->text('description')->nullable();
            $table->decimal('estimated_hours', 8, 2)->default(0);
            $table->decimal('estimated_cost', 12, 2)->default(0);
            $table->string('status', 30)->default('open');
            $table->timestamps();
        });

        Schema::create('mechanics', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('employee_id')->nullable();
            $table->string('name')->nullable();
            $table->string('specialization')->nullable();
            $table->decimal('hourly_rate', 12, 2)->default(0);
            $table->string('status', 30)->default('active');
            $table->timestamps();

            $table->foreign('employee_id')->references('Employee_ID')->on('tbl_employee')->nullOnDelete();
        });

        Schema::create('diagnosis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_card_id')->unique()->constrained('job_cards')->cascadeOnDelete();
            $table->foreignId('mechanic_id')->nullable()->constrained('mechanics')->nullOnDelete();
            $table->text('symptoms')->nullable();
            $table->text('findings');
            $table->text('root_cause')->nullable();
            $table->text('recommendation')->nullable();
            $table->decimal('estimated_hours', 8, 2)->default(0);
            $table->decimal('estimated_parts_cost', 12, 2)->default(0);
            $table->boolean('approved')->default(false);
            $table->timestamps();
        });

        Schema::create('job_mechanics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_card_id')->constrained('job_cards')->cascadeOnDelete();
            $table->foreignId('mechanic_id')->constrained('mechanics')->restrictOnDelete();
            $table->date('assigned_date');
            $table->string('role')->nullable();
            $table->decimal('hours_worked', 8, 2)->default(0);
            $table->unsignedTinyInteger('completion_percent')->default(0);
            $table->string('status', 30)->default('assigned');
            $table->timestamps();

            $table->unique(['job_card_id', 'mechanic_id']);
        });

        Schema::create('labour_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_card_id')->constrained('job_cards')->cascadeOnDelete();
            $table->foreignId('mechanic_id')->constrained('mechanics')->restrictOnDelete();
            $table->text('work_done');
            $table->decimal('hours', 8, 2);
            $table->decimal('rate', 12, 2);
            $table->decimal('amount', 12, 2);
            $table->date('date');
            $table->timestamps();
        });

        Schema::create('parts_used', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_card_id')->constrained('job_cards')->cascadeOnDelete();
            $table->unsignedInteger('part_id');
            $table->unsignedInteger('quantity');
            $table->decimal('unit_price', 12, 2);
            $table->decimal('total', 12, 2);
            $table->unsignedBigInteger('issued_by')->nullable();
            $table->date('issue_date');
            $table->unsignedInteger('subdepartment_id')->nullable();
            $table->timestamps();

            $table->foreign('part_id')->references('id')->on('tbl_items')->restrictOnDelete();
            $table->foreign('issued_by')->references('id')->on('users')->nullOnDelete();
            $table->foreign('subdepartment_id')->references('Subdepartment_ID')->on('tbl_subdepartment')->nullOnDelete();
        });

        Schema::create('job_completion', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_card_id')->unique()->constrained('job_cards')->cascadeOnDelete();
            $table->text('completion_notes')->nullable();
            $table->unsignedBigInteger('completed_by')->nullable();
            $table->date('completed_date');
            $table->boolean('vehicle_tested')->default(false);
            $table->boolean('ready_for_inspection')->default(true);
            $table->timestamps();

            $table->foreign('completed_by')->references('id')->on('users')->nullOnDelete();
        });

        Schema::create('quality_checks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_card_id')->unique()->constrained('job_cards')->cascadeOnDelete();
            $table->unsignedBigInteger('inspector_id')->nullable();
            $table->date('inspection_date');
            $table->boolean('repair_completed')->default(false);
            $table->boolean('road_test')->default(false);
            $table->boolean('no_oil_leaks')->default(false);
            $table->boolean('brakes_checked')->default(false);
            $table->boolean('lights_working')->default(false);
            $table->boolean('complaint_resolved')->default(false);
            $table->text('remarks')->nullable();
            $table->string('status', 30);
            $table->timestamps();

            $table->foreign('inspector_id')->references('id')->on('users')->nullOnDelete();
        });

        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_card_id')->unique()->constrained('job_cards')->cascadeOnDelete();
            $table->string('invoice_no')->unique();
            $table->decimal('labour_total', 12, 2)->default(0);
            $table->decimal('parts_total', 12, 2)->default(0);
            $table->decimal('tax', 12, 2)->default(0);
            $table->decimal('discount', 12, 2)->default(0);
            $table->decimal('other_charges', 12, 2)->default(0);
            $table->decimal('grand_total', 12, 2)->default(0);
            $table->string('status', 30)->default('draft');
            $table->timestamps();
        });

        $this->seedWorkshopMenus();
    }

    public function down(): void
    {
        Schema::dropIfExists('invoices');
        Schema::dropIfExists('quality_checks');
        Schema::dropIfExists('job_completion');
        Schema::dropIfExists('parts_used');
        Schema::dropIfExists('labour_entries');
        Schema::dropIfExists('job_mechanics');
        Schema::dropIfExists('diagnosis');
        Schema::dropIfExists('repair_orders');
        Schema::dropIfExists('job_cards');
        Schema::dropIfExists('vehicles');
        Schema::dropIfExists('customers');
        Schema::dropIfExists('mechanics');
    }

    private function seedWorkshopMenus(): void
    {
        if (! Schema::hasTable('tbl_menus')) {
            return;
        }

        $menus = [
            ['key' => 'workshop.setup', 'label' => 'Workshop Management', 'icon' => 'bi-tools', 'url' => null, 'parent' => null, 'collapse' => 1],
            ['key' => 'workshop.dashboard', 'label' => 'Dashboard', 'icon' => null, 'url' => 'workshop/dashboard', 'parent' => 'workshop.setup'],
            ['key' => 'workshop.job-cards', 'label' => 'Job Cards', 'icon' => null, 'url' => 'workshop/job-cards', 'parent' => 'workshop.setup'],
            ['key' => 'workshop.open-jobs', 'label' => 'Open Jobs', 'icon' => null, 'url' => 'workshop/job-cards?status=open', 'parent' => 'workshop.setup'],
            ['key' => 'workshop.completed-jobs', 'label' => 'Completed Jobs', 'icon' => null, 'url' => 'workshop/job-cards?status=completed', 'parent' => 'workshop.setup'],
            ['key' => 'workshop.closed-jobs', 'label' => 'Closed Jobs', 'icon' => null, 'url' => 'workshop/job-cards?status=closed', 'parent' => 'workshop.setup'],
            ['key' => 'workshop.invoices', 'label' => 'Invoices', 'icon' => null, 'url' => 'workshop/job-cards?status=invoiced', 'parent' => 'workshop.setup'],
            ['key' => 'workshop.reports', 'label' => 'Reports', 'icon' => null, 'url' => 'workshop/dashboard', 'parent' => 'workshop.setup'],
        ];

        $ids = [];
        $nextModuleId = ((int) DB::table('tbl_menus')->max('module_id')) + 1;

        foreach ($menus as $menu) {
            $parentId = $menu['parent'] ? ($ids[$menu['parent']] ?? DB::table('tbl_menus')->where('name', $menu['parent'])->value('module_id')) : null;
            $existingId = DB::table('tbl_menus')->where('name', $menu['key'])->value('module_id');
            $data = [
                'name' => $menu['key'],
                'label' => $menu['label'],
                'menu_icon' => $menu['icon'],
                'route_path' => $menu['url'],
                'parent_id' => $parentId,
                'is_menu' => true,
                'description' => $menu['label'],
                'is_dashboard' => 0,
                'collapse' => $menu['collapse'] ?? 0,
                'new_message' => 0,
            ];

            if (Schema::hasColumn('tbl_menus', 'parent_id2')) {
                $data['parent_id2'] = null;
            }

            if ($existingId) {
                DB::table('tbl_menus')->where('module_id', $existingId)->update($data);
                $ids[$menu['key']] = (int) $existingId;
            } else {
                DB::table('tbl_menus')->insert(array_merge(['module_id' => $nextModuleId], $data));
                $ids[$menu['key']] = $nextModuleId++;
            }
        }

        if (Schema::hasTable('tbl_users_privileges') && Schema::hasTable('user_type_menu_permissions')) {
            $adminIds = DB::table('tbl_users_privileges')
                ->whereIn('privilege_name', ['Admin', 'Administrator'])
                ->pluck('id');

            foreach ($adminIds as $privilegeId) {
                foreach (array_keys($ids) as $menuKey) {
                    DB::table('user_type_menu_permissions')->updateOrInsert(
                        ['privilege_id' => $privilegeId, 'menu_key' => $menuKey],
                        ['can_access' => true, 'created_at' => now(), 'updated_at' => now()]
                    );
                }
            }
        }
    }
};
