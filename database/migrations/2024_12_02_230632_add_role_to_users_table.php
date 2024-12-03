public function up()
{
    Schema::table('users', function (Blueprint $table) {
        $table->string('role')->default('staff'); // Default 'staff'
    });
}

public function down()
{
    Schema::table('users', function (Blueprint $table) {
        $table->dropColumn('role');
    });
}
