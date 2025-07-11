
<?php
use think\migration\Migrator;
use think\migration\db\Column;

class UpdateUsersTable extends Migrator
{
    public function up()
    {
        $table = $this->table('user');
        $table->addColumn('password', 'string', ['limit' => 255, 'after' => 'username'])
              ->addColumn('role', 'integer', ['limit' => 1, 'default' => 3, 'after' => 'status'])
              ->addColumn('last_login_time', 'datetime', ['null' => true, 'after' => 'update_time'])
              ->addColumn('delete_time', 'datetime', ['null' => true, 'after' => 'update_time'])
              ->update();
    }

    public function down()
    {
        $table = $this->table('user');
        $table->removeColumn('password')
              ->removeColumn('role')
              ->removeColumn('last_login_time')
              ->removeColumn('delete_time')
              ->update();
    }
}
