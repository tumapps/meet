<?php

use yii\db\Migration;
use Faker\Factory;
use yii\helpers\Console;

/**
 * Class m250602_191139_test_endpoints
 */
class m250602_191139_test_endpoints extends Migration
{
    public function safeUp()
    {
        // Table: student_profile
        $this->createTable('{{%student_profile}}', [
            'std_id' => $this->primaryKey(),
            'reg_number' => $this->string(20)->notNull()->unique(),
            'id_number' => $this->string(20)->notNull()->unique(),
            'student_email' => $this->string(100)->notNull()->unique(),
            'fee_paid' => $this->decimal(10, 2)->notNull(),
            'total_fee' => $this->decimal(10, 2)->notNull(),
            'photo' => $this->string(255),
            'status' => $this->string(50)->notNull(),
            'verification_status' => $this->string(50)->notNull()->defaultValue('unverified'),
            'last_payment_note' => $this->string(255),
            'next_due_note' => $this->string(255),
            'class' => $this->string(50)->notNull(),
            'school' => $this->string(100)->notNull(),
            'department' => $this->string(100)->notNull(),
            'year_of_study' => $this->string(10)->notNull(), // e.g. 1.1, 2.2
            'is_deleted' => $this->integer()->defaultValue(0),
            'created_at' => $this->integer(),
            'updated_at' => $this->integer(),
        ]);

        // Table: verification_detail
        $this->createTable('{{%verification_detail}}', [
            'id' => $this->primaryKey(),
            'student_id' => $this->integer()->notNull(),
            'staff_id' => $this->integer()->notNull(),
            'is_deleted' => $this->integer()->defaultValue(0),
            'created_at' => $this->integer(),
            'updated_at' => $this->integer(),
            'FOREIGN KEY ([[student_id]]) REFERENCES {{%student_profile}} ([[std_id]])' .
                $this->buildFkClause('ON DELETE CASCADE', 'ON UPDATE CASCADE'),
        ]);


        // Table: issues
        $this->createTable('{{%issues}}', [
            'id' => $this->primaryKey(),
            'staff_id' => $this->integer()->notNull(),
            'student_id' => $this->integer()->notNull(),
            'issue' => $this->text()->notNull(),
            'is_deleted' => $this->integer()->defaultValue(0),
            'created_at' => $this->integer(),
            'updated_at' => $this->integer(),
            'FOREIGN KEY ([[student_id]]) REFERENCES {{%student_profile}} ([[std_id]])' .
                $this->buildFkClause('ON DELETE CASCADE', 'ON UPDATE CASCADE'),
        ]);


        $this->seed();
    }

    public function safeDown()
    {
        // $this->dropForeignKey('fk-verification-student', '{{%verification_detail}}');
        // $this->dropForeignKey('fk-issues-student', '{{%issues}}');

        $this->dropTable('{{%issues}}');
        $this->dropTable('{{%verification_detail}}');
        $this->dropTable('{{%student_profile}}');
    }

    protected function buildFkClause($delete = '', $update = '')
    {
        return implode(' ', ['', $delete, $update]);
    }



    private function seed()
    {
        $faker = Factory::create();
        $uniqueRegNumbers = [];
        $uniqueEmails = [];
        $uniqueIdNumbers = [];

        for ($i = 0; $i < 100; $i++) {
            // Unique registration number
            do {
                $reg_number = strtoupper($faker->bothify('TUM_REG_NO/??/####'));
            } while (in_array($reg_number, $uniqueRegNumbers));
            $uniqueRegNumbers[] = $reg_number;

            // Unique email
            do {
                $email = $faker->unique()->safeEmail;
            } while (in_array($email, $uniqueEmails));
            $uniqueEmails[] = $email;

            // Unique ID number
            do {
                $id_number = $faker->unique()->numerify('2########');
            } while (in_array($id_number, $uniqueIdNumbers));
            $uniqueIdNumbers[] = $id_number;

            // Fee logic
            $total_fee = 75000;
            $fee_paid = $faker->randomElement([
                75000, // fully paid
                $faker->numberBetween(30000, 74999), // partially paid
                $faker->numberBetween(0, 29999), // low payment
            ]);

            $status = $fee_paid >= $total_fee ? 'cleared' : 'pending';
            $verification_status = $status === 'cleared' ? 'verified' : 'unverified';

            // Notes
            $lastPaymentDate = $faker->dateTimeBetween('-3 months', 'now');
            $nextDueDate = (clone $lastPaymentDate)->modify('+1 month');

            $last_payment_note = 'Last Payment: Ksh' . number_format($fee_paid, 2) . ' (' . $lastPaymentDate->format('d M Y') . ')';
            $next_due_note = 'Next Due: ' . $nextDueDate->format('d M Y');

            $timestamp = time();

            $this->insert('{{%student_profile}}', [
                'reg_number' => $reg_number,
                'id_number' => $id_number,
                'student_email' => $email,
                'fee_paid' => $fee_paid,
                'total_fee' => $total_fee,
                'photo' => "https://i.pravatar.cc/150?img=" . $faker->numberBetween(1, 70),
                'status' => $status,
                // 'verification_status' => $verification_status,
                'last_payment_note' => $last_payment_note,
                'next_due_note' => $next_due_note,
                'class' => $faker->randomElement(['CS A', 'CS B', 'IT A', 'IT B']),
                'school' => $faker->randomElement(['School of ICT', 'School of Engineering', 'School of Business']),
                'department' => $faker->randomElement(['Computer Science', 'Information Technology', 'Business Administration']),
                'year_of_study' => $faker->randomElement(['1.1', '1.2', '2.1', '2.2', '3.1', '3.2', '4.1', '4.2']),
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ]);
        }

        Console::output('✅ 100 students seeded into `student_profile` table.');
    }


    private function seed2()
    {
        $faker = Factory::create();
        $uniqueRegNumbers = [];
        $uniqueEmails = [];

        for ($i = 0; $i < 100; $i++) {
            do {
                $reg_number = strtoupper($faker->bothify('TUM/??/####'));
            } while (in_array($reg_number, $uniqueRegNumbers));
            $uniqueRegNumbers[] = $reg_number;

            do {
                $email = $faker->unique()->safeEmail;
            } while (in_array($email, $uniqueEmails));
            $uniqueEmails[] = $email;

            $fee_paid = $faker->numberBetween(20000, 70000);
            $total_fee = 75000;
            $status = $fee_paid >= $total_fee ? 'cleared' : 'pending';

            $this->insert('{{%student_profile}}', [
                'reg_number' => $reg_number,
                'student_email' => $email,
                'fee_paid' => $fee_paid,
                'total_fee' => $total_fee,
                // 'photo' => $faker->imageUrl(200, 200, 'people'),
                'photo' => "https://i.pravatar.cc/150?img=" . $faker->numberBetween(1, 70),
                'last_payment_note' => "Last Payment: Ksh{$faker->numberBetween(500, 5000)} ({$faker->date('d M Y')})",
                'next_due_note' => "Next Due: {$faker->date('d M Y')}",
                'status' => $status,
                'class' => $faker->randomElement(['CS A', 'CS B', 'IT A', 'IT B']),
                'school' => $faker->randomElement(['School of ICT', 'School of Engineering', 'School of Business']),
                'department' => $faker->randomElement(['Computer Science', 'Information Technology', 'Business Administration']),
                'year_of_study' => $faker->randomElement(['1.1', '1.2', '2.1', '2.2', '3.1', '3.2', '4.1', '4.2']),
            ]);
        }

        Console::output('✅ 100 students seeded into `student_profile` table.');
    }
}
