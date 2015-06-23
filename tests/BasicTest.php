<?php
/*@description        Dice - A minimal Dependency Injection Container for PHP
* @author             Tom Butler tom@r.je
* @copyright          2012-2015 Tom Butler <tom@r.je>
* @link               http://r.je/dice.html
* @license            http://www.opensource.org/licenses/bsd-license.php  BSD License
* @version            2.0
*/
class BasicTest extends DiceTest {

	public function testCreate() {
		$this->getMock('stdClass', array(), array(), 'TestCreate');
		$myobj = $this->dice->create('TestCreate');
		$this->assertInstanceOf('TestCreate', $myobj);
	}

	public function testCreateInvalid() {
		//"can't expect default exception". Not sure why.
		$this->setExpectedException('ErrorException');
		try {
			$this->dice->create('SomeClassThatDoesNotExist');
		}
		catch (Exception $e) {
			throw new ErrorException('Error occurred');
		}
	}
	
	public function testNoConstructor() {
		$a = $this->dice->create('NoConstructor');		
		$this->assertInstanceOf('NoConstructor', $a);
	}


	public function testSetDefaultRule() {
		$defaultBehaviour = [];
		$defaultBehaviour['shared'] = true;
		$defaultBehaviour['newInstances'] = array('Foo', 'Bar');
		$this->dice->addRule('*', $defaultBehaviour);		

		$rule = $this->dice->getRule('*');
		foreach ($defaultBehaviour as $name => $value) {
			$this->assertEquals($rule[$name], $defaultBehaviour[$name]);
		}
	}


	public function testDefaultRuleWorks() {
		$defaultBehaviour = [];
		$defaultBehaviour['shared'] = true;
		
		$this->dice->addRule('*', $defaultBehaviour);
		
		$rule = $this->dice->getRule('A');
		
		$this->assertTrue($rule['shared']);
		
		$a1 = $this->dice->create('A');
		$a2 = $this->dice->create('A');
		
		$this->assertSame($a1, $a2);
	}


	/*
	 * Object graph creation cannot be tested with mocks because the constructor need to be tested.
	 * You can't set 'expects' on the objects which are created making them redundant for that as well
	 * Need real classes to test with unfortunately. 
	 */
	public function testObjectGraphCreation() {
		$a = $this->dice->create('A');
		$this->assertInstanceOf('B', $a->b);
		$this->assertInstanceOf('c', $a->b->c);
		$this->assertInstanceOf('D', $a->b->c->d);
		$this->assertInstanceOf('E', $a->b->c->e);
		$this->assertInstanceOf('F', $a->b->c->e->f);
	} 


	public function testAssignSharedNamed() {
		$rule = [];
		$rule['shared'] = true;
		$rule['instanceOf'] = function() {
			return Baz77::create();
		};
		$this->dice->addRule('$SharedBaz', $rule);
	}



	public function testSharedNamed() {
		$rule = [];
		$rule['shared'] = true;
		$rule['instanceOf'] = 'A';
		
		$this->dice->addRule('[A]', $rule);
		
		$a1 = $this->dice->create('[A]');
		$a2 = $this->dice->create('[A]');
		$this->assertSame($a1, $a2);
	}

	public function testSharedRule() {
		$shared = [];
		$shared['shared'] = true;
	
		$this->dice->addRule('MyObj', $shared);
	
		$obj = $this->dice->create('MyObj');
		$this->assertInstanceOf('MyObj', $obj);
	
		$obj2 = $this->dice->create('MyObj');
		$this->assertInstanceOf('MyObj', $obj2);
	
		$this->assertSame($obj, $obj2);
	
	
		//This check isn't strictly needed but it's nice to have that safety measure!
		$obj->setFoo('bar');
		$this->assertEquals($obj->getFoo(), $obj2->getFoo());
		$this->assertEquals($obj->getFoo(), 'bar');
		$this->assertEquals($obj2->getFoo(), 'bar');
	}

	public function testForceNewInstance() {
		$rule = [];
		$rule['shared'] = true;
		$this->dice->addRule('A', $rule);
		
		$a1 = $this->dice->create('A');
		$a2 = $this->dice->create('A');
		
		$a3 = $this->dice->create('A', array(), true);
		
		$this->assertSame($a1, $a2);
		$this->assertNotSame($a1, $a3);
		$this->assertNotSame($a2, $a3);	
	}	

	public function testDefaultValueAssigned() {
		$obj = $this->dice->create('MethodWithDefaultValue');
		$this->assertEquals($obj->foo, 'bar');
	}
	
	
	public function testNewInstances() {
		$rule = [];
		$rule['shared'] = true;
		$this->dice->addRule('B', $rule);
		
		$rule = [];
		$rule['newInstances'][] = 'B';
		$this->dice->addRule('A', $rule);
		
		$a1 = $this->dice->create('A');
		$a2 = $this->dice->create('A');
		
		$this->assertNotSame($a1->b, $a2->b);
	}
	
	public function testInterfaceRule() {
		$rule = [];

		$rule['shared'] = true;
		$this->dice->addRule('interfaceTest', $rule);
		
		$one = $this->dice->create('InterfaceTestClass');
		$two = $this->dice->create('InterfaceTestClass');		
		
		$this->assertSame($one, $two);		
	}

	public function testCyclicReferences() {
		$rule = [];
		$rule['shared'] = true;
		
		$this->dice->addRule('CyclicB', $rule);
		
		$a = $this->dice->create('CyclicA');
		
		$this->assertInstanceOf('CyclicB', $a->b);
		$this->assertInstanceOf('CyclicA', $a->b->a);
		
		$this->assertSame($a->b, $a->b->a->b);
	}

}