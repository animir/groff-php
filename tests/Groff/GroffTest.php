<?php
/**
 * @author animir
 */

namespace Groff;

use Groff\Groff;

class GroffTest extends \PHPUnit_Framework_TestCase {    
    
    /**
     * @expectedException \Exception
     */
    public function testInvalidExecutable() {
        $groff = new Groff('/usr/bin/badgroff');
    }
    
    /**
     * 
     */
    public function testConvert() {
        $groff = new Groff();
        $manText = <<<MANTEXT
GROFF(1)                                               General Commands Manual                                               GROFF(1)

NAME
       groff - front-end for the groff document formatting system        
MANTEXT;
        $html = preg_replace('/\<\!\-\-.+\-\-\>/', '', $groff->convert($manText, 'html'));
        $cmpHtml = <<<HTML
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
"http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta name="generator" content="groff -Thtml, see www.gnu.org">
<meta http-equiv="Content-Type" content="text/html; charset=US-ASCII">
<meta name="Content-Style" content="text/css">
<style type="text/css">
       p       { margin-top: 0; margin-bottom: 0; vertical-align: top }
       pre     { margin-top: 0; margin-bottom: 0; vertical-align: top }
       table   { margin-top: 0; margin-bottom: 0; vertical-align: top }
       h1      { text-align: center }
</style>
<title></title>
</head>
<body>

<hr>


<p>GROFF(1) General Commands Manual GROFF(1)</p>

<p style="margin-top: 1em">NAME <br>
groff - front-end for the groff document formatting
system</p>
<hr>
</body>
</html>
HTML;
        $this->assertEquals(trim($cmpHtml), trim($html));
    }
    
    public function testGetManPart() {
        $mandoc = <<<MANDOC
TEST

         test       this is the test


TEST2


         This is the text
MANDOC;
        $exceptPart = <<<PART
         test       this is the test
PART;
        $groff = new Groff();
        $part = $groff->getManPart($mandoc, 'TEST');
        $this->assertSame($exceptPart, $part);
    }
    
    public function testGetTable() {
        $mandocPart = <<<MANDOC
some text
another some text

       123  test
       next row
       456  test

some text
MANDOC;
        $exceptTable = <<<TABLE
       123  test
       next row
       456  test
TABLE;
        $groff = new Groff();
        $table = $groff->getTable($mandocPart, '123  test');
        $this->assertSame($exceptTable, $table);
        
    }
    
    public function testGetArrayFromTable() {
        $tableString = <<<TABLE
       123   test
       next  row text and
             part of prev row
       456   test
TABLE;
        $exceptTableArray = [
          ['123', 'test'],
          ['next', 'row text and part of prev row'],
          ['456', 'test']
        ];
        $groff = new Groff();
        $tableArray = $groff->getArrayFromTable($tableString);
        $this->assertEquals($exceptTableArray, $tableArray);
    }
}
