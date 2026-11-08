<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Menambahkan peran 'admin' pada kolom users.role.
 *
 * Di MySQL kolom role berupa ENUM, jadi diubah dengan ALTER TABLE. Di SQLite
 * (dipakai saat pengujian) kolom role memakai batasan CHECK, sehingga tabel
 * users dibangun ulang dengan kolom role tanpa batasan peran.
 */
return new class extends Migration
{
    /**
     * PRAGMA foreign_keys tidak berlaku di dalam transaksi, jadi migrasi ini
     * dijalankan di luar transaksi.
     */
    public $withinTransaction = false;

    public function up(): void
    {
        if (DB::connection()->getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE users MODIFY role ENUM('buyer','seller','admin') NOT NULL DEFAULT 'buyer'");

            return;
        }

        $this->bangunUlangUsersSqlite();
    }

    public function down(): void
    {
        if (DB::connection()->getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE users MODIFY role ENUM('buyer','seller') NOT NULL DEFAULT 'buyer'");

            return;
        }

        $this->bangunUlangUsersSqlite();
    }

    /**
     * Membuat ulang tabel users tanpa batasan CHECK pada kolom role.
     */
    private function bangunUlangUsersSqlite(): void
    {
        DB::statement('PRAGMA foreign_keys=off');

        $kolomAsal = Schema::getColumnListing('users');
        $pakaiIsActive = in_array('is_active', $kolomAsal, true);

        Schema::dropIfExists('users_peran_baru');

        Schema::create('users_peran_baru', function (Blueprint $table) use ($pakaiIsActive) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();
            $table->string('role')->default('buyer')->index();
            $table->timestamps();

            if ($pakaiIsActive) {
                $table->boolean('is_active')->default(true);
            }
        });

        $kolom = ['id', 'name', 'email', 'email_verified_at', 'password', 'remember_token', 'role', 'created_at', 'updated_at'];

        if ($pakaiIsActive) {
            $kolom[] = 'is_active';
        }

        $kolom = array_values(array_intersect($kolom, $kolomAsal));

        DB::statement(
            'INSERT INTO users_peran_baru ('.implode(', ', $kolom).') SELECT '.implode(', ', $kolom).' FROM users'
        );

        Schema::drop('users');

        DB::statement('ALTER TABLE users_peran_baru RENAME TO users');

        DB::statement('PRAGMA foreign_keys=on');
    }
};
