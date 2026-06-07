<?php

use Illuminate\Database\Seeder;
use App\Models\Branch;
use App\Models\User;
use App\Models\Patient;
use App\Models\ServicePriceList;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. إنشاء الفروع التجريبية (Branches)
        $branch1 = Branch::create([
            'name' => 'عيادة الشام لطب الأسنان - الفرع الرئيسي',
            'address' => 'دمشق، ساحة النجمة',
            'phone' => '011223344',
            'email' => 'main_branch@dental.com',
            'is_active' => true,
        ]);

        $branch2 = Branch::create([
            'name' => 'عيادة الشام لطب الأسنان - فرع المزة',
            'address' => 'دمشق، المزة اتستراد',
            'phone' => '011556677',
            'email' => 'mazzeh_branch@dental.com',
            'is_active' => true,
        ]);

        // 2. إنشاء حساب الـ Super Admin (لا يتبع لفرع معين بالضرورة)
        User::create([
            'name' => 'محمد اللبابيدي (Super Admin)',
            'email' => 'superadmin@dental.com',
            'password' => Hash::make('password'),
            'phone' => '0911111111',
            'role' => 'super_admin',
            'branch_id' => null,
            'is_active' => true,
        ]);

        // 3. إنشاء حسابات الفرع الرئيسي (Branch 1)
        User::create([
            'name' => 'مدير الفرع الرئيسي (Admin)',
            'email' => 'admin1@dental.com',
            'password' => Hash::make('password'),
            'phone' => '0922222222',
            'role' => 'admin',
            'branch_id' => $branch1->id,
            'is_active' => true,
        ]);

        $doctor1 = User::create([
            'name' => 'د. أحمد علي (طبيب أسنان)',
            'email' => 'doctor1@dental.com',
            'password' => Hash::make('password'),
            'phone' => '0933333333',
            'role' => 'doctor',
            'branch_id' => $branch1->id,
            'commission_rate' => 20.00, // نسبة الطبيب 20%
            'is_active' => true,
        ]);

        User::create([
            'name' => 'سارة الأحمد (سكرتارية)',
            'email' => 'sec1@dental.com',
            'password' => Hash::make('password'),
            'phone' => '0944444444',
            'role' => 'secretary',
            'branch_id' => $branch1->id,
            'is_active' => true,
        ]);

        User::create([
            'name' => 'خالد العمر (محاسب)',
            'email' => 'acc1@dental.com',
            'password' => Hash::make('password'),
            'phone' => '0955555555',
            'role' => 'accountant',
            'branch_id' => $branch1->id,
            'is_active' => true,
        ]);

        // 4. إنشاء قائمة خدمات وأسعار تجريبية للفرع الرئيسي
        $services = [
            ['service_name' => 'فحص واستشارة', 'price' => 50.00],
            ['service_name' => 'تنظيف أسنان وتيرتير', 'price' => 100.00],
            ['service_name' => 'حشوة تجميلية ضوئية', 'price' => 150.00],
            ['service_name' => 'سحب عصب ومعالجة لبية', 'price' => 300.00],
            ['service_name' => 'قلع سن عادي', 'price' => 80.00],
            ['service_name' => 'زرع سن (تيتانيوم)', 'price' => 1200.00],
        ];

        foreach ($services as $service) {
            ServicePriceList::create([
                'branch_id' => $branch1->id,
                'service_name' => $service['service_name'],
                'price' => $service['price'],
            ]);
        }

        // 5. إضافة مريض تجريبي للفرع الرئيسي
        Patient::create([
            'branch_id' => $branch1->id,
            'name' => 'سامر المصري',
            'phone' => '0966666666',
            'gender' => 'male',
            'dob' => '1995-05-12',
            'chronic_conditions' => ['السكري', 'حساسية البنسلين'], // حفظ بصيغة مصفوفة وسيتحول تلقائياً لـ JSON
        ]);
    }
}