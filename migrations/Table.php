<?php
namespace x51\yii2\modules\metafields\migrations;
	use yii\db\Migration;

class Table extends Migration {
    public $baseTableName = 'metafields';
	
	public function init() {
		parent::init();
	} // end init
	
	/**
     * {@inheritdoc}
     */
    public function safeUp()
    {
		$tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            // http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }
		$this->createTable('{{%'.$this->baseTableName.'_meta}}', [
            'id' => $this->primaryKey(),
			'type' => $this->string(50)->notNull()->defaultValue(''),
			'tid' =>  $this->bigInteger()->defaultValue(0),
			'meta_key' => $this->string(50)->notNull()->defaultValue(''),
			'meta_value' => $this->text()->notNull(),
        ], $tableOptions);
			$this->createIndex('k_'.$this->baseTableName.'_type', '{{%'.$this->baseTableName.'_meta}}', 'type');
			$this->createIndex('k_'.$this->baseTableName.'_ttid', '{{%'.$this->baseTableName.'_meta}}', ['type', 'tid']);
			$this->createIndex('k_'.$this->baseTableName.'_meta_key', '{{%'.$this->baseTableName.'_meta}}', 'meta_key');
			$this->createIndex('k_'.$this->baseTableName.'_ttmk', '{{%'.$this->baseTableName.'_meta}}', ['type', 'tid', 'meta_key'], true);
			
	} // end safeUp

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
		$this->dropTable('{{%'.$this->baseTableName.'_meta}}');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180806_115337_article cannot be reverted.\n";

        return false;
    }
    */
} // end class