<?php
declare(strict_types=1);
/**
 * Copyright 2010 - 2019, Cake Development Corporation (https://www.cakedc.com)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright 2010 - 2018, Cake Development Corporation (https://www.cakedc.com)
 * @license MIT License (http://www.opensource.org/licenses/mit-license.php)
 */
namespace CakeDC\Users\Test\TestCase\Email;

use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;

/**
 * Test Case
 */
class UsersMailerTest extends TestCase
{
    /**
     * Fixtures
     *
     * @var array
     */
    public $fixtures = [
        'plugin.CakeDC/Users.SocialAccounts',
        'plugin.CakeDC/Users.Users',
    ];

    /**
     * setUp
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();
        $this->Email = $this->getMockBuilder('Cake\Mailer\Email')
            ->setMethods(['setTo', 'setSubject', 'setViewVars', 'setTemplate'])
            ->getMock();

        $this->UsersMailer = $this->getMockBuilder('CakeDC\Users\Mailer\UsersMailer')
            ->setConstructorArgs([$this->Email])
            ->setMethods(['setTo', 'setSubject', 'setViewVars', 'setTemplate'])
            ->getMock();
    }

    /**
     * tearDown
     *
     * @return void
     */
    public function tearDown()
    {
        unset($this->UsersMailer);
        unset($this->Email);
        parent::tearDown();
    }

    /**
     * test sendValidationEmail
     *
     * @return void
     */
    public function testValidation()
    {
        $table = TableRegistry::getTableLocator()->get('CakeDC/Users.Users');
        $data = [
            'first_name' => 'FirstName',
            'email' => 'test@example.com',
            'token' => '12345',
        ];
        $user = $table->newEntity($data);
        $this->UsersMailer->expects($this->once())
            ->method('setTo')
            ->with($user['email'])
            ->will($this->returnValue($this->Email));

        $this->Email->expects($this->once())
            ->method('setSubject')
            ->with('FirstName, Your account validation link')
            ->will($this->returnValue($this->Email));

        $this->Email->expects($this->once())
            ->method('setViewVars')
            ->with($data)
            ->will($this->returnValue($this->Email));

        $this->invokeMethod($this->UsersMailer, 'validation', [$user]);
    }

    /**
     * test SocialAccountValidation
     *
     * @return void
     */
    public function testSocialAccountValidation()
    {
        $social = TableRegistry::getTableLocator()->get('CakeDC/Users.SocialAccounts')
            ->get('00000000-0000-0000-0000-000000000001', ['contain' => 'Users']);

        $this->UsersMailer->expects($this->once())
            ->method('setTo')
            ->with('user-1@test.com')
            ->will($this->returnValue($this->Email));

        $this->Email->expects($this->once())
            ->method('setSubject')
            ->with('first1, Your social account validation link')
            ->will($this->returnValue($this->Email));

        $this->Email->expects($this->once())
            ->method('setViewVars')
            ->with(['user' => $social->user, 'socialAccount' => $social])
            ->will($this->returnValue($this->Email));

        $this->invokeMethod($this->UsersMailer, 'socialAccountValidation', [$social->user, $social]);
    }

    /**
     * test sendValidationEmail including 'template'
     *
     * @return void
     */
    public function testResetPassword()
    {
        $table = TableRegistry::getTableLocator()->get('CakeDC/Users.Users');
        $data = [
            'first_name' => 'FirstName',
            'email' => 'test@example.com',
            'token' => '12345',
        ];
        $user = $table->newEntity($data);
        $this->UsersMailer->expects($this->once())
            ->method('setTo')
            ->with($user['email'])
            ->will($this->returnValue($this->Email));

        $this->Email->expects($this->once())
            ->method('setSubject')
            ->with('FirstName, Your reset password link')
            ->will($this->returnValue($this->Email));

        $this->Email->expects($this->once())
            ->method('setViewVars')
            ->with($data)
            ->will($this->returnValue($this->Email));

        $this->invokeMethod($this->UsersMailer, 'resetPassword', [$user]);
    }

    /**
     * Call protected/private method of a class.
     *
     * @param object &$object    Instantiated object that we will run method on.
     * @param string $methodName Method name to call
     * @param array  $parameters Array of parameters to pass into method.
     *
     * @return mixed Method return.
     */
    public function invokeMethod(&$object, $methodName, $parameters = [])
    {
        $reflection = new \ReflectionClass(get_class($object));
        $method = $reflection->getMethod($methodName);
        $method->setAccessible(true);

        return $method->invokeArgs($object, $parameters);
    }
}
