<?php

use ZBateson\MailMimeParser\Header\Consumer\ConsumerService;
use ZBateson\MailMimeParser\Header\Part\PartFactory;

/**
 * Description of AddressEmailConsumerTest
 *
 * @group Consumers
 * @group AddressConsumer
 * @author Zaahid Bateson
 */
class AddressConsumerTest extends PHPUnit_Framework_TestCase
{
    private $addressConsumer;
    
    public function setUp()
    {
        $pf = new PartFactory();
        $cs = new ConsumerService($pf);
        $this->addressConsumer = $cs->getAddressConsumer();
    }
    
    public function tearDown()
    {
        unset($this->addressConsumer);
    }
    
    public function testConsumeEmail()
    {
        $email = 'Max.Payne@AddressUnknown.com';
        $ret = $this->addressConsumer->__invoke($email);
        $this->assertNotEmpty($ret);
        $this->assertCount(1, $ret);
        
        $address = $ret[0];
        $this->assertInstanceOf('\ZBateson\MailMimeParser\Header\Part\Address', $address);
        $this->assertEquals('', $address->getName());
        $this->assertEquals($email, $address->getEmail());
    }
    
    public function testConsumeEmailName()
    {
        $email = 'Max Payne <Max.Payne@AddressUnknown.com>';
        $ret = $this->addressConsumer->__invoke($email);
        $this->assertNotEmpty($ret);
        $this->assertCount(1, $ret);
        
        $address = $ret[0];
        $this->assertInstanceOf('\ZBateson\MailMimeParser\Header\Part\Address', $address);
        $this->assertEquals('Max.Payne@AddressUnknown.com', $address->getEmail());
        $this->assertEquals('Max Payne', $address->getName());
    }
    
    public function testConsumeMimeEncodedName()
    {
        $email = '=?US-ASCII?Q?Kilgore_Trout?= <Kilgore.Trout@Iliyum.ny>';
        $ret = $this->addressConsumer->__invoke($email);
        $this->assertNotEmpty($ret);
        $this->assertCount(1, $ret);
        
        $address = $ret[0];
        $this->assertInstanceOf('\ZBateson\MailMimeParser\Header\Part\Address', $address);
        $this->assertEquals('Kilgore.Trout@Iliyum.ny', $address->getEmail());
        $this->assertEquals('Kilgore Trout', $address->getName());
    }
    
    public function testConsumeEmailWithComments()
    {
        // can't remember any longer if this is how it should be handled
        // need to review RFC
        $email = 'Max(imum).Payne (comment)@AddressUnknown.com';
        $ret = $this->addressConsumer->__invoke($email);
        $this->assertNotEmpty($ret);
        $this->assertCount(1, $ret);
        
        $address = $ret[0];
        $this->assertInstanceOf('\ZBateson\MailMimeParser\Header\Part\Address', $address);
        $this->assertEquals('Max.Payne@AddressUnknown.com', $address->getEmail());
    }
    
    public function testConsumeEmailWithQuotes()
    {
        // can't remember any longer if this is how it should be handled
        // need to review RFC
        $email = 'Max"(imum).Payne (comment)"@AddressUnknown.com';
        $ret = $this->addressConsumer->__invoke($email);
        $this->assertNotEmpty($ret);
        $this->assertCount(1, $ret);
        
        $address = $ret[0];
        $this->assertInstanceOf('\ZBateson\MailMimeParser\Header\Part\Address', $address);
        $this->assertEquals('Max(imum).Payne(comment)@AddressUnknown.com', $address->getEmail());
    }
    
    public function testConsumeAddressGroup()
    {
        $email = 'Senate: Caesar@Dictator.com,Cicero@Philosophy.com, Marc Antony <MarcAntony@imawesome.it>';
        $ret = $this->addressConsumer->__invoke($email);
        $this->assertNotEmpty($ret);
        $this->assertCount(1, $ret);
        
        $addressGroup = $ret[0];
        $this->assertInstanceOf('\ZBateson\MailMimeParser\Header\Part\AddressGroup', $addressGroup);
        $this->assertEquals('Senate', $addressGroup->getName());
    }
}