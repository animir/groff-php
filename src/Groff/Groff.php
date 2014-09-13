<?php
namespace Groff;
/**
 * Wrapper for groff
 *
 * @author animir
 */

class Groff {
    
    private $executable;
    
    private $outFormat = [
        'dvi',
        'html',
        'xhtml',
        'lbp',
        'lj4',
        'ps',
        'pdf'
    ];
    
     /**
     * Setup path to the groff
     *
     * @param string $executable Path to the pandoc executable
     */
    public function __construct($executable = null)
    {
        if ( ! $executable) {
            exec('which groff', $output, $returnVar);
            if ($returnVar === 0) {
                $this->executable = $output[0];
            } else {
                throw new \Exception('Unable to locate groff');
            }
        } else {
            $this->executable = $executable;
        }

        if ( ! is_executable($this->executable)) {
            throw new \Exception('Groff is not executable');
        }
    }

    /**
     * Run the conversion from one type to another
     *
     * 
     * @param string $from The type we are converting from
     * @param string $to   The type we want to convert the document to
     *
     * @return string
     */
    public function convert($string, $to = 'html')
    {
        if ( ! in_array($to, $this->outFormat)) {
            throw new Exception(
                sprintf('%s is not a valid real device for groff. See "man groff"', $to)
            );
        }
        $tmpFile = sprintf("%s/%s", sys_get_temp_dir(), uniqid("groff"));
        file_put_contents($tmpFile, $string);

        $command = sprintf(
            '%s -T%s %s',            
            $this->executable,
            $to,
            $tmpFile
        );

        exec($command, $output);
        unlink($tmpFile);
        return implode("\n", $output);
    }
    
    /**
     * Return part of mandoc document by first level header
     * 
     * @param string $mandoc
     * @param string $header
     * @return string|null
     */
    public function getManPart($mandoc, $header) {
        preg_match("/$header\R\R(.+)\R\R\R/msU", $mandoc, $matches);        
        if (isset($matches[1])) {
            return $matches[1];
        } else {
            return null;
        }
    }
    
    /**
     * Get table data from mandoc by firtColumn content
     * 
     * @param type $mandoc
     * @param string $firstRow
     * @return string|null
     */
    public function getTable($mandoc, $firstRow) {
        $firstRow = addslashes($firstRow);
        preg_match("/\R\R(\s*$firstRow.+)\R\R/msU", $mandoc, $matches);         
        if (isset($matches[1])) {
            return $matches[1];
        } else {
            return null;
        }        
    }
    
    /**
     * Get two columns array from mandoc table with two columns in string
     * For this string:
     *<<<TABLE
     * 123   test
     * next  row text and
     *         part of prev row
     * 456   test
     * TABLE;
     * 
     * Array:
     * [
     *    ['123', 'test'],
     *    ['next', 'row text and part of prev row'],
     *    ['456', 'test']
     * ];
     * 
     * 
     * @param string $tableString
     * @return array
     */
    public function getArrayFromTable($tableString) {
        $tableArray = [];
        $rowsArray = preg_split('#\R#', $tableString);
        
        if (count($rowsArray) === 0) return $tableArray;
        
        preg_match("#(\s+)\S+.+#", $rowsArray[0], $matches);
        $countStartSpaces = strlen($matches[1]);
        foreach($rowsArray as $row) {
            preg_match("#(\s+)\S+.+#", $row, $matches);
            if (strlen($matches[1]) === $countStartSpaces) {
                preg_match("#\s+(\S+)\s{2,}(.+)#", $row, $matches);                
                $tableArray []= [$matches[1], $matches[2]];
            } else {
                preg_match("#\s+(.+)#", $row, $matches);
                end($tableArray);
                $tableArray [key($tableArray)][1] .= ' ' . $matches[1];
            }
        }
        
        return $tableArray;
    }
}
