<?php namespace App\Test;

//use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\CIDatabaseTestCase;
use CodeIgniter\Test\FeatureTestCase;

class Foo2Test extends FeatureTestCase
{

    protected $setUpMethods = [
        'mockEmail',
        'mockSession',
    ];

    protected $tearDownMethods = [
        'purgeRows',
    ];

    protected function purgeRows()
    {
        $this->model->purgeDeleted();
        echo "\r\n-----purgeRows----";
    }

    public function setUp(): void
    {
        parent::setUp();
        //helper('text');
        
        echo "\r\n-----setUp----\r\n";
        $forge = \Config\Database::forge();
        $fields = [
            'project_name' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 100,
            ],
            'project_id' => [
                'type' => 'INT',
                'auto_increment' => true,
            ],
            'project_code' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 100,
            ],
            'project_acquaintance_start_date' => [
                'type' => 'datetime',
                'null' => true
            ],
            'project_main_agenda_start_date' => [
                'type' => 'datetime',
                'null' => true
            ],
            'project_additional_agenda_start_date' => [
                'type' => 'datetime',
                'null' => true
            ],
            'project_meeting_finish_date' => [
                'type' => 'datetime',
                'null' => true
            ]
        ];
        $forge->addField($fields);
        $forge->createTable('project');
        //$this->up();
    }

    // public function testFoo2NotBar()
    // {
	   // echo "\r\n-----test2----";
    //    $projects_model = model('Projects_model');
    //    $projects_model->new_project('test1', 'test1', null, null, null, null);
    //    $projects_model->new_project('test2', 'test2', null, null, null, null);
    //    $projects_model->new_project('test3', 'test3', null, null, null, null);
    //    $projects_model->new_project('test4', 'test4', null, null, null, null);
    //    $projects_model->new_project('test5', 'test5', null, null, null, null);

    //    $p_list = $projects_model->getProjectList();
    //    foreach ($p_list->getResult() as $p) {
    //        echo "\r\n==$p->project_id : $p->project_name ==";
    //    }
    //    echo "\r\n";

    //    $this->seeInDatabase('project', ['project_name' => 'test2']);
    //    $this->dontSeeInDatabase('project', ['project_name' => 'test21']);
    // }

    public function testShowCategories()
    {
        $routes = [
            [ 'get', 'project12', 'Project123::index13' ]
        ];

        $result = $this->withRoutes($routes)
            ->get('project12');

    }
}